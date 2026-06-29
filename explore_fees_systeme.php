<?php
$hostname = 'localhost';
$username = 'u707543112_systeme';
$password = '/|098hH7';
$database = 'u707543112_systeme';

$mysqli = new mysqli($hostname, $username, $password, $database);

echo "--- FEE GROUPS (SYSTEME) ---\n";
$res = $mysqli->query("SELECT * FROM fee_groups");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Name: " . $row['name'] . "\n";
}

echo "\n--- FEE TYPES (SYSTEME) ---\n";
$res = $mysqli->query("SELECT * FROM feetype");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Name: " . $row['type'] . " - Code: " . $row['code'] . "\n";
}

echo "\n--- FEE GROUPS FEETYPE (SYSTEME) ---\n";
$res = $mysqli->query("SELECT fg.name as group_name, ft.type as type_name, fgft.amount 
                     FROM fee_groups_feetype fgft 
                     JOIN fee_groups fg ON fg.id = fgft.fee_groups_id 
                     JOIN feetype ft ON ft.id = fgft.feetype_id");
while ($row = $res->fetch_assoc()) {
    echo "Group: " . $row['group_name'] . " - Type: " . $row['type_name'] . " - Amount: " . $row['amount'] . "\n";
}

$mysqli->close();
