<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FuncionModel;
use App\Models\TipoEntradaModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class TiposEntrada extends BaseController
{
    public function index(): string
    {
        $tipos = (new TipoEntradaModel())
            ->select('tipos_entrada.*, funciones.nombre AS funcion_nombre, funciones.fecha_inicio, funciones.aforo_total, eventos.nombre AS evento_nombre')
            ->join('funciones', 'funciones.id = tipos_entrada.funcion_id')
            ->join('eventos', 'eventos.id = funciones.evento_id')
            ->orderBy('funciones.fecha_inicio', 'ASC')
            ->orderBy('tipos_entrada.precio', 'ASC')
            ->findAll();

        return view('admin/tipos_entrada/index', $this->viewData([
            'titulo' => 'Tipos de entrada',
            'tipos' => $tipos,
        ]));
    }

    public function new(int $funcionId): string
    {
        $funcion = $this->findFuncionConEvento($funcionId);

        return view('admin/tipos_entrada/form', $this->viewData([
            'titulo' => 'Crear tipo de entrada',
            'tipoEntrada' => null,
            'funcion' => $funcion,
            'funciones' => $this->funcionesDisponibles(),
            'cupoAsignado' => $this->cupoAsignado($funcionId),
            'accion' => base_url("admin/funciones/{$funcionId}/tipos-entrada"),
        ]));
    }

    public function create(int $funcionId): RedirectResponse
    {
        $this->findFuncionConEvento($funcionId);
        $data = $this->typeData();
        $data['funcion_id'] = $funcionId;

        if (($errors = $this->businessErrors($data)) !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $tipoModel = new TipoEntradaModel();

        if (! $tipoModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $tipoModel->errors());
        }

        return redirect()->to(base_url('admin/tipos-entrada'))->with('success', 'El tipo de entrada fue creado.');
    }

    public function edit(int $id): string
    {
        $tipoEntrada = $this->findTipoEntrada($id);
        $funcion = $this->findFuncionConEvento((int) $tipoEntrada['funcion_id']);

        return view('admin/tipos_entrada/form', $this->viewData([
            'titulo' => 'Editar tipo de entrada',
            'tipoEntrada' => $tipoEntrada,
            'funcion' => $funcion,
            'funciones' => $this->funcionesDisponibles(),
            'cupoAsignado' => $this->cupoAsignado((int) $tipoEntrada['funcion_id'], $id),
            'accion' => base_url("admin/tipos-entrada/{$id}"),
        ]));
    }

    public function update(int $id): RedirectResponse
    {
        $this->findTipoEntrada($id);
        $funcionId = (int) $this->request->getPost('funcion_id');
        $this->findFuncionConEvento($funcionId);

        $data = $this->typeData();
        $data['id'] = $id;
        $data['funcion_id'] = $funcionId;

        if (($errors = $this->businessErrors($data, $id)) !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $tipoModel = new TipoEntradaModel();

        if (! $tipoModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $tipoModel->errors());
        }

        return redirect()->to(base_url('admin/tipos-entrada'))->with('success', 'El tipo de entrada fue actualizado.');
    }

    public function delete(int $id): RedirectResponse
    {
        $this->findTipoEntrada($id);
        (new TipoEntradaModel())->delete($id);

        return redirect()->to(base_url('admin/tipos-entrada'))->with('success', 'El tipo de entrada fue eliminado.');
    }

    /** @return array<string, mixed> */
    private function typeData(): array
    {
        return [
            'nombre' => trim((string) $this->request->getPost('nombre')),
            'descripcion' => trim((string) $this->request->getPost('descripcion')),
            'precio' => $this->request->getPost('precio'),
            'cupo' => $this->request->getPost('cupo'),
            'limite_por_compra' => $this->request->getPost('limite_por_compra'),
            'activo' => $this->request->getPost('activo') === '1' ? 1 : 0,
        ];
    }

    /** @param array<string, mixed> $data @return array<string, string> */
    private function businessErrors(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        $funcionId = (int) $data['funcion_id'];
        $funcion = $this->findFuncionConEvento($funcionId);
        $cupo = filter_var($data['cupo'], FILTER_VALIDATE_INT);

        if ($cupo !== false && $cupo >= 0) {
            $asignado = $this->cupoAsignado($funcionId, $excludeId);

            if ($asignado + $cupo > (int) $funcion['aforo_total']) {
                $disponible = max(0, (int) $funcion['aforo_total'] - $asignado);
                $errors['cupo'] = "El cupo supera el aforo. Solo quedan {$disponible} lugares disponibles.";
            }
        }

        $duplicate = (new TipoEntradaModel())
            ->where('funcion_id', $funcionId)
            ->where('nombre', $data['nombre']);

        if ($excludeId !== null) {
            $duplicate->where('id !=', $excludeId);
        }

        if ($duplicate->first() !== null) {
            $errors['nombre'] = 'Ya existe un tipo de entrada con ese nombre para la función.';
        }

        return $errors;
    }

    private function cupoAsignado(int $funcionId, ?int $excludeId = null): int
    {
        $query = (new TipoEntradaModel())->selectSum('cupo', 'total')->where('funcion_id', $funcionId);

        if ($excludeId !== null) {
            $query->where('id !=', $excludeId);
        }

        $result = $query->first();

        return (int) ($result['total'] ?? 0);
    }

    /** @return array<int, array<string, mixed>> */
    private function funcionesDisponibles(): array
    {
        return (new FuncionModel())
            ->select('funciones.*, eventos.nombre AS evento_nombre')
            ->join('eventos', 'eventos.id = funciones.evento_id')
            ->whereIn('funciones.estado', ['programada', 'en_curso'])
            ->orderBy('funciones.fecha_inicio', 'ASC')
            ->findAll();
    }

    /** @return array<string, mixed> */
    private function findFuncionConEvento(int $id): array
    {
        $funcion = (new FuncionModel())
            ->select('funciones.*, eventos.nombre AS evento_nombre')
            ->join('eventos', 'eventos.id = funciones.evento_id')
            ->where('funciones.id', $id)
            ->first();

        if ($funcion === null) {
            throw PageNotFoundException::forPageNotFound('La función solicitada no existe.');
        }

        return $funcion;
    }

    /** @return array<string, mixed> */
    private function findTipoEntrada(int $id): array
    {
        $tipo = (new TipoEntradaModel())->find($id);

        if ($tipo === null) {
            throw PageNotFoundException::forPageNotFound('El tipo de entrada solicitado no existe.');
        }

        return $tipo;
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function viewData(array $data): array
    {
        return array_merge([
            'paginaActiva' => 'tipos_entrada',
            'usuario' => ['nombre' => 'Administrador', 'rol' => 'Administrador'],
        ], $data);
    }
}
