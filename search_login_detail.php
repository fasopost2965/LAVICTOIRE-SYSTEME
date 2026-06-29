<?php
$dir = 'domains/groupelavictoire.com/public_html/e-victoire/application/';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($it as $file) {
    if ($file->isDir()) continue;
    $content = file_get_contents($file->getPathname());
    if (strpos($content, 'getlogindetail') !== false) {
        echo $file->getPathname() . "\n";
    }
}
?>
