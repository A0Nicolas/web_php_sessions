<?php
session_start();
if (isset($_SESSION['usuario_activo'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Sistema Biblioteca</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99,102,241,0.3), transparent 70%);
            top: -200px;
            right: -200px;
            border-radius: 50%;
        }
        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(14,165,233,0.2), transparent 70%);
            bottom: -100px;
            left: -100px;
            border-radius: 50%;
        }
        .login-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        .login-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #4338ca, #6366f1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(67,56,202,0.3);
        }
        .login-icon svg { width: 36px; height: 36px; color: white; }
        h2 {
            text-align: center;
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .subtitle {
            text-align: center;
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 32px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            outline: none;
        }
        .form-group input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.15);
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4338ca, #6366f1);
            color: white;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #3730a3, #4f46e5);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(67,56,202,0.35);
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            text-align: center;
        }
        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #94a3b8;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <h2>SISTEMA BIBLIOTECA</h2>
        <p class="subtitle">Ingrese sus credenciales para acceder</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert-error">Usuario o contrasena incorrectos.</div>
        <?php endif; ?>

        <form method="POST" action="backend/procesar_login.php">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ingrese su usuario" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Contrasena</label>
                <input type="password" id="password" name="password" placeholder="Ingrese su contrasena" required>
            </div>
            <button type="submit" class="btn-login">Iniciar Sesion</button>
        </form>
        <p class="footer-text">Sistema de Gestion Bibliotecaria v1.0</p>
    </div>
</body>
</html>
