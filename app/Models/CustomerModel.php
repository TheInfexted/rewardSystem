<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'username', 
        'password', 
        'email', 
        'name', 
        'profile_background',
        'profile_background_image',
        'dashboard_bg_color',
        'spin_tokens',
        'spin_count', 
        'last_spin_date', 
        'points', 
        'monthly_checkins', 
        'is_active',
        'last_login'
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[customers.username,id,{id}]',
        'password' => 'required|min_length[4]',
        'email' => 'permit_empty|valid_email|is_unique[customers.email,id,{id}]',
        'phone' => 'permit_empty|min_length[10]|max_length[20]',
        'name' => 'permit_empty|max_length[100]'
    ];

    protected $validationMessages = [
        'username' => [
            'required' => 'Username is required',
            'min_length' => 'Username must be at least 3 characters',
            'max_length' => 'Username cannot exceed 50 characters',
            'is_unique' => 'Username already exists'
        ],
        'password' => [
            'required' => 'Password is required',
            'min_length' => 'Password must be at least 4 characters'
        ],
        'email' => [
            'valid_email' => 'Please enter a valid email address',
            'is_unique' => 'Email already exists'
        ],
        'phone' => [
            'min_length' => 'Phone number must be at least 10 digits',
            'max_length' => 'Phone number cannot exceed 20 digits'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks - FIXED: Only hash on insert, not update
    protected $allowCallbacks = true;
    protected $beforeInsert = ['hashPassword'];
    // REMOVED: protected $beforeUpdate = ['hashPassword']; // This was causing double hashing!

    /**
     * Hash password before insert ONLY
     * For updates, handle hashing manually in the controller
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            // Only hash if it doesn't look like it's already hashed
            $password = $data['data']['password'];
            
            // Check if it's already a bcrypt hash (starts with $2y$ and is ~60 chars)
            if (!preg_match('/^\$2y\$/', $password) || strlen($password) < 50) {
                $data['data']['password'] = password_hash($password, PASSWORD_DEFAULT);
                log_message('info', 'Password hashed by model callback for insert');
            } else {
                log_message('info', 'Password already hashed, skipping model callback');
            }
        }
        return $data;
    }

    /**
     * Update password without automatic hashing (for manual control)
     * This method ensures passwords are updated safely without double-hashing
     */
    public function updatePasswordRaw(int $customerId, string $hashedPassword): bool
    {
        try {
            // Skip validation and callbacks for this specific update
            $this->skipValidation(true);
            $this->allowCallbacks(false);
            
            $result = $this->update($customerId, [
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Reset validation and callbacks
            $this->skipValidation(false);
            $this->allowCallbacks(true);
            
            if ($result) {
                log_message('info', "Password updated successfully for customer ID: {$customerId}");
            } else {
                log_message('error', "Failed to update password for customer ID: {$customerId}");
            }
            
            return $result;
            
        } catch (\Exception $e) {
            // Reset validation and callbacks in case of error
            $this->skipValidation(false);
            $this->allowCallbacks(true);
            
            log_message('error', 'Error updating password: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Change customer password with security logging
     */
    public function changePassword(int $customerId, string $newPassword, int $adminId = null, string $reason = ''): bool
    {
        try {
            // Get current customer data for logging
            $customer = $this->find($customerId);
            if (!$customer) {
                log_message('error', "Customer not found for password change: {$customerId}");
                return false;
            }
            
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password using raw method
            $result = $this->updatePasswordRaw($customerId, $hashedPassword);
            
            if ($result) {
                // Log the password change
                $this->logPasswordChange($customerId, $adminId, $reason);
                
                log_message('info', "Password changed successfully for customer: {$customer['username']} (ID: {$customerId})");
            }
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', 'Error in changePassword: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate and set a random password for customer
     */
    public function generateRandomPassword(int $customerId, int $adminId = null, string $reason = '', int $length = 8): array|false
    {
        try {
            // Generate random password
            $newPassword = $this->createRandomPassword($length);
            
            // Change the password
            $result = $this->changePassword($customerId, $newPassword, $adminId, $reason ?: 'Random password generated');
            
            if ($result) {
                return [
                    'success' => true,
                    'password' => $newPassword
                ];
            }
            
            return false;
            
        } catch (\Exception $e) {
            log_message('error', 'Error in generateRandomPassword: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log password changes for security audit
     */
    private function logPasswordChange(int $customerId, int $adminId = null, string $reason = '')
    {
        $db = \Config\Database::connect();
        
        try {
            // Log to security_log table
            $securityData = [
                'customer_id' => $customerId,
                'event_type' => 'password_changed',
                'event_details' => json_encode([
                    'admin_id' => $adminId,
                    'reason' => $reason,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            $db->table('security_log')->insert($securityData);
            
            // Also log to customer_profile_changes if the table exists
            $profileChangeData = [
                'customer_id' => $customerId,
                'admin_id' => $adminId,
                'field_changed' => 'password',
                'old_value' => '[HIDDEN]',
                'new_value' => '[HIDDEN]',
                'change_reason' => $reason ?: 'Password changed',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            $db->table('customer_profile_changes')->insert($profileChangeData);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log password change: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique username (phone number format)
     */
    public function generateUniqueUsername(): string
    {
        $maxAttempts = 100;
        $attempt = 0;
        
        do {
            // Generate Malaysian phone number format: 01xxxxxxxx
            $username = '01' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            
            // Check if username exists
            $exists = $this->where('username', $username)->first();
            $attempt++;
            
            if ($attempt >= $maxAttempts) {
                throw new \Exception('Unable to generate unique username after maximum attempts');
            }
            
        } while ($exists);
        
        return $username;
    }

    /**
     * Create auto customer account
     */
    public function createAutoCustomer(): array|false
    {
        try {
            // Generate unique username and password
            $username = $this->generateUniqueUsername();
            $password = $this->createRandomPassword();
            
            // Prepare customer data
            $customerData = [
                'username' => $username,
                'password' => $password, // Will be hashed by beforeInsert callback
                'name' => $username,
                'email' => null,
                'phone' => $username,
                'is_active' => 1,
                'points' => 0
            ];
            
            // Insert customer
            $customerId = $this->insert($customerData);
            
            if ($customerId) {
                log_message('info', 'Auto customer created successfully: ' . $username);
                
                return [
                    'id' => $customerId,
                    'username' => $username,
                    'password' => $password, // Return plain password for display
                    'name' => $username,
                    'phone' => $username
                ];
            }
            
            return false;
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to create auto customer: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate random password with customizable complexity
     */
    private function createRandomPassword(int $length = 8): string
    {
        // Character sets for password generation
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*';
        
        // Ensure password has at least one character from each set for strength
        $password = '';
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        
        // Fill the rest with random characters from all sets
        $allChars = $lowercase . $uppercase . $numbers;
        for ($i = 3; $i < $length; $i++) {
            $password .= $allChars[rand(0, strlen($allChars) - 1)];
        }
        
        // Shuffle the password to randomize character positions
        return str_shuffle($password);
    }

    /**
     * Authenticate customer with enhanced security
     */
    public function authenticate(string $username, string $password): array|false
    {
        try {
            $customer = $this->where('username', $username)
                             ->where('is_active', 1)
                             ->first();
            
            if ($customer && password_verify($password, $customer['password'])) {
                // Update last login
                $this->update($customer['id'], ['last_login' => date('Y-m-d H:i:s')]);
                
                // Log successful login
                $this->logSecurityEvent($customer['id'], 'login_success', [
                    'username' => $username,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                // Remove password from returned data
                unset($customer['password']);
                
                return $customer;
            } else {
                // Log failed login attempt
                if ($customer) {
                    $this->logSecurityEvent($customer['id'], 'login_failed', [
                        'username' => $username,
                        'reason' => 'invalid_password',
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]);
                } else {
                    // Log attempt for non-existent user
                    log_message('warning', "Login attempt for non-existent user: {$username}");
                }
            }
            
            return false;
            
        } catch (\Exception $e) {
            log_message('error', 'Authentication error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log security events
     */
    private function logSecurityEvent(int $customerId, string $eventType, array $details)
    {
        $db = \Config\Database::connect();
        
        try {
            $securityData = [
                'customer_id' => $customerId,
                'event_type' => $eventType,
                'event_details' => json_encode($details),
                'ip_address' => $details['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            $db->table('security_log')->insert($securityData);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log security event: ' . $e->getMessage());
        }
    }

    /**
     * Get customer by ID with safe data
     */
    public function getCustomerSafeData(int $customerId): array|null
    {
        $customer = $this->find($customerId);
        
        if ($customer) {
            // Remove sensitive data
            unset($customer['password']);
            return $customer;
        }
        
        return null;
    }

    /**
     * Update customer points
     */
    public function updatePoints(int $customerId, int $points): bool
    {
        return $this->update($customerId, ['points' => $points]);
    }

    /**
     * Add points to customer
     */
    public function addPoints(int $customerId, int $pointsToAdd): bool
    {
        $customer = $this->find($customerId);
        
        if ($customer) {
            $newPoints = ($customer['points'] ?? 0) + $pointsToAdd;
            return $this->update($customerId, ['points' => $newPoints]);
        }
        
        return false;
    }

    /**
     * Deactivate customer
     */
    public function deactivateCustomer(int $customerId): bool
    {
        return $this->update($customerId, ['is_active' => 0]);
    }

    /**
     * Activate customer
     */
    public function activateCustomer(int $customerId): bool
    {
        return $this->update($customerId, ['is_active' => 1]);
    }

    /**
     * Get active customers count
     */
    public function getActiveCustomersCount(): int
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Get customers with pagination
     */
    public function getCustomersPaginated(int $limit = 20, int $offset = 0): array
    {
        return $this->select('id, username, name, email, phone, points, is_active, last_login, created_at')
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit, $offset);
    }

    /**
     * Search customers
     */
    public function searchCustomers(string $term, int $limit = 20): array
    {
        return $this->select('id, username, name, email, phone, points, is_active, last_login, created_at')
                    ->groupStart()
                        ->like('username', $term)
                        ->orLike('name', $term)
                        ->orLike('email', $term)
                        ->orLike('phone', $term)
                    ->groupEnd()
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit);
    }

    /**
     * Get password security statistics
     */
    public function getPasswordSecurityStats(int $customerId): array
    {
        $db = \Config\Database::connect();
        
        try {
            // Get password change history
            $query = $db->query("
                SELECT COUNT(*) as password_changes,
                       MAX(created_at) as last_password_change
                FROM security_log 
                WHERE customer_id = ? 
                AND event_type = 'password_changed'
            ", [$customerId]);
            
            $stats = $query->getRowArray();
            
            // Get recent security events
            $recentQuery = $db->query("
                SELECT event_type, created_at, event_details
                FROM security_log 
                WHERE customer_id = ? 
                ORDER BY created_at DESC 
                LIMIT 10
            ", [$customerId]);
            
            $recentEvents = $recentQuery->getResultArray();
            
            return [
                'password_changes' => $stats['password_changes'] ?? 0,
                'last_password_change' => $stats['last_password_change'],
                'recent_events' => $recentEvents
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting password security stats: ' . $e->getMessage());
            return [
                'password_changes' => 0,
                'last_password_change' => null,
                'recent_events' => []
            ];
        }
    }
    
    /**
     * Add spin tokens to customer
     */
    public function addSpinTokens(int $customerId, int $tokens, int $adminId = null, string $reason = null): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Get current balance
            $customer = $this->find($customerId);
            if (!$customer) {
                throw new \Exception('Customer not found');
            }
            
            $currentBalance = $customer['spin_tokens'] ?? 0;
            $newBalance = $currentBalance + $tokens;
            
            // Update customer tokens
            $this->update($customerId, ['spin_tokens' => $newBalance]);
            
            // Record in history
            $historyData = [
                'customer_id' => $customerId,
                'admin_id' => $adminId,
                'action' => 'add',
                'amount' => $tokens,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'reason' => $reason
            ];
            
            $db->table('spin_tokens_history')->insert($historyData);
            
            $db->transComplete();
            
            return $db->transStatus();
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Failed to add spin tokens: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove spin tokens from customer
     */
    public function removeSpinTokens(int $customerId, int $tokens, int $adminId = null, string $reason = null): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Get current balance
            $customer = $this->find($customerId);
            if (!$customer) {
                throw new \Exception('Customer not found');
            }
            
            $currentBalance = $customer['spin_tokens'] ?? 0;
            $newBalance = max(0, $currentBalance - $tokens); // Don't go below 0
            
            // Update customer tokens
            $this->update($customerId, ['spin_tokens' => $newBalance]);
            
            // Record in history
            $historyData = [
                'customer_id' => $customerId,
                'admin_id' => $adminId,
                'action' => 'remove',
                'amount' => $tokens,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'reason' => $reason
            ];
            
            $db->table('spin_tokens_history')->insert($historyData);
            
            $db->transComplete();
            
            return $db->transStatus();
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Failed to remove spin tokens: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Use a spin token
     */
    public function useSpinToken(int $customerId): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Get current balance
            $customer = $this->find($customerId);
            if (!$customer) {
                throw new \Exception('Customer not found');
            }
            
            $currentBalance = $customer['spin_tokens'] ?? 0;
            
            if ($currentBalance <= 0) {
                throw new \Exception('No spin tokens available');
            }
            
            $newBalance = $currentBalance - 1;
            
            // Update customer tokens
            $this->update($customerId, ['spin_tokens' => $newBalance]);
            
            // Record in history
            $historyData = [
                'customer_id' => $customerId,
                'admin_id' => null,
                'action' => 'used',
                'amount' => 1,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'reason' => 'Fortune wheel spin'
            ];
            
            $db->table('spin_tokens_history')->insert($historyData);
            
            $db->transComplete();
            
            return $db->transStatus();
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Failed to use spin token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get spin tokens history
     */
    public function getTokensHistory(int $customerId, int $limit = 20): array
    {
        $db = \Config\Database::connect();
        
        return $db->table('spin_tokens_history')
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}