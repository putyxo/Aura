<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserEqualizer;

class EqualizerController extends Controller
{

    public function preferencias()
{
    $eq = auth()->user()->equalizer; // puede ser null la primera vez
    return view('preferencias', compact('eq'));
}


    public function save(Request $request)
    {
        $data = $request->validate([
            'preamp' => 'required|numeric',
            'eq'     => 'required|array',
            'eq.*'   => 'numeric',
        ]);

        $eq = $data['eq'];

        auth()->user()->equalizer()->updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'preamp'    => $data['preamp'],
                'band_60'   => $eq[60] ?? 0,
                'band_170'  => $eq[170] ?? 0,
                'band_310'  => $eq[310] ?? 0,
                'band_600'  => $eq[600] ?? 0,
                'band_1000' => $eq[1000] ?? 0,
                'band_3000' => $eq[3000] ?? 0,
                'band_6000' => $eq[6000] ?? 0,
                'band_12000'=> $eq[12000] ?? 0,
                'band_14000'=> $eq[14000] ?? 0,
                'band_16000'=> $eq[16000] ?? 0,
            ]
        );

        return back()->with('ok', 'Ecualizador guardado ✅');
    }
}
