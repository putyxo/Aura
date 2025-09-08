<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Cancion;
use App\Services\GoogleDriveOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    /**
     * Guardar un nuevo álbum
     */
    public function store(Request $request, GoogleDriveOAuthService $drive)
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

        return redirect()->route('album.show', $album->id)
                         ->with('success', 'Álbum creado correctamente.');
    }

    /**
     * Mostrar un álbum específico
     */
    public function show($id)
    {
        // Obtener el álbum por su ID
        $album = Album::findOrFail($id);

        // Pasar el álbum a la vista
        return view('menu_album', compact('album'));
    }

    /**
     * Eliminar un álbum (y sus canciones). Ruta: DELETE /album/{id}
     */
    public function destroy($id, GoogleDriveOAuthService $drive)
    {
        $album = Album::findOrFail($id);

        // Verificación de propietario
        if ((int)$album->user_id !== (int)auth()->id()) {
            abort(403, 'Acción no autorizada.');
        }

        // 1) Intentar borrar portada del álbum en Drive
        $this->deleteFromDriveIfPossible($drive, $album->cover_id ?? $album->cover_path ?? null);

        // 2) Borrar canciones del álbum (DB + archivos)
        $songs = Cancion::where('album_id', $album->id)->get();
        foreach ($songs as $s) {
            $this->deleteFromDriveIfPossible($drive, $s->cover_id ?? $s->cover_url ?? $s->portada ?? null);
            $this->deleteFromDriveIfPossible($drive, $s->audio_id ?? $s->audio_url ?? $s->audio ?? null);
            $s->delete();
        }

        // 3) Borrar el álbum
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
     * Extrae un fileId válido desde:
     *  - un id "crudo"
     *  - una URL de Drive ( /d/{id} o ?id=... )
     *  - una URL propia con ?id=...
     */
    private function extractDriveId($value): ?string
    {
        if (!$value) return null;
        $v = trim((string)$value);

        // Si ya parece un ID crudo
        if (strpos($v, 'http') !== 0) {
            return preg_match('/^[A-Za-z0-9_\-]{20,}$/', $v) ? $v : null;
        }

        // Si es URL y trae ?id=...
        $q = parse_url($v, PHP_URL_QUERY);
        if ($q) {
            parse_str($q, $p);
            if (!empty($p['id']) && preg_match('/^[A-Za-z0-9_\-]{10,}$/', $p['id'])) {
                return $p['id'];
            }
        }

        // Si es URL tipo /d/{id}
        if (preg_match('~/(?:d|folders)/([^/?#]+)~', $v, $m)) {
            return $m[1];
        }

        return null;
    }
}
