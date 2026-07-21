<?php

namespace App\Controllers;

class MiCuenta extends BaseController
{
    public function entradas(): string
    {
        $usuario = session('usuario');
        $entradas = db_connect()->table('entradas')
            ->select('entradas.*, ventas.codigo AS venta_codigo, eventos.nombre AS evento_nombre, funciones.fecha_inicio, funciones.recinto, tipos_entrada.nombre AS tipo_nombre')
            ->join('detalle_ventas', 'detalle_ventas.id = entradas.detalle_venta_id')
            ->join('ventas', 'ventas.id = detalle_ventas.venta_id')
            ->join('tipos_entrada', 'tipos_entrada.id = detalle_ventas.tipo_entrada_id')
            ->join('funciones', 'funciones.id = tipos_entrada.funcion_id')
            ->join('eventos', 'eventos.id = funciones.evento_id')
            ->where('ventas.cliente_id', $usuario['id'])->where('ventas.estado', 'pagada')->where('ventas.deleted_at', null)
            ->orderBy('funciones.fecha_inicio', 'DESC')->get()->getResultArray();

        $reservas = db_connect()->table('ventas')
            ->select('ventas.*, detalle_ventas.cantidad, tipos_entrada.nombre tipo_nombre, eventos.nombre evento_nombre, funciones.fecha_inicio, funciones.recinto')
            ->join('detalle_ventas', 'detalle_ventas.venta_id=ventas.id')
            ->join('tipos_entrada', 'tipos_entrada.id=detalle_ventas.tipo_entrada_id')
            ->join('funciones', 'funciones.id=tipos_entrada.funcion_id')->join('eventos', 'eventos.id=funciones.evento_id')
            ->where('ventas.cliente_id', $usuario['id'])->where('ventas.estado', 'pendiente')
            ->where('ventas.metodo_pago', 'pago-en-ventanilla')->where('ventas.deleted_at', null)
            ->orderBy('ventas.created_at', 'DESC')->get()->getResultArray();

        return view('public/cuenta/entradas', ['titulo' => 'Mis entradas', 'entradas' => $entradas, 'reservas' => $reservas, 'usuario' => $usuario]);
    }
}
