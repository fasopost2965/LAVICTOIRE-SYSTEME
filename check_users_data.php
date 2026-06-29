<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$sql = "SELECT * FROM users WHERE user_id >= 111";
$res = $c->query($sql);
while($row = $res->fetch_assoc()) {
    print_r($row);
}
$c->close();
?>
