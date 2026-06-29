<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$r = $c->query("DESCRIBE users");
while ($row = $r->fetch_assoc()) {
    echo "Field: " . $row['Field'] . " - Type: " . $row['Type'] . "\n";
}
$c->close();
