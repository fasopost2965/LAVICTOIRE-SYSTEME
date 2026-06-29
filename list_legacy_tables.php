<?php
$c = new mysqli('localhost', 'u707543112_systeme', 'Prodesk@1922', 'u707543112_systeme');
$r = $c->query("SHOW TABLES");
while($row = $r->fetch_row()) {
    echo $row[0] . "\n";
}
$c->close();
?>
