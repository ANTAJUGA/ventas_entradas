<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    public function login(): string|RedirectResponse
    {
        if (session('isLoggedIn')) {
            return redirect()->to(base_url('admin'));
        }

        return view('auth/login', ['titulo' => 'Iniciar sesión']);
    }

    public function authenticate(): RedirectResponse
    {
        $credentials = [
            'email' => trim(strtolower((string) $this->request->getPost('email'))),
            'password' => (string) $this->request->getPost('password'),
        ];

        if (! $this->validateData($credentials, [
            'email' => 'required|valid_email|max_length[190]',
            'password' => 'required|max_length[255]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $usuario = (new UsuarioModel())
            ->select('usuarios.*, roles.nombre AS rol_nombre, roles.slug AS rol_slug')
            ->join('roles', 'roles.id = usuarios.rol_id')
            ->where('usuarios.email', $credentials['email'])
            ->first();

        if ($usuario === null || ! password_verify($credentials['password'], $usuario['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'El correo o la contraseña son incorrectos.');
        }

        if ($usuario['estado'] !== 'activo') {
            return redirect()->back()->withInput()->with('error', 'La cuenta se encuentra inactiva o bloqueada.');
        }

        $session = session();
        $session->regenerate(true);
        $session->set([
            'isLoggedIn' => true,
            'usuario' => [
                'id' => (int) $usuario['id'],
                'nombre' => trim($usuario['nombres'] . ' ' . $usuario['apellidos']),
                'email' => $usuario['email'],
                'rol' => $usuario['rol_nombre'],
                'rol_slug' => $usuario['rol_slug'],
            ],
        ]);

        (new UsuarioModel())->update($usuario['id'], ['ultimo_acceso' => date('Y-m-d H:i:s')]);

        $destination = $session->get('intendedUrl') ?: base_url('admin');
        $session->remove('intendedUrl');

        return redirect()->to($destination)->with('success', 'Bienvenido al panel administrativo.');
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();

        return redirect()->to(base_url('login'))->with('success', 'La sesión fue cerrada correctamente.');
    }
}
