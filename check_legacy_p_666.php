<?php
$c = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$sql = "SELECT admission_no, firstname, lastname, parent_id FROM students WHERE guardian_phone = '66666666'";
$r = $c->query($sql);
while($row = $r->fetch_assoc()) {
    print_r($row);
}
$c->close();
?>
