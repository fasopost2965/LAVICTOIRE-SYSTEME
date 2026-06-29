<?php
$hostname = 'localhost';
$username = 'u707543112_evictoire';
$password = 'Prodesk@1922';
$database = 'u707543112_evictoire';

$mysqli = new mysqli($hostname, $username, $password, $database);

echo "--- ID CARDS CONFIG ---\n";
$res = $mysqli->query("SELECT * FROM id_cards");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Title: " . $row['title'] . "\n";
    echo "  Enable QR: " . $row['enable_vertical_card'] . " (Wait, columns might be different)\n";
    print_r($row);
}

echo "\n--- CHECKING IF QR CODE IS EXPECTED IN DIFFERENT FOLDER ---\n";
// Sometimes it's uploads/student_id_card/qrcode/ or uploads/student_id_card/
$mysqli->close();
