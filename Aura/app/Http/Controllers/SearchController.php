<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cancion;
use App\Models\Album;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function buscar(Request $request)
    {
        try {
            $query = trim((string) $request->input('q', ''));

            if ($query === '') {
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
                    $avatar = $u->avatar;

                    if (!$avatar) {
                        $avatarUrl = asset('img/default-user.png');
                    } elseif (Str::startsWith($avatar, ['http://', 'https://'])) {
                        $avatarUrl = $avatar;
                    } else {
                        // Ruta relativa guardada en storage/app/public/...
                        $avatarUrl = asset('storage/' . ltrim($avatar, '/'));
                    }

                    return [
                        'tipo'   => 'usuario',
                        'id'     => $u->id,
                        'nombre' => $u->nombre_artistico ?: $u->nombre,
                        'avatar' => $avatarUrl,
                        'url'    => url('/perfil/' . $u->id),
                    ];
                });

            // === Canciones ===
            $canciones = Cancion::query()
                ->where('title', 'LIKE', "%{$query}%")
                ->orWhere('nombre', 'LIKE', "%{$query}%")
                ->select('id', 'title', 'nombre', 'cover_path', 'audio_path', 'user_id')
                ->with(['user:id,nombre,nombre_artistico'])
                ->limit(5)
                ->get()
                ->map(function ($c) {
                    // Cover
                    if (!empty($c->cover_url)) {
                        $cover = $c->cover_url; // accessor si existe
                    } elseif (!empty($c->cover_path)) {
                        $cover = Str::startsWith($c->cover_path, ['http://', 'https://'])
                            ? $c->cover_path
                            : asset('storage/' . ltrim($c->cover_path, '/'));
                    } else {
                        $cover = asset('img/default-cancion.png');
                    }

                    // Audio
                    if (!empty($c->audio_url)) {
                        $audioUrl = $c->audio_url; // accessor si existe
                    } elseif (!empty($c->audio_path)) {
                        $audioUrl = Str::startsWith($c->audio_path, ['http://', 'https://'])
                            ? $c->audio_path
                            : asset('storage/' . ltrim($c->audio_path, '/'));
                    } else {
                        $audioUrl = null;
                    }

                    return [
                        'tipo'   => 'cancion',
                        'id'     => $c->id,
                        'nombre' => $c->title ?: ($c->nombre ?? 'Sin título'),
                        'avatar' => $cover,
                        'audio'  => $audioUrl,
                        'artist' => optional($c->user)->nombre_artistico
                                    ?: optional($c->user)->nombre
                                    ?: 'Desconocido',
                        'url'    => url('/cancion/' . $c->id),
                    ];
                });

            // === Álbumes ===
            $albumes = Album::query()
                ->where('title', 'LIKE', "%{$query}%")
                ->orWhere('titulo', 'LIKE', "%{$query}%")
                ->select('id', 'title', 'titulo', 'cover_path', 'user_id')
                ->limit(5)
                ->get()
                ->map(function ($a) {
                    // Cover
                    if (!empty($a->cover_url)) {
                        $cover = $a->cover_url; // accessor si existe
                    } elseif (!empty($a->cover_path)) {
                        $cover = Str::startsWith($a->cover_path, ['http://', 'https://'])
                            ? $a->cover_path
                            : asset('storage/' . ltrim($a->cover_path, '/'));
                    } else {
                        $cover = asset('img/default-album.png');
                    }

                    return [
                        'tipo'   => 'album',
                        'id'     => $a->id,
                        'nombre' => $a->title ?: ($a->titulo ?? 'Sin título'),
                        'avatar' => $cover,
                        'url'    => url('/album/' . $a->id),
                    ];
                });

            // === Unir resultados ===
            $results = $usuarios->merge($canciones)->merge($albumes)->take(12)->values();

            return response()->json($results);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile()
            ], 500);
        }
    }
}
