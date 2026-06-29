<?php
$file = 'domains/groupelavictoire.com/public_html/e-victoire/application/controllers/Student.php';
$content = file($file);
$found = false;
foreach($content as $i => $line) {
    if(strpos($line, 'getlogindetail') !== false) {
        $found = true;
        for($j = $i; $j < $i + 50; $j++) {
            echo $content[$j];
        }
        break;
    }
}
if(!$found) echo "Not found";
?>
