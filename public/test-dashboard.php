<?php
// Archivo de prueba para verificar el dashboard
require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';
require_once '../app/controllers/DashboardController.php';

// Verificar sesión
if (!SessionManager::isLoggedIn()) {
    echo "<h2>No estás logueado</h2>";
    echo "<p><a href='login.php'>Iniciar sesión</a></p>";
    exit;
}

$user = SessionManager::getUserData();
$controller = new DashboardController();

echo "<h2>Estado del Dashboard</h2>";
echo "<p><strong>Usuario logueado:</strong> " . $user['nombre'] . " (" . $user['email'] . ")</p>";
echo "<p><strong>Tipo de usuario:</strong> " . $user['tipo'] . "</p>";

// Mostrar el dashboard correspondiente
switch($user['tipo']) {
    case 'vendedor':
        echo "<h3>Cargando Dashboard de Vendedor...</h3>";
        $controller->dashboardVendedor();
        break;
    
    case 'cliente':
        echo "<h3>Cargando Dashboard de Cliente...</h3>";
        $controller->dashboardCliente();
        break;
        
    case 'admin':
        echo "<h3>Cargando Dashboard de Administrador...</h3>";
        $controller->dashboardAdmin();
        break;
        
    default:
        echo "<p>Tipo de usuario no válido: " . $user['tipo'] . "</p>";
}
?>