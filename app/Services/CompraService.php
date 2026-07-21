<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use Throwable;

class CompraService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= db_connect();
    }

    public function opcion(int $id): array
    {
        $r = $this->db->table('tipos_entrada te')->select('te.*, f.fecha_inicio,f.venta_inicio,f.venta_fin,f.recinto,f.ciudad,f.estado funcion_estado,e.id evento_id,e.nombre evento_nombre,e.slug,e.estado evento_estado')->join('funciones f', 'f.id=te.funcion_id')->join('eventos e', 'e.id=f.evento_id')->where('te.id', $id)->where('te.activo', 1)->where('te.deleted_at', null)->where('f.deleted_at', null)->where('e.deleted_at', null)->get()->getRowArray();
        if (!$r || $r['evento_estado'] !== 'publicado' || in_array($r['funcion_estado'], ['cancelada', 'finalizada'], true)) throw new DomainException('La entrada seleccionada no está disponible.');
        $now = time();
        if (strtotime($r['fecha_inicio']) <= $now || ($r['venta_inicio'] && strtotime($r['venta_inicio']) > $now) || ($r['venta_fin'] && strtotime($r['venta_fin']) < $now)) throw new DomainException('La venta no está disponible en este momento.');
        $r['vendidos'] = $this->vendidos($id);
        $r['disponibles'] = max(0, (int)$r['cupo'] - $r['vendidos']);
        return $r;
    }

    public function preview(int $id, int $cantidad, ?string $codigo): array
    {
        $o = $this->opcion($id);
        $this->cantidadValida($o, $cantidad);
        $subtotal = round((float)$o['precio'] * $cantidad, 2);
        $d = $this->descuento($codigo, (int)$o['evento_id'], $subtotal, false);
        $rebaja = $this->rebaja($d, $subtotal);
        return ['opcion' => $o, 'cantidad' => $cantidad, 'codigo_descuento' => $d['codigo'] ?? null, 'descuento_nombre' => $d['nombre'] ?? null, 'subtotal' => $subtotal, 'descuento_total' => $rebaja, 'total' => round($subtotal - $rebaja, 2)];
    }

    public function comprar(int $id, int $cantidad, ?string $codigo, array $cliente, ?array $vendedor = null, string $metodoPago = 'simulado'): array
    {
        $this->db->transBegin();
        try {
            $o = $this->db->query("SELECT te.*,f.fecha_inicio,f.venta_inicio,f.venta_fin,f.estado funcion_estado,e.id evento_id,e.estado evento_estado FROM tipos_entrada te JOIN funciones f ON f.id=te.funcion_id JOIN eventos e ON e.id=f.evento_id WHERE te.id=? AND te.activo=1 AND te.deleted_at IS NULL AND f.deleted_at IS NULL AND e.deleted_at IS NULL FOR UPDATE", [$id])->getRowArray();
            if (!$o || $o['evento_estado'] !== 'publicado' || in_array($o['funcion_estado'], ['cancelada', 'finalizada'], true)) throw new DomainException('La entrada ya no está disponible.');
            $now = time();
            $ventaPresencial = $vendedor !== null;
            if (strtotime($o['fecha_inicio']) <= $now || (! $ventaPresencial && (($o['venta_inicio'] && strtotime($o['venta_inicio']) > $now) || ($o['venta_fin'] && strtotime($o['venta_fin']) < $now)))) throw new DomainException('La venta no está disponible en este momento.');
            $o['vendidos'] = $this->vendidos($id);
            $o['disponibles'] = max(0, (int)$o['cupo'] - $o['vendidos']);
            $this->cantidadValida($o, $cantidad);
            $subtotal = round((float)$o['precio'] * $cantidad, 2);
            $d = $this->descuento($codigo, (int)$o['evento_id'], $subtotal, true);
            $rebaja = $this->rebaja($d, $subtotal);
            $total = round($subtotal - $rebaja, 2);
            $fecha = date('Y-m-d H:i:s');
            $venta = 'VTA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $this->db->table('ventas')->insert(['codigo' => $venta, 'cliente_id' => $cliente['id'], 'vendedor_id' => $vendedor['id'] ?? null, 'descuento_id' => $d['id'] ?? null, 'cliente_nombre' => $cliente['nombre'], 'cliente_email' => $cliente['email'], 'subtotal' => $subtotal, 'descuento_total' => $rebaja, 'total' => $total, 'estado' => 'pagada', 'metodo_pago' => $metodoPago, 'referencia_pago' => ($vendedor ? 'POS-' : 'SIM-') . strtoupper(bin2hex(random_bytes(5))), 'pagado_at' => $fecha, 'created_at' => $fecha, 'updated_at' => $fecha]);
            $ventaId = (int)$this->db->insertID();
            $this->db->table('detalle_ventas')->insert(['venta_id' => $ventaId, 'tipo_entrada_id' => $id, 'cantidad' => $cantidad, 'precio_unitario' => $o['precio'], 'descuento_unitario' => round($rebaja / $cantidad, 2), 'subtotal' => $subtotal, 'created_at' => $fecha, 'updated_at' => $fecha]);
            $detalle = (int)$this->db->insertID();
            $rows = [];
            for ($i = 0; $i < $cantidad; $i++) {
                $rows[] = ['detalle_venta_id' => $detalle, 'codigo' => 'ENT-' . strtoupper(bin2hex(random_bytes(6))), 'qr_token_hash' => hash('sha256', bin2hex(random_bytes(24))), 'titular_nombre' => $cliente['nombre'], 'titular_email' => $cliente['email'], 'estado' => 'vigente', 'emitida_at' => $fecha, 'created_at' => $fecha, 'updated_at' => $fecha];
            }
            $this->db->table('entradas')->insertBatch($rows);
            if ($d) $this->db->table('descuentos')->where('id', $d['id'])->set('usos_actuales', 'usos_actuales + 1', false)->set('updated_at', $fecha)->update();
            if (!$this->db->transStatus()) throw new DomainException('No se pudo completar la transacción.');
            $this->db->transCommit();
            return ['id' => $ventaId, 'codigo' => $venta, 'total' => $total, 'entradas' => $cantidad];
        } catch (Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function reservar(int $id, int $cantidad, ?string $codigo, array $cliente): array
    {
        $this->db->transBegin();
        try {
            $o = $this->db->query("SELECT te.*,f.fecha_inicio,f.venta_inicio,f.venta_fin,f.estado funcion_estado,e.id evento_id,e.estado evento_estado FROM tipos_entrada te JOIN funciones f ON f.id=te.funcion_id JOIN eventos e ON e.id=f.evento_id WHERE te.id=? AND te.activo=1 AND te.deleted_at IS NULL AND f.deleted_at IS NULL AND e.deleted_at IS NULL FOR UPDATE", [$id])->getRowArray();
            if (!$o || $o['evento_estado'] !== 'publicado' || in_array($o['funcion_estado'], ['cancelada', 'finalizada'], true)) throw new DomainException('La entrada ya no está disponible.');
            $now = time();
            if (strtotime($o['fecha_inicio']) <= $now || ($o['venta_inicio'] && strtotime($o['venta_inicio']) > $now) || ($o['venta_fin'] && strtotime($o['venta_fin']) < $now)) throw new DomainException('La reserva no está disponible en este momento.');
            $o['vendidos'] = $this->vendidos($id);
            $o['disponibles'] = max(0, (int)$o['cupo'] - $o['vendidos']);
            $this->cantidadValida($o, $cantidad);
            $subtotal = round((float)$o['precio'] * $cantidad, 2);
            $d = $this->descuento($codigo, (int)$o['evento_id'], $subtotal, true);
            $rebaja = $this->rebaja($d, $subtotal);
            $total = round($subtotal - $rebaja, 2);
            $fecha = date('Y-m-d H:i:s');
            $venta = 'RES-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $this->db->table('ventas')->insert(['codigo' => $venta, 'cliente_id' => $cliente['id'], 'descuento_id' => $d['id'] ?? null, 'cliente_nombre' => $cliente['nombre'], 'cliente_email' => $cliente['email'], 'subtotal' => $subtotal, 'descuento_total' => $rebaja, 'total' => $total, 'estado' => 'pendiente', 'metodo_pago' => 'pago-en-ventanilla', 'referencia_pago' => null, 'pagado_at' => null, 'created_at' => $fecha, 'updated_at' => $fecha]);
            $ventaId = (int)$this->db->insertID();
            $this->db->table('detalle_ventas')->insert(['venta_id' => $ventaId, 'tipo_entrada_id' => $id, 'cantidad' => $cantidad, 'precio_unitario' => $o['precio'], 'descuento_unitario' => round($rebaja / $cantidad, 2), 'subtotal' => $subtotal, 'created_at' => $fecha, 'updated_at' => $fecha]);
            if ($d) $this->db->table('descuentos')->where('id', $d['id'])->set('usos_actuales', 'usos_actuales + 1', false)->set('updated_at', $fecha)->update();
            if (!$this->db->transStatus()) throw new DomainException('No se pudo completar la reserva.');
            $this->db->transCommit();
            return ['id' => $ventaId, 'codigo' => $venta, 'total' => $total, 'entradas' => $cantidad];
        } catch (Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function confirmarReserva(int $ventaId, array $vendedor, string $metodoPago): array
    {
        if (!in_array($metodoPago, ['efectivo', 'transferencia', 'tarjeta-simulada'], true)) throw new DomainException('Selecciona un método de pago válido.');
        $this->db->transBegin();
        try {
            $venta = $this->db->query("SELECT * FROM ventas WHERE id=? AND deleted_at IS NULL FOR UPDATE", [$ventaId])->getRowArray();
            if (!$venta) throw new DomainException('La reserva no existe.');
            if ($venta['estado'] !== 'pendiente' || $venta['metodo_pago'] !== 'pago-en-ventanilla') throw new DomainException('La reserva ya fue procesada o no requiere confirmación en ventanilla.');
            $detalles = $this->db->query("SELECT dv.*,f.fecha_inicio,f.estado funcion_estado,e.estado evento_estado FROM detalle_ventas dv JOIN tipos_entrada te ON te.id=dv.tipo_entrada_id JOIN funciones f ON f.id=te.funcion_id JOIN eventos e ON e.id=f.evento_id WHERE dv.venta_id=? FOR UPDATE", [$ventaId])->getResultArray();
            if ($detalles === []) throw new DomainException('La reserva no tiene entradas asociadas.');
            $fecha = date('Y-m-d H:i:s');
            $rows = [];
            foreach ($detalles as $detalle) {
                if (strtotime($detalle['fecha_inicio']) <= time() || $detalle['evento_estado'] === 'cancelado' || $detalle['funcion_estado'] === 'cancelada') throw new DomainException('La función ya comenzó o fue cancelada.');
                for ($i = 0; $i < (int)$detalle['cantidad']; $i++) {
                    $rows[] = ['detalle_venta_id' => $detalle['id'], 'codigo' => 'ENT-' . strtoupper(bin2hex(random_bytes(6))), 'qr_token_hash' => hash('sha256', bin2hex(random_bytes(24))), 'titular_nombre' => $venta['cliente_nombre'], 'titular_email' => $venta['cliente_email'], 'estado' => 'vigente', 'emitida_at' => $fecha, 'created_at' => $fecha, 'updated_at' => $fecha];
                }
            }
            $this->db->table('entradas')->insertBatch($rows);
            $this->db->table('ventas')->where('id', $ventaId)->update(['vendedor_id' => $vendedor['id'], 'estado' => 'pagada', 'metodo_pago' => $metodoPago, 'referencia_pago' => 'POS-' . strtoupper(bin2hex(random_bytes(5))), 'pagado_at' => $fecha, 'updated_at' => $fecha]);
            if (!$this->db->transStatus()) throw new DomainException('No se pudo confirmar el pago.');
            $this->db->transCommit();
            return ['codigo' => $venta['codigo'], 'entradas' => count($rows)];
        } catch (Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    private function vendidos(int $id): int
    {
        $emitidos = $this->db->table('entradas e')->join('detalle_ventas d', 'd.id=e.detalle_venta_id')->join('ventas v', 'v.id=d.venta_id')->where('d.tipo_entrada_id', $id)->where('v.estado', 'pagada')->where('v.deleted_at', null)->whereIn('e.estado', ['vigente', 'usada'])->countAllResults();
        $reserva = $this->db->table('detalle_ventas d')->selectSum('d.cantidad', 'total')->join('ventas v', 'v.id=d.venta_id')->where('d.tipo_entrada_id', $id)->where('v.estado', 'pendiente')->where('v.metodo_pago', 'pago-en-ventanilla')->where('v.deleted_at', null)->get()->getRowArray();
        return $emitidos + (int)($reserva['total'] ?? 0);
    }
    private function cantidadValida(array $o, int $n): void
    {
        if ($n < 1 || $n > (int)$o['limite_por_compra']) throw new DomainException('La cantidad debe estar entre 1 y ' . $o['limite_por_compra'] . '.');
        if ($n > (int)$o['disponibles']) throw new DomainException('No hay suficientes entradas disponibles.');
    }
    private function descuento(?string $c, int $evento, float $subtotal, bool $lock): ?array
    {
        if (!$c) return null;
        $sql = 'SELECT * FROM descuentos WHERE codigo=? AND activo=1 AND deleted_at IS NULL' . ($lock ? ' FOR UPDATE' : '');
        $d = $this->db->query($sql, [strtoupper(trim($c))])->getRowArray();
        $now = time();
        if (!$d || ($d['evento_id'] !== null && (int)$d['evento_id'] !== $evento) || strtotime($d['fecha_inicio']) > $now || strtotime($d['fecha_fin']) < $now || ($d['limite_usos'] !== null && (int)$d['usos_actuales'] >= (int)$d['limite_usos']) || $subtotal < (float)$d['compra_minima']) throw new DomainException('El código de descuento no es válido para esta compra.');
        return $d;
    }
    private function rebaja(?array $d, float $subtotal): float
    {
        if (!$d) return 0;
        return round(min($subtotal, $d['tipo'] === 'porcentaje' ? $subtotal * (float)$d['valor'] / 100 : (float)$d['valor']), 2);
    }
}
