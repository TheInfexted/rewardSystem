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
        'sort_order',        // Add this new field
        'views_count',       // Add this new field
        'clicks_count',      // Add this new field
        'last_viewed_at',    // Add this new field
        'is_active',
        'start_date',
        'end_date'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get active ads for Swiper display
     * Updated to use sort_order for Swiper slide ordering
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
                    ->orderBy('sort_order', 'ASC')     // Changed from display_order to sort_order
                    ->orderBy('created_at', 'DESC')
                    ->limit(20)                        // Limit for performance
                    ->findAll();
    }

    /**
     * Get all ads for admin (keep your existing method)
     */
    public function getAllAds()
    {
        return $this->orderBy('sort_order', 'ASC')     // Changed from display_order to sort_order
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Update display order (keep your existing method)
     */
    public function updateOrder($id, $order)
    {
        return $this->update($id, ['display_order' => $order]);
    }

    /**
     * Update sort order for Swiper slides
     */
    public function updateSortOrder($id, $sortOrder)
    {
        return $this->update($id, ['sort_order' => $sortOrder]);
    }

    /**
     * Batch update sort orders for multiple ads
     */
    public function updateSortOrders($orders)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        foreach ($orders as $id => $order) {
            $this->update($id, ['sort_order' => $order]);
        }
        
        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Increment view count (optional - for basic tracking)
     */
    public function incrementViews($id)
    {
        $db = \Config\Database::connect();
        return $db->query("
            UPDATE {$this->table} 
            SET views_count = views_count + 1, 
                last_viewed_at = NOW() 
            WHERE id = ?
        ", [$id]);
    }

    /**
     * Increment click count (optional - for basic tracking)
     */
    public function incrementClicks($id)
    {
        $db = \Config\Database::connect();
        return $db->query("
            UPDATE {$this->table} 
            SET clicks_count = clicks_count + 1 
            WHERE id = ?
        ", [$id]);
    }

    /**
     * Get ads with performance stats (for admin dashboard)
     */
    public function getAdsWithStats()
    {
        return $this->select('
                id, ad_title, ad_type, media_file, click_url, 
                sort_order, is_active, views_count, clicks_count, 
                CASE 
                    WHEN views_count > 0 THEN ROUND((clicks_count / views_count) * 100, 2)
                    ELSE 0 
                END as ctr_percentage,
                created_at, updated_at
            ')
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get next available sort order
     */
    public function getNextSortOrder()
    {
        $result = $this->selectMax('sort_order', 'max_order')->first();
        return ($result['max_order'] ?? 0) + 1;
    }

    /**
     * Reorder all ads sequentially (cleanup function)
     */
    public function reorderAll()
    {
        $ads = $this->orderBy('sort_order', 'ASC')
                   ->orderBy('created_at', 'ASC')
                   ->findAll();
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        foreach ($ads as $index => $ad) {
            $this->update($ad['id'], ['sort_order' => $index + 1]);
        }
        
        $db->transComplete();
        return $db->transStatus();
    }
}