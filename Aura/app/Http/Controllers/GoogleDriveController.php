<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GoogleDriveController extends Controller
{
    private function baseClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_DRIVE_REDIRECT'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->addScope(Google_Service_Drive::DRIVE_FILE);
        return $client;
    }

    // Inicia el flujo de autorización
    public function redirectToGoogle()
    {
        $client = $this->baseClient();
        return redirect()->away($client->createAuthUrl());
    }

    // Callback que Google llama después de autorizar
    public function handleCallback(Request $request)
    {
        if (!$request->has('code')) {
            return response('Falta parámetro "code" en el callback', 400);
        }

        $client = $this->baseClient();
        $token = $client->fetchAccessTokenWithAuthCode($request->code);

        if (isset($token['error'])) {
            return response('Error al obtener token: ' . $token['error'], 400);
        }

        $fullPath = storage_path('app/google/token.json');
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($fullPath, json_encode($token, JSON_PRETTY_PRINT));
        
        // Para confirmar la ruta exacta que se usó:
        return response()->json([
            'message' => '✅ Token guardado',
            'path' => $fullPath,
            'exists' => file_exists($fullPath),
            'token' => $token,
        ]);

        // Mostrar el token en pantalla para confirmar
        return response()->json([
            'message' => '✅ Token guardado correctamente en storage/app/google/token.json',
            'token' => $token
        ]);
    }
}
