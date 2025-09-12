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

        // Asegura que la canción pertenece al usuario logueado
        if ((int)$cancion->user_id !== (int)Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        // ---- Portada ----
        // Soporta: cover_id, cover_url, portada, cover_path (URL o ruta local)
        $this->deleteLocalIfExists($cancion->cover_path ?? null);
        $this->deleteLocalIfExists($cancion->cover_url ?? null);
        $this->deleteLocalIfExists($cancion->portada    ?? null);
        $this->deleteDriveIfPossible($drive, $cancion->cover_id ?? $cancion->cover_url ?? $cancion->portada ?? $cancion->cover_path ?? null);

        // ---- Audio ----
        // Soporta: audio_id, audio_url, audio, audio_path
        $this->deleteLocalIfExists($cancion->audio_path ?? null);
        $this->deleteLocalIfExists($cancion->audio      ?? null);
        $this->deleteLocalIfExists($cancion->audio_url  ?? null);
        $this->deleteDriveIfPossible($drive, $cancion->audio_id ?? $cancion->audio_url ?? $cancion->audio ?? null);

        // Desvincular relaciones si no tienes cascadas
        if (method_exists($cancion, 'likedBy'))   { $cancion->likedBy()->detach(); }
        if (method_exists($cancion, 'playlists')) { $cancion->playlists()->detach(); }

        $cancion->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Canción eliminada correctamente ✅']);
        }
        return redirect()->back()->with('success', 'Canción eliminada correctamente ✅');
    }

    /**
     * ❤️ Alternar like/unlike a una canción
     */
    public function toggleLike(Cancion $cancion)
    {
        $user = Auth::user();

        if ($user->likes()->where('song_id', $cancion->id)->exists()) {
            $user->likes()->detach($cancion->id);
            return response()->json(['liked' => false, 'message' => 'Like eliminado']);
        } else {
            $user->likes()->attach($cancion->id);
            return response()->json(['liked' => true, 'message' => 'Like agregado']);
        }
    }

    /**
     * ❤️ Saber si el usuario ya dio like
     */
    public function liked(Cancion $cancion)
    {
        $liked = Auth::user()->likes()->where('song_id', $cancion->id)->exists();
        return response()->json(['liked' => $liked]);
    }

    /**
     * 🎵 Vista de canciones que el usuario ha marcado con like
     */
    public function like()
    {
        $user = Auth::user();
        $likedSongs = $user->likes()->with('user')->get();
        return view('like', compact('likedSongs'));
    }

    /* ================== Helpers privados ================== */

    /**
     * Borra un archivo local si $path es una ruta válida en Storage.
     * Ignora URLs (http/https).
     */
    private function deleteLocalIfExists(?string $path): void
    {
        if (!$path) return;

        // Si es URL, no es local
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        // Intentar en 'public' y luego en el disco por defecto
        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return;
            }
        } catch (\Throwable $e) { /* noop */ }

        try {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        } catch (\Throwable $e) { /* noop */ }
    }

    /**
     * Si $value es un ID/URL de Drive (o una URL interna con ?id=),
     * intenta borrar en Google Drive.
     */
    private function deleteDriveIfPossible(GoogleDriveOAuthService $drive, $value): void
    {
        $id = $this->extractDriveId($value);
        if (!$id) return;

        if (method_exists($drive, 'delete')) {
            try { $drive->delete($id); } catch (\Throwable $e) { /* noop */ }
        }
    }

    /**
     * Extrae fileId desde:
     *  - un id crudo
     *  - URL Drive: /d/{id} o ?id=...
     *  - URL interna tuya con ?id=...
     */
    private function extractDriveId($value): ?string
    {
        if (!$value) return null;
        $v = trim((string)$value);

        // Id crudo (sin http)
        if (!str_starts_with($v, 'http://') && !str_starts_with($v, 'https://')) {
            return preg_match('/^[A-Za-z0-9_\-]{10,}$/', $v) ? $v : null;
        }

        // ?id=...
        $q = parse_url($v, PHP_URL_QUERY);
        if ($q) {
            parse_str($q, $params);
            if (!empty($params['id']) && preg_match('/^[A-Za-z0-9_\-]{10,}$/', $params['id'])) {
                return $params['id'];
            }
        }

        // /d/{id} o /folders/{id}
        if (preg_match('~/(?:d|folders)/([^/?#]+)~', $v, $m)) {
            return $m[1];
        }

        return null;
    }

public function lyrics(\App\Models\Cancion $cancion)
{
    if ($cancion->lyric) {
        return response()->json([
            'song_id' => $cancion->id,
            'lyrics'  => $cancion->lyric->content,
            'synced'  => (bool) $cancion->lyric->synced,
            'status'  => 'ready',
        ]);
    }

    // Si no hay letra, dispara job
    \App\Jobs\GenerateLyricsJob::dispatch($cancion->id);

    return response()->json([
        'song_id' => $cancion->id,
        'lyrics'  => null,
        'synced'  => false,
        'status'  => 'pending',
    ]);
}

}
