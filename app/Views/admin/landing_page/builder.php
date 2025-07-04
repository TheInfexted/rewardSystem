<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gold"><?= t('Admin.landing.title') ?></h1>
        <div class="btn-group">
            <a href="<?= base_url('/') ?>" target="_blank" class="btn btn-gold">
                <i class="bi bi-eye"></i> <?= t('Admin.landing.preview') ?>
            </a>
            <button type="button" class="btn btn-warning" onclick="cleanupImages()">
                <i class="bi bi-trash"></i> <?= t('Admin.landing.cleanup') ?>
            </button>
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#bonusSettingsModal">
                <i class="bi bi-gear"></i> <?= t('Admin.bonus.modal.title') ?>
            </button>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner-border text-gold" role="status">
                <span class="visually-hidden"><?= t('Admin.common.loading') ?></span>
            </div>
            <p class="text-gold mt-3"><?= t('Admin.landing.saving') ?></p>
        </div>
    </div>

    <form id="landingPageForm" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="row">
            <!-- Header Section -->
            <div class="col-md-6">
                <div class="card bg-dark border-secondary mb-4">
                    <div class="card-header">
                        <h5 class="text-gold mb-0"><i class="bi bi-layout-text-window-reverse"></i> <?= t('Admin.landing.sections.header') ?></h5>
                    </div>
                    <div class="card-body">
                        <!-- Header Text -->
                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.landing.fields.header_text') ?></label>
                            <textarea name="header_text" class="form-control bg-secondary text-light border-0" 
                                      rows="3" placeholder="<?= t('Admin.landing.fields.header_text_placeholder', [], 'Enter header text...') ?>"><?= esc($landing_data['header_text'] ?? '') ?></textarea>
                            <small class="text-muted"><?= t('Admin.landing.fields.header_text_help') ?></small>
                        </div>

                        <!-- Header Media 1 (Image/Video) -->
                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.landing.fields.header_media_1') ?></label>
                            <div class="media-upload-container" data-field="header_media_1">
                                <?php if (!empty($landing_data['header_media_1'])): ?>
                                    <div class="current-media mb-2">
                                        <div class="current-media-card">
                                            <?php if (($landing_data['header_media_1_type'] ?? 'image') === 'video'): ?>
                                                <!-- Video Display -->
                                                <video class="preview-media" controls>
                                                    <source src="<?= base_url('uploads/landing/' . $landing_data['header_media_1']) ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <div class="media-type-badge">
                                                    <span class="badge bg-primary"><i class="bi bi-play-fill"></i> Video</span>
                                                </div>
                                            <?php else: ?>
                                                <!-- Image Display -->
                                                <img src="<?= base_url('uploads/landing/' . $landing_data['header_media_1']) ?>" 
                                                    class="img-thumbnail preview-media" alt="Header Media 1">
                                                <div class="media-type-badge">
                                                    <span class="badge bg-success"><i class="bi bi-image"></i> Image</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="media-actions mt-2">
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="removeImage('header_media_1')">
                                                    <i class="bi bi-trash"></i> <?= t('Admin.landing.media.remove') ?>
                                                </button>
                                                <a href="<?= base_url('uploads/landing/' . $landing_data['header_media_1']) ?>" 
                                                target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i> <?= t('Admin.landing.media.view_full') ?>
                                                </a>
                                            </div>
                                            <div class="media-filename">
                                                <small class="text-success">
                                                    <i class="bi bi-file-earmark"></i> <?= $landing_data['header_media_1'] ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="media-upload-area">
                                    <input type="file" name="header_media_1" class="form-control bg-secondary text-light border-0" 
                                        accept="image/*,video/*" onchange="previewMedia(this, 'header_media_1')">
                                    <small class="text-muted">
                                        <?= !empty($landing_data['header_media_1']) ? t('Admin.landing.media.upload_new') : t('Admin.landing.media.upload', ['type' => 'header']) ?>
                                    </small>
                                    <small class="text-info d-block mt-1">
                                        <i class="bi bi-info-circle"></i> <?= t('Admin.landing.media.requirements') ?>:
                                        <ul class="mb-0 ps-4">
                                            <li><?= t('Admin.landing.media.image_formats') ?></li>
                                            <li><?= t('Admin.landing.media.video_formats') ?></li>
                                        </ul>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Header Media 2 (Image/Video) -->
                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.landing.fields.header_media_2') ?></label>
                            <div class="media-upload-container" data-field="header_media_2">
                                <?php if (!empty($landing_data['header_media_2'])): ?>
                                    <div class="current-media mb-2">
                                        <div class="current-media-card">
                                            <?php if (($landing_data['header_media_2_type'] ?? 'image') === 'video'): ?>
                                                <!-- Video Display -->
                                                <video class="preview-media" controls>
                                                    <source src="<?= base_url('uploads/landing/' . $landing_data['header_media_2']) ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <div class="media-type-badge">
                                                    <span class="badge bg-primary"><i class="bi bi-play-fill"></i> Video</span>
                                                </div>
                                            <?php else: ?>
                                                <!-- Image Display -->
                                                <img src="<?= base_url('uploads/landing/' . $landing_data['header_media_2']) ?>" 
                                                    class="img-thumbnail preview-media" alt="Header Media 2">
                                                <div class="media-type-badge">
                                                    <span class="badge bg-success"><i class="bi bi-image"></i> Image</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="media-actions mt-2">
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="removeImage('header_media_2')">
                                                    <i class="bi bi-trash"></i> <?= t('Admin.landing.media.remove') ?>
                                                </button>
                                                <a href="<?= base_url('uploads/landing/' . $landing_data['header_media_2']) ?>" 
                                                target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i> <?= t('Admin.landing.media.view_full') ?>
                                                </a>
                                            </div>
                                            <div class="media-filename">
                                                <small class="text-success">
                                                    <i class="bi bi-file-earmark"></i> <?= $landing_data['header_media_2'] ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="media-upload-area">
                                    <input type="file" name="header_media_2" class="form-control bg-secondary text-light border-0" 
                                        accept="image/*,video/*" onchange="previewMedia(this, 'header_media_2')">
                                    <small class="text-muted">
                                        <?= !empty($landing_data['header_media_2']) ? t('Admin.landing.media.upload_new') : t('Admin.landing.media.upload', ['type' => 'header']) ?>
                                    </small>
                                    <small class="text-info d-block mt-1">
                                        <i class="bi bi-info-circle"></i> <?= t('Admin.landing.media.requirements') ?>:
                                        <ul class="mb-0 ps-4">
                                            <li><?= t('Admin.landing.media.image_formats') ?></li>
                                            <li><?= t('Admin.landing.media.video_formats') ?></li>
                                        </ul>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Section -->
            <div class="col-md-6">
                <div class="card bg-dark border-secondary mb-4">
                    <div class="card-header">
                        <h5 class="text-gold mb-0"><i class="bi bi-layout-text-window"></i> <?= t('Admin.landing.sections.footer') ?></h5>
                    </div>
                    <div class="card-body">
                        <!-- Footer Text -->
                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.landing.fields.footer_text') ?></label>
                            <textarea name="footer_text" class="form-control bg-secondary text-light border-0" 
                                    rows="3" placeholder="<?= t('Admin.landing.fields.footer_text_placeholder', [], 'Enter footer text...') ?>"><?= esc($landing_data['footer_text'] ?? '') ?></textarea>
                            <small class="text-muted"><?= t('Admin.landing.fields.footer_text_help') ?></small>
                        </div>

                        <!-- Footer Media 1 (Image/Video) -->
                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.landing.fields.footer_media_1') ?></label>
                            <div class="media-upload-container" data-field="footer_media_1">
                                <?php if (!empty($landing_data['footer_media_1'])): ?>
                                    <div class="current-media mb-2">
                                        <div class="current-media-card">
                                            <?php if (($landing_data['footer_media_1_type'] ?? 'image') === 'video'): ?>
                                                <!-- Video Display -->
                                                <video class="preview-media" controls>
                                                    <source src="<?= base_url('uploads/landing/' . $landing_data['footer_media_1']) ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <div class="media-type-badge">
                                                    <span class="badge bg-primary"><i class="bi bi-play-fill"></i> Video</span>
                                                </div>
                                            <?php else: ?>
                                                <!-- Image Display -->
                                                <img src="<?= base_url('uploads/landing/' . $landing_data['footer_media_1']) ?>" 
                                                    class="img-thumbnail preview-media" alt="Footer Media 1">
                                                <div class="media-type-badge">
                                                    <span class="badge bg-success"><i class="bi bi-image"></i> Image</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="media-actions mt-2">
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="removeImage('footer_media_1')">
                                                    <i class="bi bi-trash"></i> <?= t('Admin.landing.media.remove') ?>
                                                </button>
                                                <a href="<?= base_url('uploads/landing/' . $landing_data['footer_media_1']) ?>" 
                                                target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i> <?= t('Admin.landing.media.view_full') ?>
                                                </a>
                                            </div>
                                            <div class="media-filename">
                                                <small class="text-success">
                                                    <i class="bi bi-file-earmark"></i> <?= $landing_data['footer_media_1'] ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="media-upload-area">
                                    <input type="file" name="footer_media_1" class="form-control bg-secondary text-light border-0" 
                                        accept="image/*,video/*" onchange="previewMedia(this, 'footer_media_1')">
                                    <small class="text-muted">
                                        <?= !empty($landing_data['footer_media_1']) ? t('Admin.landing.media.upload_new') : t('Admin.landing.media.upload', ['type' => 'footer']) ?>
                                    </small>
                                    <small class="text-info d-block mt-1">
                                        <i class="bi bi-info-circle"></i> <?= t('Admin.landing.media.requirements') ?>:
                                        <ul class="mb-0 ps-4">
                                            <li><?= t('Admin.landing.media.image_formats') ?></li>
                                            <li><?= t('Admin.landing.media.video_formats') ?></li>
                                        </ul>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Media 2 (Image/Video) -->
                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.landing.fields.footer_media_2') ?></label>
                            <div class="media-upload-container" data-field="footer_media_2">
                                <?php if (!empty($landing_data['footer_media_2'])): ?>
                                    <div class="current-media mb-2">
                                        <div class="current-media-card">
                                            <?php if (($landing_data['footer_media_2_type'] ?? 'image') === 'video'): ?>
                                                <!-- Video Display -->
                                                <video class="preview-media" controls>
                                                    <source src="<?= base_url('uploads/landing/' . $landing_data['footer_media_2']) ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <div class="media-type-badge">
                                                    <span class="badge bg-primary"><i class="bi bi-play-fill"></i> Video</span>
                                                </div>
                                            <?php else: ?>
                                                <!-- Image Display -->
                                                <img src="<?= base_url('uploads/landing/' . $landing_data['footer_media_2']) ?>" 
                                                    class="img-thumbnail preview-media" alt="Footer Media 2">
                                                <div class="media-type-badge">
                                                    <span class="badge bg-success"><i class="bi bi-image"></i> Image</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="media-actions mt-2">
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="removeImage('footer_media_2')">
                                                    <i class="bi bi-trash"></i> <?= t('Admin.landing.media.remove') ?>
                                                </button>
                                                <a href="<?= base_url('uploads/landing/' . $landing_data['footer_media_2']) ?>" 
                                                target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i> <?= t('Admin.landing.media.view_full') ?>
                                                </a>
                                            </div>
                                            <div class="media-filename">
                                                <small class="text-success">
                                                    <i class="bi bi-file-earmark"></i> <?= $landing_data['footer_media_2'] ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="media-upload-area">
                                    <input type="file" name="footer_media_2" class="form-control bg-secondary text-light border-0" 
                                        accept="image/*,video/*" onchange="previewMedia(this, 'footer_media_2')">
                                    <small class="text-muted">
                                        <?= !empty($landing_data['footer_media_2']) ? t('Admin.landing.media.upload_new') : t('Admin.landing.media.upload', ['type' => 'footer']) ?>
                                    </small>
                                    <small class="text-info d-block mt-1">
                                        <i class="bi bi-info-circle"></i> <?= t('Admin.landing.media.requirements') ?>:
                                        <ul class="mb-0 ps-4">
                                            <li><?= t('Admin.landing.media.image_formats') ?></li>
                                            <li><?= t('Admin.landing.media.video_formats') ?></li>
                                        </ul>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Free Spins Section -->
        <div class="col-md-6">
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header">
                    <h5 class="text-gold mb-0"><i class="bi bi-dice-3"></i> <?= t('Admin.landing.sections.free_spins') ?></h5>
                </div>
                <div class="card-body">
                    <!-- Free Spins Subtitle -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.fields.free_spins_subtitle') ?></label>
                        <textarea name="free_spins_subtitle" 
                                  class="form-control bg-secondary text-light border-0" 
                                  rows="2"
                                  placeholder="<?= t('Admin.landing.fields.free_spins_subtitle_placeholder', [], 'Try your luck now and win up to 120% BONUS!') ?>"><?= esc($landing_data['free_spins_subtitle'] ?? 'Try your luck now and win up to 120% BONUS!') ?></textarea>
                        <small class="text-muted"><?= t('Admin.landing.fields.free_spins_subtitle_help') ?></small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Welcome Screen Section -->
        <div class="col-md-6">
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header">
                    <h5 class="text-gold mb-0"><i class="bi bi-door-open"></i> <?= t('Admin.landing.sections.welcome') ?></h5>
                </div>
                <div class="card-body">
                    <!-- Welcome Image -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.fields.welcome_image') ?></label>
                        <div class="image-upload-container" data-field="welcome_image">
                            <?php if (!empty($landing_data['welcome_image'])): ?>
                                <div class="current-image mb-2">
                                    <div class="current-image-card">
                                        <img src="<?= base_url('uploads/landing/' . $landing_data['welcome_image']) ?>" 
                                             class="img-thumbnail preview-image" alt="Welcome Image">
                                        <div class="image-actions mt-2">
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="removeImage('welcome_image')">
                                                <i class="bi bi-trash"></i> <?= t('Admin.landing.image.remove') ?>
                                            </button>
                                            <a href="<?= base_url('uploads/landing/' . $landing_data['welcome_image']) ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-zoom-in"></i> <?= t('Admin.landing.image.view_full') ?>
                                            </a>
                                        </div>
                                        <div class="image-filename">
                                            <small class="text-success">
                                                <i class="bi bi-file-image"></i> <?= $landing_data['welcome_image'] ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="image-upload-area">
                                <input type="file" name="welcome_image" class="form-control bg-secondary text-light border-0" 
                                       accept="image/*" onchange="previewImage(this, 'welcome_image')">
                                <small class="text-muted d-block">
                                    <?= !empty($landing_data['welcome_image']) ? t('Admin.landing.image.upload_new') : t('Admin.landing.fields.welcome_image_upload', [], 'Upload welcome screen image') ?>
                                </small>
                                <small class="text-info d-block mt-1">
                                    <i class="bi bi-info-circle"></i> <?= t('Admin.landing.image.requirements') ?>:
                                    <ul class="mb-0 ps-4">
                                        <li><?= t('Admin.landing.image.max_size') ?></li>
                                        <li><?= t('Admin.landing.image.dimensions') ?></li>
                                        <li><?= t('Admin.landing.image.formats') ?></li>
                                    </ul>
                                </small>
                            </div>
                        </div>
                    </div>
        
                    <!-- Welcome Title -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.fields.welcome_title') ?></label>
                        <input type="text" name="welcome_title" 
                               class="form-control bg-secondary text-light border-0" 
                               value="<?= esc($landing_data['welcome_title'] ?? 'TAP TAP WIN') ?>"
                               placeholder="<?= t('Admin.landing.fields.welcome_title_placeholder', [], 'Enter welcome title...') ?>"
                               maxlength="255">
                        <small class="text-muted"><?= t('Admin.landing.fields.welcome_title_help') ?></small>
                    </div>
        
                    <!-- Welcome Subtitle -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.fields.welcome_subtitle') ?></label>
                        <input type="text" name="welcome_subtitle" 
                               class="form-control bg-secondary text-light border-0" 
                               value="<?= esc($landing_data['welcome_subtitle'] ?? 'Welcome to TapTapWin!') ?>"
                               placeholder="<?= t('Admin.landing.fields.welcome_subtitle_placeholder', [], 'Enter welcome subtitle...') ?>"
                               maxlength="255">
                        <small class="text-muted"><?= t('Admin.landing.fields.welcome_subtitle_help') ?></small>
                    </div>
        
                    <!-- Welcome Text -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.fields.welcome_text') ?></label>
                        <textarea name="welcome_text" 
                                  class="form-control bg-secondary text-light border-0" 
                                  rows="2"
                                  placeholder="<?= t('Admin.landing.fields.welcome_text_placeholder', [], 'Enter welcome message...') ?>"><?= esc($landing_data['welcome_text'] ?? 'Prizes for you to win!') ?></textarea>
                        <small class="text-muted"><?= t('Admin.landing.fields.welcome_text_help') ?></small>
                    </div>
        
                    <!-- Welcome Button Text -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.fields.welcome_button') ?></label>
                        <input type="text" name="welcome_button_text" 
                               class="form-control bg-secondary text-light border-0" 
                               value="<?= esc($landing_data['welcome_button_text'] ?? 'Click to Start!') ?>"
                               placeholder="<?= t('Admin.landing.fields.welcome_button_placeholder', [], 'Enter button text...') ?>"
                               maxlength="100">
                        <small class="text-muted"><?= t('Admin.landing.fields.welcome_button_help') ?></small>
                    </div>
        
                    <!-- Welcome Footer Text -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.fields.welcome_footer') ?></label>
                        <input type="text" name="welcome_footer_text" 
                               class="form-control bg-secondary text-light border-0" 
                               value="<?= esc($landing_data['welcome_footer_text'] ?? 'Please try our game before you leave!') ?>"
                               placeholder="<?= t('Admin.landing.fields.welcome_footer_placeholder', [], 'Enter footer text...') ?>"
                               maxlength="255">
                        <small class="text-muted"><?= t('Admin.landing.fields.welcome_footer_help') ?></small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Background Music Section -->
        <div class="col-md-6">
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header">
                    <h5 class="text-gold mb-0"><i class="bi bi-music-note-beamed"></i> <?= t('Admin.landing.sections.music') ?></h5>
                </div>
                <div class="card-body">
                    <?php 
                    $musicData = model('App\Models\LandingPageMusicModel')->getActiveMusic();
                    ?>
                    
                    <!-- Current Music -->
                    <?php if ($musicData && !empty($musicData['music_file'])): ?>
                        <div class="current-music mb-3">
                            <label class="form-label text-light"><?= t('Admin.landing.music.current') ?></label>
                            <div class="d-flex align-items-center">
                                <audio controls class="form-control bg-secondary">
                                    <source src="<?= base_url('uploads/music/' . $musicData['music_file']) ?>" type="audio/mpeg">
                                    <?= t('Admin.landing.audio_not_supported', [], 'Your browser does not support the audio element.') ?>
                                </audio>
                                <button type="button" class="btn btn-danger ms-2" onclick="deleteMusic()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Upload New Music -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.music.upload') ?></label>
                        <input type="file" name="music_file" id="music_file" 
                               class="form-control bg-secondary text-light border-0" 
                               accept="audio/mp3,audio/mpeg">
                        <small class="text-muted"><?= t('Admin.landing.music.upload_help') ?></small>
                    </div>
                    
                    <!-- Volume Control -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.music.volume') ?></label>
                        <div class="d-flex align-items-center">
                            <input type="range" name="volume" id="volume" 
                                   class="form-range flex-grow-1" 
                                   min="0" max="1" step="0.1" 
                                   value="<?= $musicData['volume'] ?? 0.5 ?>">
                            <span id="volumeValue" class="ms-3 text-gold"><?= ($musicData['volume'] ?? 0.5) * 100 ?>%</span>
                        </div>
                    </div>
                    
                    <!-- Options -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="autoplay" id="autoplay" 
                                   class="form-check-input" 
                                   <?= ($musicData['autoplay'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label text-light" for="autoplay">
                                <?= t('Admin.landing.music.autoplay') ?>
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" name="loop" id="loop" 
                                   class="form-check-input" 
                                   <?= ($musicData['loop'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label text-light" for="loop">
                                <?= t('Admin.landing.music.loop') ?>
                            </label>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-gold" onclick="saveMusic()">
                        <i class="bi bi-save"></i> <?= t('Admin.landing.music.save') ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Wheel Sound Effects Section -->
        <div class="col-md-6">
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header">
                    <h5 class="text-gold mb-0"><i class="bi bi-volume-up"></i> <?= t('Admin.landing.sections.wheel_sound') ?></h5>
                </div>
                <div class="card-body">
                    <?php 
                    $spinSound = model('App\Models\WheelSoundModel')->getActiveSound('spin');
                    ?>
                    
                    <!-- Current Spinning Sound -->
                    <?php if ($spinSound && !empty($spinSound['sound_file'])): ?>
                        <div class="current-sound mb-3">
                            <label class="form-label text-light"><?= t('Admin.landing.sound.current_spin') ?></label>
                            <div class="d-flex align-items-center">
                                <audio controls class="form-control bg-secondary" style="height: 40px;">
                                    <source src="<?= base_url('uploads/sounds/' . $spinSound['sound_file']) ?>" type="audio/mpeg">
                                    <?= t('Admin.landing.audio_not_supported', [], 'Your browser does not support the audio element.') ?>
                                </audio>
                                <button type="button" class="btn btn-danger ms-2" onclick="deleteSpinSound()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Upload New Sound -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.sound.upload_spin') ?></label>
                        <input type="file" name="spin_sound_file" id="spin_sound_file" 
                               class="form-control bg-secondary text-light border-0" 
                               accept="audio/mp3,audio/mpeg,audio/wav,audio/ogg">
                        <small class="text-muted"><?= t('Admin.landing.sound.spin_help') ?></small>
                    </div>
                    
                    <!-- Volume Control -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.sound.volume') ?></label>
                        <div class="d-flex align-items-center">
                            <input type="range" name="spin_volume" id="spin_volume" 
                                   class="form-range flex-grow-1" 
                                   min="0" max="1" step="0.1" 
                                   value="<?= $spinSound['volume'] ?? 0.7 ?>">
                            <span id="spinVolumeValue" class="ms-3 text-gold"><?= ($spinSound['volume'] ?? 0.7) * 100 ?>%</span>
                        </div>
                    </div>
                    
                    <!-- Enable/Disable -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="spin_enabled" id="spin_enabled" 
                                   class="form-check-input" 
                                   <?= ($spinSound['enabled'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label text-light" for="spin_enabled">
                                <?= t('Admin.landing.sound.enable_spin') ?>
                            </label>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-gold" onclick="saveSpinSound()">
                        <i class="bi bi-save"></i> <?= t('Admin.landing.sound.save_spin') ?>
                    </button>
                    
                    <div class="mt-3">
                        <small class="text-info">
                            <i class="bi bi-info-circle"></i> 
                            <?= t('Admin.landing.sound.spin_info') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Win Sound Section -->
        <div class="col-md-6">
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header">
                    <h5 class="text-gold mb-0"><i class="bi bi-award-fill"></i> <?= t('Admin.landing.sections.win_sound') ?></h5>
                </div>
                <div class="card-body">
                    <?php 
                    $winSound = model('App\Models\WheelSoundModel')->getActiveSound('win');
                    ?>
                    
                    <!-- Current Win Sound -->
                    <?php if ($winSound && !empty($winSound['sound_file'])): ?>
                        <div class="current-sound mb-3">
                            <label class="form-label text-light"><?= t('Admin.landing.sound.current_win') ?></label>
                            <div class="d-flex align-items-center">
                                <audio controls class="form-control bg-secondary" style="height: 40px;">
                                    <source src="<?= base_url('uploads/sounds/' . $winSound['sound_file']) ?>" type="audio/mpeg">
                                    <?= t('Admin.landing.audio_not_supported', [], 'Your browser does not support the audio element.') ?>
                                </audio>
                                <button type="button" class="btn btn-danger ms-2" onclick="deleteWinSound()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Upload New Win Sound -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.sound.upload_win') ?></label>
                        <input type="file" name="win_sound_file" id="win_sound_file" 
                               class="form-control bg-secondary text-light border-0" 
                               accept="audio/mp3,audio/mpeg,audio/wav,audio/ogg">
                        <small class="text-muted"><?= t('Admin.landing.sound.win_help') ?></small>
                    </div>
                    
                    <!-- Volume Control -->
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.landing.sound.win_volume') ?></label>
                        <div class="d-flex align-items-center">
                            <input type="range" name="win_volume" id="win_volume" 
                                   class="form-range flex-grow-1" 
                                   min="0" max="1" step="0.1" 
                                   value="<?= $winSound['volume'] ?? 0.8 ?>">
                            <span id="winVolumeValue" class="ms-3 text-gold"><?= ($winSound['volume'] ?? 0.8) * 100 ?>%</span>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-gold" onclick="saveWinSound()">
                        <i class="bi bi-save"></i> <?= t('Admin.landing.sound.save_win') ?>
                    </button>
                 </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="card bg-dark border-secondary mb-4">
            <div class="card-body">
                <h6 class="text-gold"><i class="bi bi-info-circle"></i> <?= t('Admin.landing.info.structure') ?></h6>
                <p class="text-light mb-0"><?= t('Admin.landing.info.structure_desc') ?></p>
                <ul class="text-light mb-0">
                    <li><?= t('Admin.landing.info.structure_header') ?></li>
                    <li><?= t('Admin.landing.info.structure_wheel') ?> <a href="<?= base_url('admin/wheel') ?>" class="text-gold"><?= t('Admin.nav.wheel_items') ?></a>)</li>
                    <li><?= t('Admin.landing.info.structure_footer') ?></li>
                </ul>
                <div class="mt-3">
                    <small class="text-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong><?= t('Admin.landing.info.note') ?>:</strong> <?= t('Admin.landing.info.note_text') ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-gold btn-lg" id="saveButton">
                <i class="bi bi-save"></i> <span class="button-text"><?= t('Admin.landing.save_settings') ?></span>
            </button>
        </div>
    </form>
</div>

<!-- Bonus Settings Modal -->
<div class="modal fade" id="bonusSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-gold">
            <div class="modal-header border-gold">
                <h5 class="modal-title text-gold"><?= t('Admin.bonus.modal.title') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bonusSettingsForm" action="<?= base_url('admin/bonus/settings') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-gold"><?= t('Admin.bonus.modal.redirect_url') ?></label>
                        <input type="url" name="bonus_redirect_url" id="bonus_redirect_url" 
                               class="form-control bg-dark text-light border-gold"
                               placeholder="<?= t('Admin.bonus.modal.redirect_placeholder') ?>">
                        <small class="text-muted"><?= t('Admin.bonus.modal.redirect_help') ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="bonus_claim_enabled" id="bonus_claim_enabled">
                            <label class="form-check-label text-light" for="bonus_claim_enabled">
                                <?= t('Admin.bonus.modal.enable_claims') ?>
                            </label>
                        </div>
                        <small class="text-muted"><?= t('Admin.bonus.modal.enable_help') ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-gold"><?= t('Admin.bonus.modal.terms') ?></label>
                        <textarea name="bonus_terms_text" id="bonus_terms_text" 
                                  class="form-control bg-dark text-light border-gold" rows="3"
                                  placeholder="<?= t('Admin.bonus.modal.terms_placeholder') ?>"></textarea>
                        <small class="text-muted"><?= t('Admin.bonus.modal.terms_help') ?></small>
                    </div>
                </div>
                <div class="modal-footer border-gold">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?= t('Admin.bonus.modal.cancel') ?>
                    </button>
                    <button type="submit" class="btn btn-gold">
                        <?= t('Admin.bonus.modal.save') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load bonus settings when modal opens
        document.getElementById('bonusSettingsModal').addEventListener('show.bs.modal', function() {
            loadBonusSettings();
        });

        // Handle bonus settings form submission
        document.getElementById('bonusSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveBonusSettings();
        });
    });

    // Load bonus settings via AJAX
    function loadBonusSettings() {
        fetch('<?= base_url('admin/bonus/get-settings') ?>', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('bonus_redirect_url').value = data.settings.bonus_redirect_url || '';
                document.getElementById('bonus_claim_enabled').checked = data.settings.bonus_claim_enabled === '1';
                document.getElementById('bonus_terms_text').value = data.settings.bonus_terms_text || '';
            } else {
                showAlert('error', data.message || 'Failed to load settings.');
            }
        })
        .catch(error => {
            console.error('Error loading settings:', error);
            showAlert('error', 'Error loading settings.');
        });
    }

    // Save bonus settings via AJAX
    function saveBonusSettings() {
        const form = document.getElementById('bonusSettingsForm');
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server responded with ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert('success', data.message || 'Settings updated successfully!');
                bootstrap.Modal.getInstance(document.getElementById('bonusSettingsModal')).hide();
            } else {
                showAlert('error', data.message || 'Failed to update settings');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Network error or invalid response.');
        });
    }

    // Alert function
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert alert at the top of the container
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHTML);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
    // Translations for JavaScript
    const translations = {
        saving: '<?= t('Admin.common.saving', [], 'Saving...') ?>',
        success: '<?= t('Admin.common.success') ?>',
        error: '<?= t('Admin.common.error') ?>',
        fileError: '<?= t('Admin.landing.file_error', [], 'Please select a valid image file.') ?>',
        fileTooLarge: '<?= t('Admin.landing.file_too_large', [], 'Image file too large. Maximum size is 1MB.') ?>',
        removeConfirm: '<?= t('Admin.landing.remove_confirm', [], 'Are you sure you want to remove this image? This action cannot be undone.') ?>',
        cleanupConfirm: '<?= t('Admin.landing.cleanup_confirm', [], 'This will delete all unused images from the server. Continue?') ?>',
        deleteMusic: '<?= t('Admin.landing.delete_music_confirm', [], 'Are you sure you want to remove the background music?') ?>',
        deleteSound: '<?= t('Admin.landing.delete_sound_confirm', [], 'Are you sure you want to remove this sound?') ?>',
        networkError: '<?= t('Admin.common.network_error', [], 'Network error occurred. Please try again.') ?>',
        newImageSelected: '<?= t('Admin.landing.new_image_selected', [], 'New image selected:') ?>',
        dimensions: '<?= t('Admin.landing.dimensions', [], 'Dimensions') ?>',
        replaceNote: '<?= t('Admin.landing.replace_note', [], 'This will replace the current image when saved.') ?>',
        removing: '<?= t('Admin.landing.removing', [], 'Removing...') ?>',
        failedToRemove: '<?= t('Admin.landing.failed_to_remove', [], 'Failed to remove image') ?>',
        failedToCleanup: '<?= t('Admin.landing.failed_to_cleanup', [], 'Failed to cleanup images') ?>',
        languageChanged: '<?= t('Admin.common.language_changed', [], 'Language changed successfully!') ?>'
    };

    // Form submission with loading
    document.getElementById('landingPageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading overlay
        showLoading(true);
        
        // Update button state
        const saveButton = document.getElementById('saveButton');
        const buttonText = saveButton.querySelector('.button-text');
        const originalText = buttonText.textContent;
        
        saveButton.disabled = true;
        buttonText.textContent = translations.saving;
        
        // Prepare form data
        const formData = new FormData(this);
        
        // Submit form
        fetch('<?= base_url('admin/landing-page/save') ?>', {
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
                showAlert('success', data.message);
                // Reload page after 1.5 seconds to show updated images
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            showLoading(false);
            console.error('Error:', error);
            showAlert('error', translations.networkError);
        })
        .finally(() => {
            // Reset button state
            saveButton.disabled = false;
            buttonText.textContent = originalText;
        });
    });
    
    // Show/hide loading overlay
    function showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        overlay.style.display = show ? 'flex' : 'none';
    }

    // Media preview functionality for both images and videos
    function previewMedia(input, fieldName) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const isImage = file.type.startsWith('image/');
            const isVideo = file.type.startsWith('video/');
            
            // Validate file type
            if (!isImage && !isVideo) {
                showAlert('error', 'Please select a valid image or video file.');
                input.value = '';
                return;
            }
            
            // Validate file size
            const maxImageSize = 5 * 1024 * 1024; // 5MB for images
            const maxVideoSize = 50 * 1024 * 1024; // 50MB for videos
            
            if (isImage && file.size > maxImageSize) {
                showAlert('error', 'Image file too large. Maximum size is 5MB.');
                input.value = '';
                return;
            }
            
            if (isVideo && file.size > maxVideoSize) {
                showAlert('error', 'Video file too large. Maximum size is 50MB.');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = input.closest('.media-upload-container');
                let previewContainer = container.querySelector('.new-media-preview');
                
                if (!previewContainer) {
                    previewContainer = document.createElement('div');
                    previewContainer.className = 'new-media-preview mt-2';
                    container.appendChild(previewContainer);
                }
                
                let mediaElement = '';
                let mediaTypeLabel = '';
                let sizeLabel = '';
                
                if (isImage) {
                    mediaElement = `<img src="${e.target.result}" class="preview-media mt-2" style="max-height: 150px;" alt="Preview">`;
                    mediaTypeLabel = '<span class="badge bg-success"><i class="bi bi-image"></i> Image</span>';
                    sizeLabel = `(${(file.size / 1024).toFixed(1)} KB)`;
                } else {
                    mediaElement = `<video class="preview-media mt-2" controls style="max-height: 150px;">
                        <source src="${e.target.result}" type="${file.type}">
                    </video>`;
                    mediaTypeLabel = '<span class="badge bg-primary"><i class="bi bi-play-fill"></i> Video</span>';
                    sizeLabel = `(${(file.size / 1024 / 1024).toFixed(1)} MB)`;
                }
                
                previewContainer.innerHTML = `
                    <div class="alert alert-info">
                        <strong>New media selected:</strong> ${mediaTypeLabel}<br>
                        ${mediaElement}
                        <div class="mt-2">
                            <small class="text-muted">${file.name} ${sizeLabel}</small><br>
                            <small class="text-warning">This will replace the current media when saved.</small>
                        </div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }
    }
    
    // Image preview functionality
    function previewImage(input, fieldName) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                showAlert('error', translations.fileError);
                input.value = '';
                return;
            }
            
            // Validate file size (1MB)
            if (file.size > 1 * 1024 * 1024) {
                showAlert('error', translations.fileTooLarge);
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const container = input.closest('.image-upload-container');
                    let previewContainer = container.querySelector('.new-image-preview');
                    
                    if (!previewContainer) {
                        previewContainer = document.createElement('div');
                        previewContainer.className = 'new-image-preview mt-2';
                        container.appendChild(previewContainer);
                    }
                    
                    previewContainer.innerHTML = `
                        <div class="alert alert-info">
                            <strong>${translations.newImageSelected}</strong><br>
                            <img src="${e.target.result}" class="img-thumbnail mt-2" style="max-height: 100px;" alt="Preview">
                            <div class="mt-2">
                                <small class="text-muted">${file.name} (${(file.size / 1024).toFixed(1)} KB)</small><br>
                                <small class="text-muted">${translations.dimensions}: ${img.width} × ${img.height} pixels</small><br>
                                <small class="text-warning">${translations.replaceNote}</small>
                            </div>
                        </div>
                    `;
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
    
    // Remove image function
    function removeImage(fieldName) {
        if (!confirm(translations.removeConfirm)) {
            return;
        }
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        formData.append('field', fieldName);
        
        // Show loading for this specific image
        const container = document.querySelector(`[data-field="${fieldName}"]`);
        const currentImage = container.querySelector('.current-image, .current-media');
        
        if (currentImage) {
            currentImage.style.opacity = '0.5';
            currentImage.innerHTML += `<div class="text-center"><small class="text-warning">${translations.removing}</small></div>`;
        }
        
        fetch('<?= base_url('admin/landing-page/remove-image') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                
                // Check if reload flag is set, then reload the page
                if (data.reload) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000); // Wait 1 second to show the success message
                } else {
                    // Fallback: remove the image from display if no reload
                    if (currentImage) {
                        currentImage.remove();
                    }
                }
            } else {
                showAlert('error', data.message);
                // Restore original state
                if (currentImage) {
                    currentImage.style.opacity = '1';
                    // Remove the "Removing..." text
                    const removeText = currentImage.querySelector('.text-warning');
                    if (removeText) {
                        removeText.parentElement.remove();
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', translations.failedToRemove);
            // Restore original state
            if (currentImage) {
                currentImage.style.opacity = '1';
            }
        });
    }
    
    // Cleanup orphaned images
    function cleanupImages() {
        if (!confirm(translations.cleanupConfirm)) {
            return;
        }
        
        fetch('<?= base_url('admin/landing-page/cleanup-images') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', translations.failedToCleanup);
        });
    }
    
    // Show alert messages
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <i class="bi ${iconClass}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Volume slider update
    document.getElementById('volume')?.addEventListener('input', function() {
        document.getElementById('volumeValue').textContent = Math.round(this.value * 100) + '%';
    });
    
    // Save music settings
    function saveMusic() {
        const saveButton = document.querySelector('button[onclick="saveMusic()"]');
        const originalText = saveButton.innerHTML;
    
        // Change button text to indicate saving
        saveButton.disabled = true;
        saveButton.innerHTML = '<i class="bi bi-save"></i> ' + translations.saving;
    
        const formData = new FormData();
        const musicFile = document.getElementById('music_file').files[0];
    
        if (musicFile) {
            formData.append('music_file', musicFile);
        }
    
        formData.append('volume', document.getElementById('volume').value);
        formData.append('autoplay', document.getElementById('autoplay').checked ? 1 : 0);
        formData.append('loop', document.getElementById('loop').checked ? 1 : 0);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
        // Show loading
        showLoading(true);
    
        fetch('<?= base_url('admin/landing-page/save-music') ?>', {
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
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            showLoading(false);
            console.error('Error:', error);
            showAlert('error', translations.networkError);
        })
        .finally(() => {
            // Restore original button text
            saveButton.disabled = false;
            saveButton.innerHTML = originalText;
        });
    }
    
    // Delete music
    function deleteMusic() {
        if (confirm(translations.deleteMusic)) {
            showLoading(true);
            
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            
            fetch('<?= base_url('admin/landing-page/delete-music') ?>', {
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
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                showLoading(false);
                console.error('Error:', error);
                showAlert('error', translations.networkError);
            });
        }
    }
    
    // Volume slider for spin sound
    document.getElementById('spin_volume')?.addEventListener('input', function() {
        document.getElementById('spinVolumeValue').textContent = Math.round(this.value * 100) + '%';
    });
    
    // Save spin sound settings
    function saveSpinSound() {
        const formData = new FormData();
        const soundFile = document.getElementById('spin_sound_file').files[0];
        
        if (soundFile) {
            formData.append('spin_sound_file', soundFile);
        }
        
        formData.append('spin_volume', document.getElementById('spin_volume').value);
        formData.append('spin_enabled', document.getElementById('spin_enabled').checked ? 1 : 0);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        showLoading(true);
        
        fetch('<?= base_url('admin/landing-page/save-spin-sound') ?>', {
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
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            showLoading(false);
            console.error('Error:', error);
            showAlert('error', translations.networkError);
        });
    }
    
    // Delete spin sound
    function deleteSpinSound() {
        if (confirm(translations.deleteSound)) {
            showLoading(true);
            
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            
            fetch('<?= base_url('admin/landing-page/delete-spin-sound') ?>', {
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
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                showLoading(false);
                console.error('Error:', error);
                showAlert('error', translations.networkError);
            });
        }
    }
    
    // Volume slider for win sound
    document.getElementById('win_volume')?.addEventListener('input', function() {
        document.getElementById('winVolumeValue').textContent = Math.round(this.value * 100) + '%';
    });
    
    // Save win sound settings
    function saveWinSound() {
        const formData = new FormData();
        const soundFile = document.getElementById('win_sound_file').files[0];
        
        if (soundFile) {
            formData.append('win_sound_file', soundFile);
        }
        
        formData.append('win_volume', document.getElementById('win_volume').value);
        formData.append('sound_type', 'win');
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        showLoading(true);
        
        fetch('<?= base_url('admin/landing-page/save-win-sound') ?>', {
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
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            showLoading(false);
            console.error('Error:', error);
            showAlert('error', translations.networkError);
        });
    }
    
    // Delete win sound
    function deleteWinSound() {
        if (confirm(translations.deleteSound)) {
            showLoading(true);
            
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('sound_type', 'win');
            
            fetch('<?= base_url('admin/landing-page/delete-win-sound') ?>', {
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
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                showLoading(false);
                console.error('Error:', error);
                showAlert('error', translations.networkError);
            });
        }
    }
</script>

<style>
.border-secondary {
    border-color: #6c757d !important;
}

.img-thumbnail {
    background-color: var(--secondary-black);
    border: 1px solid #6c757d;
    max-height: 150px;
}

.text-info ul {
    font-size: 0.85rem;
}

.image-upload-area {
    border: 2px dashed #6c757d;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: rgba(108, 117, 125, 0.1);
}

.current-image-card {
    background-color: rgba(255, 215, 0, 0.05);
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid rgba(255, 215, 0, 0.2);
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-content {
    text-align: center;
}

.preview-image {
    max-width: 200px;
    max-height: 150px;
}

.image-upload-container {
    border: 2px dashed #6c757d;
    border-radius: 8px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.05);
}

.current-image-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}

.image-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.image-filename {
    margin-top: 10px;
    text-align: center;
}

.border-gold {
    border-color: #ffd700 !important;
}

.text-gold {
    color: #ffd700 !important;
}

.btn-gold {
    background-color: #ffd700;
    border-color: #ffd700;
    color: #000;
}

.btn-gold:hover {
    background-color: #ffed4e;
    border-color: #ffed4e;
    color: #000;
}
</style>
<?= $this->endSection() ?>