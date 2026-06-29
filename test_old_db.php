<?php
\ = new mysqli('localhost', 'u707543112_systeme', '/|098hH7', 'u707543112_systeme');
if (\->connect_error) {
    die('Connection failed: ' . \->connect_error);
}
\ = \->query('SHOW TABLES');
\ = [];
while (\ = \->fetch_array()) {
    \[] = \[0];
}
echo implode('\n', \);
\->close();
?>
