<?php
/**
 * Dashboard Limpio - AgroConecta v2
 * Versión limpia sin problemas de redirección
 */

require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';
require_once '../app/models/Model.php';
require_once '../app/models/Usuario.php';
require_once '../app/models/Producto.php';
require_once '../app/models/Pedido.php';
require_once '../app/controllers/DashboardController.php';

// Verificar autenticación
if (!SessionManager::isLoggedIn()) {
    SessionManager::setFlash('error', 'Debes iniciar sesión para acceder al dashboard');
    header('Location: login.php');
    exit;
}

$user = SessionManager::getUserData();
$dashboardController = new DashboardController();

try {
    // Procesar según tipo de usuario
    switch ($user['tipo']) {
        case 'vendedor':
            $dashboardData = $dashboardController->dashboardVendedor();
            extract($dashboardData);
            include '../app/views/dashboard/vendedor.php';
            break;
            
        case 'cliente':
            $dashboardData = $dashboardController->dashboardCliente();
            extract($dashboardData);
            include '../app/views/dashboard/cliente.php';
            break;
            
        case 'admin':
            $dashboardData = $dashboardController->dashboardAdmin();
            extract($dashboardData);
            include '../app/views/dashboard/admin.php';
            break;
            
        default:
            SessionManager::setFlash('error', 'Tipo de usuario no válido');
            header('Location: login.php');
            exit;
    }
    
} catch (Exception $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    SessionManager::setFlash('error', 'Error al cargar el dashboard. Inténtalo de nuevo.');
    header('Location: index.php');
    exit;
}
?>