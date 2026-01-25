<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Proyectos<?= $this->endSection() ?>

<?= $this->section('content') ?>
<header class="page-header">
    <h1 class="page-title">Proyectos</h1>
    <p class="page-subtitle">Gestiona las invitaciones digitales y filtros AR de tus clientes.</p>
</header>

<div class="card">
    <h2 style="margin-bottom: 1rem;">Lista de Proyectos</h2>
    <p style="color: var(--text-secondary);">
        Esta sección está en desarrollo. Próximamente podrás:
    </p>
    <ul style="margin-top: 0.5rem; margin-left: 1.5rem; color: var(--text-secondary);">
        <li>Crear nuevos proyectos de invitaciones</li>
        <li>Gestionar filtros AR por proyecto</li>
        <li>Ver el estado de cada proyecto</li>
        <li>Acceder a los analytics de cada filtro</li>
    </ul>
</div>
<?= $this->endSection() ?>
