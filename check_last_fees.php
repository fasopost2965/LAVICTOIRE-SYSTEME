<?php
$conn = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$sql = "SELECT s.id, s.admission_no, s.firstname, s.lastname, ss.id as student_session_id
        FROM students s
        JOIN student_session ss ON ss.student_id = s.id
        WHERE ss.session_id = 21 AND ss.class_id = 1
        ORDER BY CAST(s.admission_no AS UNSIGNED) ASC";
$res = $conn->query($sql);
while($row = $res->fetch_assoc()) {
    $ss_id = $row['student_session_id'];
    $fee_sql = "SELECT fgft.amount 
                FROM student_fees_master sfm
                JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
                JOIN fee_groups_feetype fgft ON fgft.fee_session_group_id = fsg.id
                JOIN feetype ft ON ft.id = fgft.feetype_id
                WHERE sfm.student_session_id = $ss_id AND ft.type LIKE '%Scolarit%'
                ORDER BY fgft.amount DESC LIMIT 1";
    $fee_res = $conn->query($fee_sql);
    $fee_row = $fee_res->fetch_assoc();
    $amount = $fee_row['amount'] ?? 0;
    
    echo $row['admission_no'] . " | " . $row['firstname'] . " " . $row['lastname'] . " | " . $amount . "\n";
}
$conn->close();
?>
