<?php
/**
 * Diagnóstico específico del bucle login/dashboard
 * No usa SessionManager para evitar conflictos
 */

// Iniciar sesión PHP básica
session_start();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Debug Bucle Login/Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #28a745; }
        .error { background: #f8d7da; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #dc3545; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #17a2b8; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 8px; overflow-x: auto; }
        .btn { padding: 10px 20px; margin: 10px 5px; text-decoration: none; border-radius: 5px; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <h1>🔧 Diagnóstico del Bucle Login/Dashboard</h1>
    
    <?php
    // 1. Estado básico de sesión
    echo "<div class='info'>";
    echo "<h3>📋 Estado Básico de la Sesión PHP</h3>";
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
    echo "<p><strong>Session Status:</strong> " . session_status() . " (1=disabled, 2=active, 3=none)</p>";
    echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "</div>";
    
    // 2. Contenido de $_SESSION
    echo "<div class='info'>";
    echo "<h3>🗂️ Contenido de \$_SESSION</h3>";
    if (empty($_SESSION)) {
        echo "<div class='error'>❌ \$_SESSION está completamente vacío</div>";
    } else {
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    }
    echo "</div>";
    
    // 3. Verificar llaves específicas para login
    echo "<div class='info'>";
    echo "<h3>🔍 Verificación de Datos de Login</h3>";
    $loginKeys = ['user_id', 'user_email', 'user_nombre', 'user_tipo'];
    
    foreach ($loginKeys as $key) {
        if (isset($_SESSION[$key])) {
            echo "<div class='success'>✅ $key: " . htmlspecialchars($_SESSION[$key]) . "</div>";
        } else {
            echo "<div class='error'>❌ $key: No existe</div>";
        }
    }
    echo "</div>";
    
    // 4. Intentar cargar SessionManager
    echo "<div class='info'>";
    echo "<h3>🔧 Cargando SessionManager</h3>";
    
    $sessionManagerWorking = false;
    $isLoggedInResult = false;
    $userDataResult = null;
    
    try {
        require_once '../core/SessionManager.php';
        echo "<div class='success'>✅ SessionManager cargado correctamente</div>";
        $sessionManagerWorking = true;
        
        // Verificar isLoggedIn
        $isLoggedInResult = SessionManager::isLoggedIn();
        echo "<div class='" . ($isLoggedInResult ? 'success' : 'error') . "'>";
        echo ($isLoggedInResult ? '✅' : '❌') . " SessionManager::isLoggedIn() = " . ($isLoggedInResult ? 'TRUE' : 'FALSE');
        echo "</div>";
        
        // Si está loggeado, obtener datos
        if ($isLoggedInResult) {
            $userDataResult = SessionManager::getUserData();
            if ($userDataResult) {
                echo "<div class='success'>✅ SessionManager::getUserData() exitoso:</div>";
                echo "<pre>" . print_r($userDataResult, true) . "</pre>";
            } else {
                echo "<div class='error'>❌ SessionManager::getUserData() devolvió null</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error cargando SessionManager: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    // 5. Simular exactamente el flujo de dashboard.php
    echo "<div class='info'>";
    echo "<h3>🎯 Simulación del Flujo de dashboard.php</h3>";
    
    if ($sessionManagerWorking) {
        echo "<h4>Paso 1: Verificar autenticación</h4>";
        if (!$isLoggedInResult) {
            echo "<div class='error'>❌ PROBLEMA ENCONTRADO: SessionManager::isLoggedIn() = FALSE</div>";
            echo "<div class='error'>🔄 dashboard.php ejecutaría: header('Location: login.php')</div>";
            echo "<div class='error'>⚠️ ESTA ES LA CAUSA DEL BUCLE</div>";
        } else {
            echo "<div class='success'>✅ Usuario autenticado correctamente</div>";
            
            echo "<h4>Paso 2: Obtener datos de usuario</h4>";
            if (!$userDataResult) {
                echo "<div class='error'>❌ PROBLEMA: getUserData() devolvió null</div>";
            } else {
                echo "<div class='success'>✅ Datos de usuario obtenidos</div>";
                
                echo "<h4>Paso 3: Verificar tipo de usuario</h4>";
                $userType = $userDataResult['tipo'] ?? null;
                if (!$userType || !in_array($userType, ['vendedor', 'cliente', 'admin'])) {
                    echo "<div class='error'>❌ PROBLEMA: Tipo de usuario inválido: " . htmlspecialchars($userType ?? 'null') . "</div>";
                } else {
                    echo "<div class='success'>✅ Tipo de usuario válido: " . htmlspecialchars($userType) . "</div>";
                    echo "<div class='success'>🎉 dashboard.php DEBERÍA FUNCIONAR CORRECTAMENTE</div>";
                }
            }
        }
    }
    echo "</div>";
    
    // 6. Información adicional
    echo "<div class='info'>";
    echo "<h3>🍪 Información de Cookies y Configuración</h3>";
    echo "<p><strong>Cookie de sesión existe:</strong> " . (isset($_COOKIE[session_name()]) ? 'SÍ' : 'NO') . "</p>";
    echo "<p><strong>session.use_cookies:</strong> " . ini_get('session.use_cookies') . "</p>";
    echo "<p><strong>session.gc_maxlifetime:</strong> " . ini_get('session.gc_maxlifetime') . " segundos</p>";
    echo "</div>";
    
    // 7. Acciones recomendadas
    echo "<div class='info'>";
    echo "<h3>🚀 Acciones Recomendadas</h3>";
    
    if (!$isLoggedInResult && !empty($_SESSION)) {
        echo "<div class='error'>";
        echo "<h4>🐛 PROBLEMA DETECTADO:</h4>";
        echo "<p>Hay datos en la sesión pero SessionManager::isLoggedIn() devuelve FALSE.</p>";
        echo "<p>Esto indica que:</p>";
        echo "<ul>";
        echo "<li>Las llaves de sesión no coinciden con lo que esperaba SessionManager</li>";
        echo "<li>O hay un problema en la lógica de verificación de SessionManager</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    if (empty($_SESSION)) {
        echo "<div class='error'>";
        echo "<h4>🔄 SESIÓN VACÍA:</h4>";
        echo "<p>No hay datos de sesión. Necesitas hacer login primero.</p>";
        echo "</div>";
    }
    echo "</div>";
    ?>
    
    <div class="info">
        <h3>🔗 Enlaces de Navegación</h3>
        <a href="login-debug-js.php" class="btn btn-primary">🐛 Login con Debug</a>
        <a href="dashboard.php" class="btn btn-success">📊 Ir a Dashboard</a>
        <a href="limpiar-sesion.php" class="btn btn-danger">🧹 Limpiar Sesión</a>
        <br><br>
        <a href="javascript:location.reload()" class="btn btn-primary">🔄 Recargar Diagnóstico</a>
    </div>
</body>
</html>