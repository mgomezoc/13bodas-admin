<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Invitados<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/events.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Invitados</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Invitados</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/events/' . $event['id'] . '/guests/import') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-upload me-2"></i>Importar
        </a>
        <a href="<?= base_url('admin/events/' . $event['id'] . '/guests/export') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-download me-2"></i>Exportar
        </a>
        <a href="<?= base_url('admin/events/' . $event['id'] . '/guests/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Invitado
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary"><i class="bi bi-people"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-label">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['accepted'] ?></div>
                <div class="stat-label">Confirmados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger"><i class="bi bi-x-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['declined'] ?></div>
                <div class="stat-label">No Asisten</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning"><i class="bi bi-clock"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['pending'] ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
    </div>
</div>

<?= view('admin/events/partials/_event_navigation', ['active' => 'invitados', 'event_id' => $event['id']]) ?>

<div class="card">
    <div class="card-body">
        <table 
            id="guestsTable"
            data-toggle="table"
            data-url="<?= base_url('admin/events/' . $event['id'] . '/guests/list') ?>"
            data-pagination="true"
            data-page-size="25"
            data-search="true"
            data-search-align="left"
            data-show-refresh="true"
            data-sort-name="group_name"
            data-sort-order="asc"
            data-locale="es-MX"
            data-response-handler="responseHandler"
            class="table table-hover">
            <thead>
                <tr>
                    <th data-field="first_name" data-sortable="true" data-formatter="nameFormatter">Nombre</th>
                    <th data-field="group_name" data-sortable="true">Grupo</th>
                    <th data-field="email">Email</th>
                    <th data-field="phone_number">Teléfono</th>
                    <th data-field="rsvp_status" data-formatter="rsvpFormatter" data-align="center">RSVP</th>
                    <th data-field="is_child" data-formatter="childFormatter" data-align="center">Tipo</th>
                    <th data-field="invitation" data-formatter="invitationFormatter" data-align="center">Invitación</th>
                    <th data-field="id" data-formatter="actionsFormatter" data-align="right">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="inviteLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Copiar enlace de invitación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <label for="inviteLinkInput" class="form-label">Enlace</label>
                <input type="text" class="form-control" id="inviteLinkInput" readonly>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="copyInviteLinkBtn">Copiar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/admin/js/events-crud.js') ?>"></script>
<script>
const eventId = '<?= $event['id'] ?>';
const inviteLinkModal = document.getElementById('inviteLinkModal');
const inviteLinkInput = document.getElementById('inviteLinkInput');
const copyInviteLinkBtn = document.getElementById('copyInviteLinkBtn');

function nameFormatter(value, row) {
    let name = `${row.first_name} ${row.last_name}`;
    if (row.is_primary_contact == 1) {
        name += ' <i class="bi bi-star-fill text-warning" title="Contacto principal"></i>';
    }
    return name;
}

function rsvpFormatter(value, row) {
    const statusMap = {
        'pending': { class: 'bg-warning', label: 'Pendiente' },
        'accepted': { class: 'bg-success', label: 'Confirmado' },
        'declined': { class: 'bg-danger', label: 'No Asiste' }
    };
    const status = statusMap[value] || { class: 'bg-secondary', label: value };
    return `<span class="badge ${status.class}">${status.label}</span>`;
}

function childFormatter(value, row) {
    return value == 1 ? '<span class="badge bg-info">Niño</span>' : '<span class="badge bg-light text-dark">Adulto</span>';
}

function invitationFormatter(value, row) {
    const hasEmail = row.email && row.email.trim() !== '';
    const emailTitle = hasEmail ? 'Enviar invitación' : 'Este invitado no tiene email';
    const emailDisabled = hasEmail ? '' : 'disabled';

    return `
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-outline-primary" ${emailDisabled} title="${emailTitle}" onclick="sendInvite('${row.id}')">
                <i class="bi bi-envelope"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" title="Copiar enlace" onclick="copyInviteLink('${row.id}')">
                <i class="bi bi-clipboard"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" title="WhatsApp" onclick="openWhatsAppInvite('${row.id}')">
                <i class="bi bi-whatsapp"></i>
            </button>
        </div>
    `;
}

function actionsFormatter(value, row) {
    const guestName = `${row.first_name || ''} ${row.last_name || ''}`.trim() || 'este invitado';
    return `
        <div class="action-buttons">
            <a href="${BASE_URL}admin/events/${eventId}/guests/edit/${row.id}" class="btn btn-sm btn-outline-primary" title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button"
                class="btn btn-sm btn-outline-danger delete-item"
                data-id="${row.id}"
                data-name="${guestName.replace(/"/g, '&quot;')}"
                data-endpoint="${BASE_URL}admin/events/${eventId}/guests/delete/${row.id}"
                data-table-id="guestsTable"
                title="Eliminar">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
}

function fetchInviteLink(guestId) {
    return $.get(`${BASE_URL}admin/events/${eventId}/guests/${guestId}/invite-link`);
}

function handleClipboardSuccess(message) {
    Toast.fire({ icon: 'success', title: message });
}

function showCopyFallback(inviteUrl) {
    inviteLinkInput.value = inviteUrl;
    const modal = bootstrap.Modal.getOrCreateInstance(inviteLinkModal);
    modal.show();
}

function copyInviteLink(guestId) {
    fetchInviteLink(guestId)
        .done(function(response) {
            if (!response.success) {
                Toast.fire({ icon: 'error', title: response.message });
                return;
            }

            const inviteUrl = response.invite_url;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(inviteUrl)
                    .then(() => handleClipboardSuccess('Enlace copiado'))
                    .catch(() => showCopyFallback(inviteUrl));
            } else {
                showCopyFallback(inviteUrl);
            }
        })
        .fail(function() {
            Toast.fire({ icon: 'error', title: 'No se pudo obtener el enlace.' });
        });
}

function sendInvite(guestId) {
    $.post(`${BASE_URL}admin/events/${eventId}/guests/${guestId}/send-invite`, {
        [CSRF_NAME]: CSRF_TOKEN
    })
        .done(function(response) {
            if (!response.success) {
                Toast.fire({ icon: 'error', title: response.message });
                return;
            }

            Toast.fire({ icon: 'success', title: response.message });
            showInviteActions(response);
        })
        .fail(function() {
            Toast.fire({ icon: 'error', title: 'No se pudo enviar la invitación.' });
        });
}

function showInviteActions(response) {
    const inviteUrl = response.invite_url;
    Swal.fire({
        title: 'Invitación enviada',
        text: '¿Quieres compartir el enlace?',
        icon: 'success',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Copiar enlace',
        denyButtonText: 'WhatsApp',
        cancelButtonText: 'Cerrar'
    }).then((result) => {
        if (result.isConfirmed) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(inviteUrl)
                    .then(() => handleClipboardSuccess('Enlace copiado'))
                    .catch(() => showCopyFallback(inviteUrl));
            } else {
                showCopyFallback(inviteUrl);
            }
        } else if (result.isDenied) {
            openWhatsAppFromPayload(response);
        }
    });
}

function buildInviteMessage(guest, eventData, inviteUrl) {
    const guestName = [guest.first_name, guest.last_name].filter(Boolean).join(' ');
    const greeting = guestName ? `Hola ${guestName},` : 'Hola,';
    const coupleTitle = eventData.couple_title ? ` de ${eventData.couple_title}` : '';
    return `${greeting} te compartimos la invitación${coupleTitle} 💍✨\nConfirma tu asistencia aquí: ${inviteUrl}`;
}

function normalizeWhatsAppPhone(phoneNumber) {
    if (!phoneNumber) {
        return null;
    }

    const cleaned = phoneNumber.replace(/[\s().-]/g, '');
    if (/^\+\d{10,15}$/.test(cleaned)) {
        return cleaned.substring(1);
    }

    return null;
}

function openWhatsAppFromPayload(payload) {
    const inviteUrl = payload.invite_url;
    const guest = payload.guest || {};
    const eventData = payload.event || {};
    const message = buildInviteMessage(guest, eventData, inviteUrl);
    const encodedMessage = encodeURIComponent(message);
    const phone = normalizeWhatsAppPhone(guest.phone_number || '');
    const url = phone
        ? `https://wa.me/${phone}?text=${encodedMessage}`
        : `https://wa.me/?text=${encodedMessage}`;

    window.open(url, '_blank');
}

function openWhatsAppInvite(guestId) {
    fetchInviteLink(guestId)
        .done(function(response) {
            if (!response.success) {
                Toast.fire({ icon: 'error', title: response.message });
                return;
            }

            openWhatsAppFromPayload(response);
        })
        .fail(function() {
            Toast.fire({ icon: 'error', title: 'No se pudo obtener el enlace.' });
        });
}

if (copyInviteLinkBtn) {
    copyInviteLinkBtn.addEventListener('click', function() {
        const inviteUrl = inviteLinkInput.value;
        if (!inviteUrl) {
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(inviteUrl)
                .then(() => handleClipboardSuccess('Enlace copiado'))
                .catch(() => inviteLinkInput.select());
        } else {
            inviteLinkInput.select();
        }
    });
}
</script>
<?= $this->endSection() ?>
