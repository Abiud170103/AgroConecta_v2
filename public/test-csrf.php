<?php
// Configuración
date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Constantes
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta_v2/public');

// Incluir configuración y clases necesarias  
require_once '../config/database.php';

// Cargar todas las clases necesarias (usando rutas correctas)
require_once '../app/core/Database.php';
require_once '../app/models/Usuario.php';
require_once '../app/core/Controller.php';
require_once '../app/controllers/BaseController.php';
require_once '../app/controllers/AuthController.php';
require_once '../core/Router.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test CSRF - AgroConecta</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        form { margin: 20px 0; padding: 20px; border: 1px solid #ccc; }
        input[type="email"], input[type="hidden"] { width: 300px; padding: 8px; margin: 5px 0; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🔧 Test del Sistema CSRF</h1>
    
    <?php
    // Generar nuevo token CSRF
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        echo "<div class='debug'><strong>✨ Nuevo token CSRF generado</strong></div>\n";
    }
    
    $currentToken = $_SESSION['csrf_token'];
    echo "<div class='debug'>";
    echo "<h3>📋 Estado de la sesión:</h3>\n";
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>\n";
    echo "<p><strong>CSRF Token:</strong> <code>" . htmlspecialchars($currentToken) . "</code></p>\n";
    echo "<p><strong>Token Length:</strong> " . strlen($currentToken) . " caracteres</p>\n";
    echo "</div>\n";
    
    // Procesar formulario si es POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<div class='debug'>";
        echo "<h3>📨 Datos recibidos por POST:</h3>\n";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($_POST['email'] ?? 'No enviado') . "</p>\n";
        echo "<p><strong>Token enviado:</strong> <code>" . htmlspecialchars($_POST['csrf_token'] ?? 'No enviado') . "</code></p>\n";
        echo "<p><strong>Token de sesión:</strong> <code>" . htmlspecialchars($_SESSION['csrf_token'] ?? 'No existe') . "</code></p>\n";
        echo "</div>\n";
        
        $submittedToken = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        if (empty($submittedToken)) {
            echo "<div class='error'><strong>❌ Error:</strong> Token CSRF no proporcionado</div>\n";
        } elseif (empty($sessionToken)) {
            echo "<div class='error'><strong>❌ Error:</strong> Token CSRF no existe en sesión</div>\n";
        } elseif ($submittedToken !== $sessionToken) {
            echo "<div class='error'>";
            echo "<strong>❌ Error:</strong> Tokens no coinciden<br>\n";
            echo "Enviado: " . htmlspecialchars($submittedToken) . "<br>\n";
            echo "Sesión: " . htmlspecialchars($sessionToken) . "<br>\n";
            echo "Coinciden con hash_equals: " . (hash_equals($sessionToken, $submittedToken) ? 'SÍ' : 'NO');
            echo "</div>\n";
        } else {
            echo "<div class='success'><strong>✅ Éxito:</strong> Token CSRF válido</div>\n";
            
            // Ahora probar el AuthController
            echo "<div class='debug'>";
            echo "<h3>🧪 Testing AuthController...</h3>\n";
            
            try {
                $authController = new AuthController();
                
                // Simular el procesamiento
                ob_start();
                $authController->processForgotPassword();
                $output = ob_get_contents();
                ob_end_clean();
                
                echo "<p><strong>Salida del controlador:</strong></p>\n";
                echo "<pre>" . htmlspecialchars($output) . "</pre>\n";
                
                // Verificar mensajes flash
                echo "<p><strong>Mensaje de éxito:</strong> " . ($_SESSION['success'] ?? 'Ninguno') . "</p>\n";
                echo "<p><strong>Mensaje de error:</strong> " . ($_SESSION['error'] ?? 'Ninguno') . "</p>\n";
                
            } catch (Exception $e) {
                echo "<div class='error'><strong>Error en AuthController:</strong> " . htmlspecialchars($e->getMessage()) . "</div>\n";
            }
            
            echo "</div>\n";
        }
    }
    ?>
    
    <h2>📝 Formulario de Test</h2>
    <form method="POST">
        <p>
            <label for="email">Email:</label><br>
            <input type="email" name="email" id="email" value="abiud170103@gmail.com" required>
        </p>
        <p>
            <label for="csrf_token">Token CSRF:</label><br>
            <input type="text" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($currentToken); ?>">
        </p>
        <p>
            <button type="submit">🧪 Test Forgot Password</button>
        </p>
    </form>
    
    <h2>🔄 Acciones Rápidas</h2>
    <p>
        <a href="?action=clear_session">🗑️ Limpiar Sesión</a> | 
        <a href="?action=new_token">🔄 Generar Nuevo Token</a> |
        <a href="login">🏠 Ir a Login</a>
    </p>
    
    <?php
    // Acciones rápidas
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'clear_session':
                session_destroy();
                session_start();
                echo "<div class='warning'><strong>⚠️ Sesión limpiada.</strong> <a href='test-csrf.php'>Recargar página</a></div>\n";
                break;
                
            case 'new_token':
                unset($_SESSION['csrf_token']);
                echo "<div class='warning'><strong>🔄 Token eliminado.</strong> <a href='test-csrf.php'>Recargar para generar nuevo</a></div>\n";
                break;
        }
    }
    ?>
    
</body>
</html>