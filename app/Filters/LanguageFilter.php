<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class LanguageFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $locale = null;
        
        // Check if user is logged in and has language preference
        if ($session->has('user_id')) {
            $db = \Config\Database::connect();
            $user = $db->table('users')
                       ->select('language_preference')
                       ->where('id', $session->get('user_id'))
                       ->get()
                       ->getRow();
            
            if ($user && $user->language_preference) {
                $locale = $user->language_preference;
            }
        }
        
        // If no user preference, check session
        if (!$locale) {
            $locale = $session->get('locale');
        }
        
        // If still no locale, use default
        if (!$locale) {
            $db = \Config\Database::connect();
            $setting = $db->table('admin_settings')
                          ->where('setting_key', 'default_language')
                          ->get()
                          ->getRow();
            
            $locale = $setting ? $setting->setting_value : 'en';
        }
        
        // Set the locale
        service('request')->setLocale($locale);
        $session->set('locale', $locale);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}