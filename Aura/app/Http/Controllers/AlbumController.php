<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    /**
     * Listar todos los álbumes
     */
    public function index()
    {
        $albums = Album::with('songs')->latest()->get();

        return view('albums.index', compact('albums'));
    }

    /**
     * Mostrar un álbum en específico
     */
    public function show($id)
    {
        $album = Album::with('songs')->findOrFail($id);

        return view('menu_album', compact('album'));
    }

    /**
     * Formulario para crear álbum
     */
    public function create()
    {
        return view('albums.create');
    }

    /**
     * Guardar un nuevo álbum
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'genre'         => 'nullable|string|max:255',
            'release_date'  => 'nullable|date',
            'cover'         => 'nullable|image|max:10240', // 10MB
        ]);

        $data = $request->only(['title','genre','release_date']);
        $data['user_id'] = auth()->id();

        // Guardar portada si se subió
        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('covers', 'public');
            $data['cover_path'] = $path;
        }

        $album = Album::create($data);

        return redirect()->route('album.show', $album->id)
                         ->with('success', 'Álbum creado correctamente.');
    }

    /**
     * Formulario de edición de álbum
     */
    public function edit($id)
    {
        $album = Album::findOrFail($id);

        return view('albums.edit', compact('album'));
    }

    /**
     * Actualizar un álbum existente
     */
    public function update(Request $request, $id)
    {
        $album = Album::findOrFail($id);

        $request->validate([
            'title'         => 'required|string|max:255',
            'genre'         => 'nullable|string|max:255',
            'release_date'  => 'nullable|date',
            'cover'         => 'nullable|image|max:10240',
        ]);

        $data = $request->only(['title','genre','release_date']);

        if ($request->hasFile('cover')) {
            // eliminar portada previa
            if ($album->cover_path && Storage::disk('public')->exists($album->cover_path)) {
                Storage::disk('public')->delete($album->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $album->update($data);

        return redirect()->route('album.show', $album->id)
                         ->with('success', 'Álbum actualizado correctamente.');
    }

    /**
     * Eliminar un álbum
     */
    public function destroy($id)
    {
        $album = Album::findOrFail($id);

        // borrar portada si existe
        if ($album->cover_path && Storage::disk('public')->exists($album->cover_path)) {
            Storage::disk('public')->delete($album->cover_path);
        }

        $album->delete();

        return redirect()->route('albums.index')
                         ->with('success', 'Álbum eliminado correctamente.');
    }
}
