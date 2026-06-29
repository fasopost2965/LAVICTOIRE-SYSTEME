<?php
$hostname = 'localhost';
$username = 'u707543112_evictoire';
$password = 'Prodesk@1922';
$database = 'u707543112_evictoire';

$mysqli = new mysqli($hostname, $username, $password, $database);

echo "--- CLASSES ---\n";
$res = $mysqli->query("SELECT * FROM classes");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Name: " . $row['class'] . "\n";
}

echo "\n--- SECTIONS ---\n";
$res = $mysqli->query("SELECT * FROM sections");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Name: " . $row['section'] . "\n";
}

echo "\n--- CLASS SECTIONS ---\n";
$res = $mysqli->query("SELECT cs.id, c.class, s.section FROM class_sections cs JOIN classes c ON c.id = cs.class_id JOIN sections s ON s.id = cs.section_id");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Class: " . $row['class'] . " - Section: " . $row['section'] . "\n";
}

echo "\n--- CE2 STUDENTS DETAIL ---\n";
// Let's find students in CE2 (Class ID 18 based on previous run) for Session 21 and 22
$res = $mysqli->query("SELECT ss.session_id, s.admission_no, s.firstname, s.lastname FROM student_session ss JOIN students s ON s.id = ss.student_id WHERE ss.class_id = 18");
$counts = [];
while ($row = $res->fetch_assoc()) {
    $counts[$row['session_id']] = ($counts[$row['session_id']] ?? 0) + 1;
}
foreach ($counts as $sid => $count) {
    echo "Session $sid: $count students\n";
}

$mysqli->close();
