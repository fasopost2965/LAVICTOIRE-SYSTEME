<?php
$c = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$r = $c->query("SELECT * FROM sessions");
while($row = $r->fetch_assoc()) {
    echo $row['id'] . ": " . $row['session'] . " (Active: " . $row['is_active'] . ")\n";
}
$c->close();
?>
