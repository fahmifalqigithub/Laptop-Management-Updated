<?php
date_default_timezone_set('Asia/Jakarta');
require_once 'config.php';

$today = date("Y-m-d");

// cari semua yang masih mengambil laptop dari hari sebelumnya
$sql = "SELECT * FROM laptop_status 
        WHERE status='diambil' 
        AND DATE(take_time) < CURDATE()";

$result = mysqli_query($conn, $sql);

while($row = mysqli_fetch_assoc($result)){

    $nis = $row['nis'];

    // update status menjadi terlambat
    $sql_update = "UPDATE laptop_status 
                   SET status='dikumpul_terlambat',
                       return_time = CONCAT('$today',' 00:00:00')
                   WHERE nis=?";

    $stmt = mysqli_prepare($conn,$sql_update);
    mysqli_stmt_bind_param($stmt,"s",$nis);
    mysqli_stmt_execute($stmt);

    // masukkan ke log transaksi
    $sql_log = "INSERT INTO laptop_transactions 
                (nis, action, status, transaction_time) 
                VALUES (?, 'kumpul', 'terlambat', CONCAT('$today',' 00:00:00'))";

    $stmt2 = mysqli_prepare($conn,$sql_log);
    mysqli_stmt_bind_param($stmt2,"s",$nis);
    mysqli_stmt_execute($stmt2);
}

?>