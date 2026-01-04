<?php
/**
 * Test Directo de Vistas - Dashboard
 * Prueba directa de inclusión de vistas sin controlador
 */

require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';

// Verificar que hay sesión activa
if (!SessionManager::isLoggedIn()) {
    echo "<h1>❌ No autenticado</h1>";
    echo "<p><a href='login-simple.php'>Iniciar sesión primero</a></p>";
    exit;
}

$user = SessionManager::getUserData();

echo "<h1>🧪 Test Directo de Vistas</h1>";
echo "<p><strong>Usuario:</strong> " . $user['correo'] . " (Tipo: " . $user['tipo'] . ")</p>";

// Preparar datos mínimos para las vistas
$statsVentas = ['total_ventas' => 0, 'pendientes' => 0];
$statsProductos = ['activos' => 0, 'agotados' => 0];
$statsClientes = ['nuevos' => 0, 'activos' => 0];
$statsGenerales = ['total_usuarios' => 5, 'total_productos' => 0];
$productosRecientes = [];
$pedidosRecientes = [];
$ventasRecientes = [];
$clientesRecientes = [];
$usuariosRecientes = [];

echo "<h2>🎯 Prueba de Vista según Tipo de Usuario</h2>";

// Buffer output para capturar errores
ob_start();

try {
    switch ($user['tipo']) {
        case 'vendedor':
            echo "<div style='background: #d1ecf1; padding: 10px; margin: 10px 0;'>";
            echo "<strong>📊 Incluyendo vista vendedor...</strong><br>";
            echo "</div>";
            
            // Intentar incluir la vista vendedor
            include '../app/views/dashboard/vendedor.php';
            echo "<p style='color: green; font-weight: bold;'>✅ Vista vendedor incluida exitosamente!</p>";
            break;
            
        case 'cliente':
            echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0;'>";
            echo "<strong>📊 Incluyendo vista cliente...</strong><br>";
            echo "</div>";
            
            // Preparar datos específicos para cliente
            $statsFavoritos = ['total' => 0];
            $itemsCarrito = [];
            $categoriasPopulares = [];
            $productosDestacados = [];
            $recomendaciones = [];
            
            include '../app/views/dashboard/cliente.php';
            echo "<p style='color: green; font-weight: bold;'>✅ Vista cliente incluida exitosamente!</p>";
            break;
            
        case 'admin':
            echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
            echo "<strong>📊 Incluyendo vista admin...</strong><br>";
            echo "</div>";
            
            // Preparar datos específicos para admin
            $alertasImportantes = [];
            $actividadReciente = [];
            $datosGraficoCrecimiento = [];
            $statsUsuarios = ['vendedores' => 2, 'clientes' => 2, 'admins' => 1];
            $statsProductos = ['activos' => 0, 'pendientes' => 0, 'agotados' => 0];
            $statsPedidos = ['pendientes' => 0, 'confirmados' => 0, 'enviados' => 0, 'completados' => 0];
            $categoriasPopulares = [];
            
            include '../app/views/dashboard/admin.php';
            echo "<p style='color: green; font-weight: bold;'>✅ Vista admin incluida exitosamente!</p>";
            break;
            
        default:
            echo "<p style='color: red;'>❌ Tipo de usuario no reconocido: " . $user['tipo'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<strong>❌ ERROR CAPTURADO:</strong><br>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
} catch (ParseError $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<strong>❌ ERROR DE PARSING:</strong><br>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
} catch (Error $e) {
    echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
    echo "<strong>⚠️ ERROR FATAL:</strong><br>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}

$output = ob_get_clean();

if (empty(trim(strip_tags($output)))) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0;'>";
    echo "<strong>⚠️ SALIDA VACÍA DETECTADA</strong><br>";
    echo "La vista no produjo salida visible. Esto puede indicar:<br>";
    echo "• Error silencioso en la vista<br>";
    echo "• Redirección inmediata (headers enviados)<br>";
    echo "• Problema con las variables requeridas<br>";
    echo "</div>";
} else {
    // La vista produjo salida, mostrarla
    echo $output;
}

echo "<hr>";
echo "<p><a href='dashboard.php' style='background:#dc3545;color:white;padding:8px;text-decoration:none;border-radius:4px;'>Dashboard Original</a> ";
echo "<a href='login-simple.php' style='background:#007bff;color:white;padding:8px;text-decoration:none;border-radius:4px;margin-left:10px;'>Login Simple</a></p>";
?>