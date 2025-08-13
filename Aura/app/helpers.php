<?php

if (!function_exists('google_image_url')) {
    function google_image_url($url, $mode = 'view')
    {
        if (!$url) return null;

        // Drive - formato uc?export
        if (preg_match('~drive\.google\.com/uc\?~', $url)) {
            return preg_replace('~([?&])export=[^&]*~', '$1export=' . $mode, $url);
        }

        // Drive - /file/d/ID
        if (preg_match('~/d/([^/]+)~', $url, $m)) {
            return "https://drive.google.com/uc?export={$mode}&id={$m[1]}";
        }

        // Drive - ?id=ID
        if (preg_match('~[?&]id=([^&]+)~', $url, $m)) {
            return "https://drive.google.com/uc?export={$mode}&id={$m[1]}";
        }

        // ID puro
        if (!str_contains($url, '://')) {
            return "https://drive.google.com/uc?export={$mode}&id={$url}";
        }

        // Google Photos
        if (str_contains($url, 'googleusercontent.com') && !str_contains($url, '=w')) {
            return $url . '=w1000';
        }

        return $url;
    }
}
