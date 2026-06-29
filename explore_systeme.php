<?php
$hostname = 'localhost';
$username = 'u707543112_systeme';
$password = '/|098hH7';
$database = 'u707543112_systeme';

$mysqli = new mysqli($hostname, $username, $password, $database);

if ($mysqli->connect_errno) {
    echo "Connection failed: " . $mysqli->connect_error;
    exit();
}

echo "--- CHECKING CLASSES AND SECTIONS (SYSTEME) ---\n";
$res = $mysqli->query("SELECT * FROM classes WHERE class LIKE '%CE2%'");
while ($row = $res->fetch_assoc()) {
    echo "Class: " . $row['id'] . " - " . $row['class'] . "\n";
}

echo "\n--- CHECKING CE2 STUDENTS (SESSION 21) ---\n";
$session_id = 21;
$sql = "SELECT count(*) as count FROM student_session ss JOIN students s ON s.id = ss.student_id WHERE ss.session_id = $session_id AND ss.class_id IN (SELECT id FROM classes WHERE class LIKE '%CE2%')";
$res = $mysqli->query($sql);
if ($row = $res->fetch_assoc()) {
    echo "Students in CE2 (Session 21): " . $row['count'] . "\n";
}

echo "\n--- CHECKING TARIFS ---\n";
$res = $mysqli->query("SELECT DISTINCT fgft.amount, fg.name 
                     FROM fee_groups_feetype fgft 
                     JOIN fee_groups fg ON fg.id = fgft.fee_groups_id 
                     WHERE fg.name LIKE '%Scolarit??%' AND fg.name LIKE '%Primaire%'");
while ($row = $res->fetch_assoc()) {
    echo "Fee: " . $row['amount'] . " - " . $row['name'] . "\n";
}

$mysqli->close();
