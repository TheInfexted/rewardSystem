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
     * @param string $default Default value if translation not found
     * @return string
     */
    function t($key, $data = [], $default = null)
    {
        $translation = lang($key, $data);
        
        // If translation key not found, use default or the key itself
        if ($translation === $key && $default !== null) {
            return $default;
        }
        
        return $translation;
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
        return session()->get('locale') ?? config('App')->defaultLocale ?? 'en';
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

if (!function_exists('format_date')) {
    /**
     * Format date based on locale
     * 
     * @param string $date
     * @param string|null $format
     * @return string
     */
    function format_date($date, $format = null)
    {
        if (empty($date)) {
            return '';
        }
        
        $locale = get_current_locale();
        
        if (!$format) {
            $format = $locale === 'zh' ? 'Y年m月d日' : 'M d, Y';
        }
        
        return date($format, strtotime($date));
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format datetime based on locale
     * 
     * @param string $datetime
     * @param string|null $format
     * @return string
     */
    function format_datetime($datetime, $format = null)
    {
        if (empty($datetime)) {
            return '';
        }
        
        $locale = get_current_locale();
        
        if (!$format) {
            if ($locale === 'zh') {
                $format = 'Y年m月d日 H:i';
            } else {
                $format = 'F d, Y h:i A';
            }
        }
        
        return date($format, strtotime($datetime));
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency based on locale
     * 
     * @param float $amount
     * @return string
     */
    function format_currency($amount)
    {
        $locale = get_current_locale();
        
        if ($locale === 'zh') {
            return '¥' . number_format($amount, 2);
        } else {
            return '$' . number_format($amount, 2);
        }
    }
}

if (!function_exists('format_number')) {
    /**
     * Format number based on locale
     * 
     * @param float $number
     * @param int $decimals
     * @return string
     */
    function format_number($number, $decimals = 0)
    {
        $locale = get_current_locale();
        
        if ($locale === 'zh') {
            return number_format($number, $decimals, '.', ',');
        } else {
            return number_format($number, $decimals);
        }
    }
}