<?php
session_start();
date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Constantes
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta_v2/public');

try {
    // Incluir archivos necesarios
    require_once '../config/database.php';
    require_once '../app/core/Database.php';
    require_once '../app/models/Usuario.php';
    require_once '../app/core/Controller.php';
    require_once '../app/controllers/BaseController.php';
    require_once '../app/controllers/AuthController.php';
    
    $authController = new AuthController();
    
    // Usar reflection para acceder a métodos protegidos
    $reflection = new ReflectionClass($authController);
    
    // Simular lo que hace forgotPassword pero sin layout
    if ($authController->isLoggedIn ?? false) {
        header('Location: /AgroConecta_v2/public/dashboard');
        exit;
    }
    
    // Generar token CSRF
    $generateMethod = $reflection->getMethod('generateCSRF');
    $generateMethod->setAccessible(true);
    $csrf_token = $generateMethod->invoke($authController);
    
    // Establecer datos de la vista
    $setViewDataMethod = $reflection->getMethod('setViewData');
    $setViewDataMethod->setAccessible(true);
    $setViewDataMethod->invoke($authController, 'csrf_token', $csrf_token);
    $setViewDataMethod->invoke($authController, 'pageTitle', 'Recuperar Contraseña');
    
    // Obtener mensajes flash
    $flashMethod = $reflection->getMethod('getFlashMessage');
    $flashMethod->setAccessible(true);
    $error = $flashMethod->invoke($authController, 'error');
    $success = $flashMethod->invoke($authController, 'success');
    
    $setViewDataMethod->invoke($authController, 'error', $error);
    $setViewDataMethod->invoke($authController, 'success', $success);
    
    // Renderizar solo la vista sin layout
    $viewMethod = $reflection->getMethod('view');
    $viewMethod->setAccessible(true);
    
    $viewMethod->invoke($authController, 'auth/forgot-password');
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; margin: 20px; border: 1px solid #f5c6cb;'>";
    echo "<h3>Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "</div>";
    
    echo "<p><a href='/AgroConecta_v2/public/login'>Volver al login</a></p>";
}
?>