<?php
/**
 * Dashboard Debug - Identificar línea exacta del bucle
 * AgroConecta - Debug paso a paso
 */

// Paso 1: Cargar archivos base
echo "PASO 1: Cargando archivos base...<br>";
require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';
echo "✅ Archivos base cargados<br>";

// Paso 2: Verificar autenticación
echo "<br>PASO 2: Verificando autenticación...<br>";
if (!SessionManager::isLoggedIn()) {
    echo "❌ Usuario no autenticado, redirigiendo...<br>";
    SessionManager::setFlash('error', 'Debes iniciar sesión para acceder al dashboard');
    header('Location: login.php');
    exit;
}
echo "✅ Usuario autenticado<br>";

// Paso 3: Obtener datos de usuario
echo "<br>PASO 3: Obteniendo datos de usuario...<br>";
$user = SessionManager::getUserData();
echo "✅ Datos de usuario: " . $user['correo'] . " (Tipo: " . $user['tipo'] . ")<br>";

// Paso 4: Cargar modelos
echo "<br>PASO 4: Cargando modelos...<br>";
require_once '../app/models/Model.php';
echo "✅ Model.php<br>";
require_once '../app/models/Usuario.php';
echo "✅ Usuario.php<br>";
require_once '../app/models/Producto.php';
echo "✅ Producto.php<br>";
require_once '../app/models/Pedido.php';
echo "✅ Pedido.php<br>";

// Paso 5: Cargar controlador
echo "<br>PASO 5: Cargando DashboardController...<br>";
require_once '../app/controllers/DashboardController.php';
echo "✅ DashboardController.php cargado<br>";

// Paso 6: Crear instancia
echo "<br>PASO 6: Creando instancia de controlador...<br>";
$dashboardController = new DashboardController();
echo "✅ Instancia creada<br>";

// Paso 7: Probar método según tipo de usuario
echo "<br>PASO 7: Ejecutando método dashboard...<br>";
echo "Tipo de usuario: " . $user['tipo'] . "<br>";

try {
    switch ($user['tipo']) {
        case 'vendedor':
            echo "Ejecutando dashboardVendedor()...<br>";
            $dashboardData = $dashboardController->dashboardVendedor();
            echo "✅ dashboardVendedor() ejecutado correctamente<br>";
            break;
            
        case 'cliente':
            echo "Ejecutando dashboardCliente()...<br>";
            $dashboardData = $dashboardController->dashboardCliente();
            echo "✅ dashboardCliente() ejecutado correctamente<br>";
            break;
            
        case 'admin':
            echo "Ejecutando dashboardAdmin()...<br>";
            $dashboardData = $dashboardController->dashboardAdmin();
            echo "✅ dashboardAdmin() ejecutado correctamente<br>";
            break;
            
        default:
            echo "❌ Tipo de usuario no reconocido<br>";
            SessionManager::setFlash('error', 'Tipo de usuario no válido');
            header('Location: login.php');
            exit;
    }
    
    echo "✅ Datos obtenidos: " . count($dashboardData) . " elementos<br>";
    
} catch (Exception $e) {
    echo "❌ Error en método dashboard: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString() . "<br>";
}

// Paso 8: Simular inclusión de vista
echo "<br>PASO 8: Simulando carga de vista...<br>";
try {
    // Extraer variables para la vista (como hace el dashboard original)
    extract($dashboardData);
    echo "✅ Variables extraídas correctamente<br>";
    
    // En lugar de incluir la vista real, mostrar mensaje
    echo "✅ Vista simulada cargada (no se incluye archivo real)<br>";
    
} catch (Exception $e) {
    echo "❌ Error simulando vista: " . $e->getMessage() . "<br>";
}

echo "<br><strong>🎉 ÉXITO: El flujo completo se ejecutó sin redirecciones!</strong><br>";
echo "<p>Si llegaste hasta aquí, significa que el DashboardController funciona correctamente.</p>";
echo "<p>El problema podría estar en:</p>";
echo "<ul>";
echo "<li>Las vistas incluidas (vendedor.php, cliente.php, admin.php)</li>";
echo "<li>Headers enviados antes de tiempo</li>";
echo "<li>Configuración del servidor</li>";
echo "<li>Cache del navegador</li>";
echo "</ul>";

echo "<hr>";
echo "<a href='dashboard.php' style='background:#dc3545;color:white;padding:10px;text-decoration:none;border-radius:5px;'>Probar Dashboard Original</a> ";
echo "<a href='dashboard-sin-controlador.php' style='background:#28a745;color:white;padding:10px;text-decoration:none;border-radius:5px;margin-left:10px;'>Dashboard Sin Controlador</a>";
?>