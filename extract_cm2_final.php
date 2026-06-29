<?php
/**
 * Extraction and Classification of CM2 Students for Migration to 6??me
 */

define('LEGACY_DB_HOST', 'localhost');
define('LEGACY_DB_USER', 'u707543112_systeme');
define('LEGACY_DB_PASS', '/|098hH7');
define('LEGACY_DB_NAME', 'u707543112_systeme');

$conn = new mysqli(LEGACY_DB_HOST, LEGACY_DB_USER, LEGACY_DB_PASS, LEGACY_DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// 1. Fetch CM2 Students (Session 21, Class 2)
$sql = "SELECT s.*, ss.id as student_session_id
        FROM students s
        JOIN student_session ss ON ss.student_id = s.id
        WHERE ss.session_id = 21 AND ss.class_id = 2
        ORDER BY CAST(s.admission_no AS UNSIGNED) ASC";

$result = $conn->query($sql);
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo "Found " . count($students) . " students.\n";

// 2. Classify by Fees
foreach ($students as &$student) {
    $ss_id = $student['student_session_id'];
    
    // Get Scolarit?? amount
    $fee_sql = "SELECT fgft.amount 
                FROM student_fees_master sfm
                JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
                JOIN fee_groups_feetype fgft ON fgft.fee_session_group_id = fsg.id
                JOIN feetype ft ON ft.id = fgft.feetype_id
                WHERE sfm.student_session_id = $ss_id AND ft.type LIKE '%Scolarit%'
                ORDER BY fgft.amount DESC LIMIT 1";
    
    $fee_res = $conn->query($fee_sql);
    $fee_row = $fee_res->fetch_assoc();
    $student['scolarite_amount'] = $fee_row['amount'] ?? 0;

    if ($student['scolarite_amount'] == 750) {
        $student['category'] = 'LEGACY_750';
    } elseif ($student['scolarite_amount'] == 900) {
        $student['category'] = 'STANDARD_900';
    } else {
        $student['category'] = 'A_VERIFIER';
    }
}
unset($student);

// 3. Generate CSVs
// Rule: count <= 15 -> 6eme-A (ID 25)
$target_class_section_id = 25;

$files = [
    'LEGACY_750' => fopen('CM2_LEGACY_750.csv', 'w'),
    'STANDARD_900' => fopen('CM2_STANDARD_900.csv', 'w'),
    'A_VERIFIER' => fopen('CM2_A_VERIFIER.csv', 'w')
];

$header = ['admission_no', 'firstname', 'lastname', 'gender', 'dob', 'guardian_name', 'guardian_phone', 'guardian_email', 'guardian_relation', 'category', 'class_section_id'];

foreach ($files as $f) fputcsv($f, $header);

foreach ($students as $student) {
    $row = [
        $student['admission_no'],
        $student['firstname'],
        $student['lastname'],
        $student['gender'],
        $student['dob'],
        $student['guardian_name'],
        $student['guardian_phone'],
        $student['guardian_email'],
        $student['guardian_relation'],
        $student['category'],
        $target_class_section_id
    ];
    
    fputcsv($files[$student['category']], $row);
}

foreach ($files as $f) fclose($f);
$conn->close();

echo "Extraction completed. 3 CSV files generated.\n";
?>
