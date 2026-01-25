<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Configuración<?= $this->endSection() ?>

<?= $this->section('content') ?>
<header class="page-header">
    <h1 class="page-title">Configuración</h1>
    <p class="page-subtitle">Ajusta las preferencias del sistema y tu cuenta.</p>
</header>

<div class="card">
    <h2 style="margin-bottom: 1rem;">Configuración del Sistema</h2>
    <p style="color: var(--text-secondary);">
        Esta sección está en desarrollo. Próximamente podrás:
    </p>
    <ul style="margin-top: 0.5rem; margin-left: 1.5rem; color: var(--text-secondary);">
        <li>Cambiar tu contraseña</li>
        <li>Configurar notificaciones</li>
        <li>Personalizar el email de respuesta a formularios</li>
        <li>Gestionar integraciones (WhatsApp, Analytics, etc.)</li>
    </ul>
</div>
<?= $this->endSection() ?>
