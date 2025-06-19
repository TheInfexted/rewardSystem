<?php

namespace App\Models;

use CodeIgniter\Model;

class RewardSystemAdModel extends Model
{
    protected $table            = 'reward_system_ads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ad_type',
        'ad_title',
        'ad_description',
        'media_file',
        'media_url',
        'click_url',
        'display_order',
        'is_active',
        'start_date',
        'end_date'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get active ads for display
     */
    public function getActiveAds()
    {
        $now = date('Y-m-d H:i:s');
        
        return $this->where('is_active', 1)
                    ->groupStart()
                        ->where('start_date IS NULL')
                        ->orWhere('start_date <=', $now)
                    ->groupEnd()
                    ->groupStart()
                        ->where('end_date IS NULL')
                        ->orWhere('end_date >=', $now)
                    ->groupEnd()
                    ->orderBy('display_order', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get all ads for admin
     */
    public function getAllAds()
    {
        return $this->orderBy('display_order', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Update display order
     */
    public function updateOrder($id, $order)
    {
        return $this->update($id, ['display_order' => $order]);
    }
}