<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$r = $c->query("SELECT id, admission_no FROM students WHERE id >= 88");
while ($row = $r->fetch_assoc()) {
    echo $row['id'] . ": [" . $row['admission_no'] . "]\n";
}
$c->close();
