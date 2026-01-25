<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Galería<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.dropzone {
    border: 2px dashed #dee2e6;
    border-radius: 0.75rem;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}
.dropzone:hover, .dropzone.dragover {
    border-color: #0d6efd;
    background: #e7f1ff;
}
.dropzone-icon {
    font-size: 3rem;
    color: #6c757d;
    margin-bottom: 1rem;
}
.dropzone.dragover .dropzone-icon {
    color: #0d6efd;
}
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}
.gallery-item {
    position: relative;
    border-radius: 0.5rem;
    overflow: hidden;
    background: #f8f9fa;
    aspect-ratio: 1;
}
.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.gallery-item:hover img {
    transform: scale(1.05);
}
.gallery-item-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.gallery-item:hover .gallery-item-overlay {
    opacity: 1;
}
.gallery-item-category {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background: rgba(0,0,0,0.6);
    color: #fff;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
}
.upload-progress {
    display: none;
}
.upload-progress.active {
    display: block;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
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
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><i class="bi bi-info-circle me-1"></i>Información</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>"><i class="bi bi-people me-1"></i>Invitados</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/rsvp') ?>"><i class="bi bi-check2-square me-1"></i>RSVPs</a></li>
    <li class="nav-item"><button class="nav-link active" type="button"><i class="bi bi-images me-1"></i>Galería</button></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/registry') ?>"><i class="bi bi-gift me-1"></i>Regalos</a></li>
</ul>

<!-- Upload Zone -->
<div class="card mb-4">
    <div class="card-body">
        <div class="dropzone" id="dropzone">
            <i class="bi bi-cloud-upload dropzone-icon"></i>
            <h5>Arrastra y suelta tus fotos aquí</h5>
            <p class="text-muted mb-3">o haz clic para seleccionar archivos</p>
            <input type="file" id="fileInput" multiple accept="image/*" class="d-none">
            <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                <i class="bi bi-folder2-open me-2"></i>Seleccionar Archivos
            </button>
            <p class="small text-muted mt-2 mb-0">Formatos: JPG, PNG, GIF, WEBP • Máximo 10MB por imagen</p>
        </div>
        
        <!-- Progress -->
        <div class="upload-progress mt-3" id="uploadProgress">
            <div class="d-flex justify-content-between mb-1">
                <span id="uploadStatus">Subiendo...</span>
                <span id="uploadPercent">0%</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width: 0%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Category Filter -->
<?php if (!empty($categories)): ?>
<div class="mb-4">
    <div class="btn-group btn-group-sm" role="group">
        <button type="button" class="btn btn-outline-secondary active" data-filter="all">Todas</button>
        <?php foreach ($categories as $cat): ?>
            <button type="button" class="btn btn-outline-secondary" data-filter="<?= esc($cat['category_tag']) ?>">
                <?= ucfirst(esc($cat['category_tag'])) ?> (<?= $cat['count'] ?>)
            </button>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Gallery Grid -->
<div class="gallery-grid" id="galleryGrid">
    <?php if (empty($images)): ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-images text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Sin fotos aún</h5>
                <p class="text-muted">Sube las primeras fotos para tu galería</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($images as $image): ?>
            <div class="gallery-item" data-id="<?= $image['id'] ?>" data-category="<?= esc($image['category_tag']) ?>">
                <img src="<?= base_url($image['file_url_thumbnail']) ?>" alt="<?= esc($image['alt_text']) ?>" loading="lazy">
                <?php if ($image['category_tag']): ?>
                    <span class="gallery-item-category"><?= ucfirst(esc($image['category_tag'])) ?></span>
                <?php endif; ?>
                <div class="gallery-item-overlay">
                    <a href="<?= base_url($image['file_url_original']) ?>" target="_blank" class="btn btn-sm btn-light" title="Ver original">
                        <i class="bi bi-eye"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-light" onclick="editImage('<?= $image['id'] ?>')" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteImage('<?= $image['id'] ?>')" title="Eliminar">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editImageId">
                <div class="mb-3">
                    <label class="form-label">Texto Alternativo (SEO)</label>
                    <input type="text" id="editAltText" class="form-control" placeholder="Descripción de la imagen">
                </div>
                <div class="mb-3">
                    <label class="form-label">Categoría</label>
                    <select id="editCategory" class="form-select">
                        <option value="general">General</option>
                        <option value="couple">Pareja</option>
                        <option value="venue">Lugar</option>
                        <option value="details">Detalles</option>
                        <option value="ceremony">Ceremonia</option>
                        <option value="reception">Recepción</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveImageEdit()">Guardar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const eventId = '<?= $event['id'] ?>';
const uploadUrl = '<?= base_url('admin/events/' . $event['id'] . '/gallery/upload') ?>';
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const progressContainer = document.getElementById('uploadProgress');
const progressBar = document.getElementById('progressBar');
const uploadStatus = document.getElementById('uploadStatus');
const uploadPercent = document.getElementById('uploadPercent');

// Drag and Drop
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
});

dropzone.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    handleFiles(files);
});

fileInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

function handleFiles(files) {
    if (files.length === 0) return;
    
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('images[]', files[i]);
    }
    
    uploadFiles(formData);
}

function uploadFiles(formData) {
    progressContainer.classList.add('active');
    progressBar.style.width = '0%';
    uploadPercent.textContent = '0%';
    uploadStatus.textContent = 'Subiendo...';
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            uploadPercent.textContent = percent + '%';
        }
    });
    
    xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                // Recargar página para mostrar nuevas imágenes
                setTimeout(() => location.reload(), 1000);
            } else {
                Toast.fire({ icon: 'error', title: response.message });
            }
        } else {
            Toast.fire({ icon: 'error', title: 'Error al subir archivos' });
        }
        progressContainer.classList.remove('active');
    });
    
    xhr.addEventListener('error', () => {
        Toast.fire({ icon: 'error', title: 'Error de conexión' });
        progressContainer.classList.remove('active');
    });
    
    xhr.open('POST', uploadUrl);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
}

// Filter by category
document.querySelectorAll('[data-filter]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        document.querySelectorAll('.gallery-item').forEach(item => {
            if (filter === 'all' || item.dataset.category === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

// Edit image
function editImage(imageId) {
    document.getElementById('editImageId').value = imageId;
    // TODO: Cargar datos actuales via AJAX
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function saveImageEdit() {
    const imageId = document.getElementById('editImageId').value;
    const altText = document.getElementById('editAltText').value;
    const category = document.getElementById('editCategory').value;
    
    $.post(`${BASE_URL}admin/events/${eventId}/gallery/update/${imageId}`, {
        alt_text: altText,
        category_tag: category
    }).done(function(response) {
        if (response.success) {
            Toast.fire({ icon: 'success', title: response.message });
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            location.reload();
        } else {
            Toast.fire({ icon: 'error', title: response.message });
        }
    });
}

// Delete image
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
                        document.querySelector(`.gallery-item[data-id="${imageId}"]`).remove();
                    } else {
                        Toast.fire({ icon: 'error', title: response.message });
                    }
                });
        }
    });
}
</script>
<?= $this->endSection() ?>
