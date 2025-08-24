<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cancion;
use App\Models\Album;

class SearchController extends Controller
{
    public function buscar(Request $request)
    {
        $query = trim($request->input('q'));

        if (!$query) {
            return response()->json([]);
        }

        // === Usuarios ===
        $usuarios = User::query()
            ->where('nombre', 'LIKE', "%{$query}%")
            ->orWhere('nombre_artistico', 'LIKE', "%{$query}%")
            ->select('id', 'nombre', 'nombre_artistico', 'avatar')
            ->limit(5)
            ->get()
            ->map(function ($u) {
                return [
                    'tipo'   => 'usuario',
                    'id'     => $u->id,
                    'nombre' => $u->nombre_artistico ?? $u->nombre,
                    'avatar' => $u->avatar ? asset($u->avatar) : asset('img/default-user.png'),
                    'url'    => url('/perfil/'.$u->id)
                ];
            });

        // === Canciones ===
        $canciones = Cancion::query()
            ->where('title', 'LIKE', "%{$query}%")
            ->select('id', 'title', 'cover_path')
            ->limit(5)
            ->get()
            ->map(function ($c) {
                return [
                    'tipo'   => 'cancion',
                    'id'     => $c->id,
                    'nombre' => $c->title,
                    'avatar' => $c->cover_path ? asset('storage/'.$c->cover_path) : asset('img/default-song.png'),
                    'url'    => url('/cancion/'.$c->id)
                ];
            });

        // === Álbumes ===
        $albumes = Album::query()
            ->where('title', 'LIKE', "%{$query}%")
            ->select('id', 'title', 'cover_path')
            ->limit(5)
            ->get()
            ->map(function ($a) {
                return [
                    'tipo'   => 'album',
                    'id'     => $a->id,
                    'nombre' => $a->title,
                    'avatar' => $a->cover_path ? asset('storage/'.$a->cover_path) : asset('img/default-album.png'),
                    'url'    => url('/album/'.$a->id)
                ];
            });

        // === Unir resultados ===
        return response()->json(
            $usuarios->merge($canciones)->merge($albumes)->take(10)->values()
        );
    }
}
