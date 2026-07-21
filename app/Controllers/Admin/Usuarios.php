<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RolModel;
use App\Models\UsuarioModel;
use App\Services\PasswordPolicy;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Usuarios extends BaseController
{
    public function index(): string
    {
        $usuarios = (new UsuarioModel())
            ->select('usuarios.*, roles.nombre AS rol_nombre, roles.slug AS rol_slug')
            ->join('roles', 'roles.id = usuarios.rol_id')
            ->orderBy('usuarios.nombres', 'ASC')
            ->findAll();

        return view('admin/usuarios/index', $this->viewData([
            'titulo' => 'Usuarios y roles',
            'usuarios' => $usuarios,
            'usuarioActualId' => (int) session('usuario')['id'],
        ]));
    }

    public function new(): string
    {
        return view('admin/usuarios/form', $this->viewData([
            'titulo' => 'Crear usuario',
            'usuarioEditado' => null,
            'roles' => $this->rolesDisponibles(),
            'accion' => base_url('admin/usuarios'),
        ]));
    }

    public function create(): RedirectResponse
    {
        $password = (string) $this->request->getPost('password');

        if (($errors = (new PasswordPolicy())->validate($password, (string) $this->request->getPost('password_confirm'))) !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data = $this->userData();
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);

        $usuarioModel = new UsuarioModel();

        if (! $usuarioModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $usuarioModel->errors());
        }

        return redirect()->to(base_url('admin/usuarios'))->with('success', 'El usuario fue creado.');
    }

    public function edit(int $id): string
    {
        return view('admin/usuarios/form', $this->viewData([
            'titulo' => 'Editar usuario',
            'usuarioEditado' => $this->findUsuario($id),
            'roles' => $this->rolesDisponibles(),
            'accion' => base_url("admin/usuarios/{$id}"),
        ]));
    }

    public function update(int $id): RedirectResponse
    {
        $this->findUsuario($id);
        $data = $this->userData();
        $data['id'] = $id;

        if ($id === (int) session('usuario')['id'] && $data['estado'] !== 'activo') {
            return redirect()->back()->withInput()->with('error', 'No puedes desactivar o bloquear tu propia cuenta.');
        }

        if ($id === (int) session('usuario')['id']) {
            $rol = (new RolModel())->find((int) $data['rol_id']);

            if ($rol === null || $rol['slug'] !== 'administrador') {
                return redirect()->back()->withInput()->with('error', 'No puedes quitarte tu propio rol de Administrador.');
            }
        }

        $usuarioModel = new UsuarioModel();

        if (! $usuarioModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $usuarioModel->errors());
        }

        $this->refreshCurrentUserSession($id);

        return redirect()->to(base_url('admin/usuarios'))->with('success', 'El usuario fue actualizado.');
    }

    public function password(int $id): string
    {
        return view('admin/usuarios/password', $this->viewData([
            'titulo' => 'Cambiar contraseña',
            'usuarioEditado' => $this->findUsuario($id),
            'accion' => base_url("admin/usuarios/{$id}/password"),
        ]));
    }

    public function updatePassword(int $id): RedirectResponse
    {
        $this->findUsuario($id);
        $password = (string) $this->request->getPost('password');

        if (($errors = (new PasswordPolicy())->validate($password, (string) $this->request->getPost('password_confirm'))) !== []) {
            return redirect()->back()->with('errors', $errors);
        }

        (new UsuarioModel())->update($id, [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return redirect()->to(base_url('admin/usuarios'))->with('success', 'La contraseña fue actualizada.');
    }

    public function toggleBlock(int $id): RedirectResponse
    {
        $usuario = $this->findUsuario($id);

        if ($id === (int) session('usuario')['id']) {
            return redirect()->to(base_url('admin/usuarios'))->with('error', 'No puedes bloquear tu propia cuenta.');
        }

        $nuevoEstado = $usuario['estado'] === 'bloqueado' ? 'activo' : 'bloqueado';
        (new UsuarioModel())->update($id, ['estado' => $nuevoEstado]);

        $mensaje = $nuevoEstado === 'bloqueado' ? 'El usuario fue bloqueado.' : 'El usuario fue desbloqueado.';

        return redirect()->to(base_url('admin/usuarios'))->with('success', $mensaje);
    }

    public function delete(int $id): RedirectResponse
    {
        $this->findUsuario($id);

        if ($id === (int) session('usuario')['id']) {
            return redirect()->to(base_url('admin/usuarios'))->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        (new UsuarioModel())->delete($id);

        return redirect()->to(base_url('admin/usuarios'))->with('success', 'El usuario fue eliminado.');
    }

    /** @return array<string, mixed> */
    private function userData(): array
    {
        return [
            'rol_id' => $this->request->getPost('rol_id'),
            'nombres' => trim((string) $this->request->getPost('nombres')),
            'apellidos' => trim((string) $this->request->getPost('apellidos')),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'telefono' => trim((string) $this->request->getPost('telefono')),
            'estado' => $this->request->getPost('estado') ?: 'activo',
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function rolesDisponibles(): array
    {
        return (new RolModel())->where('activo', 1)->orderBy('nombre', 'ASC')->findAll();
    }

    /** @return array<string, mixed> */
    private function findUsuario(int $id): array
    {
        $usuario = (new UsuarioModel())->find($id);

        if ($usuario === null) {
            throw PageNotFoundException::forPageNotFound('El usuario solicitado no existe.');
        }

        return $usuario;
    }

    private function refreshCurrentUserSession(int $id): void
    {
        if ($id !== (int) session('usuario')['id']) {
            return;
        }

        $usuario = (new UsuarioModel())
            ->select('usuarios.*, roles.nombre AS rol_nombre, roles.slug AS rol_slug')
            ->join('roles', 'roles.id = usuarios.rol_id')
            ->where('usuarios.id', $id)
            ->first();

        session()->set('usuario', [
            'id' => (int) $usuario['id'],
            'nombre' => trim($usuario['nombres'] . ' ' . $usuario['apellidos']),
            'email' => $usuario['email'],
            'rol' => $usuario['rol_nombre'],
            'rol_slug' => $usuario['rol_slug'],
        ]);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function viewData(array $data): array
    {
        return array_merge(['paginaActiva' => 'usuarios', 'usuario' => session('usuario')], $data);
    }
}
