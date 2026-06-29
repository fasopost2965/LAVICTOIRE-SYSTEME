<?php
$c = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');

$sql = "SELECT s.admission_no, s.firstname, s.lastname, fg.name as fee_group, fgft.amount
        FROM students s
        JOIN student_session ss ON ss.student_id = s.id
        JOIN student_fees_master sfm ON sfm.student_session_id = ss.id
        JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
        JOIN fee_groups fg ON fg.id = fsg.fee_groups_id
        JOIN fee_groups_feetype fgft ON fgft.fee_session_group_id = fsg.id
        JOIN feetype ft ON ft.id = fgft.feetype_id
        WHERE ss.session_id = 21 AND ss.class_id = 3 AND ft.type LIKE '%Scolarit%'
        ORDER BY s.admission_no";

$r = $c->query($sql);
if (!$r) {
    die("Query failed: " . $c->error);
}

while($row = $r->fetch_assoc()) {
    echo $row['admission_no'] . " | " . $row['firstname'] . " " . $row['lastname'] . " | " . $row['fee_group'] . " | " . $row['amount'] . "\n";
}
$c->close();
?>
