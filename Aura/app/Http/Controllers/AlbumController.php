<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AlbumController extends Controller
{
    /**
     * Mostrar detalle de un álbum (misma UI visual para dueño y visitantes).
     * - Dueño: verá controles de edición (la vista los muestra con $isOwner).
     * - Visitante/no autenticado: solo lectura.
     */
    public function show(int $id): View
    {
        $album = Album::with(['user', 'songs.user'])->findOrFail($id);

        // Otros álbumes del mismo artista para la columna izquierda
        $albumes = Album::where('user_id', $album->user_id)
            ->orderByDesc('created_at')
            ->get();

        // Reutiliza la vista visual "menu_album"
        return view('menu_album', [
            'user'    => $album->user,
            'album'   => $album,     // Álbum seleccionado (evita "No hay álbum seleccionado")
            'albumes' => $albumes,   // Para "Otros álbumes"
        ]);
    }

    /**
     * Actualiza nombre y/o portada del álbum.
     * Acepta:
     *  - title: string
     *  - cover: image
     */
    public function update(Request $request, int $id)
    {
        $album = Album::findOrFail($id);

        // Solo el dueño puede editar
        if ((int)$album->user_id !== (int)Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'cover' => ['nullable', 'image', 'max:10240'], // 10MB
        ]);

        $changed = [];

        if (array_key_exists('title', $validated)) {
            $album->title = $validated['title'];
            $changed['title'] = $album->title;
        }

        if ($request->hasFile('cover')) {
            $img  = $request->file('cover');
            $slug = Str::slug($album->title ?: ($album->titulo ?? 'album')) . '-' . time();
            $ext  = $img->getClientOriginalExtension();
            // Guarda en storage/app/public/albums/covers
            $path = $img->storeAs('albums/covers', $slug . '.' . $ext, 'public');

            // Borra la portada anterior si era ruta local
            $old = $album->cover_path ?? $album->portada ?? null;
            if ($old && !preg_match('~^https?://~i', $old)) {
                Storage::disk('public')->delete(ltrim(preg_replace('#^/?public/#', '', $old), '/'));
            }

            $album->cover_path = $path;
            $changed['cover_path'] = $path;
        }

        $album->save();

        // Respuesta compatible con el JS (acepta cover_path en raíz o en data.cover_path)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok'         => true,
                'cover_path' => $changed['cover_path'] ?? ($album->cover_path ?? null),
                'data'       => [
                    'id'         => $album->id,
                    'title'      => $album->title,
                    'cover_path' => $changed['cover_path'] ?? ($album->cover_path ?? null),
                ],
                // También devolvemos "album" y "changed" por si tu UI los usa
                'album'      => $album->only(['id','title','cover_path']),
                'changed'    => $changed,
            ]);
        }

        return back()->with('ok', 'Álbum actualizado correctamente.');
    }

    /**
     * Eliminar un álbum (y sus canciones) + archivos locales.
     */
    public function destroy($album, Request $request)
    {
        $model = $album instanceof Album ? $album : Album::findOrFail($album);

        if ((int) $model->user_id !== (int) Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        $model->load('songs');

        foreach ($model->songs as $song) {
            $songCoverPath = $song->cover_path ?? $song->portada ?? null;
            $songAudioPath = $song->audio_path ?? $song->audio ?? null;

            $this->deleteLocalIfRelative($songAudioPath);
            $this->deleteLocalIfRelative($songCoverPath);

            if (method_exists($song, 'likedBy')) {
                $song->likedBy()->detach();
            }
            if (method_exists($song, 'playlists')) {
                $song->playlists()->detach();
            }

            $song->delete();
        }

        $albumCoverPath = $model->cover_path ?? $model->portada ?? null;
        $this->deleteLocalIfRelative($albumCoverPath);

        $model->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Álbum eliminado correctamente ✅']);
        }

        return back()->with('success', 'Álbum eliminado correctamente ✅');
    }

    private function deleteLocalIfRelative(?string $path): void
    {
        if (!$path) return;
        if (preg_match('~^https?://~i', $path)) return;

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return;
            }
        } catch (\Throwable $e) {}

        try {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        } catch (\Throwable $e) {}
    }
}
