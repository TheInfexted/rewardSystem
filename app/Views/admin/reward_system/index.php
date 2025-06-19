<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gold">Reward System Ads Management</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#settingsModal">
                <i class="bi bi-gear"></i> Settings
            </button>
        </div>
    </div>
<?= $this->endSection() ?>