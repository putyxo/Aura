<?php

namespace App\Services;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_Permission;

class GoogleDriveOAuthService
{
    private function client(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_DRIVE_REDIRECT'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->addScope(Google_Service_Drive::DRIVE_FILE); // acceso a archivos creados por la app

        // cargar token guardado
        $tokenPath = storage_path('app/google/token.json');
        if (!file_exists($tokenPath)) {
            throw new \RuntimeException('No existe token.json. Ve a /google-drive/auth y autoriza la app.');
        }
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);

        // refrescar si expiró
        if ($client->isAccessTokenExpired()) {
            if (!empty($accessToken['refresh_token'])) {
                $client->fetchAccessTokenWithRefreshToken($accessToken['refresh_token']);
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            } else {
                throw new \RuntimeException('El token expiró y no hay refresh_token. Vuelve a autorizar en /google-drive/auth');
            }
        }

        return $client;
    }

    public function drive(): Google_Service_Drive
    {
        return new Google_Service_Drive($this->client());
    }

    /**
     * Sube un archivo y lo hace accesible por enlace.
     * @return array{id:string, directUrl:string}
     */
    public function uploadPublic(string $localPath, string $name, string $mime, ?string $parentId = null): array
    {
        $service = $this->drive();

        $fileMeta = new Google_Service_Drive_DriveFile([
            'name'    => $name,
            'parents' => $parentId ? [$parentId] : null,
        ]);

        $file = $service->files->create($fileMeta, [
            'data'       => file_get_contents($localPath),
            'mimeType'   => $mime,
            'uploadType' => 'multipart',
            'fields'     => 'id',
        ]);

        // Hacerlo público por enlace
        $perm = new Google_Service_Drive_Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);
        $service->permissions->create($file->id, $perm);

        return [
            'id'        => $file->id,
            'directUrl' => "https://drive.google.com/uc?export=download&id={$file->id}",
        ];
    }
}
