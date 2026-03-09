<?php
require_once 'config.php';

function formatDate($date) {
    return date('d F Y', strtotime($date));
}

$requestDate = $_GET['date'] ?? date('Y-m-d');

function getDailyReport($conn, $date) {

    $report = [
        'total_diambil' => 0,
        'total_dikumpulkan' => 0,
        'belum_dikumpulkan' => []
    ];

    $sql_stats = "SELECT 
        SUM(CASE WHEN action = 'ambil' THEN 1 ELSE 0 END) as total_diambil,
        SUM(CASE WHEN action = 'kumpul' THEN 1 ELSE 0 END) as total_dikumpulkan
        FROM laptop_transactions 
        WHERE DATE(transaction_time) = ?";

    $stmt = mysqli_prepare($conn, $sql_stats);
    mysqli_stmt_bind_param($stmt, "s", $date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $report['total_diambil'] = $row['total_diambil'] ?? 0;
        $report['total_dikumpulkan'] = $row['total_dikumpulkan'] ?? 0;
    }

    $sql_not_returned = "SELECT s.nis, s.name, s.class, 
        DATE_FORMAT(ls.take_time, '%Y-%m-%d %H:%i:%s') as take_time
        FROM laptop_status ls
        JOIN students s ON ls.nis = s.nis
        WHERE ls.status = 'diambil' 
        AND DATE(ls.take_time) = ?";

    $stmt2 = mysqli_prepare($conn, $sql_not_returned);
    mysqli_stmt_bind_param($stmt2, "s", $date);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);

    while ($row = mysqli_fetch_assoc($result2)) {
        $report['belum_dikumpulkan'][] = $row;
    }

    return $report;
}

$report = getDailyReport($conn, $requestDate);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Print Laporan</title>
    <link href="report.css" rel="stylesheet">
    <style>
        body {
            background: white;
        }
        .btn {
            display: none;
        }
    </style>
</head>
<body onload="window.print()">

<div class="container">
    <header class="page-header">
        <h2>Laporan Pengambilan & Pengumpulan Laptop</h2>
        <p>Tanggal: <?php echo formatDate($requestDate); ?></p>
    </header>

    <div class="stat-cards">
        <div class="stat-card">
            <div class="stat-value"><?php echo $report['total_diambil']; ?></div>
            <div class="stat-label">Total Diambil</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?php echo $report['total_dikumpulkan']; ?></div>
            <div class="stat-label">Total Dikumpulkan</div>
        </div>
    </div>

    <div class="content-card">
        <h3>Laptop Belum Dikumpulkan</h3>

        <?php if (count($report['belum_dikumpulkan']) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Waktu Ambil</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['belum_dikumpulkan'] as $item): ?>
                        <tr>
                            <td><?php echo $item['nis']; ?></td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $item['class']; ?></td>
                            <td><?php echo $item['take_time']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Tidak ada laptop yang belum dikumpulkan.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>