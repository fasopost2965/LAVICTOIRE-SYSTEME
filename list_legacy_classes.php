<?php
$c = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$r = $c->query("SELECT id, class FROM classes");
while($row = $r->fetch_assoc()) {
    echo $row['id'] . ": " . $row['class'] . "\n";
}
$c->close();
?>
