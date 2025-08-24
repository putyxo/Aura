<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriveMediaController extends Controller
{
    public function stream(Request $request, string $id)
    {
        // Permite pasar url completa o solo el id
        if (preg_match('~/d/([^/]+)~', $id, $m)) {
            $id = $m[1];
        }
        if (preg_match('~[?&]id=([^&]+)~', $id, $m)) {
            $id = $m[1];
        }

        // Construir la URL pública de descarga de Google Drive
        $url = "https://docs.google.com/uc?export=download&id={$id}";

        // Stream del archivo desde Drive hacia el navegador
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
            "Content-Type" => "audio/mpeg",
            "Cache-Control" => "no-cache, must-revalidate",
            "Pragma" => "no-cache"
        ]);
    }
}
