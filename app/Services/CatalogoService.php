<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class CatalogoService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= db_connect();
    }

    /** @return array<int, array<string, mixed>> */
    public function eventos(): array
    {
        $eventos = $this->db->table('eventos')
            ->select('eventos.*, MIN(funciones.fecha_inicio) AS proxima_funcion, MIN(tipos_entrada.precio) AS precio_desde, COUNT(DISTINCT funciones.id) AS total_funciones')
            ->join('funciones', 'funciones.evento_id = eventos.id AND funciones.deleted_at IS NULL')
            ->join('tipos_entrada', 'tipos_entrada.funcion_id = funciones.id AND tipos_entrada.deleted_at IS NULL AND tipos_entrada.activo = 1')
            ->where('eventos.estado', 'publicado')
            ->where('eventos.deleted_at', null)
            ->where('funciones.fecha_inicio >=', date('Y-m-d H:i:s'))
            ->whereNotIn('funciones.estado', ['cancelada', 'finalizada'])
            ->groupBy('eventos.id')
            ->orderBy('proxima_funcion', 'ASC')
            ->get()->getResultArray();

        foreach ($eventos as &$evento) {
            $evento['imagen_url'] = $this->imageUrl($evento['imagen']);
        }
        unset($evento);

        return $eventos;
    }

    /** @return array<string, mixed>|null */
    public function evento(string $slug): ?array
    {
        $evento = $this->db->table('eventos')->where(['slug' => $slug, 'estado' => 'publicado', 'deleted_at' => null])->get()->getRowArray();

        if ($evento === null) {
            return null;
        }

        $funciones = $this->db->table('funciones')
            ->where('evento_id', $evento['id'])->where('deleted_at', null)
            ->where('fecha_inicio >=', date('Y-m-d H:i:s'))
            ->whereNotIn('estado', ['cancelada', 'finalizada'])
            ->orderBy('fecha_inicio', 'ASC')->get()->getResultArray();

        foreach ($funciones as &$funcion) {
            $now = time();
            $funcion['venta_disponible'] = strtotime($funcion['fecha_inicio']) > $now
                && (! $funcion['venta_inicio'] || strtotime($funcion['venta_inicio']) <= $now)
                && (! $funcion['venta_fin'] || strtotime($funcion['venta_fin']) >= $now);
            $tipos = $this->db->table('tipos_entrada')
                ->select("tipos_entrada.*, COUNT(DISTINCT CASE WHEN ventas.estado = 'pagada' AND entradas.estado IN ('vigente','usada') THEN entradas.id END) AS vendidos", false)
                ->join('detalle_ventas', 'detalle_ventas.tipo_entrada_id = tipos_entrada.id', 'left')
                ->join('ventas', 'ventas.id = detalle_ventas.venta_id AND ventas.deleted_at IS NULL', 'left')
                ->join('entradas', 'entradas.detalle_venta_id = detalle_ventas.id', 'left')
                ->where('tipos_entrada.funcion_id', $funcion['id'])->where('tipos_entrada.activo', 1)->where('tipos_entrada.deleted_at', null)
                ->groupBy('tipos_entrada.id')->orderBy('tipos_entrada.precio', 'ASC')->get()->getResultArray();

            foreach ($tipos as &$tipo) {
                $pendientes = $this->db->table('detalle_ventas dv')->selectSum('dv.cantidad', 'total')
                    ->join('ventas v', 'v.id=dv.venta_id')->where('dv.tipo_entrada_id', $tipo['id'])
                    ->where('v.estado', 'pendiente')->where('v.metodo_pago', 'pago-en-ventanilla')
                    ->where('v.deleted_at', null)->get()->getRowArray();
                $tipo['reservados'] = (int)($pendientes['total'] ?? 0);
                $tipo['disponibles'] = max(0, (int) $tipo['cupo'] - (int) $tipo['vendidos'] - $tipo['reservados']);
            }
            unset($tipo);
            $funcion['tipos'] = $tipos;
        }
        unset($funcion);
        $evento['funciones'] = $funciones;
        $evento['imagen_url'] = $this->imageUrl($evento['imagen']);

        return $evento;
    }

    private function imageUrl(?string $image): ?string
    {
        $image = trim((string) $image);

        if ($image === '') {
            return null;
        }

        if (filter_var($image, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $image)) {
            return $image;
        }

        $image = preg_replace('#^(?:public/|/)+#', '', str_replace('\\', '/', $image));

        return base_url($image);
    }
}
