<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$r = $c->query("SELECT role, count(*) as count FROM users GROUP BY role");
while ($row = $r->fetch_assoc()) {
    echo "Role: [" . $row['role'] . "] - Count: " . $row['count'] . "\n";
}
$c->close();
