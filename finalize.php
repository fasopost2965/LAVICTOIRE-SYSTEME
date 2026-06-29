<?php
/**
 * Post-import Verification and Finalization
 */

$db_config = [
    'host' => 'localhost',
    'user' => 'u707543112_evictoire',
    'pass' => 'Prodesk@1922',
    'name' => 'u707543112_evictoire'
];

$conn = new mysqli($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['name']);
$conn->set_charset("utf8mb4");

echo "--- FINAL VERIFICATION ---\n";

// 1. Count CM1 students
$res = $conn->query("SELECT count(*) as count FROM student_session WHERE class_id IN (20, 21) AND session_id = 22");
$count = $res->fetch_assoc()['count'];
echo "Total CM1 students in Session 22: $count\n";

// 2. Check parents
$res = $conn->query("SELECT count(DISTINCT parent_id) as count FROM students WHERE id IN (SELECT student_id FROM student_session WHERE class_id IN (20, 21))");
$parents = $res->fetch_assoc()['count'];
echo "Total unique parents linked: $parents\n";

// 3. Check Fees
$res = $conn->query("SELECT fg.name, count(*) as count 
                     FROM student_fees_master sfm 
                     JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id 
                     JOIN fee_groups fg ON fg.id = fsg.fee_groups_id 
                     WHERE sfm.student_session_id IN (SELECT id FROM student_session WHERE class_id IN (20, 21))
                     GROUP BY fg.name");
echo "Fee assignment summary:\n";
while ($row = $res->fetch_assoc()) {
    echo "  - " . $row['name'] . ": " . $row['count'] . "\n";
}

// 4. Configure Attendance if missing
$check = $conn->query("SELECT id FROM student_attendence_schedules WHERE class_id IN (20, 21)");
if ($check->num_rows == 0) {
    echo "Configuring attendance schedules for CM1...\n";
    $conn->query("INSERT INTO student_attendence_schedules (class_id, entry_time, exit_time) VALUES (20, '08:00:00', '16:00:00')");
    $conn->query("INSERT INTO student_attendence_schedules (class_id, entry_time, exit_time) VALUES (21, '08:00:00', '16:00:00')");
    echo "  - Done (08:00 - 16:00).\n";
}

$conn->close();
?>
