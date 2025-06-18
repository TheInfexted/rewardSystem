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

    /**
     * Get claims with pagination
     */
    public function getClaimsWithPagination($perPage = 20, $status = null, $search = null)
    {
        $builder = $this->builder();
        
        // Join with customers table for additional info
        $builder->select('bonus_claims.*, customers.username as customer_username')
                ->join('customers', 'customers.id = bonus_claims.customer_id', 'left');
        
        // Filter by status if provided
        if ($status && $status !== 'all') {
            $builder->where('bonus_claims.status', $status);
        }
        
        // Search functionality
        if ($search) {
            $builder->groupStart()
                    ->like('bonus_claims.user_name', $search)
                    ->orLike('bonus_claims.phone_number', $search)
                    ->orLike('bonus_claims.email', $search)
                    ->orLike('bonus_claims.bonus_type', $search)
                    ->orLike('bonus_claims.user_ip', $search)
                    ->orLike('customers.username', $search)
                    ->groupEnd();
        }
        
        // Order by claim time
        $builder->orderBy('bonus_claims.claim_time', 'DESC');
        
        // Return paginated results
        return $this->paginate($perPage);
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics()
    {
        $db = \Config\Database::connect();
        
        return [
            'total_claims' => $this->countAllResults(),
            'pending_claims' => $this->where('status', 'pending')->countAllResults(false),
            'processed_claims' => $this->where('status', 'processed')->countAllResults(false),
            'cancelled_claims' => $this->where('status', 'cancelled')->countAllResults(false),
            'today_claims' => $this->where('DATE(claim_time)', date('Y-m-d'))->countAllResults(false),
            'total_amount' => $db->table($this->table)
                                 ->selectSum('bonus_amount')
                                 ->where('status', 'processed')
                                 ->get()
                                 ->getRow()
                                 ->bonus_amount ?? 0
        ];
    }

    /**
     * Get recent claims for dashboard
     */
    public function getRecentClaims($limit = 10)
    {
        return $this->select('bonus_claims.*, customers.username as customer_username')
                    ->join('customers', 'customers.id = bonus_claims.customer_id', 'left')
                    ->orderBy('claim_time', 'DESC')
                    ->limit($limit)
                    ->find();
    }

    /**
     * Check if user has already claimed today
     */
    public function hasClaimedToday($userIp, $sessionId = null)
    {
        $builder = $this->where('user_ip', $userIp)
                        ->where('DATE(claim_time)', date('Y-m-d'));
        
        if ($sessionId) {
            $builder->orWhere('session_id', $sessionId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Get claims by date range
     */
    public function getClaimsByDateRange($startDate, $endDate, $status = null)
    {
        $builder = $this->where('claim_time >=', $startDate)
                        ->where('claim_time <=', $endDate);
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->orderBy('claim_time', 'DESC')->findAll();
    }

    /**
     * Export claims to CSV
     */
    public function exportToCsv($status = null, $startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        
        $builder->select('
            bonus_claims.id,
            bonus_claims.user_name,
            customers.username as customer_id,
            bonus_claims.phone_number,
            bonus_claims.email,
            bonus_claims.bonus_type,
            bonus_claims.bonus_amount,
            bonus_claims.platform_selected,
            bonus_claims.claim_time,
            bonus_claims.status,
            bonus_claims.user_ip,
            bonus_claims.account_type
        ')->join('customers', 'customers.id = bonus_claims.customer_id', 'left');
        
        if ($status && $status !== 'all') {
            $builder->where('bonus_claims.status', $status);
        }
        
        if ($startDate && $endDate) {
            $builder->where('bonus_claims.claim_time >=', $startDate)
                    ->where('bonus_claims.claim_time <=', $endDate);
        }
        
        return $builder->orderBy('bonus_claims.claim_time', 'DESC')->get()->getResultArray();
    }
}