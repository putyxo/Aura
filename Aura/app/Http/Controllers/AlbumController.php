<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Album;
use App\Models\Cancion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Mostrar el formulario del perfil del usuario.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Álbumes del usuario
        $albumes = Album::where('user_id', $user->id)->get();

        // Normalizar para la vista
        $albumsNormalized = collect($albumes)->map(function ($a) {
            return (object) [
                'id'      => $a->id,
                'titulo'  => $a->title ?? $a->titulo ?? 'Sin título',
                'portada' => $a->cover_path ?? $a->portada ?? null,
            ];
        });

        // 4 álbumes por "página" (para el carrusel / grid)
        $albumPages = $albumsNormalized->chunk(4);

        return view('profile.edit', [
            'user'       => $user,
            'albumPages' => $albumPages,
        ]);
    }

    /**
     * Mostrar los álbumes del usuario (pantalla de menú de álbum).
     */
    public function menuAlbum(Request $request): View
    {
        $user = $request->user();

        $albumes = Album::where('user_id', $user->id)->get();

        // Si tu modelo User tiene relación followers(), úsala; si no, cae en un campo numérico.
        $followersCount = method_exists($user, 'followers')
            ? $user->followers()->count()
            : (int) ($user->seguidores ?? 0);

        return view('menu_album', [
            'user'           => $user,
            'albumes'        => $albumes,
            'followersCount' => $followersCount,
        ]);
    }

    /**
     * Actualizar la información del perfil del usuario.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        // Si el email cambió, reinicia la verificación
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
        // Validar password actual
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // Eliminar usuario
        $user->delete();

        // Invalidar sesión
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Guardar un nuevo álbum (almacenamiento LOCAL).
     */
    public function storeAlbum(Request $request): RedirectResponse
    {
        $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'genre'        => ['nullable', 'string', 'max:255'],
            'release_date' => ['nullable', 'date'],
            'cover'        => ['nullable', 'image', 'max:10240'], // 10MB
        ]);

        $data = $request->only(['title', 'genre', 'release_date']);
        $data['user_id'] = auth()->id();

        // Subir portada local (opcional)
        if ($request->hasFile('cover')) {
            $img      = $request->file('cover');
            $slug     = Str::slug($data['title']) . '-' . time();
            $ext      = $img->getClientOriginalExtension();
            $imgName  = $slug . '-cover.' . $ext;

            // Guarda en storage/app/public/portadas
            $relativePath = $img->storeAs('portadas', $imgName, 'public'); // ej: portadas/mi-album-...-cover.jpg
            $data['cover_path'] = $relativePath;
        }

        Album::create($data);

        return redirect()->route('profile.edit')->with('success', 'Álbum creado correctamente.');
    }

    /**
     * Eliminar un álbum (y sus canciones) con archivos LOCALES.
     */
    public function destroyAlbum($id): RedirectResponse
    {
        $album = Album::findOrFail($id);

        // Verificación de propietario
        if ((int) $album->user_id !== (int) auth()->id()) {
            abort(403, 'Acción no autorizada.');
        }

        // Borrar canciones del álbum (DB + archivos locales)
        $songs = Cancion::where('album_id', $album->id)->get();

        foreach ($songs as $s) {
            // Intenta encontrar rutas relativas en distintos nombres de campo
            $songCoverPath = $s->cover_path ?? $s->portada ?? null;
            $songAudioPath = $s->audio_path ?? $s->audio ?? null;

            $this->deleteLocalIfRelative($songAudioPath);
            $this->deleteLocalIfRelative($songCoverPath);

            $s->delete();
        }

        // Borrar portada del álbum (al final, por si alguna canción la reutilizaba)
        $albumCoverPath = $album->cover_path ?? $album->portada ?? null;
        $this->deleteLocalIfRelative($albumCoverPath);

        // Borrar álbum
        $album->delete();

        return back()->with('success', 'Álbum eliminado correctamente.');
    }

    /**
     * Elimina un archivo del disco 'public' si la ruta es RELATIVA (no URL absoluta).
     */
    private function deleteLocalIfRelative(?string $path): void
    {
        if (!$path) return;

        // Si es URL absoluta http/https, no borrar aquí
        if (preg_match('~^https?://~i', $path)) {
            return;
        }

        // Asegura formato relativo tipo "portadas/archivo.jpg" o "audios/archivo.mp3"
        Storage::disk('public')->delete($path);
    }
}
