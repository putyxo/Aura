<?php

if (!function_exists('drive_img_url')) {
    /**
     * Devuelve una URL optimizada de Google Drive para mostrar imágenes.
     *
     * @param string|null $value   El ID del archivo o la URL completa de Drive.
     * @param int|null    $size    Ancho deseado (si usas thumbnail).
     * @return string|null
     */
    function drive_img_url(?string $value, ?int $size = null): ?string
    {
        if (!$value) return null;

        // Extraer fileId si viene como URL
        if (preg_match('~/d/([^/]+)~', $value, $m)) {
            $id = $m[1];
        } elseif (preg_match('~[?&]id=([^&]+)~', $value, $m)) {
            $id = $m[1];
        } else {
            $id = $value; // ya es fileId
        }

        // Usar thumbnail si se pasa $size
        if ($size) {
            return "https://drive.google.com/thumbnail?id={$id}&sz=w{$size}";
        }

        // Si no hay size → usar export=view
        return "https://drive.google.com/uc?export=view&id={$id}";
    }
}
