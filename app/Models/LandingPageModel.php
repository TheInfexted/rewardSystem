<?php

namespace App\Models;

use CodeIgniter\Model;

class LandingPageModel extends Model
{
    protected $table = 'landing_pages';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'header_image_1',
        'header_image_2',
        'header_text',
        'footer_image_1',
        'footer_image_2',
        'footer_text',
        'welcome_image',
        'welcome_title', 
        'welcome_subtitle', 
        'welcome_text', 
        'welcome_button_text', 
        'welcome_footer_text',
        'free_spins_subtitle',
        'is_active',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get active landing page data
     */
    public function getActiveData()
    {
        return $this->where('is_active', 1)
                    ->orderBy('updated_at', 'DESC')
                    ->first();
    }

    /**
     * Get all landing page versions
     */
    public function getAllVersions()
    {
        return $this->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Deactivate all pages before activating new one
     */
    public function deactivateAll()
    {
        return $this->set(['is_active' => 0])
                    ->where('is_active', 1)
                    ->update();
    }

    /**
     * Custom saveData method for landing page specific operations
     */
    public function saveData($data)
    {
        try {
            // Start transaction
            $this->db->transStart();
            
            // Get current active data for updates
            $currentData = $this->getActiveData();
            
            // For new records or when activating, deactivate all first
            if (!$currentData || (isset($data['is_active']) && $data['is_active'] == 1)) {
                $this->deactivateAll();
            }
            
            // Handle different save scenarios
            if ($currentData && !isset($data['id'])) {
                // Update existing active record
                unset($data['id']); // Remove id from data if it exists
                $result = $this->update($currentData['id'], $data);
            } elseif (isset($data['id']) && $data['id']) {
                // Update specific record by ID
                $id = $data['id'];
                unset($data['id']);
                $result = $this->update($id, $data);
            } else {
                // Insert new record
                $result = $this->insert($data);
            }
            
            // Complete transaction
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                return false;
            }
            
            return $result !== false;
            
        } catch (\Exception $e) {
            log_message('error', 'LandingPageModel saveData error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate landing page data
     */
    public function validateLandingData($data)
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'header_text' => 'permit_empty|max_length[1000]',
            'footer_text' => 'permit_empty|max_length[1000]',
            'header_image_1' => 'permit_empty|max_length[255]',
            'header_image_2' => 'permit_empty|max_length[255]',
            'footer_image_1' => 'permit_empty|max_length[255]',
            'footer_image_2' => 'permit_empty|max_length[255]',
            'welcome_image' => 'permit_empty|max_length[255',
            'welcome_title' => 'permit_empty|max_length[1000]', 
            'welcome_subtitle' => 'permit_empty|max_length[1000]',
            'welcome_text'  => 'permit_empty|max_length[1000]', 
            'welcome_button_text'   => 'permit_empty|max_length[1000]', 
            'welcome_footer_text'   => 'permit_empty|max_length[1000]',
            'free_spins_subtitle' => 'permit_empty|max_length[1000]',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];
        
        $validation->setRules($rules);
        
        return $validation->run($data);
    }

    /**
     * Get validation errors
     */
    public function getValidationErrors()
    {
        return $this->validation->getErrors();
    }
}