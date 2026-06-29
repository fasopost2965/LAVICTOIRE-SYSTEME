<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$sql = "SELECT id, user_id, username, password, role FROM users WHERE user_id BETWEEN 149 AND 160 AND role = 'student'";
$res = $c->query($sql);
while($row = $res->fetch_assoc()) {
    print_r($row);
}
$c->close();
?>
