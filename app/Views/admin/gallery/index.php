<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Galería<?= $this->endSection() ?>

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
    </div>
    <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
        <i class="bi bi-upload me-2"></i>Subir Fotos
    </button>
</div>

<!-- Input oculto para subir -->
<input type="file" id="fileInput" multiple accept="image/*" style="display: none;">

<?php $activeTab = 'gallery'; ?>
<?= $this->include('admin/events/partials/modules_tabs') ?>

<!-- Zona de drop -->
<div id="dropZone" class="card mb-4" style="border: 2px dashed var(--border-color); background: var(--bg-body);">
    <div class="card-body text-center py-5">
        <i class="bi bi-cloud-upload text-muted" style="font-size: 3rem;"></i>
        <p class="mt-3 mb-1">Arrastra y suelta tus fotos aquí</p>
        <p class="text-muted small">o haz clic en "Subir Fotos" • Máx 5MB por imagen • JPG, PNG, WebP</p>
    </div>
</div>

<!-- Galería -->
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
        <div class="card h-100">
            <img src="<?= base_url($image['file_url_original']) ?>" 
                 class="card-img-top" 
                 alt="<?= esc($image['alt_text']) ?>"
                 style="height: 180px; object-fit: cover;">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted text-truncate"><?= esc($image['alt_text']) ?></small>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= base_url($image['file_url_original']) ?>" target="_blank">
                                <i class="bi bi-eye me-2"></i>Ver Original
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteImage('<?= $image['id'] ?>')">
                                <i class="bi bi-trash me-2"></i>Eliminar
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const eventId = '<?= $event['id'] ?>';
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');

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
    for (let i = 0; i < files.length; i++) {
        formData.append('images[]', files[i]);
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
                location.reload();
            } else {
                Toast.fire({ icon: 'error', title: response.message || 'Error al subir' });
                resetDropZone();
            }
        },
        error: function() {
            Toast.fire({ icon: 'error', title: 'Error de conexión' });
            resetDropZone();
        }
    });
}

function resetDropZone() {
    dropZone.innerHTML = `
        <div class="card-body text-center py-5">
            <i class="bi bi-cloud-upload text-muted" style="font-size: 3rem;"></i>
            <p class="mt-3 mb-1">Arrastra y suelta tus fotos aquí</p>
            <p class="text-muted small">o haz clic en "Subir Fotos" • Máx 5MB por imagen • JPG, PNG, WebP</p>
        </div>
    `;
}

function deleteImage(imageId) {
    Swal.fire({
        title: '¿Eliminar imagen?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`${BASE_URL}admin/events/${eventId}/gallery/delete/${imageId}`)
                .done(function(response) {
                    if (response.success) {
                        Toast.fire({ icon: 'success', title: response.message });
                        $(`[data-id="${imageId}"]`).fadeOut(300, function() { $(this).remove(); });
                    } else {
                        Toast.fire({ icon: 'error', title: response.message });
                    }
                });
        }
    });
}
</script>
<?= $this->endSection() ?>
