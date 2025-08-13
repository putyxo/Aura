<?php

namespace App\Http\Controllers;

class MiControlador extends Controller
{
    public function mostrarVista()
    {
        return view('busqueda_album'); // resources/views/busqueda_album.blade.php
    }

    public function mostrarVistaIndividual()
    {
        return view('busqueda_individual');

    }
}
