<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ArtistaModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * CRUD académico del módulo Artistas.
 *
 * Cada mal olor tiene inmediatamente debajo un reemplazo completo comentado.
 * Para la exposición se deben aplicar en orden, del 1 al 6.
 */
class Artistas extends BaseController
{


    // ------------------------------------------------------------------
    // REFACTORIZACIÓN 1: NOMBRES EXPRESIVOS
    // Elimina el index() anterior y descomenta este index() completo.
    // ------------------------------------------------------------------
    public function index(): string
    {
        $artistas = $this->artistaModel()
            ->orderBy('created_at', 'DESC')
            ->findAll();

        foreach ($artistas as &$artista) {
            $artista['tipo_etiqueta'] = $this->tipoEtiqueta((string) $artista['tipo']);
            $artista['descripcion_resumen'] =
                $this->resumirDescripcion((string) $artista['descripcion']);
        }
        unset($artista);

        return view('admin/artistas/index', $this->viewData([
            'titulo' => 'Artistas',
            'artistas' => $artistas,
        ]));
    }


    // ------------------------------------------------------------------
    // REFACTORIZACIÓN 2: EXPRESIÓN MATCH
    // Elimina tipoEtiqueta() anterior y descomenta este método completo.
    // ------------------------------------------------------------------
    private function tipoEtiqueta(string $tipo): string
    {
        return match ($tipo) {
            'solista' => 'Solista',
            'banda' => 'Banda',
            'duo' => 'Dúo',
            'orquesta' => 'Orquesta',
            default => 'Otro',
        };
    }


    // ------------------------------------------------------------------
    // REFACTORIZACIÓN 3: EXTRAER MÉTODO
    // Elimina create() anterior y descomenta estos dos métodos completos.
    // ------------------------------------------------------------------
    public function create(): RedirectResponse
    {
        $artistaModel = $this->artistaModel();

        if (! $artistaModel->insert($this->datosFormulario())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $artistaModel->errors());
        }

        return redirect()->to(base_url('admin/artistas'))
            ->with('success', 'El artista fue creado.');
    }

    private function datosFormulario(): array
    {
        return [
            'nombre_artistico' => trim((string) $this->request->getPost('nombre_artistico')),
            'nombre_real' => trim((string) $this->request->getPost('nombre_real')),
            'tipo' => trim((string) $this->request->getPost('tipo')),
            'genero' => trim((string) $this->request->getPost('genero')),
            'pais' => trim((string) $this->request->getPost('pais')),
            'descripcion' => trim((string) $this->request->getPost('descripcion')),
            'imagen' => trim((string) $this->request->getPost('imagen')),
            'estado' => trim((string) $this->request->getPost('estado')),
        ];
    }


    // ------------------------------------------------------------------
    // REFACTORIZACIÓN 4: REUTILIZAR datosFormulario()
    // Primero aplica la refactorización 3. Después elimina update()
    // anterior y descomenta este update() completo.
    // ------------------------------------------------------------------
    public function update(int $id): RedirectResponse
    {
        $this->findArtista($id);
        $data = $this->datosFormulario();
        $data['id'] = $id;
        $artistaModel = $this->artistaModel();

        if (! $artistaModel->update($id, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $artistaModel->errors());
        }

        return redirect()->to(base_url('admin/artistas'))
            ->with('success', 'El artista fue actualizado.');
    }


    // ------------------------------------------------------------------
    // REFACTORIZACIÓN 5: INYECCIÓN DE DEPENDENCIA
    // Elimina artistaModel() anterior y descomenta este bloque completo.
    // Las demás funciones no necesitan cambios.
    // ------------------------------------------------------------------
    private ArtistaModel $artistaModel;

    public function __construct(?ArtistaModel $artistaModel = null)
    {
        $this->artistaModel = $artistaModel ?? new ArtistaModel();
    }

    private function artistaModel(): ArtistaModel
    {
        return $this->artistaModel;
    }


    // ------------------------------------------------------------------
    // REFACTORIZACIÓN 6: CONSTANTE CON NOMBRE
    // Elimina resumirDescripcion() anterior y descomenta el bloque completo.
    // ------------------------------------------------------------------
    private const LIMITE_RESUMEN_DESCRIPCION = 80;

    private function resumirDescripcion(string $descripcion): string
    {
        if (mb_strlen($descripcion) <= self::LIMITE_RESUMEN_DESCRIPCION) {
            return $descripcion;
        }

        return mb_substr(
            $descripcion,
            0,
            self::LIMITE_RESUMEN_DESCRIPCION,
        ) . '…';
    }

    // ==================================================================
    // RESTO DEL CRUD: métodos de apoyo sin olores de la demostración.
    // ==================================================================
    public function new(): string
    {
        return view('admin/artistas/form', $this->viewData([
            'titulo' => 'Crear artista',
            'artista' => null,
            'accion' => base_url('admin/artistas'),
        ]));
    }

    public function edit(int $id): string
    {
        return view('admin/artistas/form', $this->viewData([
            'titulo' => 'Editar artista',
            'artista' => $this->findArtista($id),
            'accion' => base_url("admin/artistas/{$id}"),
        ]));
    }

    public function delete(int $id): RedirectResponse
    {
        $this->findArtista($id);
        $this->artistaModel()->delete($id);

        return redirect()->to(base_url('admin/artistas'))
            ->with('success', 'El artista fue eliminado.');
    }

    /** @return array<string, mixed> */
    private function findArtista(int $id): array
    {
        $artista = $this->artistaModel()->find($id);

        if ($artista === null) {
            throw PageNotFoundException::forPageNotFound('El artista solicitado no existe.');
        }

        return $artista;
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function viewData(array $data): array
    {
        return array_merge([
            'paginaActiva' => 'artistas',
            'usuario' => [
                'nombre' => 'Administrador',
                'rol' => 'Administrador',
            ],
        ], $data);
    }
}
