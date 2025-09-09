<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TraductorController extends Controller
{
    public function traducir(Request $request)
    {
        $textos = $request->input('textos', []);
        $target = $request->input('lang', 'en');

        $tr = new GoogleTranslate($target);
        $tr->setSource('auto'); // 🔥 corrección

        $traducciones = [];
        foreach ($textos as $t) {
            $traducciones[] = $tr->translate($t);
        }

        return response()->json($traducciones);
    }
}
