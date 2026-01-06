<?php
echo "TEST BÁSICO - productos.php";
echo "<br>Fecha: " . date('Y-m-d H:i:s');

try {
    echo "<br>1. ✅ PHP funciona";
    
    // Test de inclusiones paso a paso
    echo "<br>2. Probando includes...";
    
    if (file_exists('../config/database.php')) {
        echo "<br>✅ database.php existe";
        require_once '../config/database.php';
        echo "<br>✅ database.php incluido";
    } else {
        echo "<br>❌ database.php NO EXISTE";
        exit;
    }
    
    if (file_exists('../core/Database.php')) {
        echo "<br>✅ Database.php existe";
        require_once '../core/Database.php';
        echo "<br>✅ Database.php incluido";
    } else {
        echo "<br>❌ Database.php NO EXISTE";
        exit;
    }
    
    if (file_exists('../core/SessionManager.php')) {
        echo "<br>✅ SessionManager.php existe";
        require_once '../core/SessionManager.php';
        echo "<br>✅ SessionManager.php incluido";
    } else {
        echo "<br>❌ SessionManager.php NO EXISTE";
        exit;
    }
    
    echo "<br>3. Iniciando sesión...";
    SessionManager::startSecureSession();
    echo "<br>✅ Sesión iniciada";
    
    echo "<br>4. Verificando autenticación...";
    if (SessionManager::isLoggedIn()) {
        $userData = SessionManager::getUserData();
        echo "<br>✅ Usuario logueado: " . ($userData['nombre'] ?? 'Sin nombre');
        echo "<br>✅ Tipo: " . ($userData['tipo'] ?? 'Sin tipo');
        
        if ($userData['tipo'] === 'admin' || $userData['tipo'] === 'vendedor') {
            echo "<br>✅ Acceso permitido a productos.php";
        } else {
            echo "<br>❌ Acceso denegado - tipo: " . $userData['tipo'];
        }
    } else {
        echo "<br>❌ Usuario NO logueado";
    }
    
    echo "<br>5. Test de base de datos...";
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "<br>✅ Conexión BD exitosa";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM producto");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    echo "<br>✅ Total productos en BD: " . $total;
    
    echo "<br><br>🎉 TODOS LOS TESTS PASARON";
    echo "<br><strong>El problema NO está en las dependencias</strong>";
    echo "<br><a href='productos.php' style='color: blue;'>Probar productos.php →</a>";
    
} catch (Exception $e) {
    echo "<br><br>❌ ERROR ENCONTRADO:";
    echo "<br>Mensaje: " . $e->getMessage();
    echo "<br>Archivo: " . $e->getFile();
    echo "<br>Línea: " . $e->getLine();
    echo "<br>Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>