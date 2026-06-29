<?php
/**
 * Cleanup Duplicate User Accounts
 * Keeps only the most recent user record for each (user_id, role) pair.
 */

$conn = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "Starting cleanup of duplicate users...\n";

// Find duplicates
$sql = "SELECT user_id, role, COUNT(*) as count 
        FROM users 
        GROUP BY user_id, role 
        HAVING count > 1";

$res = $conn->query($sql);
$total_deleted = 0;

while ($row = $res->fetch_assoc()) {
    $uid = $row['user_id'];
    $role = $row['role'];
    
    // Get all IDs for this user/role
    $ids_res = $conn->query("SELECT id FROM users WHERE user_id = $uid AND role = '$role' ORDER BY id DESC");
    $ids = [];
    while ($id_row = $ids_res->fetch_assoc()) {
        $ids[] = $id_row['id'];
    }
    
    // Keep the first (newest), delete the rest
    $keep_id = array_shift($ids);
    if (!empty($ids)) {
        $delete_ids = implode(',', $ids);
        echo "Keeping ID $keep_id, deleting IDs: $delete_ids for User: $uid Role: $role\n";
        $conn->query("DELETE FROM users WHERE id IN ($delete_ids)");
        $total_deleted += count($ids);
    }
}

echo "Cleanup completed. Total records deleted: $total_deleted\n";
$conn->close();
?>
