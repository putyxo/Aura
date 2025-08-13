<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriveMediaController extends Controller
{
    public function stream(Request $request, string $id)
    {
        // Permite pasar url completa o id "pelado"
        if (preg_match('~/d/([^/]+)~', $id, $m)) $id = $m[1];
        if (preg_match('~[?&]id=([^&]+)~', $id, $m)) $id = $m[1];

        $tokenPath = 'google/token.json';
        if (!Storage::exists($tokenPath)) abort(403, 'No hay token de Google');
        $tokenData = json_decode(Storage::get($tokenPath), true);
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) abort(403, 'Token inválido');

        $range = $request->header('Range');
        $url   = "https://www.googleapis.com/drive/v3/files/{$id}?alt=media";

        $client = new Client(['http_errors' => false, 'stream' => true, 'timeout' => 0]);
        $headers = ['Authorization' => "Bearer {$accessToken}"];
        if ($range) $headers['Range'] = $range;

        $res = $client->request('GET', $url, ['headers' => $headers]);
        $status = $res->getStatusCode();
        if (!in_array($status, [200, 206])) abort($status, 'No se pudo obtener el archivo de Drive');

        $forward = [];
        foreach (['Content-Type','Content-Length','Accept-Ranges','Content-Range','Cache-Control','ETag','Last-Modified'] as $h) {
            if ($res->hasHeader($h)) $forward[$h] = $res->getHeaderLine($h);
        }
        if (!isset($forward['Cache-Control'])) $forward['Cache-Control'] = 'private, max-age=0, no-cache';

        $body = $res->getBody();
        return new StreamedResponse(function () use ($body) {
            while (!$body->eof()) { echo $body->read(8192); flush(); }
        }, $status, $forward);
    }
}