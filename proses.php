<?php
require_once 'config.php';

$barcode = trim($_POST['barcode'] ?? '');
$action = $_POST['action'] ?? 'ambil';
$time = date("Y-m-d H:i:s");
$currentHour = (int)date("H");
$currentMinute = (int)date("i");
$currentTime = ($currentHour * 60) + $currentMinute; 

$minTimeAmbil = 7 * 60; // 7:00 AM
$maxTimeKumpul = (21 * 60) + 15; // 9:15 PM

// Check if student exists
$sql = "SELECT * FROM students WHERE nis = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $barcode);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $student = mysqli_fetch_assoc($result);
    
    // Check current laptop status
    $sql_status = "SELECT * FROM laptop_status WHERE nis = ?";
    $stmt_status = mysqli_prepare($conn, $sql_status);
    mysqli_stmt_bind_param($stmt_status, "s", $barcode);
    mysqli_stmt_execute($stmt_status);
    $result_status = mysqli_stmt_get_result($stmt_status);
    $current_status = mysqli_fetch_assoc($result_status);
    
    if ($action == 'ambil') {
        if ($currentTime == $minTimeAmbil) {
            $message = "❌ <span style='color:red;'>Pengambilan laptop hanya diperbolehkan mulai pukul 07:00!</span>";
        } else {
            if ($current_status && $current_status['status'] == 'diambil') {
                $message = "❌ <span style='color:red;'>Anda sudah mengambil laptop!</span>";
            } else {
                // Process laptop pickup
                if ($current_status) {
                    // Update existing record
                    $sql_update = "UPDATE laptop_status SET status = 'diambil', take_time = ?, return_time = NULL WHERE nis = ?";
                    $stmt_update = mysqli_prepare($conn, $sql_update);
                    mysqli_stmt_bind_param($stmt_update, "ss", $time, $barcode);
                    mysqli_stmt_execute($stmt_update);
                } else {
                    // Insert new record
                    $sql_insert = "INSERT INTO laptop_status (nis, status, take_time) VALUES (?, 'diambil', ?)";
                    $stmt_insert = mysqli_prepare($conn, $sql_insert);
                    mysqli_stmt_bind_param($stmt_insert, "ss", $barcode, $time);
                    mysqli_stmt_execute($stmt_insert);
                }

                // Log transaction
                $sql_log = "INSERT INTO laptop_transactions (nis, action, transaction_time) VALUES (?, 'ambil', ?)";
                $stmt_log = mysqli_prepare($conn, $sql_log);
                mysqli_stmt_bind_param($stmt_log, "ss", $barcode, $time);
                mysqli_stmt_execute($stmt_log);
                
                $message = "✅ <strong>Pengambilan Laptop Berhasil</strong><br>" .
                           "<strong>NIS:</strong> {$barcode}<br>" .
                           "<strong>Nama:</strong> {$student['name']}<br>" .
                           "<strong>Kelas:</strong> {$student['class']}<br>" .
                           "<strong>Waktu Ambil:</strong> {$time}";
            }
        }
    } 
    else if ($action == 'kumpul') {
        if (!$current_status || $current_status['status'] != 'diambil') {
            $message = "❌ <span style='color:red;'>Anda belum mengambil laptop!</span>";
        } else {
            $isLate = $currentTime > $maxTimeKumpul;
            $status = $isLate ? 'dikumpul_terlambat' : 'dikumpul';
            $trans_status = $isLate ? 'terlambat' : 'normal';
            
            // Update laptop status
            $sql_update = "UPDATE laptop_status SET status = ?, return_time = ? WHERE nis = ?";
            $stmt_update = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "sss", $status, $time, $barcode);
            mysqli_stmt_execute($stmt_update);

            // Log transaction
            $sql_log = "INSERT INTO laptop_transactions (nis, action, status, transaction_time) VALUES (?, 'kumpul', ?, ?)";
            $stmt_log = mysqli_prepare($conn, $sql_log);
            mysqli_stmt_bind_param($stmt_log, "sss", $barcode, $trans_status, $time);
            mysqli_stmt_execute($stmt_log);
            
            $message = "✅ <strong>Pengumpulan Laptop Berhasil</strong><br>" .
                       "<strong>NIS:</strong> {$barcode}<br>" .
                       "<strong>Nama:</strong> {$student['name']}<br>" .
                       "<strong>Kelas:</strong> {$student['class']}<br>" .
                       "<strong>Waktu Kumpul:</strong> {$time}";
            
            if ($isLate) {
                $message .= "<br><span style='color:orange;'><strong>Peringatan:</strong> Pengumpulan terlambat (batas waktu 21:15)</span>";
            }
        }
    }
} else {
    $message = "❌ <span style='color:red;'>NIS tidak dikenal!</span>";
}

mysqli_close($conn);
header("Location: index.php?action={$action}&message=" . urlencode($message));
exit;
