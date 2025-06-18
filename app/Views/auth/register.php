<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - CMS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('css/landing-page.css') ?>">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            padding: 2rem 0;
        }
        .register-card {
            width: 100%;
            max-width: 500px;
            background-color: #1a1a1a;
            border: 2px solid #FFD700;
            box-shadow: 0 10px 40px rgba(255, 215, 0, 0.3);
        }
        .register-header {
            background-color: #000000;
            border-bottom: 2px solid #FFD700;
            padding: 2rem;
            text-align: center;
        }
        .form-floating label {
            color: #B8860B;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card rounded">
            <div class="register-header">
                <h2 class="text-gold mb-0">
                    <i class="bi bi-person-plus"></i> 
                    <?= $isFirstUser ? 'Initial Setup' : 'Create Account' ?>
                </h2>
                <?php if ($isFirstUser): ?>
                    <p class="text-muted mt-2 mb-0">Create your admin account to get started</p>
                <?php endif; ?>
            </div>
            
            <div class="card-body p-4">
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

                <form action="<?= base_url('register') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="form-floating mb-3">
                        <input type="text" 
                               class="form-control bg-dark text-light border-gold" 
                               id="name" 
                               name="name" 
                               placeholder="Full Name"
                               value="<?= old('name') ?>"
                               required>
                        <label for="name"><i class="bi bi-person"></i> Full Name</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" 
                               class="form-control bg-dark text-light border-gold" 
                               id="email" 
                               name="email" 
                               placeholder="name@example.com"
                               value="<?= old('email') ?>"
                               required>
                        <label for="email"><i class="bi bi-envelope"></i> Email address</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" 
                               class="form-control bg-dark text-light border-gold" 
                               id="password" 
                               name="password" 
                               placeholder="Password"
                               required>
                        <label for="password"><i class="bi bi-key"></i> Password</label>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" 
                               class="form-control bg-dark text-light border-gold" 
                               id="password_confirm" 
                               name="password_confirm" 
                               placeholder="Confirm Password"
                               required>
                        <label for="password_confirm"><i class="bi bi-key-fill"></i> Confirm Password</label>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-gold btn-lg">
                            <i class="bi bi-check-circle"></i> Create Account
                        </button>
                    </div>

                    <div class="text-center">
                        <?php if (!$isFirstUser): ?>
                            <a href="<?= base_url('login') ?>" class="text-gold text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Back to Login
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('/') ?>" class="text-gold text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Back to Website
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>