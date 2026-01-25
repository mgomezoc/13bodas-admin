<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($event['couple_title']) ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Usamos el color del evento si existe, si no, un default del template */
            --primary-color: <?= $theme['primary_color'] ?? '#D4AF37' ?>;
            --bg-color: #FAFAFA;
            --text-color: #333;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Lato', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        /* Hero Section estilo "LoveLove" */
        .header-hero {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            background-image: url('<?= base_url("img/demo-preview.png") ?>');
            /* Placeholder */
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.85);
            /* Capa blanca transparente */
        }

        .hero-content {
            position: relative;
            z-index: 10;
            border: 2px solid var(--primary-color);
            padding: 40px;
            max-width: 500px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            color: var(--primary-color);
            margin: 0;
            line-height: 1.2;
        }

        .subtitle {
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .date-location {
            margin-top: 20px;
            font-style: italic;
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
        }

        .rsvp-btn {
            margin-top: 30px;
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.8rem;
            transition: background 0.3s;
        }
    </style>
</head>

<body>

    <header class="header-hero">
        <div class="overlay"></div>
        <div class="hero-content">
            <p class="subtitle">¡Nos Casamos!</p>

            <h1><?= esc($event['couple_title']) ?></h1>

            <div class="date-location">
                <?= date('d • M • Y', strtotime($event['event_date_start'])) ?>
                <br>
                <?= esc($event['venue_name']) ?>
            </div>

            <a href="#rsvp" class="rsvp-btn">Confirmar Asistencia</a>
        </div>
    </header>

    <div class="container" style="padding: 50px 20px; text-align: center;">
        <?php if (!empty($modules)): ?>
            <?php foreach ($modules as $mod): ?>
                <div class="module">
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>La historia de amor comienza aquí...</p>
        <?php endif; ?>
    </div>

</body>

</html>