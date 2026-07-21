<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventoModel;
use App\Models\FuncionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Funciones extends BaseController
{
    public function index(): string
    {
        $funciones = (new FuncionModel())
            ->select('funciones.*, eventos.nombre AS evento_nombre')
            ->join('eventos', 'eventos.id = funciones.evento_id')
            ->orderBy('funciones.fecha_inicio', 'ASC')
            ->findAll();

        return view('admin/funciones/index', $this->viewData([
            'titulo' => 'Funciones',
            'funciones' => $funciones,
        ]));
    }

    public function new(int $eventoId): string
    {
        $evento = $this->findEvento($eventoId);

        return view('admin/funciones/form', $this->viewData([
            'titulo' => 'Crear función',
            'funcion' => null,
            'evento' => $evento,
            'eventos' => $this->eventosDisponibles(),
            'accion' => base_url("admin/eventos/{$eventoId}/funciones"),
        ]));
    }

    public function create(int $eventoId): RedirectResponse
    {
        $this->findEvento($eventoId);
        $data = $this->functionData();
        $data['evento_id'] = $eventoId;
        $data['estado'] = 'programada';

        $dateErrors = $this->dateErrors($data);

        if ($dateErrors !== []) {
            return redirect()->back()->withInput()->with('errors', $dateErrors);
        }

        $funcionModel = new FuncionModel();

        if (! $funcionModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $funcionModel->errors());
        }

        return redirect()->to(base_url('admin/funciones'))->with('success', 'La función fue creada.');
    }

    public function edit(int $id): string
    {
        $funcion = $this->findFuncion($id);

        return view('admin/funciones/form', $this->viewData([
            'titulo' => 'Editar función',
            'funcion' => $funcion,
            'evento' => $this->findEvento((int) $funcion['evento_id']),
            'eventos' => $this->eventosDisponibles(),
            'accion' => base_url("admin/funciones/{$id}"),
        ]));
    }

    public function update(int $id): RedirectResponse
    {
        $funcion = $this->findFuncion($id);
        $eventoId = (int) $this->request->getPost('evento_id');
        $this->findEvento($eventoId);

        $data = $this->functionData();
        $data['id'] = $id;
        $data['evento_id'] = $eventoId;
        $data['estado'] = $funcion['estado'];

        $dateErrors = $this->dateErrors($data);

        if ($dateErrors !== []) {
            return redirect()->back()->withInput()->with('errors', $dateErrors);
        }

        $funcionModel = new FuncionModel();

        if (! $funcionModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $funcionModel->errors());
        }

        return redirect()->to(base_url('admin/funciones'))->with('success', 'La función fue actualizada.');
    }

    public function delete(int $id): RedirectResponse
    {
        $this->findFuncion($id);
        (new FuncionModel())->delete($id);

        return redirect()->to(base_url('admin/funciones'))->with('success', 'La función fue eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function functionData(): array
    {
        return [
            'nombre' => trim((string) $this->request->getPost('nombre')),
            'fecha_inicio' => $this->databaseDate($this->request->getPost('fecha_inicio')),
            'fecha_fin' => $this->databaseDate($this->request->getPost('fecha_fin')),
            'venta_inicio' => $this->databaseDate($this->request->getPost('venta_inicio')),
            'venta_fin' => $this->databaseDate($this->request->getPost('venta_fin')),
            'recinto' => trim((string) $this->request->getPost('recinto')),
            'direccion' => trim((string) $this->request->getPost('direccion')),
            'ciudad' => trim((string) $this->request->getPost('ciudad')),
            'aforo_total' => $this->request->getPost('aforo_total'),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function dateErrors(array $data): array
    {
        $errors = [];
        $inicio = strtotime((string) $data['fecha_inicio']);
        $fin = $data['fecha_fin'] ? strtotime((string) $data['fecha_fin']) : null;
        $ventaInicio = $data['venta_inicio'] ? strtotime((string) $data['venta_inicio']) : null;
        $ventaFin = $data['venta_fin'] ? strtotime((string) $data['venta_fin']) : null;

        if ($inicio !== false && $fin !== null && $fin !== false && $fin <= $inicio) {
            $errors['fecha_fin'] = 'La fecha final debe ser posterior al inicio de la función.';
        }

        if (($ventaInicio === null) !== ($ventaFin === null)) {
            $errors['periodo_venta'] = 'Debes indicar tanto el inicio como el fin del periodo de venta.';
        } elseif ($ventaInicio !== null && $ventaInicio !== false && $ventaFin !== false) {
            if ($ventaFin <= $ventaInicio) {
                $errors['venta_fin'] = 'El fin de venta debe ser posterior al inicio de venta.';
            }

            if ($inicio !== false && $ventaFin > $inicio) {
                $errors['venta_funcion'] = 'La venta debe finalizar antes de iniciar la función.';
            }
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function eventosDisponibles(): array
    {
        return (new EventoModel())
            ->whereIn('estado', ['borrador', 'publicado'])
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }

    /**
     * @return array<string, mixed>
     */
    private function findEvento(int $id): array
    {
        $evento = (new EventoModel())->find($id);

        if ($evento === null) {
            throw PageNotFoundException::forPageNotFound('El evento solicitado no existe.');
        }

        return $evento;
    }

    /**
     * @return array<string, mixed>
     */
    private function findFuncion(int $id): array
    {
        $funcion = (new FuncionModel())->find($id);

        if ($funcion === null) {
            throw PageNotFoundException::forPageNotFound('La función solicitada no existe.');
        }

        return $funcion;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function viewData(array $data): array
    {
        return array_merge([
            'paginaActiva' => 'funciones',
            'usuario' => [
                'nombre' => 'Administrador',
                'rol' => 'Administrador',
            ],
        ], $data);
    }
}
