<?php

if (!function_exists('format_date')) {
    /**
     * Format a date string using a given format.
     *
     * @param string|null $date
     * @param string $format
     * @return string
     */
    function format_date($date, $format = null) {
        $locale = get_current_locale();
        
        if (!$format) {
            $format = $locale === 'zh' ? 'Y年m月d日' : 'M d, Y';
        }
        
        return date($format, strtotime($date));
    }
}
