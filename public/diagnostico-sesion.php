<?php
/**
 * Diagnóstico de Sesión - AgroConecta
 * Identifica problemas de bucles de redirección
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔧 Diagnóstico de Sesión - AgroConecta</h2>";

// No cargar SessionManager aún, primero verificar sesión PHP básica
session_start();

echo "<h3>📋 Estado de la Sesión PHP:</h3>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";

echo "<h4>🗂️ Contenido de \$_SESSION:</h4>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Ahora cargar archivos uno por uno y ver si alguno falla
echo "<h3>📁 Cargando archivos del sistema:</h3>";

try {
    require_once '../config/database.php';
    echo "<p>✅ config/database.php cargado</p>";
} catch (Exception $e) {
    echo "<p>❌ Error cargando database.php: " . $e->getMessage() . "</p>";
}

try {
    require_once '../core/Database.php';
    echo "<p>✅ core/Database.php cargado</p>";
} catch (Exception $e) {
    echo "<p>❌ Error cargando Database.php: " . $e->getMessage() . "</p>";
}

try {
    require_once '../core/SessionManager.php';
    echo "<p>✅ core/SessionManager.php cargado</p>";
    
    // Probar SessionManager
    echo "<h4>🔍 Pruebas de SessionManager:</h4>";
    
    // Verificar si isLoggedIn funciona
    $isLoggedIn = SessionManager::isLoggedIn();
    echo "<p><strong>SessionManager::isLoggedIn():</strong> " . ($isLoggedIn ? 'true' : 'false') . "</p>";
    
    if ($isLoggedIn) {
        echo "<h4>👤 Datos del usuario en sesión:</h4>";
        $userData = SessionManager::getUserData();
        echo "<pre>";
        print_r($userData);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error cargando SessionManager.php: " . $e->getMessage() . "</p>";
}

// Verificar archivos que pueden causar redirecciones
echo "<h3>🔄 Análisis de posibles redirecciones:</h3>";

// Verificar si hay headers ya enviados
echo "<p><strong>Headers enviados:</strong> " . (headers_sent() ? 'Sí' : 'No') . "</p>";

// Información de la petición actual
echo "<h4>📡 Información de la petición:</h4>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'No definido') . "</p>";
echo "<p><strong>HTTP_REFERER:</strong> " . ($_SERVER['HTTP_REFERER'] ?? 'No definido') . "</p>";
echo "<p><strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'No definido') . "</p>";

// Limpiar sesión si está causando problemas
echo "<h3>🧹 Opciones de limpieza:</h3>";
echo "<p>";
echo "<a href='?action=clear_session' style='background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Limpiar Sesión</a>";
echo "<a href='login.php' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Ir a Login</a>";
echo "<a href='dashboard.php' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Ir a Dashboard</a>";
echo "</p>";

// Acción para limpiar sesión
if (isset($_GET['action']) && $_GET['action'] === 'clear_session') {
    echo "<h4>🧽 Limpiando sesión...</h4>";
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
    echo "<p>✅ Sesión limpiada. <a href='?'>Recargar página</a></p>";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Sesión - AgroConecta</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
            background-color: #f8f9fa;
        }
        h2, h3, h4 { color: #2c3e50; }
        pre { 
            background: #f4f4f4; 
            padding: 15px; 
            border-radius: 8px;
            border-left: 4px solid #007bff;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
    </style>
</head>
</html>