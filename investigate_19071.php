<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$sql = "SELECT id, admission_no, parent_id FROM students WHERE admission_no = '19071'";
$res = $c->query($sql);
while($row = $res->fetch_assoc()) {
    print_r($row);
    $sid = $row['id'];
    $pid = $row['parent_id'];
    $u_s = $c->query("SELECT * FROM users WHERE user_id = $sid AND role = 'student'")->fetch_assoc();
    $u_p = $c->query("SELECT * FROM users WHERE user_id = $pid AND role = 'parent'")->fetch_assoc();
    echo "Student User:\n"; print_r($u_s);
    echo "Parent User:\n"; print_r($u_p);
}
$c->close();
?>
