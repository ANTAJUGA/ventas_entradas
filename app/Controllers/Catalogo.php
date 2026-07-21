<?php

namespace App\Controllers;

use App\Services\CatalogoService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Catalogo extends BaseController
{
    public function index(): string
    {
        return view('public/catalogo/index', ['titulo' => 'Eventos', 'eventos' => (new CatalogoService())->eventos()]);
    }

    public function show(string $slug): string
    {
        $evento = (new CatalogoService())->evento($slug);

        if ($evento === null) {
            throw PageNotFoundException::forPageNotFound('El evento no está disponible.');
        }

        return view('public/catalogo/show', ['titulo' => $evento['nombre'], 'evento' => $evento]);
    }
}
