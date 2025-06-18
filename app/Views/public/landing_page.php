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
        // Global variables
        let theWheel;
        let wheelSpinning = false;
        let spinsRemaining = <?= $spins_remaining ?>;
        let tickSoundEnabled = <?= (isset($spin_sound) && $spin_sound['enabled']) ? 'true' : 'false' ?>;
        let winSoundEnabled = <?= (isset($win_sound) && $win_sound['enabled']) ? 'true' : 'false' ?>;

        //Welcome Overlay Handler
        document.addEventListener('DOMContentLoaded', function() {
            // Add welcome-active class to body
            document.body.classList.add('welcome-active');
            
            // Handle start game button click
            const startGameBtn = document.getElementById('startGameBtn');
            if (startGameBtn) {
                startGameBtn.addEventListener('click', function() {
                    console.log('Start button clicked');
                    
                    // Initialize audio contexts
                    if (typeof AudioContext !== 'undefined') {
                        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        if (audioContext.state === 'suspended') {
                            audioContext.resume();
                        }
                    }
                    
                    // Start background music if it exists
                    const bgMusic = document.getElementById('bgMusic');
                    if (bgMusic) {
                        bgMusic.play().catch(e => console.log('BGM autoplay failed:', e));
                    }
                    
                    // Fade out welcome screen
                    const overlay = document.getElementById('welcomeOverlay');
                    if (overlay) {
                        overlay.style.animation = 'fadeOut 0.5s ease-out forwards';
                        
                        setTimeout(() => {
                            overlay.style.display = 'none';
                            document.body.classList.remove('welcome-active');
                            
                            console.log('Welcome overlay hidden, wheel should be visible now');
                            
                            // Initialize wheel after overlay is hidden
                            if (typeof initializeWheel === 'function') {
                                initializeWheel();
                            }
                        }, 500);
                    }
                });
            } else {
                console.error('startGameBtn not found!');
            }
        });

        // Add fadeOut CSS animation
        const welcomeStyle = document.createElement('style');
        welcomeStyle.textContent = `
            @keyframes fadeOut {
                from { 
                    opacity: 1; 
                    transform: scale(1);
                }
                to { 
                    opacity: 0; 
                    transform: scale(0.95);
                }
            }
            
            .welcome-active .portrait-container {
                display: none;
            }
        `;
        document.head.appendChild(welcomeStyle);
        
        // Store the wheel items from database
        const wheelItems = [
            <?php foreach($wheel_items as $index => $item): ?>
            {
                name: '<?= esc($item['item_name']) ?>',
                prize: <?= $item['item_prize'] ?>,
                type: '<?= $item['item_types'] ?>',
                winningRate: <?= $item['winning_rate'] ?>,
                order: <?= $item['order'] ?>,
                id: <?= $item['item_id'] ?>
            }<?= $index < count($wheel_items) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ];

        // Initialize wheel when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing wheel with items:', wheelItems);
            initializeWheel();
            updateSpinsCounter();
        });

        // Initialize the wheel
        function initializeWheel() {
            if (wheelItems.length === 0) {
                console.error('No wheel items found!');
                return;
            }

            // Create segments array for the wheel (using original image-based segments)
            const segments = [];
            wheelItems.forEach((item, index) => {
                const useRedBackground = index % 2 === 0;
                const textColor = useRedBackground ? "#f8b500" : "#000";
                
                segments.push({
                    'text': item.name,
                    'textFillStyle': textColor,
                    'image': `<?= base_url("img/fortune_wheel/") ?>${useRedBackground ? 'red.png' : 'brown.png'}`,
                    'itemData': item
                });
            });

            console.log('Creating wheel with segments:', segments);

            // Create the wheel (using original image-based configuration)
            theWheel = new Winwheel({
                'canvasId': 'fortuneWheel',
                'numSegments': segments.length,
                'outerRadius': 200,
                'responsive': true,
                'drawMode': 'segmentImage',  // Important: Use image mode
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

            console.log('Wheel created successfully:', theWheel);
        }

        // Main spin function - MUST match your button's onclick
        function startSpin() {
            console.log('startSpin() called');
            console.log('wheelSpinning:', wheelSpinning);
            console.log('spinsRemaining:', spinsRemaining);
            
            if (wheelSpinning === true) {
                console.log('Wheel already spinning');
                return;
            }
            
            if (spinsRemaining <= 0) {
                console.log('No spins remaining');
                showNoSpinsModal();
                return;
            }

            if (!theWheel) {
                console.error('Wheel not initialized!');
                alert('Wheel not ready. Please refresh the page.');
                return;
            }
            
            wheelSpinning = true;
            console.log('Starting spin...');
            
            // Update button state immediately
            const spinButton = document.getElementById('spinButton');
            const spinButtonText = document.getElementById('spinButtonText');
            
            spinButton.disabled = true;
            if (spinButtonText) {
                spinButtonText.textContent = 'SPINNING...';
            } else {
                spinButton.textContent = 'SPINNING...';
            }

            // Play tick sound if enabled
            if (tickSoundEnabled) {
                playTickSound();
            }

            // Determine winner
            const winnerResult = determineWinner();
            const winnerIndex = winnerResult.winnerIndex;
            const winnerItem = winnerResult.winnerItem;
            
            console.log('Winner determined:', winnerItem);
            
            // Calculate the exact angle to land on the predetermined segment
            const segmentAngle = 360 / theWheel.numSegments;
            const targetAngle = (winnerIndex * segmentAngle) + (segmentAngle / 2);
            
            // Add many rotations for dramatic effect
            const additionalSpins = Math.floor(Math.random() * 5) + 8;
            const finalRotation = (360 * additionalSpins) + targetAngle;
            
            // Set the wheel to stop at the predetermined segment
            theWheel.animation.stopAngle = finalRotation;
            
            // Store the winner data BEFORE starting animation
            theWheel.winnerData = winnerItem;
            theWheel.winnerIndex = winnerIndex;
            
            // Start the animation
            theWheel.startAnimation();
            
            // Decrease spins remaining
            spinsRemaining--;
            updateSpinsCounter();
            
            // Record spin result
            recordSpinResult(winnerItem);
        }

        // Determine winner based on winning rates
        function determineWinner() {
            const rand = Math.random() * 100;
            let cumulativeRate = 0;
            
            for (let i = 0; i < wheelItems.length; i++) {
                cumulativeRate += parseFloat(wheelItems[i].winningRate);
                if (rand <= cumulativeRate) {
                    return {
                        winnerIndex: i,
                        winnerItem: wheelItems[i]
                    };
                }
            }
            
            // Fallback to random if rates don't add up to 100%
            const randomIndex = Math.floor(Math.random() * wheelItems.length);
            return {
                winnerIndex: randomIndex,
                winnerItem: wheelItems[randomIndex]
            };
        }

        // Handle prize alert when wheel stops
        function alertPrize(indicatedSegment) {
            console.log('alertPrize called with:', indicatedSegment);
            
            const winner = theWheel.winnerData || indicatedSegment.itemData;
            
            if (!winner) {
                console.error('No winner data found!');
                resetSpinButton();
                return;
            }

            console.log('Processing winner:', winner);

            // Play win sound for actual prizes (not "Try Again")
            if (winner.name !== 'Try Again' && winSoundEnabled) {
                playWinSound();
            }

            // Handle different types of wins
            if (winner.name === 'Try Again') {
                showTryAgainModal();
            } else if (winner.name.includes('120%') || winner.name.includes('BONUS') || winner.type === 'product') {
                // NEW: Redirect to reward system for bonus prizes
                storeWinnerDataAndRedirect(winner);
            } else {
                // Regular prize
                showRegularWinModal(winner);
            }

            // Reset button after delay
            setTimeout(() => {
                resetSpinButton();
            }, 3000);
        }

        // NEW: Store winner data and redirect to reward system
        function storeWinnerDataAndRedirect(winner) {
            console.log('Storing winner data and redirecting:', winner);
            
            // Store winner data in session via AJAX
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('winner_data', JSON.stringify(winner));
            
            fetch('<?= base_url('store-winner') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Store winner response:', data);
                if (data.success) {
                    // Show modal with redirect button
                    showBonusWinModal(winner);
                } else {
                    console.error('Failed to store winner data:', data.message);
                    // Fallback to regular modal
                    showRegularWinModal(winner);
                }
            })
            .catch(error => {
                console.error('Error storing winner data:', error);
                // Fallback to regular modal
                showRegularWinModal(winner);
            });
        }

        // Show bonus win modal with redirect functionality
        function showBonusWinModal(winner) {
            const modalContent = document.querySelector('#winModal .modal-content');
            const modalPrize = document.getElementById('modalPrize');
            const winTitle = document.querySelector('.win-title');
            const collectButton = document.querySelector('#winModal .btn');
            
            // Update modal for bonus win
            winTitle.textContent = '🎉 CONGRATULATIONS! 🎉';
            modalPrize.textContent = `You Won: ${winner.name}${winner.prize > 0 ? ' - ¥' + parseFloat(winner.prize).toLocaleString() : ''}`;
            collectButton.textContent = 'Claim Reward';
            collectButton.className = 'btn btn-success btn-sm';
            
            // Style the modal
            winTitle.style.color = '#ffd700';
            modalPrize.style.color = '#28a745';
            modalContent.style.background = 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)';
            modalContent.style.border = '3px solid #ffd700';
            
            // Set up redirect functionality
            collectButton.onclick = function() {
                window.location.href = '<?= base_url('reward') ?>';
            };
            
            // Show modal
            const winModal = new bootstrap.Modal(document.getElementById('winModal'));
            winModal.show();
            
            // Auto-redirect after 5 seconds
            setTimeout(() => {
                window.location.href = '<?= base_url('reward') ?>';
            }, 5000);
        }

        // Show regular win modal
        function showRegularWinModal(winner) {
            const modalContent = document.querySelector('#winModal .modal-content');
            const modalPrize = document.getElementById('modalPrize');
            const winTitle = document.querySelector('.win-title');
            const collectButton = document.querySelector('#winModal .btn');
            
            winTitle.textContent = '🎉 You Won! 🎉';
            modalPrize.textContent = `${winner.name}${winner.prize > 0 ? ' - ¥' + parseFloat(winner.prize).toLocaleString() : ''}`;
            collectButton.textContent = 'Collect Prize';
            collectButton.className = 'btn btn-warning btn-sm';
            
            winTitle.style.color = '#ffd700';
            modalPrize.style.color = '#fff';
            modalContent.style.background = '';
            modalContent.style.border = '';
            
            collectButton.onclick = function() {
                bootstrap.Modal.getInstance(document.getElementById('winModal')).hide();
            };
            
            const winModal = new bootstrap.Modal(document.getElementById('winModal'));
            winModal.show();
        }

        // Show try again modal
        function showTryAgainModal() {
            const modalContent = document.querySelector('#winModal .modal-content');
            const modalPrize = document.getElementById('modalPrize');
            const winTitle = document.querySelector('.win-title');
            const collectButton = document.querySelector('#winModal .btn');
            
            winTitle.textContent = '😔 Try Again!';
            modalPrize.textContent = 'Better luck next time!';
            collectButton.textContent = spinsRemaining > 0 ? 'Continue Playing' : 'Come Back Tomorrow';
            collectButton.className = 'btn btn-secondary btn-sm';
            
            winTitle.style.color = '#ffa500';
            modalPrize.style.color = '#fff';
            modalContent.style.background = 'linear-gradient(135deg, #6c757d, #495057)';
            modalContent.style.border = '';
            
            collectButton.onclick = function() {
                bootstrap.Modal.getInstance(document.getElementById('winModal')).hide();
            };
            
            const winModal = new bootstrap.Modal(document.getElementById('winModal'));
            winModal.show();
        }

        // Show no spins modal
        function showNoSpinsModal() {
            const modalContent = document.querySelector('#winModal .modal-content');
            const modalPrize = document.getElementById('modalPrize');
            const winTitle = document.querySelector('.win-title');
            const collectButton = document.querySelector('#winModal .btn');
            
            winTitle.textContent = '⏰ No Spins Left!';
            modalPrize.textContent = 'Come back tomorrow for more free spins!';
            collectButton.textContent = 'OK';
            collectButton.className = 'btn btn-secondary btn-sm';
            
            winTitle.style.color = '#dc3545';
            modalPrize.style.color = '#fff';
            modalContent.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
            modalContent.style.border = '';
            
            collectButton.onclick = function() {
                bootstrap.Modal.getInstance(document.getElementById('winModal')).hide();
            };
            
            const winModal = new bootstrap.Modal(document.getElementById('winModal'));
            winModal.show();
        }

        // Reset spin button
        function resetSpinButton() {
            wheelSpinning = false;
            const spinButton = document.getElementById('spinButton');
            const spinButtonText = document.getElementById('spinButtonText');
            
            if (spinsRemaining > 0) {
                spinButton.disabled = false;
                if (spinButtonText) {
                    spinButtonText.textContent = 'Spin the Wheel';
                } else {
                    spinButton.textContent = 'Spin the Wheel';
                }
                spinButton.style.background = '';
                spinButton.style.color = '';
            } else {
                spinButton.disabled = true;
                if (spinButtonText) {
                    spinButtonText.textContent = 'Out of Spins';
                } else {
                    spinButton.textContent = 'Out of Spins';
                }
                spinButton.style.background = 'linear-gradient(135deg, #6c757d 0%, #495057 100%)';
                spinButton.style.color = '#fff';
            }
        }

        // Update spins counter
        function updateSpinsCounter() {
            const spinsCount = document.getElementById('spinsCount');
            const freeSpinsText = document.getElementById('freeSpinsText');
            
            if (spinsCount) {
                spinsCount.textContent = spinsRemaining;
            }
            if (freeSpinsText) {
                freeSpinsText.textContent = spinsRemaining;
            }
        }

        // Record spin result
        function recordSpinResult(winner) {
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('predetermined_outcome', JSON.stringify({
                item_name: winner.name,
                item_prize: winner.prize,
                item_types: winner.type,
                index: winner.order
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

        // Sound functions
        function playTickSound() {
            const spinAudio = document.getElementById('spinAudio');
            if (spinAudio) {
                spinAudio.currentTime = 0;
                spinAudio.play().catch(e => console.log('Tick sound play failed'));
            }
        }

        function playWinSound() {
            const winAudio = document.getElementById('winAudio');
            if (winAudio) {
                winAudio.currentTime = 0;
                winAudio.play().catch(e => console.log('Win sound play failed'));
            }
        }

        // Debug function to test the wheel manually
        function testSpin() {
            console.log('Test spin triggered');
            console.log('Current state:', {
                wheelSpinning: wheelSpinning,
                spinsRemaining: spinsRemaining,
                theWheel: theWheel,
                wheelItems: wheelItems
            });
            startSpin();
        }

        // Add some CSS for better visual effects
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
            
            .winner-glow {
                filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.8)) !important;
            }
            
            .celebration-modal .modal-content {
                animation: bounce 0.6s ease-in-out;
            }
            
            @keyframes bounce {
                0%, 20%, 60%, 100% { transform: scale(1); }
                40% { transform: scale(1.1); }
                80% { transform: scale(1.05); }
            }
        `;
        document.head.appendChild(style);

    </script>
</body>
</html>