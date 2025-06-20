<?php

if (!function_exists('get_video_type')) {
    /**
     * Get video MIME type from extension
     */
    function get_video_type($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $types = [
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo'
        ];
        
        return $types[$ext] ?? 'video/mp4';
    }
}

if (!function_exists('is_video_file')) {
    /**
     * Check if file is a video
     */
    function is_video_file($filename) {
        $video_extensions = ['mp4', 'webm', 'ogg', 'mov', 'avi'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        return in_array($ext, $video_extensions);
    }
}

if (!function_exists('format_file_size')) {
    /**
     * Format file size in human readable format
     */
    function format_file_size($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        
        return $bytes;
    }
}