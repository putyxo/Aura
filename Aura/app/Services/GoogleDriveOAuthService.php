<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;

class GoogleDriveOAuthService
{
    public function drive(): Drive
    {
        return env('GOOGLE_AUTH_MODE', 'sa') === 'oauth'
            ? $this->driveOAuth()
            : $this->driveSa();
    }

    /** ---- Service Account ---- */
    protected function driveSa(): Drive
    {
        $kp = env('GOOGLE_SA_KEY_PATH', 'storage/app/google-sa.json');

        $isAbs = function (string $p): bool {
            $p = str_replace('\\','/',$p);
            return str_starts_with($p, '/') || preg_match('/^[A-Za-z]:\//', $p) === 1;
        };
        $norm = fn(string $p) => rtrim(str_replace('\\','/',$p), '/');

        $candidates = [];
        if ($isAbs($kp)) $candidates[] = $norm($kp);
        $candidates[] = $norm(base_path($kp));
        $kpNorm = $norm($kp);
        if (str_starts_with($kpNorm, 'storage/')) {
            $inside = ltrim(substr($kpNorm, strlen('storage/')), '/');
            $candidates[] = $norm(storage_path($inside));
        }
        $candidates[] = $norm(storage_path('app/google-sa.json'));

        $fullPath = null;
        foreach (array_unique($candidates) as $p) {
            if (is_file($p)) { $fullPath = $p; break; }
        }
        if (!$fullPath) {
            throw new \RuntimeException("SA_JSON_NOT_FOUND: ajusta GOOGLE_SA_KEY_PATH. Buscado:\n- ".implode("\n- ", $candidates));
        }

        $client = new Client();
        $client->setAuthConfig($fullPath);
        $client->setScopes([Drive::DRIVE]);
        return new Drive($client);
    }

    /** ---- OAuth de usuario ---- */
    protected function driveOAuth(): Drive
    {
        $client = new Client();

        // ✅ usar credenciales desde .env
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_DRIVE_REDIRECT'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([Drive::DRIVE]);

        $tokenPath = storage_path('app/google/token.json');

        if (!file_exists($tokenPath)) {
            // todavía no hay token: este client se usa solo para createAuthUrl()
            return $client;
        }

        $accessToken = json_decode(file_get_contents($tokenPath), true) ?: [];
        $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            if (!empty($accessToken['refresh_token'])) {
                $client->fetchAccessTokenWithRefreshToken($accessToken['refresh_token']);
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            } else {
                throw new \RuntimeException(
                    'El token expiró y no hay refresh_token. Borra token.json y vuelve a /google-drive/auth'
                );
            }
        }

        return new Drive($client);
    }

    /** ---- Subida pública ---- */
    public function uploadPublic(string $localPath, string $name, string $mime, ?string $parentId = null): array
    {
        $service = $this->drive();

        $fileMeta = new DriveFile([
            'name'    => $name,
            'parents' => $parentId ? [$parentId] : null,
        ]);

        $file = $service->files->create($fileMeta, [
            'data'       => file_get_contents($localPath),
            'mimeType'   => $mime,
            'uploadType' => 'multipart',
            'fields'     => 'id',
        ]);

        $perm = new Permission([
            'type' => 'anyone',
            'role' => 'reader'
        ]);
        $service->permissions->create($file->id, $perm);

        return [
            'id'        => $file->id,
            'directUrl' => "https://drive.google.com/uc?export=download&id={$file->id}",
        ];
    }
}
