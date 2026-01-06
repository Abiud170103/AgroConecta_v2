<?php
/**
 * Versión mínima de productos para debug
 */

echo "<h1>PRODUCTOS.PHP - VERSIÓN DEBUG</h1>";
echo "<p>Si ves esto, el archivo funciona básicamente</p>";

try {
    require_once '../config/database.php';
    require_once '../core/Database.php';
    require_once '../core/SessionManager.php';
    
    SessionManager::startSecureSession();
    
    if (!SessionManager::isLoggedIn()) {
        echo "<p>❌ No logueado - redirigiendo...</p>";
        header('Location: login.php');
        exit;
    }
    
    $userData = SessionManager::getUserData();
    echo "<p>✅ Usuario: " . $userData['nombre'] . " (" . $userData['tipo'] . ")</p>";
    
    if ($userData['tipo'] !== 'vendedor' && $userData['tipo'] !== 'admin') {
        echo "<p>❌ Acceso denegado - redirigiendo...</p>";
        header('Location: dashboard.php');
        exit;
    }
    
    echo "<p>✅ Acceso permitido</p>";
    echo "<p>✅ productos.php debería funcionar</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='productos.php'>Probar productos.php original →</a></p>";
?>