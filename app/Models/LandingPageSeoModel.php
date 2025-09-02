<?php

namespace App\Models;

use CodeIgniter\Model;

class LandingPageSeoModel extends Model
{
    protected $table = 'landing_page_seo';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'meta_title',
        'meta_keywords',
        'meta_description',
        'meta_content'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get SEO data (assuming single row for landing page)
     */
    public function getSeoData()
    {
        return $this->first();
    }

    /**
     * Save or update SEO data
     */
    public function saveSeoData($data)
    {
        $existing = $this->first();
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data);
        }
    }
}