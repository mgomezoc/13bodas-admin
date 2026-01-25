<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =============================================================================
// RUTAS PÚBLICAS
// =============================================================================
$routes->get('/', 'Home::index');
$routes->get('terminos', 'Home::terminos');
$routes->get('privacidad', 'Home::privacidad');
$routes->get('gracias', 'Home::gracias');

// =============================================================================
// RUTAS DE AUTENTICACIÓN
// =============================================================================
$routes->group('auth', function ($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::attemptLogin');
    $routes->get('logout', 'Auth::logout');
});

// =============================================================================
// RUTAS DE ADMINISTRACIÓN (Protegidas por filtro de autenticación)
// =============================================================================
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Admin\Dashboard::index');
    $routes->get('dashboard', 'Admin\Dashboard::index');
    
    // Gestión de contactos/leads
    $routes->get('leads', 'Admin\Leads::index');
    $routes->get('leads/(:num)', 'Admin\Leads::view/$1');
    $routes->post('leads/status/(:num)', 'Admin\Leads::updateStatus/$1');
    
    // Gestión de proyectos
    $routes->get('proyectos', 'Admin\Proyectos::index');
    $routes->get('proyectos/nuevo', 'Admin\Proyectos::create');
    $routes->post('proyectos/nuevo', 'Admin\Proyectos::store');
    $routes->get('proyectos/(:num)', 'Admin\Proyectos::view/$1');
    $routes->get('proyectos/editar/(:num)', 'Admin\Proyectos::edit/$1');
    $routes->post('proyectos/editar/(:num)', 'Admin\Proyectos::update/$1');
    $routes->delete('proyectos/(:num)', 'Admin\Proyectos::delete/$1');
    
    // Configuración
    $routes->get('configuracion', 'Admin\Configuracion::index');
    $routes->post('configuracion', 'Admin\Configuracion::update');
    
    // Perfil del usuario admin
    $routes->get('perfil', 'Admin\Perfil::index');
    $routes->post('perfil', 'Admin\Perfil::update');
    $routes->post('perfil/password', 'Admin\Perfil::changePassword');
});
