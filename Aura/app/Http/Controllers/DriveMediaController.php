<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriveMediaController extends Controller
{
    public function stream(Request $request, string $id)
    {
        // Permitir pasar URL completa o solo el ID
        if (preg_match('~/d/([^/]+)~', $id, $m)) {
            $id = $m[1];
        }
        if (preg_match('~[?&]id=([^&]+)~', $id, $m)) {
            $id = $m[1];
        }

        // Construir la URL pública de descarga de Google Drive
        $url = "https://docs.google.com/uc?export=download&id={$id}";

        return new StreamedResponse(function () use ($url) {
            $stream = fopen($url, 'r');
            if ($stream) {
                while (!feof($stream)) {
                    echo fread($stream, 8192);
                    flush();
                }
                fclose($stream);
            }
        }, 200, [
            // Detectar el MIME usando PHP en vez de forzarlo a audio
            "Content-Type" => $this->detectMime($url),
            "Cache-Control" => "public, max-age=86400",
        ]);
    }

    private function detectMime(string $url): string
    {
        $headers = @get_headers($url, 1);
        if ($headers && isset($headers["Content-Type"])) {
            return is_array($headers["Content-Type"]) ? $headers["Content-Type"][0] : $headers["Content-Type"];
        }
        return "application/octet-stream"; // fallback
    }
}
