<?php
/**
 * Debug del sistema de rutas
 */

// Configuración inicial
session_start();
date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Debug del Router</h1>";

// Constantes y rutas
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', BASE_PATH . '/app');

echo "<h2>📋 Configuración del Sistema:</h2>";
echo "<p><strong>BASE_PATH:</strong> " . BASE_PATH . "</p>";
echo "<p><strong>APP_PATH:</strong> " . APP_PATH . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>REQUEST_METHOD:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";

try {
    // Incluir archivos necesarios
    echo "<h2>📦 Cargando archivos...</h2>";
    
    require_once '../app/core/Database.php';
    echo "✅ Database.php cargado<br>";
    
    require_once '../app/models/Usuario.php'; 
    echo "✅ Usuario.php cargado<br>";
    
    require_once '../app/core/Controller.php';
    echo "✅ Controller.php cargado<br>";
    
    require_once '../app/controllers/BaseController.php';
    echo "✅ BaseController.php cargado<br>";
    
    require_once '../app/controllers/AuthController.php';
    echo "✅ AuthController.php cargado<br>";
    
    require_once '../core/Router.php';
    echo "✅ Router.php cargado<br>";
    
    require_once '../config/database.php';
    echo "✅ database.php config cargado<br>";
    
    echo "<h2>🛣️ Inicializando Router...</h2>";
    
    // Crear router
    $basePath = '/AgroConecta_v2/public';
    $router = new Router($basePath);
    echo "✅ Router creado con basePath: $basePath<br>";
    
    // Cargar rutas
    require_once '../config/agroconecta_routes.php';
    echo "✅ Rutas cargadas<br>";
    
    echo "<h2>🎯 Testing rutas específicas:</h2>";
    
    // Simular solicitud GET a /olvide-password
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/AgroConecta_v2/public/olvide-password';
    
    echo "<p>Simulando: GET /olvide-password</p>";
    
    try {
        // Crear instancia del AuthController para probar
        echo "<h3>🧪 Testing AuthController:</h3>";
        $authController = new AuthController();
        echo "✅ AuthController instanciado correctamente<br>";
        
        // Probar método forgotPassword
        echo "<p>Probando AuthController->forgotPassword()...</p>";
        ob_start();
        $authController->forgotPassword();
        $output = ob_get_contents();
        ob_end_clean();
        
        if (strlen($output) > 0) {
            echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0;'>";
            echo "✅ <strong>forgotPassword() produjo output:</strong><br>";
            echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>";
            echo "</div>";
        } else {
            echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0;'>";
            echo "⚠️ <strong>forgotPassword() no produjo output visible</strong>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
        echo "❌ <strong>Error en AuthController:</strong> " . $e->getMessage();
        echo "<br><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine();
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
    echo "❌ <strong>Error crítico:</strong> " . $e->getMessage();
    echo "<br><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine();
    echo "<br><strong>Stack trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<h2>🔗 Enlaces de prueba:</h2>";
echo "<p><a href='/AgroConecta_v2/public/olvide-password'>Ir a /olvide-password</a></p>";
echo "<p><a href='/AgroConecta_v2/public/login'>Ir a /login</a></p>";
echo "<p><a href='/AgroConecta_v2/public/'>Ir a inicio</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { background: #f8f9fa; padding: 10px; border-radius: 4px; }
</style>