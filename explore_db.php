<?php
$hostname = 'localhost';
$username = 'u707543112_evictoire';
$password = 'Prodesk@1922';
$database = 'u707543112_evictoire';

$mysqli = new mysqli($hostname, $username, $password, $database);

if ($mysqli->connect_errno) {
    echo "Connection failed: " . $mysqli->connect_error;
    exit();
}

echo "--- CHECKING CLASSES AND SECTIONS ---\n";
$res = $mysqli->query("SELECT * FROM classes WHERE class LIKE '%CM1%'");
while ($row = $res->fetch_assoc()) {
    echo "Class: " . $row['id'] . " - " . $row['class'] . "\n";
}

$res = $mysqli->query("SELECT * FROM sections WHERE section IN ('A', 'B')");
while ($row = $res->fetch_assoc()) {
    echo "Section: " . $row['id'] . " - " . $row['section'] . "\n";
}

echo "\n--- CHECKING CE2 STUDENTS (SESSION 21) ---\n";
// Assuming session 21 is the 'old' one. I should check sessions table first.
$res = $mysqli->query("SELECT id, session FROM sessions");
while ($row = $res->fetch_assoc()) {
    echo "Session: " . $row['id'] . " - " . $row['session'] . "\n";
}

// Find CE2 class ID
$ce2_id = null;
$res = $mysqli->query("SELECT id FROM classes WHERE class LIKE '%CE2%' LIMIT 1");
if ($row = $res->fetch_assoc()) {
    $ce2_id = $row['id'];
    echo "CE2 Class ID: $ce2_id\n";
}

if ($ce2_id) {
    // Count students in CE2 for session 21 (if 21 is indeed the session ID)
    // Actually, I'll count for all sessions and see where they are.
    $res = $mysqli->query("SELECT session_id, count(*) as count FROM student_session WHERE class_id = $ce2_id GROUP BY session_id");
    while ($row = $res->fetch_assoc()) {
        echo "Students in CE2 (Session " . $row['session_id'] . "): " . $row['count'] . "\n";
    }
}

echo "\n--- CHECKING FEE GROUPS ---\n";
$res = $mysqli->query("SELECT id, name FROM fee_groups");
while ($row = $res->fetch_assoc()) {
    echo "Fee Group: " . $row['id'] . " - " . $row['name'] . "\n";
}

$mysqli->close();
