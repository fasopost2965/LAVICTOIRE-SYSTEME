<?php
/**
 * Final Asset Generation for CM2 Migrated Students
 */

set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$root = 'domains/groupelavictoire.com/public_html/e-victoire/';
require_once $root . 'application/third_party/omnipay/vendor/autoload.php';

use chillerlan\QRCode\{QRCode, QROptions};

// Define Paths for Zend
if(!defined('APPPATH')) define('APPPATH', $root . 'application/');
set_include_path(get_include_path() . PATH_SEPARATOR . APPPATH . 'libraries');

require_once APPPATH . 'libraries/Zend/Barcode.php';

// DB Connection
$conn = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');

// Fetch students with IDs 136 to 148
$res = $conn->query("SELECT id, admission_no FROM students WHERE id >= 136");

$qr_dir = $root . 'uploads/student_id_card/qrcode/';
$bar_dir = $root . 'uploads/student_id_card/barcodes/';

if(!is_dir($qr_dir)) mkdir($qr_dir, 0777, true);
if(!is_dir($bar_dir)) mkdir($bar_dir, 0777, true);

// QR Options
$options = new QROptions([
    'version'      => 5,
    'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel'     => QRCode::ECC_L,
    'scale'        => 10,
    'imageBase64'  => false,
    'bgColor'      => [255, 255, 255],
    'imageTransparency' => false,
]);

echo "Generating assets for " . $res->num_rows . " students...\n";

while($row = $res->fetch_assoc()){
    $id = $row['id'];
    $adm = $row['admission_no'];
    
    // QR Code
    $qr_file = $qr_dir . $id . '.png';
    (new QRCode($options))->render($adm, $qr_file);
    
    // Barcode
    $bar_file = $bar_dir . $id . '.png';
    try {
        $rendererOptions = ['imageType' => 'png'];
        $barcodeOptions = ['text' => $adm, 'barHeight' => 50, 'factor' => 2];
        $imageResource = Zend_Barcode::draw('code128', 'image', $barcodeOptions, $rendererOptions);
        imagepng($imageResource, $bar_file);
        imagedestroy($imageResource);
    } catch (Exception $e) {
        echo "Error generating barcode for $id: " . $e->getMessage() . "\n";
    }
    
    echo "Generated assets for $id ($adm)\n";
}

$conn->close();
echo "Done.\n";
?>
