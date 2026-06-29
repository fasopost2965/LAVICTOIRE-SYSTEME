<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$r = $c->query("SELECT session_id FROM sch_settings LIMIT 1");
$row = $r->fetch_assoc();
echo "Current Session ID (Target): " . $row['session_id'] . "\n";

$r = $c->query("SELECT * FROM sessions WHERE id = " . $row['session_id']);
$row = $r->fetch_assoc();
echo "Current Session Name: " . $row['session'] . "\n";
$c->close();
?>
