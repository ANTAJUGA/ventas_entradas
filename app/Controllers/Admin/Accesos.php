<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

class Accesos extends BaseController
{
    public function index(): string
    {
        $codigo = strtoupper(trim((string) $this->request->getGet('codigo')));
        $entrada = $codigo !== '' ? $this->buscarEntrada($codigo) : null;
        $db = db_connect();
        $hoy = date('Y-m-d 00:00:00');
        $resumen = [
            'aceptados' => $db->table('control_accesos')->where('resultado', 'aceptado')->where('registrado_at >=', $hoy)->countAllResults(),
            'rechazados' => $db->table('control_accesos')->where('resultado', 'rechazado')->where('registrado_at >=', $hoy)->countAllResults(),
        ];
        $historial = $db->table('control_accesos ca')
            ->select('ca.*,e.codigo,ev.nombre evento_nombre,u.nombres,u.apellidos')
            ->join('entradas e', 'e.id=ca.entrada_id')->join('detalle_ventas dv', 'dv.id=e.detalle_venta_id')
            ->join('tipos_entrada te', 'te.id=dv.tipo_entrada_id')->join('funciones f', 'f.id=te.funcion_id')
            ->join('eventos ev', 'ev.id=f.evento_id')->join('usuarios u', 'u.id=ca.usuario_id', 'left')
            ->orderBy('ca.registrado_at', 'DESC')->limit(50)->get()->getResultArray();

        return view('admin/accesos/index', [
            'titulo' => 'Control de acceso', 'paginaActiva' => 'accesos', 'usuario' => session('usuario'),
            'codigo' => $codigo, 'entrada' => $entrada, 'resumen' => $resumen, 'historial' => $historial,
        ]);
    }

    public function validateTicket(): RedirectResponse
    {
        $codigo = strtoupper(trim((string) $this->request->getPost('codigo')));
        $punto = trim((string) $this->request->getPost('punto_acceso'));
        if ($codigo === '') {
            return redirect()->back()->with('error', 'Ingresa o escanea el código de la entrada.');
        }

        $db = db_connect();
        $db->transBegin();
        try {
            $entrada = $db->query('SELECT e.*, f.estado funcion_estado, ev.estado evento_estado FROM entradas e JOIN detalle_ventas dv ON dv.id=e.detalle_venta_id JOIN tipos_entrada te ON te.id=dv.tipo_entrada_id JOIN funciones f ON f.id=te.funcion_id JOIN eventos ev ON ev.id=f.evento_id WHERE e.codigo=? FOR UPDATE', [$codigo])->getRowArray();
            if ($entrada === null) {
                $db->transRollback();
                return redirect()->to(base_url('admin/accesos?codigo=' . rawurlencode($codigo)))->with('error', 'El código no corresponde a ninguna entrada.');
            }

            $resultado = 'aceptado';
            $motivo = 'Ingreso autorizado.';
            if ($entrada['estado'] === 'usada') {
                $resultado = 'rechazado';
                $motivo = 'La entrada ya fue utilizada el ' . date('d/m/Y H:i', strtotime($entrada['usada_at'])) . '.';
            } elseif ($entrada['estado'] === 'anulada') {
                $resultado = 'rechazado';
                $motivo = 'La entrada está anulada.';
            } elseif ($entrada['evento_estado'] === 'cancelado' || $entrada['funcion_estado'] === 'cancelada') {
                $resultado = 'rechazado';
                $motivo = 'El evento o la función están cancelados.';
            }

            $fecha = date('Y-m-d H:i:s');
            if ($resultado === 'aceptado') {
                $db->table('entradas')->where('id', $entrada['id'])->update(['estado' => 'usada', 'usada_at' => $fecha, 'updated_at' => $fecha]);
            }
            $db->table('control_accesos')->insert([
                'entrada_id' => $entrada['id'], 'usuario_id' => session('usuario')['id'], 'resultado' => $resultado,
                'motivo' => $motivo, 'punto_acceso' => $punto ?: 'Acceso principal',
                'direccion_ip' => $this->request->getIPAddress(), 'registrado_at' => $fecha, 'created_at' => $fecha,
            ]);
            if (! $db->transStatus()) {
                throw new \RuntimeException('Falló el registro del acceso.');
            }
            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al validar acceso: {message}', ['message' => $e->getMessage()]);
            return redirect()->to(base_url('admin/accesos?codigo=' . rawurlencode($codigo)))->with('error', 'No se pudo validar la entrada.');
        }

        $tipo = $resultado === 'aceptado' ? 'success' : 'error';
        return redirect()->to(base_url('admin/accesos?codigo=' . rawurlencode($codigo)))->with($tipo, $motivo);
    }

    private function buscarEntrada(string $codigo): ?array
    {
        return db_connect()->table('entradas e')
            ->select('e.*,v.codigo venta_codigo,v.cliente_nombre,te.nombre tipo_nombre,f.nombre funcion_nombre,f.fecha_inicio,f.recinto,f.ciudad,ev.nombre evento_nombre')
            ->join('detalle_ventas dv', 'dv.id=e.detalle_venta_id')->join('ventas v', 'v.id=dv.venta_id')
            ->join('tipos_entrada te', 'te.id=dv.tipo_entrada_id')->join('funciones f', 'f.id=te.funcion_id')
            ->join('eventos ev', 'ev.id=f.evento_id')->where('e.codigo', $codigo)->get()->getRowArray() ?: null;
    }
}
