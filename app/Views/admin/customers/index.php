<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<meta name="csrf_token" content="<?= csrf_hash() ?>">
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><?= t('Admin.customers.title') ?></h3>
                    <div class="card-tools">
                        <form method="get" class="form-inline">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="<?= t('Admin.customers.search_placeholder') ?>" value="<?= esc($search) ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> <?= t('Admin.common.search') ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th><?= t('Admin.customers.table.id') ?></th>
                                    <th><?= t('Admin.customers.table.username') ?></th>
                                    <th><?= t('Admin.customers.table.name') ?></th>
                                    <th><?= t('Admin.customers.table.email') ?></th>
                                    <th><?= t('Admin.customers.table.points') ?></th>
                                    <th><?= t('Admin.customers.table.spin_tokens') ?></th>
                                    <th><?= t('Admin.customers.table.profile_theme') ?></th>
                                    <th><?= t('Admin.customers.table.status') ?></th>
                                    <th><?= t('Admin.customers.table.actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?= $customer['id'] ?></td>
                                    <td>
                                        <strong><?= esc($customer['username']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= t('Admin.customers.table.joined') ?>: <?= date('M j, Y', strtotime($customer['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td><?= esc($customer['name'] ?? 'N/A') ?></td>
                                    <td><?= esc($customer['email'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <?= number_format($customer['points']) ?> <?= t('Admin.customers.table.pts') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= $customer['spin_tokens'] ?? 0 ?> <?= t('Admin.customers.table.tokens') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="profile-preview me-2">
                                                <?php if (!empty($customer['profile_background_image'])): ?>
                                                    <img src="<?= base_url('uploads/profile_backgrounds/' . $customer['profile_background_image']) ?>" 
                                                         alt="Profile BG" class="profile-bg-thumb">
                                                <?php else: ?>
                                                    <div class="profile-bg-thumb <?= $customer['profile_background'] ?? 'default' ?>-bg"></div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <small class="d-block">BG: <?= ucfirst($customer['profile_background'] ?? 'default') ?></small>
                                                <small class="text-muted">
                                                    <i class="fas fa-palette"></i> 
                                                    <?= $customer['dashboard_bg_color'] ?? '#ffffff' ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($customer['is_active']): ?>
                                            <span class="badge badge-success"><?= t('Admin.customers.table.active') ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><?= t('Admin.customers.table.inactive') ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($customer['last_login'])): ?>
                                            <br><small class="text-muted">
                                                <?= t('Admin.customers.table.last_login') ?>: <?= date('M j, H:i', strtotime($customer['last_login'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= base_url('admin/customers/view/' . $customer['id']) ?>" 
                                               class="btn btn-info" title="<?= t('Admin.customers.actions.view_details') ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('admin/customers/edit/' . $customer['id']) ?>" 
                                               class="btn btn-warning" title="<?= t('Admin.customers.actions.edit_customer') ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-secondary" 
                                                    title="<?= t('Admin.customers.actions.change_password') ?>"
                                                    onclick="openPasswordModal(<?= $customer['id'] ?>, '<?= esc($customer['username']) ?>')">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <a href="<?= base_url('admin/customers/tokens/' . $customer['id']) ?>" 
                                               class="btn btn-primary" title="<?= t('Admin.customers.actions.manage_tokens') ?>">
                                                <i class="fas fa-coins"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-<?= $customer['is_active'] ? 'secondary' : 'success' ?>" 
                                                    title="<?= $customer['is_active'] ? t('Admin.customers.actions.deactivate') : t('Admin.customers.actions.activate') ?>"
                                                    onclick="toggleCustomerStatus(<?= $customer['id'] ?>, <?= $customer['is_active'] ?>)">
                                                <i class="fas fa-<?= $customer['is_active'] ? 'user-slash' : 'user-check' ?>"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-danger" 
                                                    title="<?= t('Admin.customers.actions.delete') ?>"
                                                    onclick="deleteCustomer(<?= $customer['id'] ?>, '<?= esc($customer['username']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (empty($customers)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5><?= t('Admin.customers.no_customers') ?></h5>
                            <p class="text-muted">
                                <?= $search ? t('Admin.customers.no_customers_desc') : t('Admin.customers.no_customers_registered') ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($pager && $pager->getPageCount() > 1): ?>
                    <?php
                    $currentPage = (int) ($pager->getCurrentPage() ?? 1);
                    $totalPages = $pager->getPageCount();
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);

                    $query = $_GET;
                    ?>
                    <nav class="mt-4">
                        <ul class="pagination pagination-sm justify-content-center">
                            <!-- Previous Page -->
                            <li class="page-item <?= $currentPage === 1 ? 'disabled' : '' ?>">
                                <?php $query['page'] = $currentPage - 1; ?>
                                <a class="page-link bg-secondary border-secondary text-light" href="<?= $currentPage > 1 ? current_url() . '?' . http_build_query($query) : '#' ?>">
                                    <?= t('Admin.common.previous') ?>
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php $query['page'] = $i; ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link <?= $i === $currentPage ? 'bg-gold border-gold text-dark' : 'bg-secondary border-secondary text-light' ?>" 
                                    href="<?= current_url() . '?' . http_build_query($query) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Page -->
                            <li class="page-item <?= $currentPage === $totalPages ? 'disabled' : '' ?>">
                                <?php $query['page'] = $currentPage + 1; ?>
                                <a class="page-link bg-secondary border-secondary text-light" href="<?= $currentPage < $totalPages ? current_url() . '?' . http_build_query($query) : '#' ?>">
                                    <?= t('Admin.common.next') ?>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Password Change Modal -->
<div class="modal fade" id="quickPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-key"></i>
                    <?= t('Admin.customers.password.quick_management') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?= t('Admin.customers.password.managing_for') ?>: <strong id="quickPasswordCustomerName"></strong>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-edit fa-2x text-warning mb-3"></i>
                                <h6><?= t('Admin.customers.password.set_custom') ?></h6>
                                <p class="text-muted"><?= t('Admin.customers.password.choose_specific') ?></p>
                                <button type="button" class="btn btn-warning" onclick="openCustomPasswordForm()">
                                    <i class="fas fa-edit"></i> <?= t('Admin.customers.password.custom_password') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-random fa-2x text-info mb-3"></i>
                                <h6><?= t('Admin.customers.password.generate_random') ?></h6>
                                <p class="text-muted"><?= t('Admin.customers.password.auto_generate') ?></p>
                                <button type="button" class="btn btn-info" onclick="quickGeneratePassword()">
                                    <i class="fas fa-random"></i> <?= t('Admin.customers.password.generate_password') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Custom Password Form -->
                <div id="customPasswordForm" style="display: none;">
                    <hr>
                    <h6><i class="fas fa-edit"></i> <?= t('Admin.customers.password.set_custom_password') ?></h6>
                    <form id="quickPasswordForm">
                        <div class="mb-3">
                            <label for="quick_new_password" class="form-label"><?= t('Admin.customers.password.new_password') ?></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="quick_new_password" 
                                       placeholder="<?= t('Admin.customers.password.enter_new') ?>" minlength="4" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleQuickPasswordVisibility('quick_new_password')">
                                    <i class="fas fa-eye" id="quick_new_password_icon"></i>
                                </button>
                            </div>
                            <small class="text-muted"><?= t('Admin.customers.password.min_4_chars') ?></small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quick_confirm_password" class="form-label"><?= t('Admin.customers.password.confirm_password') ?></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="quick_confirm_password" 
                                       placeholder="<?= t('Admin.customers.password.confirm_new') ?>" minlength="4" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleQuickPasswordVisibility('quick_confirm_password')">
                                    <i class="fas fa-eye" id="quick_confirm_password_icon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quick_reason" class="form-label"><?= t('Admin.customers.password.reason_optional') ?></label>
                            <input type="text" class="form-control" id="quick_reason" 
                                   placeholder="<?= t('Admin.customers.password.reason_placeholder') ?>">
                        </div>
                        
                        <button type="button" class="btn btn-warning" onclick="submitQuickPasswordChange()">
                            <i class="fas fa-save"></i> <?= t('Admin.customers.password.change_password_btn') ?>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="cancelCustomPasswordForm()">
                            <i class="fas fa-times"></i> <?= t('Admin.customers.password.cancel') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Result Modal -->
<div class="modal fade" id="quickPasswordResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i>
                    <?= t('Admin.customers.password.update_successful') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span id="quickPasswordResultMessage"></span>
                </div>
                
                <div id="quickGeneratedPasswordDisplay" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label"><strong><?= t('Admin.customers.password.generated_password') ?>:</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control font-monospace" id="quickGeneratedPasswordField" readonly>
                            <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard('quickGeneratedPasswordField')">
                                <i class="fas fa-copy"></i> <?= t('Admin.common.copy') ?>
                            </button>
                        </div>
                        <small class="form-text text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= t('Admin.customers.password.save_securely') ?>
                        </small>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-shield-alt"></i>
                    <strong><?= t('Admin.customers.password.security_note') ?>:</strong> <?= t('Admin.customers.password.securely_hashed') ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                    <i class="fas fa-check"></i> <?= t('Admin.customers.password.understood') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= t('Admin.customers.delete.title') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong><?= t('Admin.customers.delete.warning') ?></strong>
                </div>
                
                <p><?= t('Admin.customers.delete.about_to_delete') ?>: <strong id="deleteCustomerName"></strong></p>
                
                <div class="mb-3">
                    <p><strong><?= t('Admin.customers.delete.this_will') ?></strong></p>
                    <ul class="text-danger">
                        <li><?= t('Admin.customers.delete.remove_data') ?></li>
                        <li><?= t('Admin.customers.delete.delete_images') ?></li>
                        <li><?= t('Admin.customers.delete.remove_access') ?></li>
                        <li><strong><?= t('Admin.customers.delete.cannot_reverse') ?></strong></li>
                    </ul>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-lightbulb"></i>
                    <strong><?= t('Admin.customers.delete.recommendation') ?>:</strong> <?= t('Admin.customers.delete.consider_deactivate') ?>
                </div>
                
                <div class="mb-3">
                    <label for="confirmUsername" class="form-label">
                        <?= t('Admin.customers.delete.confirm_deletion') ?>: <strong id="confirmUsernameTarget"></strong>
                    </label>
                    <input type="text" class="form-control" id="confirmUsername" 
                           placeholder="<?= t('Admin.customers.delete.enter_username') ?>">
                    <div class="form-text text-danger" id="confirmUsernameError" style="display: none;">
                        <?= t('Admin.customers.delete.username_mismatch') ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> <?= t('Admin.common.cancel') ?>
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                    <i class="fas fa-trash"></i> <?= t('Admin.customers.delete.permanently_delete') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.profile-bg-thumb {
    width: 30px;
    height: 20px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.profile-bg-thumb.default-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.profile-bg-thumb.blue-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.profile-bg-thumb.purple-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.profile-bg-thumb.green-bg {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.profile-bg-thumb.orange-bg {
    background: linear-gradient(135deg, #ff9a56 0%, #ffad56 100%);
}

.profile-bg-thumb.pink-bg {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.profile-bg-thumb.dark-bg {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.profile-preview {
    display: flex;
    align-items: center;
}

/* Loading states */
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

/* Status indicators */
.btn-group .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.card.h-100 {
    height: 100% !important;
}

.font-monospace {
    font-family: 'Courier New', Courier, monospace;
    letter-spacing: 1px;
}
</style>

<script>
let currentCustomerId = null;
let currentCustomerUsername = null;

// Password management functions
function openPasswordModal(customerId, username) {
    currentCustomerId = customerId;
    currentCustomerUsername = username;
    
    // Update modal content
    const customerNameEl = document.getElementById('quickPasswordCustomerName');
    if (customerNameEl) {
        customerNameEl.textContent = username;
    }
    
    // Reset form visibility
    const customForm = document.getElementById('customPasswordForm');
    if (customForm) {
        customForm.style.display = 'none';
    }
    
    const passwordForm = document.getElementById('quickPasswordForm');
    if (passwordForm) {
        passwordForm.reset();
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('quickPasswordModal'));
    modal.show();
}

function openCustomPasswordForm() {
    const customForm = document.getElementById('customPasswordForm');
    if (customForm) {
        customForm.style.display = 'block';
        const passwordField = document.getElementById('quick_new_password');
        if (passwordField) {
            passwordField.focus();
        }
    }
}

function cancelCustomPasswordForm() {
    const customForm = document.getElementById('customPasswordForm');
    if (customForm) {
        customForm.style.display = 'none';
    }
    
    const passwordForm = document.getElementById('quickPasswordForm');
    if (passwordForm) {
        passwordForm.reset();
    }
}

function toggleQuickPasswordVisibility(fieldId) {
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

// Submit custom password change
function submitQuickPasswordChange() {
    const newPassword = document.getElementById('quick_new_password')?.value;
    const confirmPassword = document.getElementById('quick_confirm_password')?.value;
    const reason = document.getElementById('quick_reason')?.value || '<?= t('Admin.customers.password.reason_placeholder') ?>';
    
    // Validation
    if (!newPassword || newPassword.length < 4) {
        showToast('<?= t('Admin.customers.password.min_length_error') ?>', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showToast('<?= t('Admin.customers.password.confirmation_error') ?>', 'error');
        return;
    }
    
    if (!currentCustomerId) {
        showToast('Customer ID not found', 'error');
        return;
    }
    
    // Show loading on the submit button
    const submitBtn = event.target;
    const originalHtml = submitBtn.innerHTML;
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    
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
    fetch(`<?= base_url('admin/customers/changePassword/') ?>${currentCustomerId}`, {
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
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
        
        if (data.success === true) {
            // Hide current modal
            const currentModal = bootstrap.Modal.getInstance(document.getElementById('quickPasswordModal'));
            if (currentModal) {
                currentModal.hide();
            }
            
            // Show success result
            showPasswordResult(data.message || '<?= t('Admin.customers.messages.password_changed') ?>');
            
        } else {
            showToast('Error: ' + (data.message || 'Unknown error occurred'), 'error');
        }
    })
    .catch(error => {
        console.error('Password change error:', error);
        
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
        
        showToast('An error occurred while changing the password: ' + error.message, 'error');
    });
}

// Generate random password
function quickGeneratePassword() {
    if (!currentCustomerId) {
        showToast('Customer ID not found', 'error');
        return;
    }
    
    // Show loading on the generate button
    const generateBtn = event.target;
    const originalHtml = generateBtn.innerHTML;
    generateBtn.classList.add('loading');
    generateBtn.disabled = true;
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf_token"]')?.getAttribute('content') || '';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('reason', 'Quick password generation from customer list');
    if (csrfToken) {
        formData.append('csrf_token', csrfToken);
    }
    
    // Send request
    fetch(`<?= base_url('admin/customers/generatePassword/') ?>${currentCustomerId}`, {
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
        generateBtn.classList.remove('loading');
        generateBtn.disabled = false;
        generateBtn.innerHTML = originalHtml;
        
        if (data.success === true) {
            // Hide current modal
            const currentModal = bootstrap.Modal.getInstance(document.getElementById('quickPasswordModal'));
            if (currentModal) {
                currentModal.hide();
            }
            
            // Show success result with generated password
            showPasswordResult(
                data.message || '<?= t('Admin.customers.messages.password_generated') ?>',
                data.new_password
            );
            
        } else {
            showToast('Error: ' + (data.message || 'Unknown error occurred'), 'error');
        }
    })
    .catch(error => {
        console.error('Password generation error:', error);
        
        generateBtn.classList.remove('loading');
        generateBtn.disabled = false;
        generateBtn.innerHTML = originalHtml;
        
        showToast('An error occurred while generating the password: ' + error.message, 'error');
    });
}

// Show password result
function showPasswordResult(message, newPassword = null) {
    console.log('showPasswordResult called with:', { message, newPassword });
    
    // Update the existing result modal
    const resultMessage = document.getElementById('quickPasswordResultMessage');
    const passwordDisplay = document.getElementById('quickGeneratedPasswordDisplay');
    const passwordField = document.getElementById('quickGeneratedPasswordField');
    
    if (resultMessage) {
        resultMessage.textContent = message;
    }
    
    if (newPassword && passwordDisplay && passwordField) {
        passwordField.value = newPassword;
        passwordDisplay.style.display = 'block';
    } else if (passwordDisplay) {
        passwordDisplay.style.display = 'none';
    }
    
    // Show the result modal
    const resultModal = new bootstrap.Modal(document.getElementById('quickPasswordResultModal'));
    resultModal.show();
}

// Copy to clipboard functionality
function copyToClipboard(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) {
        showToast('Copy field not found', 'error');
        return;
    }
    
    try {
        field.select();
        field.setSelectionRange(0, 99999);
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(field.value).then(() => {
                showToast('<?= t('Admin.customers.password.copied_clipboard') ?>', 'success');
            }).catch(() => {
                // Fallback to execCommand
                const successful = document.execCommand('copy');
                if (successful) {
                    showToast('<?= t('Admin.customers.password.copied_clipboard') ?>', 'success');
                } else {
                    showToast('Failed to copy password', 'error');
                }
            });
        } else {
            // Fallback to execCommand
            const successful = document.execCommand('copy');
            if (successful) {
                showToast('<?= t('Admin.customers.password.copied_clipboard') ?>', 'success');
            } else {
                showToast('Failed to copy password', 'error');
            }
        }
    } catch (err) {
        console.error('Copy failed:', err);
        showToast('Failed to copy password', 'error');
    }
}

// Customer deletion functions
function deleteCustomer(customerId, username) {
    currentCustomerId = customerId;
    currentCustomerUsername = username;
    
    // Update modal content
    const customerNameEl = document.getElementById('deleteCustomerName');
    const confirmTargetEl = document.getElementById('confirmUsernameTarget');
    const confirmInputEl = document.getElementById('confirmUsername');
    const confirmBtnEl = document.getElementById('confirmDeleteBtn');
    const errorEl = document.getElementById('confirmUsernameError');
    
    if (customerNameEl) customerNameEl.textContent = username;
    if (confirmTargetEl) confirmTargetEl.textContent = username;
    if (confirmInputEl) confirmInputEl.value = '';
    if (confirmBtnEl) confirmBtnEl.disabled = true;
    if (errorEl) errorEl.style.display = 'none';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deleteCustomerModal'));
    modal.show();
}

// Real-time validation of username input
document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('confirmUsername');
    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            const enteredUsername = this.value.trim();
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            const errorDiv = document.getElementById('confirmUsernameError');
            
            if (enteredUsername === currentCustomerUsername) {
                if (confirmBtn) confirmBtn.disabled = false;
                if (errorDiv) errorDiv.style.display = 'none';
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                if (confirmBtn) confirmBtn.disabled = true;
                if (enteredUsername.length > 0) {
                    if (errorDiv) errorDiv.style.display = 'block';
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    if (errorDiv) errorDiv.style.display = 'none';
                    this.classList.remove('is-invalid', 'is-valid');
                }
            }
        });
    }
    
    // Handle actual deletion
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const deleteBtn = this;
            const originalHtml = deleteBtn.innerHTML;
            
            // Show loading state
            deleteBtn.classList.add('loading');
            deleteBtn.disabled = true;
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf_token"]')?.getAttribute('content') || '';
            
            // Prepare form data
            const formData = new FormData();
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }
            
            // Send delete request
            fetch(`<?= base_url('admin/customers/delete/') ?>${currentCustomerId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                deleteBtn.classList.remove('loading');
                deleteBtn.innerHTML = originalHtml;
                
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteCustomerModal'));
                if (modal) modal.hide();
                
                if (data.success) {
                    // Show success message
                    showToast('✅ ' + data.message, 'success');
                    
                    // Remove the row from table with animation
                    const customerRow = document.querySelector(`button[onclick*="${currentCustomerId}"]`)?.closest('tr');
                    if (customerRow) {
                        customerRow.style.transition = 'opacity 0.3s ease';
                        customerRow.style.opacity = '0';
                        setTimeout(() => {
                            customerRow.remove();
                            
                            // Show empty state if no customers left
                            const tbody = document.querySelector('tbody');
                            if (tbody && tbody.children.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                    
                } else {
                    showToast('❌ Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                deleteBtn.classList.remove('loading');
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalHtml;
                
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteCustomerModal'));
                if (modal) modal.hide();
                
                console.error('Delete error:', error);
                showToast('❌ An error occurred while deleting the customer. Please try again.', 'error');
            });
        });
    }
});

// Toggle customer status
function toggleCustomerStatus(customerId, currentStatus) {
    const action = currentStatus ? 'deactivate' : 'activate';
    const statusText = currentStatus ? 'deactivated' : 'activated';
    
    const confirmed = confirm(
        `<?= t('Admin.customers.actions.activate') ?> / <?= t('Admin.customers.actions.deactivate') ?>?\n\n` +
        `${currentStatus ? 
            '• Customer will not be able to login\n• Dashboard will be inaccessible\n• Data will be preserved' : 
            '• Customer will regain access\n• Dashboard will be accessible\n• All data will be restored'
        }`
    );
    
    if (!confirmed) return;
    
    // Find the status button and show loading state
    const statusBtn = event.target.closest('button');
    if (!statusBtn) return;
    
    const originalHtml = statusBtn.innerHTML;
    statusBtn.classList.add('loading');
    statusBtn.disabled = true;
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf_token"]')?.getAttribute('content') || '';
    
    // Prepare form data
    const formData = new FormData();
    if (csrfToken) {
        formData.append('csrf_token', csrfToken);
    }
    
    // Send status update request
    fetch(`<?= base_url('admin/customers/deactivate/') ?>${customerId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        statusBtn.classList.remove('loading');
        statusBtn.disabled = false;
        
        if (data.success) {
            // Update button appearance
            const newStatus = data.new_status;
            statusBtn.className = `btn btn-sm btn-${newStatus ? 'secondary' : 'success'}`;
            statusBtn.title = `${newStatus ? '<?= t('Admin.customers.actions.deactivate') ?>' : '<?= t('Admin.customers.actions.activate') ?>'} Customer`;
            statusBtn.innerHTML = `<i class="fas fa-${newStatus ? 'user-slash' : 'user-check'}"></i>`;
            
            // Update status badge in the table
            const statusCell = statusBtn.closest('tr')?.querySelector('td:nth-last-child(2)');
            const badge = statusCell?.querySelector('.badge');
            if (badge) {
                badge.className = `badge badge-${newStatus ? 'success' : 'danger'}`;
                badge.textContent = newStatus ? '<?= t('Admin.customers.table.active') ?>' : '<?= t('Admin.customers.table.inactive') ?>';
            }
            
            // Update the onclick attribute for next toggle
            statusBtn.setAttribute('onclick', `toggleCustomerStatus(${customerId}, ${newStatus})`);
            
            // Show success message
            showToast(data.message, 'success');
            
        } else {
            statusBtn.innerHTML = originalHtml;
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        statusBtn.classList.remove('loading');
        statusBtn.disabled = false;
        statusBtn.innerHTML = originalHtml;
        
        console.error('Status update error:', error);
        showToast('An error occurred while updating customer status.', 'error');
    });
}

// Toast notification function
function showToast(message, type) {
    // Remove existing toast
    const existingToast = document.getElementById('mainToast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.id = 'mainToast';
    toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 4000);
}
</script>
<?= $this->endSection() ?>