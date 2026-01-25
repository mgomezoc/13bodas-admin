<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Importar Invitados<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>">Invitados</a></li>
        <li class="breadcrumb-item active">Importar</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Importar Invitados</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-upload me-2"></i>Subir Archivo CSV
            </div>
            <div class="card-body">
                <form action="<?= base_url('admin/events/' . $event['id'] . '/guests/process-import') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="mb-4">
                        <label class="form-label" for="csv_file">Archivo CSV</label>
                        <input type="file" id="csv_file" name="csv_file" class="form-control" accept=".csv" required>
                        <div class="form-text">
                            El archivo debe estar en formato CSV (separado por comas).
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-2"></i>Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Formato del CSV
            </div>
            <div class="card-body">
                <p class="small">El archivo CSV debe tener las siguientes columnas en orden:</p>
                <ol class="small">
                    <li><strong>Nombre</strong> (requerido)</li>
                    <li><strong>Apellido</strong> (requerido)</li>
                    <li>Email (opcional)</li>
                    <li>Teléfono (opcional)</li>
                    <li>Nombre del Grupo (opcional)</li>
                    <li>Es niño: "si" o "no" (opcional)</li>
                </ol>
                
                <hr>
                
                <p class="small mb-2"><strong>Ejemplo:</strong></p>
                <pre class="bg-light p-2 small">Nombre,Apellido,Email,Teléfono,Grupo,Niño
Juan,Pérez,juan@email.com,8112345678,Familia Pérez,no
María,Pérez,maria@email.com,,Familia Pérez,no
Pedrito,Pérez,,,Familia Pérez,si</pre>
                
                <a href="#" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="bi bi-download me-1"></i>Descargar plantilla
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
