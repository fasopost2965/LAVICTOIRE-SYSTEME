<?php
$file = 'domains/groupelavictoire.com/public_html/e-victoire/application/models/User_model.php';
$content = file($file);
$found = false;
foreach($content as $i => $line) {
    if(strpos($line, 'getStudentLoginDetails') !== false) {
        $found = true;
        for($j = $i; $j < $i + 50; $j++) {
            echo $content[$j];
        }
        break;
    }
}
if(!$found) echo "Not found";
?>
