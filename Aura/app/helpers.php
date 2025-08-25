<?php

if (!function_exists('drive_direct_url')) {
    function drive_direct_url($value) {
        if (!$value) return null;

        // /d/FILE_ID/
        if (preg_match('~/d/([^/]+)~', $value, $m)) {
            return "https://drive.google.com/uc?export=download&id={$m[1]}";
        }

        // ?id=FILE_ID
        if (preg_match('~[?&]id=([^&]+)~', $value, $m)) {
            return "https://drive.google.com/uc?export=download&id={$m[1]}";
        }

        // Si ya es ID puro
        if (preg_match('~^[a-zA-Z0-9_-]{20,}$~', $value)) {
            return "https://drive.google.com/uc?export=download&id={$value}";
        }

        return $value;
    }
}