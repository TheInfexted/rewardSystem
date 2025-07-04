<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LandingPageModel;
use App\Models\WheelItemsModel;
use App\Models\LandingPageMusicModel;
use App\Models\AdminSettingsModel;
use \App\Models\WheelSoundModel;


class LandingPageController extends BaseController
{
    protected $landingPageModel;
    protected $wheelSoundModel;
    protected $adminSettingsModel;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->landingPageModel = new LandingPageModel();
        $this->wheelItemsModel = new WheelItemsModel();
        $this->landingPageMusicModel = new LandingPageMusicModel();
        $this->wheelSoundModel = new WheelSoundModel();
        $this->adminSettingsModel = new AdminSettingsModel();
        
        // Ensure upload directory exists
        $uploadPath = FCPATH . 'uploads/landing/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Ensure music upload directory exists
        $musicPath = FCPATH . 'uploads/music/';
        if (!is_dir($musicPath)) {
            mkdir($musicPath, 0755, true);
        }
        
            $soundsPath = FCPATH . 'uploads/sounds/';
        if (!is_dir($soundsPath)) {
            mkdir($soundsPath, 0755, true);
        }
    }

    /**
     * Display landing page builder interface
     */
    public function index()
    {
        try {
            // Get landing page data
            $landingData = $this->landingPageModel->getActiveData();
            
            // Get bonus settings for the modal
            $bonusSettings = $this->adminSettingsModel->getBonusSettings();

            $data = [
                'title' => 'Landing Page Builder',
                'landing_data' => $landingData,
                'bonus_settings' => $bonusSettings
            ];

            return view('admin/landing_page/builder', $data);
        } catch (\Exception $e) {
            log_message('error', 'Landing page builder error: ' . $e->getMessage());
            return redirect()->to('admin/dashboard')->with('error', 'Failed to load landing page builder.');
        }
    }

    /**
     * Save landing page data
     */
    public function save()
    {
        if ($this->request->getMethod() === 'POST') {
            try {
                // Get current data to identify old files for deletion
                $currentData = $this->landingPageModel->getActiveData();
                
                $data = [
                    'header_text' => $this->request->getPost('header_text'),
                    'footer_text' => $this->request->getPost('footer_text'),
                    'welcome_title' => $this->request->getPost('welcome_title'),
                    'welcome_subtitle' => $this->request->getPost('welcome_subtitle'),
                    'welcome_text' => $this->request->getPost('welcome_text'),
                    'welcome_button_text' => $this->request->getPost('welcome_button_text'),
                    'welcome_footer_text' => $this->request->getPost('welcome_footer_text'),
                    'free_spins_subtitle' => $this->request->getPost('free_spins_subtitle'),
                    'is_active' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Handle media uploads (images and videos) for header/footer
                $mediaFields = ['header_media_1', 'header_media_2', 'footer_media_1', 'footer_media_2'];
                
                foreach ($mediaFields as $field) {
                    $newMedia = $this->uploadMedia($field);
                    if ($newMedia !== null) {
                        // Delete old media if it exists and is different
                        if (!empty($currentData[$field]) && $currentData[$field] !== $newMedia['filename']) {
                            $this->deleteMedia($currentData[$field]);
                        }
                        $data[$field] = $newMedia['filename'];
                        $data[$field . '_type'] = $newMedia['type'];
                    } else {
                        // Keep existing media if no new upload
                        $data[$field] = $currentData[$field] ?? null;
                        $data[$field . '_type'] = $currentData[$field . '_type'] ?? 'image';
                    }
                }

                // Handle welcome image (still image only) - FIXED: Use uploadImage method
                $newWelcomeImage = $this->uploadImage('welcome_image');
                if ($newWelcomeImage !== null) {
                    if (!empty($currentData['welcome_image']) && $currentData['welcome_image'] !== $newWelcomeImage) {
                        $this->deleteImage($currentData['welcome_image']);
                    }
                    $data['welcome_image'] = $newWelcomeImage;
                } else {
                    $data['welcome_image'] = $currentData['welcome_image'] ?? null;
                }

                // Save to database
                if ($this->landingPageModel->saveData($data)) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Landing page settings saved successfully!'
                    ]);
                } else {
                    $errors = $this->landingPageModel->errors();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Validation failed: ' . implode(', ', $errors)
                    ]);
                }

            } catch (\Exception $e) {
                log_message('error', 'Landing page save error: ' . $e->getMessage());
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'An error occurred while saving: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
    }

    /**
     * Remove specific image
     */
    public function removeImage()
    {
        if ($this->request->isAJAX()) {
            $field = $this->request->getPost('field');
            $validFields = ['header_media_1', 'header_media_2', 'footer_media_1', 'footer_media_2', 'welcome_image'];
            
            if (!in_array($field, $validFields)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid media field'
                ]);
            }

            try {
                // Get current data
                $currentData = $this->landingPageModel->getActiveData();
                
                if (!empty($currentData[$field])) {
                    // Delete the physical file
                    if ($field === 'welcome_image') {
                        $this->deleteMedia($currentData[$field]);
                    } else {
                        $this->deleteMedia($currentData[$field]);
                    }
                    
                    // Update database - set field to null
                    $updateData = [$field => null, 'updated_at' => date('Y-m-d H:i:s')];
                    
                    // Also reset media type for non-welcome fields
                    if ($field !== 'welcome_image') {
                        $updateData[$field . '_type'] = 'image';
                    }
                    
                    if ($this->landingPageModel->saveData($updateData)) {
                        return $this->response->setJSON([
                            'success' => true,
                            'message' => 'Media removed successfully!',
                            'reload' => true  // ADD THIS FLAG
                        ]);
                    } else {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Failed to update database'
                        ]);
                    }
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No media found to remove'
                    ]);
                }

            } catch (\Exception $e) {
                log_message('error', 'Media removal error: ' . $e->getMessage());
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error removing media: ' . $e->getMessage()
                ]);
            }
        }

        // For non-AJAX requests, redirect back to landing page
        return redirect()->to('admin/landing-page')->with('success', 'Media removed successfully!');
    }

    /**
     * Enhanced image upload with better validation (for welcome_image only)
     */
    private function uploadImage($fieldName)
    {
        $img = $this->request->getFile($fieldName);
        
        if ($img && $img->isValid() && !$img->hasMoved()) {
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($img->getMimeType(), $allowedTypes)) {
                throw new \Exception('Invalid image type. Only JPEG, PNG, GIF, and WebP are allowed.');
            }

            // Validate file size (max 1MB)
            if ($img->getSize() > 1 * 1024 * 1024) {
                throw new \Exception('Image file too large. Maximum size is 5MB.');
            }

            // Create directory if it doesn't exist
            $uploadPath = FCPATH . 'uploads/landing/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Generate unique filename with timestamp
            $extension = $img->getClientExtension();
            $newName = $fieldName . '_' . time() . '_' . uniqid() . '.' . $extension;
            
            if ($img->move($uploadPath, $newName)) {
                return $newName;
            } else {
                throw new \Exception('Failed to move uploaded file.');
            }
        }
        
        return null;
    }

    /**
     * Enhanced media upload supporting both images and videos
     */
    private function uploadMedia($fieldName)
    {
        $file = $this->request->getFile($fieldName);
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $mimeType = $file->getMimeType();
            
            // Determine if it's image or video
            $isImage = strpos($mimeType, 'image/') === 0;
            $isVideo = strpos($mimeType, 'video/') === 0;
            
            if ($isImage) {
                // Validate image types
                $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($mimeType, $allowedImageTypes)) {
                    throw new \Exception('Invalid image type. Only JPEG, PNG, GIF, and WebP images are allowed.');
                }
                
                // Validate image size (max 1MB)
                if ($file->getSize() > 1 * 1024 * 1024) {
                    throw new \Exception('Image file too large. Maximum size is 1MB.');
                }
                
                $mediaType = 'image';
                
            } elseif ($isVideo) {
                // Validate video types
                $allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/mov', 'video/avi'];
                if (!in_array($mimeType, $allowedVideoTypes)) {
                    throw new \Exception('Invalid video type. Only MP4, WebM, OGG, MOV, and AVI videos are allowed.');
                }
                
                // Validate video size (max 5MB)
                if ($file->getSize() > 5 * 1024 * 1024) {
                    throw new \Exception('Video file too large. Maximum size is 5MB.');
                }
                
                $mediaType = 'video';
                
            } else {
                throw new \Exception('Invalid file type. Only images and videos are allowed.');
            }

            // Create directory if it doesn't exist
            $uploadPath = FCPATH . 'uploads/landing/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Generate unique filename with timestamp
            $extension = $file->getClientExtension();
            $newName = $fieldName . '_' . time() . '_' . uniqid() . '.' . $extension;
            
            if ($file->move($uploadPath, $newName)) {
                return [
                    'filename' => $newName,
                    'type' => $mediaType
                ];
            } else {
                throw new \Exception('Failed to move uploaded file.');
            }
        }
        
        return null;
    }

    /**
     * Delete image file from filesystem
     */
    private function deleteImage($filename)
    {
        if (empty($filename)) {
            return false;
        }

        $filePath = FCPATH . 'uploads/landing/' . $filename;
        
        if (file_exists($filePath)) {
            $deleted = unlink($filePath);
            if ($deleted) {
                log_message('info', 'Deleted old image: ' . $filename);
            } else {
                log_message('error', 'Failed to delete image: ' . $filename);
            }
            return $deleted;
        }

        return false;
    }

    /**
     * Delete media file (image or video) from filesystem
     */
    private function deleteMedia($filename)
    {
        if (empty($filename)) {
            return false;
        }

        $filePath = FCPATH . 'uploads/landing/' . $filename;
        
        if (file_exists($filePath)) {
            $deleted = unlink($filePath);
            if ($deleted) {
                log_message('info', 'Deleted old media file: ' . $filename);
            } else {
                log_message('error', 'Failed to delete media file: ' . $filename);
            }
            return $deleted;
        }

        return redirect()->to('admin/landing-page');
    }

    /**
     * Get image info for management
     */
    public function getImageInfo()
    {
        if ($this->request->isAJAX()) {
            $currentData = $this->landingPageModel->getActiveData();
            $imageInfo = [];

            $imageFields = ['header_image_1', 'header_image_2', 'footer_image_1', 'footer_image_2'];
            
            foreach ($imageFields as $field) {
                if (!empty($currentData[$field])) {
                    $filePath = FCPATH . 'uploads/landing/' . $currentData[$field];
                    $imageInfo[$field] = [
                        'filename' => $currentData[$field],
                        'exists' => file_exists($filePath),
                        'size' => file_exists($filePath) ? filesize($filePath) : 0,
                        'url' => base_url('uploads/landing/' . $currentData[$field])
                    ];
                } else {
                    $imageInfo[$field] = null;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'images' => $imageInfo
            ]);
        }

        return redirect()->to('admin/landing-page');
    }

    /**
     * Clean up orphaned images (admin utility)
     */
    public function cleanupImages()
    {
        if ($this->request->isAJAX()) {
            try {
                $uploadPath = FCPATH . 'uploads/landing/';
                $currentData = $this->landingPageModel->getActiveData();
                
                // Get list of images currently in use
                $usedImages = array_filter([
                    $currentData['header_image_1'] ?? null,
                    $currentData['header_image_2'] ?? null,
                    $currentData['footer_image_1'] ?? null,
                    $currentData['footer_image_2'] ?? null
                ]);

                // Get all files in upload directory
                $allFiles = array_diff(scandir($uploadPath), ['.', '..']);
                $deletedFiles = [];
                $deletedCount = 0;

                foreach ($allFiles as $file) {
                    if (!in_array($file, $usedImages)) {
                        $filePath = $uploadPath . $file;
                        if (is_file($filePath) && unlink($filePath)) {
                            $deletedFiles[] = $file;
                            $deletedCount++;
                        }
                    }
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Cleaned up {$deletedCount} orphaned images",
                    'deleted_files' => $deletedFiles
                ]);

            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error during cleanup: ' . $e->getMessage()
                ]);
            }
        }

        return redirect()->to('admin/landing-page');
    }

    public function preview()
    {
        $data = [
            'page_data' => $this->landingPageModel->getActiveData(),
            'wheel_items' => $this->wheelItemsModel->getWheelItems(), 
            'spins_remaining' => 0,
        ];

        return view('public/landing_page', $data);
    }

    /**
     * Handle multiple image uploads
     */
    private function uploadMultipleImages($fieldName)
    {
        $images = [];
        $files = $this->request->getFileMultiple($fieldName);
        
        if ($files) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move(WRITEPATH . 'uploads/landing/content', $newName);
                    $images[] = $newName;
                }
            }
        }
        
        return $images;
    }
    
    /**
     * Save background music
     */
    public function saveMusic()
    {
        if ($this->request->getMethod() === 'POST') {
            try {
                $data = [
                    'volume' => $this->request->getPost('volume') ?? 0.5,
                    'autoplay' => $this->request->getPost('autoplay') ? 1 : 0,
                    'loop' => $this->request->getPost('loop') ? 1 : 0,
                    'is_active' => 1
                ];
    
                // Handle music file upload
                $musicFile = $this->request->getFile('music_file');
                if ($musicFile && $musicFile->isValid() && !$musicFile->hasMoved()) {
                    // Validate file type
                    $allowedTypes = ['audio/mpeg', 'audio/mp3'];
                    if (!in_array($musicFile->getMimeType(), $allowedTypes)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Only MP3 files are allowed.'
                        ]);
                    }
    
                    // Delete old music file if exists
                    $currentMusic = $this->landingPageMusicModel->getActiveMusic();
                    if ($currentMusic && !empty($currentMusic['music_file'])) {
                        $oldFile = FCPATH . 'uploads/music/' . $currentMusic['music_file'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
    
                    // Save new file
                    $newName = $musicFile->getRandomName();
                    $musicFile->move(FCPATH . 'uploads/music', $newName);
                    $data['music_file'] = $newName;
                } else {
                    // Keep existing file if no new upload
                    $currentMusic = $this->landingPageMusicModel->getActiveMusic();
                    if ($currentMusic) {
                        $data['music_file'] = $currentMusic['music_file'];
                    }
                }
    
                // Save to database
                if ($this->landingPageMusicModel->saveMusic($data)) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Background music settings saved successfully!'
                    ]);
                }
    
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save music settings.'
                ]);
    
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Delete background music
     */
    public function deleteMusic()
    {
        try {
            $currentMusic = $this->landingPageMusicModel->getActiveMusic();
            if ($currentMusic && !empty($currentMusic['music_file'])) {
                // Delete file
                $file = FCPATH . 'uploads/music/' . $currentMusic['music_file'];
                if (file_exists($file)) {
                    unlink($file);
                }
                
                // Update database
                $this->landingPageMusicModel->update($currentMusic['id'], ['is_active' => 0, 'music_file' => null]);
            }
    
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Background music removed successfully!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    public function saveSpinSound()
    {
        if ($this->request->getMethod() === 'POST') {
            try {
                $data = [
                    'sound_type' => 'spin',
                    'volume' => $this->request->getPost('spin_volume') ?? 0.7,
                    'enabled' => $this->request->getPost('spin_enabled') ? 1 : 0,
                    'is_active' => 1
                ];
    
                // Handle sound file upload
                $soundFile = $this->request->getFile('spin_sound_file');
                if ($soundFile && $soundFile->isValid() && !$soundFile->hasMoved()) {
                    // Validate file type
                    $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg'];
                    if (!in_array($soundFile->getMimeType(), $allowedTypes)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Only MP3, WAV, and OGG files are allowed.'
                        ]);
                    }
    
                    // Max 5MB for sound effects
                    if ($soundFile->getSize() > 5 * 1024 * 1024) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Sound file too large. Maximum size is 5MB.'
                        ]);
                    }
    
                    // Delete old sound file if exists
                    $currentSound = $this->wheelSoundModel->getActiveSound('spin');
                    if ($currentSound && !empty($currentSound['sound_file'])) {
                        $oldFile = FCPATH . 'uploads/sounds/' . $currentSound['sound_file'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
    
                    // Save new file
                    $newName = 'spin_' . time() . '_' . $soundFile->getRandomName();
                    $soundFile->move(FCPATH . 'uploads/sounds', $newName);
                    $data['sound_file'] = $newName;
                } else {
                    // Keep existing file if no new upload
                    $currentSound = $this->wheelSoundModel->getActiveSound('spin');
                    if ($currentSound) {
                        $data['sound_file'] = $currentSound['sound_file'];
                    }
                }
    
                // Save to database
                if ($this->wheelSoundModel->saveSound($data)) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Spinning sound settings saved successfully!'
                    ]);
                }
    
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save sound settings.'
                ]);
    
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Delete spinning sound
     */
    public function deleteSpinSound()
    {
        try {
            $currentSound = $this->wheelSoundModel->getActiveSound('spin');
            if ($currentSound && !empty($currentSound['sound_file'])) {
                // Delete file
                $file = FCPATH . 'uploads/sounds/' . $currentSound['sound_file'];
                if (file_exists($file)) {
                    unlink($file);
                }
                
                // Update database
                $this->wheelSoundModel->update($currentSound['id'], [
                    'is_active' => 0, 
                    'enabled' => 0,
                    'sound_file' => null
                ]);
            }
    
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Spinning sound removed successfully!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Save wheel win sound
     */
    public function saveWinSound()
    {
        if ($this->request->getMethod() === 'POST') {
            try {
                $data = [
                    'sound_type' => 'win',
                    'volume' => $this->request->getPost('win_volume') ?? 0.8,
                    'enabled' => 1,
                    'is_active' => 1
                ];
    
                // Handle sound file upload
                $soundFile = $this->request->getFile('win_sound_file');
                if ($soundFile && $soundFile->isValid() && !$soundFile->hasMoved()) {
                    // Validate file type
                    $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg'];
                    if (!in_array($soundFile->getMimeType(), $allowedTypes)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Only MP3, WAV, and OGG files are allowed.'
                        ]);
                    }
    
                    // Delete old sound file if exists
                    $currentSound = $this->wheelSoundModel->getActiveSound('win');
                    if ($currentSound && !empty($currentSound['sound_file'])) {
                        $oldFile = FCPATH . 'uploads/sounds/' . $currentSound['sound_file'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
    
                    // Save new file
                    $newName = 'win_' . time() . '_' . $soundFile->getRandomName();
                    $soundFile->move(FCPATH . 'uploads/sounds', $newName);
                    $data['sound_file'] = $newName;
                } else {
                    // Keep existing file if no new upload
                    $currentSound = $this->wheelSoundModel->getActiveSound('win');
                    if ($currentSound) {
                        $data['sound_file'] = $currentSound['sound_file'];
                    }
                }
    
                // Save to database
                if ($this->wheelSoundModel->saveSound($data)) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Win sound settings saved successfully!'
                    ]);
                }
    
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save sound settings.'
                ]);
    
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Delete win sound
     */
    public function deleteWinSound()
    {
        try {
            $currentSound = $this->wheelSoundModel->getActiveSound('win');
            if ($currentSound && !empty($currentSound['sound_file'])) {
                // Delete file
                $file = FCPATH . 'uploads/sounds/' . $currentSound['sound_file'];
                if (file_exists($file)) {
                    unlink($file);
                }
                
                // Update database
                $this->wheelSoundModel->update($currentSound['id'], [
                    'is_active' => 0, 
                    'enabled' => 0,
                    'sound_file' => null
                ]);
            }
    
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Win sound removed successfully!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}