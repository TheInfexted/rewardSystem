<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gold"><?= t('Admin.reports.title') ?></h1>
        <div class="btn-group">
            <a href="<?= base_url('admin/reports/export?' . http_build_query($filters)) ?>" 
               class="btn btn-gold">
                <i class="bi bi-download"></i> <?= t('Admin.reports.export_csv') ?>
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h5 class="text-gold"><?= number_format($stats['total_spins']) ?></h5>
                    <p class="text-light mb-0"><?= t('Admin.reports.stats.total_spins') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h5 class="text-warning"><?= number_format($stats['today_spins']) ?></h5>
                    <p class="text-light mb-0"><?= t('Admin.reports.stats.today_spins') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h5 class="text-info"><?= number_format($stats['unique_users']) ?></h5>
                    <p class="text-light mb-0"><?= t('Admin.reports.stats.unique_users') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <h5 class="text-success"><?= number_format($stats['prize_spins']) ?></h5>
                    <p class="text-light mb-0"><?= t('Admin.reports.stats.prize_spins') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-dark border-secondary mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label text-light"><?= t('Admin.reports.filters.from_date') ?></label>
                    <input type="date" name="date_from" value="<?= $filters['date_from'] ?>" 
                           class="form-control bg-secondary text-light border-0">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-light"><?= t('Admin.reports.filters.to_date') ?></label>
                    <input type="date" name="date_to" value="<?= $filters['date_to'] ?>" 
                           class="form-control bg-secondary text-light border-0">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-light"><?= t('Admin.reports.filters.prize_type') ?></label>
                    <select name="prize_type" class="form-select bg-secondary text-light border-0">
                        <option value=""><?= t('Admin.reports.filters.all_types') ?></option>
                        <option value="cash" <?= $filters['prize_type'] === 'cash' ? 'selected' : '' ?>><?= t('Admin.reports.filters.cash') ?></option>
                        <option value="product" <?= $filters['prize_type'] === 'product' ? 'selected' : '' ?>><?= t('Admin.reports.filters.product') ?></option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-light"><?= t('Admin.reports.filters.user_ip') ?></label>
                    <input type="text" name="user_ip" value="<?= esc($filters['user_ip']) ?>" 
                           placeholder="<?= t('Admin.reports.filters.ip_placeholder') ?>" 
                           class="form-control bg-secondary text-light border-0">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-light"><?= t('Admin.reports.filters.search') ?></label>
                    <input type="text" name="search" value="<?= esc($filters['search']) ?>" 
                           placeholder="<?= t('Admin.reports.filters.search_placeholder') ?>" 
                           class="form-control bg-secondary text-light border-0">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-gold me-2">
                        <i class="bi bi-search"></i> <?= t('Admin.reports.filters.filter') ?>
                    </button>
                    <a href="<?= base_url('admin/reports') ?>" class="btn btn-outline-secondary">
                        <?= t('Admin.reports.filters.clear') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Spins Table -->
    <div class="card bg-dark border-secondary">
        <div class="card-body">
            <?php if (empty($spins)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-bar-chart display-1 text-muted"></i>
                    <h5 class="text-muted mt-3"><?= t('Admin.reports.no_spins') ?></h5>
                    <p class="text-muted"><?= t('Admin.reports.no_spins_desc') ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th><?= t('Admin.reports.table.id') ?></th>
                                <th><?= t('Admin.reports.table.time') ?></th>
                                <th><?= t('Admin.reports.table.session_info') ?></th>
                                <th><?= t('Admin.reports.table.user_ip') ?></th>
                                <th><?= t('Admin.reports.table.item_won') ?></th>
                                <th><?= t('Admin.reports.table.prize') ?></th>
                                <th><?= t('Admin.reports.table.spin_number') ?></th>
                                <th><?= t('Admin.reports.table.bonus') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($spins as $spin): ?>
                            <tr>
                                <td class="text-gold">#<?= $spin['id'] ?></td>
                                <td>
                                    <small class="text-light">
                                        <?= format_date($spin['spin_time'], 'M j, H:i') ?><br>
                                        <span class="text-muted"><?= date('Y', strtotime($spin['spin_time'])) ?></span>
                                    </small>
                                </td>
                                <td>
                                    <?php if (!empty($spin['session_id'])): ?>
                                        <code class="text-info"><?= substr($spin['session_id'], 0, 100) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted"><?= t('Admin.reports.table.no_session') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code class="text-warning"><?= $spin['user_ip'] ?></code>
                                </td>
                                <td>
                                    <span class="text-light"><?= t('Database.wheel_items.' . $spin['item_won'], [], esc($spin['item_won'])) ?></span>
                                </td>
                                <td>
                                    <?php if ($spin['prize_amount'] > 0): ?>
                                        <span class="text-success">
                                            <?= format_currency($spin['prize_amount']) ?>
                                        </span>
                                        <br><small class="text-muted"><?= t('Database.prize_types.' . $spin['prize_type']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted"><?= t('Admin.reports.table.no_prize') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($spin['spin_number']) && $spin['spin_number']): ?>
                                        <span class="badge bg-info">#<?= $spin['spin_number'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($spin['bonus_claimed']) && $spin['bonus_claimed']): ?>
                                        <span class="badge bg-success"><?= t('Admin.reports.table.claimed') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= t('Admin.reports.table.no') ?></span>
                                    <?php endif; ?>
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
                        <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link bg-secondary border-secondary text-light" 
                               href="<?= base_url('admin/reports?' . http_build_query(array_merge($filters, ['page' => $currentPage - 1]))) ?>">
                                <?= t('Admin.common.previous') ?>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                            <a class="page-link bg-secondary border-secondary text-light" 
                               href="<?= base_url('admin/reports?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <!-- Next Page -->
                        <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link bg-secondary border-secondary text-light" 
                               href="<?= base_url('admin/reports?' . http_build_query(array_merge($filters, ['page' => $currentPage + 1]))) ?>">
                                <?= t('Admin.common.next') ?>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    <!-- Page Info -->
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <?= t('Admin.reports.showing', [
                                'start' => (($currentPage - 1) * $perPage) + 1,
                                'end' => min($currentPage * $perPage, $totalSpins),
                                'total' => number_format($totalSpins)
                            ]) ?>
                        </small>
                    </div>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Prize Distribution -->
    <?php if (!empty($stats['prize_distribution'])): ?>
    <div class="card bg-dark border-secondary mt-4">
        <div class="card-header">
            <h5 class="text-gold mb-0"><?= t('Admin.reports.prize_distribution') ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach (array_slice($stats['prize_distribution'], 0, 6) as $prize): ?>
                <div class="col-md-2 text-center mb-3">
                    <h6 class="text-light"><?= t('Database.wheel_items.' . $prize['item_won'], [], esc($prize['item_won'])) ?></h6>
                    <div class="text-gold h5"><?= number_format($prize['count']) ?></div>
                    <small class="text-muted">
                        <?php if ($prize['total_amount'] > 0): ?>
                            <?= format_currency($prize['total_amount']) ?>
                        <?php else: ?>
                            <?= t('Admin.reports.no_cash_value') ?>
                        <?php endif; ?>
                    </small>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Date and currency formatting helpers
function format_date(dateStr, format = null) {
    const date = new Date(dateStr);
    const locale = '<?= get_current_locale() ?>' === 'zh' ? 'zh-CN' : 'en-US';
    
    if (format === 'short') {
        return date.toLocaleDateString(locale, { month: 'short', day: 'numeric' });
    }
    
    return date.toLocaleDateString(locale);
}

function format_currency(amount) {
    const locale = '<?= get_current_locale() ?>';
    const symbol = locale === 'zh' ? '¥' : '$';
    return symbol + parseFloat(amount).toFixed(2);
}
</script>

<style>
.page-item.active .page-link {
    background-color: #ffd700 !important;
    border-color: #ffd700 !important;
    color: #000 !important;
}

.table-dark tbody tr:hover {
    background-color: rgba(255, 215, 0, 0.1);
}

.card:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

code {
    font-size: 0.85rem;
}
</style>

<?= $this->endSection() ?>