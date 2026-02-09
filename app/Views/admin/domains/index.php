<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Dominios<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/events.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Dominios</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Dominios Personalizados</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#domainModal" onclick="openDomainModal()">
        <i class="bi bi-plus-lg me-2"></i>Agregar Dominio
    </button>
</div>

<?= view('admin/events/partials/_event_navigation', ['active' => 'dominios', 'event_id' => $event['id']]) ?>

<div id="domainsList" class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Dominio</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($domains as $domain): ?>
                    <tr>
                        <td><?= esc($domain['domain']) ?></td>
                        <td><?= esc($domain['status']) ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick='openDomainModal(<?= json_encode($domain) ?>)' title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button"
                                class="btn btn-sm btn-outline-danger delete-item"
                                data-id="<?= $domain['id'] ?>"
                                data-name="<?= esc($domain['domain']) ?>"
                                data-endpoint="<?= base_url('admin/events/' . $event['id'] . '/domains/delete/' . $domain['id']) ?>"
                                data-refresh-target="#domainsList"
                                title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="domainModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="domainForm"
                class="modal-ajax-form"
                data-refresh-target="#domainsList"
                action="<?= base_url('admin/events/' . $event['id'] . '/domains/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-globe2 me-2"></i><span id="domainModalTitle">Nuevo Dominio</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="domain_id" id="domain_id">
                    <div class="mb-3">
                        <label class="form-label">Dominio <span class="text-danger">*</span></label>
                        <input type="text" name="domain" id="domain" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="status" id="status" class="form-select">
                            <option value="pending_dns">Pendiente DNS</option>
                            <option value="active">Activo</option>
                            <option value="disabled">Deshabilitado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/admin/js/events-crud.js') ?>"></script>
<script>
const eventId = '<?= $event['id'] ?>';

function openDomainModal(domain = null) {
    $('#domainForm')[0].reset();
    $('#domain_id').val('');
    $('#domainModalTitle').text(domain ? 'Editar Dominio' : 'Nuevo Dominio');
    const form = document.getElementById('domainForm');
    form.action = domain
        ? `${BASE_URL}admin/events/${eventId}/domains/update/${domain.id}`
        : `${BASE_URL}admin/events/${eventId}/domains/store`;

    if (domain) {
        $('#domain_id').val(domain.id);
        $('#domain').val(domain.domain);
        $('#status').val(domain.status);
    }

    const modal = new bootstrap.Modal(document.getElementById('domainModal'));
    modal.show();
}

</script>
<?= $this->endSection() ?>
