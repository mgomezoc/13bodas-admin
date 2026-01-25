<?php

namespace App\Controllers;

class Home extends BaseController
{
    /**
     * Página principal
     */
    public function index(): string
    {
        return view('pages/home');
    }

    /**
     * Página de Términos y Condiciones
     */
    public function terminos(): string
    {
        return view('pages/terminos');
    }

    /**
     * Página de Aviso de Privacidad
     */
    public function privacidad(): string
    {
        return view('pages/privacidad');
    }

    /**
     * Página de agradecimiento (después del envío del formulario)
     */
    public function gracias(): string
    {
        return view('pages/gracias');
    }
}
