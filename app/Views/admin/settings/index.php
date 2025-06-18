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
                    <h5 class="text-gold mb-0"><i class="bi bi-key"></i> <?= t('Admin.settings.password.title') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('password_success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('password_success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('password_error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('password_error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->get('password_errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php foreach (session()->get('password_errors') as $error): ?>
                                <p class="mb-0"><?= esc($error) ?></p>
                            <?php endforeach; ?>
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

        <!-- System Expiration Info -->
        <div class="col-md-12">
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
<?= $this->endSection() ?>