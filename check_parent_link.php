<?php
$c = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$r = $c->query("SELECT s.id, s.parent_id, u.role FROM students s JOIN users u ON u.id = s.parent_id WHERE u.role = 'parent' LIMIT 1");
if ($row = $r->fetch_assoc()) {
    echo "Found parent link: Student ID " . $row['id'] . " -> Parent User ID " . $row['parent_id'] . " (Role: " . $row['role'] . ")\n";
} else {
    echo "No standard parent link found in legacy DB.\n";
}
$c->close();
