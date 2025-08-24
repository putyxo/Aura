<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PlaylistController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required','string','max:120'],
            'descripcion' => ['nullable','string','max:1000'],
            'portada'     => ['nullable','image','max:5120'], // 5MB
        ]);

        $coverUrl = null;
        if ($request->hasFile('portada')) {
            $path = $request->file('portada')->store('portadas', 'public');
            $coverUrl = Storage::url($path); // /storage/portadas/...
        }

        // Si ya tienes el modelo Playlist:
        if (class_exists(\App\Models\Playlist::class)) {
            Playlist::create([
                'user_id'     => Auth::id(),
                'nombre'      => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'cover_url'   => $coverUrl,
            ]);
        }

        return back()->with([
            'ok'        => 'Playlist creada correctamente.',
            'cover_url' => $coverUrl,
        ]);
    }
}
