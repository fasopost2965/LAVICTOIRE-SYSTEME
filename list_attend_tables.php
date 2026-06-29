<?php
$hostname = 'localhost';
$username = 'u707543112_evictoire';
$password = 'Prodesk@1922';
$database = 'u707543112_evictoire';

$mysqli = new mysqli($hostname, $username, $password, $database);

$res = $mysqli->query("SHOW TABLES");
while ($row = $res->fetch_row()) {
    if (stripos($row[0], 'attend') !== false) {
        echo $row[0] . "\n";
    }
}

$mysqli->close();
