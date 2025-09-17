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
     * Mostrar detalle de un álbum (portada + canciones).
     */
    public function show(int $id): View
    {
<<<<<<< HEAD
        $user = $request->user();

        // Obtener los álbumes asociados al usuario
        $albumes = Album::where('user_id', $user->id)->get();

        // Normalizar los álbumes para la vista
        $albumsNormalized = collect($albumes)->map(function($a) {
            return (object)[
                'id'      => $a->id,
                'titulo'  => $a->title ?? $a->titulo ?? 'Sin título',
                'portada' => $a->cover_path ?? $a->portada ?? null,
            ];
        });

        // Paginación de los álbumes (4 álbumes por página)
        $albumPages = $albumsNormalized->chunk(4);

        return view('profile.edit', [
            'user' => $user,
            'albumPages' => $albumPages,
        ]);
=======
        $album = Album::with(['user', 'songs.user'])->findOrFail($id);
        return view('albums.show', compact('album'));
>>>>>>> recup-ayer
    }

    /**
     * Actualiza nombre y/o portada del álbum.
     * Acepta:
     *  - title: string
     *  - cover: image
     */
<<<<<<< HEAD
    public function menuAlbum(Request $request): View
    {
        $user = $request->user();

        // Obtener los álbumes asociados al usuario
        $albumes = Album::where('user_id', $user->id)->get();

        // Calcular el número de seguidores
        $followersCount = method_exists($user, 'followers') ? $user->followers()->count() : (int)($user->seguidores ?? 0);

        return view('menu_album', [
            'user' => $user,
            'albumes' => $albumes,
            'followersCount' => $followersCount,
        ]);
    }

    /**
     * Mostrar un álbum específico dentro de `menu_album`.
     */
    public function show($id): View
{
    // Retrieve the album by its ID and eager load the user relationship
    $album = Album::with('user')->findOrFail($id);
    $user = $album->user; // User who created the album

    // Retrieve the liked songs for the authenticated user (if applicable)
    $likedSongs = Auth::user()->likedSongs; // Assuming 'likedSongs' is a relationship or method in the User model

    // Retrieve all albums of the user to display in the pagination
    $albumes = Album::where('user_id', $user->id)->get();
    $albumPages = $albumes->chunk(4);  // Chunk albums for pagination

    // Pass the album, user, liked songs, and album pages to the view
    return view('menu_album', [
        'user' => $user,
        'albumes' => $albumes,
        'followersCount' => method_exists($user, 'followers') ? $user->followers()->count() : (int)($user->seguidores ?? 0),
        'albumPages' => $albumPages,
        'selectedAlbum' => $album,  // Pass the selected album
        'likedSongs' => $likedSongs // Pass the liked songs
    ]);
}


    /**
     * Actualizar la información del perfil del usuario.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        // Si el email ha cambiado, eliminar la verificación de email
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Eliminar la cuenta del usuario.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Desconectar al usuario
        Auth::logout();

        // Eliminar al usuario de la base de datos
        $user->delete();

        // Invalidar la sesión
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Guardar un nuevo álbum.
     */
    public function storeAlbum(Request $request, GoogleDriveOAuthService $drive)
    {
        // Validar los datos del álbum
        $request->validate([
            'title'         => 'required|string|max:255',
            'genre'         => 'nullable|string|max:255',
            'release_date'  => 'nullable|date',
            'cover'         => 'nullable|image|max:10240', // 10MB
        ]);

        $data = $request->only(['title', 'genre', 'release_date']);
        $data['user_id'] = auth()->id();

        // Subir portada del álbum (opcional) a Google Drive
        if ($request->hasFile('cover')) {
            $img = $request->file('cover');
            $slug = Str::slug($data['title']) . '-' . time();
            $imgName = $slug . '-cover.' . $img->getClientOriginalExtension();
            $imgMime = $img->getMimeType() ?: 'image/jpeg';

            // Usando GoogleDriveOAuthService para subir la imagen
            $cover = $drive->uploadPublic($img->getRealPath(), $imgName, $imgMime);

            $data['cover_path'] = $cover['directUrl'] ?? null;
            if (!empty($cover['id'])) {
                $data['cover_id'] = $cover['id'];
            }
        }

        // Guardar el álbum en la base de datos
        $album = Album::create($data);

        return redirect()->route('profile.edit')->with('success', 'Álbum creado correctamente.');
    }

    /**
     * Eliminar un álbum (y sus canciones).
     */
    public function destroyAlbum($id, GoogleDriveOAuthService $drive)
=======
    public function update(Request $request, int $id)
>>>>>>> recup-ayer
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
            $path = $img->storeAs('portadas', $slug . '.' . $ext, 'public');

<<<<<<< HEAD
        return back()->with('success', 'Álbum eliminado correctamente.');
    }

    /**
     * Borra un archivo en Drive si podemos deducir su fileId.
     */
    private function deleteFromDriveIfPossible(GoogleDriveOAuthService $drive, $value): void
    {
        $fileId = $this->extractDriveId($value);
        if (!$fileId) return;

        if (method_exists($drive, 'delete')) {
            try {
                $drive->delete($fileId);
            } catch (\Throwable $e) {
                // No bloquear la eliminación por errores de Drive
=======
            // Borra la portada anterior si era ruta local
            $old = $album->cover_path ?? $album->portada ?? null;
            if ($old && !preg_match('~^https?://~i', $old)) {
                Storage::disk('public')->delete(ltrim(preg_replace('#^/?public/#', '', $old), '/'));
>>>>>>> recup-ayer
            }

            $album->cover_path = $path;
            $changed['cover_path'] = $path;
        }

        $album->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok'      => true,
                'album'   => $album->only(['id','title','cover_path']),
                'changed' => $changed,
            ]);
        }

        return back()->with('ok', 'Álbum actualizado correctamente.');
    }

    /**
     * Eliminar un álbum (y sus canciones) + archivos locales.
     * (Si no usas esta acción, tu ruta ya apunta a ProfileController@destroyAlbum)
     */
    public function destroy($album, Request $request)
    {
<<<<<<< HEAD
        return $value ? Str::after($value, 'drive.com/file/d/') : null;
=======
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
>>>>>>> recup-ayer
    }
}
