<?php
session_start();
date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Constantes
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta_v2/public');

echo "<h1>🔧 Debug Paso a Paso</h1>";

try {
    // Incluir archivos necesarios
    require_once '../config/database.php';
    require_once '../app/core/Database.php';
    require_once '../app/models/Usuario.php';
    require_once '../app/core/Controller.php';
    require_once '../app/controllers/BaseController.php';
    require_once '../app/controllers/AuthController.php';
    
    echo "<h2>✅ Archivos cargados correctamente</h2>";
    
    // Verificar que existe la vista
    $viewPath = APP_PATH . '/views/auth/forgot-password.php';
    echo "<h2>📁 Verificando vista:</h2>";
    echo "<p><strong>Ruta esperada:</strong> " . htmlspecialchars($viewPath) . "</p>";
    
    if (file_exists($viewPath)) {
        echo "<p>✅ Archivo de vista existe</p>";
        echo "<p><strong>Tamaño:</strong> " . filesize($viewPath) . " bytes</p>";
        echo "<p><strong>Modificado:</strong> " . date('Y-m-d H:i:s', filemtime($viewPath)) . "</p>";
    } else {
        echo "<p>❌ Archivo de vista NO existe</p>";
    }
    
    // Verificar layout
    $layoutPath = APP_PATH . '/views/shared/layouts/main.php';
    echo "<h2>🎨 Verificando layout:</h2>";
    echo "<p><strong>Ruta esperada:</strong> " . htmlspecialchars($layoutPath) . "</p>";
    
    if (file_exists($layoutPath)) {
        echo "<p>✅ Layout existe</p>";
    } else {
        echo "<p>❌ Layout NO existe</p>";
        
        // Buscar otros layouts
        $layoutDir = APP_PATH . '/views/shared/layouts/';
        if (is_dir($layoutDir)) {
            $layouts = scandir($layoutDir);
            echo "<p><strong>Layouts disponibles:</strong></p><ul>";
            foreach ($layouts as $layout) {
                if ($layout != '.' && $layout != '..') {
                    echo "<li>" . htmlspecialchars($layout) . "</li>";
                }
            }
            echo "</ul>";
        }
    }
    
    // Crear AuthController y probar paso a paso
    echo "<h2>🧪 Testing AuthController paso a paso:</h2>";
    
    $authController = new AuthController();
    echo "<p>✅ AuthController instanciado</p>";
    
    // Verificar si isLoggedIn funciona
    $reflection = new ReflectionClass($authController);
    $isLoggedInProperty = $reflection->getProperty('isLoggedIn');
    $isLoggedInProperty->setAccessible(true);
    $isLoggedIn = $isLoggedInProperty->getValue($authController);
    
    echo "<p><strong>isLoggedIn:</strong> " . ($isLoggedIn ? 'true' : 'false') . "</p>";
    
    if ($isLoggedIn) {
        echo "<p>⚠️ Usuario está logueado, se intentará redirect</p>";
    } else {
        echo "<p>✅ Usuario no logueado, debe mostrar formulario</p>";
        
        // Probar método forgotPassword() completo
        echo "<h3>🧪 Ejecutando forgotPassword() completo:</h3>";
        
        try {
            echo "<p>Llamando AuthController->forgotPassword()...</p>";
            ob_start();
            $authController->forgotPassword();
            $output = ob_get_contents();
            ob_end_clean();
            
            if (strlen($output) > 0) {
                echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
                echo "<h4>✅ SUCCESS! El método produjo output</h4>";
                echo "<p><strong>Tamaño del output:</strong> " . strlen($output) . " caracteres</p>";
                echo "<p><strong>Primeros 200 caracteres:</strong></p>";
                echo "<pre style='background: #f8f9fa; padding: 10px;'>" . htmlspecialchars(substr($output, 0, 200)) . "...</pre>";
                
                echo "<h4>📋 Output completo:</h4>";
                echo "<div style='max-height: 400px; overflow: auto; background: white; padding: 10px; border: 1px solid #ccc;'>";
                echo $output;
                echo "</div>";
                echo "</div>";
                
                echo "<h3>🎉 SOLUCIÓN ENCONTRADA:</h3>";
                echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb;'>";
                echo "<p>El método <strong>forgotPassword()</strong> funciona perfectamente y genera el HTML correcto.</p>";
                echo "<p><strong>El problema está en el router o en el index.php</strong> - no está procesando la ruta correctamente.</p>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0;'>";
                echo "<h4>❌ El método no produjo output</h4>";
                echo "<p>Esto indica que hay un problema interno en el método.</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0;'>";
            echo "<h4>❌ Error ejecutando forgotPassword():</h4>";
            echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
            echo "<p><strong>Tipo:</strong> " . get_class($e) . "</p>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0;'>";
    echo "<h3>❌ Error crítico:</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>