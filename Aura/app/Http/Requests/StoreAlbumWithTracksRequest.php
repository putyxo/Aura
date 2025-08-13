<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlbumWithTracksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (bool) auth()->user()->es_artista;
    }

    public function rules(): array
    {
        return [
            'title'           => ['required','string','max:255'],
            'genre'           => ['nullable','string','max:255'],
            'release_date'    => ['nullable','date'],
            'cover'           => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],

            // múltiples canciones
            'tracks'          => ['required','array','min:1'],
            'tracks.*'        => ['file','mimetypes:audio/mpeg','max:51200'], // 50MB c/u (ajusta)
            // títulos opcionales por pista (mismo orden que files)
            'titles'          => ['nullable','array'],
            'titles.*'        => ['nullable','string','max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'  => 'El título del álbum es obligatorio.',
            'tracks.required' => 'Debes subir al menos una canción.',
            'tracks.*.mimetypes' => 'Cada pista debe ser un MP3.',
        ];
    }
}