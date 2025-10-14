<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="text-gold"><?= t('Admin.wheel.title') ?></h1>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-gold" data-bs-toggle="modal" data-bs-target="#wheelSettingsModal">
                        <i class="bi bi-gear"></i> <?= t('Admin.wheel.settings.button') ?>
                    </button>
                    <a href="<?= base_url('admin/wheel/add') ?>" class="btn btn-gold">
                        <i class="bi bi-plus-circle"></i> <?= t('Admin.wheel.add_item') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Winning Rate Summary -->
    <div class="card bg-dark border-gold mb-4">
        <div class="card-body">
            <h5 class="text-gold"><?= t('Admin.wheel.rate_summary') ?></h5>
            <?php 
            $totalRate = 0;
            foreach ($wheel_items as $item) {
                if ($item['is_active']) {
                    $totalRate += $item['winning_rate'];
                }
            }
            ?>
            <div class="progress" style="height: 25px;">
                <div class="progress-bar bg-<?= $totalRate > 100 ? 'danger' : ($totalRate == 100 ? 'success' : 'warning') ?>" 
                     role="progressbar" 
                     style="width: <?= min($totalRate, 100) ?>%">
                    <?= number_format($totalRate, 2) ?>%
                </div>
            </div>
            <small class="text-muted"><?= t('Admin.wheel.rate_help') ?></small>
        </div>
    </div>

    <!-- Wheel Items Table -->
    <div class="card bg-dark border-gold">
        <div class="card-header bg-black border-gold">
            <h5 class="text-gold mb-0"><i class="bi bi-list"></i> <?= t('Admin.wheel.max_items') ?></h5>
        </div>
        <div class="card-body">
            <?php if (empty($wheel_items)): ?>
                <p class="text-center text-muted"><?= t('Admin.wheel.no_items') ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr class="text-gold">
                                <th><?= t('Admin.wheel.table.order') ?></th>
                                <th><?= t('Admin.wheel.table.name') ?></th>
                                <th><?= t('Admin.wheel.table.prize') ?></th>
                                <th><?= t('Admin.wheel.table.type') ?></th>
                                <th><?= t('Admin.wheel.table.win_rate') ?></th>
                                <th><?= t('Admin.wheel.table.status') ?></th>
                                <th><?= t('Admin.wheel.table.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wheel_items as $item): ?>
                            <tr>
                                <td><?= $item['order'] ?></td>
                                <td><?= t('Database.wheel_items.' . $item['item_name'], [], esc($item['item_name'])) ?></td>
                                <td>
                                    <?php if ($item['item_prize'] > 0): ?>
                                        <?= format_currency($item['item_prize']) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $item['item_types'] == 'cash' ? 'success' : 'info' ?>">
                                        <?= t('Database.prize_types.' . $item['item_types']) ?>
                                    </span>
                                </td>
                                <td><?= number_format($item['winning_rate'], 2) ?>%</td>
                                <td>
                                    <?php if ($item['is_active']): ?>
                                        <span class="badge bg-success"><?= t('Admin.wheel.table.active') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= t('Admin.wheel.table.inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('admin/wheel/edit/' . $item['item_id']) ?>" 
                                       class="btn btn-sm btn-outline-gold">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?= $item['item_id'] ?>)" 
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Instructions -->
    <div class="mt-4">
        <div class="card bg-dark border-gold">
            <div class="card-header bg-black border-gold">
                <h5 class="text-gold mb-0"><i class="bi bi-info-circle"></i> <?= t('Admin.wheel.instructions.title') ?></h5>
            </div>
            <div class="card-body">
                <ul class="text-light">
                    <li><?= t('Admin.wheel.instructions.max_8') ?></li>
                    <li><?= t('Admin.wheel.instructions.total_100') ?></li>
                    <li><?= t('Admin.wheel.instructions.order_position') ?></li>
                    <li><?= t('Admin.wheel.instructions.prize_type') ?></li>
                    <li><?= t('Admin.wheel.instructions.inactive_hidden') ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Wheel Settings Modal -->
<div class="modal fade" id="wheelSettingsModal" tabindex="-1" aria-labelledby="wheelSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-gold">
            <div class="modal-header bg-black border-gold">
                <h5 class="modal-title text-gold" id="wheelSettingsModalLabel">
                    <i class="bi bi-gear"></i> <?= t('Admin.wheel.settings.modal_title') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="wheelSettingsForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="maxDailySpins" class="form-label text-light">
                            <i class="bi bi-arrow-clockwise"></i> <?= t('Admin.wheel.settings.max_daily_spins') ?>
                        </label>
                        <input type="number" 
                               class="form-control bg-dark text-light border-gold" 
                               id="maxDailySpins" 
                               name="max_daily_spins" 
                               min="1" 
                               required>
                        <div class="form-text text-muted">
                            <?= t('Admin.wheel.settings.max_daily_spins_help') ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-black border-gold">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('Admin.wheel.settings.cancel') ?></button>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-check-circle"></i> <?= t('Admin.wheel.settings.save_settings') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const translations = {
    confirmDelete: '<?= t('Admin.wheel.confirm_delete') ?>',
    totalRateWarning: '<?= t('Admin.wheel.total_rate_warning', [], 'Total winning rate is {rate}%. Should be 100% for balanced odds.') ?>',
    settingsSaved: '<?= t('Admin.wheel.settings.settings_saved') ?>',
    settingsFailed: '<?= t('Admin.wheel.settings.settings_failed') ?>',
    settingsError: '<?= t('Admin.wheel.settings.settings_error') ?>',
    savingSettings: '<?= t('Admin.wheel.settings.saving_settings') ?>'
};

function confirmDelete(id) {
    if (confirm(translations.confirmDelete)) {
        window.location.href = '<?= base_url('admin/wheel/delete') ?>/' + id;
    }
}

// Format currency helper
function format_currency(amount) {
    const locale = '<?= get_current_locale() ?>';
    const symbol = locale === 'zh' ? '¥' : '$';
    return symbol + parseFloat(amount).toFixed(2);
}

// Check winning rates on page load
document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= base_url('admin/wheel/check-rates') ?>')
        .then(response => response.json())
        .then(data => {
            if (!data.valid && data.total !== 100) {
                console.warn(translations.totalRateWarning.replace('{rate}', data.total));
            }
        });
    
    // Load current settings when modal is opened
    document.getElementById('wheelSettingsModal').addEventListener('show.bs.modal', function () {
        loadWheelSettings();
    });
    
    // Handle settings form submission
    document.getElementById('wheelSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveWheelSettings();
    });
});

// Load current wheel settings
function loadWheelSettings() {
    fetch('<?= base_url('admin/wheel/get-settings') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('maxDailySpins').value = data.settings.max_daily_spins || 3;
            }
        })
        .catch(error => {
            console.error('Error loading settings:', error);
        });
}

// Save wheel settings
function saveWheelSettings() {
    const formData = new FormData(document.getElementById('wheelSettingsForm'));
    const submitButton = document.querySelector('#wheelSettingsForm button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Disable button and show loading
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i> ' + translations.savingSettings;
    
    fetch('<?= base_url('admin/wheel/update-settings') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert(translations.settingsSaved, 'success');
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('wheelSettingsModal'));
            modal.hide();
        } else {
            showAlert(data.message || translations.settingsFailed, 'error');
        }
    })
    .catch(error => {
        console.error('Error saving settings:', error);
        showAlert(translations.settingsError, 'error');
    })
    .finally(() => {
        // Re-enable button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

// Show alert message
function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert alert at the top of the page
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}
</script>

<style>
.btn-outline-gold {
    color: #FFD700;
    border-color: #FFD700;
}
.btn-outline-gold:hover {
    background-color: #FFD700;
    border-color: #FFD700;
    color: #000;
}
</style>
<?= $this->endSection() ?>