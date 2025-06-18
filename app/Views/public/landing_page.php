<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Wheel of Fortune</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CSS-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('css/landing-page.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/wheel-fortune.css') ?>">
    <style>
        /* Prevent zooming on mobile devices */
        * {
            touch-action: manipulation;
        }
    
        .welcome-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }
        
        .welcome-content {
            text-align: center;
            animation: welcomeFadeIn 0.5s ease-out;
            padding: 2rem;
            max-width: 500px;
        }
        
        @keyframes welcomeFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .welcome-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        .welcome-subtitle {
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 1rem;
        }
        
        .welcome-text {
            color: #ccc;
            font-size: 1.1rem;
        }
        
        .welcome-start-btn {
            background: linear-gradient(135deg, #ffd700 0%, #ffb347 100%);
            border: 3px solid #b8860b;
            color: #000;
            padding: 1.5rem 3rem;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(255, 215, 0, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-start-btn:before {
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
        
        .welcome-start-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.6);
        }
        
        .welcome-start-btn:hover:before {
            transform: translate(-50%, -50%) scale(1);
        }
        
        .btn-text {
            display: block;
            font-size: 1.5rem;
        }
        
        .btn-subtext {
            display: block;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .welcome-footer {
            color: #888;
        }
        
        .welcome-timer {
            margin-top: 2rem;
            font-size: 1.2rem;
            color: #ffd700;
            font-weight: bold;
        }
        
        /* Hide main content until welcome is dismissed */
        body.welcome-active {
            overflow: hidden;
        }
    
    
        /* Main Content */
        /* Force portrait mode styling */
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;  /* Horizontally center content */
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
        }
        
        .portrait-container {
            width: 100%;
            max-width: 414px;
            background-color: #000;
            min-height: 100vh;
        }
        
        /* Remove gaps between images */
        .stacked-images img {
            display: block;
            width: 100%;
            height: auto;
            margin: 0;
            padding: 0;
            line-height: 0;
        }
        
        /* Remove footer border */
        .casino-footer {
            border-top: none !important;
        }
        
        .wrap-text-frame {
            font-size: .8rem;
            padding: 1rem;
            margin: 0 2rem;
            background: url('img/fortune_wheel/text_frame.png') no-repeat center center;
            background-size: 100% 100%;
        }
        
        .wrap-fortuneWheel {
            background: url('img/fortune_wheel/bg_spin.png') no-repeat center center;
            background-size: 100%;
        }
        
        .wrap-fortuneWheel .innerWheel::before {
            content: '';
            width: 50px;
            height: 50px;
            display: block;
            position: relative;
            margin: 0 auto -40px auto;
            background: url('img/fortune_wheel/arrow.png') no-repeat top center;
            background-size: contain;
            z-index: 20;
        }
        
        #fortuneWheel {
            padding: 1rem;
            max-width: 100%;
            width: 100%;
            height: 100%;
            display: inline-block;
            background: url('img/fortune_wheel/bg_wheel_frame.png') no-repeat top center;
            background-size: 100%;
            position: relative;
            z-index: 0;
            overflow: hidden;
        }
        
        @keyframes shake {
            0% { transform: translate(1px, 1px) rotate(0deg); -webkit-transform: translate(1px, 1px) rotate(0deg); -moz-transform: translate(1px, 1px) rotate(0deg); -ms-transform: translate(1px, 1px) rotate(0deg); -o-transform: translate(1px, 1px) rotate(0deg); }
            10% { transform: translate(-1px, -2px) rotate(-1deg); -webkit-transform: translate(-1px, -2px) rotate(-1deg); -moz-transform: translate(-1px, -2px) rotate(-1deg); -ms-transform: translate(-1px, -2px) rotate(-1deg); -o-transform: translate(-1px, -2px) rotate(-1deg); }
            20% { transform: translate(-3px, 0px) rotate(1deg); -webkit-transform: translate(-3px, 0px) rotate(1deg); -moz-transform: translate(-3px, 0px) rotate(1deg); -ms-transform: translate(-3px, 0px) rotate(1deg); -o-transform: translate(-3px, 0px) rotate(1deg); }
            30% { transform: translate(3px, 2px) rotate(0deg); -webkit-transform: translate(3px, 2px) rotate(0deg); -moz-transform: translate(3px, 2px) rotate(0deg); -ms-transform: translate(3px, 2px) rotate(0deg); -o-transform: translate(3px, 2px) rotate(0deg); }
            40% { transform: translate(1px, -1px) rotate(1deg); -webkit-transform: translate(1px, -1px) rotate(1deg); -moz-transform: translate(1px, -1px) rotate(1deg); -ms-transform: translate(1px, -1px) rotate(1deg); -o-transform: translate(1px, -1px) rotate(1deg); }
            50% { transform: translate(-1px, 2px) rotate(-1deg); -webkit-transform: translate(-1px, 2px) rotate(-1deg); -moz-transform: translate(-1px, 2px) rotate(-1deg); -ms-transform: translate(-1px, 2px) rotate(-1deg); -o-transform: translate(-1px, 2px) rotate(-1deg); }
            60% { transform: translate(-3px, 1px) rotate(0deg); -webkit-transform: translate(-3px, 1px) rotate(0deg); -moz-transform: translate(-3px, 1px) rotate(0deg); -ms-transform: translate(-3px, 1px) rotate(0deg); -o-transform: translate(-3px, 1px) rotate(0deg); }
            70% { transform: translate(3px, 1px) rotate(-1deg); -webkit-transform: translate(3px, 1px) rotate(-1deg); -moz-transform: translate(3px, 1px) rotate(-1deg); -ms-transform: translate(3px, 1px) rotate(-1deg); -o-transform: translate(3px, 1px) rotate(-1deg); }
            80% { transform: translate(-1px, -1px) rotate(1deg); -webkit-transform: translate(-1px, -1px) rotate(1deg); -moz-transform: translate(-1px, -1px) rotate(1deg); -ms-transform: translate(-1px, -1px) rotate(1deg); -o-transform: translate(-1px, -1px) rotate(1deg); }
            90% { transform: translate(1px, 2px) rotate(0deg); -webkit-transform: translate(1px, 2px) rotate(0deg); -moz-transform: translate(1px, 2px) rotate(0deg); -ms-transform: translate(1px, 2px) rotate(0deg); -o-transform: translate(1px, 2px) rotate(0deg); }
            100% { transform: translate(1px, -2px) rotate(-1deg); -webkit-transform: translate(1px, -2px) rotate(-1deg); -moz-transform: translate(1px, -2px) rotate(-1deg); -ms-transform: translate(1px, -2px) rotate(-1deg); -o-transform: translate(1px, -2px) rotate(-1deg); }
        }
        
        .glow {
            color: #fff;
            -webkit-animation: glow 1s ease-in-out infinite alternate;
            -moz-animation: glow 1s ease-in-out infinite alternate;
            animation: glow 1s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
          from {
            text-shadow: 0 0 1px #fff, 0 0 5px #fff, 0 0 10px #e60073, 0 0 20px #e60073, 0 0 30px #e60073;
          }
          to {
            text-shadow: 0 0 1px #fff, 0 0 10px #ff4da6, 0 0 20px #ff4da6, 0 0 30px #ff4da6, 0 0 50px #ff4da6;
          }
        }
        
        .blinking {
            animation: blinker 1s linear infinite;
        }
        
        @keyframes blinker {
          50% {
            opacity: 0;
          }
        }
        
        .modal-content {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8);
        }
        
        #claimModal .modal-content {
            background: linear-gradient(135deg, rgba(0,0,0,0.95) 0%, rgba(25,25,25,0.95) 100%);
            animation: modalSlideIn 0.4s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .form-control:focus {
            background-color: #495057 !important;
            border-color: #ffd700 !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25) !important;
            color: #fff !important;
        }
        
        .form-check-input:checked {
            background-color: #ffd700;
            border-color: #ffd700;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
        }
        
        .alert {
            border: none;
            border-radius: 8px;
        }
        
        .terms-content {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .pulse-animation {
            animation: pulse 1s infinite;
        }
        
        #musicToggle {
            width: 60px;
            height: 60px;
            padding: 0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffd700;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.5);
            transition: all 0.3s ease;
        }
        
        #musicToggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.7);
        }
    </style>
</head>
<body class="casino-body">
    
    <!-- Welcome Overlay -->
    <div id="welcomeOverlay" class="welcome-overlay">
        <div class="welcome-content">
            <div class="welcome-logo">
                <?php if (!empty($page_data['welcome_image'])): ?>
                    <img src="<?= base_url('uploads/landing/' . $page_data['welcome_image']) ?>" 
                         alt="Welcome" class="mb-3" style="max-width: 200px;">
                <?php else: ?>
                    <img src="<?= base_url('img/taptapwin.png') ?>" 
                         alt="TapTapWin" class="mb-3" style="max-width: 200px;">
                <?php endif; ?>
            </div>
            
            <h1 class="welcome-title text-gold"><?= esc($page_data['welcome_title'] ?? 'TAP TAP WIN') ?></h1>
            <h2 class="welcome-subtitle"><?= esc($page_data['welcome_subtitle'] ?? 'Welcome to TapTapWin!') ?></h2>
            
            <p class="welcome-text mb-4"><?= esc($page_data['welcome_text'] ?? 'Prizes for you to win!') ?></p>
            
            <div class="welcome-button-container">
                <button id="startGameBtn" class="welcome-start-btn">
                    <span class="btn-text"><?= esc($page_data['welcome_button_text'] ?? 'Click to Start!') ?></span>
                    <span class="btn-subtext">Spins for today: <b><?= $spins_remaining ?></b></span>
                </button>
            </div>
            
            <p class="welcome-footer mt-4">
                <small><?= esc($page_data['welcome_footer_text'] ?? 'Please try our game before you leave!') ?></small>
            </p>
        </div>
    </div>
    
    <div class="portrait-container">
        <!-- Header Section -->
        <header class="casino-header">
            <div class="container-fluid px-0">
                <?php if (!empty($page_data['header_text'])): ?>
                <div class="text-center py-2">
                    <p class="golden-text mb-0 small"><?= $page_data['header_text'] ?></p>
                </div>
                <?php endif; ?>
                
                <div class="stacked-images">
                    <?php if (!empty($page_data['header_image_1'])): ?>
                        <img src="<?= base_url('uploads/landing/' . $page_data['header_image_1']) ?>" 
                             alt="Header Image 1">
                    <?php endif; ?>
                    <?php if (!empty($page_data['header_image_2'])): ?>
                        <img src="<?= base_url('uploads/landing/' . $page_data['header_image_2']) ?>" 
                             alt="Header Image 2">
                    <?php endif; ?>
                </div>
            </div>
        </header>
        
        <!-- Main Wheel Section -->
        <main class="wheel-container py-3">
            <div class="container-fluid">
                <!-- Wheel Header -->
                <article class="text-center my-3 pb-3">
                    <p class="m-0 fw-bold text-warning blinking">You have <b id="spinsCount"><?= $spins_remaining ?></b> Free Spins Left!</p>
                    <label>Limited Time Event: <b class="fw-semibold text-danger">LAST CHANCE!</b></label>
                </article>
                
                <!-- Image-based Fortune Wheel -->
                <section class="wrap-fortuneWheel pb-5">
                    <section class="wrap-text-frame text-center">
                        <p class="m-0 glow">Free <span id="freeSpinsText">3</span> Spins!</p>
                        <p class="m-0">Try your luck now and win up to 120% BONUS!</p>
                    </section>
                
                    <figure class="d-block m-0 pt-1 p-4 innerWheel position-relative">
                        <canvas id="fortuneWheel" width="460" height="460" 
                                data-responsiveMinWidth="180" 
                                data-responsiveScaleHeight="true" 
                                data-responsiveMargin="50">
                            <p class="text-white text-center">Sorry, your browser doesn't support canvas. Please try another.</p>
                        </canvas>
                    </figure>
                    
                    <div class="text-center position-relative">
                        <button class="btn btn-warning bg-gradient" 
                                id="spinButton" 
                                onclick="startSpin();" 
                                <?= $spins_remaining <= 0 ? 'disabled' : '' ?>>
                            <span id="spinButtonText"><?= $spins_remaining > 0 ? 'Spin the Wheel' : 'Out of Spins' ?></span>
                        </button>
                    </div>
                </section>
            </div>
        </main>

        <!-- Footer -->
        <footer class="casino-footer">
            <div class="container-fluid px-0">
                <div class="stacked-images">
                    <?php if (!empty($page_data['footer_image_1'])): ?>
                        <img src="<?= base_url('uploads/landing/' . $page_data['footer_image_1']) ?>" 
                             alt="Footer Image 1">
                    <?php endif; ?>
                    <?php if (!empty($page_data['footer_image_2'])): ?>
                        <img src="<?= base_url('uploads/landing/' . $page_data['footer_image_2']) ?>" 
                             alt="Footer Image 2">
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($page_data['footer_text'])): ?>
                <div class="text-center py-2">
                    <p class="mb-0 small text-gold"><?= $page_data['footer_text'] ?></p>
                </div>
                <?php endif; ?>
            </div>
        </footer>
        
        <!-- Background Music Player -->
        <?php if (isset($music_data) && !empty($music_data['music_file'])): ?>
        <div id="bgMusicContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
            <button id="musicToggle" class="btn btn-gold btn-sm rounded-circle" style="width: 50px; height: 50px;">
                <i class="bi bi-music-note-beamed" id="musicIcon"></i>
            </button>
            
            <audio id="bgMusic" 
                   <?= $music_data['loop'] ? 'loop' : '' ?>
                   preload="auto">
                <source src="<?= base_url('uploads/music/' . $music_data['music_file']) ?>" type="audio/mpeg">
            </audio>
        </div>
        <?php endif; ?>
        
        <!-- Spin Sound Plauer -->
        <?php if (!empty($spin_sound['spin_sound'])): ?>
            <audio id="spinSound" preload="auto">
                <source src="<?= base_url('uploads/sounds/' . $spin_sound['spin_sound']) ?>" type="audio/mpeg">
            </audio>
        <?php endif; ?>


    </div> <!-- End of portrait-container -->

    <!-- Win Modal -->
    <div class="modal fade" id="winModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content bg-dark border-0">
                <div class="modal-body text-center py-4">
                    <div class="win-animation">
                        <h5 class="win-title mb-3">🎉 You Won! 🎉</h5>
                        <div class="win-prize mb-3" id="modalPrize"></div>
                        <button type="button" class="btn btn-gold btn-sm" data-bs-dismiss="modal">Collect Your Prize</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- NEW: Bonus Claim Form Modal -->
    <div class="modal fade" id="claimModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border-0" style="border: 2px solid #ffd700;">
                <div class="modal-header border-0 text-center">
                    <div class="w-100">
                        <h4 class="modal-title text-center" style="color: #ffd700;">
                            🎉 CONGRATULATIONS! 🎉
                        </h4>
                        <p class="mb-0" style="color: #ffb347;">You won: <span id="claimPrizeDisplay" style="font-weight: bold;"></span></p>
                    </div>
                </div>
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="claimError" class="alert alert-danger" style="display: none;"></div>
                    <div id="claimSuccess" class="alert alert-success" style="display: none;"></div>
                    
                    <!-- Claim Form -->
                    <form id="claimForm" onsubmit="submitClaim(event)">
                        <!-- Hidden fields for bonus details -->
                        <input type="hidden" id="claimBonusType" value="">
                        <input type="hidden" id="claimBonusAmount" value="">
                        
                        <div class="mb-3">
                            <label for="claimUserName" class="form-label text-light">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control bg-secondary text-light border-0" 
                                   id="claimUserName" 
                                   name="user_name"
                                   placeholder="Enter your full name"
                                   required
                                   maxlength="100">
                        </div>
                        
                        <div class="mb-3">
                            <label for="claimPhoneNumber" class="form-label text-light">
                                Phone Number <span class="text-danger">*</span>
                            </label>
                            <input type="tel" 
                                   class="form-control bg-secondary text-light border-0" 
                                   id="claimPhoneNumber" 
                                   name="phone_number"
                                   placeholder="Enter your phone number"
                                   required
                                   maxlength="20">
                        </div>
                        
                        <div class="mb-3">
                            <label for="claimEmail" class="form-label text-light">
                                Email Address <span class="text-light">(Optional)</span>
                            </label>
                            <input type="email" 
                                   class="form-control bg-secondary text-light border-0" 
                                   id="claimEmail" 
                                   name="email"
                                   placeholder="Enter your email (optional)"
                                   maxlength="100">
                        </div>
                        
                        <!-- Terms and Conditions -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                                <label class="form-check-label text-light small" for="agreeTerms">
                                    I agree to the 
                                    <a href="#" class="text-warning" data-bs-toggle="modal" data-bs-target="#termsModal">
                                        Terms and Conditions
                                    </a>
                                    <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" 
                                    id="claimSubmitBtn"
                                    class="btn btn-lg px-4 py-2"
                                    style="background: linear-gradient(135deg, #ffd700 0%, #ffb347 100%); 
                                           border: 2px solid #b8860b; 
                                           color: #000; 
                                           font-weight: bold;">
                                Claim My Bonus
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <small class="text-light">
                            * Required fields. Your information is secure and will only be used for bonus processing.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark border-0">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title text-light">Terms and Conditions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-light">
                    <div class="terms-content">
                        <?php 
                        $termsText = $bonus_settings['bonus_terms_text'] ?? 'Terms and conditions apply. Must be 18+ to claim bonus.';
                        echo nl2br(esc($termsText));
                        ?>
                    </div>
                    
                    <hr class="border-secondary">
                    
                    <h6 class="text-warning">Additional Terms:</h6>
                    <ul class="small">
                        <li>You must be 18 years or older to claim this bonus</li>
                        <li>One bonus claim per person/IP address per day</li>
                        <li>Bonus is subject to verification and approval</li>
                        <li>Company reserves the right to modify or cancel bonuses at any time</li>
                        <li>By claiming this bonus, you agree to receive promotional communications</li>
                    </ul>
                </div>
                <div class="modal-footer border-top border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal" 
                            onclick="document.getElementById('agreeTerms').checked = true;">
                        I Agree
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/Winwheel.min.js') ?>"></script>
    <script src="<?= base_url('js/TweenMax.min.js') ?>"></script>
    
    <!-- Music Player Script (SEPARATE) -->
    <?php if (isset($music_data) && !empty($music_data['music_file'])): ?>
    <script>
    // Music player functionality
    document.addEventListener('DOMContentLoaded', function () {
        const audio = document.getElementById('bgMusic');
        const icon = document.getElementById('musicIcon');
        const toggleBtn = document.getElementById('musicToggle');
    
        if (!audio || !toggleBtn) return;
    
        audio.volume = <?= $music_data['volume'] ?? 0.5 ?>;
    
        function tryPlay() {
            audio.play().then(() => {
                icon.classList.add('bi-pause-fill');
                icon.classList.remove('bi-music-note-beamed');
                toggleBtn.classList.add('pulse-animation');
            }).catch(() => {
                console.log('Autoplay blocked, waiting for user interaction');
            });
        }
    
        <?php if ($music_data['autoplay']): ?>
        tryPlay();
        <?php endif; ?>
    
        // Force autoplay after first interaction
        document.addEventListener('click', tryPlay, { once: true });
        document.addEventListener('touchstart', tryPlay, { once: true });
    
        toggleBtn.addEventListener('click', function () {
            if (audio.paused) {
                audio.play();
                icon.classList.add('bi-pause-fill');
                icon.classList.remove('bi-music-note-beamed');
                toggleBtn.classList.add('pulse-animation');
            } else {
                audio.pause();
                icon.classList.remove('bi-pause-fill');
                icon.classList.add('bi-music-note-beamed');
                toggleBtn.classList.remove('pulse-animation');
            }
        });
    });
    </script>
    <?php endif; ?>
    
    <script>
        let theWheel;
        let wheelSpinning = false;
        let spinsRemaining = <?= $spins_remaining ?>;
        let tickSoundContext = null;
        let tickSoundBuffer = null;
        let tickSoundVolume = <?= isset($spin_sound) && isset($spin_sound['volume']) ? $spin_sound['volume'] : 0.7 ?>;
        let lastTickTime = 0;
        let tickSoundEnabled = <?= (isset($spin_sound) && $spin_sound['enabled']) ? 'true' : 'false' ?>;
        let winSoundBuffer = null;
        let winSoundEnabled = <?= (isset($win_sound) && $win_sound['enabled']) ? 'true' : 'false' ?>;
        
        // Store the wheel items from database with their winning rates
        const wheelItems = [
            <?php foreach($wheel_items as $index => $item): ?>
            {
                name: '<?= esc($item['item_name']) ?>',
                prize: <?= $item['item_prize'] ?>,
                type: '<?= $item['item_types'] ?>',
                winningRate: <?= $item['winning_rate'] ?>,
                order: <?= $item['order'] ?>
            }<?= $index < count($wheel_items) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ];
        
        // Calculate winning ranges based on winning rates
        function calculateWinningRanges() {
            let totalRate = 0;
            const ranges = [];
            
            // First, calculate total of all winning rates
            wheelItems.forEach(item => {
                totalRate += item.winningRate;
            });
            
            // If total doesn't equal 100, normalize the rates proportionally
            if (totalRate !== 100 && totalRate > 0) {
                wheelItems.forEach(item => {
                    item.normalizedRate = (item.winningRate / totalRate) * 100;
                });
            } else {
                wheelItems.forEach(item => {
                    item.normalizedRate = item.winningRate;
                });
            }
            
            // Calculate cumulative ranges
            let currentSum = 0;
            wheelItems.forEach((item, index) => {
                ranges[index] = {
                    start: currentSum,
                    end: currentSum + item.normalizedRate,
                    item: item
                };
                currentSum += item.normalizedRate;
            });
            
            return ranges;
        }
        
        // Determine winner based on winning rates
        function determineWinner() {
            const ranges = calculateWinningRanges();
            const random = Math.random() * 100; // Random number between 0-100
            
            console.log('Random number:', random);
            console.log('Winning ranges:', ranges);
            
            // Find which range the random number falls into
            for (let i = 0; i < ranges.length; i++) {
                if (random >= ranges[i].start && random < ranges[i].end) {
                    console.log('Winner determined:', ranges[i].item);
                    return {
                        winnerIndex: i,
                        winnerItem: ranges[i].item
                    };
                }
            }
            
            // Fallback to first item if no match found
            console.log('No match found, defaulting to first item');
            return {
                winnerIndex: 0,
                winnerItem: wheelItems[0]
            };
        }
        
        // Initialize tick sound
        function initializeTickSound() {
            <?php if (isset($spin_sound) && !empty($spin_sound['sound_file']) && $spin_sound['enabled']): ?>
            try {
                // Create audio context (only once)
                if (!tickSoundContext) {
                    tickSoundContext = new (window.AudioContext || window.webkitAudioContext)();
                }
                
                // Load the tick sound file
                fetch('<?= base_url('uploads/sounds/' . $spin_sound['sound_file']) ?>')
                    .then(response => response.arrayBuffer())
                    .then(data => tickSoundContext.decodeAudioData(data))
                    .then(buffer => {
                        tickSoundBuffer = buffer;
                        console.log('Tick sound loaded successfully');
                    })
                    .catch(error => {
                        console.error('Error loading tick sound:', error);
                    });
            } catch (error) {
                console.error('Web Audio API not supported:', error);
            }
            <?php endif; ?>
        }
        
        // Play tick sound once
        function playTickSound() {
            if (!tickSoundEnabled || !tickSoundBuffer || !tickSoundContext) return;
            
            try {
                // Resume audio context if suspended
                if (tickSoundContext.state === 'suspended') {
                    tickSoundContext.resume();
                }
                
                // Prevent overlapping sounds - ensure minimum time between ticks
                const currentTime = Date.now();
                if (currentTime - lastTickTime < 50) return; // Minimum 50ms between ticks
                lastTickTime = currentTime;
                
                // Create a new source for each tick
                const source = tickSoundContext.createBufferSource();
                source.buffer = tickSoundBuffer;
                
                // Create gain node for volume control
                const gainNode = tickSoundContext.createGain();
                gainNode.gain.value = tickSoundVolume;
                
                // Connect nodes
                source.connect(gainNode);
                gainNode.connect(tickSoundContext.destination);
                
                // Play the tick
                source.start(0);
                
                // Clean up when done
                source.onended = function() {
                    source.disconnect();
                    gainNode.disconnect();
                };
                
            } catch (error) {
                console.error('Error playing tick sound:', error);
            }
        }
        
        // Initialize win sound
        function initializeWinSound() {
            <?php if (isset($win_sound) && !empty($win_sound['sound_file']) && $win_sound['enabled']): ?>
            try {
                if (!tickSoundContext) {
                    tickSoundContext = new (window.AudioContext || window.webkitAudioContext)();
                }
                
                // Load win sound
                fetch('<?= base_url('uploads/sounds/' . $win_sound['sound_file']) ?>')
                    .then(response => response.arrayBuffer())
                    .then(data => tickSoundContext.decodeAudioData(data))
                    .then(buffer => {
                        winSoundBuffer = buffer;
                        console.log('Win sound loaded successfully');
                    })
                    .catch(error => {
                        console.error('Error loading win sound:', error);
                    });
            } catch (error) {
                console.error('Win sound initialization error:', error);
            }
            <?php endif; ?>
        }
        
        // Play win sound
        function playWinSound() {
            if (!winSoundEnabled || !winSoundBuffer || !tickSoundContext) return;
            
            try {
                const source = tickSoundContext.createBufferSource();
                source.buffer = winSoundBuffer;
                
                const gainNode = tickSoundContext.createGain();
                gainNode.gain.value = <?= isset($win_sound) && isset($win_sound['volume']) ? $win_sound['volume'] : 0.8 ?>;
                
                source.connect(gainNode);
                gainNode.connect(tickSoundContext.destination);
                
                source.start(0);
                
                source.onended = function() {
                    source.disconnect();
                    gainNode.disconnect();
                };
            } catch (error) {
                console.error('Error playing win sound:', error);
            }
        }
        
        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Add welcome-active class to body
            document.body.classList.add('welcome-active');
            
            // Start game button
            document.getElementById('startGameBtn').addEventListener('click', function() {
                console.log('Start button clicked');
                
                // Initialize audio contexts (this is the user interaction we need!)
                if (tickSoundContext && tickSoundContext.state === 'suspended') {
                    tickSoundContext.resume();
                }
                
                // Start background music if it exists
                const bgMusic = document.getElementById('bgMusic');
                if (bgMusic) {
                    bgMusic.play().catch(e => console.log('BGM autoplay failed:', e));
                }
                
                // Fade out welcome screen
                const overlay = document.getElementById('welcomeOverlay');
                overlay.style.animation = 'fadeOut 0.5s ease-out forwards';
                
                setTimeout(() => {
                    overlay.style.display = 'none';
                    document.body.classList.remove('welcome-active');
                    
                    // Initialize everything AFTER the overlay is hidden
                    initializeTickSound();
                    initializeWinSound();
                    createWheel();
                    addCelebrationStyles();
                    addEnhancedSpinStyles();
                }, 500);
            });
            
            // Handle additional click for audio context
            document.addEventListener('click', function initAudio() {
                if (tickSoundContext && tickSoundContext.state === 'suspended') {
                    tickSoundContext.resume();
                }
            }, { once: true });
        });
        
        function createWheel() {
            // Create segments for the wheel display
            let segments = [];
            
            for (let i = 0; i < wheelItems.length; i++) {
                const item = wheelItems[i];
                const useRedBackground = i % 2 === 0;
                const textColor = useRedBackground ? "#f8b500" : "#000";
                
                segments.push({
                    'text': item.name,
                    'textFillStyle': textColor,
                    'image': `<?= base_url("img/fortune_wheel/") ?>${useRedBackground ? 'red.png' : 'brown.png'}`,
                    'itemData': item
                });
            }
        
            // Destroy existing wheel if it exists
            if (theWheel) {
                theWheel.ctx.clearRect(0, 0, theWheel.canvas.width, theWheel.canvas.height);
            }
        
            // Create new wheel with tick sound
            theWheel = new Winwheel({
                'canvasId': 'fortuneWheel',
                'numSegments': segments.length,
                'outerRadius': 200,
                'responsive': true,
                'drawMode': 'segmentImage',
                'drawText': true,
                'textFontSize': 16,
                'textFontWeight': 'bold',
                'textOrientation': 'horizontal',
                'textAlignment': 'center',
                'textDirection': 'reversed',
                'textMargin': 15,
                'textFontFamily': 'Arial, sans-serif',
                'segments': segments,
                'pins': {
                    'number': segments.length * 2, // Double pins for more frequent ticks
                    'outerRadius': 5,
                    'responsive': true,
                    'margin': 5,
                    'fillStyle': '#f8b500',
                    'strokeStyle': '#f8b500'
                },
                'animation': {
                    'type': 'spinToStop',
                    'duration': 8,
                    'spins': 12,
                    'callbackFinished': alertPrize,
                    'soundTrigger': 'pin', // Trigger sound when pointer hits pin
                    'callbackSound': playTickSound, // Function to call for sound
                    'easing': 'Power3.easeOut'
                }
            });
        }
        
        function startSpin() {
            if (wheelSpinning === true || spinsRemaining <= 0) {
                return;
            }
            
            wheelSpinning = true;
            
            // Update button state immediately
            const spinButton = document.getElementById('spinButton');
            const spinButtonText = document.getElementById('spinButtonText');
            
            spinButton.disabled = true;
            if (spinButtonText) {
                spinButtonText.textContent = 'SPINNING...';
            } else {
                spinButton.textContent = 'SPINNING...';
            }
        
            // Determine winner based on actual winning rates
            const winnerResult = determineWinner();
            const winnerIndex = winnerResult.winnerIndex;
            const winnerItem = winnerResult.winnerItem;
            
            // Calculate the exact angle to land on the predetermined segment
            const segmentAngle = 360 / theWheel.numSegments;
            const targetAngle = (winnerIndex * segmentAngle) + (segmentAngle / 2);
            
            // Add many rotations for dramatic effect
            const additionalSpins = Math.floor(Math.random() * 8) + 15;
            const finalRotation = (360 * additionalSpins) + targetAngle;
            
            // Set the wheel to stop at the predetermined segment
            theWheel.animation.stopAngle = finalRotation;
            
            // Store the winner data BEFORE starting animation
            theWheel.winnerData = winnerItem;
            theWheel.winnerIndex = winnerIndex;
            
            // Start the animation
            theWheel.startAnimation();
            
            // Add spinning effects
            addSpinningEffects();
            
            // Decrease spins remaining IMMEDIATELY
            spinsRemaining--;
            
            // Update counter immediately
            updateSpinsCounter();
        
            // Send spin data to backend
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('predetermined_outcome', JSON.stringify({
                item_name: winnerItem.name,
                item_prize: winnerItem.prize,
                item_types: winnerItem.type,
                index: winnerIndex
            }));
        
            fetch('<?= base_url('spin') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Spin recorded:', data);
            })
            .catch(error => {
                console.error('Error recording spin:', error);
            });
        }
        
        function alertPrize(indicatedSegment) {
            const winner = theWheel.winnerData || indicatedSegment.itemData;
            
            if (!winner) {
                resetSpinButton();
                return;
            }
            
            // Play win sound for actual prizes (not "Try Again")
            if (winner.name !== 'Try Again') {
                playWinSound();
            }
            
            const modalContent = document.querySelector('#winModal .modal-content');
            const modalPrize = document.getElementById('modalPrize');
            const winTitle = document.querySelector('.win-title');
            const collectButton = document.querySelector('#winModal .btn');
            
            let prizeText = winner.name;
            let titleText = '';
            let buttonText = '';
            
            // Remove previous styling classes
            modalContent.classList.remove('try-again', 'big-win');
            
            // Check if it's "Try Again" or a real prize
            if (winner.name === 'Try Again') {
                // "Try Again" message
                titleText = '😔 Please Try Again!';
                prizeText = 'Better luck on your next spin!';
                buttonText = spinsRemaining > 0 ? 'Continue Playing' : 'Come Back Tomorrow';
                
                modalContent.classList.add('try-again');
                winTitle.style.color = '#ffa500';
                modalPrize.style.color = '#fff';
                collectButton.className = 'btn btn-warning btn-sm';
                collectButton.onclick = function() {
                    bootstrap.Modal.getInstance(document.getElementById('winModal')).hide();
                };
                
            } else if (winner.name.includes('120%') || winner.name.includes('BONUS') || winner.type === 'product') {
                // Bonus/Prize message - Show claim form instead of simple modal
                showClaimForm(winner);
                return; // Exit early since we're showing claim form
                
            } else {
                // Other prizes (smaller bonuses)
                titleText = '🎉 You Won! 🎉';
                
                if (winner.prize > 0) {
                    prizeText = `${winner.name} - ${winner.type === 'cash' ? '¥' : ''}${parseFloat(winner.prize).toLocaleString()}`;
                } else {
                    prizeText = winner.name;
                }
                
                buttonText = 'Collect Your Prize';
                
                winTitle.style.color = '#ffd700';
                modalPrize.style.color = '#ffb347';
                collectButton.className = 'btn spin-wheel-btn btn-sm';
                collectButton.onclick = function() {
                    bootstrap.Modal.getInstance(document.getElementById('winModal')).hide();
                };
            }
            
            // Update modal content for non-bonus wins
            winTitle.textContent = titleText;
            modalPrize.textContent = prizeText;
            collectButton.textContent = buttonText;
            
            // Show Bootstrap modal
            const winModal = new bootstrap.Modal(document.getElementById('winModal'));
            winModal.show();
            
            // Reset spin button after delay
            setTimeout(() => {
                resetSpinButton();
            }, 3000);
        }
        
        //Fadeout Animation
        const fadeOutStyle = document.createElement('style');
        fadeOutStyle.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(fadeOutStyle);
        
        // Show claim form for bonus wins
        function showClaimForm(winner) {
            // Hide the regular win modal if it's open
            const winModalEl = document.getElementById('winModal');
            if (winModalEl) {
                const winModal = bootstrap.Modal.getInstance(winModalEl);
                if (winModal) {
                    winModal.hide();
                }
            }
            
            // Show claim form modal
            const claimModal = new bootstrap.Modal(document.getElementById('claimModal'));
            
            // Update claim form with winner details
            document.getElementById('claimBonusType').value = winner.name;
            document.getElementById('claimBonusAmount').value = winner.prize || 0;
            document.getElementById('claimPrizeDisplay').textContent = `${winner.name}${winner.prize > 0 ? ' - ¥' + parseFloat(winner.prize).toLocaleString() : ''}`;
            
            // Add celebration effects
            addCelebrationEffects();
            
            claimModal.show();
            
            // Reset spin button after delay
            setTimeout(() => {
                resetSpinButton();
            }, 3000);
        }
        
        // Handle claim form submission
        function submitClaim(event) {
            event.preventDefault();
            
            const submitBtn = document.getElementById('claimSubmitBtn');
            const originalText = submitBtn.textContent;
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            // Get form data
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('user_name', document.getElementById('claimUserName').value.trim());
            formData.append('phone_number', document.getElementById('claimPhoneNumber').value.trim());
            formData.append('email', document.getElementById('claimEmail').value.trim());
            formData.append('bonus_type', document.getElementById('claimBonusType').value);
            formData.append('bonus_amount', document.getElementById('claimBonusAmount').value);
            
            // Client-side validation
            const userName = formData.get('user_name');
            const phoneNumber = formData.get('phone_number');
            const email = formData.get('email');
            
            if (!userName || userName.length < 2) {
                showClaimError('Please enter a valid name (minimum 2 characters).');
                resetClaimButton(submitBtn, originalText);
                return;
            }
            
            if (!phoneNumber || phoneNumber.length < 10) {
                showClaimError('Please enter a valid phone number (minimum 10 digits).');
                resetClaimButton(submitBtn, originalText);
                return;
            }
            
            if (email && !isValidEmail(email)) {
                showClaimError('Please enter a valid email address.');
                resetClaimButton(submitBtn, originalText);
                return;
            }
            
            // Submit to backend
            fetch('<?= base_url('claim-bonus') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showClaimSuccess('Bonus claimed successfully! Redirecting...');
                    
                    // Hide modal after delay and redirect
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('claimModal')).hide();
                        
                        // Redirect to admin-configured URL
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        }
                    }, 2000);
                } else {
                    showClaimError(data.message || 'Failed to claim bonus. Please try again.');
                    resetClaimButton(submitBtn, originalText);
                }
            })
            .catch(error => {
                showClaimError('Network error. Please check your connection and try again.');
                resetClaimButton(submitBtn, originalText);
            });
        }
        
        // Helper functions for claim form
        function resetClaimButton(button, originalText) {
            button.disabled = false;
            button.textContent = originalText;
        }
        
        function showClaimError(message) {
            const errorDiv = document.getElementById('claimError');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            // Hide error after 5 seconds
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
        
        function showClaimSuccess(message) {
            const successDiv = document.getElementById('claimSuccess');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
        }
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Clear form when modal is hidden
        document.getElementById('claimModal').addEventListener('hidden.bs.modal', function() {
            // Clear form
            document.getElementById('claimForm').reset();
            
            // Hide messages
            document.getElementById('claimError').style.display = 'none';
            document.getElementById('claimSuccess').style.display = 'none';
            
            // Reset button
            const submitBtn = document.getElementById('claimSubmitBtn');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Claim My Bonus';
        });
        
        function updateSpinsCounter() {
            // Update main counter "You have X Free Spins Left!"
            const spinsCount = document.getElementById('spinsCount');
            if (spinsCount) {
                spinsCount.textContent = spinsRemaining;
            }
            
            // Update free spins text in the frame
            const freeSpinsText = document.getElementById('freeSpinsText');
            if (freeSpinsText) {
                freeSpinsText.textContent = Math.max(spinsRemaining, 0);
            }
            
            // Update the main text if no spins left
            const mainSpinsText = spinsCount?.parentElement;
            if (mainSpinsText && spinsRemaining <= 0) {
                mainSpinsText.innerHTML = '<b id="spinsCount">0</b> Free Spins Left! Come back tomorrow!';
                mainSpinsText.classList.remove('blinking');
                mainSpinsText.classList.add('text-muted');
            }
        }
        
        function resetSpinButton() {
            wheelSpinning = false;
            const spinButton = document.getElementById('spinButton');
            const spinButtonText = document.getElementById('spinButtonText');
            
            if (spinsRemaining > 0) {
                spinButton.disabled = false;
                if (spinButtonText) {
                    spinButtonText.textContent = 'SPIN THE WHEEL';
                } else {
                    spinButton.textContent = 'SPIN THE WHEEL';
                }
                spinButton.style.background = '';
                spinButton.style.color = '';
            } else {
                spinButton.disabled = true;
                if (spinButtonText) {
                    spinButtonText.textContent = 'COME BACK TOMORROW!';
                } else {
                    spinButton.textContent = 'COME BACK TOMORROW!';
                }
                spinButton.style.background = 'linear-gradient(135deg, #6c757d 0%, #495057 100%)';
                spinButton.style.color = '#fff';
            }
        }
        
        function addCelebrationEffects() {
            // Add confetti-like effect
            const modal = document.getElementById('winModal');
            modal.classList.add('celebration-modal');
            
            // Remove after animation
            setTimeout(() => {
                modal.classList.remove('celebration-modal');
            }, 3000);
            
            // Add sparkle effect to the wheel
            const canvas = document.getElementById('fortuneWheel');
            canvas.classList.add('winner-glow');
            
            setTimeout(() => {
                canvas.classList.remove('winner-glow');
            }, 3000);
        }
        
        // Add visual effects during spinning
        function addSpinningEffects() {
            const canvas = document.getElementById('fortuneWheel');
            const spinButton = document.getElementById('spinButton');
            
            // Add glow effect to wheel during spin
            canvas.style.filter = 'drop-shadow(0 0 20px rgba(255, 215, 0, 0.6))';
            
            // Make button pulse
            spinButton.style.animation = 'pulse 1s infinite';
            
            // Remove effects after spin completes
            setTimeout(() => {
                canvas.style.filter = '';
                spinButton.style.animation = '';
            }, 9000);
        }
        
        function addCelebrationStyles() {
            const celebrationStyles = `
                .celebration-modal .modal-content {
                    animation: celebrationBounce 0.6s ease-in-out;
                    box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
                }
                
                @keyframes celebrationBounce {
                    0%, 100% { transform: scale(1); }
                    25% { transform: scale(1.05); }
                    50% { transform: scale(1.1); }
                    75% { transform: scale(1.05); }
                }
                
                .winner-glow {
                    animation: winnerGlow 1s ease-in-out infinite alternate;
                }
                
                @keyframes winnerGlow {
                    0% { filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.3)); }
                    100% { filter: drop-shadow(0 0 40px rgba(255, 215, 0, 0.8)); }
                }
                
                .win-title {
                    transition: color 0.3s ease;
                }
                
                .win-prize {
                    transition: color 0.3s ease;
                }
                
                /* Try Again specific styling */
                .modal-content.try-again {
                    border: 2px solid #ffa500;
                    box-shadow: 0 0 20px rgba(255, 165, 0, 0.5);
                }
                
                /* Big win specific styling */
                .modal-content.big-win {
                    border: 2px solid #ffd700;
                    box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
                    background: linear-gradient(135deg, rgba(0,0,0,0.9) 0%, rgba(25,25,25,0.9) 100%);
                }
            `;
            
            // Add the celebration styles to the page
            const styleSheet = document.createElement('style');
            styleSheet.textContent = celebrationStyles;
            document.head.appendChild(styleSheet);
        }
        
        // Enhanced CSS animations for spinning effects
        function addEnhancedSpinStyles() {
            const enhancedStyles = `
                @keyframes pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                    100% { transform: scale(1); }
                }
                
                .spinning-wheel {
                    animation: wheelGlow 0.5s ease-in-out infinite alternate;
                }
                
                @keyframes wheelGlow {
                    0% { 
                        filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.3));
                        transform: scale(1);
                    }
                    50% { 
                        filter: drop-shadow(0 0 30px rgba(255, 215, 0, 0.8));
                        transform: scale(1.02);
                    }
                    100% { 
                        filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.3));
                        transform: scale(1);
                    }
                }
            `;
            
            const styleSheet = document.createElement('style');
            styleSheet.textContent = enhancedStyles;
            document.head.appendChild(styleSheet);
        }
        
        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (tickSoundContext) {
                tickSoundContext.close();
            }
        });
        
        // Prevent zooming with Ctrl/Cmd + scroll
        document.addEventListener('wheel', function(e) {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
            }
        }, { passive: false });
        
        // Prevent zooming with Ctrl/Cmd + plus/minus
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '-' || e.key === '=')) {
                e.preventDefault();
            }
        });
        
        // Prevent double-tap zoom on mobile
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(e) {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
        
        // Prevent pinch zoom on iOS
        document.addEventListener('gesturestart', function(e) {
            e.preventDefault();
        });
        
        document.addEventListener('gesturechange', function(e) {
            e.preventDefault();
        });
        
        document.addEventListener('gestureend', function(e) {
            e.preventDefault();
        });
    </script>
</body>
</html>