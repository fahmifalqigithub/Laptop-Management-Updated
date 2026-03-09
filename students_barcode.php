<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';
include 'koneksi.php';

require 'vendor/autoload.php';
include 'koneksi.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

$generator = new BarcodeGeneratorPNG();

$query = mysqli_query($conn, "SELECT * FROM students ORDER BY class ASC, name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Barcode Siswa</title>
    <style>
        body {
            font-family: Arial;
        }
        .card {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px;
            width: 250px;
            display: inline-block;
            text-align: center;
        }
    </style>
</head>
<body>

<h2>Daftar Barcode Siswa</h2>

<?php while($row = mysqli_fetch_assoc($query)) : ?>
    <div class="card">
        <strong><?= $row['name']; ?></strong><br>
        NIS: <?= $row['nis']; ?><br>
        Kelas: <?= $row['class']; ?><br><br>

        <img src="data:image/png;base64,<?= base64_encode(
            $generator->getBarcode($row['nis'], $generator::TYPE_CODE_128)
        ); ?>">
    </div>
<?php endwhile; ?>
<br><br>
<a href="download_barcode.php?nis=<?= $row['nis']; ?>">
    <button>Download Barcode</button>
</a>
</body>
</html>