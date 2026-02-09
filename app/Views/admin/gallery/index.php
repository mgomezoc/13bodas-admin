<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Galería<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/events.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Galería</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Galería de Fotos</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?> • <?= count($images) ?> imagen(es)</p>
        <p class="text-muted small mb-0">Estas fotos alimentan la galería pública del template.</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
        <i class="bi bi-upload me-2"></i>Subir Fotos
    </button>
</div>

<!-- Input oculto para subir -->
<input type="file" id="fileInput" multiple accept="image/*" style="display: none;">

<?= view('admin/events/partials/_event_navigation', ['active' => 'galeria', 'event_id' => $event['id']]) ?>

<!-- Category Nav -->
<div class="card mb-3">
    <div class="card-body py-2">
        <nav class="d-flex flex-wrap gap-2" aria-label="Categorías de media">
            <?php foreach (($allowedCategories ?? ['gallery' => 'Galería']) as $catKey => $catLabel): ?>
                <?php
                    $isActive = ($currentCategory ?? 'gallery') === $catKey;
                    $count = $categoryCounts[$catKey] ?? 0;
                ?>
                <a href="<?= base_url("admin/events/{$event['id']}/gallery?category={$catKey}") ?>"
                   class="btn btn-sm <?= $isActive ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <?= esc($catLabel) ?>
                    <?php if ($count > 0): ?>
                        <span class="badge <?= $isActive ? 'bg-white text-primary' : 'bg-secondary' ?> ms-1"><?= $count ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>

<!-- Zona de drop -->
<div id="dropZone" class="card mb-4" style="border: 2px dashed var(--border-color); background: var(--bg-body);">
    <div class="card-body text-center py-5">
        <i class="bi bi-cloud-upload text-muted" style="font-size: 3rem;"></i>
        <p class="mt-3 mb-1">Arrastra y suelta tus fotos aquí</p>
        <p class="text-muted small mb-3">o haz clic en "Subir Fotos" • Máx 10MB por imagen • JPG, PNG, WebP</p>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('fileInput').click()">
            <i class="bi bi-upload me-2"></i>Seleccionar Fotos
        </button>
    </div>
</div>

<!-- Galería -->
<div id="gallerySection">
    <?php if (empty($images)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state py-5">
                <i class="bi bi-images empty-state-icon"></i>
                <h4 class="empty-state-title">Sin fotos todavía</h4>
                <p class="empty-state-text">Sube las primeras fotos de la pareja para mostrar en la invitación.</p>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                    <i class="bi bi-upload me-2"></i>Subir Primera Foto
                </button>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-3" id="galleryGrid">
        <?php foreach ($images as $image): ?>
        <div class="col-6 col-md-4 col-lg-3" data-id="<?= $image['id'] ?>">
            <div class="card h-100 shadow-sm">
                <div class="ratio ratio-4x3 bg-light">
                    <img src="<?= base_url($image['file_url_original']) ?>"
                         class="w-100 h-100 object-fit-cover"
                         alt="<?= esc($image['alt_text']) ?>">
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="text-truncate">
                            <div class="fw-semibold small text-truncate"><?= esc($image['alt_text']) ?></div>
                            <div class="text-muted small"><?= date('d/m/Y', strtotime($image['created_at'])) ?></div>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a class="btn btn-outline-secondary" href="<?= base_url($image['file_url_original']) ?>" target="_blank" title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button type="button"
                                class="btn btn-outline-danger delete-item"
                                data-id="<?= $image['id'] ?>"
                                data-name="<?= esc($image['alt_text'] ?: 'imagen') ?>"
                                data-endpoint="<?= base_url('admin/events/' . $event['id'] . '/gallery/delete/' . $image['id']) ?>"
                                data-refresh-target="#gallerySection"
                                title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/admin/js/events-crud.js') ?>"></script>
<script>
const eventId = '<?= $event['id'] ?>';
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const maxFileSizeMb = 10;
const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

// Drag & Drop
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.style.borderColor = 'var(--primary)', false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.style.borderColor = 'var(--border-color)', false);
});

dropZone.addEventListener('drop', handleDrop, false);
fileInput.addEventListener('change', handleFiles, false);

function handleDrop(e) {
    const files = e.dataTransfer.files;
    uploadFiles(files);
}

function handleFiles() {
    const files = fileInput.files;
    uploadFiles(files);
}

function uploadFiles(files) {
    if (files.length === 0) return;

    const formData = new FormData();
    const skipped = [];

    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        if (!allowedTypes.includes(file.type)) {
            skipped.push(`${file.name}: tipo no permitido`);
            continue;
        }

        if (file.size > maxFileSizeMb * 1024 * 1024) {
            skipped.push(`${file.name}: excede ${maxFileSizeMb}MB`);
            continue;
        }

        formData.append('images[]', file);
    }
    formData.append('category', '<?= $currentCategory ?? "gallery" ?>');

    if (!formData.has('images[]')) {
        Toast.fire({
            icon: 'warning',
            title: 'No hay archivos válidos para subir.',
        });
        if (skipped.length) {
            Swal.fire({
                icon: 'info',
                title: 'Archivos omitidos',
                html: `<ul class="text-start mb-0">${skipped.map(item => `<li>${item}</li>`).join('')}</ul>`
            });
        }
        return;
    }
    
    // Loading
    dropZone.innerHTML = '<div class="card-body text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Subiendo...</p></div>';
    
    $.ajax({
        url: `${BASE_URL}admin/events/${eventId}/gallery/upload`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
            } else {
                Toast.fire({ icon: 'error', title: response.message || 'Error al subir' });
            }

            if (response.errors && response.errors.length) {
                Swal.fire({
                    icon: 'info',
                    title: 'Archivos con error',
                    html: `<ul class="text-start mb-0">${response.errors.map(item => `<li>${item}</li>`).join('')}</ul>`
                });
            }

            if (skipped.length) {
                Swal.fire({
                    icon: 'info',
                    title: 'Archivos omitidos',
                    html: `<ul class="text-start mb-0">${skipped.map(item => `<li>${item}</li>`).join('')}</ul>`
                });
            }

            if (response.success) {
                refreshModuleSection('#gallerySection').always(function() {
                    resetDropZone();
                });
            } else {
                resetDropZone();
            }
        },
        error: function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
            resetDropZone();
        },
        complete: function() {
            fileInput.value = '';
        }
    });
}

function resetDropZone() {
    dropZone.innerHTML = `
        <div class="card-body text-center py-5">
            <i class="bi bi-cloud-upload text-muted" style="font-size: 3rem;"></i>
            <p class="mt-3 mb-1">Arrastra y suelta tus fotos aquí</p>
            <p class="text-muted small mb-3">o haz clic en "Subir Fotos" • Máx ${maxFileSizeMb}MB por imagen • JPG, PNG, WebP</p>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('fileInput').click()">
                <i class="bi bi-upload me-2"></i>Seleccionar Fotos
            </button>
        </div>
    `;
}

</script>
<?= $this->endSection() ?>
