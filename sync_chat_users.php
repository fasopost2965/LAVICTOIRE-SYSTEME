<?php
// sync_chat_users.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_config = [
    'host' => 'localhost',
    'user' => 'u707543112_evictoire',
    'pass' => 'Prodesk@1922',
    'name' => 'u707543112_evictoire'
];

$conn = new mysqli($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['name']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// 1. Add Staff
$staff_res = $conn->query("SELECT id FROM staff");
$staff_count = 0;
while ($staff = $staff_res->fetch_assoc()) {
    $staff_id = $staff['id'];
    $check = $conn->query("SELECT id FROM chat_users WHERE staff_id = $staff_id AND user_type = 'staff'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO chat_users (user_type, staff_id, is_active) VALUES ('staff', $staff_id, 1)");
        $staff_count++;
    }
}

// 2. Add Students and Parents
$student_res = $conn->query("SELECT id FROM students");
$student_count = 0;
$parent_count = 0;
while ($student = $student_res->fetch_assoc()) {
    $student_id = $student['id'];
    
    // Student
    $check_st = $conn->query("SELECT id FROM chat_users WHERE student_id = $student_id AND user_type = 'student'");
    if ($check_st->num_rows == 0) {
        $conn->query("INSERT INTO chat_users (user_type, student_id, is_active) VALUES ('student', $student_id, 1)");
        $student_count++;
    }
    
    // Parent
    $check_p = $conn->query("SELECT id FROM chat_users WHERE student_id = $student_id AND user_type = 'parent'");
    if ($check_p->num_rows == 0) {
        $conn->query("INSERT INTO chat_users (user_type, student_id, is_active) VALUES ('parent', $student_id, 1)");
        $parent_count++;
    }
}

echo "Sync finished:\n";
echo "- $staff_count staff added to chat\n";
echo "- $student_count students added to chat\n";
echo "- $parent_count parents added to chat\n";
?>
