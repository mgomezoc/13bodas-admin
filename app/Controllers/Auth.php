<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    /**
     * Muestra el formulario de login
     */
    public function login()
    {
        // Si ya está logueado, redirigir al dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/dashboard'));
        }
        
        return view('auth/login');
    }

    /**
     * Procesa el intento de login
     */
    public function attemptLogin()
    {
        $validation = \Config\Services::validation();
        
        // Validar los campos
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Por favor, completa todos los campos correctamente.');
        }
        
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
        
        if ($user && password_verify($password, $user['password'])) {
            // Verificar si el usuario está activo
            if ($user['is_active'] != 1) {
                return redirect()->back()
                    ->with('error', 'Tu cuenta está desactivada. Contacta al administrador.');
            }
            
            // Crear la sesión
            $sessionData = [
                'user_id'    => $user['id'],
                'user_name'  => $user['name'],
                'user_email' => $user['email'],
                'user_role'  => $user['role'],
                'isLoggedIn' => true
            ];
            
            session()->set($sessionData);
            
            // Actualizar último login
            $userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            
            return redirect()->to(base_url('admin/dashboard'))
                ->with('success', '¡Bienvenido de vuelta, ' . $user['name'] . '!');
        }
        
        return redirect()->back()
            ->withInput()
            ->with('error', 'Credenciales incorrectas. Verifica tu email y contraseña.');
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('auth/login'))
            ->with('success', 'Has cerrado sesión exitosamente.');
    }
}
