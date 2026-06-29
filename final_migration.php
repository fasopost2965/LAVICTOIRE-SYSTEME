<?php
/**
 * FINAL CE2 TO CM1 IMPORT SCRIPT
 * Migrates data from u707543112_systeme to u707543112_evictoire
 */

$legacy_db = [
    'host' => 'localhost',
    'user' => 'u707543112_systeme',
    'pass' => '/|098hH7',
    'name' => 'u707543112_systeme'
];

$new_db = [
    'host' => 'localhost',
    'user' => 'u707543112_evictoire',
    'pass' => 'Prodesk@1922',
    'name' => 'u707543112_evictoire'
];

$conn_legacy = new mysqli($legacy_db['host'], $legacy_db['user'], $legacy_db['pass'], $legacy_db['name']);
$conn_new = new mysqli($new_db['host'], $new_db['user'], $new_db['pass'], $new_db['name']);

$conn_legacy->set_charset("utf8");
$conn_new->set_charset("utf8");

// Settings
$target_session_id = 22;
$target_section_id = 2; // Primaire
$class_map = [
    'A' => 20, // CM1-A
    'B' => 21  // CM1-B
];
$fee_map = [
    900 => 2, // Standard
    750 => 3  // Legacy
];

echo "--- STARTING MIGRATION ---\n";

// 1. Get CE2 Students from Legacy (Session 21)
$sql = "SELECT s.*, ss.id as old_student_session_id 
        FROM students s 
        JOIN student_session ss ON s.id = ss.student_id 
        WHERE ss.session_id = 21 AND ss.class_id = 4 AND s.is_active = 'yes'
        ORDER BY s.lastname ASC, s.firstname ASC";
$result = $conn_legacy->query($sql);

$index = 0;
while ($student = $result->fetch_assoc()) {
    $admission_no = $student['admission_no'];
    echo "Processing: $admission_no - " . $student['firstname'] . " " . $student['lastname'] . "\n";

    // Check if already exists in new DB
    $check = $conn_new->query("SELECT id FROM students WHERE admission_no = '$admission_no'");
    if ($check->num_rows > 0) {
        echo "  - SKIP: Student already exists in new DB.\n";
        continue;
    }

    // A/B Split
    $groupe = ($index % 2 === 0) ? 'A' : 'B';
    $target_class_id = $class_map[$groupe];
    $index++;

    // 2. Determine Fee Type (from legacy)
    $tarif_base = 0;
    $fee_sql = "SELECT fgft.amount 
                FROM student_fees_master sfm 
                JOIN fee_session_groups fsg ON sfm.fee_session_group_id = fsg.id 
                JOIN fee_groups_feetype fgft ON fsg.fee_groups_id = fgft.fee_groups_id 
                JOIN fee_groups fg ON fgft.fee_groups_id = fg.id 
                WHERE sfm.student_session_id = " . $student['old_student_session_id'] . " 
                AND fg.name LIKE '%Scolarit%' AND fg.name LIKE '%Primair%'
                LIMIT 1";
    $fee_res = $conn_legacy->query($fee_sql);
    if ($fee_row = $fee_res->fetch_assoc()) {
        $tarif_base = (int)$fee_row['amount'];
    }
    $target_fee_group_id = isset($fee_map[$tarif_base]) ? $fee_map[$tarif_base] : 2; // Default to Standard

    // 3. Create Parent User (if not exists)
    // In Smart School, parents are often shared. We search by guardian_phone or guardian_email.
    $parent_id = 0;
    $g_phone = $student['guardian_phone'];
    $p_check = $conn_new->query("SELECT user_id FROM users WHERE username = '$g_phone' AND role = 'parent'");
    if ($p_check->num_rows > 0) {
        $parent_id = $p_check->fetch_assoc()['user_id'];
        echo "  - Found existing parent ID: $parent_id\n";
    } else {
        // Create new parent user
        $p_pass = password_hash('victoire2026', PASSWORD_DEFAULT);
        $conn_new->query("INSERT INTO users (username, password, role, is_active) VALUES ('$g_phone', '$p_pass', 'parent', 'yes')");
        $parent_id = $conn_new->insert_id;
        echo "  - Created new parent ID: $parent_id\n";
    }

    // 4. Insert Student
    $fields = [];
    $values = [];
    $student['parent_id'] = $parent_id;
    $student['is_active'] = 'yes';
    
    // Remove internal IDs and non-existent columns
    unset($student['id']);
    unset($student['old_student_session_id']);
    
    foreach ($student as $key => $val) {
        $fields[] = "`$key`";
        $values[] = "'" . $conn_new->real_escape_string($val) . "'";
    }
    
    $insert_sql = "INSERT INTO students (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
    if ($conn_new->query($insert_sql)) {
        $new_student_id = $conn_new->insert_id;
        echo "  - Student inserted (New ID: $new_student_id)\n";

        // 5. Insert Student Session
        $ss_sql = "INSERT INTO student_session (session_id, student_id, class_id, section_id, is_active) 
                   VALUES ($target_session_id, $new_student_id, $target_class_id, $target_section_id, 'yes')";
        $conn_new->query($ss_sql);
        $new_ss_id = $conn_new->insert_id;
        echo "  - Student session created (ID: $new_ss_id)\n";

        // 6. Create Student User
        $s_user = "std" . $admission_no;
        $s_pass = password_hash('victoire2026', PASSWORD_DEFAULT);
        $conn_new->query("INSERT INTO users (username, password, role, user_id, is_active) VALUES ('$s_user', '$s_pass', 'student', $new_student_id, 'yes')");

        // 7. Assign Fees
        $conn_new->query("INSERT INTO student_fees_master (student_session_id, fee_session_group_id) VALUES ($new_ss_id, $target_fee_group_id)");
        echo "  - Fee group $target_fee_group_id assigned.\n";
    } else {
        echo "  - ERROR inserting student: " . $conn_new->error . "\n";
    }
}

echo "--- MIGRATION COMPLETED ---\n";
$conn_legacy->close();
$conn_new->close();
?>
