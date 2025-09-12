<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Cancion;
use App\Services\GoogleDriveOAuthService;

class CancionController extends Controller
{
    /**
     * Eliminar una canción (local + Google Drive)
     */
    public function destroy($id, Request $request, GoogleDriveOAuthService $drive)
    {
        $cancion = Cancion::findOrFail($id);

        // Verifica que la canción pertenece al usuario logueado
        if ((int)$cancion->user_id !== (int)Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        // Eliminar portada (si existe) - desde local y Google Drive
        $this->deleteLocalIfExists($cancion->cover_path ?? null);
        $this->deleteLocalIfExists($cancion->cover_url ?? null);
        $this->deleteLocalIfExists($cancion->portada ?? null);
        $this->deleteDriveIfPossible($drive, $cancion->cover_id ?? $cancion->cover_url ?? $cancion->portada ?? $cancion->cover_path ?? null);

        // Eliminar audio (si existe) - desde local y Google Drive
        $this->deleteLocalIfExists($cancion->audio_path ?? null);
        $this->deleteLocalIfExists($cancion->audio ?? null);
        $this->deleteLocalIfExists($cancion->audio_url ?? null);
        $this->deleteDriveIfPossible($drive, $cancion->audio_id ?? $cancion->audio_url ?? $cancion->audio ?? null);

        // Desvincular relaciones de "Me gusta" y "Playlists"
        if (method_exists($cancion, 'likedBy'))   { $cancion->likedBy()->detach(); }
        if (method_exists($cancion, 'playlists')) { $cancion->playlists()->detach(); }

        // Eliminar la canción
        $cancion->delete();

        // Responder con JSON o redirigir
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Canción eliminada correctamente ✅']);
        }
        return redirect()->back()->with('success', 'Canción eliminada correctamente ✅');
    }

    // Métodos auxiliares para eliminar archivos locales y en Google Drive
    private function deleteLocalIfExists(?string $path): void
    {
        if (!$path) return;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Throwable $e) { }

        try {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        } catch (\Throwable $e) { }
    }

    private function deleteDriveIfPossible(GoogleDriveOAuthService $drive, $value): void
    {
        $id = $this->extractDriveId($value);
        if (!$id) return;

        if (method_exists($drive, 'delete')) {
            try { $drive->delete($id); } catch (\Throwable $e) { }
        }
    }

    private function extractDriveId($value): ?string
    {
        if (!$value) return null;
        $v = trim((string)$value);

        if (!str_starts_with($v, 'http://') && !str_starts_with($v, 'https://')) {
            return preg_match('/^[A-Za-z0-9_\-]{10,}$/', $v) ? $v : null;
        }

        $q = parse_url($v, PHP_URL_QUERY);
        if ($q) {
            parse_str($q, $params);
            if (!empty($params['id']) && preg_match('/^[A-Za-z0-9_\-]{10,}$/', $params['id'])) {
                return $params['id'];
            }
        }

        if (preg_match('~/(?:d|folders)/([^/?#]+)~', $v, $m)) {
            return $m[1];
        }

        return null;
    }
}
