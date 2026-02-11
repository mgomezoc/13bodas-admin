<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =============================================================================
// RUTAS PÚBLICAS DEL SITIO PRINCIPAL
// =============================================================================
$routes->get('/', 'Home::index', ['as' => 'home']);
$routes->get('terminos', 'Home::terminos', ['as' => 'legal.terms']);
$routes->get('privacidad', 'Home::privacidad', ['as' => 'legal.privacy']);
$routes->get('gracias', 'Home::gracias', ['as' => 'home.thanks']);


// Alias SEO/marketing para secciones de home (evita 404 por enlaces compartidos)
$routes->get('paquetes', 'Home::index', ['as' => 'home.packages']);
$routes->get('servicios', 'Home::index', ['as' => 'home.services']);
$routes->get('faq', 'Home::index', ['as' => 'home.faq']);
$routes->get('contacto', 'Home::index', ['as' => 'home.contact']);

// Sitemap XML dinámico (SEO)
$routes->get('sitemap.xml', 'Sitemap::index', ['as' => 'seo.sitemap']);

// Login público (clientes)
$routes->get('login', 'Auth::login', ['as' => 'login']);
$routes->post('login', 'Auth::attemptLogin', ['as' => 'login.attempt']);

// =============================================================================
// RUTAS DE AUTENTICACIÓN (Admin)
// =============================================================================
$routes->group('admin', function ($routes) {
    $routes->get('login', 'Admin\Auth::login', ['as' => 'admin.login']);
    $routes->post('login', 'Admin\Auth::attemptLogin', ['as' => 'admin.login.attempt']);
    $routes->get('logout', 'Admin\Auth::logout', ['as' => 'admin.logout']);
});


// Registro público
$routes->get('register', 'Auth::register', ['as' => 'register.index']);
$routes->post('register', 'Auth::processRegister', ['as' => 'register.store']);

// Checkout
$routes->group('checkout', static function ($routes) {
    $routes->get('(:segment)', 'Checkout::index/$1', ['filter' => 'auth', 'as' => 'checkout.index']);
    $routes->post('create-session/(:segment)', 'Checkout::createSession/$1', ['filter' => 'auth', 'as' => 'checkout.create_session']);
    $routes->get('success', 'Checkout::success', ['as' => 'checkout.success']); // SIN FILTRO AUTH - Stripe redirect
    $routes->get('cancel', 'Checkout::cancel', ['filter' => 'auth', 'as' => 'checkout.cancel']);
});

// Webhook Stripe (sin CSRF)
$routes->post('webhooks/stripe', 'Webhooks\StripeWebhook::handle', ['as' => 'webhooks.stripe']);

// =============================================================================
// RUTAS DEL PANEL DE ADMINISTRACIÓN (Protegidas)
// =============================================================================
$routes->group('admin', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('/', 'Admin\Dashboard::index', ['as' => 'admin.dashboard']);
    $routes->get('dashboard', 'Admin\Dashboard::index', ['as' => 'admin.dashboard.alt']);

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
        $routes->get('/', 'Admin\Events::index', ['as' => 'admin.events.index']);
        $routes->get('list', 'Admin\Events::list');
        $routes->get('create', 'Admin\Events::create');
        $routes->post('store', 'Admin\Events::store');
        $routes->get('view/(:segment)', 'Admin\Events::view/$1', ['as' => 'admin.events.view']);
        $routes->get('edit/(:segment)', 'Admin\Events::edit/$1');
        $routes->post('update/(:segment)', 'Admin\Events::update/$1');
        $routes->post('update-settings/(:segment)', 'Admin\Events::updateSettings/$1', ['as' => 'admin.events.update_settings']);
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
        $routes->get('(:segment)/guests/(:segment)/invite-link', 'Admin\Guests::inviteLink/$1/$2', ['as' => 'admin.guests.invite_link']);
        $routes->post('(:segment)/guests/(:segment)/send-invite', 'Admin\Guests::sendInvite/$1/$2', ['as' => 'admin.guests.send_invite']);
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

        // Historia / timeline
        $routes->get('(:segment)/timeline', 'Admin\Timeline::index/$1', ['as' => 'admin.timeline.index']);
        $routes->get('(:segment)/timeline/new', 'Admin\Timeline::new/$1', ['as' => 'admin.timeline.new']);
        $routes->post('(:segment)/timeline', 'Admin\Timeline::create/$1', ['as' => 'admin.timeline.create']);
        $routes->get('(:segment)/timeline/edit/(:segment)', 'Admin\Timeline::edit/$1/$2', ['as' => 'admin.timeline.edit']);
        $routes->post('(:segment)/timeline/update/(:segment)', 'Admin\Timeline::update/$1/$2', ['as' => 'admin.timeline.update']);
        $routes->post('(:segment)/timeline/delete/(:segment)', 'Admin\Timeline::delete/$1/$2', ['as' => 'admin.timeline.delete']);

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

        // Dominios personalizados (solicitud manual)
        $routes->get('(:segment)/domains', 'Admin\EventDomainsController::index/$1', ['as' => 'admin.events.domains.index']);
        $routes->post('(:segment)/domains/request', 'Admin\EventDomainsController::request/$1', ['as' => 'admin.events.domains.request']);
        $routes->post('(:segment)/domains/update', 'Admin\EventDomainsController::update/$1', ['as' => 'admin.events.domains.update']);
        $routes->post('(:segment)/domains/cancel', 'Admin\EventDomainsController::cancel/$1', ['as' => 'admin.events.domains.cancel']);
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
$routes->post('api/leads', 'Api\Leads::store', ['as' => 'api.leads.store']);

// =============================================================================
// RUTAS PÚBLICAS DE INVITACIONES
// =============================================================================
$routes->get('i/(:segment)', 'Invitation::view/$1', ['as' => 'invitation.view']);
$routes->get('i/(:segment)/rsvp', 'Invitation::rsvp/$1', ['as' => 'invitation.rsvp']);
$routes->post('i/(:segment)/rsvp', 'RsvpController::submit/$1', ['as' => 'rsvp.submit']);
$routes->get('i/(:segment)/rsvp/(:segment)', 'Invitation::rsvpWithCode/$1/$2', ['as' => 'invitation.rsvp.code']);
