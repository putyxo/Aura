<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Cancion;
use App\Services\GoogleDriveOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Guarda una canción individual (single) en Google Drive y BD.
     */
    public function storeSong(Request $request, GoogleDriveOAuthService $drive)
    {
        if (!auth()->user()?->es_artista) {
            abort(403, 'Solo artistas pueden subir música.');
        }

        $data = $request->validate([
            'nombre'    => ['required','string','max:255'],
            'categoria' => ['nullable','string','max:100'],
            'mp3'       => ['required','file','mimes:mp3','max:102400'],              // 100MB
            'portada'   => ['nullable','image','mimes:jpg,jpeg,png,webp','max:10240'] // 10MB
        ]);

        $folderId = env('GOOGLE_DRIVE_UPLOAD_FOLDER_ID'); // null => Mi unidad
        $slug     = Str::slug($data['nombre']).'-'.time();

        // Subir MP3 a Drive
        $mp3     = $request->file('mp3');
        $mp3Name = $slug.'.'.$mp3->getClientOriginalExtension();
        $audio   = $drive->uploadPublic($mp3->getRealPath(), $mp3Name, 'audio/mpeg', $folderId);

        // Subir portada opcional a Drive
        $coverUrl = null;
        if ($request->hasFile('portada')) {
            $img     = $request->file('portada');
            $imgName = $slug.'-cover.'.$img->getClientOriginalExtension();
            $imgMime = $img->getMimeType() ?: 'image/jpeg';
            $cover   = $drive->uploadPublic($img->getRealPath(), $imgName, $imgMime, $folderId);
            $coverUrl = $cover['directUrl'];
        }

        // Guardar en BD
        $cancion = Cancion::create([
            'user_id'    => $request->user()->id,
            'title'      => $data['nombre'],
            'genre'      => $data['categoria'] ?? null,
            'audio_path' => $audio['directUrl'],
            'cover_path' => $coverUrl,
            'status'     => 'published',
        ]);

        return redirect()
            ->route('musica.subir')
            ->with('ok', "Canción subida: {$cancion->title}")
            ->with('audio_url', $audio['directUrl'])
            ->with('cover_url', $coverUrl);
    }

    /**
     * Guarda un ALBUM con múltiples canciones en Google Drive y BD.
     * RUTA: POST /musica/subir-albums  (name: albums.store)
     */
    public function storeAlbum(Request $request, GoogleDriveOAuthService $drive)
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
            'tracks.*'      => ['file','mimes:mp3','max:102400'], // 100MB c/u
            'titles'        => ['nullable','array'],
            'titles.*'      => ['nullable','string','max:255'],
        ]);

        $user     = $request->user();
        $folderId = env('GOOGLE_DRIVE_UPLOAD_FOLDER_ID');
        $slug     = Str::slug($data['title']).'-'.time();

        return DB::transaction(function () use ($request, $drive, $user, $data, $folderId, $slug) {
            // 1) Subir portada del álbum (opcional) a Drive
            $albumCoverUrl = null;
            if ($request->hasFile('cover')) {
                $img     = $request->file('cover');
                $imgName = $slug.'-cover.'.$img->getClientOriginalExtension();
                $imgMime = $img->getMimeType() ?: 'image/jpeg';
                $cover   = $drive->uploadPublic($img->getRealPath(), $imgName, $imgMime, $folderId);
                $albumCoverUrl = $cover['directUrl'];
            }

            // 2) Crear álbum en BD
            $album = Album::create([
                'user_id'      => $user->id,
                'title'        => $data['title'],
                'genre'        => $data['genre'] ?? null,
                'cover_path'   => $albumCoverUrl,
                'release_date' => $data['release_date'] ?? null,
            ]);

            // 3) Subir múltiples canciones a Drive y ligarlas al álbum
            $titles = $request->input('titles', []);
            foreach ($request->file('tracks') as $i => $mp3) {
                $base     = pathinfo($mp3->getClientOriginalName(), PATHINFO_FILENAME);
                $name     = $titles[$i] ?? $base;
                $nameSlug = Str::slug($name) ?: ('track-'.($i+1));
                $mp3Name  = $slug.'-'.$nameSlug.'.'.$mp3->getClientOriginalExtension();

                // Sube MP3 a Drive
                $audio    = $drive->uploadPublic($mp3->getRealPath(), $mp3Name, 'audio/mpeg', $folderId);
                $audioUrl = $audio['directUrl'];

                // Crea canción ligada al álbum (heredando portada del álbum para la pista)
                Cancion::create([
                    'user_id'    => $user->id,
                    'album_id'   => $album->id,
                    'title'      => $name,
                    'genre'      => $data['genre'] ?? null, // hereda el género del álbum (si se envió)
                    'audio_path' => $audioUrl,
                    'cover_path' => $albumCoverUrl,         // hereda portada del álbum
                    'duration'   => null,                   // calcula luego con job si quieres
                    'status'     => 'published',
                ]);
            }

            return redirect()
                ->route('musica.subir')
                ->with('ok', 'Álbum y canciones subidos correctamente.')
                ->with('cover_url', $albumCoverUrl)
                ->with('album_id', $album->id);
        });
    }
}
