<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class AuthFilter implements FilterInterface
{
    /**
     * Verifica si el usuario está autenticado
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Si no hay sesión activa, redirigir al login
        if (!$session->get('isLoggedIn')) {
            // Guardar la URL actual para redirigir después del login
            $session->set('redirect_url', current_url());
            
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        // Verificar si el usuario sigue activo en la BD
        $userModel = new UserModel();
        $user = $userModel->find($session->get('user_id'));
        
        if (!$user || !$user['is_active']) {
            $session->destroy();
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'Tu cuenta ha sido desactivada.');
        }

        // Verificar roles permitidos si se especificaron
        if ($arguments) {
            $userRoles = array_column($userModel->getUserRoles($session->get('user_id')), 'name');
            $allowedRoles = is_array($arguments) ? $arguments : [$arguments];
            
            $hasAccess = false;
            foreach ($allowedRoles as $role) {
                if (in_array($role, $userRoles)) {
                    $hasAccess = true;
                    break;
                }
            }
            
            if (!$hasAccess) {
                return redirect()->to(base_url('admin/dashboard'))
                    ->with('error', 'No tienes permisos para acceder a esta sección.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita acción después de la respuesta
    }
}
