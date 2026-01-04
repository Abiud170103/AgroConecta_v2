<?php
/**
 * Dashboard Principal - AgroConecta (Versión Corregida)
 * Punto de acceso seguro para dashboards específicos por tipo de usuario
 */

require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';
require_once '../app/models/Model.php';
require_once '../app/models/Usuario.php';
require_once '../app/models/Producto.php';
require_once '../app/models/Pedido.php';
require_once '../app/controllers/DashboardController.php';

// Inicializar sesión
SessionManager::startSecureSession();

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
            
            // Verificar que tenemos los datos necesarios
            if (!$dashboardData || !is_array($dashboardData)) {
                throw new Exception("Error obteniendo datos del dashboard de vendedor");
            }
            
            // Extraer variables para la vista
            extract($dashboardData);
            
            // Incluir la vista del vendedor
            include '../app/views/dashboard/vendedor.php';
            break;
            
        case 'cliente':
            $dashboardData = $dashboardController->dashboardCliente();
            
            if (!$dashboardData || !is_array($dashboardData)) {
                throw new Exception("Error obteniendo datos del dashboard de cliente");
            }
            
            // Extraer variables para la vista
            extract($dashboardData);
            
            include '../app/views/dashboard/cliente.php';
            break;
            
        case 'admin':
            $dashboardData = $dashboardController->dashboardAdmin();
            
            if (!$dashboardData || !is_array($dashboardData)) {
                throw new Exception("Error obteniendo datos del dashboard de admin");
            }
            
            // Extraer variables para la vista
            extract($dashboardData);
            
            include '../app/views/dashboard/admin.php';
            break;
            
        default:
            // Tipo de usuario no reconocido
            SessionManager::setFlash('error', 'Tipo de usuario no válido: ' . $user['tipo']);
            header('Location: login.php');
            exit;
    }
    
} catch (Exception $e) {
    // Log del error para debugging
    error_log("Error en dashboard: " . $e->getMessage() . " - Usuario: " . $user['email'] . " - Tipo: " . $user['tipo']);
    
    // Mostrar página de error en lugar de redirección infinita
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error Dashboard - AgroConecta</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h4 class="mb-0">⚠️ Error en el Dashboard</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger">
                                <h5>Error Técnico</h5>
                                <p><strong>Mensaje:</strong> <?php echo htmlspecialchars($e->getMessage()); ?></p>
                                <p><strong>Usuario:</strong> <?php echo htmlspecialchars($user['nombre']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</p>
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($user['tipo']); ?></p>
                            </div>
                            
                            <h5>🔧 Opciones Disponibles:</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="dashboard-simple.php" class="btn btn-primary">📊 Dashboard Simplificado</a>
                                <a href="dashboard-corregido.php" class="btn btn-info">🔧 Dashboard de Debug</a>
                                <a href="logout.php" class="btn btn-secondary">🚪 Cerrar Sesión</a>
                                <a href="login-simple.php" class="btn btn-outline-secondary">🔙 Login Simple</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>