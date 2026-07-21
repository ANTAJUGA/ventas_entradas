<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class DashboardService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /** @return array<int, array<string, int|string>> */
    public function resumen(): array
    {
        $eventosActivos = $this->eventosActivos();
        $proximasSemana = $this->funcionesProximaSemana();
        $entradasVendidas = $this->entradasVendidas();
        $aforo = $this->aforoProgramado();
        $ingresos = $this->ingresosConfirmados();

        $porcentajeDisponible = $aforo['total'] > 0
            ? (int) round((($aforo['total'] - $aforo['ocupado']) / $aforo['total']) * 100)
            : 0;

        return [
            [
                'etiqueta' => 'Eventos activos',
                'valor' => $eventosActivos,
                'detalle' => $proximasSemana . ' función(es) en los próximos 7 días',
                'tono' => 'violet',
            ],
            [
                'etiqueta' => 'Entradas vendidas',
                'valor' => $entradasVendidas['total'],
                'detalle' => $this->detalleComparacionVentas($entradasVendidas['actual'], $entradasVendidas['anterior']),
                'tono' => 'blue',
            ],
            [
                'etiqueta' => 'Aforo disponible',
                'valor' => $porcentajeDisponible . ' %',
                'detalle' => max(0, $aforo['total'] - $aforo['ocupado']) . ' de ' . $aforo['total'] . ' lugares',
                'tono' => 'green',
            ],
            [
                'etiqueta' => 'Ingresos',
                'valor' => '$' . number_format($ingresos, 2, ',', '.'),
                'detalle' => 'Ventas pagadas y confirmadas',
                'tono' => 'orange',
            ],
        ];
    }

    /** @return array<int, array<string, int|string>> */
    public function proximasFunciones(int $limit = 5): array
    {
        $rows = $this->db->table('funciones')
            ->select("funciones.id, funciones.nombre AS funcion_nombre, funciones.fecha_inicio, funciones.venta_inicio, funciones.venta_fin, funciones.aforo_total, funciones.estado, eventos.nombre, eventos.estado AS evento_estado, COUNT(DISTINCT CASE WHEN ventas.estado = 'pagada' AND entradas.estado IN ('vigente', 'usada') THEN entradas.id END) AS vendidas", false)
            ->join('eventos', 'eventos.id = funciones.evento_id')
            ->join('tipos_entrada', 'tipos_entrada.funcion_id = funciones.id AND tipos_entrada.deleted_at IS NULL', 'left')
            ->join('detalle_ventas', 'detalle_ventas.tipo_entrada_id = tipos_entrada.id', 'left')
            ->join('ventas', 'ventas.id = detalle_ventas.venta_id AND ventas.deleted_at IS NULL', 'left')
            ->join('entradas', 'entradas.detalle_venta_id = detalle_ventas.id', 'left')
            ->where('funciones.deleted_at', null)
            ->where('eventos.deleted_at', null)
            ->where('funciones.fecha_inicio >=', date('Y-m-d H:i:s'))
            ->whereNotIn('funciones.estado', ['cancelada', 'finalizada'])
            ->groupBy('funciones.id')
            ->orderBy('funciones.fecha_inicio', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        return array_map(function (array $row): array {
            return [
                'nombre' => $row['nombre'],
                'funcion' => $row['funcion_nombre'] ?: 'Función general',
                'fecha' => $this->formatDate((string) $row['fecha_inicio']),
                'vendidas' => (int) $row['vendidas'],
                'aforo' => (int) $row['aforo_total'],
                'estado' => $this->estadoVenta($row),
            ];
        }, $rows);
    }

    public function fechaActual(): string
    {
        $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $timestamp = time();

        return ucfirst($dias[(int) date('w', $timestamp)]) . ', ' . date('j', $timestamp) . ' de ' . $meses[(int) date('n', $timestamp) - 1];
    }

    private function eventosActivos(): int
    {
        return $this->db->table('eventos')
            ->where('estado', 'publicado')
            ->where('deleted_at', null)
            ->countAllResults();
    }

    private function funcionesProximaSemana(): int
    {
        return $this->db->table('funciones')
            ->join('eventos', 'eventos.id = funciones.evento_id')
            ->where('funciones.deleted_at', null)
            ->where('eventos.deleted_at', null)
            ->where('funciones.fecha_inicio >=', date('Y-m-d H:i:s'))
            ->where('funciones.fecha_inicio <=', date('Y-m-d H:i:s', strtotime('+7 days')))
            ->whereNotIn('funciones.estado', ['cancelada', 'finalizada'])
            ->countAllResults();
    }

    /** @return array{total: int, actual: int, anterior: int} */
    private function entradasVendidas(): array
    {
        $count = function (?string $from = null, ?string $to = null): int {
            $builder = $this->db->table('entradas')
                ->join('detalle_ventas', 'detalle_ventas.id = entradas.detalle_venta_id')
                ->join('ventas', 'ventas.id = detalle_ventas.venta_id')
                ->where('ventas.estado', 'pagada')
                ->where('ventas.deleted_at', null)
                ->whereIn('entradas.estado', ['vigente', 'usada']);

            if ($from !== null) {
                $builder->where('ventas.pagado_at >=', $from);
            }

            if ($to !== null) {
                $builder->where('ventas.pagado_at <', $to);
            }

            return $builder->countAllResults();
        };

        $inicioActual = date('Y-m-01 00:00:00');
        $inicioSiguiente = date('Y-m-01 00:00:00', strtotime('first day of next month'));
        $inicioAnterior = date('Y-m-01 00:00:00', strtotime('first day of last month'));

        return [
            'total' => $count(),
            'actual' => $count($inicioActual, $inicioSiguiente),
            'anterior' => $count($inicioAnterior, $inicioActual),
        ];
    }

    /** @return array{total: int, ocupado: int} */
    private function aforoProgramado(): array
    {
        $total = $this->db->table('funciones')
            ->selectSum('aforo_total', 'total')
            ->where('deleted_at', null)
            ->where('fecha_inicio >=', date('Y-m-d H:i:s'))
            ->whereNotIn('estado', ['cancelada', 'finalizada'])
            ->get()
            ->getRowArray();

        $ocupado = $this->db->table('entradas')
            ->select('COUNT(entradas.id) AS total', false)
            ->join('detalle_ventas', 'detalle_ventas.id = entradas.detalle_venta_id')
            ->join('ventas', 'ventas.id = detalle_ventas.venta_id')
            ->join('tipos_entrada', 'tipos_entrada.id = detalle_ventas.tipo_entrada_id')
            ->join('funciones', 'funciones.id = tipos_entrada.funcion_id')
            ->where('ventas.estado', 'pagada')
            ->where('ventas.deleted_at', null)
            ->whereIn('entradas.estado', ['vigente', 'usada'])
            ->where('funciones.deleted_at', null)
            ->where('funciones.fecha_inicio >=', date('Y-m-d H:i:s'))
            ->whereNotIn('funciones.estado', ['cancelada', 'finalizada'])
            ->get()
            ->getRowArray();

        return ['total' => (int) ($total['total'] ?? 0), 'ocupado' => (int) ($ocupado['total'] ?? 0)];
    }

    private function ingresosConfirmados(): float
    {
        $row = $this->db->table('ventas')
            ->selectSum('total', 'total_ingresos')
            ->where('estado', 'pagada')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        return (float) ($row['total_ingresos'] ?? 0);
    }

    private function detalleComparacionVentas(int $actual, int $anterior): string
    {
        if ($anterior === 0) {
            return $actual === 0 ? 'Sin ventas durante este mes' : $actual . ' vendida(s) durante este mes';
        }

        $variacion = (int) round((($actual - $anterior) / $anterior) * 100);

        return ($variacion >= 0 ? '+' : '') . $variacion . ' % frente al mes anterior';
    }

    /** @param array<string, mixed> $row */
    private function estadoVenta(array $row): string
    {
        if ($row['evento_estado'] === 'borrador') {
            return 'Borrador';
        }

        $now = time();
        $inicio = $row['venta_inicio'] ? strtotime((string) $row['venta_inicio']) : null;
        $fin = $row['venta_fin'] ? strtotime((string) $row['venta_fin']) : null;

        if (($inicio === null || $now >= $inicio) && ($fin === null || $now <= $fin)) {
            return 'Venta activa';
        }

        return $inicio !== null && $now < $inicio ? 'Próximamente' : 'Venta cerrada';
    }

    private function formatDate(string $date): string
    {
        return date('d/m/Y · H:i', strtotime($date));
    }
}
