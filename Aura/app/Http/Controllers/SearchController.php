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
                        'avatar' => $u->avatar
                            ? drive_img_url($u->avatar, 120)
                            : asset('img/default-user.png'),
                        'url'    => url('/perfil/' . $u->id),
                    ];
                });

            // === Canciones ===
            $canciones = Cancion::query()
                ->where('title', 'LIKE', "%{$query}%")
                ->select('id', 'title', 'cover_path', 'audio_path', 'user_id')
                ->limit(5)
                ->get()
                ->map(function ($c) {
                    // Portada
                    $cover = $c->cover_path
                        ? drive_img_url($c->cover_path, 120)
                        : asset('img/default-cancion.png');

                    // Audio
                    $audioUrl = null;
                    if ($c->audio_path) {
                        if (Str::contains($c->audio_path, 'drive.google')) {
                            if (preg_match('~/d/([^/]+)~', $c->audio_path, $m)) {
                                $id = $m[1];
                            } elseif (preg_match('~[?&]id=([^&]+)~', $c->audio_path, $m)) {
                                $id = $m[1];
                            } else {
                                $id = null;
                            }
                            $audioUrl = $id ? route('media.drive', ['id' => $id]) : $c->audio_path;
                        } else {
                            $audioUrl = $c->audio_path;
                        }
                    }

                    return [
                        'tipo'   => 'cancion',
                        'id'     => $c->id,
                        'nombre' => $c->title,
                        'avatar' => $cover,
                        'audio'  => $audioUrl,
                        'artist' => $c->user->nombre_artistico ?? $c->user->nombre ?? 'Desconocido',
                        'url'    => url('/cancion/' . $c->id),
                    ];
                });

            // === Álbumes ===
            $albumes = Album::query()
                ->where('title', 'LIKE', "%{$query}%")
                ->select('id', 'title', 'cover_path', 'user_id')
                ->limit(5)
                ->get()
                ->map(function ($a) {
                    $cover = $a->cover_path
                        ? drive_img_url($a->cover_path, 120)
                        : asset('img/default-album.png');

                    return [
                        'tipo'   => 'album',
                        'id'     => $a->id,
                        'nombre' => $a->title,
                        'avatar' => $cover,
                        'url'    => url('/album/' . $a->id),
                    ];
                });

            // === Unir resultados ===
            return response()->json(
                $usuarios->merge($canciones)->merge($albumes)->take(12)->values()
            );

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile()
            ], 500);
        }
    }
}
