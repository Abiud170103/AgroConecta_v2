<?php
/**
 * Debug específico del dashboard.php problemático
 * Vamos a capturar exactamente qué está pasando
 */

// Capturar todos los headers enviados
ob_start();

// Registrar función para capturar headers
$headers_sent = false;
$output_started = false;

function debug_headers() {
    global $headers_sent, $output_started;
    
    if (!headers_sent()) {
        $headers_sent = false;
        echo "<div style='background:#d4edda;padding:15px;margin:10px 0;border-radius:5px;'>";
        echo "✅ <strong>Headers NO enviados aún</strong> - Esto está bien";
        echo "</div>";
    } else {
        $headers_sent = true;
        echo "<div style='background:#f8d7da;padding:15px;margin:10px 0;border-radius:5px;'>";
        echo "❌ <strong>Headers YA enviados</strong> - Esto puede causar problemas";
        echo "</div>";
    }
    
    $headers_list = headers_list();
    if (!empty($headers_list)) {
        echo "<div style='background:#fff3cd;padding:15px;margin:10px 0;border-radius:5px;'>";
        echo "<strong>📋 Headers actuales:</strong><br>";
        foreach ($headers_list as $header) {
            echo "- " . htmlspecialchars($header) . "<br>";
        }
        echo "</div>";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Debug Dashboard.php</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .step { margin: 20px 0; padding: 15px; border: 1px solid #dee2e6; border-radius: 8px; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debugging Dashboard.php - Paso a Paso</h1>
        
        <div class="step info">
            <h3>📋 Paso 1: Estado inicial</h3>
            <?php debug_headers(); ?>
        </div>

        <div class="step info">
            <h3>🔧 Paso 2: Cargando SessionManager</h3>
            <?php
            try {
                require_once '../core/SessionManager.php';
                SessionManager::startSecureSession();
                echo "<div class='success'>✅ SessionManager cargado exitosamente</div>";
                debug_headers();
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error cargando SessionManager: " . $e->getMessage() . "</div>";
            }
            ?>
        </div>

        <div class="step info">
            <h3>🔐 Paso 3: Verificando autenticación</h3>
            <?php
            if (!SessionManager::isLoggedIn()) {
                echo "<div class='error'>❌ Usuario NO autenticado - ESTE ES EL PROBLEMA</div>";
                echo "<div class='warning'>⚠️ dashboard.php ejecutaría: header('Location: login.php')</div>";
            } else {
                echo "<div class='success'>✅ Usuario autenticado correctamente</div>";
                
                $user = SessionManager::getUserData();
                if ($user) {
                    echo "<div class='success'>✅ Datos de usuario obtenidos</div>";
                    echo "<pre>" . print_r($user, true) . "</pre>";
                } else {
                    echo "<div class='error'>❌ getUserData() devolvió null</div>";
                }
            }
            debug_headers();
            ?>
        </div>

        <div class="step info">
            <h3>📁 Paso 4: Cargando dependencias de dashboard.php</h3>
            <?php
            $includes_loaded = [];
            $includes_failed = [];
            
            $required_files = [
                '../config/database.php',
                '../core/Database.php', 
                '../app/models/Model.php',
                '../app/models/Usuario.php',
                '../app/models/Producto.php',
                '../app/models/Pedido.php',
                '../app/controllers/DashboardController.php'
            ];
            
            foreach ($required_files as $file) {
                try {
                    require_once $file;
                    $includes_loaded[] = $file;
                    echo "<div class='success'>✅ " . basename($file) . " cargado</div>";
                } catch (Exception $e) {
                    $includes_failed[] = $file . " - " . $e->getMessage();
                    echo "<div class='error'>❌ " . basename($file) . " falló: " . $e->getMessage() . "</div>";
                }
            }
            debug_headers();
            ?>
        </div>

        <div class="step info">
            <h3>🎯 Paso 5: Instanciando DashboardController</h3>
            <?php
            try {
                $dashboardController = new DashboardController();
                echo "<div class='success'>✅ DashboardController instanciado</div>";
                debug_headers();
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error instanciando DashboardController: " . $e->getMessage() . "</div>";
                echo "<div class='error'>📍 Archivo: " . $e->getFile() . "</div>";
                echo "<div class='error'>📍 Línea: " . $e->getLine() . "</div>";
            }
            ?>
        </div>

        <div class="step info">
            <h3>💾 Paso 6: Llamando método del dashboard según tipo de usuario</h3>
            <?php
            if (isset($user) && isset($dashboardController)) {
                try {
                    switch ($user['tipo']) {
                        case 'vendedor':
                            echo "<div class='info'>📊 Llamando dashboardVendedor()...</div>";
                            $dashboardData = $dashboardController->dashboardVendedor();
                            break;
                        case 'cliente':
                            echo "<div class='info'>📊 Llamando dashboardCliente()...</div>";
                            $dashboardData = $dashboardController->dashboardCliente();
                            break;
                        case 'admin':
                            echo "<div class='info'>📊 Llamando dashboardAdmin()...</div>";
                            $dashboardData = $dashboardController->dashboardAdmin();
                            break;
                        default:
                            echo "<div class='error'>❌ Tipo de usuario inválido: " . htmlspecialchars($user['tipo']) . "</div>";
                            $dashboardData = null;
                    }
                    
                    if ($dashboardData) {
                        echo "<div class='success'>✅ Método del dashboard ejecutado exitosamente</div>";
                        echo "<div class='info'>📋 Datos devueltos:</div>";
                        echo "<pre>" . print_r(array_keys($dashboardData), true) . "</pre>";
                    }
                    debug_headers();
                    
                } catch (Exception $e) {
                    echo "<div class='error'>🚨 EXCEPCIÓN EN MÉTODO DASHBOARD - ¡ESTA ES LA CAUSA!</div>";
                    echo "<div class='error'>❌ Mensaje: " . $e->getMessage() . "</div>";
                    echo "<div class='error'>📁 Archivo: " . $e->getFile() . "</div>";
                    echo "<div class='error'>📍 Línea: " . $e->getLine() . "</div>";
                    echo "<div class='warning'>⚠️ Esta excepción causa el redirect a index.php en dashboard.php</div>";
                    echo "<pre>" . $e->getTraceAsString() . "</pre>";
                }
            }
            ?>
        </div>

        <div class="step success">
            <h3>🎉 Conclusión</h3>
            <p>Si llegaste hasta aquí sin errores, el problema NO está en el código PHP.</p>
            <p>El problema puede ser:</p>
            <ul>
                <li>🔄 JavaScript ejecutándose en dashboard.php que redirige</li>
                <li>📱 Cache del navegador con versión antigua</li>
                <li>🌐 Configuración del servidor Apache</li>
                <li>📄 Output buffer problems</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard.php" style="background:#007bff;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;">🎯 Intentar Dashboard Original</a>
            <a href="dashboard-simple.php" style="background:#28a745;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;margin-left:10px;">✅ Dashboard Simple</a>
        </div>
    </div>
</body>
</html>

<?php
ob_end_flush();
?>