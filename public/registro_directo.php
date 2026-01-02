<?php
/**
 * Página de registro temporal sin layout - solución directa
 */

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
require_once '../app/core/Database.php';
require_once '../app/models/Model.php';
require_once '../app/models/Usuario.php';
require_once '../app/models/Notificacion.php';
require_once '../app/core/Controller.php';
require_once '../app/controllers/BaseController.php';
require_once '../app/controllers/AuthController.php';

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>🔧 Procesando Registro POST</h2>\n";
    echo "<p>✅ Método POST detectado</p>\n";
    
    // Mostrar datos recibidos (sin contraseñas)
    echo "<h3>📝 Datos recibidos:</h3>\n";
    echo "<ul>\n";
    foreach ($_POST as $key => $value) {
        if (in_array($key, ['password', 'password_confirm'])) {
            echo "<li><strong>{$key}:</strong> [OCULTO]</li>\n";
        } else {
            echo "<li><strong>{$key}:</strong> " . htmlspecialchars($value) . "</li>\n";
        }
    }
    echo "</ul>\n";
    
    try {
        echo "<p>🔧 Creando AuthController...</p>\n";
        $authController = new AuthController();
        echo "<p>✅ AuthController creado</p>\n";
        
        echo "<p>🚀 Llamando processRegister()...</p>\n";
        
        // Capturar cualquier output antes de la redirección
        ob_start();
        $authController->processRegister();
        $output = ob_get_contents();
        ob_end_clean();
        
        echo "<h3>💬 Output del processRegister:</h3>\n";
        echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px 0;'>";
        echo htmlspecialchars($output);
        echo "</div>\n";
        
        echo "<p>⚠️ Si ves esto, significa que NO hubo redirección</p>\n";
        
    } catch (Exception $e) {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0;'>";
        echo "<h3 style='color: #d32f2f;'>❌ Error en processRegister:</h3>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        echo "<h4>Stack Trace:</h4>";
        echo "<pre style='background: #f5f5f5; padding: 10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    }
    
    exit;
}

// Mostrar formulario - definir variables para la vista
$csrf_token = bin2hex(random_bytes(16)); // Token temporal
$pageTitle = 'Crear Cuenta';
$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;

// Limpiar mensajes flash
unset($_SESSION['error'], $_SESSION['success']);

// Incluir la vista de registro directamente
require_once APP_PATH . '/views/auth/register.php';
?>