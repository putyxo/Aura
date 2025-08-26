<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoogleDriveController extends Controller
{
    private function baseClient(): \Google_Client
    {
        $client = new \Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->addScope(\Google_Service_Drive::DRIVE_FILE);

        return $client;
    }

    public function redirectToGoogle()
    {
        $client = $this->baseClient();
        return redirect()->away($client->createAuthUrl());
    }

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

        // Guardamos el token en storage/app/google/token.json
        $path = storage_path('app/google/token.json');
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, json_encode($token, JSON_PRETTY_PRINT));

        return response()->json([
            'message' => '✅ Token guardado correctamente',
            'path' => $path,
            'token' => $token
        ]);
    }
}
