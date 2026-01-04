<?php
/**
 * Captura de Headers - Dashboard Debug
 * Intercepta y muestra todos los headers que se están enviando
 */

// Buffer de salida para capturar headers
ob_start();

// Hook para capturar headers antes de que se envíen
$headers_to_send = [];
$redirect_detected = false;

// Función personalizada para capturar headers
function custom_header($string, $replace = true, $http_response_code = null) {
    global $headers_to_send, $redirect_detected;
    
    $headers_to_send[] = $string;
    
    if (stripos($string, 'Location:') === 0) {
        $redirect_detected = true;
    }
    
    // NO llamar header() real para poder mostrar el debug
    // header($string, $replace, $http_response_code);
}

// Reemplazar temporalmente la función header
// No podemos override header() directamente, pero podemos interceptar

echo "<h1>🕵️ Captura de Headers - Dashboard Debug</h1>";
echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
echo "<strong>⚠️ Advertencia:</strong> Este debug intercepta headers para mostrar qué redirecciones se están intentando.";
echo "</div>";

// Intentar cargar dashboard.php pero capturando errores
echo "<h2>📋 Cargando dashboard.php...</h2>";

try {
    // Simular la carga de dashboard.php paso a paso
    
    echo "Paso 1: Cargar archivos base...<br>";
    require_once '../config/database.php';
    require_once '../core/Database.php';
    require_once '../core/SessionManager.php';
    require_once '../app/models/Model.php';
    require_once '../app/models/Usuario.php';
    require_once '../app/models/Producto.php';
    require_once '../app/models/Pedido.php';
    require_once '../app/controllers/DashboardController.php';
    echo "✅ Archivos cargados<br>";
    
    echo "Paso 2: Verificar autenticación...<br>";
    if (!SessionManager::isLoggedIn()) {
        echo "❌ Usuario no autenticado - dashboard.php redirigiría a login.php<br>";
        echo "<div style='background: #f8d7da; padding: 10px; margin: 5px 0;'>";
        echo "<strong>REDIRECCIÓN DETECTADA:</strong> Location: login.php";
        echo "</div>";
        die();
    }
    echo "✅ Usuario autenticado<br>";
    
    echo "Paso 3: Obtener datos de usuario...<br>";
    $user = SessionManager::getUserData();
    echo "✅ Usuario: " . $user['correo'] . " (Tipo: " . $user['tipo'] . ")<br>";
    
    echo "Paso 4: Crear controlador...<br>";
    $dashboardController = new DashboardController();
    echo "✅ DashboardController creado<br>";
    
    echo "Paso 5: Ejecutar switch...<br>";
    
    switch ($user['tipo']) {
        case 'vendedor':
            echo "📊 Procesando dashboard vendedor...<br>";
            $dashboardData = $dashboardController->dashboardVendedor();
            echo "✅ Datos obtenidos: " . count($dashboardData) . " elementos<br>";
            
            extract($dashboardData);
            echo "✅ Variables extraídas<br>";
            
            echo "⚠️ <strong>PUNTO CRÍTICO:</strong> Simulando include de vendedor.php...<br>";
            
            // En lugar de incluir la vista, verificar las condiciones que la vista verificaría
            echo "<div style='background: #e7f3ff; padding: 10px; margin: 5px 0; border-left: 3px solid #007bff;'>";
            echo "<strong>Verificaciones de vendedor.php:</strong><br>";
            
            if (!SessionManager::isLoggedIn()) {
                echo "❌ SessionManager::isLoggedIn() falló<br>";
                echo "<strong style='color: red;'>REDIRECCIÓN DETECTADA:</strong> Location: ../../public/login.php<br>";
            } else {
                echo "✅ SessionManager::isLoggedIn() = true<br>";
            }
            
            if ($user['tipo'] !== 'vendedor') {
                echo "❌ \$user['tipo'] !== 'vendedor' (Actual: '" . $user['tipo'] . "')<br>";
                echo "<strong style='color: red;'>REDIRECCIÓN DETECTADA:</strong> Location: ../../public/login.php<br>";
            } else {
                echo "✅ \$user['tipo'] === 'vendedor'<br>";
            }
            
            echo "</div>";
            
            echo "🎉 <strong>RESULTADO:</strong> Vista vendedor debería cargar sin problemas<br>";
            break;
            
        case 'cliente':
            echo "📊 Procesando dashboard cliente...<br>";
            $dashboardData = $dashboardController->dashboardCliente();
            extract($dashboardData);
            echo "🎉 Vista cliente debería cargar correctamente<br>";
            break;
            
        case 'admin':
            echo "📊 Procesando dashboard admin...<br>";
            $dashboardData = $dashboardController->dashboardAdmin();
            extract($dashboardData);
            echo "🎉 Vista admin debería cargar correctamente<br>";
            break;
            
        default:
            echo "❌ Tipo de usuario no válido: " . $user['tipo'] . "<br>";
            echo "<strong style='color: red;'>REDIRECCIÓN DETECTADA:</strong> Location: login.php<br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>EXCEPCIÓN CAPTURADA:</strong><br>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "<details><summary>Stack Trace</summary><pre>" . $e->getTraceAsString() . "</pre></details>";
}

echo "<h2>🔍 ANÁLISIS FINAL</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb;'>";

if (!isset($dashboardData)) {
    echo "<strong style='color: red;'>❌ PROBLEMA DETECTADO:</strong><br>";
    echo "No se pudieron generar los datos del dashboard. Esto indica que el error ocurre antes de llegar a las vistas.<br>";
} else {
    echo "<strong style='color: green;'>✅ FLUJO CORRECTO:</strong><br>";
    echo "Los datos del dashboard se generan correctamente. Si dashboard.php sigue fallando, puede ser:<br>";
    echo "• <strong>Cache del navegador:</strong> Limpiar caché y cookies<br>";
    echo "• <strong>Headers ya enviados:</strong> Espacios/caracteres antes de &lt;?php en las vistas<br>";
    echo "• <strong>Sesiones múltiples:</strong> Conflicto entre diferentes pestañas<br>";
    echo "• <strong>Configuración del servidor:</strong> mod_rewrite u otras configuraciones<br>";
}

echo "</div>";

echo "<hr>";
echo "<h3>🧪 Pruebas Recomendadas</h3>";
echo "<ol>";
echo "<li><strong>Limpiar caché del navegador</strong> y volver a intentar dashboard.php</li>";
echo "<li><strong>Abrir dashboard.php en una pestaña privada/incógnito</strong></li>";
echo "<li><strong>Verificar que no hay espacios antes de &lt;?php</strong> en las vistas</li>";
echo "<li><strong>Probar con diferentes usuarios</strong> (vendedor, cliente, admin)</li>";
echo "</ol>";

echo "<p>";
echo "<a href='dashboard.php' style='background:#dc3545;color:white;padding:10px;text-decoration:none;border-radius:5px;' target='_blank'>🔥 Dashboard Original (Nueva pestaña)</a> ";
echo "<a href='dashboard-sin-controlador.php' style='background:#28a745;color:white;padding:10px;text-decoration:none;border-radius:5px;margin-left:10px;'>✅ Dashboard Sin Controlador</a>";
echo "</p>";

// Obtener el contenido capturado
$output = ob_get_clean();
echo $output;
?>