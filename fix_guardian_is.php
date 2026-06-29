<?php
/**
 * Fix guardian_is field for migrated students
 */

$legacy_conn = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$prod_conn = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');

// Fetch migrated students (IDs >= 111)
$res = $prod_conn->query("SELECT id, admission_no FROM students WHERE id >= 111");

echo "Fixing guardian_is field...\n";

while ($s = $res->fetch_assoc()) {
    $adm = $s['admission_no'];
    $l_res = $legacy_conn->query("SELECT guardian_is FROM students WHERE admission_no = '$adm' LIMIT 1");
    $l_row = $l_res->fetch_assoc();
    
    $guardian_is = $l_row['guardian_is'] ?? 'father'; // Default to father if not found
    
    if (empty($guardian_is)) $guardian_is = 'father';
    
    $prod_conn->query("UPDATE students SET guardian_is = '$guardian_is' WHERE id = {$s['id']}");
}

$legacy_conn->close();
$prod_conn->close();
echo "Done.\n";
?>
