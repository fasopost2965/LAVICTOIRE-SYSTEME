<?php
$c = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$sql = "SELECT s.id, s.admission_no, s.firstname, s.lastname, fm.amount, ft.type
        FROM students s
        JOIN student_fees_master sfm ON sfm.student_id = s.id
        JOIN feemasters fm ON fm.id = sfm.fee_session_group_id
        JOIN feetype ft ON ft.id = fm.fee_type_id
        WHERE ft.type LIKE '%Scolarit%'
        LIMIT 10";
$r = $c->query($sql);
while($row = $r->fetch_assoc()) {
    echo $row['admission_no'] . " - " . $row['firstname'] . " " . $row['lastname'] . ": " . $row['amount'] . " (" . $row['type'] . ")\n";
}
$c->close();
?>
