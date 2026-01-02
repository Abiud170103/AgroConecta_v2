<?php
/**
 * Página temporal para probar recuperación de contraseña
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
    echo "<h2>🔧 Procesando solicitud de reset...</h2>\n";
    
    // Mostrar datos recibidos
    echo "<p><strong>Email recibido:</strong> " . htmlspecialchars($_POST['email'] ?? 'No enviado') . "</p>\n";
    
    $email = $_POST['email'] ?? '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
        echo "<h3>❌ Error:</h3>";
        echo "<p>Email inválido: " . htmlspecialchars($email) . "</p>";
        echo "</div>";
    } else {
        // Procesar directamente sin AuthController para evitar problemas de CSRF
        echo "<h3>📧 Procesando email: " . htmlspecialchars($email) . "</h3>\n";
        
        try {
            $userModel = new Usuario();
            $token = $userModel->generateResetToken($email);
            
            if ($token) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 20px 0;'>";
                echo "<h4>✅ Token generado exitosamente</h4>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
                echo "<p><strong>Token (primeros 16 chars):</strong> " . htmlspecialchars(substr($token, 0, 16)) . "...</p>";
                echo "<p><strong>Token completo:</strong> <code style='word-break: break-all; font-size: 12px;'>" . htmlspecialchars($token) . "</code></p>";
                echo "<p>✉️ En un sistema real, este token se enviaría por email.</p>";
                echo "</div>\n";
                
                // Verificar en base de datos
                echo "<h3>🔍 Verificación en base de datos:</h3>\n";
                $pdo = new PDO($dsn, $username, $password, $options);
                $stmt = $pdo->prepare("SELECT correo, token_reset, fecha_actualizacion FROM Usuario WHERE correo = ? AND token_reset IS NOT NULL");
                $stmt->execute([$email]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px;'>";
                    echo "<p>✅ Token confirmado en base de datos</p>";
                    echo "<p><strong>Fecha actualización:</strong> " . $result['fecha_actualizacion'] . "</p>";
                    echo "</div>\n";
                } else {
                    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px;'>";
                    echo "<p>❌ Token no encontrado en base de datos</p>";
                    echo "</div>\n";
                }
                
            } else {
                echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin: 10px 0;'>";
                echo "<h3>⚠️ Usuario no encontrado</h3>";
                echo "<p>No se encontró un usuario con el email: " . htmlspecialchars($email) . "</p>";
                echo "<p>En un sistema real, se mostraría el mismo mensaje por seguridad.</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0;'>";
            echo "<h3>❌ Error de sistema:</h3>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    }
    
    echo "<p style='margin-top: 30px;'>";
    echo "<a href='login' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; margin-right: 10px;'>Ir al Login</a>";
    echo "<a href='olvide-password_temp.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none;'>Probar de Nuevo</a>";
    echo "</p>\n";
    
    exit;
}

// Mostrar formulario - definir variables para la vista
$csrf_token = bin2hex(random_bytes(16)); // Token temporal
$pageTitle = 'Recuperar Contraseña';
$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;

// Limpiar mensajes flash
unset($_SESSION['error'], $_SESSION['success']);

// Incluir la vista de recuperación de contraseña directamente
require_once APP_PATH . '/views/auth/forgot-password.php';
?>