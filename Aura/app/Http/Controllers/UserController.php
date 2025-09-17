<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function destroy(User $user)
{
    try {
        $user->delete();
        return response()->json(['success' => true]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error'   => $e->getMessage()
        ], 500);
    }
}

}
