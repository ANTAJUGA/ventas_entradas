<?php

namespace App\Controllers;

use App\Models\RolModel;
use App\Models\UsuarioModel;
use App\Services\PasswordPolicy;
use CodeIgniter\HTTP\RedirectResponse;

class ClienteAuth extends BaseController
{
    public function login(): string|RedirectResponse
    {
        if (session('isLoggedIn') && (session('usuario')['rol_slug'] ?? '') === 'cliente') {
            return redirect()->to(base_url('mi-cuenta/entradas'));
        }
        return view('public/auth/login', ['titulo' => 'Ingresar']);
    }

    public function authenticate(): RedirectResponse
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $usuario = (new UsuarioModel())->select('usuarios.*, roles.nombre AS rol_nombre, roles.slug AS rol_slug')->join('roles', 'roles.id = usuarios.rol_id')->where('usuarios.email', $email)->first();

        if ($usuario === null || $usuario['rol_slug'] !== 'cliente' || ! password_verify((string) $this->request->getPost('password'), $usuario['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'El correo o la contraseña son incorrectos.');
        }
        if ($usuario['estado'] !== 'activo') {
            return redirect()->back()->withInput()->with('error', 'La cuenta se encuentra inactiva o bloqueada.');
        }

        $this->startSession($usuario);
        (new UsuarioModel())->update($usuario['id'], ['ultimo_acceso' => date('Y-m-d H:i:s')]);
        $destination = session('clientIntendedUrl') ?: base_url('mi-cuenta/entradas');
        session()->remove('clientIntendedUrl');

        return redirect()->to($destination);
    }

    public function register(): string
    {
        return view('public/auth/register', ['titulo' => 'Crear cuenta']);
    }

    public function store(): RedirectResponse
    {
        $password = (string) $this->request->getPost('password');
        $errors = (new PasswordPolicy())->validate($password, (string) $this->request->getPost('password_confirm'));
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $rol = (new RolModel())->where('slug', 'cliente')->where('activo', 1)->first();
        if ($rol === null) {
            return redirect()->back()->withInput()->with('error', 'El registro de clientes no está disponible.');
        }

        $model = new UsuarioModel();
        $data = ['rol_id' => $rol['id'], 'nombres' => trim((string) $this->request->getPost('nombres')), 'apellidos' => trim((string) $this->request->getPost('apellidos')), 'email' => strtolower(trim((string) $this->request->getPost('email'))), 'telefono' => trim((string) $this->request->getPost('telefono')), 'estado' => 'activo', 'password_hash' => password_hash($password, PASSWORD_DEFAULT)];
        if (! $model->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        $usuario = $model->select('usuarios.*, roles.nombre AS rol_nombre, roles.slug AS rol_slug')->join('roles', 'roles.id = usuarios.rol_id')->where('usuarios.id', $model->getInsertID())->first();
        $this->startSession($usuario);

        return redirect()->to(base_url('mi-cuenta/entradas'))->with('success', 'Tu cuenta fue creada correctamente.');
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();
        return redirect()->to(base_url('/'));
    }

    /** @param array<string, mixed> $usuario */
    private function startSession(array $usuario): void
    {
        session()->regenerate(true);
        session()->set(['isLoggedIn' => true, 'usuario' => ['id' => (int) $usuario['id'], 'nombre' => trim($usuario['nombres'] . ' ' . $usuario['apellidos']), 'email' => $usuario['email'], 'rol' => $usuario['rol_nombre'], 'rol_slug' => $usuario['rol_slug']]]);
    }
}
