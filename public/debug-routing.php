<?php
/**
 * Script de debugging para verificar el routing
 */

// Configuración inicial
date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Constantes
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta_v2/public');

echo "<h2>🔍 Debug de Routing - AgroConecta</h2>\n";

// 1. Verificar estructura de archivos
echo "<h3>📁 Estructura de archivos:</h3>\n";
echo "<ul>\n";
echo "<li>Router: " . (file_exists(ROOT_PATH . '/core/Router.php') ? '✅' : '❌') . "</li>\n";
echo "<li>AuthController: " . (file_exists(APP_PATH . '/controllers/AuthController.php') ? '✅' : '❌') . "</li>\n";
echo "<li>Routes config: " . (file_exists(CONFIG_PATH . '/agroconecta_routes.php') ? '✅' : '❌') . "</li>\n";
echo "<li>Register view: " . (file_exists(APP_PATH . '/views/auth/register.php') ? '✅' : '❌') . "</li>\n";
echo "</ul>\n";

// 2. Verificar URL actual
echo "<h3>🌐 Información de URL:</h3>\n";
echo "<ul>\n";
echo "<li><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'No definido') . "</li>\n";
echo "<li><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'No definido') . "</li>\n";
echo "<li><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'No definido') . "</li>\n";
echo "<li><strong>PATH_INFO:</strong> " . ($_SERVER['PATH_INFO'] ?? 'No definido') . "</li>\n";
echo "<li><strong>QUERY_STRING:</strong> " . ($_SERVER['QUERY_STRING'] ?? 'No definido') . "</li>\n";
echo "</ul>\n";

// 3. Probar carga del router
echo "<h3>🔧 Test de Router:</h3>\n";
try {
    require_once ROOT_PATH . '/core/Router.php';
    $router = new Router();
    echo "<p>✅ Router cargado correctamente</p>\n";
    
    // Cargar rutas
    require_once CONFIG_PATH . '/agroconecta_routes.php';
    echo "<p>✅ Rutas cargadas correctamente</p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ Error al cargar router: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// 4. Enlaces de prueba
echo "<h3>🔗 Enlaces de prueba:</h3>\n";
echo "<ul>\n";
echo "<li><a href='login'>Login</a></li>\n";
echo "<li><a href='registro'>Registro</a></li>\n";
echo "<li><a href=''>Home</a></li>\n";
echo "</ul>\n";

echo "<h3>📋 Instrucciones:</h3>\n";
echo "<p>Prueba los enlaces arriba para ver si funcionan correctamente.</p>\n";
echo "<p>Si ves este mensaje, significa que PHP está funcionando.</p>\n";
echo "<p>El problema puede estar en el .htaccess o la configuración del router.</p>\n";
?>