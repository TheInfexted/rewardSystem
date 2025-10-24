<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Dynamic SEO Meta Tags -->
    <title><?= !empty($seo_data['meta_title']) ? esc($seo_data['meta_title']) : 'Fortune Wheel - With Reward System!' ?></title>
    
    <?php if (!empty($seo_data['meta_description'])): ?>
    <meta name="description" content="<?= esc($seo_data['meta_description']) ?>">
    <?php endif; ?>
    
    <?php if (!empty($seo_data['meta_keywords'])): ?>
    <meta name="keywords" content="<?= esc($seo_data['meta_keywords']) ?>">
    <?php endif; ?>
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= !empty($seo_data['meta_title']) ? esc($seo_data['meta_title']) : 'Wheel of Fortune - Your Chance to Win Big!' ?>">
    <?php if (!empty($seo_data['meta_description'])): ?>
    <meta property="og:description" content="<?= esc($seo_data['meta_description']) ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    
    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= !empty($seo_data['meta_title']) ? esc($seo_data['meta_title']) : 'Wheel of Fortune - Your Chance to Win Big!' ?>">
    <?php if (!empty($seo_data['meta_description'])): ?>
    <meta name="twitter:description" content="<?= esc($seo_data['meta_description']) ?>">
    <?php endif; ?>
    
    <!-- Other meta tags -->
    <meta name="robots" content="index, follow">
    <meta name="author" content="Fortune Wheel">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CSS-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('css/landing-page.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('css/wheel-fortune.css') ?>?v=<?= time() ?>">
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
            display: block;
            background: url('img/fortune_wheel/bg_wheel_frame.png') no-repeat top center !important;
            background-size: 100% !important;
            position: relative !important;
            z-index: 15 !important;
            overflow: hidden !important;
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

        /* Media Upload Areas (Images and Videos) */
        .media-upload-area {
            border: 2px dashed var(--dark-gold);
            border-radius: 0.375rem;
            padding: 1rem;
            background-color: rgba(255, 215, 0, 0.05);
            text-align: center;
        }

        .preview-media {
            max-width: 100%;
            max-height: 200px;
            border: 2px solid var(--primary-gold);
        }

        .current-media-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            position: relative;
        }

        .media-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .media-filename {
            margin-top: 10px;
            text-align: center;
        }

        .media-type-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }

        .media-type-badge .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* SEO Meta Content Styling */
        .seo-meta-content {
            background-color: #1a1a1a;
            color: #ccc;
        }

        .seo-content-wrapper {
            font-size: 14px;
            line-height: 1.6;
            color: #ccc;
        }

        .seo-content-wrapper h1,
        .seo-content-wrapper h2,
        .seo-content-wrapper h3,
        .seo-content-wrapper h4,
        .seo-content-wrapper h5,
        .seo-content-wrapper h6 {
            color: #ffd700;
            margin-bottom: 0.5rem;
        }

        .seo-content-wrapper p {
            margin-bottom: 1rem;
            color: #ccc;
        }

        .seo-content-wrapper a {
            color: #ffd700;
            text-decoration: none;
        }

        .seo-content-wrapper a:hover {
            text-decoration: underline;
        }

        .seo-content-wrapper ul,
        .seo-content-wrapper ol {
            color: #ccc;
            padding-left: 1.5rem;
        }

        .seo-content-wrapper li {
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body class="casino-body">
    
    <!-- Trace Code -->
    <?php if (!empty($page_data['trace_code'])): ?>
        <!-- Pixel Tracking Code -->
        <?= $page_data['trace_code'] ?>
        <!-- End Pixel Tracking Code -->
    <?php endif; ?>

    <!-- Welcome Overlay -->
    <div id="welcomeOverlay" class="welcome-overlay">
        <div class="welcome-content">
            <div class="welcome-logo">
                <?php if (!empty($page_data['welcome_image'])): ?>
                    <?php 
                    $fileExtension = strtolower(pathinfo($page_data['welcome_image'], PATHINFO_EXTENSION));
                    $isVideo = in_array($fileExtension, ['mov', 'mp4', 'webm', 'ogg', 'avi']);
                    ?>
                    <?php if ($isVideo): ?>
                        <video src="<?= base_url('uploads/landing/' . $page_data['welcome_image']) ?>" 
                               autoplay muted loop playsinline
                               class="mb-3" style="max-width: 200px; max-height: 200px;">
                            Your browser does not support the video tag.
                        </video>
                    <?php else: ?>
                        <img src="<?= base_url('uploads/landing/' . $page_data['welcome_image']) ?>" 
                             alt="Welcome" class="mb-3" style="max-width: 200px;">
                    <?php endif; ?>
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
                    <?php if (!empty($page_data['header_media_1'])): ?>
                        <?php if (($page_data['header_media_1_type'] ?? 'image') === 'video'): ?>
                            <video autoplay muted loop playsinline>
                                <source src="<?= base_url('uploads/landing/' . $page_data['header_media_1']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <img src="<?= base_url('uploads/landing/' . $page_data['header_media_1']) ?>" 
                                alt="Header Media 1">
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($page_data['header_media_2'])): ?>
                        <?php if (($page_data['header_media_2_type'] ?? 'image') === 'video'): ?>
                            <video autoplay muted loop playsinline>
                                <source src="<?= base_url('uploads/landing/' . $page_data['header_media_2']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <img src="<?= base_url('uploads/landing/' . $page_data['header_media_2']) ?>" 
                                alt="Header Media 2">
                        <?php endif; ?>
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
                        <p class="m-0 glow">Free <span id="freeSpinsText"><?= $max_daily_spins ?></span> Spins!</p>
                        <p class="m-0"><?= esc($page_data['free_spins_subtitle'] ?? 'Try your luck now and win up to 120% BONUS!') ?></p>
                    </section>
                
                    <figure class="d-block m-0 pt-1 p-4 innerWheel position-relative">
                        <canvas id="fortuneWheel" width="460" height="460" 
                                data-responsiveMinWidth="220" 
                                data-responsiveMargin="-20">
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
                    <?php if (!empty($page_data['footer_media_1'])): ?>
                        <?php if (($page_data['footer_media_1_type'] ?? 'image') === 'video'): ?>
                            <video autoplay muted loop playsinline>
                                <source src="<?= base_url('uploads/landing/' . $page_data['footer_media_1']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <img src="<?= base_url('uploads/landing/' . $page_data['footer_media_1']) ?>" 
                                alt="Footer media 1">
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($page_data['footer_media_2'])): ?>
                        <?php if (($page_data['footer_media_2_type'] ?? 'image') === 'video'): ?>
                            <video autoplay muted loop playsinline>
                                <source src="<?= base_url('uploads/landing/' . $page_data['footer_media_2']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <img src="<?= base_url('uploads/landing/' . $page_data['footer_media_2']) ?>" 
                                alt="Footer media 2">
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($page_data['footer_text'])): ?>
                <div class="text-center py-2">
                    <p class="mb-0 small text-gold"><?= $page_data['footer_text'] ?></p>
                </div>
                <?php endif; ?>
            </div>
        </footer>

         <!-- SEO Meta Content Section -->
        <?php if (!empty($seo_data['meta_content'])): ?>
        <section id="seo-content" class="seo-meta-content py-4" style="background-color: #000000ff;">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="seo-content-wrapper">
                            <?= $seo_data['meta_content'] ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Background Music Player and Contact Button -->
        <div style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; display: flex; flex-direction: column; gap: 10px;">
            
            <!-- Contact Button (only show if there are enabled contact links) -->
            <?php if (!empty($contact_links)): ?>
            <button id="contactToggle" class="btn btn-gold btn-sm rounded-circle" style="width: 50px; height: 50px;" data-bs-toggle="modal" data-bs-target="#contactModal">
                <i class="bi bi-telephone" id="contactIcon"></i>
            </button>
            <?php endif; ?>
            
            <!-- Music Button -->
            <?php if (isset($music_data) && !empty($music_data['music_file'])): ?>
            <button id="musicToggle" class="btn btn-gold btn-sm rounded-circle" style="width: 50px; height: 50px;">
                <i class="bi bi-music-note-beamed" id="musicIcon"></i>
            </button>
            
            <audio id="bgMusic" 
                   <?= $music_data['loop'] ? 'loop' : '' ?>
                   preload="auto">
                <source src="<?= base_url('uploads/music/' . $music_data['music_file']) ?>" type="audio/mpeg">
            </audio>
            <?php endif; ?>
        </div>

        <!-- Contact Modal -->
        <?php if (!empty($contact_links)): ?>
        <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark border-gold">
                    <div class="modal-header border-gold">
                        <h5 class="modal-title text-gold" id="contactModalLabel">
                            <i class="bi bi-headset"></i> Contact Us
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="text-light mb-4">Choose your preferred way to contact us:</p>
                        
                        <div class="contact-links d-grid gap-3">
                            <?php foreach ($contact_links as $link): ?>
                            <a href="<?= esc($link['url']) ?>" 
                               target="_blank" 
                               class="btn btn-outline-gold btn-lg contact-link-btn">
                                <?php 
                                // Add appropriate icon based on the link name or URL
                                $icon = 'bi-link-45deg'; // default icon
                                $linkName = strtolower($link['name']);
                                $linkUrl = strtolower($link['url']);
                                
                                if (strpos($linkName, 'whatsapp') !== false || strpos($linkUrl, 'wa.me') !== false) {
                                    $icon = 'bi-whatsapp';
                                } elseif (strpos($linkName, 'telegram') !== false || strpos($linkUrl, 't.me') !== false) {
                                    $icon = 'bi-telegram';
                                } elseif (strpos($linkName, 'email') !== false || strpos($linkUrl, 'mailto:') !== false) {
                                    $icon = 'bi-envelope';
                                } elseif (strpos($linkName, 'phone') !== false || strpos($linkUrl, 'tel:') !== false) {
                                    $icon = 'bi-telephone';
                                } elseif (strpos($linkName, 'support') !== false) {
                                    $icon = 'bi-headset';
                                }
                                ?>
                                <i class="bi <?= $icon ?> me-2"></i>
                                <?= esc($link['name']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Tick & Win Sounds -->
        <?php if (!empty($spin_sound['spin_sound'])): ?>
        <audio id="spinAudio" preload="auto">
            <source src="<?= base_url('uploads/sounds/' . $spin_sound['spin_sound']) ?>" type="audio/mpeg">
        </audio>
        <?php endif; ?>

        <?php if (!empty($win_sound['sound_file'])): ?>
        <audio id="winAudio" preload="auto">
            <source src="<?= base_url('uploads/sounds/' . $win_sound['sound_file']) ?>" type="audio/mpeg">
        </audio>
        <?php endif; ?>


    </div> <!-- End of portrait-container -->

    <!-- Win Modal -->
    <div class="modal fade" id="winModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content bg-dark border-0">
                <div class="modal-body text-center py-4">
                    <div class="win-animation">
                        <h5 class="win-title mb-3">🎉 You Won! 🎉</h5>
                        <div class="win-prize mb-3" id="modalPrize"></div>
                        <button type="button" class="btn btn-gold btn-sm" id="collectPrizeBtn">Collect Your Prize</button>
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
                // Autoplay blocked, waiting for user interaction
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
        let tickInterval = null;
        let lastPinNumber = 0;
        let tickSoundContext = null;
        let tickSoundBuffer = null;
        let tickSoundVolume = <?= isset($spin_sound) && isset($spin_sound['volume']) ? $spin_sound['volume'] : 0.7 ?>;
        let lastTickTime = 0;
        let winSoundBuffer = null;

        //Welcome Overlay Handler
        document.addEventListener('DOMContentLoaded', function() {
            // Add welcome-active class to body
            document.body.classList.add('welcome-active');
            
            // Handle start game button click
            const startGameBtn = document.getElementById('startGameBtn');
            if (startGameBtn) {
                startGameBtn.addEventListener('click', function() {
                    // Start button clicked
                    
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
                        bgMusic.play().catch(e => {
                            // BGM autoplay failed
                        });
                    }
                    
                    // Initialize wheel immediately for faster response
                    initializeTickSound();
                    initializeWinSound();
                    
                    if (typeof initializeWheel === 'function') {
                        initializeWheel();
                    }
                    
                    // Fade out welcome screen with reduced delay
                    const overlay = document.getElementById('welcomeOverlay');
                    if (overlay) {
                        overlay.style.animation = 'fadeOut 0.3s ease-out forwards';
                        
                        setTimeout(() => {
                            overlay.style.display = 'none';
                            document.body.classList.remove('welcome-active');
                            // Welcome overlay hidden, wheel should be visible now
                        }, 300);
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

        // Preload sounds in background for faster loading
        function preloadSounds() {
            <?php if (isset($spin_sound) && !empty($spin_sound['sound_file']) && $spin_sound['enabled']): ?>
            // Preload tick sound
            fetch('<?= base_url('uploads/sounds/' . $spin_sound['sound_file']) ?>')
                .then(response => response.arrayBuffer())
                .then(data => {
                    if (!tickSoundContext) {
                        tickSoundContext = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    return tickSoundContext.decodeAudioData(data);
                })
                .then(buffer => {
                    tickSoundBuffer = buffer;
                    // Tick sound preloaded successfully
                })
                .catch(error => {
                    console.error('Error preloading tick sound:', error);
                });
            <?php endif; ?>

            <?php if (isset($win_sound) && !empty($win_sound['sound_file']) && $win_sound['enabled']): ?>
            // Preload win sound
            fetch('<?= base_url('uploads/sounds/' . $win_sound['sound_file']) ?>')
                .then(response => response.arrayBuffer())
                .then(data => {
                    if (!tickSoundContext) {
                        tickSoundContext = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    return tickSoundContext.decodeAudioData(data);
                })
                .then(buffer => {
                    winSoundBuffer = buffer;
                    // Win sound preloaded successfully
                })
                .catch(error => {
                    console.error('Error preloading win sound:', error);
                });
            <?php endif; ?>
        }

        // Legacy sound initialization functions (kept for compatibility)
        function initializeTickSound() {
            // Sounds are now preloaded, just ensure context exists
            if (!tickSoundContext) {
                tickSoundContext = new (window.AudioContext || window.webkitAudioContext)();
            }
        }

        function initializeWinSound() {
            // Sounds are now preloaded, just ensure context exists
            if (!tickSoundContext) {
                tickSoundContext = new (window.AudioContext || window.webkitAudioContext)();
            }
        }

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
            // Preload sounds in background for faster button response
            preloadSounds();
            
            // Initializing wheel with items
            initializeWheel();
            updateSpinsCounter();
            
            // Check spin status on page load to ensure sync
            setTimeout(() => {
                updateSpinStatus();
            }, 500);
        });

        // Initialize the wheel
        function initializeWheel() {
            if (wheelItems.length === 0) {
                console.error('No wheel items found!');
                return;
            }

            // Helper function to format text for multi-line display
            const formatTextForWheel = (text, maxCharsPerLine = 15) => {
                if (text.length <= maxCharsPerLine) {
                    return text;
                }
                
                // Split by common separators and try to break at logical points
                const words = text.split(/(\s+)/);
                const lines = [];
                let currentLine = '';
                
                for (let i = 0; i < words.length; i++) {
                    const word = words[i];
                    if ((currentLine + word).length <= maxCharsPerLine) {
                        currentLine += word;
                    } else {
                        if (currentLine.trim()) {
                            lines.push(currentLine.trim());
                        }
                        currentLine = word;
                    }
                }
                
                if (currentLine.trim()) {
                    lines.push(currentLine.trim());
                }
                
                return lines.join('\n');
            };

            // Create segments array for the wheel
            const segments = [];
            wheelItems.forEach((item, index) => {
                const useRedBackground = index % 2 === 0;
                const textColor = useRedBackground ? "#f8b500" : "#000";
                
                segments.push({
                    'text': formatTextForWheel(item.name),
                    'textFillStyle': textColor,
                    'image': `<?= base_url("img/fortune_wheel/") ?>${useRedBackground ? 'red.png' : 'brown.png'}`,
                    'itemData': item
                });
            });

            // Creating wheel with segments

            // Create the wheel with multi-line text support
            theWheel = new Winwheel({
                'canvasId': 'fortuneWheel',
                'numSegments': segments.length,
                'outerRadius': 200,
                'responsive': true,
                'drawMode': 'segmentImage',
                'drawText': true,
                'textFontSize': 16,  // Reduced for multi-line support
                'textFontWeight': 'bold',
                'textOrientation': 'horizontal',
                'textAlignment': 'center',
                'textDirection': 'reversed',
                'textMargin': 12,  // Reduced margin for better spacing
                'textFontFamily': 'Arial, sans-serif',
                'segments': segments,
                'pins': {
                    'number': segments.length * 2,
                    'outerRadius': 5,
                    'responsive': true,
                    'margin': 5,
                    'fillStyle': '#f8b500',
                    'strokeStyle': '#f8b500'
                },
                'animation': {
                    'type': 'spinToStop',
                    'duration': 8,
                    'spins': 15,
                    'callbackFinished': alertPrize,
                    'callbackBefore': animationBefore,
                    'callbackAfter': animationAfter,
                    'soundTrigger': 'pin',
                    'callbackSound': playTickSound,
                    'easing': 'Power3.easeOut'
                }
            });

            // Wheel created successfully
        }

        // Animation callbacks for manual sound handling
        function animationBefore() {
        }

        function animationAfter() {
            // Called after each frame update
        }

        // Function to check and update spin status from server
        function updateSpinStatus() {
            fetch('<?= base_url('spin-status') ?>', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    spinsRemaining = data.spins_remaining;
                    updateSpinsCounter();
                    updateSpinButton();
                    // Spin status updated
                }
            })
            .catch(error => {
                console.error('Error checking spin status:', error);
            });
        }

        // Main spin function - UPDATED
        function startSpin() {
            // startSpin() called
            
            if (wheelSpinning === true) {
                // Wheel already spinning
                return;
            }
            
            if (spinsRemaining <= 0) {
                // No spins remaining
                showNoSpinsModal();
                return;
            }

            if (!theWheel) {
                console.error('Wheel not initialized!');
                alert('Wheel not ready. Please refresh the page.');
                return;
            }
            
            wheelSpinning = true;
            lastPinNumber = 0; // Reset pin tracking
            // Starting spin...
            
            // Update button state immediately
            const spinButton = document.getElementById('spinButton');
            const spinButtonText = document.getElementById('spinButtonText');
            
            spinButton.disabled = true;
            if (spinButtonText) {
                spinButtonText.textContent = 'SPINNING...';
            } else {
                spinButton.textContent = 'SPINNING...';
            }

            // MAKE AJAX CALL TO BACKEND FIRST
            fetch('<?= base_url('spin') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update spins remaining from server response
                    spinsRemaining = data.spins_remaining;
                    // Server updated spins remaining
                    
                    // Continue with wheel spin animation
                    performWheelSpin();
                } else {
                    // Reset if spin failed
                    wheelSpinning = false;
                    spinButton.disabled = false;
                    if (spinButtonText) {
                        spinButtonText.textContent = 'Spin the Wheel';
                    }
                    alert(data.message || 'Spin failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Spin request failed:', error);
                wheelSpinning = false;
                spinButton.disabled = false;
                if (spinButtonText) {
                    spinButtonText.textContent = 'Spin the Wheel';
                }
                alert('Network error. Please try again.');
            });
        }

        // NEW function for wheel animation
        function performWheelSpin() {
            
            // Determine winner
            const winnerResult = determineWinner();
            const winnerIndex = winnerResult.winnerIndex;
            const winnerItem = winnerResult.winnerItem;
            
            // Winner determined
            
            // Calculate the exact angle to land on the predetermined segment
            const segmentAngle = 360 / theWheel.numSegments;
            const targetAngle = (winnerIndex * segmentAngle) + (segmentAngle / 2);
            
            // Add many rotations for dramatic effect
            const additionalSpins = Math.floor(Math.random() * 8) + 8;
            const finalRotation = (360 * additionalSpins) + targetAngle;
            
            // Set the wheel to stop at the predetermined segment
            theWheel.animation.stopAngle = finalRotation;
            
            // Store the winner data BEFORE starting animation
            theWheel.winnerData = winnerItem;
            theWheel.winnerIndex = winnerIndex;
            
            // Reset pin tracking for proper sound triggering
            lastPinNumber = -1;
            
            // Start the animation - Winwheel will handle sounds automatically
            theWheel.startAnimation();
            
            // Update spins counter immediately (already decremented on server)
            updateSpinsCounter();
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
            // alertPrize called
            
            // Clear any tick interval
            if (tickInterval) {
                clearInterval(tickInterval);
                tickInterval = null;
            }
            
            const winner = theWheel.winnerData || indicatedSegment.itemData;
            
            if (!winner) {
                console.error('No winner data found!');
                resetSpinButton();
                return;
            }

            // Processing winner

            // Play win sound for actual prizes (not "Try Again")
            if (winner.name !== 'Try Again' && winSoundEnabled) {
                playWinSound();
            }

            // Handle different types of wins
            if (winner.name === 'Try Again') {
                showTryAgainModal();
            } else {
                // All prizes except "Try Again" redirect to reward system
                storeWinnerDataAndRedirect(winner);
            }

            // Reset button after delay
            setTimeout(() => {
                resetSpinButton();
            }, 3000);
        }

        // Store winner data and redirect to reward system
        function storeWinnerDataAndRedirect(winner) {
            // Storing winner data and showing modal
            
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
                // Store winner response
                if (data.success) {
                    // Store redirect data for later use
                    window.rewardRedirectData = {
                        type: data.redirect_type,
                        url: data.redirect_url,
                        token: data.api_token,
                        apiUrl: data.api_url
                    };
                    
                    // Show the modal first
                    showBonusWinModal(winner);
                } else {
                    console.error('Failed to store winner data:', data.message);
                    showRegularWinModal(winner);
                }
            })
            .catch(error => {
                console.error('Error storing winner data:', error);
                showRegularWinModal(winner);
            });
        }

        // Show bonus win modal with redirect functionality
        function showBonusWinModal(winner) {
            const modalContent = document.querySelector('#winModal .modal-content');
            const modalPrize = document.getElementById('modalPrize');
            const winTitle = document.querySelector('.win-title');
            const collectButton = document.getElementById('collectPrizeBtn');
            
            winTitle.innerHTML = '🎉 CONGRATULATIONS! 🎉';
            modalPrize.textContent = `You Won: ${winner.name}${winner.prize > 0 ? ' - ¥' + parseFloat(winner.prize).toLocaleString() : ''}`;
            collectButton.textContent = 'Claim Reward';
            collectButton.className = 'btn btn-success btn-sm';
            
            // Style the modal - use same dark background as regular modal
            winTitle.style.color = '#ffd700';
            modalPrize.style.color = '#28a745';
            modalContent.style.background = '';
            modalContent.style.border = '';
            
            // Set click handler for claim button - IMPORTANT: Must handle modal close AND redirect
            collectButton.onclick = function() {
                // Hide modal first
                const modal = bootstrap.Modal.getInstance(document.getElementById('winModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Redirect to claim page after a short delay to allow modal to close
                setTimeout(() => {
                    handlePrizeRedirect(winner);
                }, 300);
            };
            
            // Show modal with static backdrop
            const winModal = new bootstrap.Modal(document.getElementById('winModal'), {
                backdrop: 'static',
                keyboard: false
            });
            winModal.show();
        }

        // Handle prize redirect with external API support
        async function handlePrizeRedirect(winner) {
            try {
                // Store winner data and get redirect info
                const formData = new FormData();
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
                formData.append('winner_data', JSON.stringify({
                    name: winner.name,
                    prize: winner.prize || 0,
                    type: winner.type || 'product'
                }));
                
                const response = await fetch('<?= base_url('store-winner') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.redirect_type === 'external') {
                        // External redirect with API data and winner data
                        redirectToExternalSite(data.redirect_url, data.api_token, data.api_url, {
                            name: winner.name,
                            prize: winner.prize || 0,
                            type: winner.type || 'product',
                            timestamp: Math.floor(Date.now() / 1000)
                        });
                    } else {
                        // Internal redirect to reward page
                        window.location.href = data.redirect_url;
                    }
                } else {
                    console.error('Failed to store winner data:', data.message);
                    // Fallback to internal redirect
                    window.location.href = '<?= base_url('reward') ?>';
                }
            } catch (error) {
                console.error('Error handling redirect:', error);
                // Fallback to internal redirect
                window.location.href = '<?= base_url('reward') ?>';
            }
        }

        // Redirect to external site with POST data
        function redirectToExternalSite(redirectUrl, apiToken, apiUrl, sessionData) {
            // Redirecting to external domain
            
            // Create a form to POST data to external URL
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = redirectUrl;
            form.target = '_self';
            form.style.display = 'none';
            
            const fields = {
                'api_token': apiToken,
                'api_url': apiUrl,
                'source': 'fortune_wheel',
                'timestamp': new Date().toISOString()
            };
            
            // Add winner data if provided
            if (sessionData) {
                fields['winner_data'] = JSON.stringify(sessionData);
            }
            
            Object.keys(fields).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // Show regular win modal
        function showRegularWinModal(winner) {
            const modalContent = document.querySelector('#winModal .modal-content');
            const modalPrize = document.getElementById('modalPrize');
            const winTitle = document.querySelector('.win-title');
            const collectButton = document.getElementById('collectPrizeBtn');
            
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
            
            const winModal = new bootstrap.Modal(document.getElementById('winModal'), {
                backdrop: 'static',
                keyboard: false
            });
            winModal.show();
        }

        // Show try again modal
        function showTryAgainModal() {
            const modalContent = document.querySelector('#winModal .modal-content');
            const modalPrize = document.getElementById('modalPrize');
            const winTitle = document.querySelector('.win-title');
            const collectButton = document.getElementById('collectPrizeBtn');
            
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
            
            const winModal = new bootstrap.Modal(document.getElementById('winModal'), {
                backdrop: 'static',
                keyboard: false
            });
            winModal.show();
        }

        // Show no spins modal
        function showNoSpinsModal() {
            const modalContent = document.querySelector('#winModal .modal-content');
            const modalPrize = document.getElementById('modalPrize');
            const winTitle = document.querySelector('.win-title');
            const collectButton = document.getElementById('collectPrizeBtn');
            
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
            
            const winModal = new bootstrap.Modal(document.getElementById('winModal'), {
                backdrop: 'static',
                keyboard: false
            });
            winModal.show();
        }

        // NEW function to update spin button state
        function updateSpinButton() {
            const spinButton = document.getElementById('spinButton');
            const spinButtonText = document.getElementById('spinButtonText');

            if (!wheelSpinning) {
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
        }

        // Reset spin button - UPDATED
        function resetSpinButton() {
            wheelSpinning = false;
            updateSpinButton();
        }

        // Update spins counter - UPDATED
        function updateSpinsCounter() {
            const spinsCount = document.getElementById('spinsCount');
            const freeSpinsText = document.getElementById('freeSpinsText');
            
            if (spinsCount) {
                spinsCount.textContent = spinsRemaining;
            }
            if (freeSpinsText) {
                freeSpinsText.textContent = spinsRemaining;
            }
            
            // Also update button state
            updateSpinButton();
        }

        // Check if sounds are ready
        function areSoundsReady() {
            let tickReady = true;
            let winReady = true;
            
            <?php if (isset($spin_sound) && !empty($spin_sound['sound_file']) && $spin_sound['enabled']): ?>
            tickReady = tickSoundBuffer !== null;
            <?php endif; ?>
            
            <?php if (isset($win_sound) && !empty($win_sound['sound_file']) && $win_sound['enabled']): ?>
            winReady = winSoundBuffer !== null;
            <?php endif; ?>
            
            return tickReady && winReady;
        }

        // Sound functions
        function playTickSound() {
            if (!tickSoundEnabled || !tickSoundBuffer || !tickSoundContext) return;
            
            try {
                if (tickSoundContext.state === 'suspended') {
                    tickSoundContext.resume();
                }
                
                const source = tickSoundContext.createBufferSource();
                source.buffer = tickSoundBuffer;
                
                const gainNode = tickSoundContext.createGain();
                gainNode.gain.value = tickSoundVolume;
                
                source.connect(gainNode);
                gainNode.connect(tickSoundContext.destination);
                
                source.start(0);
                
                source.onended = function() {
                    source.disconnect();
                    gainNode.disconnect();
                };
                
            } catch (error) {
                console.error('Error playing tick sound:', error);
            }
        }

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

        // Debug functions
        function testSpin() {
            // Test spin triggered
            // Current state
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