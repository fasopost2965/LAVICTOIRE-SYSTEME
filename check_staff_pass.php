<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$res = $c->query("SELECT email, password FROM staff");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
$c->close();
?>
