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
    // REFACTORIZACIÓN 1: NOMBRES EXPRESIVOS
    // Reemplaza nombres ambiguos por términos propios del dominio.
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

    // ==================================================================
    // MAL OLOR 2: CONDICIONAL LARGA
    // Problema: agregar un tipo obliga a añadir otro elseif.
    // ==================================================================
    private function tipoEtiqueta(string $tipo): string
    {
        if ($tipo === 'solista') {
            return 'Solista';
        } elseif ($tipo === 'banda') {
            return 'Banda';
        } elseif ($tipo === 'duo') {
            return 'Dúo';
        } elseif ($tipo === 'orquesta') {
            return 'Orquesta';
        } else {
            return 'Otro';
        }
    }

    // ------------------------------------------------------------------
    // REFACTORIZACIÓN 2: EXPRESIÓN MATCH
    // Elimina tipoEtiqueta() anterior y descomenta este método completo.
    // ------------------------------------------------------------------
    // private function tipoEtiqueta(string $tipo): string
    // {
    //     return match ($tipo) {
    //         'solista' => 'Solista',
    //         'banda' => 'Banda',
    //         'duo' => 'Dúo',
    //         'orquesta' => 'Orquesta',
    //         default => 'Otro',
    //     };
    // }

    // ==================================================================
    // MAL OLOR 3: MÉTODO LARGO
    // Problema: create() lee la petición, arma datos, guarda y responde.
    // ==================================================================
    public function create(): RedirectResponse
    {
        $data = [
            'nombre_artistico' => trim((string) $this->request->getPost('nombre_artistico')),
            'nombre_real' => trim((string) $this->request->getPost('nombre_real')),
            'tipo' => trim((string) $this->request->getPost('tipo')),
            'genero' => trim((string) $this->request->getPost('genero')),
            'pais' => trim((string) $this->request->getPost('pais')),
            'descripcion' => trim((string) $this->request->getPost('descripcion')),
            'imagen' => trim((string) $this->request->getPost('imagen')),
            'estado' => trim((string) $this->request->getPost('estado')),
        ];

        $artistaModel = $this->artistaModel();

        if (! $artistaModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $artistaModel->errors());
        }

        return redirect()->to(base_url('admin/artistas'))
            ->with('success', 'El artista fue creado.');
    }

    // ------------------------------------------------------------------
    // REFACTORIZACIÓN 3: EXTRAER MÉTODO
    // Elimina create() anterior y descomenta estos dos métodos completos.
    // ------------------------------------------------------------------
    // public function create(): RedirectResponse
    // {
    //     $artistaModel = $this->artistaModel();
    //
    //     if (! $artistaModel->insert($this->datosFormulario())) {
    //         return redirect()->back()
    //             ->withInput()
    //             ->with('errors', $artistaModel->errors());
    //     }
    //
    //     return redirect()->to(base_url('admin/artistas'))
    //         ->with('success', 'El artista fue creado.');
    // }
    //
    // private function datosFormulario(): array
    // {
    //     return [
    //         'nombre_artistico' => trim((string) $this->request->getPost('nombre_artistico')),
    //         'nombre_real' => trim((string) $this->request->getPost('nombre_real')),
    //         'tipo' => trim((string) $this->request->getPost('tipo')),
    //         'genero' => trim((string) $this->request->getPost('genero')),
    //         'pais' => trim((string) $this->request->getPost('pais')),
    //         'descripcion' => trim((string) $this->request->getPost('descripcion')),
    //         'imagen' => trim((string) $this->request->getPost('imagen')),
    //         'estado' => trim((string) $this->request->getPost('estado')),
    //     ];
    // }

    // ==================================================================
    // MAL OLOR 4: CÓDIGO DUPLICADO
    // Problema: update() repite la construcción de datos de create().
    // ==================================================================
    public function update(int $id): RedirectResponse
    {
        $this->findArtista($id);

        $data = [
            'id' => $id,
            'nombre_artistico' => trim((string) $this->request->getPost('nombre_artistico')),
            'nombre_real' => trim((string) $this->request->getPost('nombre_real')),
            'tipo' => trim((string) $this->request->getPost('tipo')),
            'genero' => trim((string) $this->request->getPost('genero')),
            'pais' => trim((string) $this->request->getPost('pais')),
            'descripcion' => trim((string) $this->request->getPost('descripcion')),
            'imagen' => trim((string) $this->request->getPost('imagen')),
            'estado' => trim((string) $this->request->getPost('estado')),
        ];

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
    // REFACTORIZACIÓN 4: REUTILIZAR datosFormulario()
    // Primero aplica la refactorización 3. Después elimina update()
    // anterior y descomenta este update() completo.
    // ------------------------------------------------------------------
    // public function update(int $id): RedirectResponse
    // {
    //     $this->findArtista($id);
    //     $data = $this->datosFormulario();
    //     $data['id'] = $id;
    //     $artistaModel = $this->artistaModel();
    //
    //     if (! $artistaModel->update($id, $data)) {
    //         return redirect()->back()
    //             ->withInput()
    //             ->with('errors', $artistaModel->errors());
    //     }
    //
    //     return redirect()->to(base_url('admin/artistas'))
    //         ->with('success', 'El artista fue actualizado.');
    // }

    // ==================================================================
    // MAL OLOR 5: DEPENDENCIA CREADA DIRECTAMENTE
    // Problema: el controlador decide siempre qué modelo concreto construir.
    // ==================================================================
    private function artistaModel(): ArtistaModel
    {
        return new ArtistaModel();
    }

    // ------------------------------------------------------------------
    // REFACTORIZACIÓN 5: INYECCIÓN DE DEPENDENCIA
    // Elimina artistaModel() anterior y descomenta este bloque completo.
    // Las demás funciones no necesitan cambios.
    // ------------------------------------------------------------------
    // private ArtistaModel $artistaModel;
    //
    // public function __construct(?ArtistaModel $artistaModel = null)
    // {
    //     $this->artistaModel = $artistaModel ?? new ArtistaModel();
    // }
    //
    // private function artistaModel(): ArtistaModel
    // {
    //     return $this->artistaModel;
    // }

    // ==================================================================
    // MAL OLOR 6: NÚMERO MÁGICO
    // Problema: 80 no explica qué limita y queda oculto dentro del método.
    // ==================================================================
    private function resumirDescripcion(string $descripcion): string
    {
        if (mb_strlen($descripcion) <= 80) {
            return $descripcion;
        }

        return mb_substr($descripcion, 0, 80) . '…';
    }

    // ------------------------------------------------------------------
    // REFACTORIZACIÓN 6: CONSTANTE CON NOMBRE
    // Elimina resumirDescripcion() anterior y descomenta el bloque completo.
    // ------------------------------------------------------------------
    // private const LIMITE_RESUMEN_DESCRIPCION = 80;
    //
    // private function resumirDescripcion(string $descripcion): string
    // {
    //     if (mb_strlen($descripcion) <= self::LIMITE_RESUMEN_DESCRIPCION) {
    //         return $descripcion;
    //     }
    //
    //     return mb_substr(
    //         $descripcion,
    //         0,
    //         self::LIMITE_RESUMEN_DESCRIPCION,
    //     ) . '…';
    // }

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
