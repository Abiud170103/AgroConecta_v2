<?php
/**
 * Punto de entrada para AuthController
 * Permite llamar métodos específicos del controlador
 */

session_start();
date_default_timezone_set('America/Mexico_City');

// Constantes
define('BASE_PATH', realpath(__DIR__ . '/../..'));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta_v2/public');

try {
    // Cargar dependencias
    require_once '../../config/database.php';
    require_once '../../app/core/Database.php';
    require_once '../../app/models/Usuario.php';
    require_once '../../core/SessionManager.php';
    require_once '../../app/core/Controller.php';
    require_once '../controllers/BaseController.php';
    require_once '../controllers/AuthController.php';
    
    // Obtener acción
    $action = $_GET['action'] ?? '';
    
    // Verificar que la acción es válida
    $allowedActions = [
        'resetPassword',
        'processResetPassword',
        'forgotPassword', 
        'processForgotPassword'
    ];
    
    if (!in_array($action, $allowedActions)) {
        SessionManager::setFlash('error', 'Acción no válida');
        header('Location: ../../public/login.php');
        exit;
    }
    
    // Crear instancia del controlador
    $authController = new AuthController();
    
    // Llamar al método correspondiente
    switch ($action) {
        case 'resetPassword':
            $token = $_GET['token'] ?? '';
            $authController->resetPassword($token);
            break;
            
        case 'processResetPassword':
            $authController->processResetPassword();
            break;
            
        case 'forgotPassword':
            $authController->forgotPassword();
            break;
            
        case 'processForgotPassword':
            $authController->processForgotPassword();
            break;
            
        default:
            SessionManager::setFlash('error', 'Método no encontrado');
            header('Location: ../../public/login.php');
            exit;
    }
    
} catch (Exception $e) {
    error_log("Error in AuthController router: " . $e->getMessage());
    
    SessionManager::setFlash('error', 'Ha ocurrido un error interno. Por favor, inténtalo de nuevo.');
    header('Location: ../../public/login.php');
    exit;
}
?>