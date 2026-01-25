<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Invitaciones Digitales y Filtros AR para Bodas, XV Años y Eventos<?= $this->endSection() ?>

<?= $this->section('description') ?>Crea invitaciones digitales elegantes y filtros de realidad aumentada personalizados para bodas, XV años y eventos. Sin apps, desde el navegador. Servicio global.<?= $this->endSection() ?>

<?= $this->section('meta_tags') ?>
<meta name="keywords" content="invitaciones digitales bodas, filtros AR eventos, MagicCam, invitaciones web XV años, realidad aumentada bodas, filtros personalizados eventos">
<?= $this->endSection() ?>

<?= $this->section('structured_data') ?>
<!-- Structured Data - Organization -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "13Bodas",
    "image": "<?= base_url('img/logo-13bodas.png') ?>",
    "description": "Agencia especializada en invitaciones digitales y filtros de realidad aumentada para bodas, XV años y eventos sociales y corporativos",
    "url": "<?= base_url() ?>",
    "telephone": "+528115247741",
    "priceRange": "$$",
    "address": {
        "@type": "PostalAddress",
        "addressCountry": "MX",
        "addressRegion": "Nuevo León"
    },
    "geo": {
        "@type": "GeoCoordinates",
        "latitude": "25.6866",
        "longitude": "-100.3161"
    },
    "sameAs": [
        "https://www.facebook.com/13bodas",
        "https://magiccam.13bodas.com"
    ],
    "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
        "opens": "09:00",
        "closes": "20:00"
    }
}
</script>

<!-- Structured Data - Service -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Service",
    "serviceType": "Invitaciones Digitales y Filtros AR",
    "provider": {
        "@type": "LocalBusiness",
        "name": "13Bodas"
    },
    "areaServed": {
        "@type": "Country",
        "name": "México"
    },
    "hasOfferCatalog": {
        "@type": "OfferCatalog",
        "name": "Servicios 13Bodas",
        "itemListElement": [
            {
                "@type": "Offer",
                "itemOffered": {
                    "@type": "Service",
                    "name": "Invitaciones Digitales Personalizadas",
                    "description": "Páginas web elegantes con toda la información del evento"
                }
            },
            {
                "@type": "Offer",
                "itemOffered": {
                    "@type": "Service",
                    "name": "Filtros AR MagicCam",
                    "description": "Filtros de realidad aumentada personalizados para eventos"
                }
            }
        ]
    }
}
</script>

<!-- Structured Data - FAQPage -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "¿Qué son los filtros AR de 13Bodas?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Son filtros de realidad aumentada personalizados que tus invitados pueden usar escaneando un código QR desde su celular. No necesitan descargar apps, funciona directo desde el navegador web."
            }
        },
        {
            "@type": "Question",
            "name": "¿Cuánto tiempo toma crear una invitación digital?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "El proceso completo toma entre 7 a 14 días dependiendo del paquete seleccionado y la complejidad del diseño. Trabajamos contigo en cada paso para asegurar que el resultado sea perfecto."
            }
        },
        {
            "@type": "Question",
            "name": "¿Ofrecen servicio internacional?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Sí, nuestros servicios son 100% digitales, por lo que trabajamos con clientes en todo el mundo sin necesidad de estar físicamente presentes."
            }
        }
    ]
}
</script>
<?= $this->endSection() ?>

<?= $this->section('header') ?>
<?= $this->include('partials/header') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- HERO Section -->
<?= $this->include('pages/home/hero') ?>

<!-- SERVICIOS Section -->
<?= $this->include('pages/home/services') ?>

<!-- MAGICCAM Demo Section -->
<?= $this->include('pages/home/magiccam') ?>

<!-- PAQUETES Section -->
<?= $this->include('pages/home/packages') ?>

<!-- PROCESO Section -->
<?= $this->include('pages/home/process') ?>

<!-- FAQ Section -->
<?= $this->include('pages/home/faq') ?>

<!-- CONTACTO Section -->
<?= $this->include('pages/home/contact') ?>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<?= $this->include('partials/footer') ?>
<?= $this->endSection() ?>
