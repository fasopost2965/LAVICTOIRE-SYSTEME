<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922');
$r = $c->query("SHOW DATABASES");
while($row = $r->fetch_row()) {
    echo $row[0] . "\n";
}
$c->close();
?>
