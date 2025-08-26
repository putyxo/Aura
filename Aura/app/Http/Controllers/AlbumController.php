<?php

namespace App\Http\Controllers;

use App\Models\Album;
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

            // Guardar la URL de la portada
            $data['cover_path'] = $cover['directUrl'];  // Guardar la URL de la portada
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
}
