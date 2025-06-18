<?php

namespace App\Models;

use CodeIgniter\Model;

class LandingPageMusicModel extends Model
{
    protected $table = 'landing_page_music';
    protected $primaryKey = 'id';
    protected $allowedFields = ['music_file', 'volume', 'autoplay', 'loop', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get active music settings
     */
    public function getActiveMusic()
    {
        return $this->where('is_active', 1)
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    /**
     * Save music settings
     */
    public function saveMusic($data)
    {
        // Deactivate all existing music
        $this->where('is_active', 1)->set('is_active', 0)->update();
    
        // Insert new music settings
        return $this->insert($data);
    }
}