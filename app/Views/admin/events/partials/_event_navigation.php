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
    'informacion' => ['icon' => 'bi-info-circle', 'label' => 'Información', 'priority' => 1, 'url' => base_url("admin/events/edit/{$eventId}")],
    'invitados' => ['icon' => 'bi-people', 'label' => 'Invitados', 'priority' => 1, 'url' => base_url("admin/events/{$eventId}/guests")],
    'grupos' => ['icon' => 'bi-collection', 'label' => 'Grupos', 'priority' => 1, 'url' => base_url("admin/events/{$eventId}/groups")],
    'confirmaciones' => ['icon' => 'bi-check-circle', 'label' => 'Confirmaciones', 'priority' => 1, 'url' => base_url("admin/events/{$eventId}/rsvp")],
    'galeria' => ['icon' => 'bi-images', 'label' => 'Galería', 'priority' => 2, 'url' => base_url("admin/events/{$eventId}/gallery")],
    'regalos' => ['icon' => 'bi-gift', 'label' => 'Regalos', 'priority' => 2, 'url' => base_url("admin/events/{$eventId}/registry")],
    'menu' => ['icon' => 'bi-card-list', 'label' => 'Menú', 'priority' => 2, 'url' => base_url("admin/events/{$eventId}/menu")],
    'cortejo' => ['icon' => 'bi-heart', 'label' => 'Cortejo', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/party")],
    'ubicaciones' => ['icon' => 'bi-geo-alt', 'label' => 'Ubicaciones', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/locations")],
    'agenda' => ['icon' => 'bi-calendar-event', 'label' => 'Agenda', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/schedule")],
    'timeline' => ['icon' => 'bi-clock-history', 'label' => 'Historia', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/timeline")],
    'faq' => ['icon' => 'bi-question-circle', 'label' => 'FAQ', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/faq")],
    'recomendaciones' => ['icon' => 'bi-star', 'label' => 'Recomendaciones', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/recommendations")],
    'preguntas-rsvp' => ['icon' => 'bi-ui-checks', 'label' => 'Preguntas RSVP', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/rsvp-questions")],
    'modulos' => ['icon' => 'bi-grid', 'label' => 'Módulos', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/modules")],
];

if ($isAdmin || $isClient) {
    $tabs['dominios'] = ['icon' => 'bi-globe2', 'label' => 'Dominios', 'priority' => 3, 'url' => base_url("admin/events/{$eventId}/domains")];
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
                    <i class="bi <?= esc($tab['icon']) ?>"></i>
                    <span class="tab-label"><?= esc($tab['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
