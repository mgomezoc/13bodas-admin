<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Mi Evento<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state py-5">
            <i class="bi bi-calendar-x empty-state-icon"></i>
            <h4 class="empty-state-title">Sin evento asignado</h4>
            <p class="empty-state-text">
                Aún no tienes un evento asignado a tu cuenta.<br>
                Por favor contacta al equipo de 13Bodas para configurar tu invitación.
            </p>
            <a href="https://wa.me/528115247741" target="_blank" class="btn btn-success">
                <i class="bi bi-whatsapp me-2"></i>Contactar por WhatsApp
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
