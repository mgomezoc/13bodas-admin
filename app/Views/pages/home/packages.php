<section id="paquetes" class="packages section-padding">
    <div class="container">
        <header class="section-header" data-aos="fade-up">
            <span class="section-tag">Planes</span>
            <h2 class="section-title">
                Empieza gratis y escala<br>
                <span class="gradient-text">según tu evento</span>
            </h2>
            <p class="section-description">
                Diseñado para parejas, planners y agencias que necesitan operar eventos de forma profesional
            </p>
        </header>

        <div class="packages-grid">
            <article class="package-card" data-aos="fade-up" data-aos-delay="100">
                <div class="package-header">
                    <h3 class="package-name">Starter</h3>
                    <p class="package-tagline">Para comenzar en minutos</p>
                </div>
                <div class="package-price">
                    <span class="price-label">Incluye</span>
                    <span class="price-value">Prueba gratis</span>
                </div>
                <ul class="package-features">
                    <li>✓ Evento demo al registrarte</li>
                    <li>✓ Página pública con diseño profesional</li>
                    <li>✓ RSVP básico y lista de invitados</li>
                    <li>✓ Configuración inicial en minutos</li>
                    <li>✓ Soporte por email</li>
                </ul>
                <a href="<?= site_url(route_to('register.index')) ?>" class="btn btn-outline" data-track-cta="ver_paquetes" data-position="pricing" data-package-type="essential">Comenzar gratis</a>
            </article>

            <article class="package-card package-card-popular" data-aos="fade-up" data-aos-delay="200">
                <div class="package-badge">Más elegido</div>
                <div class="package-header">
                    <h3 class="package-name">Growth</h3>
                    <p class="package-tagline">Para eventos con alta interacción</p>
                </div>
                <div class="package-price">
                    <span class="price-label">Activa</span>
                    <span class="price-value">RSVP pro</span>
                </div>
                <ul class="package-features">
                    <li>✓ Todo lo de Starter</li>
                    <li>✓ RSVP avanzado y preguntas personalizadas</li>
                    <li>✓ Gestión por grupos y exportación</li>
                    <li>✓ Módulos de galería, agenda y recomendaciones</li>
                    <li>✓ Métricas para decisiones de logística</li>
                </ul>
                <a href="<?= site_url(route_to('register.index')) ?>" class="btn btn-primary" data-track-cta="ver_paquetes" data-position="pricing" data-package-type="interactive">Probar plataforma</a>
            </article>

            <article class="package-card" data-aos="fade-up" data-aos-delay="300">
                <div class="package-header">
                    <h3 class="package-name">Scale</h3>
                    <p class="package-tagline">Para planners y agencias</p>
                </div>
                <div class="package-price">
                    <span class="price-label">Ideal para</span>
                    <span class="price-value">Multi-evento</span>
                </div>
                <ul class="package-features">
                    <li>✓ Operación de múltiples eventos</li>
                    <li>✓ Soporte para equipos de trabajo</li>
                    <li>✓ Integraciones y dominios personalizados</li>
                    <li>✓ MagicCam y activos interactivos</li>
                    <li>✓ Onboarding asistido</li>
                </ul>
                <a href="#contacto" class="btn btn-outline" data-track-cta="solicitar_demo_ar" data-position="pricing" data-package-type="infinity">Hablar con ventas</a>
            </article>
        </div>

        <div class="packages-note" data-aos="fade-up">
            <p>
                💡 <strong>¿Quieres validar rápido?</strong> Crea tu cuenta y explora la demo sin costo.
                <a href="<?= site_url(route_to('register.index')) ?>" data-track-cta="ver_paquetes" data-position="pricing">Registrarme ahora →</a>
            </p>
        </div>
    </div>
</section>
