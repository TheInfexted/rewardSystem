<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Customer - <?= esc($customer['username']) ?></h4>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?= base_url('admin/customers/update/' . $customer['id']) ?>" 
                          method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" 
                                           value="<?= esc($customer['username']) ?>" disabled>
                                    <small class="form-text text-muted">Username cannot be changed</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= esc(old('name', $customer['name'])) ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= esc(old('email', $customer['email'])) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active" class="form-label">Status</label>
                                    <select class="form-control" id="is_active" name="is_active">
                                        <option value="1" <?= old('is_active', $customer['is_active']) == 1 ? 'selected' : '' ?>>Active</option>
                                        <option value="0" <?= old('is_active', $customer['is_active']) == 0 ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Points and Tokens -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="points" class="form-label">Total Points</label>
                                    <input type="number" class="form-control" id="points" name="points" 
                                           value="<?= old('points', $customer['points']) ?>" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="spin_tokens" class="form-label">Spin Tokens</label>
                                    <input type="number" class="form-control" id="spin_tokens" name="spin_tokens" 
                                           value="<?= old('spin_tokens', $customer['spin_tokens']) ?>" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Appearance Settings -->
                        <hr>
                        <h5 class="mb-3">Appearance Settings</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="profile_background" class="form-label">Profile Background Theme</label>
                                    <select class="form-control" id="profile_background" name="profile_background">
                                        <?php foreach ($profile_backgrounds as $key => $label): ?>
                                            <option value="<?= $key ?>" 
                                                <?= old('profile_background', $customer['profile_background']) == $key ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dashboard_bg_color" class="form-label">Dashboard Background Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" 
                                               id="dashboard_bg_color" name="dashboard_bg_color" 
                                               value="<?= old('dashboard_bg_color', $customer['dashboard_bg_color']) ?>">
                                        <input type="text" class="form-control" 
                                               id="dashboard_bg_color_text" 
                                               value="<?= old('dashboard_bg_color', $customer['dashboard_bg_color']) ?>"
                                               readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Profile Background Image -->
                        <div class="form-group">
                            <label for="profile_background_image" class="form-label">Custom Profile Background Image</label>
                            <?php if (!empty($customer['profile_background_image'])): ?>
                                <div class="current-image mb-2">
                                    <img src="<?= base_url('uploads/profile_backgrounds/' . $customer['profile_background_image']) ?>" 
                                         alt="Current Background" class="img-thumbnail" style="max-width: 200px; max-height: 120px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" 
                                               id="remove_background_image" name="remove_background_image" value="1">
                                        <label class="form-check-label" for="remove_background_image">
                                            Remove current background image
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="profile_background_image" 
                                   name="profile_background_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="form-text text-muted">
                                Upload a new background image (JPEG, PNG, GIF, WebP). Max file size: 2MB. Recommended size: 800x400px
                            </small>
                        </div>
                        
                        <div class="form-group text-center mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Update Customer
                            </button>
                            <a href="<?= base_url('admin/customers/view/' . $customer['id']) ?>" 
                               class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Live Preview -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Live Preview</h5>
                </div>
                <div class="card-body">
                    <!-- Profile Header Preview -->
                    <div class="preview-section">
                        <h6>Profile Header</h6>
                        <div class="profile-header-preview" id="profilePreview">
                            <div class="profile-header-overlay" id="profileOverlay" style="display: none;"></div>
                            <div class="profile-content">
                                <h6 class="text-white mb-1"><?= esc($customer['username']) ?></h6>
                                <small class="text-white-50">Premium Member</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dashboard Background Preview -->
                    <div class="preview-section mt-4">
                        <h6>Dashboard Background</h6>
                        <div class="dashboard-preview" id="dashboardPreview">
                            <div class="dashboard-content">
                                <div class="stat-card-preview">
                                    <div class="stat-number"><?= number_format($customer['points']) ?></div>
                                    <div class="stat-label">Total Points</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Color Info -->
                    <div class="color-info mt-3">
                        <small class="text-muted d-block">Selected Color:</small>
                        <div class="color-display" id="colorDisplay">
                            <div class="color-swatch" id="colorSwatch"></div>
                            <span id="colorValue"><?= $customer['dashboard_bg_color'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Live Preview Styles */
.preview-section h6 {
    color: #333;
    font-weight: 600;
    margin-bottom: 10px;
}

.profile-header-preview {
    height: 100px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
}

.profile-header-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
}

.profile-content {
    position: relative;
    z-index: 2;
    text-align: center;
}

.dashboard-preview {
    height: 80px;
    border-radius: 8px;
    background: <?= $customer['dashboard_bg_color'] ?>;
    padding: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #dee2e6;
    transition: background-color 0.3s ease;
}

.stat-card-preview {
    text-align: center;
    background: rgba(255, 255, 255, 0.9);
    padding: 10px 15px;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 1.2rem;
    font-weight: 600;
    color: #28a745;
    line-height: 1;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 2px;
}

.color-display {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 5px;
}

.color-swatch {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    border: 1px solid #ccc;
    background: <?= $customer['dashboard_bg_color'] ?>;
}

/* Background themes for preview */
.default-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.blue-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.purple-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.green-bg { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important; }
.orange-bg { background: linear-gradient(135deg, #ff9a56 0%, #ffad56 100%) !important; }
.pink-bg { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; }
.dark-bg { background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%) !important; }

/* Form enhancements */
.form-control-color {
    width: 60px;
    padding: 0.375rem 0.5rem;
    border-radius: 0.375rem 0 0 0.375rem;
}

.current-image img {
    border-radius: 8px;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileSelect = document.getElementById('profile_background');
    const colorInput = document.getElementById('dashboard_bg_color');
    const colorText = document.getElementById('dashboard_bg_color_text');
    const profilePreview = document.getElementById('profilePreview');
    const dashboardPreview = document.getElementById('dashboardPreview');
    const colorSwatch = document.getElementById('colorSwatch');
    const colorValue = document.getElementById('colorValue');
    const profileOverlay = document.getElementById('profileOverlay');
    const imageInput = document.getElementById('profile_background_image');
    const removeImageCheck = document.getElementById('remove_background_image');
    
    // Update profile background preview
    function updateProfilePreview() {
        const selectedTheme = profileSelect.value;
        profilePreview.className = 'profile-header-preview ' + selectedTheme + '-bg';
        
        // Show/hide overlay based on whether there's a custom image
        const hasCustomImage = <?= !empty($customer['profile_background_image']) ? 'true' : 'false' ?>;
        if (hasCustomImage && !removeImageCheck.checked) {
            profileOverlay.style.display = 'block';
        } else {
            profileOverlay.style.display = 'none';
        }
    }
    
    // Update dashboard color preview
    function updateDashboardPreview() {
        const color = colorInput.value;
        colorText.value = color;
        dashboardPreview.style.backgroundColor = color;
        colorSwatch.style.backgroundColor = color;
        colorValue.textContent = color;
    }
    
    // Update color input from text
    function updateColorInput() {
        const color = colorText.value;
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            colorInput.value = color;
            updateDashboardPreview();
        }
    }
    
    // Event listeners
    profileSelect.addEventListener('change', updateProfilePreview);
    colorInput.addEventListener('input', updateDashboardPreview);
    colorText.addEventListener('input', updateColorInput);
    
    if (removeImageCheck) {
        removeImageCheck.addEventListener('change', updateProfilePreview);
    }
    
    // Handle image upload preview
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePreview.style.backgroundImage = `url(${e.target.result})`;
                    profilePreview.style.backgroundSize = 'cover';
                    profilePreview.style.backgroundPosition = 'center';
                    profileOverlay.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Initialize previews
    updateProfilePreview();
    updateDashboardPreview();
});
</script>
<?= $this->endSection() ?>