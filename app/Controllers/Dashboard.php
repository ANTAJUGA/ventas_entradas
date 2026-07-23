<?php

namespace App\Controllers;

use App\Services\DashboardService;

class Dashboard extends BaseController
{
    public function index(): mixed
    {

        $usuario = session('usuario');
        if (($usuario['rol_slug'] ?? '') === 'vendedor') {
            return redirect()->to(base_url('admin/ventas/nueva'));
        }
        if (($usuario['rol_slug'] ?? '') === 'control-acceso') {
            return redirect()->to(base_url('admin/accesos'));
        }
        $dashboard = new DashboardService();

        return view('dashboard/index', [
            'titulo' => 'Panel administrativo',
            'paginaActiva' => 'dashboard',
            'usuario' => [
                'nombre' => $usuario['nombre'],
                'rol' => $usuario['rol'],
            ],
            'fechaActual' => $dashboard->fechaActual(),
            'resumen' => $dashboard->resumen(),
            'eventos' => $dashboard->proximasFunciones(),
        ]);
    }
    public function pruebaDesarrollo()
    {

        return $this->response->setJSON([

            'estado' => 'ok',

            'mensaje' => 'La rama desarrollo funciona correctamente',

        ]);

    }


}
