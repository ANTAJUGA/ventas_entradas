<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    /**
     * @param array<string>|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            $session->set('intendedUrl', current_url());

            return redirect()->to(base_url('login'))->with('error', 'Inicia sesión para acceder al panel.');
        }

        $allowedRoles = $arguments ?: ['administrador', 'organizador', 'vendedor', 'control-acceso'];
        $usuario = $session->get('usuario');

        if (! is_array($usuario) || ! in_array($usuario['rol_slug'] ?? null, $allowedRoles, true)) {
            return redirect()->to(base_url('/'))->with('error', 'No tienes permisos para acceder al panel administrativo.');
        }

        return null;
    }

    /**
     * @param array<string>|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
