<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RolModel;
use App\Models\UsuarioModel;
use App\Services\CompraService;
use CodeIgniter\HTTP\RedirectResponse;
use DomainException;
use Throwable;

class Ventas extends BaseController
{
    public function index(): string
    {
        $usuario = session('usuario');
        $query = db_connect()->table('ventas v')
            ->select('v.*, u.nombres vendedor_nombres, u.apellidos vendedor_apellidos, COUNT(e.id) entradas_cantidad')
            ->join('usuarios u', 'u.id = v.vendedor_id', 'left')
            ->join('detalle_ventas dv', 'dv.venta_id = v.id', 'left')
            ->join('entradas e', 'e.detalle_venta_id = dv.id', 'left')
            ->where('v.deleted_at', null)
            ->groupBy('v.id')
            ->orderBy('v.created_at', 'DESC');

        if (($usuario['rol_slug'] ?? '') === 'vendedor') {
            $query->groupStart()
                ->where('v.vendedor_id', $usuario['id'])
                ->orWhere('v.vendedor_id', null)
                ->groupEnd();
        }

        return view('admin/ventas/index', $this->data('Historial de ventas', 'ventas', [
            'ventas' => $query->get()->getResultArray(),
        ]));
    }

    public function new(): string
    {
        $db = db_connect();
        $opciones = $db->table('tipos_entrada te')
            ->select('te.id, te.nombre tipo_nombre, te.precio, te.cupo, te.limite_por_compra, f.fecha_inicio, f.nombre funcion_nombre, e.nombre evento_nombre')
            ->join('funciones f', 'f.id = te.funcion_id')
            ->join('eventos e', 'e.id = f.evento_id')
            ->where('te.activo', 1)->where('te.deleted_at', null)
            ->where('f.deleted_at', null)->where('e.deleted_at', null)
            ->where('e.estado', 'publicado')->whereNotIn('f.estado', ['cancelada', 'finalizada'])
            ->where('f.fecha_inicio >', date('Y-m-d H:i:s'))
            ->orderBy('f.fecha_inicio', 'ASC')->get()->getResultArray();
        $clientes = $db->table('usuarios u')->select('u.id,u.nombres,u.apellidos,u.email')
            ->join('roles r', 'r.id=u.rol_id')->where('r.slug', 'cliente')->where('u.estado', 'activo')
            ->where('u.deleted_at', null)->orderBy('u.nombres')->get()->getResultArray();

        return view('admin/ventas/form', $this->data('Punto de venta', 'punto_venta', [
            'opciones' => $opciones,
            'clientes' => $clientes,
        ]));
    }

    public function create(): RedirectResponse
    {
        $tipoId = (int) $this->request->getPost('tipo_entrada_id');
        $cantidad = (int) $this->request->getPost('cantidad');
        $metodoPago = (string) $this->request->getPost('metodo_pago');
        if (! in_array($metodoPago, ['efectivo', 'transferencia', 'tarjeta-simulada'], true)) {
            return redirect()->back()->withInput()->with('error', 'Selecciona un método de pago válido.');
        }

        try {
            [$cliente, $claveTemporal] = $this->resolverCliente();
            $resultado = (new CompraService())->comprar(
                $tipoId,
                $cantidad,
                trim((string) $this->request->getPost('codigo_descuento')) ?: null,
                $cliente,
                session('usuario'),
                $metodoPago
            );
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            log_message('error', 'Error de venta presencial: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'No se pudo completar la venta presencial.');
        }

        $mensaje = "Venta {$resultado['codigo']} confirmada por $" . number_format((float) $resultado['total'], 2) . '.';
        if ($claveTemporal !== null) {
            $mensaje .= " Cliente registrado. Contraseña temporal: {$claveTemporal}";
        }

        return redirect()->to(base_url('admin/ventas/' . $resultado['id']))->with('success', $mensaje);
    }

    public function show(int $id): string|RedirectResponse
    {
        $venta = $this->ventaAutorizada($id);
        if ($venta === null) {
            return redirect()->to(base_url('admin/ventas'))->with('error', 'Venta no encontrada o sin autorización.');
        }

        $entradas = db_connect()->table('entradas e')
            ->select('e.*, te.nombre tipo_nombre, f.fecha_inicio, f.recinto, f.ciudad, ev.nombre evento_nombre')
            ->join('detalle_ventas dv', 'dv.id=e.detalle_venta_id')
            ->join('tipos_entrada te', 'te.id=dv.tipo_entrada_id')
            ->join('funciones f', 'f.id=te.funcion_id')->join('eventos ev', 'ev.id=f.evento_id')
            ->where('dv.venta_id', $id)->orderBy('e.id')->get()->getResultArray();

        $detalles = db_connect()->table('detalle_ventas dv')
            ->select('dv.*,te.nombre tipo_nombre,f.fecha_inicio,f.recinto,f.ciudad,ev.nombre evento_nombre')
            ->join('tipos_entrada te', 'te.id=dv.tipo_entrada_id')->join('funciones f', 'f.id=te.funcion_id')
            ->join('eventos ev', 'ev.id=f.evento_id')->where('dv.venta_id', $id)->get()->getResultArray();

        return view('admin/ventas/show', $this->data('Detalle de venta', 'ventas', compact('venta', 'entradas', 'detalles')));
    }

    public function confirmPayment(int $id): RedirectResponse
    {
        if ($this->ventaAutorizada($id) === null) {
            return redirect()->to(base_url('admin/ventas'))->with('error', 'Reserva no encontrada o sin autorización.');
        }
        try {
            $resultado = (new CompraService())->confirmarReserva($id, session('usuario'), (string)$this->request->getPost('metodo_pago'));
        } catch (DomainException $e) {
            return redirect()->to(base_url('admin/ventas/' . $id))->with('error', $e->getMessage());
        } catch (Throwable $e) {
            log_message('error', 'Error al confirmar reserva: {message}', ['message' => $e->getMessage()]);
            return redirect()->to(base_url('admin/ventas/' . $id))->with('error', 'No se pudo confirmar el pago de la reserva.');
        }
        return redirect()->to(base_url('admin/ventas/' . $id))->with('success', "Pago de {$resultado['codigo']} confirmado. Se emitieron {$resultado['entradas']} entrada(s).");
    }

    public function printTicket(int $id): string|RedirectResponse
    {
        $entrada = db_connect()->table('entradas e')
            ->select('e.*, v.id venta_id,v.codigo venta_codigo,v.cliente_nombre,te.nombre tipo_nombre,f.fecha_inicio,f.recinto,f.ciudad,ev.nombre evento_nombre')
            ->join('detalle_ventas dv', 'dv.id=e.detalle_venta_id')->join('ventas v', 'v.id=dv.venta_id')
            ->join('tipos_entrada te', 'te.id=dv.tipo_entrada_id')->join('funciones f', 'f.id=te.funcion_id')
            ->join('eventos ev', 'ev.id=f.evento_id')->where('e.id', $id)->get()->getRowArray();
        if ($entrada === null || $this->ventaAutorizada((int) $entrada['venta_id']) === null) {
            return redirect()->to(base_url('admin/ventas'))->with('error', 'Entrada no encontrada o sin autorización.');
        }
        return view('admin/ventas/print', ['titulo' => 'Entrada ' . $entrada['codigo'], 'entrada' => $entrada]);
    }

    private function resolverCliente(): array
    {
        $model = new UsuarioModel();
        $email = strtolower(trim((string) $this->request->getPost('cliente_email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Ingresa un correo válido para identificar al cliente.');
        }
        $existente = $model->select('usuarios.*,roles.slug rol_slug')->join('roles', 'roles.id=usuarios.rol_id')->where('usuarios.email', $email)->first();
        if ($existente !== null) {
            if ($existente['rol_slug'] !== 'cliente' || $existente['estado'] !== 'activo') {
                throw new DomainException('El correo pertenece a una cuenta que no es un cliente activo.');
            }
            return [['id' => (int) $existente['id'], 'nombre' => trim($existente['nombres'] . ' ' . $existente['apellidos']), 'email' => $existente['email']], null];
        }

        $nombres = trim((string) $this->request->getPost('cliente_nombres'));
        $apellidos = trim((string) $this->request->getPost('cliente_apellidos'));
        if ($nombres === '' || $apellidos === '') {
            throw new DomainException('El correo no está registrado. Completa nombre y apellido para crear el cliente.');
        }
        $rol = (new RolModel())->where('slug', 'cliente')->where('activo', 1)->first();
        if ($rol === null) {
            throw new DomainException('El rol Cliente no está disponible.');
        }
        $clave = 'Cli-' . strtoupper(bin2hex(random_bytes(4)));
        if (! $model->insert(['rol_id' => $rol['id'], 'nombres' => $nombres, 'apellidos' => $apellidos, 'email' => $email, 'telefono' => trim((string) $this->request->getPost('cliente_telefono')), 'password_hash' => password_hash($clave, PASSWORD_DEFAULT), 'estado' => 'activo'])) {
            throw new DomainException(implode(' ', $model->errors()));
        }
        return [['id' => (int) $model->getInsertID(), 'nombre' => "$nombres $apellidos", 'email' => $email], $clave];
    }

    private function ventaAutorizada(int $id): ?array
    {
        $query = db_connect()->table('ventas v')->select('v.*,u.nombres vendedor_nombres,u.apellidos vendedor_apellidos')
            ->join('usuarios u', 'u.id=v.vendedor_id', 'left')->where('v.id', $id)->where('v.deleted_at', null);
        $usuario = session('usuario');
        if (($usuario['rol_slug'] ?? '') === 'vendedor') {
            $query->groupStart()
                ->where('v.vendedor_id', $usuario['id'])
                ->orWhere('v.vendedor_id', null)
                ->groupEnd();
        }
        return $query->get()->getRowArray() ?: null;
    }

    private function data(string $titulo, string $paginaActiva, array $extra = []): array
    {
        return array_merge(['titulo' => $titulo, 'paginaActiva' => $paginaActiva, 'usuario' => session('usuario')], $extra);
    }
}
