<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');

echo "--- SECTIONS ---\n";
$r = $c->query("SELECT * FROM sections");
while($row = $r->fetch_assoc()) {
    echo $row['id'] . ": " . $row['section'] . "\n";
}

echo "\n--- FEE GROUPS ---\n";
$r = $c->query("SELECT * FROM fee_groups");
while($row = $r->fetch_assoc()) {
    echo $row['id'] . ": " . $row['name'] . "\n";
}

echo "\n--- LEGACY STUDENTS (CM1) ---\n";
$c2 = new mysqli('localhost', 'u707543112_systeme', 'Prodesk@1922', 'u707543112_systeme');
$r = $c2->query("SELECT COUNT(*) as count FROM student_session JOIN students ON students.id = student_session.student_id WHERE student_session.session_id = 21 AND student_session.class_id = (SELECT id FROM classes WHERE class = 'CM1' LIMIT 1)");
$row = $r->fetch_assoc();
echo "Total CM1 Students: " . $row['count'] . "\n";

$c->close();
$c2->close();
?>
