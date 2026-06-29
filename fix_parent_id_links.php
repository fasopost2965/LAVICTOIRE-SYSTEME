<?php
/**
 * Fix students.parent_id to point to users.id (parent role)
 */

$conn = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');

// Get all students and their "logical" parent_id (which is currently users.user_id)
$res = $conn->query("SELECT id, parent_id FROM students WHERE id >= 1");

echo "Fixing students.parent_id links...\n";

$count = 0;
while ($s = $res->fetch_assoc()) {
    $sid = $s['id'];
    $logical_pid = $s['parent_id'];
    
    // Find the users record for this parent
    $u_res = $conn->query("SELECT id FROM users WHERE user_id = $logical_pid AND role = 'parent' LIMIT 1");
    if ($u_row = $u_res->fetch_assoc()) {
        $real_users_id = $u_row['id'];
        
        // Update students table to point to the real users.id
        if ($logical_pid != $real_users_id) {
            $conn->query("UPDATE students SET parent_id = $real_users_id WHERE id = $sid");
            $count++;
        }
    }
}

$conn->close();
echo "Done. Updated $count students.\n";
?>
