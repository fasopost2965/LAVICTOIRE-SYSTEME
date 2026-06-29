<?php
$file = 'application/logs/test_php.txt';
if (file_put_contents($file, 'test')) {
    echo 'Successfully wrote to ' . $file;
} else {
    echo 'Failed to write to ' . $file;
    print_r(error_get_last());
}
?>
