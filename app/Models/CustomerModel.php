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

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hash password before insert/update
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
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
            $password = $this->generateRandomPassword();
            
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
     * Generate random password
     */
    private function generateRandomPassword(int $length = 8): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Authenticate customer
     */
    public function authenticate(string $username, string $password): array|false
    {
        $customer = $this->where('username', $username)
                         ->where('is_active', 1)
                         ->first();
        
        if ($customer && password_verify($password, $customer['password'])) {
            // Update last login
            $this->update($customer['id'], ['last_login' => date('Y-m-d H:i:s')]);
            
            // Remove password from returned data
            unset($customer['password']);
            
            return $customer;
        }
        
        return false;
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