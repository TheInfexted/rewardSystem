<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class TranslateController extends BaseController
{
    protected $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function index($lang = 'en')
    {
        $supportedLanguages = $this->getSupportedLanguages();
    
        if (!in_array($lang, $supportedLanguages)) {
            return $this->response->setJSON([
                'code' => 0,
                'message' => 'Unsupported language'
            ]);
        }
    
        // Set in session
        session()->set('locale', $lang);
        session()->markAsTempdata('locale', 3600); // optional, avoid early garbage collection
        session()->close(); // <-- ensures session write before AJAX returns
    
        // Set in cookie
        helper('cookie');
        set_cookie('lang', $lang, YEAR);
    
        // Persist to DB if user is logged in
        if (session()->has('user_id')) {
            $this->db->table('users')
                     ->where('id', session()->get('user_id'))
                     ->update(['language_preference' => $lang]);
        }
    
        // Optional: also call service('request')->setLocale($lang); — this works only for current request
        service('request')->setLocale($lang);
    
        return $this->response->setJSON([
            'code' => 1,
            'message' => 'Language changed successfully',
            'locale' => $lang
        ]);
    }
    
    public function getCurrentLanguage()
    {
        $locale = session()->get('locale') ?? $this->getDefaultLanguage();
        
        return $this->response->setJSON([
            'locale' => $locale,
            'language_name' => $locale === 'zh' ? '简体中文' : 'English'
        ]);
    }
    
    /**
     * Get supported languages from admin settings
     */
    private function getSupportedLanguages()
    {
        $setting = $this->db->table('admin_settings')
                           ->where('setting_key', 'supported_languages')
                           ->get()
                           ->getRow();
        
        if ($setting && $setting->setting_value) {
            return explode(',', $setting->setting_value);
        }
        
        return ['en', 'zh']; // Default fallback
    }
    
    /**
     * Get default language from admin settings
     */
    private function getDefaultLanguage()
    {
        $setting = $this->db->table('admin_settings')
                           ->where('setting_key', 'default_language')
                           ->get()
                           ->getRow();
        
        return $setting ? $setting->setting_value : 'en';
    }
}