<?php

/**
 * Translation Helper Functions
 */

if (!function_exists('t')) {
    /**
     * Translate a string
     * 
     * @param string $key Translation key
     * @param array $data Data for interpolation
     * @return string
     */
    function t($key, $data = [])
    {
        return lang($key, $data);
    }
}

if (!function_exists('get_current_locale')) {
    /**
     * Get current locale
     * 
     * @return string
     */
    function get_current_locale()
    {
        return service('request')->getLocale();
    }
}

if (!function_exists('is_locale')) {
    /**
     * Check if current locale matches
     * 
     * @param string $locale
     * @return bool
     */
    function is_locale($locale)
    {
        return get_current_locale() === $locale;
    }
}

if (!function_exists('get_language_name')) {
    /**
     * Get language name by locale
     * 
     * @param string|null $locale
     * @return string
     */
    function get_language_name($locale = null)
    {
        if (!$locale) {
            $locale = get_current_locale();
        }
        
        $languages = [
            'en' => 'English',
            'zh' => '简体中文'
        ];
        
        return $languages[$locale] ?? $locale;
    }
}

if (!function_exists('get_supported_languages')) {
    /**
     * Get all supported languages
     * 
     * @return array
     */
    function get_supported_languages()
    {
        return [
            'en' => 'English',
            'zh' => '简体中文'
        ];
    }
}