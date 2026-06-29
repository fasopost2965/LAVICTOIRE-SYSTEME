<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$sql = "SELECT id, admission_no, parent_id FROM students WHERE id >= 149";
$res = $c->query($sql);
while($s = $res->fetch_assoc()) {
    $sid = $s['id'];
    $pid = $s['parent_id'];
    
    $u_s = $c->query("SELECT id FROM users WHERE user_id = $sid AND role = 'student'")->num_rows;
    $u_p = $c->query("SELECT id FROM users WHERE user_id = $pid AND role = 'parent'")->num_rows;
    
    echo "Student $sid ($u_s) | Parent $pid ($u_p)\n";
}
$c->close();
?>
