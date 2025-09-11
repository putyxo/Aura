<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    // Constructor removido: no usamos $this->middleware() aquí

    // Alterna like y vuelve atrás (para formularios HTML)
    public function toggle(Cancion $cancion): RedirectResponse
    {
        $user = auth()->user();

        $liked = $user->likedSongs()->whereKey($cancion->getKey())->exists();
        if ($liked) {
            $user->likedSongs()->detach($cancion->getKey());
            return back()->with('ok', 'Quitado de Me gusta');
        }

        $user->likedSongs()->attach($cancion->getKey());
        return back()->with('ok', 'Añadido a Me gusta');
    }
}
