<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
echo "Parent of 111 (user_id 544):\n";
$u = $c->query("SELECT id, user_id, role FROM users WHERE user_id = 544 AND role = 'parent'")->fetch_assoc();
print_r($u);

echo "Parent of 180 (user_id 610):\n";
$u = $c->query("SELECT id, user_id, role FROM users WHERE user_id = 610 AND role = 'parent'")->fetch_assoc();
print_r($u);
$c->close();
?>
