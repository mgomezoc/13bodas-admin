<?php declare(strict_types=1); ?>
<?php
/**
 * Navegación por tabs del módulo de eventos.
 *
 * Uso:
 * <?= view('admin/events/partials/_event_navigation', ['active' => 'informacion', 'event_id' => $event['id']]) ?>
 */
$active = $active ?? '';
$eventId = $event_id ?? null;
$session = session();
$userRoles = $session->get('user_roles') ?? [];
$isAdmin = in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true);
$isClient = in_array('client', $userRoles, true) && !$isAdmin;

$tabs = [
    'informacion' => ['icon' => 'fa-solid fa-circle-info', 'label' => 'Información', 'priority' => 1, 'url' => base_url("admin/events/edit/{$eventId}")],
    'invitados' => ['icon' => 'fa-solid fa-users', 'label' => 'Invitados', 'priority' => 1, 'url' => base_url("admin/events/{$eventId}/guests")],
    'grupos' => ['icon' => 'fa-solid fa-layer-group', 'label' => 'Grupos', 'priority' => 1, 'url' => base_url("admin/events/{$eventId}/groups")],
    'confirmaciones' => ['icon' => 'fa-solid fa-circle-check', 'label' => 'Confirmaciones', 'priority' => 1, 'url' => base_url("admin/events/{$eventId}/rsvp")],
    'galeria' => ['icon' => 'fa-solid fa-image', 'label' => 'Galería', 'priority' => 2, 'url' => base_url("admin/events/{$eventId}/gallery")],
    'regalos' => ['icon' => 'fa-solid fa-gift', 'label' => 'Regalos', 'priority' => 2, 'url' => base_url("admin/events/{$eventId}/registry")],
    'menu' => ['icon' => 'fa-solid fa-utensils', 'label' => 'Menú', 'priority' => 2, 'url' => base_url("admin/events/{$eventId}/menu")],
    'cortejo' => ['icon' => 'fa-solid fa-heart', 'label' => 'Cortejo', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/party")],
    'ubicaciones' => ['icon' => 'fa-solid fa-location-dot', 'label' => 'Ubicaciones', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/locations")],
    'agenda' => ['icon' => 'fa-solid fa-calendar-days', 'label' => 'Agenda', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/schedule")],
    'timeline' => ['icon' => 'fa-solid fa-timeline', 'label' => 'Historia', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/timeline")],
    'faq' => ['icon' => 'fa-solid fa-circle-question', 'label' => 'FAQ', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/faq")],
    'recomendaciones' => ['icon' => 'fa-solid fa-star', 'label' => 'Recomendaciones', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/recommendations")],
    'preguntas-rsvp' => ['icon' => 'fa-solid fa-list-check', 'label' => 'Preguntas RSVP', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/rsvp-questions")],
    'modulos' => ['icon' => 'fa-solid fa-table-cells-large', 'label' => 'Módulos', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/modules")],
];

if ($isAdmin || $isClient) {
    $tabs['dominios'] = ['icon' => 'fa-solid fa-globe', 'label' => 'Dominios', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/domains")];
}

if ($isClient) {
    unset($tabs['recomendaciones'], $tabs['preguntas-rsvp'], $tabs['modulos']);
}

$orderedTabs = array_filter(
    $tabs,
    static fn(array $tab): bool => $tab['priority'] >= 1
);
?>

<div class="event-tabs-wrapper">
    <ul class="nav nav-tabs event-nav" id="eventMainTabs" role="tablist">
        <?php foreach ($orderedTabs as $key => $tab): ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $active === $key ? 'active' : '' ?>"
                   href="<?= esc($tab['url']) ?>"
                   data-section="<?= esc($key) ?>">
                    <i class="<?= esc($tab['icon']) ?>" aria-hidden="true"></i>
                    <span class="tab-label"><?= esc($tab['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
