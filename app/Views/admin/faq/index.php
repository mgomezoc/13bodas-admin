<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>FAQ<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/events.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">FAQ</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= view('admin/events/partials/_event_navigation', ['active' => 'faq', 'event_id' => $event['id']]) ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Preguntas Frecuentes</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#faqModal" onclick="openFaqModal()">
        <i class="bi bi-plus-lg me-2"></i>Agregar Pregunta
    </button>
</div>

<?= view('admin/events/partials/_section_help', ['message' => 'Agrega respuestas rápidas a dudas frecuentes de invitados para reducir mensajes y aclarar logística.']) ?>


<div id="faqList" class="card">
    <div class="card-body">
        <div class="accordion" id="faqAccordion">
            <?php foreach ($items as $index => $item): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeading<?= $index ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?= $index ?>">
                            <?= esc($item['question']) ?>
                        </button>
                    </h2>
                    <div id="faqCollapse<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <div class="mb-3"><?= nl2br(esc($item['answer'])) ?></div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick='openFaqModal(<?= json_encode($item) ?>)'>
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger delete-item"
                                    data-id="<?= $item['id'] ?>"
                                    data-name="<?= esc($item['question']) ?>"
                                    data-endpoint="<?= base_url('admin/events/' . $event['id'] . '/faq/delete/' . $item['id']) ?>"
                                    data-refresh-target="#faqList">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="faqModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="faqForm"
                class="modal-ajax-form"
                data-refresh-target="#faqList"
                action="<?= base_url('admin/events/' . $event['id'] . '/faq/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-question-circle me-2"></i><span id="faqModalTitle">Nueva Pregunta</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="item_id">
                    <div class="mb-3">
                        <label class="form-label">Pregunta <span class="text-danger">*</span></label>
                        <input type="text" name="question" id="question" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Respuesta <span class="text-danger">*</span></label>
                        <textarea name="answer" id="answer" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Orden</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Visible</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible" checked>
                                <label class="form-check-label" for="is_visible">Mostrar</label>
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
<script src="<?= base_url('assets/admin/js/events-crud.js') ?>"></script>
<script>
const eventId = '<?= $event['id'] ?>';

function openFaqModal(item = null) {
    $('#faqForm')[0].reset();
    $('#item_id').val('');
    $('#faqModalTitle').text(item ? 'Editar Pregunta' : 'Nueva Pregunta');
    $('#is_visible').prop('checked', true);
    const form = document.getElementById('faqForm');
    form.action = item
        ? `${BASE_URL}admin/events/${eventId}/faq/update/${item.id}`
        : `${BASE_URL}admin/events/${eventId}/faq/store`;

    if (item) {
        $('#item_id').val(item.id);
        $('#question').val(item.question);
        $('#answer').val(item.answer);
        $('#sort_order').val(item.sort_order);
        $('#is_visible').prop('checked', parseInt(item.is_visible) === 1);
    }

    const modal = new bootstrap.Modal(document.getElementById('faqModal'));
    modal.show();
}

</script>
<?= $this->endSection() ?>
