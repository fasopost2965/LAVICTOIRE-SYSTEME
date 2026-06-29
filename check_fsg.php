<?php
$hostname = 'localhost';
$username = 'u707543112_evictoire';
$password = 'Prodesk@1922';
$database = 'u707543112_evictoire';

$mysqli = new mysqli($hostname, $username, $password, $database);

echo "--- FEE SESSION GROUPS (NEW DB) ---\n";
$res = $mysqli->query("SELECT fsg.id, fg.name FROM fee_session_groups fsg JOIN fee_groups fg ON fg.id = fsg.fee_groups_id WHERE fsg.session_id = 22");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Group Name: " . $row['name'] . "\n";
}

$mysqli->close();
