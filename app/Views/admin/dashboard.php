<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-grid">
    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon bg-cyan">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value">0</span>
                <span class="stat-label">Leads este mes</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 6h16v2H4V6zm2 4h12v2H6v-2zm2 4h8v2H8v-2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value">0</span>
                <span class="stat-label">Proyectos activos</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value">0</span>
                <span class="stat-label">Proyectos completados</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-gold">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value">$0</span>
                <span class="stat-label">Ingresos del mes</span>
            </div>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="welcome-card">
        <div class="welcome-content">
            <h2>¡Bienvenido, <?= session()->get('user_name') ?>! 👋</h2>
            <p>Este es el panel de administración de 13Bodas. Desde aquí puedes gestionar leads, proyectos y la configuración del sitio.</p>
            <div class="welcome-actions">
                <a href="<?= base_url('admin/leads') ?>" class="btn btn-primary">Ver Leads</a>
                <a href="<?= base_url('admin/proyectos/nuevo') ?>" class="btn btn-outline">Nuevo Proyecto</a>
            </div>
        </div>
        <div class="welcome-illustration">
            <img src="<?= base_url('img/13bodas-logo-invitaciones-digitales.svg') ?>" alt="13Bodas">
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>Acciones Rápidas</h3>
        <div class="actions-grid">
            <a href="<?= base_url('admin/leads') ?>" class="action-card">
                <div class="action-icon">📨</div>
                <span>Ver Leads</span>
            </a>
            <a href="<?= base_url('admin/proyectos/nuevo') ?>" class="action-card">
                <div class="action-icon">➕</div>
                <span>Nuevo Proyecto</span>
            </a>
            <a href="<?= base_url('/') ?>" class="action-card" target="_blank">
                <div class="action-icon">🌐</div>
                <span>Ver Sitio</span>
            </a>
            <a href="<?= base_url('admin/configuracion') ?>" class="action-card">
                <div class="action-icon">⚙️</div>
                <span>Configuración</span>
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
