<?php
$c = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$sql = "SELECT guardian_phone, COUNT(*) as count FROM students GROUP BY guardian_phone HAVING count > 1 ORDER BY count DESC";
$r = $c->query($sql);
while($row = $r->fetch_assoc()) {
    echo $row['guardian_phone'] . " : " . $row['count'] . "\n";
}
$c->close();
?>
