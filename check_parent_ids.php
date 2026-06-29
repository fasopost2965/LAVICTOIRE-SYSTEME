<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$r = $c->query("SELECT parent_id, admission_no FROM students LIMIT 5");
while($row = $r->fetch_assoc()) {
    echo $row['parent_id'] . " | " . $row['admission_no'] . "\n";
}
$c->close();
?>
