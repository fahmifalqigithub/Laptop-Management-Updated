<?php
date_default_timezone_set('Asia/Jakarta');
require_once 'config.php';

$today = date("Y-m-d");

// Hapus transaksi hari ini
mysqli_query($conn, "
    DELETE FROM laptop_transactions 
    WHERE DATE(transaction_time) = '$today'
");

// Reset status siswa yang ambil hari ini
mysqli_query($conn, "
    DELETE FROM laptop_status 
    WHERE DATE(take_time) = '$today'
");

mysqli_close($conn);

// Redirect
header("Location: index.php?message=" . urlencode("✅ Data hari ini berhasil direset!"));
exit;