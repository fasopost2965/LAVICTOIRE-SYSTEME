<?php
$hostname = 'localhost';
$username = 'u707543112_evictoire';
$password = 'Prodesk@1922';
$database = 'u707543112_evictoire';

$mysqli = new mysqli($hostname, $username, $password, $database);

echo "--- STUDENTS TABLE COLUMNS ---\n";
$res = $mysqli->query("SHOW COLUMNS FROM students");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n--- STUDENT_SESSION TABLE COLUMNS ---\n";
$res = $mysqli->query("SHOW COLUMNS FROM student_session");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

$mysqli->close();
