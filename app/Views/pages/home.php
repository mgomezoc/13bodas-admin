<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Plataforma de Invitaciones Digitales con RSVP | 13Bodas<?= $this->endSection() ?>

<?= $this->section('description') ?>Crea tu invitación digital profesional, activa RSVP y administra invitados desde un panel web. Regístrate gratis en 13Bodas y publica tu evento desde cualquier país.<?= $this->endSection() ?>

<?= $this->section('meta_tags') ?>
<meta name="keywords" content="plataforma invitaciones digitales, RSVP online, software para eventos, wedding website builder, invitaciones digitales internacionales, registro de invitados, invitación digital con cuenta regresiva">
<meta name="application-name" content="13Bodas">
<meta name="format-detection" content="telephone=no">
<meta name="theme-color" content="#0D1F33">
<?= $this->endSection() ?>

<?= $this->section('robots') ?>index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1<?= $this->endSection() ?>

<?= $this->section('og_title') ?>13Bodas | Plataforma de Invitaciones Digitales con RSVP<?= $this->endSection() ?>

<?= $this->section('og_description') ?>Lanza tu invitación digital, gestiona confirmaciones RSVP y comparte tu evento con invitados de cualquier lugar. Regístrate gratis y pruébalo hoy.<?= $this->endSection() ?>

<?= $this->section('twitter_title') ?>13Bodas | Invitaciones Digitales + RSVP<?= $this->endSection() ?>

<?= $this->section('twitter_description') ?>Crea y publica tu invitación digital con RSVP desde cualquier país. Prueba gratis la plataforma.<?= $this->endSection() ?>

<?= $this->section('structured_data') ?>
<?= $homeStructuredDataScripts ?? '' ?>
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
