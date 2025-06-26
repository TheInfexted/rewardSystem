<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Claim Your Reward' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        .reward-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 500px;
            margin: 0 auto;
        }
        .reward-header {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            padding: 2rem;
            text-align: center;
        }
        .reward-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .prize-display {
            font-size: 2rem;
            color: #28a745;
            font-weight: bold;
            margin: 1rem 0;
        }
        .claim-form {
            padding: 2rem;
        }
        .btn-claim {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            transition: transform 0.3s;
            width: 100%;
        }
        .btn-claim:hover {
            transform: translateY(-2px);
        }
        .reward-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .contact-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-whatsapp {
            background: #25D366;
            color: white;
        }
        .btn-telegram {
            background: #0088cc;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if (!empty($success_message)): ?>
                    <!-- Success Message -->
                    <div class="reward-card">
                        <div class="reward-header">
                            <h1 class="reward-title">✅ Success!</h1>
                        </div>
                        <div class="claim-form text-center">
                            <div class="alert alert-success">
                                <h5><?= esc($success_message) ?></h5>
                            </div>
                            <div class="contact-buttons">
                                <a href="https://wa.me/<?= $whatsapp_number ?>?text=I%20just%20submitted%20a%20reward%20claim" 
                                   class="btn btn-whatsapp flex-fill">
                                    📱 Contact via WhatsApp
                                </a>
                                <a href="https://t.me/<?= $telegram_username ?>" 
                                   class="btn btn-telegram flex-fill">
                                    📱 Contact via Telegram
                                </a>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif (!empty($rewardData)): ?>
                    <!-- Successful Reward Display -->
                    <div class="reward-card">
                        <div class="reward-header">
                            <h1 class="reward-title">🎉 Congratulations! 🎉</h1>
                            <div class="prize-display"><?= esc($rewardData['prize_name']) ?></div>
                            <?php if ($rewardData['prize_value'] > 0): ?>
                                <div class="text-success fs-4">Value: ¥<?= number_format($rewardData['prize_value'], 2) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="claim-form">
                            <div class="reward-details">
                                <h5>🎁 Your Prize Details:</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Prize:</strong> <?= esc($rewardData['prize_name']) ?></li>
                                    <li><strong>Type:</strong> <?= ucfirst(esc($rewardData['prize_type'])) ?></li>
                                    <li><strong>Won at:</strong> <?= esc($rewardData['won_at']) ?></li>
                                    <li><strong>Reference:</strong> <?= esc($rewardData['session_id']) ?></li>
                                </ul>
                            </div>
                            
                            <!-- Claim Form -->
                            <form method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="claim_reward" value="1">
                                <input type="hidden" name="prize_name" value="<?= esc($rewardData['prize_name']) ?>">
                                <input type="hidden" name="prize_value" value="<?= esc($rewardData['prize_value']) ?>">
                                <input type="hidden" name="session_id" value="<?= esc($rewardData['session_id']) ?>">
                                
                                <div class="mb-3">
                                    <label for="user_name" class="form-label">Your Full Name *</label>
                                    <input type="text" class="form-control" id="user_name" name="user_name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                    <small class="text-muted">We'll contact you via WhatsApp/Telegram</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address (Optional)</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-claim btn-success">
                                        🎁 Claim My Reward Now!
                                    </button>
                                </div>
                            </form>
                            
                            <div class="contact-buttons">
                                <a href="https://wa.me/<?= $whatsapp_number ?>?text=I%20just%20won%20<?= urlencode($rewardData['prize_name']) ?>%20from%20the%20fortune%20wheel!" 
                                   class="btn btn-whatsapp flex-fill">
                                    📱 WhatsApp
                                </a>
                                <a href="https://t.me/<?= $telegram_username ?>" 
                                   class="btn btn-telegram flex-fill">
                                    📱 Telegram
                                </a>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif (!empty($error)): ?>
                    <!-- Error Display -->
                    <div class="reward-card">
                        <div class="reward-header bg-danger">
                            <h1 class="reward-title text-white">⚠️ Error</h1>
                        </div>
                        <div class="claim-form text-center">
                            <div class="alert alert-danger">
                                <h5>Unable to Load Reward Data</h5>
                                <p><?= esc($error) ?></p>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- No Data Received -->
                    <div class="reward-card">
                        <div class="reward-header bg-warning">
                            <h1 class="reward-title">🎯 Client Zone</h1>
                        </div>
                        <div class="claim-form text-center">
                            <div class="alert alert-info">
                                <h5>Welcome to the Reward Claim Center</h5>
                                <p>This page displays your reward details when you win a prize from our fortune wheel.</p>
                            </div>
                            <a href="<?= base_url('/') ?>" class="btn btn-primary btn-lg">
                                🎰 Go to Fortune Wheel
                            </a>
                            
                            <div class="contact-buttons">
                                <a href="https://wa.me/<?= $whatsapp_number ?>" class="btn btn-whatsapp flex-fill">
                                    📱 WhatsApp Support
                                </a>
                                <a href="https://t.me/<?= $telegram_username ?>" class="btn btn-telegram flex-fill">
                                    📱 Telegram Support
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameField = document.getElementById('user_name');
            if (nameField) {
                nameField.focus();
            }
        });
    </script>
</body>
</html>