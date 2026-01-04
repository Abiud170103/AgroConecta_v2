<?php
/**
 * Punto de acceso público para dashboards específicos por tipo de usuario
 * AgroConecta - Sistema de dashboards diferenciados
 */

require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';
require_once '../app/models/Model.php';
require_once '../app/models/Usuario.php';
require_once '../app/models/Producto.php';
require_once '../app/models/Pedido.php';
require_once '../app/controllers/DashboardController.php';

// Verificar que el usuario esté autenticado
if (!SessionManager::isLoggedIn()) {
    SessionManager::setFlash('error', 'Debes iniciar sesión para acceder al dashboard');
    header('Location: login.php');
    exit;
}

$user = SessionManager::getUserData();
$dashboardController = new DashboardController();

try {
    // Redireccionar al dashboard específico según el tipo de usuario
    switch ($user['tipo']) {
        case 'vendedor':
            $dashboardData = $dashboardController->dashboardVendedor();
            
            // Extraer variables para la vista
            extract($dashboardData);
            
            include '../app/views/dashboard/vendedor.php';
            break;
            
        case 'cliente':
            $dashboardData = $dashboardController->dashboardCliente();
            
            // Extraer variables para la vista
            extract($dashboardData);
            
            include '../app/views/dashboard/cliente.php';
            break;
            
        case 'admin':
            $dashboardData = $dashboardController->dashboardAdmin();
            
            // Extraer variables para la vista
            extract($dashboardData);
            
            include '../app/views/dashboard/admin.php';
            break;
            
        default:
            // Tipo de usuario no reconocido
            SessionManager::setFlash('error', 'Tipo de usuario no válido');
            header('Location: login.php');
            exit;
    }
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en dashboard: " . $e->getMessage());
    
    // Mostrar mensaje de error al usuario
    SessionManager::setFlash('error', 'Error al cargar el dashboard. Inténtalo de nuevo.');
    header('Location: index.php');
    exit;
}
?>