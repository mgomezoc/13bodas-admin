<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Verifica si el usuario está autenticado antes de permitir acceso al admin
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Si no hay sesión de admin, redirigir al login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('auth/login'))->with('error', 'Debes iniciar sesión para acceder al panel de administración.');
        }
    }

    /**
     * No se necesita hacer nada después de la respuesta
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita acción después de la respuesta
    }
}
