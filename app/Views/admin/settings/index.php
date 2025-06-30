<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="text-gold"><?= t('Admin.settings.title') ?></h1>
        </div>
    </div>

    <div class="row">
        <!-- Profile Settings -->
        <div class="col-md-6">
            <div class="card bg-dark border-gold mb-4">
                <div class="card-header bg-black border-gold">
                    <h5 class="text-gold mb-0"><i class="bi bi-person-circle"></i> <?= t('Admin.settings.profile.title') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->get('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php foreach (session()->get('errors') as $error): ?>
                                <p class="mb-0"><?= esc($error) ?></p>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('admin/settings/profile') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.settings.profile.name') ?></label>
                            <input type="text" name="name" class="form-control bg-dark text-light border-gold"
                                   value="<?= old('name', $user['name']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.settings.profile.email') ?></label>
                            <input type="email" name="email" class="form-control bg-dark text-light border-gold"
                                   value="<?= old('email', $user['email']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.settings.profile.member_since') ?></label>
                            <input type="text" class="form-control bg-dark text-light border-gold"
                                   value="<?= date('F d, Y', strtotime($user['created_at'])) ?>" readonly>
                        </div>
                        
                        <?php if ($user['last_login']): ?>
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.settings.profile.last_login') ?></label>
                            <input type="text" class="form-control bg-dark text-light border-gold"
                                   value="<?= date('F d, Y h:i A', strtotime($user['last_login'])) ?>" readonly>
                        </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-gold">
                            <i class="bi bi-save"></i> <?= t('Admin.settings.profile.update') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password Settings -->
        <div class="col-md-6">
            <div class="card bg-dark border-gold mb-4">
                <div class="card-header bg-black border-gold">
                    <h5 class="text-gold mb-0"><i class="bi bi-shield-lock"></i> <?= t('Admin.settings.password.title') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('password_errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php foreach (session()->getFlashdata('password_errors') as $error): ?>
                                <p class="mb-0"><?= esc($error) ?></p>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('password_error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('password_error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('password_success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('password_success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('admin/settings/password') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.settings.password.current') ?></label>
                            <input type="password" name="current_password" class="form-control bg-dark text-light border-gold" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.settings.password.new') ?></label>
                            <input type="password" name="new_password" class="form-control bg-dark text-light border-gold" required>
                            <small class="text-muted"><?= t('Admin.settings.password.new_help') ?></small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.settings.password.confirm') ?></label>
                            <input type="password" name="confirm_password" class="form-control bg-dark text-light border-gold" required>
                        </div>
                        
                        <button type="submit" class="btn btn-gold">
                            <i class="bi bi-shield-check"></i> <?= t('Admin.settings.password.update') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Customer Service Settings -->
        <div class="col-md-6">
            <div class="card bg-dark border-gold mb-4">
                <div class="card-header bg-black border-gold">
                    <h5 class="text-gold mb-0"><i class="bi bi-chat-dots"></i> <?= t('Admin.settings.customer_service.title') ?></h5>
                </div>
                <div class="card-body">
                    <div id="customerServiceAlert"></div>
                    
                    <form id="customerServiceForm">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label text-gold">
                                <i class="bi bi-whatsapp me-1"></i><?= t('Admin.settings.customer_service.whatsapp_number') ?>
                            </label>
                            <input type="text" name="reward_whatsapp_number" 
                                   class="form-control bg-dark text-light border-gold"
                                   value="<?= esc($customer_service_settings['reward_whatsapp_number'] ?? '601159599022') ?>" 
                                   placeholder="60xxxxxxxxx" required>
                            <small class="text-muted"><?= t('Admin.settings.customer_service.whatsapp_help') ?></small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-gold">
                                <i class="bi bi-telegram me-1"></i><?= t('Admin.settings.customer_service.telegram_username') ?>
                            </label>
                            <input type="text" name="reward_telegram_username" 
                                   class="form-control bg-dark text-light border-gold"
                                   value="<?= esc($customer_service_settings['reward_telegram_username'] ?? 'harryford19') ?>" 
                                   placeholder="username" required>
                            <small class="text-muted"><?= t('Admin.settings.customer_service.telegram_help') ?></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gold">
                                <i class="bi bi-clock me-1"></i><?= t('Admin.settings.customer_service.service_hours') ?>
                            </label>
                            <input type="text" name="customer_service_hours" 
                                   class="form-control bg-dark text-light border-gold"
                                   value="<?= esc($customer_service_settings['customer_service_hours'] ?? '9:00 AM - 6:00 PM (GMT+8)') ?>" 
                                   placeholder="9:00 AM - 6:00 PM (GMT+8)">
                            <small class="text-muted"><?= t('Admin.settings.customer_service.service_hours_help') ?></small>
                        </div>
                        
                        <button type="submit" class="btn btn-gold" id="updateCustomerServiceBtn">
                            <i class="bi bi-save"></i> <?= t('Admin.settings.customer_service.update') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- System Expiration Info -->
        <div class="col-md-6">
            <div class="card bg-dark border-danger mb-4">
                <div class="card-header bg-black border-danger">
                    <h5 class="text-danger mb-0"><i class="bi bi-calendar-x"></i> <?= t('Admin.settings.expiration.title') ?></h5>
                </div>
                <div class="card-body">
                    <p class="text-light mb-1">
                        <?= t('Admin.settings.expiration.notice') ?>
                    </p>
                    <h4 class="text-danger"><?= date('F d, Y', strtotime('2025-12-20')) ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="settingsToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="bi bi-gear me-2"></i>
            <strong class="me-auto"><?= t('Admin.settings.title') ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="settingsToastBody">
            <!-- Toast message will be inserted here -->
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Customer Service Form Handler
    const customerServiceForm = document.getElementById('customerServiceForm');
    if (customerServiceForm) {
        customerServiceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateCustomerServiceSettings();
        });
    }
});

/**
 * Update Customer Service Settings
 */
function updateCustomerServiceSettings() {
    const form = document.getElementById('customerServiceForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('updateCustomerServiceBtn');
    const alertDiv = document.getElementById('customerServiceAlert');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i><?= t('Admin.common.saving') ?>...';
    submitBtn.disabled = true;
    
    // Clear previous alerts
    alertDiv.innerHTML = '';
    
    fetch('<?= base_url('admin/settings/customer-service') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(alertDiv, 'success', data.message);
            showToast('success', data.message);
        } else {
            showAlert(alertDiv, 'danger', data.message);
            if (data.errors) {
                console.error('Validation errors:', data.errors);
                let errorList = '<ul class="mb-0">';
                for (let field in data.errors) {
                    errorList += `<li>${data.errors[field]}</li>`;
                }
                errorList += '</ul>';
                showAlert(alertDiv, 'danger', '<?= t('Admin.settings.validation_errors') ?>:<br>' + errorList);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert(alertDiv, 'danger', '<?= t('Admin.settings.update_error') ?>');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Show alert message
 */
function showAlert(container, type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    container.innerHTML = alertHtml;
    
    // Auto-dismiss success alerts after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
}

/**
 * Show toast notification
 */
function showToast(type, message) {
    const toastEl = document.getElementById('settingsToast');
    const toastBody = document.getElementById('settingsToastBody');
    
    // Update toast content
    toastBody.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
    `;
    
    // Update toast classes
    toastEl.className = `toast ${type === 'success' ? 'bg-success' : 'bg-danger'} text-white`;
    
    // Show toast
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: 3000
    });
    toast.show();
}
</script>
<?= $this->endSection() ?>