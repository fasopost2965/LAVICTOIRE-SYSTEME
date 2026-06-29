<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$sql = "SELECT u.username, u.role, s.admission_no 
        FROM users u 
        JOIN students s ON s.id = u.user_id 
        WHERE u.role = 'student' AND s.admission_no IS NOT NULL 
        LIMIT 10";
$r = $c->query($sql);
while($row = $r->fetch_assoc()) {
    echo $row['username'] . " | " . $row['role'] . " | " . $row['admission_no'] . "\n";
}
$c->close();
?>
