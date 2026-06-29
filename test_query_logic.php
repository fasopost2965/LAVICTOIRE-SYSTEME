<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$sid = 111;
$sql = "select students.parent_id from users INNER JOIN students on students.id =users.user_id WHERE users.user_id=$sid AND users.role ='student'";
$res = $c->query($sql);
$row = $res->fetch_assoc();
echo "Parent ID for 111 from subquery: " . $row['parent_id'] . "\n";

$pid = $row['parent_id'];
$u = $c->query("SELECT * FROM users WHERE id = $pid")->fetch_assoc();
echo "User record for ID $pid:\n";
print_r($u);

echo "\n--- Now for 180 ---\n";
$sid = 180;
$sql = "select students.parent_id from users INNER JOIN students on students.id =users.user_id WHERE users.user_id=$sid AND users.role ='student'";
$res = $c->query($sql);
$row = $res->fetch_assoc();
echo "Parent ID for 180 from subquery: " . $row['parent_id'] . "\n";

$pid = $row['parent_id'];
$u = $c->query("SELECT * FROM users WHERE id = $pid")->fetch_assoc();
echo "User record for ID $pid:\n";
print_r($u);

$c->close();
?>
