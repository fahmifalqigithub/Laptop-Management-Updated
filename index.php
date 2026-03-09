<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kumpulin laptop kalian woyy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

    <style>
        
    </style>
</head>
<body>
    <div class="container">
        <header class="header-container">
            <div class="d-flex align-items-center justify-content-between">
                <img src="Kemenag.png" alt="Logo Kemenag" class="logo">
                <h1 class="title flex-grow-1 text-center">Pengambilan & Pengumpulan Laptop</h1>
                <img src="IC.png" alt="Logo IC" class="logo">
            </div>
        </header>
        
        <div class="scan-area">
            <i class="fas fa-laptop-code"></i>
            <div>Scan barcode siswa untuk memproses pengambilan atau pengumpulan laptop</div>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="result">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo urldecode($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="?action=ambil" class="btn btn-outline-warning <?php echo (!isset($_GET['action']) || $_GET['action'] == 'ambil') ? 'active' : ''; ?>">
                <i class="fas fa-laptop"></i> Mode Pengambilan
            </a>
            <a href="?action=kumpul" class="btn btn-outline-primary <?php echo (isset($_GET['action']) && $_GET['action'] == 'kumpul') ? 'active' : ''; ?>">
                <i class="fas fa-laptop-house"></i> Mode Pengumpulan
            </a>
            <a href="report.php" class="btn btn-outline-info">
                <i class="fas fa-chart-bar"></i> Lihat Laporan
            </a>
        </div>

        <div class="status-display">
            <p>
                <i class="fas <?php echo isset($_GET['action']) && $_GET['action'] == 'kumpul' ? 'fa-laptop-house' : 'fa-laptop'; ?>"></i>
                Mode Aktif: <strong><?php echo isset($_GET['action']) && $_GET['action'] == 'kumpul' ? 'Pengumpulan Laptop' : 'Pengambilan Laptop'; ?></strong>
            </p>
            <p>
                <i class="fas fa-clock"></i>
                Waktu Sekarang: <strong id="current-time"></strong>
            </p>
        </div>

        <form id="barcode-form" action="proses.php" method="POST">
            <input type="text" name="barcode" id="barcode-input" autofocus autocomplete="off" placeholder="Scan atau ketik NISN siswa di sini...">
            <input type="hidden" name="action" value="<?php echo isset($_GET['action']) && $_GET['action'] == 'kumpul' ? 'kumpul' : 'ambil'; ?>">
        </form>
    </div>

   <footer class="footer">
            <p><i class="fas fa-heart" style="color: var(--danger-color);"></i> © <?php echo date('Y'); ?> MAN INSAN CENDEKIA KOTA BATAM</p>
        </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateTime() {
            const now = new Date();
            const options = { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false
            };
            document.getElementById("current-time").textContent = now.toLocaleTimeString("id-ID", options);
        }
        
        updateTime();
        setInterval(updateTime, 1000);

        document.addEventListener('DOMContentLoaded', function() {
            const barcodeInput = document.getElementById('barcode-input');
            barcodeInput.focus();

            barcodeInput.addEventListener('focus', function() {
                this.style.transform = 'scale(1.02)';
            });
            
            barcodeInput.addEventListener('blur', function() {
                this.style.transform = 'scale(1)';
            });
        });

        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                let ripple = document.createElement('span');
                ripple.classList.add('ripple');
                this.appendChild(ripple);
                
                let x = e.clientX - e.target.offsetLeft;
                let y = e.clientY - e.target.offsetTop;
                
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                
                setTimeout(() => {
                    ripple.remove();
                }, 300);
            });
        });
    </script>
</body>
</html>