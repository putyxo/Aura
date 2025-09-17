<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateLyricsJob;
use App\Models\Album;
use App\Models\Cancion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\GoogleDriveOAuthService; // Asegúrate de importar correctamente el servicio

class CancionController extends Controller
{
    public function adminIndex()
    {
        // Solo admins
        if (!checkAdminAccess()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Acceso restringido.']);
        }

        // Traer canciones con su usuario
        $canciones = Cancion::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.admin', compact('canciones'));
    }

    /**
     * Eliminar una canción (archivos locales + relaciones).
     */
    public function destroy(Cancion $cancion, Request $request, GoogleDriveOAuthService $drive)
    {
        // Verifica si el usuario es el propietario de la canción
        if ((int) $cancion->user_id !== (int) Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        // Elimina los archivos locales
        $songCoverPath = $cancion->cover_path ?? $cancion->portada ?? null;
        $songAudioPath = $cancion->audio_path ?? $cancion->audio ?? null;

        $this->deleteLocalIfRelative($songAudioPath);
        $this->deleteLocalIfRelative($songCoverPath);

        // Elimina las relaciones (likes, playlists)
        if (method_exists($cancion, 'likedBy')) {
            $cancion->likedBy()->detach();
        }
        if (method_exists($cancion, 'playlists')) {
            $cancion->playlists()->detach();
        }

        // Elimina la canción
        $cancion->delete();

        // Respuesta JSON si es una solicitud AJAX
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Canción eliminada correctamente ✅']);
        }

        return redirect()->back()->with('success', 'Canción eliminada correctamente ✅');
    }

    /**
     * Actualiza campos de la canción (título/portada).
     */
    public function update(Request $request, int $cancion)
    {
        $song = Cancion::findOrFail($cancion);

        // Verifica que el usuario sea el propietario
        $ownerId = $song->user_id ?? optional(Album::find($song->album_id))->user_id;
        if ((int) $ownerId !== (int) Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        $validated = $request->validate([
            'title'            => ['nullable', 'string', 'max:255'],
            'cover'            => ['nullable', 'image', 'max:8192'],
            'cover_from_album' => ['nullable', 'in:1'],
            'cover_path'       => ['nullable', 'string', 'max:255'],
        ]);

        $changed = [];

        // Actualiza el título si es necesario
        if (array_key_exists('title', $validated)) {
            $song->title = $validated['title'];
            $changed['title'] = $song->title;
        }

        // 1) Copiar ruta desde el álbum si se especifica cover_from_album
        if (!empty($validated['cover_from_album']) && !empty($validated['cover_path'])) {
            $song->cover_path = ltrim($validated['cover_path'], '/');
            $changed['cover_path'] = $song->cover_path;
        }
        // 2) O subir archivo directo
        elseif ($request->hasFile('cover')) {
            $img  = $request->file('cover');
            $slug = \Str::slug($song->title ?: 'cancion') . '-' . time();
            $ext  = $img->getClientOriginalExtension();
            $path = $img->storeAs('songs/covers', $slug . '.' . $ext, 'public');

            // Elimina la imagen anterior si existe
            $old = $song->cover_path ?? $song->portada ?? null;
            if ($old && !preg_match('~^https?://~i', $old)) {
                Storage::disk('public')->delete(ltrim(preg_replace('#^/?public/#', '', $old), '/'));
            }

            $song->cover_path = $path;
            $changed['cover_path'] = $path;
        }

        $song->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok'      => true,
                'song'    => $song->only(['id', 'title', 'cover_path']),
                'changed' => $changed,
            ]);
        }

        return back()->with('ok', 'Canción actualizada correctamente.');
    }

    /**
     * ❤️ Alternar like/unlike a una canción.
     */
    public function toggleLike(Cancion $cancion)
    {
        $user = Auth::user();
        $yaLeDioLike = $cancion->likedBy()->where('users.id', $user->id)->exists();

        if ($yaLeDioLike) {
            $cancion->likedBy()->detach($user->id);
            return response()->json(['liked' => false, 'message' => 'Like eliminado']);
        } else {
            $cancion->likedBy()->attach($user->id);
            return response()->json(['liked' => true, 'message' => 'Like agregado']);
        }
    }

    /**
     * Mostrar las canciones que un usuario ha marcado como "Me gusta".
     */
    public function like()
    {
        $user = Auth::user();

        $likedSongs = Cancion::with('user')
            ->whereHas('likedBy', fn ($q) => $q->where('users.id', $user->id))
            ->get();

        return view('like', compact('likedSongs'));
    }

    public function generateLyrics(Cancion $cancion)
    {
        if ((int) $cancion->user_id !== (int) Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        // Inicia el trabajo para generar las letras
        $cancion->update([
            'lyrics'        => null,
            'lyrics_status' => 'pending',
            'lyrics_error'  => null,
        ]);

        GenerateLyricsJob::dispatch($cancion->id)->onQueue('default');

        return response()->json([
            'ok'      => true,
            'message' => 'Generación de letras iniciada.',
        ]);
    }

    private function deleteLocalIfRelative(?string $path): void
    {
        if (!$path) return;
        if (preg_match('~^https?://~i', $path)) return;

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Throwable $e) {}
    }
}
