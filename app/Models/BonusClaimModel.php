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

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'user_ip' => 'required|max_length[45]',
        'user_name' => 'required|max_length[100]',
        'phone_number' => 'required|max_length[20]',
        'bonus_type' => 'max_length[50]',
        'bonus_amount' => 'numeric',
        'status' => 'in_list[pending,processed,cancelled]'
    ];

    protected $validationMessages = [
        'user_ip' => [
            'required' => 'User IP is required',
            'max_length' => 'User IP is too long'
        ],
        'user_name' => [
            'required' => 'User name is required',
            'max_length' => 'User name is too long'
        ],
        'phone_number' => [
            'required' => 'Phone number is required',
            'max_length' => 'Phone number is too long'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeInsertCallback'];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Before insert callback - set claim_time if not provided
     */
    protected function beforeInsertCallback(array $data)
    {
        if (!isset($data['data']['claim_time'])) {
            $data['data']['claim_time'] = date('Y-m-d H:i:s');
        }
        
        return $data;
    }

    /**
     * Check if user has claimed bonus today
     */
    public function hasClaimedToday(string $userIp): bool
    {
        $today = date('Y-m-d');
        
        $result = $this->where('user_ip', $userIp)
                      ->where('DATE(claim_time)', $today)
                      ->where('status !=', 'cancelled')
                      ->first();
                      
        return !empty($result);
    }

    /**
     * Get user's claims history
     */
    public function getUserClaims(string $userIp, int $limit = 10): array
    {
        return $this->where('user_ip', $userIp)
                   ->orderBy('claim_time', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get claims by status
     */
    public function getClaimsByStatus(string $status = 'pending'): array
    {
        return $this->where('status', $status)
                   ->orderBy('claim_time', 'DESC')
                   ->findAll();
    }

    /**
     * Override insert method with detailed error logging
     */
    public function insert($data = null, bool $returnID = true)
    {
        log_message('debug', 'BonusClaimModel::insert called with data: ' . json_encode($data));
        
        try {
            $result = parent::insert($data, $returnID);
            
            if ($result === false) {
                $errors = $this->errors();
                log_message('error', 'BonusClaimModel::insert failed with validation errors: ' . json_encode($errors));
                
                // Check database errors
                $dbError = $this->db->error();
                if (!empty($dbError['message'])) {
                    log_message('error', 'BonusClaimModel::insert database error: ' . json_encode($dbError));
                }
            } else {
                log_message('debug', 'BonusClaimModel::insert successful with ID: ' . $result);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', 'BonusClaimModel::insert exception: ' . $e->getMessage());
            log_message('error', 'BonusClaimModel::insert stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}