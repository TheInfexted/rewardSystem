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
        }
        
        .credential-label {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        /* Platform Selection */
        .platform-selection {
            display: flex;
            gap: 15px;
            margin: 20px 0;
        }
        
        .platform-btn {
            flex: 1;
            padding: 20px;
            border: 2px solid #444;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .platform-btn:hover {
            border-color: #ffd700;
            background: rgba(255, 215, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .platform-btn i {
            font-size: 3rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .platform-btn.whatsapp i {
            color: #25D366;
        }
        
        .platform-btn.telegram i {
            color: #0088cc;
        }
        
        .platform-btn span {
            color: #fff;
            font-weight: 500;
        }
        
        /* Buttons */
        .btn-reward {
            background: linear-gradient(135deg, #ffd700 0%, #ffb347 100%);
            border: none;
            color: #000;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            width: 100%;
        }
        
        .btn-reward:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300%;
            height: 300%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            transform: translate(-50%, -50%) scale(0);
            transition: transform 0.5s ease;
        }
        
        .btn-reward:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.6);
        }
        
        .btn-reward:hover:before {
            transform: translate(-50%, -50%) scale(1);
        }
        
        /* Alert Messages */
        .alert-custom {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 300px;
            max-width: 90%;
            animation: alertSlideDown 0.5s ease-out;
        }
        
        @keyframes alertSlideDown {
            from {
                transform: translate(-50%, -100%);
                opacity: 0;
            }
            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }
        
        /* Loading Spinner */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
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
        
        /* Success Animation */
        .success-animation {
            animation: successPulse 1s ease-out;
        }
        
        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
        
        /* Important Note */
        .important-note {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            border-radius: 5px;
            padding: 10px;
            margin: 15px 0;
            color: #ff6b6b;
            font-size: 0.9rem;
            text-align: center;
        }
        
        .important-note i {
            color: #ff4444;
        }
    </style>
</head>
<body>
    <div class="portrait-container">
        <!-- Header -->
        <header class="reward-header">
            <h1><i class="bi bi-gift-fill"></i> Claim Your Reward</h1>
        </header>
        
        <!-- Main Content -->
        <main class="reward-content">
            <div class="reward-card">
                <?php if ($winner_data): ?>
                    <!-- Prize Display -->
                    <div class="prize-display">
                        <p class="prize-text">Congratulations! You Won:</p>
                        <p class="prize-amount"><?= esc($winner_data['name']) ?></p>
                        <?php if ($winner_data['prize'] > 0): ?>
                            <p class="prize-text">Value: ¥<?= number_format($winner_data['prize']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!$logged_in): ?>
                        <!-- Registration Step -->
                        <div id="registrationStep">
                            <h5 class="text-center text-warning mb-3">Create Account to Claim</h5>
                            <p class="text-center text-light mb-4">We'll create an instant account for you!</p>
                            
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-reward" onclick="autoRegister()">
                                    <i class="bi bi-lightning-fill"></i> Create Instant Account
                                </button>
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
                            
                            <div class="d-grid">
                                <button type="button" class="btn btn-reward" onclick="showPlatformSelection()">
                                    <i class="bi bi-gift-fill"></i> Proceed to Claim Reward
                                </button>
                            </div>
                        </div>
                        
                        <!-- Platform Selection Step (Hidden Initially) -->
                        <div id="platformSelectionStep" style="display: none;">
                            <h5 class="text-center text-warning mb-4">Select Platform to Claim:</h5>
                            
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
                            
                            <p class="text-center text-light mt-3">
                                You'll be redirected to send your claim details
                            </p>
                        </div>
                        
                    <?php else: ?>
                        <!-- Already Logged In -->
                        <div class="text-center">
                            <h5 class="text-success mb-3">
                                <i class="bi bi-check-circle-fill"></i> Welcome back, <?= esc($customer_data['name']) ?>!
                            </h5>
                            
                            <h6 class="text-warning mb-4">Select Platform to Claim:</h6>
                            
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
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- No Prize Data -->
                    <div class="text-center">
                        <i class="bi bi-emoji-frown text-warning" style="font-size: 4rem;"></i>
                        <h4 class="text-warning mt-3">No Prize Data Found</h4>
                        <p class="text-light mb-4">Please spin the wheel first to win a prize!</p>
                        
                        <div class="d-grid">
                            <a href="<?= base_url('/') ?>" class="btn btn-reward">
                                <i class="bi bi-arrow-left-circle"></i> Go to Spin Wheel
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="reward-footer">
            <p>&copy; <?= date('Y') ?> TapTapWin. All rights reserved.</p>
        </footer>
    </div>
    
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- Alert Container -->
    <div id="alertContainer"></div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Store credentials globally
        let userCredentials = null;
        
        // Show alert function
        function showAlert(message, type = 'info') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show alert-custom" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            document.getElementById('alertContainer').innerHTML = alertHtml;
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const alert = document.querySelector('.alert-custom');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }
        
        // Show/hide loading spinner
        function toggleLoading(show) {
            const spinner = document.getElementById('loadingSpinner');
            if (show) {
                spinner.classList.add('active');
            } else {
                spinner.classList.remove('active');
            }
        }
        
        // Auto register function
        async function autoRegister() {
            toggleLoading(true);
            
            try {
                const response = await fetch('<?= base_url('reward/auto-register') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                toggleLoading(false);
                
                if (data.success) {
                    // Store credentials
                    userCredentials = data.credentials;
                    
                    // Display credentials
                    document.getElementById('displayUsername').textContent = data.credentials.username;
                    document.getElementById('displayPassword').textContent = data.credentials.password;
                    
                    // Hide registration step, show account created step
                    document.getElementById('registrationStep').style.display = 'none';
                    document.getElementById('accountCreatedStep').style.display = 'block';
                    
                    showAlert('Account created successfully!', 'success');
                } else {
                    showAlert(data.message || 'Registration failed', 'danger');
                }
            } catch (error) {
                toggleLoading(false);
                showAlert('Network error. Please try again.', 'danger');
                console.error('Registration error:', error);
            }
        }
        
        // Show platform selection
        function showPlatformSelection() {
            document.getElementById('accountCreatedStep').style.display = 'none';
            document.getElementById('platformSelectionStep').style.display = 'block';
        }
        
        // Claim reward with selected platform
        async function claimReward(platform) {
            toggleLoading(true);
            
            const formData = new FormData();
            formData.append('platform', platform);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            
            try {
                const response = await fetch('<?= base_url('reward/claim-reward') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                toggleLoading(false);
                
                if (data.success) {
                    showAlert(`Redirecting to ${platform === 'whatsapp' ? 'WhatsApp' : 'Telegram'}...`, 'success');
                    
                    // Open platform in new tab
                    window.open(data.redirect_url, '_blank');
                    
                    // Redirect to dashboard after 2 seconds
                    setTimeout(() => {
                        window.location.href = data.dashboard_url;
                    }, 2000);
                } else {
                    showAlert(data.message || 'Claim failed', 'danger');
                }
            } catch (error) {
                toggleLoading(false);
                showAlert('Network error. Please try again.', 'danger');
                console.error('Claim error:', error);
            }
        }
        
        // Prevent zoom on double tap
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
        
        // Prevent pinch zoom
        document.addEventListener('gesturestart', function (e) {
            e.preventDefault();
        });
        
        document.addEventListener('touchstart', function(event) {
            if (event.touches.length > 1) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>