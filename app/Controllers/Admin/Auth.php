<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ClientOnboardingService;
use App\Models\UserModel;
use App\Models\ClientModel;

class Auth extends BaseController
{
    public function __construct(private readonly ClientOnboardingService $clientOnboardingService = new ClientOnboardingService())
    {
    }
    /**
     * Mostrar formulario de login
     */
    public function login(): string|\CodeIgniter\HTTP\ResponseInterface
    {
        // Si ya está logueado, redirigir al dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        return view('admin/auth/login');
    }

    /**
     * Procesar intento de login
     */
    public function attemptLogin(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Por favor, ingresa un email y contraseña válidos.');
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Credenciales incorrectas.');
        }

        if (!password_verify($password, $user['password_hash'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Credenciales incorrectas.');
        }

        if (!$user['is_active']) {
            return redirect()->back()
                ->with('error', 'Tu cuenta está desactivada. Contacta al administrador.');
        }

        // Obtener roles del usuario
        $roles = $userModel->getUserRoles($user['id']);
        $roleNames = array_column($roles, 'name');

        // Verificar si es cliente y obtener su client_id
        $clientId = null;
        $clientModel = new ClientModel();
        $client = $clientModel->getByUserId($user['id']);
        if ($client) {
            $clientId = $client['id'];
        }

        // Crear sesión
        $sessionData = [
            'user_id'     => $user['id'],
            'user_email'  => $user['email'],
            'user_name'   => $user['full_name'] ?? $user['email'],
            'user_roles'  => $roleNames,
            'client_id'   => $clientId,
            'isLoggedIn'  => true
        ];

        session()->set($sessionData);

        // Actualizar último login
        $userModel->updateLastLogin($user['id']);

        // Redirigir a URL guardada o flujo de onboarding cliente (selección de template)
        $redirectUrl = session()->get('redirect_url');
        session()->remove('redirect_url');

        if (empty($redirectUrl)) {
            $redirectUrl = $this->clientOnboardingService->resolvePostLoginRedirect($roleNames, $clientId)
                ?? base_url('admin/dashboard');
        }

        return redirect()->to($redirectUrl)
            ->with('success', '¡Bienvenido, ' . ($user['full_name'] ?? $user['email']) . '!');
    }

    /**
     * Cerrar sesión
     */
    public function logout(): \CodeIgniter\HTTP\ResponseInterface
    {
        session()->destroy();

        return redirect()->to(base_url('admin/login'))
            ->with('success', 'Has cerrado sesión correctamente.');
    }
}
