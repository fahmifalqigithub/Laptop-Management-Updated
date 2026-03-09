<?php
require 'vendor/autoload.php';
include 'koneksi.php';

use Dompdf\Dompdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

$generator = new BarcodeGeneratorPNG();
$query = mysqli_query($conn, "SELECT * FROM students ORDER BY class, name");

$html = "
<style>
body {
    font-family: Arial, sans-serif;
}

.page {
    page-break-after: always;
}

.card {
    width: 23%;
    height: 140px;
    border: 1px solid #000;
    display: inline-block;
    margin: 5px;
    padding: 5px;
    text-align: center;
    vertical-align: top;
}

.name {
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 5px;
}

img {
    width: 100%;
    height: 60px;
}
</style>
";

$count = 0;

while($row = mysqli_fetch_assoc($query)) {

    if($count % 20 == 0 && $count != 0){
        $html .= "<div class='page'></div>";
    }

    $barcode = base64_encode(
        $generator->getBarcode($row['nis'], $generator::TYPE_CODE_128)
    );

    $html .= "
    <div class='card'>
        <div class='name'>{$row['name']}</div>
        <img src='data:image/png;base64,$barcode'>
    </div>
    ";

    $count++;
}

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("barcode_4x5.pdf", array("Attachment" => true));
exit;