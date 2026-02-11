<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Plataforma de Invitaciones Digitales con RSVP | 13Bodas<?= $this->endSection() ?>

<?= $this->section('description') ?>Crea tu invitación digital profesional, activa RSVP y administra invitados desde un panel web. Regístrate gratis en 13Bodas y publica tu evento desde cualquier país.<?= $this->endSection() ?>

<?= $this->section('meta_tags') ?>
<meta name="keywords" content="plataforma invitaciones digitales, RSVP online, software para eventos, wedding website builder, invitaciones digitales internacionales, registro de invitados, invitación digital con cuenta regresiva">
<meta name="application-name" content="13Bodas">
<meta name="format-detection" content="telephone=no">
<meta name="theme-color" content="#0a0510">
<?= $this->endSection() ?>

<?= $this->section('robots') ?>index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1<?= $this->endSection() ?>

<?= $this->section('og_title') ?>13Bodas | Plataforma de Invitaciones Digitales con RSVP<?= $this->endSection() ?>

<?= $this->section('og_description') ?>Lanza tu invitación digital, gestiona confirmaciones RSVP y comparte tu evento con invitados de cualquier lugar. Regístrate gratis y pruébalo hoy.<?= $this->endSection() ?>

<?= $this->section('twitter_title') ?>13Bodas | Invitaciones Digitales + RSVP<?= $this->endSection() ?>

<?= $this->section('twitter_description') ?>Crea y publica tu invitación digital con RSVP desde cualquier país. Prueba gratis la plataforma.<?= $this->endSection() ?>

<?= $this->section('structured_data') ?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "13Bodas",
    "url": "<?= esc(base_url()) ?>",
    "logo": "<?= esc(base_url('img/logo-13bodas.png')) ?>",
    "description": "Plataforma para crear invitaciones digitales y administrar RSVP para eventos.",
    "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "sales",
        "telephone": "+52-81-1524-7741",
        "availableLanguage": ["es", "en"],
        "areaServed": "Worldwide"
    },
    "sameAs": [
        "https://www.facebook.com/13bodas",
        "https://magiccam.13bodas.com"
    ],
    "knowsAbout": [
        "Invitaciones digitales",
        "RSVP online",
        "Gestión de invitados",
        "Filtros AR para eventos"
    ]
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "13Bodas Platform",
    "applicationCategory": "BusinessApplication",
    "operatingSystem": "Web",
    "inLanguage": "es-MX",
    "url": "<?= esc(base_url()) ?>",
    "description": "Plataforma web para crear invitaciones digitales con RSVP, gestión de invitados y página pública del evento.",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD",
        "description": "Registro gratuito con demo inicial"
    },
    "provider": {
        "@type": "Organization",
        "name": "13Bodas",
        "url": "<?= esc(base_url()) ?>"
    }
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "Plataforma de Invitaciones Digitales con RSVP",
    "provider": {
        "@type": "Organization",
        "name": "13Bodas"
    },
    "areaServed": "Worldwide",
    "serviceType": "Event invitation and RSVP management software"
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "¿Puedo usar 13Bodas fuera de México?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Sí. La plataforma funciona online y puedes administrar tu evento desde cualquier país."
            }
        },
        {
            "@type": "Question",
            "name": "¿Qué significa RSVP en una invitación?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "RSVP significa por favor confirma tu asistencia. Te ayuda a saber quiénes asistirán para organizar mejor lugares, alimentos y logística."
            }
        },
        {
            "@type": "Question",
            "name": "¿La prueba incluye RSVP?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Sí. Al registrarte tienes acceso al flujo de confirmación RSVP y al panel para gestionar invitados."
            }
        },
        {
            "@type": "Question",
            "name": "¿Necesito instalar una app?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "No. Todo funciona desde el navegador en celular o computadora."
            }
        }
    ]
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "13Bodas",
    "url": "<?= esc(base_url()) ?>",
    "inLanguage": "es-MX"
}
</script>
<?= $this->endSection() ?>

<?= $this->section('header') ?>
<?= $this->include('partials/header') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('pages/home/hero') ?>
<?= $this->include('pages/home/services') ?>
<?= $this->include('pages/home/magiccam') ?>
<?= $this->include('pages/home/packages') ?>
<?= $this->include('pages/home/process') ?>
<?= $this->include('pages/home/faq') ?>
<?= $this->include('pages/home/contact') ?>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<?= $this->include('partials/footer') ?>
<?= $this->endSection() ?>
