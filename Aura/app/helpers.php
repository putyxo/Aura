<?php

if (!function_exists('drive_direct_url')) {
    function drive_direct_url($value) {
        if (!$value) return null;

        // /file/d/FILE_ID/
        if (preg_match('~/d/([^/]+)~', $value, $m)) {
            return "https://docs.google.com/uc?export=download&id={$m[1]}";
        }

        // ?id=FILE_ID
        if (preg_match('~[?&]id=([^&]+)~', $value, $m)) {
            return "https://docs.google.com/uc?export=download&id={$m[1]}";
        }

        return $value;
    }
}

