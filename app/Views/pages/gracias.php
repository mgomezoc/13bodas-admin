<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>¡Gracias por tu mensaje!<?= $this->endSection() ?>

<?= $this->section('description') ?>Hemos recibido tu solicitud. Te responderemos en menos de 24 horas.<?= $this->endSection() ?>

<?= $this->section('robots') ?>noindex, nofollow<?= $this->endSection() ?>

<?= $this->section('header') ?>
<?= $this->include('partials/header') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="gracias-section section-padding">
    <div class="container">
        <div class="gracias-content" data-aos="fade-up">
            <div class="gracias-icon">
                <svg width="80" height="80" viewBox="0 0 80 80" fill="none" stroke="currentColor" stroke-width="3">
                    <circle cx="40" cy="40" r="35" />
                    <path d="M25 40l10 10 20-20" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
            <h1 class="gracias-title">
                ¡Gracias por <span class="gradient-text">contactarnos</span>!
            </h1>
            
            <p class="gracias-description">
                Hemos recibido tu solicitud y estamos emocionados de ayudarte a crear 
                una experiencia inolvidable para tu evento.
            </p>
            
            <div class="gracias-info">
                <div class="info-item">
                    <span class="info-icon">⏰</span>
                    <span class="info-text">Te responderemos en <strong>menos de 24 horas</strong></span>
                </div>
                <div class="info-item">
                    <span class="info-icon">📧</span>
                    <span class="info-text">Revisa tu bandeja de entrada y spam</span>
                </div>
                <div class="info-item">
                    <span class="info-icon">💬</span>
                    <span class="info-text">¿Urgente? Escríbenos por <a href="https://wa.me/528115247741" target="_blank" rel="noopener">WhatsApp</a></span>
                </div>
            </div>
            
            <div class="gracias-cta">
                <a href="<?= base_url('/') ?>" class="btn btn-primary">Volver al Inicio</a>
                <a href="https://wa.me/528115247741" class="btn btn-secondary" target="_blank" rel="noopener">
                    Contactar por WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<?= $this->include('partials/footer') ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .gracias-section {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    
    .gracias-content {
        max-width: 600px;
        margin: 0 auto;
    }
    
    .gracias-icon {
        margin-bottom: 2rem;
        color: var(--primary-cyan, #00f5ff);
    }
    
    .gracias-icon svg {
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
    }
    
    .gracias-title {
        font-size: clamp(2rem, 5vw, 3rem);
        margin-bottom: 1rem;
        font-family: var(--font-serif, 'Playfair Display', serif);
    }
    
    .gracias-description {
        font-size: 1.125rem;
        color: var(--text-secondary, rgba(255, 255, 255, 0.7));
        margin-bottom: 2rem;
    }
    
    .gracias-info {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
    }
    
    .info-item:not(:last-child) {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .info-icon {
        font-size: 1.25rem;
    }
    
    .info-text {
        color: var(--text-secondary, rgba(255, 255, 255, 0.7));
    }
    
    .info-text a {
        color: var(--primary-cyan, #00f5ff);
        text-decoration: underline;
    }
    
    .gracias-cta {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }
</style>
<?= $this->endSection() ?>
