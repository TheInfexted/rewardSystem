<?php

namespace App\Models;

use CodeIgniter\Model;

class BonusClaimModel extends Model
{
    protected $table = 'bonus_claims';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'session_id',
        'user_ip', 
        'user_name',
        'phone_number',
        'email',
        'bonus_type',
        'bonus_amount',
        'claim_time',
        'user_agent',
        'status',
        'admin_notes',
        'user_account',
        'account_type',
        'customer_id',        // Added for customer relationship
        'platform_selected'   // Added to track WhatsApp/Telegram selection
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'user_name' => 'required|min_length[3]|max_length[100]',
        'phone_number' => 'permit_empty|min_length[10]|max_length[20]',
        'email' => 'permit_empty|valid_email',
        'bonus_type' => 'required|max_length[50]',
        'bonus_amount' => 'required|decimal',
        'user_ip' => 'required|valid_ip',
        'session_id' => 'required|max_length[128]'
    ];

    /**
     * Get active claims (pending status)
     */
    public function getActiveClaims()
    {
        return $this->where('status', 'pending')
                    ->orderBy('claim_time', 'DESC')
                    ->findAll();
    }

    /**
     * Get claims by status
     */
    public function getByStatus($status)
    {
        return $this->where('status', $status)
                    ->orderBy('claim_time', 'DESC')
                    ->findAll();
    }

    /**
     * Get claims by customer
     */
    public function getByCustomer($customerId)
    {
        return $this->where('customer_id', $customerId)
                    ->orderBy('claim_time', 'DESC')
                    ->findAll();
    }

    /**
     * Update claim status
     */
    public function updateStatus($id, $status, $adminNotes = null)
    {
        $data = ['status' => $status];
        if ($adminNotes) {
            $data['admin_notes'] = $adminNotes;
        }
        
        return $this->update($id, $data);
    }
}