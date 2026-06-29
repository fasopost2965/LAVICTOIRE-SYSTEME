<?php
/**
 * Sync father/mother fields with guardian fields based on guardian_is
 */

$conn = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');

$res = $conn->query("SELECT id, guardian_is, guardian_name, guardian_phone, guardian_email, guardian_relation FROM students WHERE id >= 111");

echo "Syncing guardian fields...\n";

while ($s = $res->fetch_assoc()) {
    $id = $s['id'];
    $is = $s['guardian_is'];
    $name = addslashes($s['guardian_name']);
    $phone = $s['guardian_phone'];
    $email = $s['guardian_email'];
    
    if ($is == 'father') {
        $conn->query("UPDATE students SET father_name = '$name', father_phone = '$phone' WHERE id = $id");
    } elseif ($is == 'mother') {
        $conn->query("UPDATE students SET mother_name = '$name', mother_phone = '$phone' WHERE id = $id");
    }
}

$conn->close();
echo "Done.\n";
?>
