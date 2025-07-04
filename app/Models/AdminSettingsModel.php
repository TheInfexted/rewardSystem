<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminSettingsModel extends Model
{
    protected $table = 'admin_settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'setting_key',
        'setting_value',
        'setting_description'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'setting_key' => 'required|max_length[100]',
        'setting_value' => 'permit_empty'
    ];

    protected $validationMessages = [
        'setting_key' => [
            'required' => 'Setting key is required',
            'max_length' => 'Setting key cannot exceed 100 characters'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get a setting value by key
     */
    public function getSetting($key, $default = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        return $setting ? $setting['setting_value'] : $default;
    }

    /**
     * Set a setting value
     */
    public function setSetting($key, $value, $description = null)
    {
        $existing = $this->where('setting_key', $key)->first();

        if ($existing) {
            // Update existing setting
            return $this->update($existing['id'], [
                'setting_value' => $value,
                'setting_description' => $description ?: $existing['setting_description']
            ]);
        } else {
            // Create new setting
            return $this->insert([
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_description' => $description
            ]);
        }
    }

    /**
     * Get multiple settings as key-value array
     */
    public function getSettings($keys = [])
    {
        $builder = $this->builder();

        if (!empty($keys)) {
            $builder->whereIn('setting_key', $keys);
        }

        $results = $builder->get()->getResultArray();
        
        $settings = [];
        foreach ($results as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }

        return $settings;
    }

    /**
     * Get all settings for admin management
     */
    public function getAllSettings()
    {
        return $this->orderBy('setting_key', 'ASC')->findAll();
    }

    /**
     * Update multiple settings at once
     */
    public function updateSettings($settings)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($settings as $key => $value) {
                $this->setSetting($key, $value);
            }

            $db->transCommit();
            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Failed to update settings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get bonus-related settings
     */
    public function getBonusSettings()
    {
        return $this->getSettings([
            'bonus_redirect_url',
            'bonus_claim_enabled',
            'bonus_terms_text'
        ]);
    }

    /**
     * Get contact settings for landing page
     */
    public function getContactSettings()
    {
        return $this->getSettings([
            'contact_link_1_name',
            'contact_link_1_url', 
            'contact_link_1_enabled',
            'contact_link_2_name',
            'contact_link_2_url',
            'contact_link_2_enabled', 
            'contact_link_3_name',
            'contact_link_3_url',
            'contact_link_3_enabled'
        ]);
    }

    /**
     * Get enabled contact links for public display
     */
    public function getEnabledContactLinks()
    {
        $contactSettings = $this->getContactSettings();
        $enabledLinks = [];

        for ($i = 1; $i <= 3; $i++) {
            if (($contactSettings["contact_link_{$i}_enabled"] ?? '0') === '1') {
                $enabledLinks[] = [
                    'name' => $contactSettings["contact_link_{$i}_name"] ?? "Contact {$i}",
                    'url' => $contactSettings["contact_link_{$i}_url"] ?? '#'
                ];
            }
        }

        return $enabledLinks;
    }
}