<?php
/**
 * Debug específico para Dashboard - AgroConecta
 * Identifica el problema del bucle de redirección
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔧 Debug Dashboard - AgroConecta</h2>";

// Paso 1: Verificar sesión básica
session_start();
echo "<h3>📋 Paso 1: Verificación de Sesión Básica</h3>";
echo "<p>✅ Sesión iniciada: " . session_id() . "</p>";
echo "<p>📊 Datos en \$_SESSION:</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Paso 2: Cargar archivos uno por uno
echo "<h3>📁 Paso 2: Cargando Archivos</h3>";

try {
    echo "<p>🔄 Cargando config/database.php...</p>";
    require_once '../config/database.php';
    echo "<p>✅ config/database.php OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en database.php: " . $e->getMessage() . "</p>";
    exit;
}

try {
    echo "<p>🔄 Cargando core/Database.php...</p>";
    require_once '../core/Database.php';
    echo "<p>✅ core/Database.php OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en Database.php: " . $e->getMessage() . "</p>";
    exit;
}

try {
    echo "<p>🔄 Cargando core/SessionManager.php...</p>";
    require_once '../core/SessionManager.php';
    echo "<p>✅ core/SessionManager.php OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en SessionManager.php: " . $e->getMessage() . "</p>";
    exit;
}

// Paso 3: Probar SessionManager
echo "<h3>🔍 Paso 3: Verificación de SessionManager</h3>";

try {
    $isLoggedIn = SessionManager::isLoggedIn();
    echo "<p><strong>SessionManager::isLoggedIn():</strong> " . ($isLoggedIn ? '✅ true' : '❌ false') . "</p>";
    
    if ($isLoggedIn) {
        $userData = SessionManager::getUserData();
        echo "<p><strong>SessionManager::getUserData():</strong></p>";
        echo "<pre>";
        print_r($userData);
        echo "</pre>";
        
        if ($userData && isset($userData['tipo'])) {
            echo "<p><strong>Tipo de usuario detectado:</strong> " . $userData['tipo'] . "</p>";
        } else {
            echo "<p>❌ No se pudo obtener el tipo de usuario</p>";
        }
        
    } else {
        echo "<p>❌ Usuario NO logueado según SessionManager</p>";
        echo "<p>🔍 Verificando datos específicos de sesión:</p>";
        echo "<ul>";
        echo "<li>user_id: " . ($_SESSION['user_id'] ?? 'NO EXISTE') . "</li>";
        echo "<li>user_email: " . ($_SESSION['user_email'] ?? 'NO EXISTE') . "</li>";
        echo "<li>user_nombre: " . ($_SESSION['user_nombre'] ?? 'NO EXISTE') . "</li>";
        echo "<li>user_tipo: " . ($_SESSION['user_tipo'] ?? 'NO EXISTE') . "</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en SessionManager: " . $e->getMessage() . "</p>";
}

// Paso 4: Cargar modelos
echo "<h3>📦 Paso 4: Cargando Modelos</h3>";

try {
    echo "<p>🔄 Cargando models/Model.php...</p>";
    require_once '../app/models/Model.php';
    echo "<p>✅ Model.php OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en Model.php: " . $e->getMessage() . "</p>";
}

try {
    echo "<p>🔄 Cargando models/Usuario.php...</p>";
    require_once '../app/models/Usuario.php';
    echo "<p>✅ Usuario.php OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en Usuario.php: " . $e->getMessage() . "</p>";
}

try {
    echo "<p>🔄 Cargando models/Producto.php...</p>";
    require_once '../app/models/Producto.php';
    echo "<p>✅ Producto.php OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en Producto.php: " . $e->getMessage() . "</p>";
}

try {
    echo "<p>🔄 Cargando models/Pedido.php...</p>";
    require_once '../app/models/Pedido.php';
    echo "<p>✅ Pedido.php OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en Pedido.php: " . $e->getMessage() . "</p>";
}

// Paso 5: Cargar DashboardController
echo "<h3>🎛️ Paso 5: Cargando DashboardController</h3>";

try {
    echo "<p>🔄 Cargando controllers/DashboardController.php...</p>";
    require_once '../app/controllers/DashboardController.php';
    echo "<p>✅ DashboardController.php OK</p>";
    
    // Intentar crear instancia
    echo "<p>🔄 Creando instancia de DashboardController...</p>";
    $dashboardController = new DashboardController();
    echo "<p>✅ DashboardController instanciado OK</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error en DashboardController: " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}

// Paso 6: Simular la lógica del dashboard
echo "<h3>🔄 Paso 6: Simulando Lógica de Dashboard</h3>";

if (isset($userData) && $userData) {
    echo "<p>📊 Tipo de usuario: <strong>" . $userData['tipo'] . "</strong></p>";
    
    switch ($userData['tipo']) {
        case 'vendedor':
            echo "<p>🎯 Debería cargar: Dashboard de Vendedor</p>";
            echo "<p>📁 Vista: ../app/views/dashboard/vendedor.php</p>";
            
            // Verificar si existe la vista
            $vistaVendedor = '../app/views/dashboard/vendedor.php';
            if (file_exists($vistaVendedor)) {
                echo "<p>✅ Vista de vendedor existe</p>";
            } else {
                echo "<p>❌ Vista de vendedor NO existe: $vistaVendedor</p>";
            }
            break;
            
        case 'cliente':
            echo "<p>🎯 Debería cargar: Dashboard de Cliente</p>";
            echo "<p>📁 Vista: ../app/views/dashboard/cliente.php</p>";
            break;
            
        case 'admin':
            echo "<p>🎯 Debería cargar: Dashboard de Admin</p>";
            echo "<p>📁 Vista: ../app/views/dashboard/admin.php</p>";
            break;
            
        default:
            echo "<p>❌ Tipo de usuario no reconocido: " . $userData['tipo'] . "</p>";
    }
} else {
    echo "<p>❌ No hay datos de usuario disponibles</p>";
}

echo "<hr>";
echo "<h3>🔗 Acciones:</h3>";
echo "<p>";
echo "<a href='login-simple.php' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>🔙 Volver al Login Simple</a>";
echo "<a href='diagnostico-sesion.php?action=clear_session' style='background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>🧹 Limpiar Sesión</a>";

// Solo mostrar si no hay errores críticos
if (isset($dashboardController) && isset($userData)) {
    echo "<a href='dashboard.php' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>🚀 Intentar Dashboard Original</a>";
}
echo "</p>";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Dashboard - AgroConecta</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
            background-color: #f8f9fa;
        }
        h2, h3 { color: #2c3e50; }
        pre { 
            background: #f4f4f4; 
            padding: 15px; 
            border-radius: 8px;
            border-left: 4px solid #007bff;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            max-height: 300px;
            overflow-y: auto;
        }
        ul li { margin-bottom: 5px; }
    </style>
</head>
</html>