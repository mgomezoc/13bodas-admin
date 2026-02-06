<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =============================================================================
// RUTAS PÚBLICAS DEL SITIO PRINCIPAL
// =============================================================================
$routes->get('/', 'Home::index');
$routes->get('terminos', 'Home::terminos');
$routes->get('privacidad', 'Home::privacidad');
$routes->get('gracias', 'Home::gracias');

// Sitemap XML dinámico (SEO)
$routes->get('sitemap.xml', 'Sitemap::index');

// =============================================================================
// RUTAS DE AUTENTICACIÓN (Admin)
// =============================================================================
$routes->group('admin', function ($routes) {
    $routes->get('login', 'Admin\Auth::login');
    $routes->post('login', 'Admin\Auth::attemptLogin');
    $routes->get('logout', 'Admin\Auth::logout');
});

// =============================================================================
// RUTAS DEL PANEL DE ADMINISTRACIÓN (Protegidas)
// =============================================================================
$routes->group('admin', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('/', 'Admin\Dashboard::index');
    $routes->get('dashboard', 'Admin\Dashboard::index');

    // ---------------------------------------------------------------------
    // USUARIOS (Solo admin/superadmin)
    // ---------------------------------------------------------------------
    $routes->group('users', function ($routes) {
        $routes->get('/', 'Admin\Users::index');
        $routes->get('list', 'Admin\Users::list');
        $routes->get('create', 'Admin\Users::create');
        $routes->get('edit/(:segment)', 'Admin\Users::edit/$1');
        $routes->post('save/(:segment)', 'Admin\Users::save/$1');
        $routes->post('save', 'Admin\Users::save');
        $routes->post('toggle-status/(:segment)', 'Admin\Users::toggleStatus/$1');
        $routes->post('delete/(:segment)', 'Admin\Users::delete/$1');
    });

    // ---------------------------------------------------------------------
    // CLIENTES
    // ---------------------------------------------------------------------
    $routes->group('clients', function ($routes) {
        $routes->get('/', 'Admin\Clients::index');
        $routes->get('list', 'Admin\Clients::list');
        $routes->get('create', 'Admin\Clients::create');
        $routes->post('store', 'Admin\Clients::store');
        $routes->get('view/(:segment)', 'Admin\Clients::view/$1');
        $routes->get('edit/(:segment)', 'Admin\Clients::edit/$1');
        $routes->post('update/(:segment)', 'Admin\Clients::update/$1');
        $routes->post('toggle-status/(:segment)', 'Admin\Clients::toggleStatus/$1');
    });

    // ---------------------------------------------------------------------
    // EVENTOS
    // ---------------------------------------------------------------------
    $routes->group('events', function ($routes) {
        $routes->get('/', 'Admin\Events::index');
        $routes->get('list', 'Admin\Events::list');
        $routes->get('create', 'Admin\Events::create');
        $routes->post('store', 'Admin\Events::store');
        $routes->get('view/(:segment)', 'Admin\Events::view/$1');
        $routes->get('edit/(:segment)', 'Admin\Events::edit/$1');
        $routes->post('update/(:segment)', 'Admin\Events::update/$1');
        $routes->post('delete/(:segment)', 'Admin\Events::delete/$1');
        $routes->post('check-slug', 'Admin\Events::checkSlug');
        $routes->get('preview/(:segment)', 'Admin\Events::preview/$1');

        // Invitados del evento
        $routes->get('(:segment)/guests', 'Admin\Guests::index/$1');
        $routes->get('(:segment)/guests/list', 'Admin\Guests::list/$1');
        $routes->get('(:segment)/guests/create', 'Admin\Guests::create/$1');
        $routes->post('(:segment)/guests/store', 'Admin\Guests::store/$1');
        $routes->get('(:segment)/guests/edit/(:segment)', 'Admin\Guests::edit/$1/$2');
        $routes->post('(:segment)/guests/update/(:segment)', 'Admin\Guests::update/$1/$2');
        $routes->post('(:segment)/guests/delete/(:segment)', 'Admin\Guests::delete/$1/$2');
        $routes->get('(:segment)/guests/import', 'Admin\Guests::import/$1');
        $routes->post('(:segment)/guests/process-import', 'Admin\Guests::processImport/$1');
        $routes->get('(:segment)/guests/export', 'Admin\Guests::export/$1');

        // Grupos de invitados
        $routes->get('(:segment)/groups', 'Admin\GuestGroups::index/$1');
        $routes->get('(:segment)/groups/list', 'Admin\GuestGroups::list/$1');
        $routes->post('(:segment)/groups/store', 'Admin\GuestGroups::store/$1');
        $routes->post('(:segment)/groups/update/(:segment)', 'Admin\GuestGroups::update/$1/$2');
        $routes->post('(:segment)/groups/delete/(:segment)', 'Admin\GuestGroups::delete/$1/$2');

        // RSVPs (Confirmaciones)
        $routes->get('(:segment)/rsvp', 'Admin\Rsvp::index/$1');
        $routes->get('(:segment)/rsvp/list', 'Admin\Rsvp::list/$1');
        $routes->get('(:segment)/rsvp/export', 'Admin\Rsvp::export/$1');
        $routes->get('(:segment)/rsvp/export-meals', 'Admin\Rsvp::exportMeals/$1');
        $routes->get('(:segment)/rsvp/export-songs', 'Admin\Rsvp::exportSongs/$1');
        $routes->post('(:segment)/rsvp/update-status/(:segment)', 'Admin\Rsvp::updateStatus/$1/$2');

        // Galería
        $routes->get('(:segment)/gallery', 'Admin\Gallery::index/$1');
        $routes->post('(:segment)/gallery/upload', 'Admin\Gallery::upload/$1');
        $routes->post('(:segment)/gallery/update/(:segment)', 'Admin\Gallery::update/$1/$2');
        $routes->post('(:segment)/gallery/delete/(:segment)', 'Admin\Gallery::delete/$1/$2');
        $routes->post('(:segment)/gallery/reorder', 'Admin\Gallery::reorder/$1');

        // Lista de regalos
        $routes->get('(:segment)/registry', 'Admin\Registry::index/$1');
        $routes->post('(:segment)/registry/store', 'Admin\Registry::store/$1');
        $routes->post('(:segment)/registry/update/(:segment)', 'Admin\Registry::update/$1/$2');
        $routes->post('(:segment)/registry/toggle-claimed/(:segment)', 'Admin\Registry::toggleClaimed/$1/$2');
        $routes->post('(:segment)/registry/delete/(:segment)', 'Admin\Registry::delete/$1/$2');

        // Opciones de menú
        $routes->get('(:segment)/menu', 'Admin\MenuOptions::index/$1');
        $routes->post('(:segment)/menu/store', 'Admin\MenuOptions::store/$1');
        $routes->post('(:segment)/menu/update/(:segment)', 'Admin\MenuOptions::update/$1/$2');
        $routes->post('(:segment)/menu/delete/(:segment)', 'Admin\MenuOptions::delete/$1/$2');

        // Cortejo nupcial
        $routes->get('(:segment)/party', 'Admin\WeddingParty::index/$1');
        $routes->post('(:segment)/party/store', 'Admin\WeddingParty::store/$1');
        $routes->post('(:segment)/party/update/(:segment)', 'Admin\WeddingParty::update/$1/$2');
        $routes->post('(:segment)/party/delete/(:segment)', 'Admin\WeddingParty::delete/$1/$2');

        // Ubicaciones del evento
        $routes->get('(:segment)/locations', 'Admin\EventLocations::index/$1');
        $routes->post('(:segment)/locations/store', 'Admin\EventLocations::store/$1');
        $routes->post('(:segment)/locations/update/(:segment)', 'Admin\EventLocations::update/$1/$2');
        $routes->post('(:segment)/locations/delete/(:segment)', 'Admin\EventLocations::delete/$1/$2');

        // Agenda / cronograma
        $routes->get('(:segment)/schedule', 'Admin\EventSchedule::index/$1');
        $routes->post('(:segment)/schedule/store', 'Admin\EventSchedule::store/$1');
        $routes->post('(:segment)/schedule/update/(:segment)', 'Admin\EventSchedule::update/$1/$2');
        $routes->post('(:segment)/schedule/delete/(:segment)', 'Admin\EventSchedule::delete/$1/$2');

        // FAQ
        $routes->get('(:segment)/faq', 'Admin\EventFaq::index/$1');
        $routes->post('(:segment)/faq/store', 'Admin\EventFaq::store/$1');
        $routes->post('(:segment)/faq/update/(:segment)', 'Admin\EventFaq::update/$1/$2');
        $routes->post('(:segment)/faq/delete/(:segment)', 'Admin\EventFaq::delete/$1/$2');

        // Recomendaciones
        $routes->get('(:segment)/recommendations', 'Admin\EventRecommendations::index/$1');
        $routes->post('(:segment)/recommendations/store', 'Admin\EventRecommendations::store/$1');
        $routes->post('(:segment)/recommendations/update/(:segment)', 'Admin\EventRecommendations::update/$1/$2');
        $routes->post('(:segment)/recommendations/delete/(:segment)', 'Admin\EventRecommendations::delete/$1/$2');

        // Preguntas RSVP personalizadas
        $routes->get('(:segment)/rsvp-questions', 'Admin\RsvpQuestions::index/$1');
        $routes->post('(:segment)/rsvp-questions/store', 'Admin\RsvpQuestions::store/$1');
        $routes->post('(:segment)/rsvp-questions/update/(:segment)', 'Admin\RsvpQuestions::update/$1/$2');
        $routes->post('(:segment)/rsvp-questions/delete/(:segment)', 'Admin\RsvpQuestions::delete/$1/$2');

        // Módulos de contenido
        $routes->get('(:segment)/modules', 'Admin\ContentModules::index/$1');
        $routes->post('(:segment)/modules/update/(:segment)', 'Admin\ContentModules::update/$1/$2');
        $routes->post('(:segment)/modules/reorder', 'Admin\ContentModules::reorder/$1');

        // Dominios personalizados
        $routes->get('(:segment)/domains', 'Admin\EventCustomDomains::index/$1');
        $routes->post('(:segment)/domains/store', 'Admin\EventCustomDomains::store/$1');
        $routes->post('(:segment)/domains/update/(:segment)', 'Admin\EventCustomDomains::update/$1/$2');
        $routes->post('(:segment)/domains/delete/(:segment)', 'Admin\EventCustomDomains::delete/$1/$2');
    });

    // ---------------------------------------------------------------------
    // LEADS
    // ---------------------------------------------------------------------
    $routes->group('leads', function ($routes) {
        $routes->get('/', 'Admin\Leads::index');
        $routes->get('list', 'Admin\Leads::list');
        $routes->get('create', 'Admin\Leads::create');
        $routes->get('edit/(:segment)', 'Admin\Leads::edit/$1');
        $routes->post('save/(:segment)', 'Admin\Leads::save/$1');
        $routes->post('save', 'Admin\Leads::save');
        $routes->get('view/(:segment)', 'Admin\Leads::view/$1');
        $routes->post('update-status/(:segment)', 'Admin\Leads::updateStatus/$1');
        $routes->post('convert/(:segment)', 'Admin\Leads::convert/$1');
        $routes->post('delete/(:segment)', 'Admin\Leads::delete/$1');
    });

    // ---------------------------------------------------------------------
    // TEMPLATES
    // ---------------------------------------------------------------------
    $routes->group('templates', function ($routes) {
        $routes->get('/', 'Admin\Templates::index');
        $routes->get('list', 'Admin\Templates::list');
        $routes->get('create', 'Admin\Templates::create');
        $routes->get('edit/(:num)', 'Admin\Templates::edit/$1');
        $routes->post('save/(:num)', 'Admin\Templates::save/$1');
        $routes->post('save', 'Admin\Templates::save');
        $routes->post('delete/(:num)', 'Admin\Templates::delete/$1');
        $routes->post('toggle-active/(:num)', 'Admin\Templates::toggleActive/$1');
        $routes->post('toggle-public/(:num)', 'Admin\Templates::togglePublic/$1');
    });

    // ---------------------------------------------------------------------
    // PERFIL
    // ---------------------------------------------------------------------
    $routes->get('profile', 'Admin\Profile::index');
    $routes->post('profile/update', 'Admin\Profile::update');
    $routes->post('profile/password', 'Admin\Profile::changePassword');
});

// =============================================================================
// API DE LEADS (Para el formulario público del sitio)
// =============================================================================
$routes->post('api/leads', 'Api\Leads::store');

// =============================================================================
// RUTAS PÚBLICAS DE INVITACIONES
// =============================================================================
$routes->get('i/(:segment)', 'Invitation::view/$1');
$routes->get('i/(:segment)/rsvp', 'Invitation::rsvp/$1');
$routes->post('i/(:segment)/rsvp', 'Invitation::submitRsvp/$1');
$routes->get('i/(:segment)/rsvp/(:segment)', 'Invitation::rsvpWithCode/$1/$2');
