<?php
$c = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$sql = "SELECT s.id, s.admission_no, ss.session_id, ss.class_id 
        FROM students s 
        LEFT JOIN student_session ss ON ss.student_id = s.id 
        WHERE s.admission_no = '19282'";
$r = $c->query($sql);
while($row = $r->fetch_assoc()) {
    print_r($row);
}
$c->close();
?>
