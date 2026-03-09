<?php
require_once 'config.php';

function formatDate($date) {
    $timestamp = strtotime($date);
    return date('d F Y', $timestamp);
}

$requestDate = $_GET['date'] ?? date('Y-m-d');

function getDailyReport($conn, $date) {
    $report = [
        'total_diambil' => 0,
        'total_dikumpulkan' => 0, 
        'total_terlambat' => 0,
        'belum_dikumpulkan' => [],
        'detail' => []
    ];

    $sql_stats = "SELECT 
                    SUM(CASE WHEN action = 'ambil' THEN 1 ELSE 0 END) as total_diambil,
                    SUM(CASE WHEN action = 'kumpul' THEN 1 ELSE 0 END) as total_dikumpulkan,
                    SUM(CASE WHEN action = 'kumpul' AND status = 'terlambat' THEN 1 ELSE 0 END) as total_terlambat
                FROM laptop_transactions 
                WHERE DATE(transaction_time) = ?";
    
    $stmt_stats = mysqli_prepare($conn, $sql_stats);
    mysqli_stmt_bind_param($stmt_stats, "s", $date);
    mysqli_stmt_execute($stmt_stats);
    $result_stats = mysqli_stmt_get_result($stmt_stats);
    
    if ($row_stats = mysqli_fetch_assoc($result_stats)) {
        $report['total_diambil'] = $row_stats['total_diambil'] ?? 0;
        $report['total_dikumpulkan'] = $row_stats['total_dikumpulkan'] ?? 0;
        $report['total_terlambat'] = $row_stats['total_terlambat'] ?? 0;
    }

    $sql_not_returned = "SELECT s.nis, s.name, s.class, 
                        DATE_FORMAT(ls.take_time, '%Y-%m-%d %H:%i:%s') as take_time
                        FROM laptop_status ls
                        JOIN students s ON ls.nis = s.nis
                        WHERE ls.status = 'diambil' 
                        AND DATE(ls.take_time) = ?";
    
    $stmt_not_returned = mysqli_prepare($conn, $sql_not_returned);
    mysqli_stmt_bind_param($stmt_not_returned, "s", $date);
    mysqli_stmt_execute($stmt_not_returned);
    $result_not_returned = mysqli_stmt_get_result($stmt_not_returned);
    
    while ($row = mysqli_fetch_assoc($result_not_returned)) {
        $report['belum_dikumpulkan'][] = $row;
    }

    $sql_details = "SELECT 
                    DATE_FORMAT(lt.transaction_time, '%Y-%m-%d %H:%i:%s') as time,
                    lt.action,
                    lt.status,
                    lt.nis,
                    s.name,
                    s.class
                  FROM laptop_transactions lt
                  JOIN students s ON lt.nis = s.nis
                  WHERE DATE(lt.transaction_time) = ?
                  ORDER BY lt.transaction_time DESC";
    
    $stmt_details = mysqli_prepare($conn, $sql_details);
    mysqli_stmt_bind_param($stmt_details, "s", $date);
    mysqli_stmt_execute($stmt_details);
    $result_details = mysqli_stmt_get_result($stmt_details);
    
    while ($row = mysqli_fetch_assoc($result_details)) {
        // Format action and status
        $action_display = $row['action'] == 'ambil' ? 'Ambil' : 'Kumpul';
        if ($row['action'] == 'kumpul' && $row['status'] == 'terlambat') {
            $action_display .= ' (Terlambat)';
        }
        
        $report['detail'][] = [
            'time' => $row['time'],
            'action' => $action_display,
            'nis' => $row['nis'],
            'name' => $row['name'],
            'class' => $row['class']
        ];
    }
    
    return $report;
}

$report = getDailyReport($conn, $requestDate);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengambilan & Pengumpulan Laptop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="report.css">
    <style>
       
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <header class="page-header loading">
            <h1><i class="fas fa-laptop-code me-3"></i>Laporan Pengambilan & Pengumpulan Laptop</h1>
            <p>Statistik dan aktivitas untuk tanggal: <?php echo formatDate($requestDate); ?></p>
            
            <div class="date-selector d-flex justify-content-between align-items-center">
                <form action="" method="GET" class="d-flex gap-3 align-items-center flex-grow-1">
                    <div class="d-flex align-items-center flex-grow-1">
                        <label for="date"><i class="fas fa-calendar-alt me-2"></i> Pilih Tanggal:</label>
                        <input type="date" id="date" name="date" value="<?php echo $requestDate; ?>" class="form-control ms-3">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i> Tampilkan
                    </button>
                </form>
                
                <a href="index.php" class="btn btn-secondary ms-3">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </header>
        

        <div class="stat-cards">
            <div class="stat-card loading">
                <div class="icon">
                    <i class="fas fa-laptop"></i>
                </div>
                <div class="stat-value"><?php echo $report['total_diambil']; ?></div>
                <div class="stat-label">Total Diambil</div>
            </div>
            <div class="stat-card loading">
                <div class="icon">
                    <i class="fas fa-laptop-house"></i>
                </div>
                <div class="stat-value"><?php echo $report['total_dikumpulkan']; ?></div>
                <div class="stat-label">Total Dikumpulkan</div>
            </div>
            <div class="stat-card loading">
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $report['total_terlambat']; ?></div>
                <div class="stat-label">Terlambat Mengumpulkan</div>
            </div>
        </div>
        
        <div class="content-card loading">
            <h3><i class="fas fa-exclamation-triangle"></i> Laptop Belum Dikumpulkan</h3>
            <?php if (count($report['belum_dikumpulkan']) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-id-card me-2"></i>NISN</th>
                                <th><i class="fas fa-user me-2"></i>Nama</th>
                                <th><i class="fas fa-school me-2"></i>Kelas</th>
                                <th><i class="fas fa-clock me-2"></i>Waktu Ambil</th>
                                <th><i class="fas fa-flag me-2"></i>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report['belum_dikumpulkan'] as $item): ?>
                                <tr>
                                    <td><strong><?php echo $item['nis']; ?></strong></td>
                                    <td><?php echo $item['name']; ?></td>
                                    <td><?php echo $item['class']; ?></td>
                                    <td><?php echo $item['take_time']; ?></td>
                                    <td><span class="badge badge-warning"><i class="fas fa-hourglass-half me-1"></i>Belum Dikumpulkan</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success-color); margin-bottom: 1rem;"></i>
                    <p>Tidak ada laptop yang belum dikumpulkan pada tanggal ini.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Detail Aktivitas -->
        <div class="content-card loading">
            <h3><i class="fas fa-clipboard-list"></i> Detail Aktivitas</h3>
            <?php if (count($report['detail']) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-clock me-2"></i>Waktu</th>
                                <th><i class="fas fa-tasks me-2"></i>Aktivitas</th>
                                <th><i class="fas fa-id-card me-2"></i>NISN</th>
                                <th><i class="fas fa-user me-2"></i>Nama</th>
                                <th><i class="fas fa-school me-2"></i>Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report['detail'] as $item): ?>
                                <tr>
                                    <td><strong><?php echo $item['time']; ?></strong></td>
                                    <td>
                                        <?php if (strpos($item['action'], 'Ambil') !== false): ?>
                                            <span class="badge bg-primary"><i class="fas fa-arrow-right me-1"></i> <?php echo $item['action']; ?></span>
                                        <?php elseif (strpos($item['action'], 'Terlambat') !== false): ?>
                                            <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> <?php echo $item['action']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i> <?php echo $item['action']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo $item['nis']; ?></strong></td>
                                    <td><?php echo $item['name']; ?></td>
                                    <td><?php echo $item['class']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <p>Tidak ada aktivitas pada tanggal ini.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <footer class="footer">
            <p><i class="fas fa-heart" style="color: var(--danger-color);"></i> © <?php echo date('Y'); ?> MAN INSAN CENDEKIA KOTA BATAM</p>
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add smooth animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading class to all elements that should animate
            const animatedElements = document.querySelectorAll('.stat-card, .content-card, .page-header');
            
            // Remove loading class with staggered delay
            animatedElements.forEach((element, index) => {
                setTimeout(() => {
                    element.classList.add('loading');
                }, index * 100);
            });
            
            // Add hover effects to table rows
            const tableRows = document.querySelectorAll('.table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                    this.style.boxShadow = '0 4px 15px rgba(135, 206, 235, 0.3)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                    this.style.boxShadow = 'none';
                });
            });
            
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Auto-refresh data every 30 seconds (optional)
            // setInterval(function() {
            //     if (document.visibilityState === 'visible') {
            //         location.reload();
            //     }
            // }, 30000);
        });
        
        // Add ripple effect styles
        const style = document.createElement('style');
        style.textContent = `
            .btn {
                position: relative;
                overflow: hidden;
            }
            
            .btn .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            /* Additional micro-animations */
            .stat-card .icon {
                transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            }
            
            .content-card {
                transition: all 0.3s ease;
            }
            
            .content-card:hover {
                transform: translateY(-2px);
            }
            
            /* Smooth scroll behavior */
            html {
                scroll-behavior: smooth;
            }
            
            /* Custom scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
            }
            
            ::-webkit-scrollbar-track {
                background: var(--light-blue);
                border-radius: 4px;
            }
            
            ::-webkit-scrollbar-thumb {
                background: var(--primary-sky);
                border-radius: 4px;
                transition: background 0.3s ease;
            }
            
            ::-webkit-scrollbar-thumb:hover {
                background: var(--primary-sky-dark);
            }
            
            /* Focus styles for accessibility */
            input:focus,
            button:focus {
                outline: 2px solid var(--accent-sky);
                outline-offset: 2px;
            }
            
            /* Print styles */
            @media print {
                body {
                    background: white !important;
                    color: black !important;
                }
                
                .page-header {
                    background: var(--primary-sky-light) !important;
                    color: black !important;
                }
                
                .stat-card,
                .content-card {
                    background: white !important;
                    border: 1px solid #ddd !important;
                    box-shadow: none !important;
                }
                
                .btn {
                    display: none !important;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
    
    <?php mysqli_close($conn); ?>

    <hr class="mt-4">

    <div class="d-flex justify-content-end">
        <a href="print_report.php?date=<?php echo $requestDate; ?>" 
           target="_blank"
           class="btn btn-success">
            <i class="fas fa-print me-2"></i> Print Laporan
        </a>
    </div>
</body>
</html>