<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$sql = "SELECT username, role FROM users WHERE role = 'parent' LIMIT 10";
$r = $c->query($sql);
while($row = $r->fetch_assoc()) {
    echo $row['username'] . " | " . $row['role'] . "\n";
}
$c->close();
?>
