<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gold"><?= t('Admin.reward_system.title') ?></h1>
        <a href="<?= base_url('admin/reward-system/add') ?>" class="btn btn-gold">
            <i class="bi bi-plus-circle"></i> <?= t('Admin.reward_system.add_new') ?>
        </a>
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

    <!-- Ads Table -->
    <div class="card bg-dark border-secondary">
        <div class="card-body">
            <?php if (empty($ads)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-badge-ad display-1 text-muted"></i>
                    <h4 class="text-muted mt-3"><?= t('Admin.reward_system.no_ads') ?></h4>
                    <p class="text-muted"><?= t('Admin.reward_system.no_ads_desc') ?></p>
                    <a href="<?= base_url('admin/reward-system/add') ?>" class="btn btn-gold mt-3">
                        <i class="bi bi-plus-circle"></i> <?= t('Admin.reward_system.add_new') ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th><?= t('Admin.reward_system.table.preview') ?></th>
                                <th><?= t('Admin.reward_system.table.title') ?></th>
                                <th><?= t('Admin.reward_system.table.type') ?></th>
                                <th><?= t('Admin.reward_system.table.status') ?></th>
                                <th><?= t('Admin.reward_system.table.order') ?></th>
                                <th><?= t('Admin.reward_system.table.schedule') ?></th>
                                <th><?= t('Admin.reward_system.table.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody id="sortableAds">
                            <?php foreach ($ads as $ad): ?>
                                <tr data-id="<?= $ad['id'] ?>">
                                    <td>
                                        <?php 
                                        $mediaUrl = $ad['media_file'] 
                                            ? base_url('uploads/reward_ads/' . $ad['media_file'])
                                            : $ad['media_url'];
                                        ?>
                                        <?php if ($ad['ad_type'] === 'video'): ?>
                                            <video width="100" height="60" muted>
                                                <source src="<?= $mediaUrl ?>" type="video/mp4">
                                            </video>
                                        <?php else: ?>
                                            <img src="<?= $mediaUrl ?>" alt="Preview" 
                                                 style="max-width: 100px; max-height: 60px;" 
                                                 class="img-thumbnail">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($ad['ad_title']) ?></strong>
                                        <?php if ($ad['ad_description']): ?>
                                            <br><small class="text-muted"><?= character_limiter(esc($ad['ad_description']), 50) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $ad['ad_type'] === 'video' ? 'primary' : 'info' ?>">
                                            <i class="bi bi-<?= $ad['ad_type'] === 'video' ? 'play-circle' : 'image' ?>"></i>
                                            <?= ucfirst($ad['ad_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($ad['is_active']): ?>
                                            <span class="badge bg-success"><?= t('Admin.reward_system.table.active') ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= t('Admin.reward_system.table.inactive') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm bg-secondary text-light order-input" 
                                               value="<?= $ad['display_order'] ?>" 
                                               data-id="<?= $ad['id'] ?>"
                                               style="width: 60px;">
                                    </td>
                                    <td>
                                        <?php if ($ad['start_date'] || $ad['end_date']): ?>
                                            <small>
                                                <?php if ($ad['start_date']): ?>
                                                    From: <?= date('M d', strtotime($ad['start_date'])) ?><br>
                                                <?php endif; ?>
                                                <?php if ($ad['end_date']): ?>
                                                    To: <?= date('M d', strtotime($ad['end_date'])) ?>
                                                <?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted"><?= t('Admin.reward_system.table.always') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= base_url('admin/reward-system/edit/' . $ad['id']) ?>" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= base_url('admin/reward-system/delete/' . $ad['id']) ?>" 
                                               class="btn btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this ad?')"
                                               title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.img-thumbnail {
    background-color: #333;
    border-color: #444;
}

.order-input {
    border: 1px solid #444;
}

.order-input:focus {
    border-color: #FFD700;
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
}

#sortableAds tr {
    cursor: move;
}

#sortableAds tr.ui-sortable-helper {
    background-color: #2a2a2a;
    opacity: 0.8;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update order on input change
    document.querySelectorAll('.order-input').forEach(input => {
        input.addEventListener('change', function() {
            updateOrder(this.dataset.id, this.value);
        });
    });
    
    // Make table sortable
    if (typeof jQuery !== 'undefined' && jQuery.fn.sortable) {
        $('#sortableAds').sortable({
            update: function(event, ui) {
                updateAllOrders();
            }
        });
    }
});

function updateOrder(id, order) {
    fetch('<?= base_url('admin/reward-system/update-order') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `id=${id}&order=${order}&<?= csrf_token() ?>=<?= csrf_hash() ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Order updated successfully', 'success');
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateAllOrders() {
    const rows = document.querySelectorAll('#sortableAds tr');
    rows.forEach((row, index) => {
        const id = row.dataset.id;
        const orderInput = row.querySelector('.order-input');
        orderInput.value = index;
        updateOrder(id, index);
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = `top: 20px; right: 20px; z-index: 9999; min-width: 250px;`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<?= $this->endSection() ?>