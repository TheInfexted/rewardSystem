<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class MediaController extends BaseController
{
    /**
     * Display media library
     */
    public function index()
    {
        $uploadPath = FCPATH . 'uploads/landing/';
        $contentPath = $uploadPath . 'content/';
        
        $images = [];
        
        // Get header/footer images
        if (is_dir($uploadPath)) {
            $files = scandir($uploadPath);
            foreach ($files as $file) {
                if (is_file($uploadPath . $file) && $this->isImage($file)) {
                    $images[] = [
                        'name' => $file,
                        'path' => 'uploads/landing/' . $file,
                        'size' => $this->formatBytes(filesize($uploadPath . $file)),
                        'type' => 'header/footer',
                        'date' => date('M d, Y', filemtime($uploadPath . $file))
                    ];
                }
            }
        }
        
        // Get content images
        if (is_dir($contentPath)) {
            $files = scandir($contentPath);
            foreach ($files as $file) {
                if (is_file($contentPath . $file) && $this->isImage($file)) {
                    $images[] = [
                        'name' => $file,
                        'path' => 'uploads/landing/content/' . $file,
                        'size' => $this->formatBytes(filesize($contentPath . $file)),
                        'type' => 'content',
                        'date' => date('M d, Y', filemtime($contentPath . $file))
                    ];
                }
            }
        }
        
        $data = [
            'title' => 'Media Library',
            'images' => $images,
            'totalSize' => $this->getTotalSize($images)
        ];
        
        return view('admin/media/index', $data);
    }
    
    /**
     * Upload new image
     */
    public function upload()
    {
        $rules = [
            'image' => 'uploaded[image]|max_size[image,4096]|is_image[image]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Invalid file upload.');
        }
        
        $file = $this->request->getFile('image');
        $type = $this->request->getPost('type') ?? 'content';
        
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $path = ($type === 'content') ? 'uploads/landing/content' : 'uploads/landing';
            
            $file->move(FCPATH . $path, $newName);
            
            return redirect()->back()->with('success', 'Image uploaded successfully.');
        }
        
        return redirect()->back()->with('error', 'Failed to upload image.');
    }
    
    /**
     * Delete image
     */
    public function delete($filename)
    {
        $filename = urldecode($filename);
        $paths = [
            FCPATH . 'uploads/landing/' . $filename,
            FCPATH . 'uploads/landing/content/' . $filename
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                if (unlink($path)) {
                    return redirect()->back()->with('success', 'Image deleted successfully.');
                }
            }
        }
        
        return redirect()->back()->with('error', 'Failed to delete image.');
    }
    
    /**
     * Check if file is an image
     */
    private function isImage($filename)
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowedExtensions);
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Get total size of all images
     */
    private function getTotalSize($images)
    {
        $total = 0;
        foreach ($images as $image) {
            // Parse size back to bytes for calculation
            $size = $image['size'];
            if (strpos($size, 'KB') !== false) {
                $total += floatval($size) * 1024;
            } elseif (strpos($size, 'MB') !== false) {
                $total += floatval($size) * 1024 * 1024;
            } else {
                $total += floatval($size);
            }
        }
        return $this->formatBytes($total);
    }
}