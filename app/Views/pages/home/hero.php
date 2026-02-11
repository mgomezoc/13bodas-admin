<section id="inicio" class="hero">
    <div class="hero-bg">
        <div class="hero-grid"></div>
        <div class="hero-glow"></div>
    </div>

    <div class="container hero-container">
        <div class="hero-content">
            <span class="hero-badge" data-aos="fade-up">
                <span class="badge-dot"></span>
                Plataforma SaaS • Bodas • XV • Eventos
            </span>

            <h1 class="hero-title" data-aos="fade-up" data-aos-delay="100">
                Crea tu invitación digital<br>
                <span class="gradient-text">y activa RSVP</span><br>
                en minutos, desde<br>
                <span class="gradient-text">cualquier lugar</span>
            </h1>

            <p class="hero-description" data-aos="fade-up" data-aos-delay="200">
                Regístrate gratis, publica tu página del evento y administra invitados en un solo panel.
                <strong>Sin instalaciones y listo para compartir por WhatsApp.</strong>
            </p>

            <div class="hero-stats" data-aos="fade-up" data-aos-delay="250">
                <div class="stat">
                    <span class="stat-icon">⚡</span>
                    <span class="stat-text">Activación rápida</span>
                </div>
                <div class="stat">
                    <span class="stat-icon">🌍</span>
                    <span class="stat-text">Uso global</span>
                </div>
                <div class="stat">
                    <span class="stat-icon">✅</span>
                    <span class="stat-text">RSVP integrado</span>
                </div>
            </div>

            <div class="hero-cta" data-aos="fade-up" data-aos-delay="300">
                <a href="<?= site_url(route_to('register.index')) ?>" class="btn btn-primary">
                    Crear cuenta gratis
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3.5L8.5 5l5 5-5 5L10 16.5l6.5-6.5z"/>
                    </svg>
                </a>
                <a href="#magiccam" class="btn btn-secondary">Ver demo en vivo</a>
            </div>
        </div>

        <div class="hero-visual" data-aos="zoom-in" data-aos-delay="400">
            <div class="phone-mockup">
                <div class="phone-frame">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <?php $heroImagePath = file_exists(FCPATH . 'img/home-hero-preview.png')
                            ? 'img/home-hero-preview.png'
                            : 'img/demo-preview.png'; ?>
                        <img
                            src="<?= esc(base_url($heroImagePath)) ?>"
                            alt="Vista previa de invitación digital en 13Bodas"
                            class="phone-content"
                            loading="eager"
                            width="375"
                            height="812"
                        >
                        <div class="phone-overlay">
                            <div class="ar-badge">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M24 4L8 14v12c0 10 5 18 16 20 11-2 16-10 16-20V14L24 4z"/>
                                </svg>
                                <span>Prueba activa</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="phone-glow"></div>
            </div>
        </div>
    </div>

    <div class="scroll-indicator" data-aos="fade-up" data-aos-delay="500">
        <span>Descubre todo lo que incluye</span>
        <div class="scroll-line"></div>
    </div>
</section>
