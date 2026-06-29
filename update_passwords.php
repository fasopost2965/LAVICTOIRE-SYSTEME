<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$sql = "UPDATE users SET password = 'victoire2026' WHERE password LIKE '$2y$%'";
$c->query($sql);
echo "Updated " . $c->affected_rows . " passwords to plain text 'victoire2026'\n";
$c->close();
