<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="text-gold"><?= t('Admin.wheel.add_new') ?></h1>
                <a href="<?= base_url('admin/wheel') ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> <?= t('Admin.wheel.form.back') ?>
                </a>
            </div>
        </div>
    </div>

    <?php if (session()->get('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php foreach (session()->get('errors') as $error): ?>
                <p class="mb-0"><?= esc($error) ?></p>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card bg-dark border-gold">
        <div class="card-header bg-black border-gold">
            <h5 class="text-gold mb-0"><i class="bi bi-plus-circle"></i> <?= t('Admin.wheel.new_item') ?></h5>
        </div>
        <div class="card-body">
            <form action="<?= base_url('admin/wheel/add') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.wheel.form.item_name') ?> <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="item_name" 
                                   class="form-control bg-dark text-light border-gold" 
                                   value="<?= old('item_name') ?>" 
                                   required
                                   placeholder="<?= t('Admin.wheel.form.item_name_placeholder') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.wheel.form.prize_amount') ?></label>
                            <input type="number" 
                                   name="item_prize" 
                                   class="form-control bg-dark text-light border-gold" 
                                   value="<?= old('item_prize', 0) ?>" 
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00">
                            <small class="text-muted"><?= t('Admin.wheel.form.prize_help') ?></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.wheel.form.type') ?> <span class="text-danger">*</span></label>
                            <select name="item_types" class="form-select bg-dark text-light border-gold" required>
                                <option value="cash" <?= old('item_types') == 'cash' ? 'selected' : '' ?>><?= t('Admin.wheel.form.cash') ?></option>
                                <option value="product" <?= old('item_types') == 'product' ? 'selected' : '' ?>><?= t('Admin.wheel.form.product') ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.wheel.form.winning_rate') ?> <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="winning_rate" 
                                   class="form-control bg-dark text-light border-gold" 
                                   value="<?= old('winning_rate', 12.5) ?>" 
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   required>
                            <small class="text-muted"><?= t('Admin.wheel.form.rate_help_special', [], 'Percentage chance of winning this item (0 for special items)') ?></small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-gold"><?= t('Admin.wheel.form.order') ?> <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="order" 
                                   class="form-control bg-dark text-light border-gold" 
                                   value="<?= old('order', 1) ?>" 
                                   min="1"
                                   max="8"
                                   required>
                            <small class="text-muted"><?= t('Admin.wheel.form.order_help') ?></small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               <?= old('is_active', 1) ? 'checked' : '' ?>>
                        <label class="form-check-label text-gold" for="is_active">
                            <?= t('Admin.wheel.form.active') ?>
                        </label>
                    </div>
                </div>

                <hr class="border-gold">

                <div class="text-end">
                    <a href="<?= base_url('admin/wheel') ?>" class="btn btn-secondary"><?= t('Admin.wheel.form.cancel') ?></a>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-save"></i> <?= t('Admin.wheel.form.save') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tips -->
    <div class="mt-4">
        <div class="card bg-dark border-gold">
            <div class="card-body">
                <h6 class="text-gold"><i class="bi bi-lightbulb"></i> <?= t('Admin.wheel.tips.title') ?></h6>
                <ul class="text-light mb-0">
                    <li><?= t('Admin.wheel.tips.tip1') ?></li>
                    <li><?= t('Admin.wheel.tips.tip2') ?></li>
                    <li><?= t('Admin.wheel.tips.tip3') ?></li>
                    <li><?= t('Admin.wheel.tips.tip4') ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>