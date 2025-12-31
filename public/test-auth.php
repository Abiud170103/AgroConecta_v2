<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba - AgroConecta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #28a745; }
        .test-item {
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-left: 4px solid #28a745;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        a {
            color: #28a745;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🌾 AgroConecta - Prueba de Autenticación</h1>
        
        <div class="test-item">
            <strong>✅ PHP funcionando:</strong> <?php echo PHP_VERSION; ?>
        </div>
        
        <div class="test-item">
            <strong>✅ Sesión:</strong> 
            <?php 
            session_start();
            echo session_id() ? 'Activa' : 'Inactiva'; 
            ?>
        </div>
        
        <div class="test-item">
            <strong>✅ Ruta del proyecto:</strong> <?php echo __DIR__; ?>
        </div>
        
        <h2>Enlaces de Prueba:</h2>
        
        <div class="test-item">
            <a href="login.php" target="_blank">→ Ir a Login (directo)</a>
        </div>
        
        <div class="test-item">
            <a href="register.php" target="_blank">→ Ir a Registro (directo)</a>
        </div>
        
        <div class="test-item">
            <a href="forgot-password.php" target="_blank">→ Ir a Recuperar Contraseña (directo)</a>
        </div>
        
        <h2>Verificación de Archivos:</h2>
        
        <div class="test-item">
            <strong>Login:</strong> 
            <span class="<?php echo file_exists('login.php') ? 'success' : 'error'; ?>">
                <?php echo file_exists('login.php') ? '✓ Existe' : '✗ No encontrado'; ?>
            </span>
        </div>
        
        <div class="test-item">
            <strong>Registro:</strong> 
            <span class="<?php echo file_exists('register.php') ? 'success' : 'error'; ?>">
                <?php echo file_exists('register.php') ? '✓ Existe' : '✗ No encontrado'; ?>
            </span>
        </div>
        
        <div class="test-item">
            <strong>Recuperar Contraseña:</strong> 
            <span class="<?php echo file_exists('forgot-password.php') ? 'success' : 'error'; ?>">
                <?php echo file_exists('forgot-password.php') ? '✓ Existe' : '✗ No encontrado'; ?>
            </span>
        </div>
        
        <div class="test-item">
            <strong>CSS Auth:</strong> 
            <span class="<?php echo file_exists('../css/auth.css') ? 'success' : 'error'; ?>">
                <?php echo file_exists('../css/auth.css') ? '✓ Existe' : '✗ No encontrado'; ?>
            </span>
        </div>
        
        <div class="test-item">
            <strong>JS Auth:</strong> 
            <span class="<?php echo file_exists('../js/auth.js') ? 'success' : 'error'; ?>">
                <?php echo file_exists('../js/auth.js') ? '✓ Existe' : '✗ No encontrado'; ?>
            </span>
        </div>
    </div>
</body>
</html>
