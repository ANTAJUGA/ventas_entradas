<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventoModel;
use App\Models\FuncionModel;
use App\Models\UsuarioModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Eventos extends BaseController
{
    public function index(): string
    {
        $eventoModel = new EventoModel();

        $eventos = $eventoModel
            ->select('eventos.*, CONCAT(usuarios.nombres, " ", usuarios.apellidos) AS organizador_nombre, COUNT(funciones.id) AS total_funciones')
            ->join('usuarios', 'usuarios.id = eventos.organizador_id')
            ->join('funciones', 'funciones.evento_id = eventos.id AND funciones.deleted_at IS NULL', 'left')
            ->groupBy('eventos.id')
            ->orderBy('eventos.created_at', 'DESC')
            ->findAll();

        return view('admin/eventos/index', $this->viewData([
            'titulo' => 'Eventos',
            'eventos' => $eventos,
        ]));
    }

    public function new(): string
    {
        return view('admin/eventos/form', $this->viewData([
            'titulo' => 'Crear evento',
            'evento' => null,
            'organizadores' => $this->organizadores(),
            'accion' => base_url('admin/eventos'),
        ]));
    }

    public function create(): RedirectResponse
    {
        helper('url');

        $eventoModel = new EventoModel();
        $data = $this->eventData();
        $data['slug'] = url_title($data['nombre'], '-', true);
        $data['estado'] = 'borrador';

        if (! $eventoModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $eventoModel->errors());
        }

        return redirect()->to(base_url('admin/eventos'))->with('success', 'El evento fue creado como borrador.');
    }

    public function edit(int $id): string
    {
        return view('admin/eventos/form', $this->viewData([
            'titulo' => 'Editar evento',
            'evento' => $this->findEvento($id),
            'organizadores' => $this->organizadores(),
            'accion' => base_url("admin/eventos/{$id}"),
        ]));
    }

    public function update(int $id): RedirectResponse
    {
        helper('url');

        $evento = $this->findEvento($id);
        $eventoModel = new EventoModel();
        $data = $this->eventData();
        $data['id'] = $id;
        $data['slug'] = url_title($data['nombre'], '-', true);
        $data['estado'] = $evento['estado'];

        if (! $eventoModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $eventoModel->errors());
        }

        return redirect()->to(base_url('admin/eventos'))->with('success', 'El evento fue actualizado.');
    }

    public function publish(int $id): RedirectResponse
    {
        $evento = $this->findEvento($id);

        if ($evento['estado'] === 'publicado') {
            return redirect()->to(base_url('admin/eventos'))->with('info', 'El evento ya se encuentra publicado.');
        }

        $totalFunciones = (new FuncionModel())
            ->where('evento_id', $id)
            ->countAllResults();

        if ($totalFunciones === 0) {
            return redirect()->to(base_url('admin/eventos'))
                ->with('error', 'Agrega al menos una función antes de publicar el evento.');
        }

        (new EventoModel())->update($id, [
            'estado' => 'publicado',
            'publicado_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/eventos'))->with('success', 'El evento fue publicado.');
    }

    public function delete(int $id): RedirectResponse
    {
        $this->findEvento($id);
        (new EventoModel())->delete($id);

        return redirect()->to(base_url('admin/eventos'))->with('success', 'El evento fue eliminado.');
    }

    /**
     * @return array<string, string|null>
     */
    private function eventData(): array
    {
        return [
            'organizador_id' => $this->request->getPost('organizador_id'),
            'nombre' => trim((string) $this->request->getPost('nombre')),
            'descripcion' => trim((string) $this->request->getPost('descripcion')),
            'categoria' => trim((string) $this->request->getPost('categoria')),
            'imagen' => trim((string) $this->request->getPost('imagen')),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function organizadores(): array
    {
        return (new UsuarioModel())
            ->select('usuarios.id, usuarios.nombres, usuarios.apellidos, roles.nombre AS rol_nombre')
            ->join('roles', 'roles.id = usuarios.rol_id')
            ->whereIn('roles.slug', ['administrador', 'organizador'])
            ->where('usuarios.estado', 'activo')
            ->orderBy('usuarios.nombres', 'ASC')
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
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function viewData(array $data): array
    {
        return array_merge([
            'paginaActiva' => 'eventos',
            'usuario' => [
                'nombre' => 'Administrador',
                'rol' => 'Administrador',
            ],
        ], $data);
    }
}
