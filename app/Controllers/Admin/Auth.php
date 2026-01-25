<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ClientModel;

class Auth extends BaseController
{
    /**
     * Mostrar formulario de login
     */
    public function login()
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
    public function attemptLogin()
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

        // Redirigir a URL guardada o al dashboard
        $redirectUrl = session()->get('redirect_url') ?? base_url('admin/dashboard');
        session()->remove('redirect_url');

        return redirect()->to($redirectUrl)
            ->with('success', '¡Bienvenido, ' . ($user['full_name'] ?? $user['email']) . '!');
    }

    /**
     * Cerrar sesión
     */
    public function logout()
    {
        session()->destroy();

        return redirect()->to(base_url('admin/login'))
            ->with('success', 'Has cerrado sesión correctamente.');
    }
}
