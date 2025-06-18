<!DOCTYPE html>
<html lang="<?= session()->get('locale') ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    
    <title><?= $this->renderSection('title') ?> - TapTapWin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom Styles for Mobile-First Design -->
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            overflow-x: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            touch-action: manipulation;
        }
        
        /* Main container - centered like reward system */
        .customer-app {
            max-width: 414px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        
        /* Main content area */
        .main-content {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 80px; /* Space for bottom navigation */
        }
        
        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 414px;
            height: 70px;
            background: white;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }
        
        .nav-item {
            flex: 1;
            text-align: center;
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