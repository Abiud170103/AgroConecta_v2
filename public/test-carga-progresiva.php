<?php
/**
 * Test de carga progresiva - Identificar dependencia problemática
 */

// Prevenir output
ob_start();

// Headers básicos
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Sesión básica
session_start();

// Verificación básica
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header('Location: login.php');
    exit;
}

ob_end_clean();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Carga Progresiva</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test { margin: 15px 0; padding: 15px; border-radius: 8px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    </style>
</head>
<body>
    <h1>🔍 Test de Carga Progresiva - Identificando Dependencia Problemática</h1>
    
    <div class="test info">
        <h3>✅ Paso 1: Sesión PHP básica funcionando</h3>
        <p>Usuario ID: <?php echo $_SESSION['user_id']; ?></p>
    </div>

    <div class="test">
        <h3>🧪 Paso 2: Cargando SessionManager...</h3>
        <?php
        try {
            require_once '../core/SessionManager.php';
            echo '<div class="success">✅ SessionManager cargado sin problemas</div>';
            
            // Verificar si SessionManager funciona
            $isLoggedIn = SessionManager::isLoggedIn();
            echo '<div class="' . ($isLoggedIn ? 'success' : 'error') . '">';
            echo ($isLoggedIn ? '✅' : '❌') . ' SessionManager::isLoggedIn() = ' . ($isLoggedIn ? 'TRUE' : 'FALSE');
            echo '</div>';
            
            if ($isLoggedIn) {
                $userData = SessionManager::getUserData();
                if ($userData) {
                    echo '<div class="success">✅ SessionManager::getUserData() exitoso</div>';
                } else {
                    echo '<div class="error">❌ SessionManager::getUserData() devolvió null</div>';
                }
            }
            
        } catch (Exception $e) {
            echo '<div class="error">❌ Error cargando SessionManager: ' . $e->getMessage() . '</div>';
            echo '<div class="error">🚨 ESTE ES EL PROBLEMA</div>';
        }
        ?>
    </div>

    <div class="test">
        <h3>🧪 Paso 3: Cargando Database core...</h3>
        <?php
        try {
            require_once '../config/database.php';
            require_once '../core/Database.php';
            echo '<div class="success">✅ Database core cargado</div>';
        } catch (Exception $e) {
            echo '<div class="error">❌ Error cargando Database: ' . $e->getMessage() . '</div>';
            echo '<div class="error">🚨 ESTE ES EL PROBLEMA</div>';
        }
        ?>
    </div>

    <div class="test">
        <h3>🧪 Paso 4: Cargando Models...</h3>
        <?php
        try {
            require_once '../app/models/Model.php';
            echo '<div class="success">✅ Model base cargado</div>';
            
            require_once '../app/models/Usuario.php';
            echo '<div class="success">✅ Usuario model cargado</div>';
            
            require_once '../app/models/Producto.php';
            echo '<div class="success">✅ Producto model cargado</div>';
            
            require_once '../app/models/Pedido.php';
            echo '<div class="success">✅ Pedido model cargado</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">❌ Error cargando Models: ' . $e->getMessage() . '</div>';
            echo '<div class="error">🚨 ESTE ES EL PROBLEMA</div>';
        }
        ?>
    </div>

    <div class="test">
        <h3>🧪 Paso 5: Instanciando DashboardController...</h3>
        <?php
        try {
            require_once '../app/controllers/DashboardController.php';
            echo '<div class="success">✅ DashboardController class cargada</div>';
            
            $dashboardController = new DashboardController();
            echo '<div class="success">✅ DashboardController instanciado</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">❌ Error instanciando DashboardController: ' . $e->getMessage() . '</div>';
            echo '<div class="error">📍 Archivo: ' . $e->getFile() . '</div>';
            echo '<div class="error">📍 Línea: ' . $e->getLine() . '</div>';
            echo '<div class="error">🚨 ESTE ES EL PROBLEMA</div>';
        }
        ?>
    </div>

    <div class="test">
        <h3>🧪 Paso 6: Llamando método dashboard...</h3>
        <?php
        if (isset($dashboardController) && isset($_SESSION['user_tipo'])) {
            try {
                switch ($_SESSION['user_tipo']) {
                    case 'vendedor':
                        $result = $dashboardController->dashboardVendedor();
                        echo '<div class="success">✅ dashboardVendedor() ejecutado exitosamente</div>';
                        break;
                    case 'cliente':
                        $result = $dashboardController->dashboardCliente();
                        echo '<div class="success">✅ dashboardCliente() ejecutado exitosamente</div>';
                        break;
                    case 'admin':
                        $result = $dashboardController->dashboardAdmin();
                        echo '<div class="success">✅ dashboardAdmin() ejecutado exitosamente</div>';
                        break;
                    default:
                        echo '<div class="error">❌ Tipo de usuario no válido: ' . $_SESSION['user_tipo'] . '</div>';
                }
                
                if (isset($result)) {
                    echo '<div class="info">📋 Datos devueltos: ' . count($result) . ' elementos</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="error">❌ Error ejecutando método dashboard: ' . $e->getMessage() . '</div>';
                echo '<div class="error">📍 Archivo: ' . $e->getFile() . '</div>';
                echo '<div class="error">📍 Línea: ' . $e->getLine() . '</div>';
                echo '<div class="error">🚨 ESTE ES EL PROBLEMA - MÉTODO DEL DASHBOARD</div>';
                echo '<div class="error">Stack trace:</div>';
                echo '<pre>' . $e->getTraceAsString() . '</pre>';
            }
        }
        ?>
    </div>

    <div class="test success">
        <h3>🎯 Conclusión</h3>
        <p>Si llegaste hasta aquí sin errores, todas las dependencias funcionan correctamente.</p>
        <p>El problema debe estar en la secuencia específica de dashboard.php o en algún output buffer.</p>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="dashboard-independiente.php" style="background:#28a745;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;">✅ Dashboard Independiente</a>
        <a href="dashboard.php" style="background:#dc3545;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;margin-left:10px;">❌ Dashboard Problemático</a>
    </div>
</body>
</html>