<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Preguntas RSVP<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Preguntas RSVP</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Preguntas RSVP</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#questionModal" onclick="openQuestionModal()">
        <i class="bi bi-plus-lg me-2"></i>Agregar Pregunta
    </button>
</div>

<?php $activeTab = 'rsvp-questions'; ?>
<?= $this->include('admin/events/partials/modules_tabs') ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Etiqueta</th>
                        <th>Tipo</th>
                        <th>Requerida</th>
                        <th>Activa</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $question): ?>
                    <tr>
                        <td><?= esc($question['code']) ?></td>
                        <td><?= esc($question['label']) ?></td>
                        <td><?= esc($question['type']) ?></td>
                        <td><?= $question['is_required'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                        <td><?= $question['is_active'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick='openQuestionModal(<?= json_encode($question) ?>)' title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteQuestion('<?= $question['id'] ?>')" title="Eliminar">
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

<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="questionForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-ui-checks me-2"></i><span id="questionModalTitle">Nueva Pregunta</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="question_id" id="question_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="code" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="text">Texto</option>
                                <option value="textarea">Texto largo</option>
                                <option value="select">Select</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="radio">Radio</option>
                                <option value="number">Número</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Etiqueta <span class="text-danger">*</span></label>
                            <input type="text" name="label" id="label" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Opciones (JSON)</label>
                            <textarea name="options_json" id="options_json" class="form-control" rows="3" placeholder='["Opción 1","Opción 2"]'></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Orden</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Requerida</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_required" id="is_required">
                                <label class="form-check-label" for="is_required">Sí</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Activa</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Sí</label>
                            </div>
                        </div>
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

function openQuestionModal(question = null) {
    $('#questionForm')[0].reset();
    $('#question_id').val('');
    $('#questionModalTitle').text(question ? 'Editar Pregunta' : 'Nueva Pregunta');
    $('#is_active').prop('checked', true);

    if (question) {
        $('#question_id').val(question.id);
        $('#code').val(question.code);
        $('#label').val(question.label);
        $('#type').val(question.type);
        $('#options_json').val(question.options_json);
        $('#sort_order').val(question.sort_order);
        $('#is_required').prop('checked', parseInt(question.is_required) === 1);
        $('#is_active').prop('checked', parseInt(question.is_active) === 1);
    }

    const modal = new bootstrap.Modal(document.getElementById('questionModal'));
    modal.show();
}

$('#questionForm').on('submit', function(e) {
    e.preventDefault();
    const questionId = $('#question_id').val();
    const url = questionId
        ? `${BASE_URL}admin/events/${eventId}/rsvp-questions/update/${questionId}`
        : `${BASE_URL}admin/events/${eventId}/rsvp-questions/store`;

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

function deleteQuestion(questionId) {
    Swal.fire({
        title: '¿Eliminar pregunta?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`${BASE_URL}admin/events/${eventId}/rsvp-questions/delete/${questionId}`)
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
