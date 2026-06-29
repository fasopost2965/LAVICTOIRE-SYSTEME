<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
echo "Parent of 111 (544):\n";
$u1 = $c->query("SELECT * FROM users WHERE user_id = 544 AND role = 'parent'")->fetch_assoc();
print_r($u1);
echo "Parent of 180 (610):\n";
$u2 = $c->query("SELECT * FROM users WHERE user_id = 610 AND role = 'parent'")->fetch_assoc();
print_r($u2);
$c->close();
?>
