<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<meta name="csrf_token" content="<?= csrf_hash() ?>">

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Edit Customer - <?= esc($customer['username']) ?></h4>
                    <a href="<?= base_url('admin/customers/view/' . $customer['id']) ?>" class="btn btn-warning btn-sm text-dark">
                        <i class="fas fa-cogs"></i> Feature Settings
                    </a>
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
                        
                        <!-- Password Management Section -->
                        <hr>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0">Password Management</h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-warning btn-sm" onclick="openChangePasswordModal()">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="generateNewPassword()">
                                    <i class="fas fa-random"></i> Generate New Password
                                </button>
                            </div>
                        </div>
                        
                        <div class="password-security-box mb-3">
                            <i class="fas fa-shield-alt text-primary me-2"></i>
                            <strong>Password Security:</strong> Passwords are securely hashed and cannot be viewed. 
                            You can either change to a specific password or generate a random one.
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

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-key"></i>
                    Change Customer Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Security Notice:</strong> You are about to change the password for customer: 
                        <strong><?= esc($customer['username']) ?></strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   placeholder="Enter new password" minlength="4" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('new_password')">
                                <i class="fas fa-eye" id="new_password_icon"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">Minimum 4 characters required</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm new password" minlength="4" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('confirm_password')">
                                <i class="fas fa-eye" id="confirm_password_icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_reason" class="form-label">Reason for Change (Optional)</label>
                        <textarea class="form-control" id="password_reason" name="reason" rows="2" 
                                  placeholder="e.g., Customer forgot password, security concern, etc."></textarea>
                    </div>
                    
                    <div class="password-strength-indicator">
                        <small class="text-muted">Password strength: </small>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" id="passwordStrengthBar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small id="passwordStrengthText" class="text-muted"></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirmChangePasswordBtn">
                    <i class="fas fa-save"></i> Change Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Generate Password Modal -->
<div class="modal fade" id="generatePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-random"></i>
                    Generate New Password
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    A secure random password will be generated for customer: <strong><?= esc($customer['username']) ?></strong>
                </div>
                
                <div class="mb-3">
                    <label for="generation_reason" class="form-label">Reason for Generation (Optional)</label>
                    <textarea class="form-control" id="generation_reason" name="reason" rows="2" 
                              placeholder="e.g., Initial setup, account recovery, etc."></textarea>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Important:</strong> Make sure to securely share the generated password with the customer.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-info" id="confirmGeneratePasswordBtn">
                    <i class="fas fa-random"></i> Generate Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CSS Styles -->
<style>
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

.password-strength-indicator {
    margin-top: 10px;
}

.progress-bar {
    transition: all 0.3s ease;
}

.password-strength-weak {
    background-color: #dc3545;
}

.password-strength-medium {
    background-color: #ffc107;
}

.password-strength-strong {
    background-color: #28a745;
}

.default-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.blue-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.purple-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.green-bg { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important; }
.orange-bg { background: linear-gradient(135deg, #ff9a56 0%, #ffad56 100%) !important; }
.pink-bg { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; }
.dark-bg { background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%) !important; }

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
    color: white;
    margin-bottom: 0.5rem;
}

.btn.loading {
    position: relative;
    color: transparent !important;
}

.btn.loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.password-security-box {
    background: #e9f5ff;
    border: 1px solid #b8daff;
    border-left: 5px solid #0d6efd;
    padding: 15px;
    border-radius: 6px;
    color: #084298;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>

<!-- JavaScript -->
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
        if (profileSelect && profilePreview) {
            const selectedTheme = profileSelect.value;
            profilePreview.className = 'profile-header-preview ' + selectedTheme + '-bg';
            
            const hasCustomImage = <?= !empty($customer['profile_background_image']) ? 'true' : 'false' ?>;
            if (hasCustomImage && removeImageCheck && !removeImageCheck.checked) {
                if (profileOverlay) profileOverlay.style.display = 'block';
            } else {
                if (profileOverlay) profileOverlay.style.display = 'none';
            }
        }
    }
    
    // Update dashboard color preview
    function updateDashboardPreview() {
        if (colorInput && dashboardPreview && colorSwatch && colorValue) {
            const color = colorInput.value;
            if (colorText) colorText.value = color;
            dashboardPreview.style.backgroundColor = color;
            colorSwatch.style.backgroundColor = color;
            colorValue.textContent = color;
        }
    }
    
    // Update color input from text
    function updateColorInput() {
        if (colorText && colorInput) {
            const color = colorText.value;
            if (/^#[0-9A-F]{6}$/i.test(color)) {
                colorInput.value = color;
                updateDashboardPreview();
            }
        }
    }
    
    // Event listeners
    if (profileSelect) profileSelect.addEventListener('change', updateProfilePreview);
    if (colorInput) colorInput.addEventListener('input', updateDashboardPreview);
    if (colorText) colorText.addEventListener('input', updateColorInput);
    if (removeImageCheck) removeImageCheck.addEventListener('change', updateProfilePreview);
    
    // Handle image upload preview
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && profilePreview && profileOverlay) {
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
    
    // Password strength checker
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', checkPasswordStrength);
    }
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }
});

// Password management functions
function openChangePasswordModal() {
    const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    
    // Reset form
    const form = document.getElementById('changePasswordForm');
    if (form) form.reset();
    
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');
    if (strengthBar) strengthBar.style.width = '0%';
    if (strengthText) strengthText.textContent = '';
    
    modal.show();
}

function generateNewPassword() {
    const modal = new bootstrap.Modal(document.getElementById('generatePasswordModal'));
    
    // Reset reason field
    const reasonField = document.getElementById('generation_reason');
    if (reasonField) reasonField.value = '';
    
    modal.show();
}

function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field && icon) {
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            field.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('new_password').value;
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');
    
    if (!strengthBar || !strengthText) return;
    
    let strength = 0;
    let feedback = '';
    
    if (password.length >= 4) strength += 25;
    if (password.length >= 8) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 25;
    
    strengthBar.style.width = strength + '%';
    
    if (strength <= 25) {
        strengthBar.className = 'progress-bar password-strength-weak';
        feedback = 'Weak';
    } else if (strength <= 50) {
        strengthBar.className = 'progress-bar password-strength-medium';
        feedback = 'Medium';
    } else {
        strengthBar.className = 'progress-bar password-strength-strong';
        feedback = 'Strong';
    }
    
    strengthText.textContent = feedback;
}

function checkPasswordMatch() {
    const password = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const confirmField = document.getElementById('confirm_password');
    
    if (confirmField && confirmPassword && password !== confirmPassword) {
        confirmField.classList.add('is-invalid');
    } else if (confirmField) {
        confirmField.classList.remove('is-invalid');
    }
}

// Password change handler
document.addEventListener('DOMContentLoaded', function() {
    const changeBtn = document.getElementById('confirmChangePasswordBtn');
    if (changeBtn) {
        changeBtn.addEventListener('click', function() {
            const newPassword = document.getElementById('new_password')?.value;
            const confirmPassword = document.getElementById('confirm_password')?.value;
            const reason = document.getElementById('password_reason')?.value || '';
            
            console.log('Password change initiated');
            
            // Validation
            if (!newPassword || newPassword.length < 4) {
                showToast('Password must be at least 4 characters long', 'error');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showToast('Password confirmation does not match', 'error');
                return;
            }
            
            const originalHtml = this.innerHTML;
            this.classList.add('loading');
            this.disabled = true;
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf_token"]')?.getAttribute('content') || '';
            
            // Prepare form data
            const formData = new FormData();
            formData.append('new_password', newPassword);
            formData.append('confirm_password', confirmPassword);
            formData.append('reason', reason);
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }
            
            // Send request
            fetch(`<?= base_url('admin/customers/changePassword/' . $customer['id']) ?>`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                this.classList.remove('loading');
                this.disabled = false;
                this.innerHTML = originalHtml;
                
                if (data.success === true) {
                    // Hide change password modal
                    const changeModalElement = document.getElementById('changePasswordModal');
                    if (changeModalElement) {
                        const changeModal = bootstrap.Modal.getInstance(changeModalElement) || 
                                          new bootstrap.Modal(changeModalElement);
                        changeModal.hide();
                    }
                    
                    // Show success result
                    showPasswordResult(data.message || 'Password changed successfully!');
                    
                    // Reset form
                    const form = document.getElementById('changePasswordForm');
                    if (form) form.reset();
                    
                } else {
                    showToast('Error: ' + (data.message || 'Unknown error occurred'), 'error');
                }
            })
            .catch(error => {
                console.error('Password change error:', error);
                
                this.classList.remove('loading');
                this.disabled = false;
                this.innerHTML = originalHtml;
                
                showToast('An error occurred while changing the password: ' + error.message, 'error');
            });
        });
    }
    
    // Password generation handler
    const generateBtn = document.getElementById('confirmGeneratePasswordBtn');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            const reason = document.getElementById('generation_reason')?.value || '';
            
            console.log('Password generation initiated');
            
            const originalHtml = this.innerHTML;
            this.classList.add('loading');
            this.disabled = true;
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf_token"]')?.getAttribute('content') || '';
            
            // Prepare form data
            const formData = new FormData();
            formData.append('reason', reason);
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }
            
            // Send request
            fetch(`<?= base_url('admin/customers/generatePassword/' . $customer['id']) ?>`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                this.classList.remove('loading');
                this.disabled = false;
                this.innerHTML = originalHtml;
                
                if (data.success === true) {
                    // Hide generate password modal
                    const generateModalElement = document.getElementById('generatePasswordModal');
                    if (generateModalElement) {
                        const generateModal = bootstrap.Modal.getInstance(generateModalElement) || 
                                            new bootstrap.Modal(generateModalElement);
                        generateModal.hide();
                    }
                    
                    // Show success result with generated password
                    showPasswordResult(
                        data.message || 'Password generated successfully!',
                        data.new_password
                    );
                    
                    // Reset form
                    const reasonField = document.getElementById('generation_reason');
                    if (reasonField) reasonField.value = '';
                    
                } else {
                    showToast('Error: ' + (data.message || 'Unknown error occurred'), 'error');
                }
            })
            .catch(error => {
                console.error('Password generation error:', error);
                
                this.classList.remove('loading');
                this.disabled = false;
                this.innerHTML = originalHtml;
                
                showToast('An error occurred while generating the password: ' + error.message, 'error');
            });
        });
    }
});

// Enhanced showPasswordResult function
function showPasswordResult(message, newPassword = null) {
    console.log('showPasswordResult called with:', { message, newPassword });
    
    try {
        // Create and show result modal
        const resultModalHtml = `
            <div class="modal fade" id="dynamicPasswordResultModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-check-circle"></i>
                                Password Updated Successfully
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                ${message}
                            </div>
                            
                            ${newPassword ? `
                            <div class="mb-3">
                                <label class="form-label"><strong>Generated Password:</strong></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="dynamicGeneratedPasswordField" value="${newPassword}" readonly>
                                    <button type="button" class="btn btn-outline-secondary" onclick="copyGeneratedPassword()">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                                <small class="form-text text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Please save this password securely. It cannot be retrieved again.
                                </small>
                            </div>
                            ` : ''}
                            
                            <div class="alert alert-info">
                                <i class="fas fa-shield-alt"></i>
                                <strong>Security Note:</strong> The password has been securely hashed in the database.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                                <i class="fas fa-check"></i> Understood
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove any existing result modal
        const existingModal = document.getElementById('dynamicPasswordResultModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add the new modal to the page
        document.body.insertAdjacentHTML('beforeend', resultModalHtml);
        
        // Show the modal
        const resultModal = new bootstrap.Modal(document.getElementById('dynamicPasswordResultModal'));
        resultModal.show();
        
        // Clean up when modal is hidden
        document.getElementById('dynamicPasswordResultModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
        
    } catch (error) {
        console.error('Error in showPasswordResult:', error);
        // Fallback to simple alert
        if (newPassword) {
            alert(`${message}\n\nGenerated Password: ${newPassword}\n\nPlease save this password securely!`);
        } else {
            alert(message);
        }
    }
}

// Function to copy generated password
function copyGeneratedPassword() {
    const passwordField = document.getElementById('dynamicGeneratedPasswordField');
    if (passwordField) {
        copyToClipboard(passwordField.value);
    }
}

// Enhanced copy to clipboard function
function copyToClipboard(text) {
    try {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Password copied to clipboard!', 'success');
            }).catch(err => {
                console.error('Clipboard write failed:', err);
                fallbackCopyToClipboard(text);
            });
        } else {
            fallbackCopyToClipboard(text);
        }
    } catch (error) {
        console.error('Copy to clipboard error:', error);
        fallbackCopyToClipboard(text);
    }
}

// Fallback copy method
function fallbackCopyToClipboard(text) {
    try {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        
        textArea.focus();
        textArea.select();
        
        const successful = document.execCommand('copy');
        document.body.removeChild(textArea);
        
        if (successful) {
            showToast('Password copied to clipboard!', 'success');
        } else {
            prompt('Copy this password manually:', text);
        }
    } catch (error) {
        console.error('Fallback copy failed:', error);
        prompt('Copy this password manually:', text);
    }
}

// Enhanced toast function
function showToast(message, type) {
    // Remove existing toast
    const existingToast = document.getElementById('dynamicToast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.id = 'dynamicToast';
    toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} position-fixed`;
    toast.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideInRight 0.3s ease-out;
    `;
    
    const iconClass = type === 'error' ? 'fas fa-exclamation-triangle' : 
                     type === 'success' ? 'fas fa-check-circle' : 'fas fa-info-circle';
    
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="${iconClass} me-2"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ms-2" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'slideInRight 0.3s ease-out reverse';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}
</script>

<?= $this->endSection() ?>