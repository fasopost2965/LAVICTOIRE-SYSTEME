<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
echo "Student 111 (Works):\n";
$u1 = $c->query("SELECT * FROM users WHERE user_id = 111 AND role = 'student'")->fetch_assoc();
print_r($u1);
echo "Student 180 (Fails):\n";
$u2 = $c->query("SELECT * FROM users WHERE user_id = 180 AND role = 'student'")->fetch_assoc();
print_r($u2);
$c->close();
?>
