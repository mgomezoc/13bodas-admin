<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    
    <title>Iniciar Sesión | Admin 13Bodas</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/svg+xml" href="<?= base_url('img/favicon.svg') ?>">
    <meta name="theme-color" content="#0a0510">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #0a0510;
            --bg-card: rgba(255, 255, 255, 0.03);
            --border-color: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --primary-cyan: #00f5ff;
            --primary-purple: #a855f7;
            --error: #ef4444;
            --success: #22c55e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1.5rem;
            padding: 2.5rem;
            backdrop-filter: blur(10px);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo img {
            max-width: 150px;
            height: auto;
        }
        
        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        
        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-cyan);
            box-shadow: 0 0 0 3px rgba(0, 245, 255, 0.1);
        }
        
        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .remember-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: var(--text-secondary);
        }
        
        .remember-label input {
            accent-color: var(--primary-cyan);
        }
        
        .forgot-link {
            color: var(--primary-cyan);
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .forgot-link:hover {
            opacity: 0.8;
        }
        
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-cyan), var(--primary-purple));
            border: none;
            border-radius: 0.75rem;
            color: var(--bg-dark);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 245, 255, 0.2);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: var(--primary-cyan);
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <a href="<?= base_url('/') ?>">
                    <img src="<?= base_url('img/13bodas-logo-blanco-transparente.png') ?>" alt="13Bodas">
                </a>
            </div>
            
            <h1 class="login-title">Panel de Administración</h1>
            <p class="login-subtitle">Ingresa tus credenciales para continuar</p>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            
            <form action="<?= base_url('auth/login') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="tu@email.com"
                        value="<?= old('email') ?>"
                        required
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="••••••••"
                        required
                    >
                </div>
                
                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" value="1">
                        Recordarme
                    </label>
                </div>
                
                <button type="submit" class="btn-login">
                    Iniciar Sesión
                </button>
            </form>
            
            <a href="<?= base_url('/') ?>" class="back-link">
                ← Volver al sitio
            </a>
        </div>
    </div>
</body>
</html>
