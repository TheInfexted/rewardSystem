<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsAppNumbersModel extends Model
{
    protected $table = 'whatsapp_numbers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'phone_number',
        'display_name',
        'is_active'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'phone_number' => 'required|regex_match[/^[\d\+\-\s\(\)]+$/]|max_length[20]|is_unique[whatsapp_numbers.phone_number,id,{id}]',
        'display_name' => 'permit_empty|max_length[100]',
        'is_active' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'phone_number' => [
            'required' => 'Phone number is required.',
            'regex_match' => 'Please enter a valid phone number.',
            'max_length' => 'Phone number cannot exceed 20 characters.',
            'is_unique' => 'This phone number already exists.'
        ],
        'display_name' => [
            'max_length' => 'Display name cannot exceed 100 characters.'
        ],
        'is_active' => [
            'in_list' => 'Active status must be 0 or 1.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = [];
    protected $beforeUpdate = [];
    protected $beforeDelete = [];
    protected $afterInsert = [];
    protected $afterUpdate = [];
    protected $afterDelete = [];

    /**
     * Get all active WhatsApp numbers
     */
    public function getActiveNumbers()
    {
        return $this->where('is_active', 1)
                   ->orderBy('created_at', 'ASC')
                   ->findAll();
    }

    /**
     * Get a random active WhatsApp number
     */
    public function getRandomActiveNumber()
    {
        $activeNumbers = $this->getActiveNumbers();
        
        if (empty($activeNumbers)) {
            return null;
        }

        // Get random index
        $randomIndex = array_rand($activeNumbers);
        return $activeNumbers[$randomIndex];
    }

    /**
     * Get WhatsApp numbers for admin management
     */
    public function getNumbersForAdmin()
    {
        return $this->orderBy('is_active', 'DESC')
                   ->orderBy('created_at', 'ASC')
                   ->findAll();
    }

    /**
     * Check if phone number exists
     */
    public function phoneNumberExists($phoneNumber, $excludeId = null)
    {
        $builder = $this->where('phone_number', $phoneNumber);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Generate WhatsApp URL from phone number
     */
    public function generateWhatsAppUrl($phoneNumber)
    {
        // Clean phone number (remove spaces, dashes, parentheses)
        $cleanNumber = preg_replace('/[\s\-\(\)]/', '', $phoneNumber);
        
        // Ensure it starts with country code if not already
        if (!str_starts_with($cleanNumber, '+')) {
            // If it doesn't start with +, assume it's already formatted
            $cleanNumber = '+' . ltrim($cleanNumber, '+');
        }
        
        return 'https://wa.me/' . $cleanNumber;
    }

    /**
     * Get count of active numbers
     */
    public function getActiveCount()
    {
        return $this->where('is_active', 1)->countAllResults();
    }
}
