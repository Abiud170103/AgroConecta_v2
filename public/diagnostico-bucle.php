<?php
/**
 * Diagnóstico específico del bucle de redirecciones
 * AgroConecta - Detección paso a paso
 */

// Prevenir cualquier output antes de headers
ob_start();

require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';

// Capturar todos los headers enviados
$headers_sent = headers_sent($file, $line);

echo "<h1>🔍 Diagnóstico de Bucle de Redirecciones</h1>";
echo "<p><strong>Headers ya enviados:</strong> " . ($headers_sent ? "SÍ (en $file línea $line)" : "NO") . "</p>";

// 1. Verificar estado de sesión
echo "<h2>1. Estado de Sesión</h2>";
SessionManager::startSecureSession();

if (SessionManager::isLoggedIn()) {
    echo "✅ Usuario autenticado<br>";
    $userData = SessionManager::getUserData();
    echo "- ID: " . $userData['id'] . "<br>";
    echo "- Correo: " . $userData['correo'] . "<br>";
    echo "- Tipo: " . $userData['tipo'] . "<br>";
} else {
    echo "❌ Usuario NO autenticado<br>";
    echo "<p><a href='login-simple.php'>Ir a login</a></p>";
    exit;
}

// 2. Cargar modelos paso a paso
echo "<h2>2. Carga de Modelos</h2>";
try {
    require_once '../app/models/Model.php';
    echo "✅ Model.php<br>";
    
    require_once '../app/models/Usuario.php';
    echo "✅ Usuario.php<br>";
    
    require_once '../app/models/Producto.php';
    echo "✅ Producto.php<br>";
    
    require_once '../app/models/Pedido.php';
    echo "✅ Pedido.php<br>";
} catch (Exception $e) {
    echo "❌ Error cargando modelos: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Cargar controlador
echo "<h2>3. Carga de Controlador</h2>";
try {
    require_once '../app/controllers/DashboardController.php';
    echo "✅ DashboardController.php<br>";
    
    $controller = new DashboardController();
    echo "✅ Instancia de DashboardController creada<br>";
} catch (Exception $e) {
    echo "❌ Error cargando controlador: " . $e->getMessage() . "<br>";
    exit;
}

// 4. Probar método requireAuth específicamente
echo "<h2>4. Prueba del método requireAuth()</h2>";
try {
    // Usaremos reflexión para llamar el método directamente
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('requireAuth');
    $method->setAccessible(true);
    
    echo "📝 Llamando requireAuth()...<br>";
    $result = $method->invoke($controller);
    echo "✅ requireAuth() ejecutado sin redirección<br>";
    echo "- Resultado: " . $result . "<br>";
} catch (Exception $e) {
    echo "❌ Error en requireAuth(): " . $e->getMessage() . "<br>";
}

// 5. Probar método dashboard específico
echo "<h2>5. Prueba del método dashboard según tipo de usuario</h2>";
try {
    switch ($userData['tipo']) {
        case 'vendedor':
            echo "📝 Llamando dashboardVendedor()...<br>";
            $data = $controller->dashboardVendedor();
            break;
        case 'cliente':
            echo "📝 Llamando dashboardCliente()...<br>";
            $data = $controller->dashboardCliente();
            break;
        case 'admin':
            echo "📝 Llamando dashboardAdmin()...<br>";
            $data = $controller->dashboardAdmin();
            break;
        default:
            throw new Exception("Tipo de usuario no reconocido: " . $userData['tipo']);
    }
    
    echo "✅ Método dashboard ejecutado correctamente<br>";
    echo "- Datos obtenidos: " . count($data) . " elementos<br>";
    echo "- Keys: " . implode(', ', array_keys($data)) . "<br>";
} catch (Exception $e) {
    echo "❌ Error en método dashboard: " . $e->getMessage() . "<br>";
}

// 6. Verificar si se han enviado headers de redirección
echo "<h2>6. Verificación de Headers</h2>";
$headers = headers_list();
if (empty($headers)) {
    echo "✅ No se han enviado headers de redirección<br>";
} else {
    echo "⚠️ Headers enviados:<br>";
    foreach ($headers as $header) {
        echo "- " . $header . "<br>";
    }
}

// 7. Simular la lógica completa de dashboard.php
echo "<h2>7. Simulación Completa de dashboard.php</h2>";
echo "<div style='background: #e7f3ff; padding: 15px; border: 1px solid #b3d9ff; border-radius: 5px;'>";
echo "<strong>Simulando lógica completa...</strong><br>";

// Resetear captura de output para simular dashboard.php real
ob_end_clean();
ob_start();

// Simular exactamente lo que hace dashboard.php
if (!SessionManager::isLoggedIn()) {
    echo "ERROR: Usuario no autenticado en simulación<br>";
} else {
    $user = SessionManager::getUserData();
    $dashboardController = new DashboardController();
    
    switch ($user['tipo']) {
        case 'vendedor':
            $dashboardData = $dashboardController->dashboardVendedor();
            echo "ÉXITO: Dashboard vendedor cargado, datos: " . count($dashboardData) . " elementos<br>";
            break;
            
        case 'cliente':
            $dashboardData = $dashboardController->dashboardCliente();
            echo "ÉXITO: Dashboard cliente cargado, datos: " . count($dashboardData) . " elementos<br>";
            break;
            
        case 'admin':
            $dashboardData = $dashboardController->dashboardAdmin();
            echo "ÉXITO: Dashboard admin cargado, datos: " . count($dashboardData) . " elementos<br>";
            break;
            
        default:
            echo "ERROR: Tipo de usuario no reconocido: " . $user['tipo'] . "<br>";
    }
}

$simulationOutput = ob_get_contents();
ob_end_clean();

echo $simulationOutput;
echo "</div>";

echo "<h2>✅ CONCLUSIÓN</h2>";
echo "<p>Si llegaste hasta aquí sin redirecciones, el problema puede estar en:</p>";
echo "<ul>";
echo "<li>El archivo dashboard.php original está enviando headers antes de tiempo</li>";
echo "<li>Hay alguna configuración de servidor que causa redirecciones</li>";
echo "<li>El navegador está cacheando redirecciones anteriores</li>";
echo "</ul>";

echo "<h3>🔧 Pruebas Recomendadas:</h3>";
echo "<p><a href='dashboard.php' target='_blank' style='background:#dc3545;color:white;padding:10px;text-decoration:none;border-radius:5px;'>Probar Dashboard Original</a> ";
echo "<a href='login-simple.php' style='background:#007bff;color:white;padding:10px;text-decoration:none;border-radius:5px;margin-left:10px;'>Volver a Login</a></p>";

echo "<hr>";
echo "<p><small>Diagnóstico completado el " . date('Y-m-d H:i:s') . "</small></p>";
?>