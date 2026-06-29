<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$r = $c->query("SELECT * FROM users WHERE user_id = 2 AND role = 'parent'");
while($row = $r->fetch_assoc()) {
    print_r($row);
}
$c->close();
?>
