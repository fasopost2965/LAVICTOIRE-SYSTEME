<?php
$c = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
$sql = "SELECT s.admission_no, ss.id as ss_id 
        FROM student_session ss 
        JOIN students s ON s.id = ss.student_id 
        WHERE ss.session_id = 21 AND ss.class_id = 3";
$r = $c->query($sql);
$counts = [];
while($row = $r->fetch_assoc()) {
    $adm = $row['admission_no'];
    if(!isset($counts[$adm])) $counts[$adm] = 0;
    $counts[$adm]++;
}
foreach($counts as $adm => $count) {
    if($count > 1) echo "$adm: $count\n";
}
$c->close();
?>
