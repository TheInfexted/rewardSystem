<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="text-gold"><?= t('Admin.dashboard.title') ?></h1>
            <p class="text-muted"><?= t('Admin.dashboard.welcome', ['name' => $_SESSION['user_name']]) ?></p>
        </div>
    </div>
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark border-secondary">
                <div class="card-header bg-black border-secondary">
                    <h5 class="text-gold mb-0"><i class="bi bi-lightning"></i> <?= t('Admin.dashboard.quick_actions') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?= base_url('admin/landing-page') ?>" class="btn btn-gold w-100">
                                <i class="bi bi-gear"></i> <?= t('Admin.dashboard.landing_settings') ?>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= base_url('admin/bonus') ?>" class="btn btn-outline-gold w-100">
                                <i class="bi bi-gift"></i> <?= t('Admin.dashboard.bonus_claims') ?>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= base_url('admin/settings') ?>" class="btn btn-outline-gold w-100">
                                <i class="bi bi-gear"></i> <?= t('Admin.dashboard.admin_settings') ?>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= base_url('/') ?>" target="_blank" class="btn btn-outline-gold w-100">
                                <i class="bi bi-globe"></i> <?= t('Admin.dashboard.view_website') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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