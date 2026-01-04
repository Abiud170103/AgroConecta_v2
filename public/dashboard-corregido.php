<?php
/**
 * Dashboard Corregido - AgroConecta
 * Versión que evita el bucle de redirección
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';

// Inicializar sesión
SessionManager::startSecureSession();

echo "<h2>🔧 Dashboard Corregido - AgroConecta</h2>";

// Verificar que el usuario esté autenticado
if (!SessionManager::isLoggedIn()) {
    echo "<p>❌ No autenticado. <a href='login-simple.php'>Iniciar sesión</a></p>";
    exit;
}

$user = SessionManager::getUserData();

echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>✅ Usuario Autenticado</h4>";
echo "<p><strong>Nombre:</strong> " . htmlspecialchars($user['nombre']) . "</p>";
echo "<p><strong>Tipo:</strong> " . $user['tipo'] . "</p>";
echo "</div>";

// Intentar cargar el DashboardController paso a paso
echo "<h3>🔧 Cargando DashboardController...</h3>";

try {
    require_once '../app/models/Model.php';
    echo "<p>✅ Model.php cargado</p>";
    
    require_once '../app/models/Usuario.php';
    echo "<p>✅ Usuario.php cargado</p>";
    
    require_once '../app/models/Producto.php';
    echo "<p>✅ Producto.php cargado</p>";
    
    require_once '../app/models/Pedido.php';
    echo "<p>✅ Pedido.php cargado</p>";
    
    require_once '../app/controllers/DashboardController.php';
    echo "<p>✅ DashboardController.php cargado</p>";
    
    // Crear una versión modificada que no use requireAuth()
    class SafeDashboardController extends DashboardController {
        
        // Override del método problemático
        public function safeRequireAuth() {
            // Solo verificar SessionManager, no hacer redirecciones
            if (!SessionManager::isLoggedIn()) {
                throw new Exception("Usuario no autenticado");
            }
            
            $userData = SessionManager::getUserData();
            return $userData['id'];  // Retornar el ID del usuario
        }
        
        // Versión segura del dashboardVendedor
        public function safeDashboardVendedor() {
            try {
                $userId = $this->safeRequireAuth();
                
                echo "<h4>🔧 Ejecutando dashboardVendedor con usuario ID: $userId</h4>";
                
                // Datos básicos sin consultas complejas por ahora
                $dashboardData = [
                    'user' => SessionManager::getUserData(),
                    'statsProductos' => [
                        'total_productos' => 0,
                        'productos_disponibles' => 0,
                        'productos_agotados' => 0
                    ],
                    'statsPedidos' => [
                        'ingresos_totales' => 0
                    ],
                    'ventasPorDia' => [],
                    'pedidosPendientes' => [],
                    'productosRecientes' => [],
                    'pedidosRecientes' => []
                ];
                
                return $dashboardData;
                
            } catch (Exception $e) {
                throw new Exception("Error en safeDashboardVendedor: " . $e->getMessage());
            }
        }
    }
    
    echo "<p>✅ SafeDashboardController creado</p>";
    
    // Crear instancia y probar
    $dashboardController = new SafeDashboardController();
    echo "<p>✅ SafeDashboardController instanciado</p>";
    
    // Probar el método según el tipo de usuario
    switch ($user['tipo']) {
        case 'vendedor':
            echo "<h3>👨‍💼 Ejecutando Dashboard de Vendedor...</h3>";
            
            $dashboardData = $dashboardController->safeDashboardVendedor();
            echo "<p>✅ Datos del dashboard obtenidos</p>";
            
            echo "<h4>📊 Datos del Dashboard:</h4>";
            echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
            print_r($dashboardData);
            echo "</pre>";
            
            echo "<h4>✅ Dashboard Vendedor - Funcional</h4>";
            echo "<p>El dashboard se ejecutó sin errores. Ahora podemos cargar la vista completa.</p>";
            
            // Mostrar vista simplificada
            echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 12px; margin: 15px 0;'>";
            echo "<h4>📊 Dashboard de " . htmlspecialchars($dashboardData['user']['nombre']) . "</h4>";
            echo "<p><strong>Tipo:</strong> Vendedor</p>";
            echo "<p><strong>Total Productos:</strong> " . $dashboardData['statsProductos']['total_productos'] . "</p>";
            echo "<p><strong>Ingresos Totales:</strong> $" . $dashboardData['statsPedidos']['ingresos_totales'] . "</p>";
            echo "</div>";
            
            break;
            
        case 'cliente':
            echo "<h3>👤 Dashboard de Cliente (por implementar)</h3>";
            break;
            
        case 'admin':
            echo "<h3>🛡️ Dashboard de Admin (por implementar)</h3>";
            break;
            
        default:
            echo "<p>❌ Tipo de usuario no válido: " . $user['tipo'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>❌ Error en Dashboard</h4>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔗 Navegación</h3>";
echo "<p>";
echo "<a href='dashboard-simple.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🎯 Dashboard Simple</a>";
echo "<a href='logout.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🚪 Cerrar Sesión</a>";
echo "<a href='login-simple.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Login Simple</a>";
echo "</p>";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Corregido - AgroConecta</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
            background-color: #f8f9fa;
        }
        h2, h3, h4 { color: #2c3e50; }
        pre { 
            max-height: 300px;
            overflow-y: auto;
            font-size: 12px;
        }
    </style>
</head>
</html>