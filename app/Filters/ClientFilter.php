<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ClientFilter implements FilterInterface
{
    /**
     * Filtro específico para clientes - verifican que accedan solo a su evento
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/login'));
        }

        // Si es admin o superior, tiene acceso a todo
        $userRoles = $session->get('user_roles') ?? [];
        $adminRoles = ['superadmin', 'admin', 'staff'];
        
        foreach ($adminRoles as $role) {
            if (in_array($role, $userRoles)) {
                return; // Tiene acceso total
            }
        }

        // Es cliente - verificar que solo acceda a sus recursos
        $clientId = $session->get('client_id');
        
        // Obtener el event_id de la URL si existe
        $segments = $request->getUri()->getSegments();
        $eventId = null;
        
        // Buscar event_id en la URL (ej: /admin/events/{event_id}/...)
        foreach ($segments as $index => $segment) {
            if ($segment === 'events' && isset($segments[$index + 1])) {
                $eventId = $segments[$index + 1];
                break;
            }
        }

        if ($eventId) {
            // Verificar que el evento pertenezca al cliente
            $db = \Config\Database::connect();
            $event = $db->table('events')
                ->where('id', $eventId)
                ->where('client_id', $clientId)
                ->get()
                ->getRow();

            if (!$event) {
                return redirect()->to(base_url('admin/dashboard'))
                    ->with('error', 'No tienes acceso a este evento.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita
    }
}
