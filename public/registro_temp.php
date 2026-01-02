<?php
/**
 * Archivo temporal de registro directo hasta solucionar routing
 */

// Configuración inicial
date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>🚀 Inicio del script de registro temporal</h2>\n";

session_start();
echo "<p>✅ Sesión iniciada</p>\n";

// Definir constantes
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta_v2/public');

echo "<p>✅ Constantes definidas</p>\n";
echo "<p>BASE_PATH: " . BASE_PATH . "</p>\n";
echo "<p>APP_PATH: " . APP_PATH . "</p>\n";

// Incluir configuración
echo "<p>🔧 Cargando configuración...</p>\n";
require_once '../config/database.php';
echo "<p>✅ Database config cargada</p>\n";

// Incluir clases necesarias
echo "<p>🔧 Cargando clases...</p>\n";
require_once '../app/core/Database.php';
echo "<p>✅ Database class cargada</p>\n";
require_once '../app/models/Model.php';
echo "<p>✅ Model class cargada</p>\n";
require_once '../app/models/Usuario.php';
echo "<p>✅ Usuario model cargada</p>\n";

// Verificar y cargar Controller
echo "<p>🔍 Verificando Controller.php...</p>\n";
$controllerPath = '../app/core/Controller.php';
if (file_exists($controllerPath)) {
    echo "<p>✅ Controller.php encontrado en: " . $controllerPath . "</p>\n";
    require_once $controllerPath;
    echo "<p>✅ Controller base cargada</p>\n";
} else {
    echo "<p>❌ Controller.php NO encontrado en: " . $controllerPath . "</p>\n";
}

require_once '../app/controllers/BaseController.php';
echo "<p>✅ BaseController cargada</p>\n";
require_once '../app/controllers/AuthController.php';
echo "<p>✅ AuthController cargada</p>\n";

try {
    echo "<p>🔧 Creando controlador...</p>\n";
    // Crear controlador
    $authController = new AuthController();
    echo "<p>✅ AuthController creado correctamente</p>\n";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<p>📝 Procesando registro POST...</p>\n";
        // Procesar registro
        $authController->processRegister();
    } else {
        echo "<p>📄 Mostrando formulario de registro...</p>\n";
        
        // En lugar de usar métodos protected, cargar la vista directamente
        echo "<hr><h3>🎯 Vista de Registro:</h3>\n";
        
        // Cargar la vista directamente
        $viewPath = APP_PATH . '/views/auth/register.php';
        if (file_exists($viewPath)) {
            echo "<p>✅ Vista encontrada, cargando...</p>\n";
            
            // Definir las variables que la vista necesita
            $csrf_token = 'test_token_12345';
            $pageTitle = 'Crear Cuenta - Temporal';
            $error = null;
            $success = null;
            
            // Incluir la vista
            include $viewPath;
            
        } else {
            echo "<p>❌ Vista no encontrada en: " . $viewPath . "</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='color: #d32f2f;'>❌ Error:</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<h4>Stack Trace:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
} catch (Error $e) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='color: #d32f2f;'>💥 Error Fatal:</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<h4>Stack Trace:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<p>🏁 Fin del script</p>\n";
?>