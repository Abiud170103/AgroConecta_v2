<?php
/**
 * Debug específico para el routing principal
 */

// Configuración inicial
date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Definir constantes
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta_v2/public');

echo "<h2>🔍 Debug del Router Principal</h2>\n";

// Autoloader
spl_autoload_register(function ($className) {
    $directories = [
        '../core/',
        '../app/core/',
        '../app/controllers/',
        '../app/models/',
        '../config/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Cargar configuración
require_once '../config/database.php';

echo "<h3>🌐 Información de Request:</h3>\n";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'No definido') . "</p>\n";
echo "<p><strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'No definido') . "</p>\n";
echo "<p><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'No definido') . "</p>\n";

try {
    echo "<h3>🔧 Inicializando Router...</h3>\n";
    
    // Crear instancia del router con base path
    $basePath = '/AgroConecta_v2/public';
    $router = new Router($basePath);
    echo "<p>✅ Router creado con basePath: {$basePath}</p>\n";
    
    // Cargar middleware
    require_once '../core/Middleware.php';
    echo "<p>✅ Middleware cargado</p>\n";
    
    // Cargar rutas
    require_once '../config/agroconecta_routes.php';
    echo "<p>✅ Rutas cargadas</p>\n";
    
    echo "<h3>📋 Rutas registradas:</h3>\n";
    echo "<ul>\n";
    $routes = $router->getRoutes();
    foreach ($routes as $route) {
        $handler = $route->getHandler();
        if ($handler instanceof Closure) {
            $handlerStr = '[Closure]';
        } else {
            $handlerStr = $handler;
        }
        echo "<li>" . $route->getMethod() . " " . $route->getPattern() . " -> " . $handlerStr . "</li>\n";
    }
    echo "</ul>\n";
    
    echo "<h3>🚀 Intentando dispatch...</h3>\n";
    
    // Simular la solicitud de registro
    $_SERVER['REQUEST_URI'] = '/AgroConecta_v2/public/registro';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    echo "<p>Simulando: GET /AgroConecta_v2/public/registro</p>\n";
    
    // Procesar la solicitud
    $router->dispatch();
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='color: #d32f2f;'>❌ Error en Router:</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<h4>Stack Trace:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<p>🏁 Fin del debug del router</p>\n";
?>