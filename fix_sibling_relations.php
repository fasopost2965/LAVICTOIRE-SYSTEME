<?php
/**
 * Fix Parent-Student Relations
 * This script ensures that siblings are correctly grouped based on Legacy parent_id
 * rather than just guardian_phone (which can be a placeholder).
 */

$legacy_conn = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$prod_conn = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');

// Fetch all migrated students from production
$res = $prod_conn->query("SELECT id, admission_no, parent_id FROM students WHERE id >= 111");

$legacy_to_prod_parent = [];
$students_to_fix = [];

echo "Analyzing relations...\n";

while ($student = $res->fetch_assoc()) {
    $adm = $student['admission_no'];
    
    // Find legacy parent_id
    $l_res = $legacy_conn->query("SELECT parent_id FROM students WHERE admission_no = '$adm' LIMIT 1");
    $l_row = $l_res->fetch_assoc();
    if ($l_row) {
        $legacy_p_id = $l_row['parent_id'];
        $students_to_fix[] = [
            'prod_id' => $student['id'],
            'legacy_p_id' => $legacy_p_id,
            'current_p_id' => $student['parent_id']
        ];
    }
}

// Map legacy parents to new production parents
// To avoid conflicts with existing IDs, we'll find the current MAX parent_id in prod
$max_p_res = $prod_conn->query("SELECT MAX(parent_id) as max_p FROM students");
$next_p_id = ($max_p_res->fetch_assoc()['max_p'] ?? 0) + 1;

foreach ($students_to_fix as $s) {
    $lp_id = $s['legacy_p_id'];
    if (!isset($legacy_to_prod_parent[$lp_id])) {
        // Assign a new prod_parent_id for this legacy parent group
        $legacy_to_prod_parent[$lp_id] = $next_p_id++;
    }
    
    $new_p_id = $legacy_to_prod_parent[$lp_id];
    
    if ($new_p_id != $s['current_p_id']) {
        echo "Fixing Student ID {$s['prod_id']} (Legacy P: {$lp_id}) -> New Prod P: {$new_p_id}\n";
        $prod_conn->query("UPDATE students SET parent_id = $new_p_id WHERE id = {$s['prod_id']}");
        
        // Also update the users table if a parent user exists for the OLD parent_id
        // Wait! Users are linked by user_id = parent_id.
        // We should create a NEW user for the NEW parent_id if it doesn't exist.
        $u_check = $prod_conn->query("SELECT id FROM users WHERE user_id = $new_p_id AND role = 'parent'");
        if ($u_check->num_rows == 0) {
            $username = "parent" . $new_p_id;
            $password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 6);
            $prod_conn->query("INSERT INTO users (user_id, username, password, role, is_active) VALUES ($new_p_id, '$username', '$password', 'parent', 'yes')");
        }
    }
}

$legacy_conn->close();
$prod_conn->close();
echo "Done fixing relations.\n";
?>
