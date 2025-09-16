<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Cancion;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadMusicController extends Controller
{
    /**
     * Pantalla principal de subida (single o álbum).
     */
    public function create(Request $request)
    {
        if (!auth()->user()?->es_artista) {
            abort(403, 'Solo artistas pueden subir música.');
        }

        return view('musica.subir');
    }

    /**
     * Guarda una canción individual (single) en almacenamiento LOCAL y BD.
     */
    public function storeSong(Request $request): RedirectResponse
    {
        if (!auth()->user()?->es_artista) {
            abort(403, 'Solo artistas pueden subir música.');
        }

        $data = $request->validate([
            'nombre'    => ['required','string','max:255'],
            'categoria' => ['nullable','string','max:100'],
            'mp3'       => ['required','file','mimetypes:audio/mpeg','max:51200'],      // 50MB
            'portada'   => ['nullable','image','mimes:jpg,jpeg,png,webp','max:10240'], // 10MB
        ]);

        $slug = Str::slug($data['nombre']) . '-' . time();

        // Subir MP3 a storage/app/public/audios
        $mp3 = $request->file('mp3');
        $audioRel = $mp3->storeAs('audios', $slug.'.'.$mp3->getClientOriginalExtension(), 'public');

        // Subir portada opcional a storage/app/public/portadas
        $coverRel = null;
        if ($request->hasFile('portada')) {
            $img = $request->file('portada');
            $coverRel = $img->storeAs('portadas', $slug.'-cover.'.$img->getClientOriginalExtension(), 'public');
        }

        // Guardar en BD (rutas relativas)
        $cancion = Cancion::create([
            'user_id'    => $request->user()->id,
            'title'      => $data['nombre'],
            'genre'      => $data['categoria'] ?? null,
            'audio_path' => $audioRel,
            'cover_path' => $coverRel,
            'status'     => 'published',
        ]);

        // Job opcional (si tu Job acepta el modelo o el ID ajústalo)
        \App\Jobs\GenerateLyricsJob::dispatch($cancion);

        return redirect()
            ->route('busqueda_individual')
            ->with('ok', "Canción subida: {$cancion->title}")
            ->with('audio_url', $audioRel ? asset('storage/'.$audioRel) : null)
            ->with('cover_url', $coverRel ? asset('storage/'.$coverRel) : null);
    }

    /**
     * Guarda un ÁLBUM con múltiples canciones en almacenamiento LOCAL y BD.
     * RUTA sugerida: POST /musica/subir-albums  (name: albums.store)
     */
    public function storeAlbum(Request $request): RedirectResponse
    {
        if (!auth()->user()?->es_artista) {
            abort(403, 'Solo artistas pueden subir música.');
        }

        $data = $request->validate([
            'title'         => ['required','string','max:255'],
            'genre'         => ['nullable','string','max:100'],
            'release_date'  => ['nullable','date'],
            'cover'         => ['nullable','image','mimes:jpg,jpeg,png,webp','max:10240'], // 10MB
            'tracks'        => ['required','array','min:1'],
            'tracks.*'      => ['file','mimetypes:audio/mpeg','max:51200'], // 50MB c/u
            'titles'        => ['nullable','array'],
            'titles.*'      => ['nullable','string','max:255'],
        ]);

        $user = $request->user();
        $slug = Str::slug($data['title']) . '-' . time();

        // Para limpiar archivos si algo falla:
        $storedFiles = [];

        try {
            DB::beginTransaction();

            // 1) Subir portada del álbum (opcional)
            $albumCoverRel = null;
            if ($request->hasFile('cover')) {
                $img = $request->file('cover');
                $albumCoverRel = $img->storeAs('portadas', $slug.'-cover.'.$img->getClientOriginalExtension(), 'public');
                $storedFiles[] = $albumCoverRel;
            }

            // 2) Crear álbum en BD
            $album = Album::create([
                'user_id'      => $user->id,
                'title'        => $data['title'],
                'genre'        => $data['genre'] ?? null,
                'cover_path'   => $albumCoverRel,
                'release_date' => $data['release_date'] ?? null,
            ]);

            // 3) Subir múltiples canciones y ligarlas al álbum
            $titles = $request->input('titles', []);

            foreach ($request->file('tracks') as $i => $mp3) {
                $base     = pathinfo($mp3->getClientOriginalName(), PATHINFO_FILENAME);
                $name     = $titles[$i] ?? $base;
                $nameSlug = Str::slug($name) ?: ('track-'.($i+1));
                $mp3Name  = $slug.'-'.$nameSlug.'.'.$mp3->getClientOriginalExtension();

                // Sube MP3 a storage/app/public/audios
                $audioRel = $mp3->storeAs('audios', $mp3Name, 'public');
                $storedFiles[] = $audioRel;

                // Usa la misma portada del álbum para cada track (si existe)
                $coverRel = $albumCoverRel;

                // Crea canción ligada al álbum (rutas RELATIVAS)
                $cancion = Cancion::create([
                    'user_id'    => $user->id,
                    'album_id'   => $album->id,
                    'title'      => $name,
                    'genre'      => $data['genre'] ?? null,
                    'audio_path' => $audioRel,
                    'cover_path' => $coverRel,
                    'duration'   => null,
                    'status'     => 'published',
                ]);

                // Job opcional (si tu Job acepta el modelo o ID, ajusta)
                \App\Jobs\GenerateLyricsJob::dispatch($cancion);
            }

            DB::commit();

            return redirect()
                ->route('busqueda_album')
                ->with('ok', 'Álbum y canciones subidos correctamente.')
                ->with('cover_url', $albumCoverRel ? asset('storage/'.$albumCoverRel) : null)
                ->with('album_id', $album->id);

        } catch (\Throwable $e) {
            DB::rollBack();

            // Limpieza de archivos subidos si la transacción falla
            foreach ($storedFiles as $rel) {
                try { Storage::disk('public')->delete($rel); } catch (\Throwable $t) {}
            }

            // Vuelve a arrojar para que el handler muestre el error
            throw $e;
        }
    }
}
