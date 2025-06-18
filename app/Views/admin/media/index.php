<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="text-gold"><?= t('Admin.media.title') ?></h1>
                    <p class="text-light mb-0"><?= t('Admin.media.total_size') ?>: <?= $totalSize ?> | <?= count($images) ?> <?= t('Admin.media.files') ?></p>
                </div>
                <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-upload"></i> <?= t('Admin.media.upload_image') ?>
                </button>
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

    <?php if (empty($images)): ?>
        <div class="card bg-dark border-gold">
            <div class="card-body text-center py-5">
                <i class="bi bi-images text-gold" style="font-size: 4rem;"></i>
                <h4 class="text-gold mt-3"><?= t('Admin.media.no_images') ?></h4>
                <p class="text-light"><?= t('Admin.media.upload_first') ?></p>
                <button class="btn btn-gold mt-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-upload"></i> <?= t('Admin.media.upload_image') ?>
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($images as $image): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card bg-dark border-gold h-100">
                        <div class="position-relative">
                            <img src="<?= base_url($image['path']) ?>" class="card-img-top" alt="<?= $image['name'] ?>" style="height: 200px; object-fit: cover;">
                            <span class="position-absolute top-0 end-0 m-2 badge bg-<?= $image['type'] === 'content' ? 'primary' : 'secondary' ?>">
                                <?= $image['type'] ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title text-gold text-truncate" title="<?= $image['name'] ?>"><?= $image['name'] ?></h6>
                            <p class="card-text text-light small mb-2">
                                <?= t('Admin.media.size', [], 'Size') ?>: <?= $image['size'] ?><br>
                                <?= t('Admin.media.date', [], 'Date') ?>: <?= format_date($image['date']) ?>
                            </p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-sm btn-outline-gold" onclick="viewImage('<?= base_url($image['path']) ?>', '<?= $image['name'] ?>')">
                                    <i class="bi bi-eye" style="color: white;"> <?= t('Admin.media.view') ?></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $image['name'] ?>')">
                                    <i class="bi bi-trash"></i> <?= t('Admin.media.delete') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-gold">
            <div class="modal-header bg-black border-gold">
                <h5 class="modal-title text-gold"><?= t('Admin.media.upload_modal.title') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/media/upload') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-gold"><?= t('Admin.media.upload_modal.select_image') ?></label>
                        <input type="file" name="image" class="form-control bg-dark text-light border-gold" accept="image/*" required>
                        <small class="text-light"><?= t('Admin.media.upload_modal.max_size') ?></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-gold"><?= t('Admin.media.upload_modal.type') ?></label>
                        <select name="type" class="form-select bg-dark text-light border-gold">
                            <option value="content"><?= t('Admin.media.upload_modal.content_image') ?></option>
                            <option value="header"><?= t('Admin.media.upload_modal.header_footer') ?></option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-gold">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('Admin.media.upload_modal.cancel') ?></button>
                    <button type="submit" class="btn btn-gold"><?= t('Admin.media.upload_modal.upload') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Image Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark border-gold">
            <div class="modal-header bg-black border-gold">
                <h5 class="modal-title text-gold" id="viewModalTitle"><?= t('Admin.media.view_modal.title') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="viewModalImage" src="" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</div>

<script>
const translations = {
    confirmDelete: '<?= t('Admin.media.confirm_delete') ?>'
};

function viewImage(src, name) {
    document.getElementById('viewModalImage').src = src;
    document.getElementById('viewModalTitle').textContent = name;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

function confirmDelete(filename) {
    if (confirm(translations.confirmDelete)) {
        window.location.href = '<?= base_url('admin/media/delete') ?>/' + encodeURIComponent(filename);
    }
}

// Helper function for date formatting
function format_date(dateStr) {
    const date = new Date(dateStr);
    const locale = '<?= get_current_locale() ?>' === 'zh' ? 'zh-CN' : 'en-US';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString(locale, options);
}
</script>

<style>
.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}
.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
}
</style>
<?= $this->endSection() ?>