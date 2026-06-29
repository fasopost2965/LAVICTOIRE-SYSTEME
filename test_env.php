<?php
require_once 'vendor/autoload.php';
echo "Autoload OK\n";
define('APPPATH', __DIR__ . '/application/');
set_include_path(get_include_path() . PATH_SEPARATOR . APPPATH . 'libraries');
require_once APPPATH . 'libraries/Zend/Barcode.php';
echo "Zend OK\n";
?>
