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

echo "<h1>🔧 Debug Micro - Método forgotPassword()</h1>";

try {
    // Incluir archivos necesarios
    require_once '../config/database.php';
    require_once '../app/core/Database.php';
    require_once '../app/models/Usuario.php';
    require_once '../app/core/Controller.php';
    require_once '../app/controllers/BaseController.php';
    require_once '../app/controllers/AuthController.php';
    
    echo "<p>✅ Archivos cargados</p>";
    
    $authController = new AuthController();
    echo "<p>✅ AuthController instanciado</p>";
    
    // Simular manualmente cada paso del método forgotPassword()
    echo "<h2>📋 Simulando método forgotPassword() paso a paso:</h2>";
    
    // Paso 1: Verificar isLoggedIn
    echo "<p><strong>Paso 1:</strong> Verificando isLoggedIn...</p>";
    $reflection = new ReflectionClass($authController);
    $isLoggedInProperty = $reflection->getProperty('isLoggedIn');
    $isLoggedInProperty->setAccessible(true);
    $isLoggedIn = $isLoggedInProperty->getValue($authController);
    
    if ($isLoggedIn) {
        echo "<p>⚠️ isLoggedIn = true, se intentaría redirect</p>";
        echo "<p>Probando redirectToDashboard...</p>";
        try {
            // No ejecutar realmente el redirect, solo verificar que existe el método
            $method = $reflection->getMethod('redirectToDashboard');
            echo "<p>✅ Método redirectToDashboard existe</p>";
        } catch (Exception $e) {
            echo "<p>❌ Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>✅ isLoggedIn = false, continúa con formulario</p>";
        
        // Paso 2: Probar generateCSRF
        echo "<p><strong>Paso 2:</strong> Probando generateCSRF...</p>";
        try {
            $generateMethod = $reflection->getMethod('generateCSRF');
            $generateMethod->setAccessible(true);
            $token = $generateMethod->invoke($authController);
            echo "<p>✅ generateCSRF funciona: " . substr($token, 0, 20) . "...</p>";
        } catch (Exception $e) {
            echo "<p>❌ Error en generateCSRF: " . $e->getMessage() . "</p>";
        }
        
        // Paso 3: Probar setViewData
        echo "<p><strong>Paso 3:</strong> Probando setViewData...</p>";
        try {
            $setViewDataMethod = $reflection->getMethod('setViewData');
            $setViewDataMethod->setAccessible(true);
            $setViewDataMethod->invoke($authController, 'csrf_token', 'test-123');
            $setViewDataMethod->invoke($authController, 'pageTitle', 'Test Title');
            echo "<p>✅ setViewData funciona</p>";
        } catch (Exception $e) {
            echo "<p>❌ Error en setViewData: " . $e->getMessage() . "</p>";
        }
        
        // Paso 4: Probar getFlashMessage
        echo "<p><strong>Paso 4:</strong> Probando getFlashMessage...</p>";
        try {
            $flashMethod = $reflection->getMethod('getFlashMessage');
            $flashMethod->setAccessible(true);
            $errorMsg = $flashMethod->invoke($authController, 'error');
            $successMsg = $flashMethod->invoke($authController, 'success');
            echo "<p>✅ getFlashMessage funciona (error: " . ($errorMsg ?: 'null') . ", success: " . ($successMsg ?: 'null') . ")</p>";
        } catch (Exception $e) {
            echo "<p>❌ Error en getFlashMessage: " . $e->getMessage() . "</p>";
        }
        
        // Paso 5: Probar render - EL PASO CRÍTICO
        echo "<p><strong>Paso 5:</strong> Probando render('auth/forgot-password') - PASO CRÍTICO</p>";
        
        try {
            echo "<p>5a. Verificando método render existe...</p>";
            $renderMethod = $reflection->getMethod('render');
            echo "<p>✅ Método render existe</p>";
            
            echo "<p>5b. Intentando ejecutar render...</p>";
            ob_start();
            
            // Usar timeout para evitar que se cuelgue
            set_time_limit(10);
            
            $renderMethod->invoke($authController, 'auth/forgot-password');
            
            $renderOutput = ob_get_contents();
            ob_end_clean();
            
            if (strlen($renderOutput) > 0) {
                echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0;'>";
                echo "<h3>🎉 ÉXITO! El render funciona</h3>";
                echo "<p><strong>Tamaño:</strong> " . strlen($renderOutput) . " caracteres</p>";
                echo "<p><strong>Inicio del HTML:</strong></p>";
                echo "<pre style='background: #f8f9fa; padding: 10px;'>" . htmlspecialchars(substr($renderOutput, 0, 300)) . "...</pre>";
                echo "</div>";
            } else {
                echo "<p>❌ Render ejecutado pero sin output</p>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0;'>";
            echo "<h3>❌ ERROR EN RENDER:</h3>";
            echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>Tipo:</strong> " . get_class($e) . "</p>";
            echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
            echo "</div>";
        } catch (Error $e) {
            echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0;'>";
            echo "<h3>❌ FATAL ERROR EN RENDER:</h3>";
            echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
            echo "</div>";
        }
        
        // Paso 6: Probar vista directa sin layout
        echo "<p><strong>Paso 6:</strong> Probando vista directa sin layout</p>";
        try {
            $viewMethod = $reflection->getMethod('view');
            $viewMethod->setAccessible(true);
            
            ob_start();
            $viewMethod->invoke($authController, 'auth/forgot-password', ['csrf_token' => 'test-direct']);
            $directOutput = ob_get_contents();
            ob_end_clean();
            
            if (strlen($directOutput) > 0) {
                echo "<p>✅ Vista directa funciona (" . strlen($directOutput) . " caracteres)</p>";
            } else {
                echo "<p>❌ Vista directa sin output</p>";
            }
            
        } catch (Exception $e) {
            echo "<p>❌ Error vista directa: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0;'>";
    echo "<h3>❌ Error crítico:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<h2>🔗 Enlaces:</h2>";
echo "<p><a href='/AgroConecta_v2/public/olvide-password'>Probar /olvide-password</a></p>";
echo "<p><a href='/AgroConecta_v2/public/login'>Ir a login</a></p>";
?>