<?php

namespace App\Http\Controllers;

class MiControlador extends Controller
{
    public function mostrarVista()
    {
        return view('menu'); // resources/views/busqueda_album.blade.php
    }

    public function mostrarVistaIndividual()
    {
        return view('menu');

    }
}
