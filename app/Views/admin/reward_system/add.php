<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gold"><?= t('Admin.reward_system.add_new') ?></h1>
        <a href="<?= base_url('admin/reward-system') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <?= t('Admin.reward_system.back_to_list') ?>
        </a>
    </div>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Ad Form -->
    <div class="card bg-dark border-secondary">
        <div class="card-body">
            <form action="<?= base_url('admin/reward-system/add') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <!-- Basic Information -->
                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.reward_system.form.ad_title') ?> *</label>
                            <input type="text" name="ad_title" class="form-control bg-secondary text-light border-0" 
                                   placeholder="<?= t('Admin.reward_system.form.ad_title_placeholder') ?>"
                                   value="<?= old('ad_title') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.reward_system.form.ad_type') ?> *</label>
                            <select name="ad_type" class="form-select bg-secondary text-light border-0" id="adType" required>
                                <option value="image" <?= old('ad_type') === 'image' ? 'selected' : '' ?>>
                                    <?= t('Admin.reward_system.form.type_image') ?>
                                </option>
                                <option value="video" <?= old('ad_type') === 'video' ? 'selected' : '' ?>>
                                    <?= t('Admin.reward_system.form.type_video') ?>
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.reward_system.form.ad_description') ?></label>
                            <textarea name="ad_description" class="form-control bg-secondary text-light border-0" 
                                      rows="3" placeholder="<?= t('Admin.reward_system.form.ad_description_placeholder') ?>"><?= old('ad_description') ?></textarea>
                        </div>

                        <!-- Media Upload -->
                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.reward_system.form.media_upload') ?></label>
                            <input type="file" name="media_file" class="form-control bg-secondary text-light border-0" 
                                   id="mediaFile" accept="image/*,video/*">
                            <small class="text-muted"><?= t('Admin.reward_system.max_file_info') ?></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.reward_system.form.media_url') ?></label>
                            <input type="url" name="media_url" class="form-control bg-secondary text-light border-0" 
                                   placeholder="<?= t('Admin.reward_system.form.media_url_placeholder') ?>"
                                   value="<?= old('media_url') ?>">
                            <small class="text-muted"><?= t('Admin.reward_system.external_url_info') ?></small>
                        </div>

                        <!-- Preview Area -->
                        <div class="mb-3" id="previewArea" style="display: none;">
                            <label class="form-label text-light"><?= t('Admin.reward_system.preview') ?></label>
                            <div class="preview-container bg-secondary p-3 rounded">
                                <img id="imagePreview" src="" alt="<?= t('Admin.reward_system.preview') ?>" style="max-width: 100%; max-height: 300px; display: none;">
                                <video id="videoPreview" controls style="max-width: 100%; max-height: 300px; display: none;">
                                    <source src="" type="video/mp4">
                                </video>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light"><?= t('Admin.reward_system.form.click_url') ?></label>
                            <input type="url" name="click_url" class="form-control bg-secondary text-light border-0" 
                                   placeholder="<?= t('Admin.reward_system.form.click_url_placeholder') ?>"
                                   value="<?= old('click_url') ?>">
                            <small class="text-muted"><?= t('Admin.reward_system.click_url_info') ?></small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Settings -->
                        <div class="card bg-secondary">
                            <div class="card-header">
                                <h5 class="mb-0"><?= t('Admin.reward_system.settings') ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label"><?= t('Admin.reward_system.form.display_order') ?></label>
                                    <input type="number" name="display_order" class="form-control bg-dark text-light border-0" 
                                           value="<?= old('display_order', 0) ?>" min="0">
                                    <small class="text-muted"><?= t('Admin.reward_system.form.display_order_help') ?></small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                               value="1" <?= old('is_active', 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="isActive">
                                            <?= t('Admin.reward_system.form.is_active') ?>
                                        </label>
                                    </div>
                                </div>

                                <hr>

                                <h6><?= t('Admin.reward_system.form.schedule') ?></h6>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?= t('Admin.reward_system.form.start_date') ?></label>
                                    <input type="datetime-local" name="start_date" 
                                           class="form-control bg-dark text-light border-0" 
                                           value="<?= old('start_date') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><?= t('Admin.reward_system.form.end_date') ?></label>
                                    <input type="datetime-local" name="end_date" 
                                           class="form-control bg-dark text-light border-0" 
                                           value="<?= old('end_date') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-save"></i> <?= t('Admin.reward_system.form.save') ?>
                    </button>
                    <a href="<?= base_url('admin/reward-system') ?>" class="btn btn-secondary">
                        <?= t('Admin.reward_system.form.cancel') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.preview-container {
    text-align: center;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-control:focus,
.form-select:focus {
    border-color: #FFD700;
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mediaFileInput = document.getElementById('mediaFile');
    const mediaUrlInput = document.querySelector('input[name="media_url"]');
    const adTypeSelect = document.getElementById('adType');
    const previewArea = document.getElementById('previewArea');
    const imagePreview = document.getElementById('imagePreview');
    const videoPreview = document.getElementById('videoPreview');

    // File upload preview
    mediaFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                showPreview(e.target.result, file.type);
            };
            reader.readAsDataURL(file);
            
            // Clear URL input
            mediaUrlInput.value = '';
        }
    });

    // URL input preview
    mediaUrlInput.addEventListener('input', function(e) {
        const url = e.target.value;
        if (url) {
            const type = adTypeSelect.value === 'video' ? 'video' : 'image';
            showPreview(url, type);
            
            // Clear file input
            mediaFileInput.value = '';
        } else {
            hidePreview();
        }
    });

    // Ad type change
    adTypeSelect.addEventListener('change', function() {
        const url = mediaUrlInput.value;
        if (url) {
            const type = this.value === 'video' ? 'video' : 'image';
            showPreview(url, type);
        }
    });

    function showPreview(src, type) {
        previewArea.style.display = 'block';
        
        if (type.includes('video') || type === 'video') {
            imagePreview.style.display = 'none';
            videoPreview.style.display = 'block';
            videoPreview.src = src;
        } else {
            videoPreview.style.display = 'none';
            imagePreview.style.display = 'block';
            imagePreview.src = src;
        }
    }

    function hidePreview() {
        previewArea.style.display = 'none';
        imagePreview.src = '';
        videoPreview.src = '';
    }
});
</script>

<?= $this->endSection() ?>