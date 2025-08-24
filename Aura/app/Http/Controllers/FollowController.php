<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FollowController extends Controller
{
    /**
     * Seguir o dejar de seguir a un usuario
     */
    public function toggleFollow($id)
    {
        $authUser = Auth::user();
        $userToFollow = User::findOrFail($id);

        // Evitar seguirse a sí mismo
        if ($authUser->id === $userToFollow->id) {
            return back()->with('error', 'No puedes seguirte a ti mismo ❌');
        }

        // Si ya lo sigue => dejar de seguir
        if ($authUser->isFollowing($id)) {
            $authUser->followings()->detach($id);
            $message = "Has dejado de seguir a {$userToFollow->nombre_artistico}";
        } else {
            // Si no lo sigue => seguir
            $authUser->followings()->attach($id);
            $message = "Ahora sigues a {$userToFollow->nombre_artistico}";
        }

        return back()->with('success', $message);
    }

    /**
     * Mostrar seguidores de un usuario
     */
    public function followers($id)
    {
        $user = User::findOrFail($id);
        $followers = $user->followers()->get();

        return view('followers', compact('user', 'followers'));
    }

    /**
     * Mostrar a quién sigue un usuario
     */
    public function followings($id)
    {
        $user = User::findOrFail($id);
        $followings = $user->followings()->get();

        return view('followings', compact('user', 'followings'));
    }
}
