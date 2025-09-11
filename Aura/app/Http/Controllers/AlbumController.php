<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Album;
use App\Models\Cancion;
use App\Services\GoogleDriveOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Mostrar el formulario del perfil del usuario.
     */
    public function edit(Request $request): View
    {
        // Obtener el usuario autenticado
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

        // Pasar datos a la vista
        return view('profile.edit', [
            'user' => $user,
            'albumPages' => $albumPages,
        ]);
    }

    /**
     * Mostrar los álbumes del usuario.
     */
    public function menuAlbum(Request $request): View
    {
        // Obtener el usuario autenticado
        $user = $request->user();

        // Obtener los álbumes asociados al usuario
        $albumes = Album::where('user_id', $user->id)->get();

        // Calcular el número de seguidores
        $followersCount = method_exists($user, 'followers') ? $user->followers()->count() : (int)($user->seguidores ?? 0);

        // Pasar datos a la vista
        return view('menu_album', [
            'user' => $user,
            'albumes' => $albumes,
            'followersCount' => $followersCount,
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
        // Validar que el password ingresado sea correcto
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
        $data['user_id'] = auth()->id(); // Asociar el usuario autenticado

        // Subir portada del álbum (opcional) a Google Drive
        if ($request->hasFile('cover')) {
            $img = $request->file('cover');
            $slug = Str::slug($data['title']) . '-' . time();
            $imgName = $slug . '-cover.' . $img->getClientOriginalExtension();
            $imgMime = $img->getMimeType() ?: 'image/jpeg';

            // Usando GoogleDriveOAuthService para subir la imagen
            $cover = $drive->uploadPublic($img->getRealPath(), $imgName, $imgMime);

            // Guardar la URL de la portada y (si existe) el id del archivo
            $data['cover_path'] = $cover['directUrl'] ?? null;
            if (!empty($cover['id'])) {
                $data['cover_id'] = $cover['id']; // <- si tienes esta columna, mejor para borrar luego
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
    {
        $album = Album::findOrFail($id);

        // Verificación de propietario
        if ((int)$album->user_id !== (int)auth()->id()) {
            abort(403, 'Acción no autorizada.');
        }

        // Intentar borrar portada del álbum en Drive
        $this->deleteFromDriveIfPossible($drive, $album->cover_id ?? $album->cover_path ?? null);

        // Borrar canciones del álbum (DB + archivos)
        $songs = Cancion::where('album_id', $album->id)->get();
        foreach ($songs as $s) {
            $this->deleteFromDriveIfPossible($drive, $s->cover_id ?? $s->cover_url ?? $s->portada ?? null);
            $this->deleteFromDriveIfPossible($drive, $s->audio_id ?? $s->audio_url ?? $s->audio ?? null);
            $s->delete();
        }

        // Borrar el álbum
        $album->delete();

        return back()->with('success', 'Álbum eliminado correctamente.');
    }

    /**
     * Borra un archivo en Drive si podemos deducir su fileId
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
            }
        }
    }

    /**
     * Extrae un fileId válido desde una URL o ID crudo.
     */
    private function extractDriveId($value): ?string
    {
        // Lógica para extraer el ID de Drive (si aplica)
        return $value ? Str::after($value, 'drive.com/file/d/') : null;
    }
}
