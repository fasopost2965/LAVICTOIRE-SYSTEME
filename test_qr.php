<?php
require_once 'vendor/autoload.php';
use chillerlan\QRCode\{QRCode, QROptions};

$options = new QROptions([
    'version'      => 5,
    'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel'     => QRCode::ECC_L,
    'scale'        => 10,
    'imageBase64'  => false,
    'bgColor'      => [255, 255, 255],
    'imageTransparency' => false,
]);

$qr_dir = 'uploads/student_id_card/qrcode/';
if(!is_dir($qr_dir)) mkdir($qr_dir, 0777, true);

(new QRCode($options))->render('TEST', $qr_dir . 'test.png');
echo "QR OK\n";
?>
