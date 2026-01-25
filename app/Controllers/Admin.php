<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Admin extends BaseController
{
    /**
     * Dashboard principal del admin
     */
    public function dashboard(): string
    {
        $data = [
            'pageTitle' => 'Dashboard',
            'user'      => session()->get('user_name'),
        ];

        return view('admin/dashboard', $data);
    }

    /**
     * Gestión de proyectos/eventos
     */
    public function proyectos(): string
    {
        $data = [
            'pageTitle' => 'Proyectos',
        ];

        return view('admin/proyectos', $data);
    }

    /**
     * Configuración del sitio
     */
    public function configuracion(): string
    {
        $data = [
            'pageTitle' => 'Configuración',
        ];

        return view('admin/configuracion', $data);
    }
}
