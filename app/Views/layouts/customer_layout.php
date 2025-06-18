<!DOCTYPE html>
<html lang="<?= session()->get('locale') ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1">
    <meta name="format-detection" content="telephone=no">
    
    <title><?= $this->renderSection('title') ?> - TapTapWin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= base_url('css/user-dashboard.css') ?>" rel="stylesheet">
    
    <!-- Prevent zoom -->
    <style>
        html, body {
            touch-action: manipulation;
            -webkit-user-select: none;
            -webkit-touch-callout: none;
            -webkit-tap-highlight-color: transparent;
        }
        
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
    </style>
</head>
<body class="user-body">
    
    <div class="portrait-container">
        <!-- Main Content -->
        <main class="main-content">
            <?= $this->renderSection('content') ?>
        </main>
        
        <!-- Bottom Navigation -->
        <nav class="bottom-nav">
            <div class="nav-item <?= uri_string() == 'user/dashboard' ? 'active' : '' ?>">
                <a href="<?= base_url('user/dashboard') ?>" class="nav-link">
                    <i class="bi bi-house"></i>
                    <span>Home</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="openWheelModal()">
                    <i class="bi bi-pie-chart"></i>
                    <span>Wheel</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-gift"></i>
                    <span>Rewards</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= base_url('logout') ?>" class="nav-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <?= $this->renderSection('scripts') ?>
    
</body>
</html>