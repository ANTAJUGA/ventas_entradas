<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DescuentoModel;
use App\Models\EventoModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Descuentos extends BaseController
{
    public function index(): string
    {
        $descuentos = (new DescuentoModel())
            ->select('descuentos.*, eventos.nombre AS evento_nombre')
            ->join('eventos', 'eventos.id = descuentos.evento_id', 'left')
            ->orderBy('descuentos.created_at', 'DESC')
            ->findAll();

        return view('admin/descuentos/index', $this->viewData([
            'titulo' => 'Descuentos',
            'descuentos' => $descuentos,
        ]));
    }

    public function new(): string
    {
        return view('admin/descuentos/form', $this->viewData([
            'titulo' => 'Crear descuento',
            'descuento' => null,
            'eventos' => $this->eventosDisponibles(),
            'accion' => base_url('admin/descuentos'),
        ]));
    }

    public function create(): RedirectResponse
    {
        $data = $this->discountData();
        $data['usos_actuales'] = 0;

        if (($errors = $this->businessErrors($data)) !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $descuentoModel = new DescuentoModel();

        if (! $descuentoModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $descuentoModel->errors());
        }

        return redirect()->to(base_url('admin/descuentos'))->with('success', 'El descuento fue creado.');
    }

    public function edit(int $id): string
    {
        return view('admin/descuentos/form', $this->viewData([
            'titulo' => 'Editar descuento',
            'descuento' => $this->findDescuento($id),
            'eventos' => $this->eventosDisponibles(),
            'accion' => base_url("admin/descuentos/{$id}"),
        ]));
    }

    public function update(int $id): RedirectResponse
    {
        $descuento = $this->findDescuento($id);
        $data = $this->discountData();
        $data['id'] = $id;
        $data['usos_actuales'] = $descuento['usos_actuales'];

        if (($errors = $this->businessErrors($data, $id)) !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $descuentoModel = new DescuentoModel();

        if (! $descuentoModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $descuentoModel->errors());
        }

        return redirect()->to(base_url('admin/descuentos'))->with('success', 'El descuento fue actualizado.');
    }

    public function delete(int $id): RedirectResponse
    {
        $this->findDescuento($id);
        (new DescuentoModel())->delete($id);

        return redirect()->to(base_url('admin/descuentos'))->with('success', 'El descuento fue eliminado.');
    }

    /** @return array<string, mixed> */
    private function discountData(): array
    {
        $eventoId = trim((string) $this->request->getPost('evento_id'));
        $limiteUsos = trim((string) $this->request->getPost('limite_usos'));

        return [
            'evento_id' => $eventoId === '' ? null : $eventoId,
            'codigo' => strtoupper(trim((string) $this->request->getPost('codigo'))),
            'nombre' => trim((string) $this->request->getPost('nombre')),
            'descripcion' => trim((string) $this->request->getPost('descripcion')),
            'tipo' => $this->request->getPost('tipo'),
            'valor' => $this->request->getPost('valor'),
            'compra_minima' => $this->request->getPost('compra_minima') ?: 0,
            'fecha_inicio' => $this->databaseDate($this->request->getPost('fecha_inicio')),
            'fecha_fin' => $this->databaseDate($this->request->getPost('fecha_fin')),
            'limite_usos' => $limiteUsos === '' ? null : $limiteUsos,
            'activo' => $this->request->getPost('activo') === '1' ? 1 : 0,
        ];
    }

    /** @param array<string, mixed> $data @return array<string, string> */
    private function businessErrors(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        $valor = filter_var($data['valor'], FILTER_VALIDATE_FLOAT);
        $inicio = strtotime((string) $data['fecha_inicio']);
        $fin = strtotime((string) $data['fecha_fin']);

        if ($data['tipo'] === 'porcentaje' && $valor !== false && $valor > 100) {
            $errors['valor'] = 'Un descuento porcentual no puede superar el 100 %.';
        }

        if ($inicio !== false && $fin !== false && $fin <= $inicio) {
            $errors['fecha_fin'] = 'La fecha final debe ser posterior al inicio de la vigencia.';
        }

        if ($data['limite_usos'] !== null && (int) $data['limite_usos'] < (int) $data['usos_actuales']) {
            $errors['limite_usos'] = 'El límite no puede ser menor que la cantidad de usos actuales.';
        }

        $duplicate = (new DescuentoModel())->where('codigo', $data['codigo']);

        if ($excludeId !== null) {
            $duplicate->where('id !=', $excludeId);
        }

        if ($duplicate->first() !== null) {
            $errors['codigo'] = 'El código de descuento ya se encuentra registrado.';
        }

        return $errors;
    }

    private function databaseDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? $value : date('Y-m-d H:i:s', $timestamp);
    }

    /** @return array<int, array<string, mixed>> */
    private function eventosDisponibles(): array
    {
        return (new EventoModel())
            ->whereIn('estado', ['borrador', 'publicado'])
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }

    /** @return array<string, mixed> */
    private function findDescuento(int $id): array
    {
        $descuento = (new DescuentoModel())->find($id);

        if ($descuento === null) {
            throw PageNotFoundException::forPageNotFound('El descuento solicitado no existe.');
        }

        return $descuento;
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function viewData(array $data): array
    {
        return array_merge([
            'paginaActiva' => 'descuentos',
            'usuario' => ['nombre' => 'Administrador', 'rol' => 'Administrador'],
        ], $data);
    }
}
