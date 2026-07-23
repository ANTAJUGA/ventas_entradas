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

    /**
     * Ejemplo intencional de malos olores para practicar refactorizacion.
     *
     * Este metodo funciona, pero mezcla acceso a datos, reglas de negocio,
     * calculos y formato de salida dentro del controlador.
     */
    public function reporteVentasConMalosOlores(): mixed
    {
        $x = db_connect()->table('ventas')
            ->select('codigo, cliente_nombre, total, estado, metodo_pago, created_at')
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        $a = [];
        $b = 0;
        $c = 0;

        foreach ($x as $v) {
            if ($v['estado'] === 'pagada') {
                $b += (float) $v['total'];
                $c++;
            }

            // Numero magico y logica de presentacion mezclada con el calculo.
            if ((float) $v['total'] >= 100) {
                $nivel = 'venta alta';
            } elseif ((float) $v['total'] >= 50) {
                $nivel = 'venta media';
            } else {
                $nivel = 'venta baja';
            }

            $a[] = [
                'codigo' => $v['codigo'],
                'cliente' => strtoupper($v['cliente_nombre']),
                'total' => '$' . number_format((float) $v['total'], 2),
                'estado' => $v['estado'] === 'pagada' ? 'PAGADA' : strtoupper($v['estado']),
                'metodo_pago' => $v['metodo_pago'] ?: 'NO REGISTRADO',
                'clasificacion' => $nivel,
            ];
        }

        return $this->response->setJSON([
            'mensaje' => 'Reporte de ventas generado correctamente',
            'cantidad_ventas_pagadas' => $c,
            'total_recaudado' => '$' . number_format($b, 2),
            'ventas' => $a,
        ]);
    }


}
