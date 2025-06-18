<?php

namespace App\Models;

use CodeIgniter\Model;

class WheelSoundModel extends Model
{
    protected $table = 'wheel_sound_settings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['sound_file', 'sound_type', 'volume', 'enabled', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get active sound by type
     */
    public function getActiveSound($type = 'spin')
    {
        return $this->where('sound_type', $type)
                    ->where('is_active', 1)
                    ->where('enabled', 1)
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    /**
     * Save sound settings
     */
    public function saveSound($data)
    {
        // Deactivate all existing sounds of the same type
        $this->set('is_active', 0)
             ->where('sound_type', $data['sound_type'])
             ->update();
        
        // Insert new sound settings
        return $this->insert($data);
    }

    /**
     * Get all active sounds
     */
    public function getAllActiveSounds()
    {
        return $this->where('is_active', 1)
                    ->where('enabled', 1)
                    ->findAll();
    }
}