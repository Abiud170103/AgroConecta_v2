<?php
session_start();
date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Constantes básicas
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', BASE_PATH . '/app');
define('BASE_URL', 'http://localhost/AgroConecta_v2/public');

// Incluir archivos necesarios
require_once '../config/database.php';
require_once '../app/core/Database.php';
require_once '../app/models/Usuario.php';
require_once '../app/core/Controller.php';
require_once '../app/controllers/BaseController.php';
require_once '../app/controllers/AuthController.php';

echo "<h1>🔧 Test Directo de Forgot Password</h1>";

try {
    $authController = new AuthController();
    echo "<p>✅ AuthController creado</p>";
    
    // Capturar la salida
    ob_start();
    $authController->forgotPassword();
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<h2>📄 Output del método forgotPassword():</h2>";
    
    if (strlen($output) > 0) {
        echo "<div style='border: 2px solid #28a745; padding: 20px; margin: 20px 0; background: #f8f9fa;'>";
        echo $output;
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ No se produjo output</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<h2>🔗 Enlaces:</h2>";
echo "<p><a href='/AgroConecta_v2/public/'>Inicio</a> | <a href='/AgroConecta_v2/public/login'>Login</a></p>";
?>