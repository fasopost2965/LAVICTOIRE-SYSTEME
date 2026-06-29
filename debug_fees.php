<?php
$hostname = 'localhost';
$username = 'u707543112_systeme';
$password = '/|098hH7';
$database = 'u707543112_systeme';

$mysqli = new mysqli($hostname, $username, $password, $database);

echo "--- CHECKING STUDENT FEES MASTER FOR ONE STUDENT ---\n";
// Let's find one student in CE2 (Session 21)
$res = $mysqli->query("SELECT ss.id as student_session_id, s.firstname, s.lastname FROM student_session ss JOIN students s ON s.id = ss.student_id WHERE ss.session_id = 21 AND ss.class_id = 4 LIMIT 1");
if ($row = $res->fetch_assoc()) {
    $ss_id = $row['student_session_id'];
    echo "Student: " . $row['firstname'] . " " . $row['lastname'] . " (SS ID: $ss_id)\n";
    
    $res2 = $mysqli->query("SELECT sfm.id, sfm.fee_session_group_id FROM student_fees_master sfm WHERE sfm.student_session_id = $ss_id");
    while ($row2 = $res2->fetch_assoc()) {
        echo "Fee Master ID: " . $row2['id'] . " - Session Group ID: " . $row2['fee_session_group_id'] . "\n";
        
        $res3 = $mysqli->query("SELECT fg.name FROM fee_session_groups fsg JOIN fee_groups fg ON fg.id = fsg.fee_groups_id WHERE fsg.id = " . $row2['fee_session_group_id']);
        if ($row3 = $res3->fetch_assoc()) {
            echo "  Group Name: " . $row3['name'] . "\n";
        }
    }
} else {
    echo "No student found in CE2 Session 21\n";
}

$mysqli->close();
