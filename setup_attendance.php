<?php
$hostname = 'localhost';
$username = 'u707543112_evictoire';
$password = 'Prodesk@1922';
$database = 'u707543112_evictoire';

$mysqli = new mysqli($hostname, $username, $password, $database);

echo "--- ATTENDANCE SCHEDULES ---\n";
$res = $mysqli->query("SELECT s.*, c.class FROM student_attendence_schedules s JOIN classes c ON c.id = s.class_id");
if ($res->num_rows == 0) {
    echo "No schedules found.\n";
} else {
    while ($row = $res->fetch_assoc()) {
        echo "Class: " . $row['class'] . " - Time: " . $row['entry_time'] . " to " . $row['exit_time'] . "\n";
    }
}

// If CM1 is missing, copy from CE2
$check = $mysqli->query("SELECT id FROM student_attendence_schedules WHERE class_id IN (20, 21)");
if ($check->num_rows == 0) {
    echo "CM1 schedules missing. Copying from CE2 (ID 18)...\n";
    $ce2 = $mysqli->query("SELECT * FROM student_attendence_schedules WHERE class_id = 18 LIMIT 1");
    if ($row = $ce2->fetch_assoc()) {
        $entry = $row['entry_time'];
        $exit = $row['exit_time'];
        $mysqli->query("INSERT INTO student_attendence_schedules (class_id, entry_time, exit_time) VALUES (20, '$entry', '$exit')");
        $mysqli->query("INSERT INTO student_attendence_schedules (class_id, entry_time, exit_time) VALUES (21, '$entry', '$exit')");
        echo "Copied to CM1-A and CM1-B.\n";
    } else {
        echo "CE2 schedules also missing. Creating default 08:00 - 16:00...\n";
        $mysqli->query("INSERT INTO student_attendence_schedules (class_id, entry_time, exit_time) VALUES (20, '08:00:00', '16:00:00')");
        $mysqli->query("INSERT INTO student_attendence_schedules (class_id, entry_time, exit_time) VALUES (21, '08:00:00', '16:00:00')");
    }
}

$mysqli->close();
