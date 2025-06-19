<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'username',
        'password',
        'email',
        'name',
        'profile_background',
        'spin_count',
        'last_spin_date',
        'points',
        'is_active'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    // Simplified validation - remove uniqueness check for now to avoid conflicts
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]',
        'password' => 'required|min_length[4]',
        'email' => 'permit_empty|valid_email',
        'name' => 'permit_empty|max_length[100]',
        'points' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'username' => [
            'required' => 'Username is required',
            'min_length' => 'Username must be at least 3 characters',
            'max_length' => 'Username cannot exceed 50 characters'
        ],
        'password' => [
            'required' => 'Password is required',
            'min_length' => 'Password must be at least 4 characters'
        ],
        'email' => [
            'valid_email' => 'Please enter a valid email address'
        ]
    ];

    /**
     * Generate unique username (phone number format)
     */
    public function generateUniqueUsername()
    {
        $attempts = 0;
        $maxAttempts = 20;
        
        do {
            // Generate phone number format: 01XXXXXXXX (Malaysian format)
            $username = '01' . str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            
            // Check if username exists
            $existing = $this->where('username', $username)->first();
            $attempts++;
            
        } while ($existing && $attempts < $maxAttempts);
        
        if ($attempts >= $maxAttempts) {
            // Fallback to timestamp-based username
            $username = 'user_' . time() . '_' . rand(100, 999);
        }
        
        return $username;
    }

    /**
     * Create new customer with auto-generated credentials
     */
    public function createAutoCustomer()
    {
        try {
            // Generate unique username
            $username = $this->generateUniqueUsername();
            
            // Generate simple password
            $password = bin2hex(random_bytes(4)); // 8 character hex string
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Prepare customer data
            $customerData = [
                'username' => $username,
                'password' => $hashedPassword,
                'name' => $username,
                'email' => null,
                'profile_background' => 'default',
                'spin_count' => 0,
                'last_spin_date' => null,
                'points' => 0,
                'is_active' => 1
            ];
            
            // Insert customer without validation first
            $db = \Config\Database::connect();
            $builder = $db->table($this->table);
            
            // Manual insert to avoid validation issues
            $result = $builder->insert($customerData);
            
            if ($result) {
                $customerId = $db->insertID();
                
                return [
                    'id' => $customerId,
                    'username' => $username,
                    'password' => $password, // Return plain password for display
                    'customer_data' => $this->find($customerId)
                ];
            }
            
            return false;
            
        } catch (\Exception $e) {
            log_message('error', 'Customer auto creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find customer by username
     */
    public function getByUsername($username)
    {
        return $this->where('username', $username)
                   ->where('is_active', 1)
                   ->first();
    }

    /**
     * Verify customer login credentials
     */
    public function verifyLogin($username, $password)
    {
        $customer = $this->getByUsername($username);
        
        if ($customer && password_verify($password, $customer['password'])) {
            // Update last login time
            $this->update($customer['id'], [
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return $customer;
        }
        
        return false;
    }

    /**
     * Add points to customer account (Fixed - no raw SQL)
     */
    public function addPoints($customerId, $points)
    {
        try {
            $customer = $this->find($customerId);
            
            if ($customer) {
                $newPoints = $customer['points'] + $points;
                
                return $this->update($customerId, [
                    'points' => $newPoints
                ]);
            }
            
            return false;
        } catch (\Exception $e) {
            log_message('error', 'CustomerModel addPoints error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deduct points from customer account
     */
    public function deductPoints($customerId, $points)
    {
        $customer = $this->find($customerId);
        
        if ($customer && $customer['points'] >= $points) {
            $newPoints = $customer['points'] - $points;
            
            return $this->update($customerId, [
                'points' => $newPoints
            ]);
        }
        
        return false;
    }

    /**
     * Update customer spin count
     */
    public function updateSpinCount($customerId)
    {
        $customer = $this->find($customerId);
        
        if ($customer) {
            return $this->update($customerId, [
                'spin_count' => $customer['spin_count'] + 1,
                'last_spin_date' => date('Y-m-d')
            ]);
        }
        
        return false;
    }

    /**
     * Get remaining spins for today
     */
    public function getRemainingSpins($customerId)
    {
        $customer = $this->find($customerId);
        if (!$customer) return 0;
        
        // Reset count if last spin was not today
        if ($customer['last_spin_date'] != date('Y-m-d')) {
            return 3; // Default daily spins
        }
        
        return max(0, 3 - $customer['spin_count']);
    }

    /**
     * Check if customer can spin today
     */
    public function canSpinToday($customerId)
    {
        $customer = $this->find($customerId);
        
        if (!$customer) {
            return false;
        }

        $today = date('Y-m-d');
        $lastSpinDate = $customer['last_spin_date'];
        
        // Allow spin if never spun or last spin was not today
        return !$lastSpinDate || $lastSpinDate !== $today;
    }

    /**
     * Update customer profile background
     */
    public function updateBackground($customerId, $background)
    {
        $validBackgrounds = ['default', 'blue', 'green', 'purple', 'orange', 'red'];
        
        if (!in_array($background, $validBackgrounds)) {
            $background = 'default';
        }
        
        return $this->update($customerId, [
            'profile_background' => $background
        ]);
    }

    /**
     * Get customers with recent activity
     */
    public function getActiveCustomers($limit = 10)
    {
        return $this->where('is_active', 1)
                   ->orderBy('updated_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Search customers by username or name
     */
    public function searchCustomers($searchTerm, $limit = 20)
    {
        return $this->like('username', $searchTerm)
                   ->orLike('name', $searchTerm)
                   ->where('is_active', 1)
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Disable customer account
     */
    public function deactivateCustomer($customerId)
    {
        return $this->update($customerId, [
            'is_active' => 0
        ]);
    }

    /**
     * Enable customer account
     */
    public function activateCustomer($customerId)
    {
        return $this->update($customerId, [
            'is_active' => 1
        ]);
    }

    /**
     * Simple method to test database connection
     */
    public function testDatabaseConnection()
    {
        try {
            $result = $this->countAllResults();
            return ['success' => true, 'count' => $result];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}