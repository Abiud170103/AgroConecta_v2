<?php
/**
 * Debug Exacto de dashboard.php
 * Reproduce exactamente la lógica de dashboard.php paso a paso
 */

// Habilitar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Debug Exacto de dashboard.php</h1>";
echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff;'>";
echo "<p><strong>Objetivo:</strong> Reproducir exactamente la lógica de dashboard.php para identificar el error</p>";
echo "</div>";

// PASO 1: Cargar archivos (igual que dashboard.php)
echo "<h2>PASO 1: Cargando archivos requeridos</h2>";
try {
    require_once '../config/database.php';
    echo "✅ database.php<br>";
    
    require_once '../core/Database.php';
    echo "✅ Database.php<br>";
    
    require_once '../core/SessionManager.php';
    echo "✅ SessionManager.php<br>";
    
    require_once '../app/models/Model.php';
    echo "✅ Model.php<br>";
    
    require_once '../app/models/Usuario.php';
    echo "✅ Usuario.php<br>";
    
    require_once '../app/models/Producto.php';
    echo "✅ Producto.php<br>";
    
    require_once '../app/models/Pedido.php';
    echo "✅ Pedido.php<br>";
    
    require_once '../app/controllers/DashboardController.php';
    echo "✅ DashboardController.php<br>";
    
} catch (Exception $e) {
    echo "❌ Error cargando archivos: " . $e->getMessage() . "<br>";
    die();
}

// PASO 2: Verificar autenticación (igual que dashboard.php)
echo "<h2>PASO 2: Verificando autenticación</h2>";
if (!SessionManager::isLoggedIn()) {
    echo "❌ Usuario no autenticado<br>";
    echo "<p><strong>REDIRECCIÓN A:</strong> login.php</p>";
    SessionManager::setFlash('error', 'Debes iniciar sesión para acceder al dashboard');
    // No ejecutar header para debug
    echo "<p style='color: red; font-weight: bold;'>AQUÍ se ejecutaría: header('Location: login.php')</p>";
    die("Simulación de exit() por no autenticación");
}
echo "✅ Usuario autenticado<br>";

// PASO 3: Obtener datos de usuario (igual que dashboard.php)
echo "<h2>PASO 3: Obteniendo datos de usuario</h2>";
$user = SessionManager::getUserData();
echo "✅ Datos obtenidos:<br>";
echo "- ID: " . ($user['id'] ?? 'N/A') . "<br>";
echo "- Correo: " . ($user['correo'] ?? 'N/A') . "<br>";
echo "- Tipo: " . ($user['tipo'] ?? 'N/A') . "<br>";

// PASO 4: Crear instancia del controlador (igual que dashboard.php)
echo "<h2>PASO 4: Creando DashboardController</h2>";
try {
    $dashboardController = new DashboardController();
    echo "✅ DashboardController instanciado<br>";
} catch (Exception $e) {
    echo "❌ Error creando controlador: " . $e->getMessage() . "<br>";
    die();
}

// PASO 5: Ejecutar switch según tipo de usuario (igual que dashboard.php)
echo "<h2>PASO 5: Ejecutando switch por tipo de usuario</h2>";
echo "<p><strong>Tipo detectado:</strong> " . $user['tipo'] . "</p>";

try {
    switch ($user['tipo']) {
        case 'vendedor':
            echo "<div style='background: #d1ecf1; padding: 10px; border: 1px solid #bee5eb;'>";
            echo "<strong>📋 Ejecutando caso 'vendedor'</strong><br>";
            
            echo "Paso 5.1: Llamando dashboardVendedor()...<br>";
            $dashboardData = $dashboardController->dashboardVendedor();
            echo "✅ dashboardVendedor() exitoso - " . count($dashboardData) . " elementos<br>";
            
            echo "Paso 5.2: Extrayendo variables con extract()...<br>";
            extract($dashboardData);
            echo "✅ extract() ejecutado<br>";
            
            echo "Paso 5.3: Variables disponibles después de extract():<br>";
            echo "- user: " . (isset($user) ? "✅" : "❌") . "<br>";
            echo "- statsVentas: " . (isset($statsVentas) ? "✅" : "❌") . "<br>";
            
            echo "Paso 5.4: SIMULANDO include de vista vendedor...<br>";
            echo "<p style='background: yellow; padding: 10px;'>";
            echo "<strong>⚠️ PUNTO CRÍTICO:</strong> Aquí se ejecutaría:<br>";
            echo "<code>include '../app/views/dashboard/vendedor.php';</code>";
            echo "</p>";
            
            // Simular las verificaciones que hace la vista vendedor
            echo "Paso 5.5: Verificaciones que hace vendedor.php:<br>";
            if (!SessionManager::isLoggedIn()) {
                echo "❌ SessionManager::isLoggedIn() = false<br>";
            } else {
                echo "✅ SessionManager::isLoggedIn() = true<br>";
            }
            
            if ($user['tipo'] !== 'vendedor') {
                echo "❌ \$user['tipo'] !== 'vendedor' (Valor: " . $user['tipo'] . ")<br>";
                echo "<p style='color: red; font-weight: bold;'>AQUÍ la vista ejecutaría: header('Location: ../../public/login.php')</p>";
            } else {
                echo "✅ \$user['tipo'] === 'vendedor'<br>";
            }
            
            echo "✅ Vista vendedor simulada correctamente<br>";
            echo "</div>";
            break;
            
        case 'cliente':
            echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb;'>";
            echo "<strong>📋 Ejecutando caso 'cliente'</strong><br>";
            
            $dashboardData = $dashboardController->dashboardCliente();
            extract($dashboardData);
            
            echo "✅ Caso cliente ejecutado - simulando vista cliente<br>";
            
            if ($user['tipo'] !== 'cliente') {
                echo "❌ Error: tipo no coincide<br>";
            } else {
                echo "✅ Verificaciones de cliente.php pasarían<br>";
            }
            echo "</div>";
            break;
            
        case 'admin':
            echo "<div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb;'>";
            echo "<strong>📋 Ejecutando caso 'admin'</strong><br>";
            
            $dashboardData = $dashboardController->dashboardAdmin();
            extract($dashboardData);
            
            echo "✅ Caso admin ejecutado - simulando vista admin<br>";
            
            if ($user['tipo'] !== 'admin') {
                echo "❌ Error: tipo no coincide<br>";
            } else {
                echo "✅ Verificaciones de admin.php pasarían<br>";
            }
            echo "</div>";
            break;
            
        default:
            echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7;'>";
            echo "❌ Tipo de usuario no reconocido: " . $user['tipo'] . "<br>";
            echo "<p style='color: orange; font-weight: bold;'>AQUÍ se ejecutaría: header('Location: login.php')</p>";
            echo "</div>";
    }
    
} catch (Exception $e) {
    echo "❌ Error en try/catch principal: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>✅ RESUMEN DEL DEBUG</h2>";
echo "<div style='background: #e7f3ff; padding: 15px; border: 1px solid #b3d9ff;'>";

if (isset($dashboardData)) {
    echo "🎉 <strong>ÉXITO:</strong> El flujo completo se ejecutó sin errores<br>";
    echo "📊 Datos del dashboard generados correctamente<br>";
    echo "🔄 Las verificaciones de la vista pasarían sin problema<br><br>";
    
    echo "<strong>Si este debug funciona pero dashboard.php falla, el problema puede ser:</strong><br>";
    echo "• Las vistas están cacheadas en el navegador<br>";
    echo "• Hay output/espacios antes de los headers en las vistas<br>";
    echo "• Conflicto con sesiones múltiples<br>";
    echo "• Problema específico del servidor web<br>";
} else {
    echo "❌ <strong>ERROR:</strong> No se pudieron generar los datos del dashboard<br>";
}

echo "</div>";

echo "<hr>";
echo "<h3>🧪 Pruebas Adicionales</h3>";
echo "<a href='dashboard.php' style='background:#dc3545;color:white;padding:10px;text-decoration:none;border-radius:5px;'>🔥 Probar Dashboard Original</a> ";
echo "<a href='login-simple.php' style='background:#007bff;color:white;padding:10px;text-decoration:none;border-radius:5px;margin-left:10px;'>🔐 Login Simple</a> ";
echo "<a href='dashboard-sin-controlador.php' style='background:#28a745;color:white;padding:10px;text-decoration:none;border-radius:5px;margin-left:10px;'>📊 Dashboard Sin Controlador</a>";

?>