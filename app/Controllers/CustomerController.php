<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\SpinHistoryModel;
use App\Models\BonusClaimModel;
use App\Models\AdminSettingsModel;

class CustomerController extends BaseController
{
    protected $customerModel;
    protected $spinHistoryModel;
    protected $bonusClaimModel;
    protected $rewardSystemAdModel;
    protected $adminSettingsModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->spinHistoryModel = new SpinHistoryModel();
        $this->bonusClaimModel = new BonusClaimModel();
        $this->rewardSystemAdModel = new \App\Models\RewardSystemAdModel();
        $this->adminSettingsModel = new AdminSettingsModel();
    }

    /**
     * Update winner storage with user ID after login
     */
    private function updateWinnerStorageWithUserId($storageId, $userId)
    {
        try {
            $db = \Config\Database::connect();
            $db->table('winner_storage')
               ->where('id', $storageId)
               ->update(['user_id' => $userId]);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Auto-claim reward for user after login
     */
    private function autoClaimRewardForUser($userId)
    {
        try {
            $db = \Config\Database::connect();
            
            // Get the most recent unclaimed winner data for this user
            $winnerRecord = $db->table('winner_storage')
                             ->where('user_id', $userId)
                             ->where('claimed', 0)
                             ->where('expires_at >', date('Y-m-d H:i:s'))
                             ->orderBy('created_at', 'DESC')
                             ->get()
                             ->getRowArray();
            
            if (!$winnerRecord) {
                return ['success' => false, 'error' => 'No unclaimed rewards found'];
            }
            
            $winnerData = json_decode($winnerRecord['winner_data'], true);
            if (!$winnerData || !isset($winnerData['name'])) {
                return ['success' => false, 'error' => 'Invalid winner data'];
            }
            
            // Get customer data
            $customer = $this->customerModel->find($userId);
            if (!$customer) {
                return ['success' => false, 'error' => 'Customer not found'];
            }
            
            // Create bonus claim record (default to Telegram)
            $claimData = [
                'session_id' => session_id(),
                'user_ip' => $this->request->getIPAddress(),
                'user_name' => $customer['username'],
                'customer_id' => $customer['id'],
                'phone_number' => $customer['phone'] ?? $customer['username'],
                'email' => null,
                'bonus_type' => $winnerData['name'],
                'bonus_amount' => isset($winnerData['prize']) ? floatval($winnerData['prize']) : 0.00,
                'claim_time' => date('Y-m-d H:i:s'),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'status' => 'pending',
                'platform_selected' => 'telegram', // Default to Telegram
                'account_type' => 'manual'
            ];
            
            $claimId = $this->bonusClaimModel->insert($claimData);
            
            if ($claimId) {
                // Mark winner storage as claimed
                $db->table('winner_storage')
                   ->where('id', $winnerRecord['id'])
                   ->update([
                       'claimed' => 1,
                       'claim_id' => $claimId,
                       'claimed_at' => date('Y-m-d H:i:s')
                   ]);
                
                // Clear winner data from session
                session()->remove(['winner_data', 'winner_storage_id']);
                
                return [
                    'success' => true,
                    'reward_data' => [
                        'bonus_type' => $winnerData['name'],
                        'claim_id' => $claimId,
                        'platform' => 'telegram'
                    ]
                ];
            } else {
                return ['success' => false, 'error' => 'Failed to create bonus claim'];
            }
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Auto-claim failed: ' . $e->getMessage()];
        }
    }

    /**
     * Display customer login page
     */
    public function login()
    {
        $session = session();
        
        // Check if customer is already logged in
        $customerId = $session->get('customer_id');
        $customerLoggedIn = $session->get('customer_logged_in');
        
        if ($customerId && $customerLoggedIn) {
            $customerData = $this->customerModel->find($customerId);
            if ($customerData) {
                return redirect()->to('/customer/dashboard');
            } else {
                // Customer not found, clear session
                $session->remove(['customer_id', 'customer_logged_in', 'customer_data']);
            }
        }

        $data = [
            'title' => 'Customer Login',
            'redirect_url' => $this->request->getGet('redirect') ?: null
        ];

        return view('customer/login', $data);
    }

    /**
     * Handle customer login attempt
     */
    public function attemptLogin()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/customer');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required|min_length[3]|max_length[50]',
            'password' => 'required|min_length[4]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please fill in all required fields correctly.',
                'errors' => $validation->getErrors()
            ]);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        
        
        try {
            // Find customer by username
            $customer = $this->customerModel->where('username', $username)->first();
            
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid username or password.'
                ]);
            }
            
            // Verify password
            $passwordValid = password_verify($password, $customer['password']);
            
            if ($passwordValid) {
                // Update last login
                $this->customerModel->update($customer['id'], [
                    'last_login' => date('Y-m-d H:i:s')
                ]);
                
                // Set session data
                session()->set([
                    'customer_id' => $customer['id'],
                    'customer_logged_in' => true,
                    'customer_data' => [
                        'id' => $customer['id'],
                        'username' => $customer['username'],
                        'name' => $customer['name'] ?? $customer['username'],
                        'phone' => $customer['phone'] ?? $customer['username'],
                        'points' => $customer['points'] ?? 0,
                    ]
                ]);

                // Update winner storage with customer ID if winner_storage_id exists
                $winnerStorageId = session()->get('winner_storage_id');
                if ($winnerStorageId) {
                    $this->updateWinnerStorageWithUserId($winnerStorageId, $customer['id']);
                }

                // Auto-claim reward if user has unclaimed winner data
                $autoClaimResult = $this->autoClaimRewardForUser($customer['id']);
                
                // Get redirect URL from request or default to dashboard
                $redirectUrl = $this->request->getPost('redirect_url') ?: base_url('customer/dashboard');
                
                $responseData = [
                    'success' => true,
                    'redirect' => $redirectUrl,
                ];
                
                if ($autoClaimResult['success']) {
                    $responseData['message'] = 'Reward Claim Successfully!';
                    $responseData['reward_claimed'] = true;
                    $responseData['reward_data'] = $autoClaimResult['reward_data'];
                } else {
                    $responseData['message'] = 'Login successful!';
                    if ($autoClaimResult['error']) {
                        $responseData['warning'] = $autoClaimResult['error'];
                    }
                }
                
                return $this->response->setJSON($responseData);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid username or password.'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ]);
        }
    }

    public function dashboard()
    {
        $customerId = session()->get('customer_id');
        if (!$customerId) {
            return redirect()->to('/customer')->with('error', 'Please log in to access dashboard');
        }

        try {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return redirect()->to('/customer')->with('error', 'Customer not found');
            }

            // Get week data with dynamic calculations
            $weekData = $this->getCurrentWeekCheckins($customerId);
            
            // Get monthly checkins
            $monthlyCheckins = $this->getMonthlyCheckins($customerId);
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities($customerId);
            
            // Get admin contact settings
            try {
                $db = \Config\Database::connect();
                $contactSettings = $db->query("
                    SELECT setting_key, setting_value 
                    FROM admin_settings 
                    WHERE setting_key IN ('reward_whatsapp_number', 'reward_telegram_username')
                ")->getResultArray();
                
                $whatsappNumber = null;
                $telegramUsername = null;
                
                foreach ($contactSettings as $setting) {
                    if ($setting['setting_key'] === 'reward_whatsapp_number') {
                        $whatsappNumber = $setting['setting_value'];
                    } elseif ($setting['setting_key'] === 'reward_telegram_username') {
                        $telegramUsername = $setting['setting_value'];
                    }
                }
                
            } catch (\Exception $e) {
                log_message('error', 'Failed to get contact settings: ' . $e->getMessage());
                // No fallback values - configuration must be properly set
                $whatsappNumber = null;
                $telegramUsername = null;
            }
            
            $data = [
                'title' => 'Customer Dashboard',
                'customer' => $customer,
                'username' => $customer['username'],
                'profile_background' => $customer['profile_background'] ?? 'default',
                'profile_background_image' => $customer['profile_background_image'] ?? null,
                'dashboard_bg_color' => $customer['dashboard_bg_color'] ?? '#ffffff',
                'spin_tokens' => $customer['spin_tokens'] ?? 0,
                'today_checkin' => $weekData['today_checkin'],
                'week_checkins' => $weekData['week_checkins'],
                'checkin_streak' => $weekData['checkin_count'],
                'weekly_progress' => $weekData['weekly_progress'],
                'current_week_start' => $weekData['week_start'],
                'current_week_end' => $weekData['week_end'],
                'total_points' => $customer['points'] ?? 0,
                'recent_activities' => $recentActivities,
                'monthly_checkins' => $monthlyCheckins,
                'whatsapp_number' => $whatsappNumber,
                'telegram_username' => $telegramUsername,
                'ads' => $this->rewardSystemAdModel->getActiveAds(),
            ];
            
            return view('customer/dashboard', $data);
        
        } catch (\Exception $e) {
            log_message('error', 'Customer dashboard error: ' . $e->getMessage());
            return redirect()->to('/customer')->with('error', 'Unable to load dashboard');
        }
    }

    /**
     * Get current password (masked for security)
     * Returns the actual password only for display toggle functionality
     */
    public function getCurrentPassword()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid request']);
        }
        
        $customerId = session()->get('customer_id');
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please log in to continue'
            ]);
        }
        
        try {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Password is securely encrypted',
                'password_secure' => true
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Get current password error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred'
            ]);
        }
    }
    
    /**
     * Change customer password
     */
    public function changePassword()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid request']);
        }
        
        $customerId = session()->get('customer_id');
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please log in to continue'
            ]);
        }
        
        // Get JSON input
        $json = $this->request->getJSON(true);
        $currentPassword = $json['current_password'] ?? '';
        $newPassword = $json['new_password'] ?? '';
        $confirmPassword = $json['confirm_password'] ?? '';
        
        // Validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ], [
            'current_password' => [
                'required' => 'Current password is required'
            ],
            'new_password' => [
                'required' => 'New password is required',
                'min_length' => 'New password must be at least 6 characters'
            ],
            'confirm_password' => [
                'required' => 'Please confirm your new password',
                'matches' => 'Password confirmation does not match'
            ]
        ]);
        
        if (!$validation->run([
            'current_password' => $currentPassword,
            'new_password' => $newPassword,
            'confirm_password' => $confirmPassword
        ])) {
            $errors = $validation->getErrors();
            $firstError = array_values($errors)[0];
            $firstField = array_keys($errors)[0];
            
            return $this->response->setJSON([
                'success' => false,
                'message' => $firstError,
                'field' => $firstField
            ]);
        }
        
        try {
            // Get customer data
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $customer['password'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                    'field' => 'current_password'
                ]);
            }
            
            // Check if new password is different from current
            if (password_verify($newPassword, $customer['password'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'New password must be different from current password',
                    'field' => 'new_password'
                ]);
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
            
            // Update password using the new method that skips the model callback
            $result = $this->customerModel->updatePasswordRaw($customerId, $hashedPassword);
            
            if ($result) {
                // Verify the update worked
                $updatedCustomer = $this->customerModel->find($customerId);
                $verificationPassed = password_verify($newPassword, $updatedCustomer['password']);
                
                if ($verificationPassed) {
                    // Log password change for security audit
                    $this->logPasswordChange($customerId);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Password updated successfully!'
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Password verification failed after update.'
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update password. Please try again.'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Password change error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ]);
        }
    }

    
    /**
     * Log password change for security audit
     */
    private function logPasswordChange($customerId)
    {
        try {
            $db = \Config\Database::connect();
            
            // Create password_changes table if it doesn't exist
            $forge = \Config\Database::forge();
            if (!$db->tableExists('password_changes')) {
                $fields = [
                    'id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'auto_increment' => true
                    ],
                    'customer_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true
                    ],
                    'ip_address' => [
                        'type' => 'VARCHAR',
                        'constraint' => 45
                    ],
                    'user_agent' => [
                        'type' => 'TEXT'
                    ],
                    'changed_at' => [
                        'type' => 'TIMESTAMP',
                        'default' => 'CURRENT_TIMESTAMP'
                    ]
                ];
                
                $forge->addField($fields);
                $forge->addKey('id', true);
                $forge->addKey('customer_id');
                $forge->createTable('password_changes');
            }
            
            // Insert log entry
            $db->table('password_changes')->insert([
                'customer_id' => $customerId,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'changed_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log password change: ' . $e->getMessage());
        }
    }

    public function updateDashboardColor()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $customerId = session()->get('customer_id');
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not authenticated'
            ]);
        }

        $color = $this->request->getPost('color');
        
        // Validate color format (hex color)
        if (!$this->isValidHexColor($color)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid color format. Please use hex color format (#RRGGBB)'
            ]);
        }

        try {
            $customerModel = new CustomerModel();
            $result = $customerModel->update($customerId, [
                'dashboard_bg_color' => $color,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                // Log the color change for audit purposes
                log_message('info', "Customer {$customerId} updated dashboard color to {$color}");
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Dashboard background color updated successfully',
                    'color' => $color
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update dashboard color in database'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Dashboard color update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while updating the color'
            ]);
        }
    }

    /**
     * Reset dashboard to default theme
     */
    public function resetDashboardTheme()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $customerId = session()->get('customer_id');
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not authenticated'
            ]);
        }

        try {
            $customerModel = new CustomerModel();
            $result = $customerModel->update($customerId, [
                'dashboard_bg_color' => '#ffffff',
                'profile_background_image' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                log_message('info', "Customer {$customerId} reset dashboard theme to default");
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Dashboard theme reset to default successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to reset dashboard theme'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Dashboard theme reset error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while resetting the theme'
            ]);
        }
    }

    /**
     * Get current dashboard theme settings
     */
    public function getDashboardTheme()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $customerId = session()->get('customer_id');
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not authenticated'
            ]);
        }

        try {
            $customerModel = new CustomerModel();
            $customer = $customerModel->find($customerId);
            
            if ($customer) {
                return $this->response->setJSON([
                    'success' => true,
                    'theme' => [
                        'dashboard_bg_color' => $customer['dashboard_bg_color'] ?? '#ffffff',
                        'profile_background_image' => $customer['profile_background_image'] ?? null,
                        'theme_updated_at' => $customer['updated_at'] ?? null
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Get dashboard theme error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while retrieving theme settings'
            ]);
        }
    }

    /**
     * Validate hex color format
     * 
     * @param string $color
     * @return bool
     */
    private function isValidHexColor($color)
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
    }

    /**
     * Get predefined theme colors
     */
    public function getThemeColors()
    {
        $themeColors = [
            'default' => '#ffffff',
            'blue' => '#667eea',
            'purple' => '#764ba2',
            'green' => '#11998e',
            'orange' => '#ff9a56',
            'pink' => '#f093fb',
            'dark' => '#2c3e50',
            'teal' => '#38ef7d',
            'red' => '#ff6b6b',
            'gold' => '#ffd700'
        ];

        return $this->response->setJSON([
            'success' => true,
            'colors' => $themeColors
        ]);
    }

    /**
     * Handle weekly check-in system
     */
    public function checkin()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/customer/dashboard');
        }

        $session = session();
        $customerId = $session->get('customer_id');
        
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first'
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('customer_checkins');
            $today = date('Y-m-d');
            
            // Check if already checked in today
            $todayCheckin = $builder->where('customer_id', $customerId)
                                ->where('checkin_date', $today)
                                ->countAllResults();
            
            if ($todayCheckin > 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You have already checked in today!'
                ]);
            }
            
            // Calculate TRUE consecutive streak
            $consecutiveStreak = $this->calculateConsecutiveStreak($customerId);
            
            // Calculate day of week (1=Monday, 7=Sunday)
            $dayOfWeek = date('N');
            
            // Calculate reward points based on TRUE streak
            $rewardPoints = $this->calculateCheckinReward($consecutiveStreak + 1, $dayOfWeek, $customerId);
            
            // Insert check-in record
            $inserted = $builder->insert([
                'customer_id' => $customerId,
                'checkin_date' => $today,
                'reward_points' => $rewardPoints,
                'streak_day' => $consecutiveStreak + 1, // TRUE consecutive streak
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($inserted) {
                // Add points to customer
                $pointsAdded = $this->addPointsToCustomer($customerId, $rewardPoints);
                
                if ($pointsAdded) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Check-in successful!',
                        'points' => $rewardPoints,
                        'day' => $consecutiveStreak + 1
                    ]);
                } else {
                    log_message('error', 'Failed to add points after check-in');
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Check-in recorded but failed to add points',
                        'points' => $rewardPoints,
                        'day' => $consecutiveStreak + 1
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to process check-in'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Check-in error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Check-in failed. Please try again.'
            ]);
        }
    }

    /**
     * Calculate TRUE consecutive streak - counts back from today until gap found
     */
    private function calculateConsecutiveStreak($customerId)
    {
        $db = \Config\Database::connect();
        $streak = 0;
        $currentDate = date('Y-m-d');
        
        // Start from yesterday (since today hasn't been checked in yet)
        $checkDate = date('Y-m-d', strtotime('-1 day'));
        
        while (true) {
            // Check if customer checked in on this date
            $checkin = $db->table('customer_checkins')
                        ->where('customer_id', $customerId)
                        ->where('checkin_date', $checkDate)
                        ->countAllResults();
            
            if ($checkin > 0) {
                $streak++;
                // Go back one more day
                $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
            } else {
                // Gap found, streak ends here
                break;
            }
            
            // Safety limit to prevent infinite loop
            if ($streak >= 365) {
                break;
            }
        }
        
        return $streak;
    }

    private function getCheckinSettings($customerId = null)
    {
        $db = \Config\Database::connect();
        
        // If customer ID provided, try to get customer-specific settings
        if ($customerId) {
            $customerSettings = $db->query("
                SELECT * FROM customer_checkin_settings 
                WHERE customer_id = ?
            ", [$customerId])->getRowArray();
            
            if ($customerSettings) {
                // Return customer-specific settings
                return [
                    'default_checkin_points' => $customerSettings['default_checkin_points'],
                    'weekly_bonus_multiplier' => $customerSettings['weekly_bonus_multiplier'],
                    'max_streak_days' => $customerSettings['max_streak_days'],
                    'weekend_bonus_points' => $customerSettings['weekend_bonus_points'],
                    'consecutive_bonus_points' => $customerSettings['consecutive_bonus_points'] ?? 5
                ];
            }
        }
        
        // Fallback to global admin settings
        $query = $db->query("
            SELECT setting_key, setting_value 
            FROM admin_settings 
            WHERE setting_key IN (
                'default_checkin_points', 
                'weekly_bonus_multiplier', 
                'max_streak_days', 
                'weekend_bonus_points'
            )
        ");
        
        $settings = [];
        foreach ($query->getResultArray() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Set defaults if not found
        $defaults = [
            'default_checkin_points' => 10,
            'weekly_bonus_multiplier' => 1.5,
            'max_streak_days' => 7,
            'weekend_bonus_points' => 5,
            'consecutive_bonus_points' => 5
        ];
        
        return array_merge($defaults, $settings);
    }

    /**
     * Update customer background image
     */
    public function updateBackground()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/customer/dashboard');
        }

        $session = session();
        $customerId = $session->get('customer_id');
        
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first'
            ]);
        }

        try {
            $backgroundFile = $this->request->getFile('background_image');
            
            if ($backgroundFile && $backgroundFile->isValid() && !$backgroundFile->hasMoved()) {
                // Validate file type
                $validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($backgroundFile->getMimeType(), $validTypes)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid file type. Please upload a valid image (JPG, PNG, GIF, or WebP)'
                    ]);
                }
                
                // Validate file size (max 5MB)
                if ($backgroundFile->getSize() > 5 * 1024 * 1024) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'File size too large. Maximum allowed size is 5MB'
                    ]);
                }
                
                // Create upload directory if it doesn't exist
                $uploadPath = FCPATH . 'uploads/profile_backgrounds/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Generate unique filename
                $newName = $customerId . '_' . time() . '_' . $backgroundFile->getRandomName();
                
                // Move the file
                if ($backgroundFile->move($uploadPath, $newName)) {
                    // Get current customer data to delete old image
                    $customer = $this->customerModel->find($customerId);
                    
                    // Delete old background image if exists
                    if (!empty($customer['profile_background_image'])) {
                        $oldImagePath = FCPATH . 'uploads/profile_backgrounds/' . $customer['profile_background_image'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    // Update database
                    $updateData = ['profile_background_image' => $newName];
                    $updated = $this->customerModel->update($customerId, $updateData);
                    
                    if ($updated) {
                        return $this->response->setJSON([
                            'success' => true,
                            'message' => 'Background image updated successfully',
                            'image_url' => base_url('uploads/profile_backgrounds/' . $newName)
                        ]);
                    }
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to upload image'
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No valid image file uploaded'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Background update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Update failed. Please try again.'
            ]);
        }
    }

    /**
     * Remove background image
     */
    public function removeBackgroundImage()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/customer/dashboard');
        }

        $session = session();
        $customerId = $session->get('customer_id');
        
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first'
            ]);
        }

        try {
            $customer = $this->customerModel->find($customerId);
            
            // Delete image file if exists
            if (!empty($customer['profile_background_image'])) {
                $imagePath = FCPATH . 'uploads/profile_backgrounds/' . $customer['profile_background_image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Update database
            $updated = $this->customerModel->update($customerId, ['profile_background_image' => null]);
            
            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Background image removed successfully'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Background removal error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to remove background image'
            ]);
        }
    }

    /**
     * Get current theme for customer (for auto-sync)
     */
    public function getTheme()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/customer/dashboard');
        }

        $session = session();
        $customerId = $session->get('customer_id');
        
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first'
            ]);
        }

        try {
            $customer = $this->customerModel->find($customerId);
            
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'theme' => $customer['profile_background'] ?? 'default',
                'dashboard_bg_color' => $customer['dashboard_bg_color'] ?? '#ffffff'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get theme error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred'
            ]);
        }
    }

    /**
     * Get current week check-in data
     */
    private function getCurrentWeekCheckins($customerId)
    {
        try {
            $db = \Config\Database::connect();
            $weekDates = $this->getCurrentWeekDates();
            $today = date('Y-m-d');
            
            // Get week check-ins
            $weekCheckins = $db->table('customer_checkins')
                            ->where('customer_id', $customerId)
                            ->where('checkin_date >=', $weekDates['week_start'])
                            ->where('checkin_date <=', $weekDates['week_end'])
                            ->orderBy('checkin_date', 'ASC')
                            ->get()
                            ->getResultArray();
            
            // Check if checked in today
            $todayCheckin = false;
            foreach ($weekCheckins as $checkin) {
                if ($checkin['checkin_date'] === $today) {
                    $todayCheckin = true;
                    break;
                }
            }
            
            // Create a map of checked-in dates for quick lookup
            $checkinMap = [];
            foreach ($weekCheckins as $checkin) {
                $checkinMap[$checkin['checkin_date']] = $checkin;
            }
            
            // Find the first check-in date this week to determine streak start
            $firstCheckinDate = !empty($weekCheckins) ? $weekCheckins[0]['checkin_date'] : null;
            
            // Calculate TRUE consecutive streak instead of weekly count
            $currentStreak = $this->calculateConsecutiveStreak($customerId);
            if ($todayCheckin) {
                $currentStreak++; // Include today if already checked in
            }
            
            // Create weekly progress array (1=Monday to 7=Sunday)
            $weeklyProgress = [];
            for ($i = 1; $i <= 7; $i++) {
                $date = date('Y-m-d', strtotime($weekDates['week_start'] . ' +' . ($i-1) . ' days'));
                $dayOfWeek = date('N', strtotime($date)); // 1=Monday, 7=Sunday
                
                $dayData = [
                    'day' => date('j', strtotime($date)),
                    'date' => $date,
                    'day_name' => date('l', strtotime($date)),
                    'day_short' => date('D', strtotime($date)),
                    'checked_in' => isset($checkinMap[$date]),
                    'is_today' => $date === $today,
                    'is_future' => $date > $today,
                    'points' => 0,
                    'actual_points' => 0,
                    'status' => 'pending'
                ];
                
                if (isset($checkinMap[$date])) {
                    // Customer checked in on this day - show actual points
                    $dayData['actual_points'] = $checkinMap[$date]['reward_points'];
                    $dayData['points'] = $checkinMap[$date]['reward_points'];
                    $dayData['status'] = 'completed';
                    $dayData['streak_day'] = $checkinMap[$date]['streak_day'];
                    
                } elseif ($date > $today) {
                    // Future day - calculate predicted points if streak continues
                    $daysBetween = (strtotime($date) - strtotime($today)) / (24 * 60 * 60);
                    $predictedStreakDay = $currentStreak + $daysBetween;
                    $dayData['points'] = $this->calculateCheckinReward($predictedStreakDay, $dayOfWeek, $customerId);
                    $dayData['status'] = 'future';
                    
                } elseif ($date === $today && !$todayCheckin) {
                    // Today, but not checked in yet - show potential points
                    $potentialStreakDay = $currentStreak + 1;
                    $dayData['points'] = $this->calculateCheckinReward($potentialStreakDay, $dayOfWeek, $customerId);
                    $dayData['status'] = 'available';
                    
                } else {
                    // Past day, not checked in - missed day (breaks streak)
                    $dayData['points'] = 0;
                    $dayData['status'] = 'missed';
                }
                
                $weeklyProgress[$i] = $dayData;
            }
            
            return [
                'today_checkin' => $todayCheckin,
                'week_checkins' => $weekCheckins,
                'checkin_count' => count($weekCheckins),
                'weekly_progress' => $weeklyProgress,
                'week_start' => $weekDates['week_start'],
                'week_end' => $weekDates['week_end'],
                'current_streak' => $currentStreak, // TRUE consecutive streak
                'first_checkin_date' => $firstCheckinDate
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Get week checkins error: ' . $e->getMessage());
            return [
                'today_checkin' => false,
                'week_checkins' => [],
                'checkin_count' => 0,
                'weekly_progress' => [],
                'week_start' => date('Y-m-d'),
                'week_end' => date('Y-m-d'),
                'current_streak' => 0,
                'first_checkin_date' => null
            ];
        }
    }

    /**
     * Get current week dates (Monday to Sunday)
     */
    private function getCurrentWeekDates()
    {
        $today = new \DateTime();
        $dayOfWeek = $today->format('N'); // 1 (Monday) to 7 (Sunday)
        
        // Calculate Monday of current week
        $mondayOffset = $dayOfWeek - 1;
        $monday = clone $today;
        $monday->sub(new \DateInterval("P{$mondayOffset}D"));
        
        // Calculate Sunday of current week
        $sunday = clone $monday;
        $sunday->add(new \DateInterval('P6D'));
        
        return [
            'week_start' => $monday->format('Y-m-d'),
            'week_end' => $sunday->format('Y-m-d')
        ];
    }

    /**
     * Calculate weekly reward points
     */
    private function calculateCheckinReward($dayNumber, $dayOfWeek, $customerId = null)
    {
        try {
            // Get customer-specific settings if customer ID provided
            $settings = $this->getCheckinSettings($customerId);
            
            // Base points from customer-specific or global settings
            $basePoints = (int) $settings['default_checkin_points'];
            
            // Consecutive bonus - now configurable per customer
            $consecutiveBonusPerDay = (int) ($settings['consecutive_bonus_points'] ?? 5);
            $consecutiveBonus = ($dayNumber - 1) * $consecutiveBonusPerDay;
            
            // Weekend bonus from customer-specific or global settings
            $weekendBonus = ($dayOfWeek == 6 || $dayOfWeek == 7) ? 
                (int) $settings['weekend_bonus_points'] : 0;
            
            // Weekly completion bonus
            $weeklyMultiplier = ($dayNumber >= 7) ? (float) $settings['weekly_bonus_multiplier'] : 1;
            
            $totalPoints = ($basePoints + $consecutiveBonus + $weekendBonus) * $weeklyMultiplier;
            
            return (int) round($totalPoints);
            
        } catch (\Exception $e) {
            log_message('error', 'Calculate checkin reward error: ' . $e->getMessage());
            // Fallback to hardcoded values if settings fail
            return 10 + (($dayNumber - 1) * 5) + (($dayOfWeek == 6 || $dayOfWeek == 7) ? 10 : 0);
        }
    }

    /**
     * Add points to customer
     */
    private function addPointsToCustomer($customerId, $points)
    {
        try {
            return $this->customerModel->addPoints($customerId, $points);
        } catch (\Exception $e) {
            log_message('error', 'Add points error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent activities for customer
     */
    private function getRecentActivities($customerId)
    {
        try {
            $db = \Config\Database::connect();
            $activities = [];
            
            // Get recent check-ins (last 5)
            $checkins = $db->table('customer_checkins')
                          ->where('customer_id', $customerId)
                          ->orderBy('created_at', 'DESC')
                          ->limit(5)
                          ->get()
                          ->getResultArray();
            
            foreach ($checkins as $checkin) {
                $activities[] = [
                    'type' => 'checkin',
                    'type_color' => 'success',
                    'icon' => 'calendar-check',
                    'title' => 'Daily Check-in Completed',
                    'time_ago' => $this->timeAgo($checkin['created_at']),
                    'reward' => '+' . $checkin['reward_points'] . ' pts'
                ];
            }
            
            // Get recent bonus claims (last 5)
            $claims = $db->table('bonus_claims')
                        ->where('customer_id', $customerId)
                        ->orderBy('created_at', 'DESC')
                        ->limit(5)
                        ->get()
                        ->getResultArray();
            
            foreach ($claims as $claim) {
                $activities[] = [
                    'type' => 'claim',
                    'type_color' => 'info',
                    'icon' => 'gift',
                    'title' => 'Reward Claimed: ' . $claim['bonus_type'],
                    'time_ago' => $this->timeAgo($claim['created_at']),
                    'reward' => ucfirst($claim['status'])
                ];
            }
            
            // Sort by date
            usort($activities, function($a, $b) {
                return strcmp($b['time_ago'], $a['time_ago']);
            });
            
            return array_slice($activities, 0, 10);
            
        } catch (\Exception $e) {
            log_message('error', 'Get recent activities error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Convert timestamp to time ago format
     */
    private function timeAgo($datetime)
    {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . ' mins ago';
        if ($time < 86400) return floor($time/3600) . ' hours ago';
        if ($time < 2592000) return floor($time/86400) . ' days ago';
        if ($time < 31536000) return floor($time/2592000) . ' months ago';
        
        return floor($time/31536000) . ' years ago';
    }

    /**
     * Get monthly checkins count
     */
    private function getMonthlyCheckins($customerId)
    {
        try {
            $db = \Config\Database::connect();
            return $db->table('customer_checkins')
                     ->where('customer_id', $customerId)
                     ->where('MONTH(checkin_date)', date('n'))
                     ->where('YEAR(checkin_date)', date('Y'))
                     ->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'Get monthly checkins error: ' . $e->getMessage());
            return 0;
        }
    }

    public function getWheelData()
    {
        $session = session();
        $customerId = $session->get('customer_id');

        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ]);
        }

        try {
            $db = \Config\Database::connect();
            
            // Get customer spin tokens
            $customer = $this->customerModel->find($customerId);
            $spinTokens = $customer['spin_tokens'] ?? 0;

            // Fetch wheel items from the database
            $wheelItemsRaw = $db->table('wheel_items')
                ->where('is_active', 1)
                ->orderBy('order', 'ASC')
                ->get()
                ->getResultArray();

            // Transform data to match JS expectations
            $wheelItems = array_map(function ($item) {
                return [
                    'item_id' => (int) $item['item_id'],
                    'item_name' => $item['item_name'],
                    'item_prize' => (float) $item['item_prize'],
                    'item_types' => $item['item_types'],
                    'winning_rate' => (float) $item['winning_rate'],
                    'order' => (int) $item['order']
                ];
            }, $wheelItemsRaw);

            return $this->response->setJSON([
                'success' => true,
                'spins_remaining' => $spinTokens,  
                'wheel_items' => $wheelItems,
                'spin_sound' => ['enabled' => true],
                'win_sound' => ['enabled' => true]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'getWheelData error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load wheel data'
            ]);
        }
    }

    public function getAds()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/customer/dashboard');
        }
        
        $ads = $this->rewardSystemAdModel->getActiveAds();
        
        return $this->response->setJSON([
            'success' => true,
            'ads' => $ads
        ]);
    }
}