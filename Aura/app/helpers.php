<?php
declare(strict_types=1);

use Illuminate\Support\Str;

/**
 * Normaliza una ruta guardada en BD y devuelve su URL pública.
 * - Si es http/https → la devuelve tal cual.
 * - Si es una ruta absoluta del storage (incluyendo Windows) → la convierte a /storage/...
 * - Si es relativa (p.ej. "songs/foo.mp3" o "covers/portada.jpg") → /storage/<ruta>
 * - Si $fallbackAsset está definido y $path es vacío → asset($fallbackAsset).
 */
if (!function_exists('public_media_url')) {
    function public_media_url(?string $path, ?string $fallbackAsset = null): string
    {
        if (!$path || trim($path) === '') {
            return $fallbackAsset ? asset($fallbackAsset) : '';
        }

        $path = trim($path);

        // Si ya es URL absoluta (CDN, otro host, etc.)
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        // Normaliza separadores Windows → Unix
        $path = str_replace('\\', '/', $path);

        // Si nos pasaron un path absoluto dentro del proyecto que apunta al storage/app/public
        // recortamos hasta quedar con la parte relativa dentro de "public"
        $marker = 'storage/app/public/';
        $ipos   = stripos($path, $marker);
        if ($ipos !== false) {
            $path = substr($path, $ipos + strlen($marker));
        }

        // Quita prefijos "public/" o "/public/"
        $path = preg_replace('#^/?public/#i', '', $path);

        // Quita "storage/" al inicio si ya viene, para no duplicarlo
        $path = ltrim(preg_replace('#^/?storage/#i', '', $path), '/');

        // Construye URL pública (requiere php artisan storage:link)
        return asset('storage/' . $path);
    }
}

/**
 * URL para imágenes locales o externas.
 * $fallbackAsset: asset() a usar si $path viene vacío (ej: 'img/default-cancion.png').
 */
if (!function_exists('img_url')) {
    function img_url(?string $path, ?string $fallbackAsset = 'img/default-cancion.png'): string
    {
        return public_media_url($path, $fallbackAsset);
    }
}

/**
 * URL para audios locales o externos.
 */
if (!function_exists('audio_url')) {
    function audio_url(?string $path): string
    {
        return public_media_url($path);
    }
}
