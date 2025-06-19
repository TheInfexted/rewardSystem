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
        
        .nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #6c757d;
            padding: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover,
        .nav-link:focus {
            color: #667eea;
            text-decoration: none;
        }
        
        .nav-item.active .nav-link {
            color: #667eea;
        }
        
        .nav-link i {
            font-size: 1.2rem;
            margin-bottom: 4px;
        }
        
        .nav-link span {
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        /* Responsive adjustments */
        @media (max-width: 414px) {
            .customer-app {
                max-width: 100%;
                box-shadow: none;
            }
            
            .bottom-nav {
                left: 0;
                transform: none;
                width: 100%;
            }
        }
        
        /* Prevent zoom on input focus */
        input, textarea, select {
            font-size: 16px !important;
        }
        
        /* Disable text selection and zoom */
        body {
            -webkit-user-select: none;
            -webkit-touch-callout: none;
            -webkit-tap-highlight-color: transparent;
            -ms-touch-action: manipulation;
            touch-action: manipulation;
        }
        
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        
        /* Loading spinner */
        .loading-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        
        /* Custom scrollbar */
        .main-content::-webkit-scrollbar {
            width: 4px;
        }
        
        .main-content::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .main-content::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <div class="customer-app">
        <!-- Main Content -->
        <main class="main-content">
            <?= $this->renderSection('content') ?>
        </main>
        
        <!-- Bottom Navigation -->
        <nav class="bottom-nav">
            <div class="nav-item <?= uri_string() == 'customer/dashboard' ? 'active' : '' ?>">
                <a href="<?= base_url('customer/dashboard') ?>" class="nav-link">
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
                <a href="#" class="nav-link" onclick="showRewards()">
                    <i class="bi bi-gift"></i>
                    <span>Rewards</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="showProfile()">
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
    
    <!-- Global Navigation Functions -->
    <script>
        function openWheelModal() {
            window.location.href = '<?= base_url('/') ?>';
        }
        
        function showRewards() {
            showToast('Rewards feature coming soon!', 'info');
        }
        
        function showProfile() {
            showToast('Profile feature coming soon!', 'info');
        }
        
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
            toast.style.cssText = `
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 9999;
                min-width: 250px;
                text-align: center;
                max-width: 90%;
            `;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
        
        // Prevent double-tap zoom
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            let now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
        
        // Handle active navigation state
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navItems = document.querySelectorAll('.nav-item');
            
            navItems.forEach(item => {
                const link = item.querySelector('a');
                if (link && link.getAttribute('href') === currentPath) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        });
    </script>
    
    <!-- Custom Scripts -->
    <?= $this->renderSection('scripts') ?>
    
</body>
</html>