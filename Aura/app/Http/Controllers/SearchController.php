<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cancion;
use App\Models\Album;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    /**
     * GET /search ó /buscar ?q=...
     * Devuelve hasta 12 resultados mezclados (usuarios, canciones, álbumes).
     */
    public function buscar(Request $request)
    {
        try {
            $query = trim((string) $request->query('q', ''));

            if ($query === '' || mb_strlen($query) < 2) {
                return response()->json([]);
            }

            // =======================
            // USUARIOS
            // =======================
            $usersSelect = ['id', 'nombre', 'avatar'];
            if (Schema::hasColumn('users', 'nombre_artistico')) {
                $usersSelect[] = 'nombre_artistico';
            }

            $usuarios = User::query()
                ->where(function ($q) use ($query) {
                    $q->where('nombre', 'LIKE', "%{$query}%");
                    if (Schema::hasColumn('users', 'nombre_artistico')) {
                        $q->orWhere('nombre_artistico', 'LIKE', "%{$query}%");
                    }
                })
                ->select($usersSelect)
                ->limit(5)
                ->get()
                ->map(function (User $u) {
                    // Usa accessor avatar_url del modelo User
                    $display = $u->nombre_artistico ?? $u->nombre;

                    return [
                        'tipo'    => 'usuario',
                        'id'      => $u->id,
                        'nombre'  => $display ?: 'Usuario',
                        'avatar'  => $u->avatar_url, // accessor
                        'url'     => url('/perfil/' . $u->id),
                        'detalle' => 'Perfil',
                    ];
                });

            // =======================
            // CANCIONES
            // =======================
            $songSelect = ['id', 'title', 'cover_path', 'audio_path', 'user_id', 'album_id'];

            // columnas del autor (users) de forma segura
            $authorCols = ['id', 'nombre'];
            if (Schema::hasColumn('users', 'nombre_artistico')) {
                $authorCols[] = 'nombre_artistico';
            }

            $canciones = Cancion::query()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%");
                    if (Schema::hasColumn('songs', 'nombre')) {
                        $q->orWhere('nombre', 'LIKE', "%{$query}%");
                    }
                })
                ->with(['user' => function ($q) use ($authorCols) {
                    $q->select($authorCols);
                }])
                ->select($songSelect)
                ->limit(5)
                ->get()
                ->map(function (Cancion $c) {
                    $artist = optional($c->user)->nombre_artistico
                              ?? optional($c->user)->nombre
                              ?? 'Desconocido';

                    // Si la columna 'nombre' no existe, getAttribute('nombre') devuelve null sin romper
                    $displayTitle = $c->title ?: ($c->getAttribute('nombre') ?? 'Sin título');

                    return [
                        'tipo'    => 'cancion',
                        'id'      => $c->id,
                        'nombre'  => $displayTitle,
                        'avatar'  => $c->cover_url,  // accessor del modelo Cancion
                        'audio'   => $c->audio_url,  // accessor del modelo Cancion
                        'artist'  => $artist,
                        'url'     => url('/cancion/' . $c->id),
                        'detalle' => 'Canción · ' . $artist,
                    ];
                });

            // =======================
            // ÁLBUMES
            // =======================
            $albumSelect = ['id', 'title', 'cover_path', 'user_id'];
            if (Schema::hasColumn('albums', 'titulo')) {
                $albumSelect[] = 'titulo';
            }

            $albumes = Album::query()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%");
                    if (Schema::hasColumn('albums', 'titulo')) {
                        $q->orWhere('titulo', 'LIKE', "%{$query}%");
                    }
                })
                ->with(['user' => function ($q) use ($authorCols) {
                    $q->select($authorCols);
                }])
                ->select($albumSelect)
                ->limit(5)
                ->get()
                ->map(function (Album $a) {
                    $artist = optional($a->user)->nombre_artistico
                              ?? optional($a->user)->nombre
                              ?? 'Desconocido';

                    $display = $a->title ?: ($a->getAttribute('titulo') ?? 'Sin título');

                    return [
                        'tipo'    => 'album',
                        'id'      => $a->id,
                        'nombre'  => $display,
                        'avatar'  => $a->cover_url,  // accessor del modelo Album
                        'artist'  => $artist,
                        'url'     => url('/album/' . $a->id),
                        'detalle' => 'Álbum · ' . $artist,
                    ];
                });

            // =======================
            // Mezclar y responder
            // =======================
            $results = $usuarios->merge($canciones)->merge($albumes)->take(12)->values();

            return response()->json($results);

        } catch (\Throwable $e) {
            // Log útil para depurar en storage/logs/laravel.log
            \Log::error('SearchController.buscar error', [
                'q'     => $request->query('q'),
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'error'   => 'Search failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
