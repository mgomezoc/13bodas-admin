<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Dominios<?= $this->endSection() ?>

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

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><i class="bi bi-info-circle me-1"></i>Información</a></li>
    <li class="nav-item"><button class="nav-link active" type="button"><i class="bi bi-globe2 me-1"></i>Dominios</button></li>
</ul>

<div class="card">
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
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDomain('<?= $domain['id'] ?>')" title="Eliminar">
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
            <form id="domainForm">
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
<script>
const eventId = '<?= $event['id'] ?>';

function openDomainModal(domain = null) {
    $('#domainForm')[0].reset();
    $('#domain_id').val('');
    $('#domainModalTitle').text(domain ? 'Editar Dominio' : 'Nuevo Dominio');

    if (domain) {
        $('#domain_id').val(domain.id);
        $('#domain').val(domain.domain);
        $('#status').val(domain.status);
    }

    const modal = new bootstrap.Modal(document.getElementById('domainModal'));
    modal.show();
}

$('#domainForm').on('submit', function(e) {
    e.preventDefault();
    const domainId = $('#domain_id').val();
    const url = domainId
        ? `${BASE_URL}admin/events/${eventId}/domains/update/${domainId}`
        : `${BASE_URL}admin/events/${eventId}/domains/store`;

    $.post(url, $(this).serialize())
        .done(function(response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                setTimeout(() => location.reload(), 600);
            } else {
                Toast.fire({ icon: 'error', title: response.message || 'Error al guardar' });
            }
        })
        .fail(function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
        });
});

function deleteDomain(domainId) {
    Swal.fire({
        title: '¿Eliminar dominio?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`${BASE_URL}admin/events/${eventId}/domains/delete/${domainId}`)
                .done(function(response) {
                    if (response.success) {
                        Toast.fire({ icon: 'success', title: response.message });
                        setTimeout(() => location.reload(), 600);
                    } else {
                        Toast.fire({ icon: 'error', title: response.message });
                    }
                });
        }
    });
}
</script>
<?= $this->endSection() ?>
