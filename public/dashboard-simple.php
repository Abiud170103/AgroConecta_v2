<?php
/**
 * Dashboard Simplificado - AgroConecta
 * Versión sin redirecciones para evitar bucles
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cargar archivos esenciales
require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';

// Inicializar sesión
SessionManager::startSecureSession();

echo "<h2>📊 Dashboard Simplificado - AgroConecta</h2>";

// Verificar autenticación
if (!SessionManager::isLoggedIn()) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>❌ No Autenticado</h4>";
    echo "<p>Debes iniciar sesión para acceder al dashboard.</p>";
    echo "<p><a href='login-simple.php'>Iniciar Sesión</a></p>";
    echo "</div>";
    exit;
}

// Obtener datos del usuario
$user = SessionManager::getUserData();

echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>✅ Usuario Autenticado</h4>";
echo "<p><strong>Nombre:</strong> " . htmlspecialchars($user['nombre']) . "</p>";
echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
echo "<p><strong>Tipo:</strong> " . $user['tipo'] . "</p>";
echo "<p><strong>ID:</strong> " . $user['id'] . "</p>";
echo "</div>";

// Dashboard específico según tipo de usuario
switch ($user['tipo']) {
    case 'vendedor':
        echo "<h3>👨‍💼 Dashboard de Vendedor</h3>";
        
        echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 12px; margin: 15px 0;'>";
        echo "<h4>📊 Estadísticas Rápidas</h4>";
        
        // Datos básicos sin consultas complejas
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0;'>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>";
        echo "<h5>🛒 Productos</h5>";
        echo "<p style='font-size: 24px; margin: 5px 0;'>0</p>";
        echo "<small>Total productos</small>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>";
        echo "<h5>📦 Pedidos</h5>";
        echo "<p style='font-size: 24px; margin: 5px 0;'>0</p>";
        echo "<small>Pedidos activos</small>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>";
        echo "<h5>💰 Ventas</h5>";
        echo "<p style='font-size: 24px; margin: 5px 0;'>$0</p>";
        echo "<small>Total ventas</small>";
        echo "</div>";
        
        echo "</div>";
        
        echo "<h4>🔧 Herramientas de Vendedor</h4>";
        echo "<p>";
        echo "<button style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;'>➕ Agregar Producto</button>";
        echo "<button style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;'>📦 Ver Pedidos</button>";
        echo "<button style='background: #17a2b8; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>📊 Reportes</button>";
        echo "</p>";
        
        echo "</div>";
        break;
        
    case 'cliente':
        echo "<h3>👤 Dashboard de Cliente</h3>";
        
        echo "<div style='background: #fff3e0; padding: 20px; border-radius: 12px; margin: 15px 0;'>";
        echo "<h4>🛍️ Tu Actividad</h4>";
        
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0;'>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>";
        echo "<h5>🛒 Carrito</h5>";
        echo "<p style='font-size: 24px; margin: 5px 0;'>0</p>";
        echo "<small>Productos en carrito</small>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>";
        echo "<h5>📦 Pedidos</h5>";
        echo "<p style='font-size: 24px; margin: 5px 0;'>0</p>";
        echo "<small>Pedidos realizados</small>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>";
        echo "<h5>❤️ Favoritos</h5>";
        echo "<p style='font-size: 24px; margin: 5px 0;'>0</p>";
        echo "<small>Productos favoritos</small>";
        echo "</div>";
        
        echo "</div>";
        
        echo "<h4>🛍️ Herramientas de Cliente</h4>";
        echo "<p>";
        echo "<button style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;'>🛒 Ver Catálogo</button>";
        echo "<button style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;'>📦 Mis Pedidos</button>";
        echo "<button style='background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>❤️ Favoritos</button>";
        echo "</p>";
        
        echo "</div>";
        break;
        
    case 'admin':
        echo "<h3>🛡️ Dashboard de Administrador</h3>";
        
        echo "<div style='background: #f3e5f5; padding: 20px; border-radius: 12px; margin: 15px 0;'>";
        echo "<h4>⚙️ Panel de Control</h4>";
        
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0;'>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>";
        echo "<h5>👥 Usuarios</h5>";
        echo "<p style='font-size: 24px; margin: 5px 0;'>5</p>";
        echo "<small>Total usuarios</small>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>";
        echo "<h5>🛒 Productos</h5>";
        echo "<p style='font-size: 24px; margin: 5px 0;'>0</p>";
        echo "<small>Total productos</small>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>";
        echo "<h5>📊 Sistema</h5>";
        echo "<p style='font-size: 24px; margin: 5px 0;'>✅</p>";
        echo "<small>Estado del sistema</small>";
        echo "</div>";
        
        echo "</div>";
        
        echo "<h4>🔧 Herramientas de Admin</h4>";
        echo "<p>";
        echo "<button style='background: #6f42c1; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;'>👥 Gestionar Usuarios</button>";
        echo "<button style='background: #fd7e14; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;'>📊 Reportes</button>";
        echo "<button style='background: #20c997; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>⚙️ Configuración</button>";
        echo "</p>";
        
        echo "</div>";
        break;
        
    default:
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>❌ Tipo de Usuario No Válido</h4>";
        echo "<p>Tipo detectado: " . htmlspecialchars($user['tipo']) . "</p>";
        echo "</div>";
}

// Enlaces de navegación
echo "<hr>";
echo "<h3>🔗 Navegación</h3>";
echo "<p>";
echo "<a href='logout.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🚪 Cerrar Sesión</a>";
echo "<a href='dashboard.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🎛️ Dashboard Completo</a>";
echo "<a href='debug-dashboard.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Debug Dashboard</a>";
echo "</p>";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Simplificado - AgroConecta</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
            background-color: #f8f9fa;
        }
        h2, h3, h4, h5 { color: #2c3e50; margin-bottom: 10px; }
        button { cursor: pointer; transition: opacity 0.2s; }
        button:hover { opacity: 0.8; }
    </style>
</head>
</html>