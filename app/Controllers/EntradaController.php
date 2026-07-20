<?php

namespace App\Controllers;

class EntradaController extends BaseController
{
    public function index(): string
    {
        return view('vista_entrada');
    }
}
