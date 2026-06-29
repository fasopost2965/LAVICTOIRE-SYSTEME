<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$r = $c->query("SELECT id, class FROM classes");
while($row = $r->fetch_assoc()) {
    echo $row['id'] . ": " . $row['class'] . "\n";
}
$c->close();
?>
