<?php
$c = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$sql = "SELECT admission_no, guardian_is FROM students WHERE admission_no IN ('19071', '19069', '19070')";
$res = $c->query($sql);
while($row = $res->fetch_assoc()) {
    print_r($row);
}
$c->close();
?>
