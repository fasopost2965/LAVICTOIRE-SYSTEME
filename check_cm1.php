<?php
$hostname = 'localhost';
$username = 'u707543112_evictoire';
$password = 'Prodesk@1922';
$database = 'u707543112_evictoire';

$mysqli = new mysqli($hostname, $username, $password, $database);

echo "--- CM1 STUDENTS (NEW DB) ---\n";
$res = $mysqli->query("SELECT ss.session_id, c.class, count(*) as count FROM student_session ss JOIN classes c ON c.id = ss.class_id WHERE c.class LIKE '%CM1%' GROUP BY ss.session_id, c.class");
while ($row = $res->fetch_assoc()) {
    echo "Session: " . $row['session_id'] . " - Class: " . $row['class'] . " - Count: " . $row['count'] . "\n";
}

$mysqli->close();
