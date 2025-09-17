<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreferenciasController extends Controller
{
    public function index()
    {
        $eq = auth()->user()->equalizer; // puede ser null
        return view('preferencias', compact('eq'));
    }
}
