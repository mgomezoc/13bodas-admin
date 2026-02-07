<?php
$session   = session();
$userRoles = $session->get('user_roles') ?? [];
$isAdmin   = $isAdmin ?? (in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true));
$activeTab = $activeTab ?? '';

if ($activeTab === '') {
    $path = service('uri')->getPath();
    $path = trim((string) $path, '/');
    $segments = explode('/', $path);
    $activeFromPath = end($segments) ?: '';
    $map = [
        'guests' => 'guests',
        'groups' => 'groups',
        'rsvp' => 'rsvp',
        'gallery' => 'gallery',
        'registry' => 'registry',
        'menu' => 'menu',
        'party' => 'party',
        'locations' => 'locations',
        'schedule' => 'schedule',
        'faq' => 'faq',
        'recommendations' => 'recommendations',
        'rsvp-questions' => 'rsvp-questions',
        'modules' => 'modules',
        'domains' => 'domains',
    ];
    if (isset($map[$activeFromPath])) {
        $activeTab = $map[$activeFromPath];
    }
}

$tabs = [
    [
        'key'  => 'info',
        'label'=> 'Información',
        'icon' => 'bi-info-circle',
        'url'  => base_url('admin/events/edit/' . $event['id']),
    ],
    [
        'key'  => 'guests',
        'label'=> 'Invitados',
        'icon' => 'bi-people',
        'url'  => base_url('admin/events/' . $event['id'] . '/guests'),
    ],
    [
        'key'  => 'groups',
        'label'=> 'Grupos',
        'icon' => 'bi-collection',
        'url'  => base_url('admin/events/' . $event['id'] . '/groups'),
    ],
    [
        'key'  => 'rsvp',
        'label'=> 'Confirmaciones',
        'icon' => 'bi-check2-square',
        'url'  => base_url('admin/events/' . $event['id'] . '/rsvp'),
    ],
    [
        'key'  => 'gallery',
        'label'=> 'Galería',
        'icon' => 'bi-images',
        'url'  => base_url('admin/events/' . $event['id'] . '/gallery'),
    ],
    [
        'key'  => 'registry',
        'label'=> 'Regalos',
        'icon' => 'bi-gift',
        'url'  => base_url('admin/events/' . $event['id'] . '/registry'),
    ],
    [
        'key'  => 'menu',
        'label'=> 'Menú',
        'icon' => 'bi-cup-hot',
        'url'  => base_url('admin/events/' . $event['id'] . '/menu'),
    ],
    [
        'key'  => 'party',
        'label'=> 'Cortejo',
        'icon' => 'bi-hearts',
        'url'  => base_url('admin/events/' . $event['id'] . '/party'),
    ],
    [
        'key'  => 'locations',
        'label'=> 'Ubicaciones',
        'icon' => 'bi-geo',
        'url'  => base_url('admin/events/' . $event['id'] . '/locations'),
    ],
    [
        'key'  => 'schedule',
        'label'=> 'Agenda',
        'icon' => 'bi-clock',
        'url'  => base_url('admin/events/' . $event['id'] . '/schedule'),
    ],
    [
        'key'  => 'faq',
        'label'=> 'FAQ',
        'icon' => 'bi-question-circle',
        'url'  => base_url('admin/events/' . $event['id'] . '/faq'),
    ],
    [
        'key'  => 'recommendations',
        'label'=> 'Recomendaciones',
        'icon' => 'bi-star',
        'url'  => base_url('admin/events/' . $event['id'] . '/recommendations'),
    ],
    [
        'key'  => 'rsvp-questions',
        'label'=> 'Preguntas RSVP',
        'icon' => 'bi-ui-checks',
        'url'  => base_url('admin/events/' . $event['id'] . '/rsvp-questions'),
    ],
    [
        'key'  => 'modules',
        'label'=> 'Módulos',
        'icon' => 'bi-grid',
        'url'  => base_url('admin/events/' . $event['id'] . '/modules'),
    ],
];

if ($isAdmin) {
    $tabs[] = [
        'key'   => 'domains',
        'label' => 'Dominios',
        'icon'  => 'bi-globe2',
        'url'   => base_url('admin/events/' . $event['id'] . '/domains'),
    ];
}
?>

<style>
    .event-module-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
        scrollbar-width: thin;
    }

    .event-module-tabs .nav-item {
        flex: 0 0 auto;
    }

    .event-module-tabs .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
</style>

<div class="event-module-tabs-wrapper mb-4">
    <ul class="nav nav-tabs event-module-tabs" role="tablist">
        <?php foreach ($tabs as $tab): ?>
            <?php $isActive = $activeTab === $tab['key']; ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= esc($tab['url']) ?>">
                    <i class="bi <?= esc($tab['icon']) ?>"></i> <?= esc($tab['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
