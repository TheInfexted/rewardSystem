<?php

namespace App\Models;

use CodeIgniter\Model;

class BonusClaimModel extends Model
{
    protected $table = 'bonus_claims';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
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
        'admin_notes'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_name' => 'required|min_length[2]|max_length[100]',
        'phone_number' => 'required|min_length[10]|max_length[20]',
        'email' => 'permit_empty|valid_email|max_length[100]',
        'user_ip' => 'required|valid_ip'
    ];

    protected $validationMessages = [
        'user_name' => [
            'required' => 'Name is required',
            'min_length' => 'Name must be at least 2 characters long',
            'max_length' => 'Name cannot exceed 100 characters'
        ],
        'phone_number' => [
            'required' => 'Phone number is required',
            'min_length' => 'Phone number must be at least 10 digits',
            'max_length' => 'Phone number cannot exceed 20 characters'
        ],
        'email' => [
            'valid_email' => 'Please enter a valid email address'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeInsert'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['beforeUpdate'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    protected function beforeInsert(array $data)
    {
        $data['data']['claim_time'] = date('Y-m-d H:i:s');
        return $data;
    }

    protected function beforeUpdate(array $data)
    {
        return $data;
    }

    /**
     * Get all claims with pagination and filtering
     */
    public function getClaimsWithPagination($limit = 20, $offset = 0, $filters = [])
    {
        $builder = $this->builder();

        // Apply filters
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('claim_time >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('claim_time <=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('user_name', $filters['search'])
                ->orLike('phone_number', $filters['search'])
                ->orLike('email', $filters['search'])
                ->orLike('user_ip', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['bonus_type'])) {
            $builder->where('bonus_type', $filters['bonus_type']);
        }

        return $builder->orderBy('claim_time', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Get total count with filters
     */
    public function getClaimsCount($filters = [])
    {
        $builder = $this->builder();

        // Apply same filters as above
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('claim_time >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('claim_time <=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('user_name', $filters['search'])
                ->orLike('phone_number', $filters['search'])
                ->orLike('email', $filters['search'])
                ->orLike('user_ip', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['bonus_type'])) {
            $builder->where('bonus_type', $filters['bonus_type']);
        }

        return $builder->countAllResults();
    }

    /**
     * Check if IP has already claimed bonus today
     */
    public function hasClaimedToday($userIp)
    {
        return $this->where('user_ip', $userIp)
            ->where('DATE(claim_time)', date('Y-m-d'))
            ->countAllResults() > 0;
    }

    /**
     * Get claims statistics
     */
    public function getClaimsStats()
    {
        $db = \Config\Database::connect();

        // Basic stats
        $stats = [
            'total_claims' => $this->countAll(),
            'today_claims' => $this->where('DATE(claim_time)', date('Y-m-d'))->countAllResults(),
            'pending_claims' => $this->where('status', 'pending')->countAllResults(),
            'processed_claims' => $this->where('status', 'processed')->countAllResults()
        ];

        // Get unique IPs count
        $uniqueIpsQuery = $db->query("
            SELECT COUNT(DISTINCT user_ip) as unique_ips 
            FROM bonus_claims
        ");
        $uniqueIpsResult = $uniqueIpsQuery->getRowArray();
        $stats['unique_ips'] = $uniqueIpsResult['unique_ips'] ?? 0;

        // Get claims by hour for today (for chart)
        $hourlyStats = $db->query("
            SELECT 
                HOUR(claim_time) as hour,
                COUNT(*) as count
            FROM bonus_claims 
            WHERE DATE(claim_time) = CURDATE()
            GROUP BY HOUR(claim_time)
            ORDER BY hour
        ")->getResultArray();

        $stats['hourly_claims'] = $hourlyStats;

        // Additional useful statistics
        
        // Top bonus types
        $topBonusTypes = $db->query("
            SELECT 
                bonus_type,
                COUNT(*) as count,
                SUM(bonus_amount) as total_amount
            FROM bonus_claims 
            GROUP BY bonus_type
            ORDER BY count DESC
            LIMIT 5
        ")->getResultArray();
        $stats['top_bonus_types'] = $topBonusTypes;

        // Claims by day for last 7 days
        $dailyStats = $db->query("
            SELECT 
                DATE(claim_time) as date,
                COUNT(*) as count
            FROM bonus_claims 
            WHERE claim_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(claim_time)
            ORDER BY date DESC
        ")->getResultArray();
        $stats['daily_claims'] = $dailyStats;

        return $stats;
    }

    /**
     * Get IP claim statistics
     */
    public function getIpStats()
    {
        $db = \Config\Database::connect();
        
        $ipStats = $db->query("
            SELECT 
                user_ip,
                COUNT(*) as claim_count,
                MAX(claim_time) as last_claim,
                GROUP_CONCAT(DISTINCT bonus_type) as bonus_types
            FROM bonus_claims 
            GROUP BY user_ip
            HAVING claim_count > 1
            ORDER BY claim_count DESC
            LIMIT 20
        ")->getResultArray();
        
        return $ipStats;
    }
}