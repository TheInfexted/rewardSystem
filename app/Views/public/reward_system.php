<?php 
// Check if winner_data exists and is valid
$hasValidPrize = isset($winner_data) && is_array($winner_data) && isset($winner_data['name']);
$isLoggedIn = isset($logged_in) && $logged_in;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $title ?? 'Claim Your Reward' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Force portrait mode styling - same as landing page */
        * {
            touch-action: manipulation;
        }
        
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            background-color: #000;
            touch-action: pan-x pan-y;
        }
        
        /* Prevent zooming on input focus (iOS) */
        input, textarea, select {
            font-size: 16px !important;
        }
        
        /* Additional zoom prevention */
        body {
            -ms-touch-action: manipulation;
            touch-action: manipulation;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            overflow-x: hidden;
        }
        
        /* Portrait container - same as landing page */
        .portrait-container {
            width: 100%;
            max-width: 414px;
            background-color: #000;
            min-height: 100vh;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        /* Header Section */
        .reward-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 20px 15px;
            text-align: center;
            border-bottom: 3px solid #ffd700;
            flex-shrink: 0;
        }
        
        .reward-header h1 {
            color: #ffd700;
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from {
                text-shadow: 0 0 10px #fff, 0 0 20px #fff, 0 0 30px #ffd700, 0 0 40px #ffd700;
            }
            to {
                text-shadow: 0 0 20px #fff, 0 0 30px #ffd700, 0 0 40px #ffd700, 0 0 50px #ffd700;
            }
        }
        
        /* Main Content Area */
        .reward-content {
            flex: 1;
            padding: 20px 15px;
            background: url('<?= base_url("img/fortune_wheel/bg_spin.png") ?>') no-repeat center center;
            background-size: cover;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        /* Reward Card */
        .reward-card {
            background: rgba(0, 0, 0, 0.9);
            border: 2px solid #ffd700;
            border-radius: 15px;
            padding: 25px 20px;
            width: 100%;
            max-width: 350px;
            box-shadow: 0 10px 40px rgba(255, 215, 0, 0.3);
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Prize Display */
        .prize-display {
            background: linear-gradient(135deg, #ffd700, #ffb347);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .prize-display::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .prize-text {
            color: #000;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        
        .prize-amount {
            color: #000;
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0 0 0;
            position: relative;
            z-index: 1;
        }
        
        /* Customer Welcome */
        .customer-welcome {
            background: rgba(255, 215, 0, 0.1);
            border: 2px solid #ffd700;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .customer-info {
            color: #ffd700;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        .points-display {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 15px;
            display: inline-block;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        /* Buttons */
        .btn-claim {
            background: linear-gradient(135deg, #ffd700 0%, #ffb347 100%);
            border: 2px solid #b8860b;
            color: #000;
            font-weight: bold;
            font-size: 1.1rem;
            padding: 15px 25px;
            border-radius: 8px;
            width: 100%;
            margin: 10px 0;
            transition: all 0.3s ease;
        }
        
        .btn-claim:hover,
        .btn-claim:focus {
            background: linear-gradient(135deg, #ffb347 0%, #ffd700 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 215, 0, 0.5);
            color: #000;
        }
        
        .btn-platform {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid #444;
            color: #fff;
            font-weight: bold;
            padding: 15px;
            border-radius: 8px;
            width: 100%;
            margin: 8px 0;
            transition: all 0.3s ease;
        }
        
        .btn-platform:hover {
            border-color: #ffd700;
            background: rgba(255, 215, 0, 0.1);
            color: #ffd700;
            transform: translateY(-2px);
        }
        
        .btn-whatsapp:hover {
            border-color: #25D366;
            background: rgba(37, 211, 102, 0.1);
            color: #25D366;
        }
        
        .btn-telegram:hover {
            border-color: #0088cc;
            background: rgba(0, 136, 204, 0.1);
            color: #0088cc;
        }
        
        /* Form Elements */
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #444;
            border-radius: 8px;
            color: #fff;
            padding: 12px 15px;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #ffd700;
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
            color: #fff;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Credentials Display */
        .credentials-box {
            background: rgba(255, 215, 0, 0.1);
            border: 2px dashed #ffd700;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .credential-item {
            margin: 10px 0;
            padding: 10px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 5px;
            font-family: monospace;
            font-size: 1.1rem;
            color: #ffd700;
            word-break: break-all;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .credential-item:hover {
            background: rgba(255, 215, 0, 0.1);
        }
        
        .credential-label {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        /* Platform Selection */
        .platform-selection {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
        
        .platform-btn {
            flex: 1;
            padding: 20px 10px;
            border: 2px solid #444;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            color: #fff;
        }
        
        .platform-btn:hover {
            border-color: #ffd700;
            background: rgba(255, 215, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .platform-btn i {
            font-size: 2.5rem;
            margin-bottom: 8px;
            display: block;
        }
        
        .platform-btn.whatsapp:hover {
            border-color: #25D366;
            background: rgba(37, 211, 102, 0.1);
            color: #25D366;
        }
        
        .platform-btn.telegram:hover {
            border-color: #0088cc;
            background: rgba(0, 136, 204, 0.1);
            color: #0088cc;
        }
        
        /* Toggle Links */
        .toggle-link {
            text-align: center;
            margin: 15px 0;
        }
        
        .toggle-link a {
            color: #ffd700;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .toggle-link a:hover {
            color: #ffb347;
            text-decoration: underline;
        }
        
        /* Loading Spinner */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
        }
        
        .loading-spinner.active {
            display: block;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-color: #ffd700;
            border-right-color: transparent;
        }
        
        /* Alerts */
        .alert {
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #28a745;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #dc3545;
        }
        
        .alert-info {
            background: rgba(23, 162, 184, 0.2);
            border: 1px solid #17a2b8;
            color: #17a2b8;
        }
        
        /* Important Note */
        .important-note {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            color: #ff6b6b;
            font-size: 0.9rem;
            text-align: center;
        }
        
        .important-note i {
            color: #ff4444;
        }
        
        /* No Prize Message */
        .no-prize-message {
            text-align: center;
            color: #fff;
            padding: 40px 20px;
        }
        
        .no-prize-message h3 {
            color: #ffd700;
            margin-bottom: 20px;
        }
        
        /* Footer */
        .reward-footer {
            background: #1a1a1a;
            padding: 15px;
            text-align: center;
            border-top: 1px solid #333;
            flex-shrink: 0;
        }
        
        .reward-footer p {
            margin: 0;
            color: #888;
            font-size: 0.9rem;
        }
        
        /* Mobile Responsive */
        @media (max-width: 375px) {
            .reward-card {
                padding: 20px 15px;
                margin: 0 10px;
            }
            
            .reward-header h1 {
                font-size: 1.5rem;
            }
            
            .prize-text {
                font-size: 1.3rem;
            }
            
            .prize-amount {
                font-size: 1.8rem;
            }
            
            .platform-btn {
                padding: 15px 8px;
            }
            
            .platform-btn i {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- CSRF Token for AJAX requests -->
    <script>
        const csrfToken = '<?= csrf_hash() ?>';
        const csrfName = '<?= csrf_token() ?>';
    </script>
    
    <div class="portrait-container">
        <!-- Header -->
        <header class="reward-header">
            <h1><i class="bi bi-gift-fill"></i> Claim Your Reward</h1>
        </header>
        
        <!-- Main Content -->
        <main class="reward-content">
            <!-- Alert Messages -->
            <div id="alertContainer"></div>
            
            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="loading-spinner">
                <div class="text-center text-warning">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">Processing...</p>
                </div>
            </div>
            
            <div class="reward-card">
                <?php if ($hasValidPrize): ?>
                    <!-- Prize Display -->
                    <div class="prize-display">
                        <p class="prize-text">🎉 Congratulations! You Won:</p>
                        <p class="prize-amount"><?= esc($winner_data['name']) ?></p>
                        <?php if (isset($winner_data['prize']) && $winner_data['prize'] > 0): ?>
                            <p class="prize-text">Value: $<?= number_format($winner_data['prize']) ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if ($isLoggedIn): ?>
                        <!-- Logged In User - Ready to Claim -->
                        <div class="customer-welcome">
                            <div class="customer-info">
                                <i class="bi bi-person-check-fill"></i> 
                                Welcome back, <?= esc($customer_data['username'] ?? 'Customer') ?>!
                            </div>
                            <?php if (isset($customer_data['points'])): ?>
                                <div class="points-display">
                                    <i class="bi bi-star-fill"></i> Points: <?= number_format($customer_data['points']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="claimStep">
                            <h5 class="text-center text-warning mb-3">Choose Platform to Contact:</h5>
                            <div class="platform-selection">
                                <div class="platform-btn whatsapp" onclick="claimReward('whatsapp')">
                                    <i class="bi bi-whatsapp"></i>
                                    <span>WhatsApp</span>
                                </div>
                                <div class="platform-btn telegram" onclick="claimReward('telegram')">
                                    <i class="bi bi-telegram"></i>
                                    <span>Telegram</span>
                                </div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="<?= base_url('customer/dashboard') ?>" class="btn btn-platform btn-sm me-2">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                                <button type="button" class="btn btn-platform btn-sm" onclick="logout()">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Not Logged In - Show Login/Register Options -->
                        <div id="authContainer">
                            <!-- Login Form (Initially Hidden) -->
                            <div id="loginForm" style="display: none;">
                                <h5 class="text-center text-warning mb-3">Login to Your Account</h5>
                                <form id="customerLoginForm">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" id="loginUsername" name="username" 
                                               placeholder="Enter your username" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="password" class="form-control" id="loginPassword" name="password" 
                                               placeholder="Enter your password" required>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-claim">
                                            <i class="bi bi-box-arrow-in-right"></i> Login
                                        </button>
                                        <button type="button" class="btn btn-platform" onclick="showRegistration()">
                                            <i class="bi bi-arrow-left"></i> Back to Registration
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Registration Options (Initially Visible) -->
                            <div id="registrationOptions">
                                <h5 class="text-center text-warning mb-3">Create Account to Claim</h5>
                                <p class="text-center text-light mb-4">We'll create an instant account for you!</p>
                                
                                <button type="button" class="btn btn-claim" onclick="autoRegister()">
                                    <i class="bi bi-lightning-fill"></i> Create Instant Account
                                </button>

                                <div class="toggle-link">
                                    <a href="#" onclick="showLogin()">
                                        Already have an account? Login here
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Account Created Step (Hidden Initially) -->
                            <div id="accountCreatedStep" style="display: none;">
                                <h5 class="text-center text-success mb-3">
                                    <i class="bi bi-check-circle-fill"></i> Account Created!
                                </h5>
                                
                                <div class="credentials-box">
                                    <h6 class="text-warning mb-3">Save Your Login Details:</h6>
                                    <div class="credential-item">
                                        <div class="credential-label">Username:</div>
                                        <div id="displayUsername">-</div>
                                    </div>
                                    <div class="credential-item">
                                        <div class="credential-label">Password:</div>
                                        <div id="displayPassword">-</div>
                                    </div>
                                </div>
                                
                                <div class="important-note">
                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                    Important: Save these credentials to access your account later!
                                </div>

                                <h5 class="text-center text-warning mb-3">Choose Platform to Contact:</h5>
                                <div class="platform-selection">
                                    <div class="platform-btn whatsapp" onclick="claimReward('whatsapp')">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>WhatsApp</span>
                                    </div>
                                    <div class="platform-btn telegram" onclick="claimReward('telegram')">
                                        <i class="bi bi-telegram"></i>
                                        <span>Telegram</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php elseif ($isLoggedIn): ?>
                    <!-- Logged In User - No Prize but Show Dashboard Access -->
                    <div class="customer-welcome">
                        <div class="customer-info">
                            <i class="bi bi-person-check-fill"></i> 
                            Welcome back, <?= esc($customer_data['username'] ?? 'Customer') ?>!
                        </div>
                        <?php if (isset($customer_data['points'])): ?>
                            <div class="points-display">
                                <i class="bi bi-star-fill"></i> Points: <?= number_format($customer_data['points']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="no-prize-message">
                        <h3><i class="bi bi-info-circle"></i> No Active Rewards</h3>
                        <p>You don't have any pending rewards to claim right now.</p>
                        <p class="text-muted">Spin the wheel to win exciting prizes!</p>
                        
                        <div class="d-grid gap-2 mt-4">
                            <a href="<?= base_url('/') ?>" class="btn btn-claim">
                                <i class="bi bi-arrow-left"></i> Back to Game
                            </a>
                            <a href="<?= base_url('customer/dashboard') ?>" class="btn btn-platform">
                                <i class="bi bi-speedometer2"></i> View Dashboard
                            </a>
                            <button type="button" class="btn btn-platform" onclick="logout()">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Not Logged In - Show Login Options -->
                    <div id="authContainer">
                        <!-- Login Form (Initially Hidden) -->
                        <div id="loginForm" style="display: none;">
                            <h5 class="text-center text-warning mb-3">Login to Your Account</h5>
                            <form id="customerLoginForm">
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="loginUsername" name="username" 
                                           placeholder="Enter your username" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control" id="loginPassword" name="password" 
                                           placeholder="Enter your password" required>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-claim">
                                        <i class="bi bi-box-arrow-in-right"></i> Login
                                    </button>
                                    <button type="button" class="btn btn-platform" onclick="showRegistration()">
                                        <i class="bi bi-arrow-left"></i> Back
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Default Options -->
                        <div id="registrationOptions">
                            <div class="no-prize-message">
                                <h3><i class="bi bi-person-circle"></i> Account Access</h3>
                                <p>Login to access your dashboard or create a new account.</p>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <button type="button" class="btn btn-claim" onclick="showLogin()">
                                        <i class="bi bi-box-arrow-in-right"></i> Login to Account
                                    </button>
                                    <button type="button" class="btn btn-platform" onclick="autoRegister()">
                                        <i class="bi bi-person-plus-fill"></i> Create New Account
                                    </button>
                                    <a href="<?= base_url('/') ?>" class="btn btn-platform">
                                        <i class="bi bi-arrow-left"></i> Back to Game
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Created Step (Hidden Initially) -->
                        <div id="accountCreatedStep" style="display: none;">
                            <h5 class="text-center text-success mb-3">
                                <i class="bi bi-check-circle-fill"></i> Account Created!
                            </h5>
                            
                            <div class="credentials-box">
                                <h6 class="text-warning mb-3">Save Your Login Details:</h6>
                                <div class="credential-item">
                                    <div class="credential-label">Username:</div>
                                    <div id="displayUsername">-</div>
                                </div>
                                <div class="credential-item">
                                    <div class="credential-label">Password:</div>
                                    <div id="displayPassword">-</div>
                                </div>
                            </div>
                            
                            <div class="important-note">
                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                Important: Save these credentials to access your account later!
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                <a href="<?= base_url('customer/dashboard') ?>" class="btn btn-claim">
                                    <i class="bi bi-speedometer2"></i> Go to Dashboard
                                </a>
                                <a href="<?= base_url('/') ?>" class="btn btn-platform">
                                    <i class="bi bi-arrow-left"></i> Back to Game
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="reward-footer">
            <p>&copy; 2025 TapTapWin. All rights reserved.</p>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Show alert message
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alertDiv);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Show loading spinner
        function showLoading(show = true) {
            const spinner = document.getElementById('loadingSpinner');
            spinner.classList.toggle('active', show);
        }

        // Toggle between login and registration
        function showLogin() {
            document.getElementById('registrationOptions').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
        }

        function showRegistration() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registrationOptions').style.display = 'block';
        }

        // Customer login
        document.getElementById('customerLoginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append(csrfName, csrfToken);
            showLoading(true);
            
            fetch('<?= base_url('reward/login') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showLoading(false);
                showAlert('Login failed. Please try again.', 'danger');
                console.error('Error:', error);
            });
        });

        // Auto register function
        function autoRegister() {
            showLoading(true);
            
            // Create FormData to handle CSRF properly
            const formData = new FormData();
            formData.append(csrfName, csrfToken);
            
            fetch('<?= base_url('reward/auto-register') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                showLoading(false);
                
                if (data.success) {
                    // Hide registration options
                    document.getElementById('registrationOptions').style.display = 'none';
                    
                    // Show account created step
                    document.getElementById('accountCreatedStep').style.display = 'block';
                    
                    // Display credentials
                    document.getElementById('displayUsername').textContent = data.customer_data.username;
                    document.getElementById('displayPassword').textContent = data.customer_data.password;
                    
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message || 'Registration failed. Please try again.', 'danger');
                }
            })
            .catch(error => {
                console.error('Registration error:', error);
                showLoading(false);
                showAlert('Registration failed. Please try again.', 'danger');
            });
        }

        // Claim reward function
        function claimReward(platform) {
            showLoading(true);
            
            const formData = new FormData();
            formData.append('platform', platform);
            formData.append(csrfName, csrfToken);
            
            fetch('<?= base_url('reward/claim-reward') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    
                    // Redirect to platform after short delay
                    setTimeout(() => {
                        window.open(data.redirect_url, '_blank');
                        // Redirect to home page after opening platform
                        setTimeout(() => {
                            window.location.href = '<?= base_url('/') ?>';
                        }, 2000);
                    }, 1500);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showLoading(false);
                showAlert('Claim failed. Please try again.', 'danger');
                console.error('Error:', error);
            });
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                showLoading(true);
                
                const formData = new FormData();
                formData.append(csrfName, csrfToken);
                
                fetch('<?= base_url('reward/logout') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    showLoading(false);
                    
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert('Logout failed. Please try again.', 'danger');
                    }
                })
                .catch(error => {
                    showLoading(false);
                    showAlert('Logout failed. Please try again.', 'danger');
                    console.error('Error:', error);
                });
            }
        }

        // Copy to clipboard function for credentials
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showAlert('Copied to clipboard!', 'info');
            }).catch(() => {
                showAlert('Could not copy to clipboard', 'warning');
            });
        }

        // Add click-to-copy functionality to credentials
        document.addEventListener('DOMContentLoaded', function() {
            const credentialElements = document.querySelectorAll('#displayUsername, #displayPassword');
            credentialElements.forEach(element => {
                element.style.cursor = 'pointer';
                element.title = 'Click to copy';
                element.addEventListener('click', function() {
                    copyToClipboard(this.textContent);
                });
            });
        });

        // Debug: Log session data on page load
        console.log('Reward page loaded');
        console.log('Has valid prize:', <?= json_encode($hasValidPrize) ?>);
        console.log('Logged in:', <?= json_encode($logged_in) ?>);
        <?php if (isset($winner_data)): ?>
        console.log('Winner data:', <?= json_encode($winner_data) ?>);
        <?php endif; ?>
        <?php if (isset($customer_data)): ?>
        console.log('Customer data:', <?= json_encode($customer_data) ?>);
        <?php endif; ?>
    </script>
</body>
</html>