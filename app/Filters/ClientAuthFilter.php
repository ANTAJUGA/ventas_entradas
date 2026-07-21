<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ClientAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $session = session();
        $usuario = $session->get('usuario');

        if (! $session->get('isLoggedIn') || ! is_array($usuario) || ($usuario['rol_slug'] ?? null) !== 'cliente') {
            $session->set('clientIntendedUrl', current_url());

            return redirect()->to(base_url('ingresar'))->with('error', 'Inicia sesión como cliente para continuar.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
