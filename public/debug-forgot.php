<?php
session_start();

// Configuración básica
date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Constantes
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', BASE_PATH . '/app');

// Incluir archivos necesarios
require_once '../config/database.php';
require_once '../app/core/Database.php';
require_once '../app/models/Usuario.php';
require_once '../app/core/Controller.php';
require_once '../app/controllers/BaseController.php';
require_once '../app/controllers/AuthController.php';

// Crear el controlador
$authController = new AuthController();

echo "<h1>🔍 Debug - Forgot Password Flow</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "<h2>📋 Estado inicial:</h2>";
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
    echo "<p><strong>CSRF Token en sesión:</strong> " . ($_SESSION['csrf_token'] ?? 'No existe') . "</p>";
    
    echo "<h2>🔄 Simulando AuthController->forgotPassword():</h2>";
    
    // Simular lo que hace el método forgotPassword
    ob_start();
    $authController->forgotPassword();
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<p><strong>CSRF Token después de generateCSRF():</strong> " . ($_SESSION['csrf_token'] ?? 'Aún no existe') . "</p>";
    
    echo "<h2>📝 Formulario de Prueba:</h2>";
    echo "<form method='POST'>";
    echo "<input type='email' name='email' value='abiud170103@gmail.com' required><br><br>";
    echo "<input type='hidden' name='csrf_token' value='" . ($_SESSION['csrf_token'] ?? '') . "'>";
    echo "<input type='submit' value='Test Forgot Password'>";
    echo "</form>";
    
    echo "<p><strong>Token que se enviará:</strong> " . ($_SESSION['csrf_token'] ?? 'Ninguno') . "</p>";
    
} else {
    echo "<h2>📨 Procesando POST:</h2>";
    echo "<p><strong>Email recibido:</strong> " . ($_POST['email'] ?? 'No enviado') . "</p>";
    echo "<p><strong>Token recibido:</strong> " . ($_POST['csrf_token'] ?? 'No enviado') . "</p>";
    echo "<p><strong>Token en sesión:</strong> " . ($_SESSION['csrf_token'] ?? 'No existe') . "</p>";
    
    // Verificar si coinciden
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $submittedToken = $_POST['csrf_token'] ?? '';
    
    if (empty($sessionToken)) {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
        echo "❌ <strong>ERROR:</strong> No hay token en la sesión";
        echo "</div>";
    } elseif (empty($submittedToken)) {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
        echo "❌ <strong>ERROR:</strong> No se envió token en el formulario";
        echo "</div>";
    } elseif ($sessionToken === $submittedToken) {
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0;'>";
        echo "✅ <strong>ÉXITO:</strong> Los tokens coinciden";
        echo "</div>";
        
        // Ahora probar el AuthController
        echo "<h3>🧪 Testing AuthController->processForgotPassword():</h3>";
        try {
            ob_start();
            $authController->processForgotPassword();
            $output = ob_get_contents();
            ob_end_clean();
            
            echo "<p><strong>Output:</strong> " . htmlspecialchars($output) . "</p>";
            echo "<p><strong>Success Message:</strong> " . ($_SESSION['success'] ?? 'Ninguno') . "</p>";
            echo "<p><strong>Error Message:</strong> " . ($_SESSION['error'] ?? 'Ninguno') . "</p>";
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
            echo "❌ <strong>Exception:</strong> " . $e->getMessage();
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
        echo "❌ <strong>ERROR:</strong> Los tokens no coinciden<br>";
        echo "Sesión: " . htmlspecialchars($sessionToken) . "<br>";
        echo "Enviado: " . htmlspecialchars($submittedToken);
        echo "</div>";
    }
}
?>