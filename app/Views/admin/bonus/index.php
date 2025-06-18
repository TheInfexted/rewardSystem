<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gold"><?= t('Admin.bonus.title') ?></h1>
        <div class="btn-group">
            <a href="<?= base_url('admin/bonus/export?' . http_build_query($filters)) ?>" 
               class="btn btn-gold">
                <i class="bi bi-download"></i> <?= t('Admin.bonus.export_csv') ?>
            </a>
            <button type="button" class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#settingsModal">
                <i class="bi bi-gear"></i> <?= t('Admin.bonus.settings') ?>
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h5 class="text-gold"><?= number_format($stats['total_claims']) ?></h5>
                    <p class="text-light mb-0"><?= t('Admin.bonus.total_claims') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h5 class="text-warning"><?= number_format($stats['today_claims']) ?></h5>
                    <p class="text-light mb-0"><?= t('Admin.bonus.today_claims') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h5 class="text-info"><?= number_format($stats['unique_ips']) ?? 0 ?></h5>
                    <p class="text-light mb-0"><?= t('Admin.bonus.unique_ips') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-dark border-secondary mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label text-light"><?= t('Admin.bonus.filters.from_date') ?></label>
                    <input type="date" name="date_from" value="<?= $filters['date_from'] ?>" 
                           class="form-control bg-secondary text-light border-0">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-light"><?= t('Admin.bonus.filters.to_date') ?></label>
                    <input type="date" name="date_to" value="<?= $filters['date_to'] ?>" 
                           class="form-control bg-secondary text-light border-0">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-light"><?= t('Admin.bonus.filters.search') ?></label>
                    <input type="text" name="search" value="<?= esc($filters['search']) ?>" 
                           placeholder="<?= t('Admin.bonus.filters.search_placeholder') ?>" 
                           class="form-control bg-secondary text-light border-0">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-light"><?= t('Admin.bonus.filters.bonus_type') ?></label>
                    <select name="bonus_type" class="form-select bg-secondary text-light border-0">
                        <option value=""><?= t('Admin.bonus.filters.all_types') ?></option>
                        <?php foreach (['120% BONUS', '100% BONUS', '50% BONUS', '30% BONUS'] as $type): ?>
                            <option value="<?= $type ?>" <?= $filters['bonus_type'] === $type ? 'selected' : '' ?>>
                                <?= t('Database.bonus_types.' . $type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-gold me-2">
                        <i class="bi bi-search"></i> <?= t('Admin.bonus.filters.filter') ?>
                    </button>
                    <a href="<?= base_url('admin/bonus') ?>" class="btn btn-outline-secondary">
                        <?= t('Admin.bonus.filters.clear') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Claims Table -->
    <div class="card bg-dark border-secondary">
        <div class="card-body">
            <?php if (empty($claims)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h5 class="text-muted mt-3"><?= t('Admin.bonus.no_claims') ?></h5>
                    <p class="text-muted"><?= t('Admin.bonus.no_claims_desc') ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th><?= t('Admin.bonus.table.id') ?></th>
                                <th><?= t('Admin.bonus.table.claim_time') ?></th>
                                <th><?= t('Admin.bonus.table.user_details') ?></th>
                                <th><?= t('Admin.bonus.table.contact') ?></th>
                                <th><?= t('Admin.bonus.table.ip_address') ?></th>
                                <th><?= t('Admin.bonus.table.bonus') ?></th>
                                <th><?= t('Admin.bonus.table.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($claims as $claim): ?>
                            <tr>
                                <td class="text-gold">#<?= $claim['id'] ?></td>
                                <td>
                                    <small class="text-light">
                                        <?= format_date($claim['claim_time'], 'M j, Y') ?><br>
                                        <?= date('H:i:s', strtotime($claim['claim_time'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-light"><?= esc($claim['user_name']) ?></strong><br>
                                    <small class="text-muted"><?= t('Admin.bonus.table.session') ?>: <?= substr($claim['session_id'], 0, 16) ?>...</small>
                                </td>
                                <td>
                                    <div class="text-light">
                                        <i class="bi bi-telephone"></i> <?= esc($claim['phone_number']) ?><br>
                                        <?php if ($claim['email']): ?>
                                            <i class="bi bi-envelope"></i> <?= esc($claim['email']) ?>
                                        <?php else: ?>
                                            <small class="text-muted"><?= t('Admin.bonus.table.no_email') ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <code class="text-warning"><?= $claim['user_ip'] ?></code><br>
                                    <small class="text-muted">
                                        <?php 
                                        $ipCount = 0;
                                        foreach ($claims as $c) {
                                            if ($c['user_ip'] === $claim['user_ip']) $ipCount++;
                                        }
                                        echo "({$ipCount} " . t('Admin.bonus.table.claims') . ")";
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-gold text-light"><?= t('Database.bonus_types.' . $claim['bonus_type']) ?></span><br>
                                    <?php if ($claim['bonus_amount'] > 0): ?>
                                        <small class="text-success"><?= format_currency($claim['bonus_amount']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="viewDetails(<?= htmlspecialchars(json_encode($claim), ENT_QUOTES, 'UTF-8') ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination pagination-sm justify-content-center">
                        <!-- Previous Page -->
                        <li class="page-item <?= $currentPage === 1 ? 'disabled' : '' ?>">
                            <a class="page-link bg-secondary border-secondary text-light" 
                               href="<?= $currentPage > 1 ? base_url('admin/bonus?' . http_build_query(array_merge($filters, ['page' => $currentPage - 1]))) : '#' ?>">
                                <?= t('Admin.common.previous', [], 'Previous') ?>
                            </a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === 1 || $i === $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link bg-secondary border-secondary text-light" 
                                       href="<?= base_url('admin/bonus?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php elseif ($i === $currentPage - 3 || $i === $currentPage + 3): ?>
                                <li class="page-item disabled">
                                    <span class="page-link bg-secondary border-secondary text-light">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <!-- Next Page -->
                        <li class="page-item <?= $currentPage === $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link bg-secondary border-secondary text-light" 
                               href="<?= $currentPage < $totalPages ? base_url('admin/bonus?' . http_build_query(array_merge($filters, ['page' => $currentPage + 1]))) : '#' ?>">
                                <?= t('Admin.common.next', [], 'Next') ?>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title text-gold"><?= t('Admin.bonus.modal.title') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= base_url('admin/bonus/settings') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.bonus.modal.redirect_url') ?></label>
                        <input type="url" name="bonus_redirect_url" 
                               value="<?= esc($bonusSettings['bonus_redirect_url'] ?? '') ?>" 
                               class="form-control bg-secondary text-light border-0"
                               placeholder="<?= t('Admin.bonus.modal.redirect_placeholder') ?>"
                               required>
                        <small class="text-muted"><?= t('Admin.bonus.modal.redirect_help') ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-light"><?= t('Admin.bonus.modal.terms') ?></label>
                        <textarea name="bonus_terms_text" rows="4" 
                                  class="form-control bg-secondary text-light border-0"
                                  placeholder="<?= t('Admin.bonus.modal.terms_placeholder') ?>"><?= esc($bonusSettings['bonus_terms_text'] ?? '') ?></textarea>
                        <small class="text-muted"><?= t('Admin.bonus.modal.terms_help') ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="bonus_claim_enabled" id="bonus_claim_enabled" 
                                   class="form-check-input" value="1"
                                   <?= ($bonusSettings['bonus_claim_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label text-light" for="bonus_claim_enabled">
                                <?= t('Admin.bonus.modal.enable_claims') ?>
                            </label>
                            <small class="text-muted d-block"><?= t('Admin.bonus.modal.enable_help') ?></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary">
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

<!-- View Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title text-gold"><?= t('Admin.bonus.details.title') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row text-light">
                    <dt class="col-sm-4"><?= t('Admin.bonus.details.claim_id') ?>:</dt>
                    <dd class="col-sm-8" id="detail-id"></dd>
                    
                    <dt class="col-sm-4"><?= t('Admin.bonus.details.user_name') ?>:</dt>
                    <dd class="col-sm-8" id="detail-name"></dd>
                    
                    <dt class="col-sm-4"><?= t('Admin.bonus.details.phone') ?>:</dt>
                    <dd class="col-sm-8" id="detail-phone"></dd>
                    
                    <dt class="col-sm-4"><?= t('Admin.bonus.details.email') ?>:</dt>
                    <dd class="col-sm-8" id="detail-email"></dd>
                    
                    <dt class="col-sm-4"><?= t('Admin.bonus.details.ip_address') ?>:</dt>
                    <dd class="col-sm-8" id="detail-ip"></dd>
                    
                    <dt class="col-sm-4"><?= t('Admin.bonus.details.session_id') ?>:</dt>
                    <dd class="col-sm-8" id="detail-session"></dd>
                    
                    <dt class="col-sm-4"><?= t('Admin.bonus.details.bonus_type') ?>:</dt>
                    <dd class="col-sm-8" id="detail-bonus"></dd>
                    
                    <dt class="col-sm-4"><?= t('Admin.bonus.details.bonus_amount') ?>:</dt>
                    <dd class="col-sm-8" id="detail-amount"></dd>
                    
                    <dt class="col-sm-4"><?= t('Admin.bonus.details.claim_time') ?>:</dt>
                    <dd class="col-sm-8" id="detail-time"></dd>
                    
                    <dt class="col-sm-4"><?= t('Admin.bonus.details.user_agent') ?>:</dt>
                    <dd class="col-sm-8"><small id="detail-agent" class="text-muted"></small></dd>
                </dl>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= t('Admin.bonus.details.close') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Translations for JavaScript
const translations = {
    notProvided: '<?= t('Admin.bonus.details.not_provided') ?>',
    notRecorded: '<?= t('Admin.bonus.details.not_recorded') ?>',
    currencySymbol: '<?= get_current_locale() === 'zh' ? '¥' : '$' ?>'
};

// View claim details
function viewDetails(claim) {
    document.getElementById('detail-id').textContent = '#' + claim.id;
    document.getElementById('detail-name').textContent = claim.user_name;
    document.getElementById('detail-phone').textContent = claim.phone_number;
    document.getElementById('detail-email').textContent = claim.email || translations.notProvided;
    document.getElementById('detail-ip').textContent = claim.user_ip;
    document.getElementById('detail-session').textContent = claim.session_id;
    document.getElementById('detail-bonus').textContent = claim.bonus_type;
    document.getElementById('detail-amount').textContent = claim.bonus_amount > 0 
        ? translations.currencySymbol + parseFloat(claim.bonus_amount).toFixed(2) 
        : 'N/A';
    document.getElementById('detail-time').textContent = formatDateTime(claim.claim_time);
    document.getElementById('detail-agent').textContent = claim.user_agent || translations.notRecorded;
    
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

// Format date time based on locale
function formatDateTime(dateStr) {
    const date = new Date(dateStr);
    const locale = '<?= get_current_locale() ?>' === 'zh' ? 'zh-CN' : 'en-US';
    return date.toLocaleString(locale);
}

// Auto-refresh stats every 60 seconds
setInterval(() => {
    fetch('<?= base_url('admin/bonus/stats') ?>')
        .then(response => response.json())
        .then(data => {
            // Update stat cards without full page reload
            document.querySelector('.text-gold').textContent = data.total_claims.toLocaleString();
            document.querySelector('.text-warning').textContent = data.today_claims.toLocaleString();
            document.querySelector('.text-info').textContent = (data.unique_ips || 0).toLocaleString();
        })
        .catch(error => console.error('Stats update error:', error));
}, 60000);
</script>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.table-dark th {
    border-color: #495057;
    color: #ffd700;
}

.table-dark td {
    border-color: #495057;
    vertical-align: middle;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}

.pagination .page-link.bg-secondary:hover {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

.pagination .page-item.active .page-link {
    background-color: #ffd700 !important;
    border-color: #ffd700 !important;
    color: #000 !important;
}

.badge {
    font-size: 0.875rem;
}

code {
    font-size: 0.875rem;
}

#detailsModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}
</style>

<?= $this->endSection() ?>