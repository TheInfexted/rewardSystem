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
    
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[customers.username,id,{id}]',
        'password' => 'required|min_length[6]',
        'email' => 'permit_empty|valid_email',
        'name' => 'permit_empty|max_length[100]'
    ];

    /**
     * Generate unique username with 01 prefix
     */
    public function generateUniqueUsername()
    {
        do {
            $username = '01' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            $exists = $this->where('username', $username)->first();
        } while ($exists);
        
        return $username;
    }

    /**
     * Get customer by username
     */
    public function getByUsername($username)
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Update spin count and date
     */
    public function updateSpinData($customerId)
    {
        return $this->update($customerId, [
            'spin_count' => $this->db->raw('spin_count + 1'),
            'last_spin_date' => date('Y-m-d')
        ]);
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
     * Add points to customer
     */
    public function addPoints($customerId, $points)
    {
        return $this->update($customerId, [
            'points' => $this->db->raw("points + {$points}")
        ]);
    }
}