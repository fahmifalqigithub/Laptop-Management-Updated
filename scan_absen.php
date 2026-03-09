<?php
include 'koneksi.php';

if(isset($_POST['nis'])){

    $nis = $_POST['nis'];
    $tanggal = date("Y-m-d");
    $waktu = date("H:i:s");

    $cek = mysqli_query($conn, "SELECT * FROM students WHERE nis='$nis'");

    if(mysqli_num_rows($cek) > 0){

        mysqli_query($conn, "INSERT INTO attendance (nis, tanggal, waktu, status)
        VALUES ('$nis', '$tanggal', '$waktu', 'Hadir')");

        echo "<h2 style='color:green;'>Absen Berhasil ?</h2>";

    } else {

        echo "<h2 style='color:red;'>NIS Tidak Ditemukan ?</h2>";

    }
}
?>

<form method="POST">
    <input type="text" name="nis" autofocus
    style="font-size:25px; padding:10px;">
</form>