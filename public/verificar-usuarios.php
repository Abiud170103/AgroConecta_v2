<?php
/**
 * Script de verificación de usuarios para AgroConecta
 * Verifica la conexión a la base de datos y lista usuarios existentes
 */

// Configuración de errores para desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔍 Verificación del Sistema AgroConecta</h2>";

// Paso 1: Verificar archivos necesarios
echo "<h3>📁 Verificando archivos del sistema...</h3>";

$archivos_requeridos = [
    '../config/database.php' => 'Configuración de BD',
    '../core/Database.php' => 'Clase Database',
    '../core/SessionManager.php' => 'Gestor de sesiones',
    '../app/models/Usuario.php' => 'Modelo Usuario'
];

$archivos_ok = true;
foreach ($archivos_requeridos as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "✅ $descripcion: OK<br>";
    } else {
        echo "❌ $descripcion: NO ENCONTRADO ($archivo)<br>";
        $archivos_ok = false;
    }
}

if (!$archivos_ok) {
    echo "<p style='color: red;'><strong>Error:</strong> Faltan archivos del sistema.</p>";
    exit;
}

// Paso 2: Cargar archivos
try {
    require_once '../config/database.php';
    require_once '../core/Database.php';
    echo "<br>📚 Archivos cargados correctamente<br>";
} catch (Exception $e) {
    echo "<br>❌ Error cargando archivos: " . $e->getMessage();
    exit;
}

// Paso 3: Verificar conexión a BD
echo "<h3>🔌 Verificando conexión a base de datos...</h3>";

try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Conexión a base de datos: OK<br>";
    echo "📍 Host: " . DB_HOST . "<br>";
    echo "📍 Base de datos: " . DB_NAME . "<br>";
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
    echo "<p><strong>Posibles soluciones:</strong></p>";
    echo "<ul>";
    echo "<li>Verificar que XAMPP esté iniciado</li>";
    echo "<li>Verificar que MySQL esté corriendo</li>";
    echo "<li>Verificar las credenciales en config/database.php</li>";
    echo "<li>Crear la base de datos 'agroconecta_db' si no existe</li>";
    echo "</ul>";
    exit;
}

// Paso 4: Verificar tabla usuarios
echo "<h3>👥 Verificando tabla de usuarios...</h3>";

try {
    // Verificar si la tabla existe
    $stmt = $db->query("SHOW TABLES LIKE 'usuario'");
    if ($stmt->rowCount() == 0) {
        echo "❌ La tabla 'usuario' no existe<br>";
        echo "<p><a href='crear-usuarios-prueba.php'>Crear usuarios de prueba</a></p>";
        exit;
    }
    echo "✅ Tabla 'usuario' encontrada<br>";
    
    // Contar usuarios
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuario");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "📊 Total de usuarios: $total<br>";
    
    if ($total == 0) {
        echo "<br><p><strong>No hay usuarios en la base de datos.</strong></p>";
        echo "<p><a href='crear-usuarios-prueba.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Crear Usuarios de Prueba</a></p>";
    } else {
        // Mostrar usuarios existentes
        echo "<h3>📋 Usuarios existentes:</h3>";
        
        $stmt = $db->query("SELECT id_usuario, nombre, correo, tipo_usuario, activo, fecha_registro FROM usuario ORDER BY tipo_usuario, nombre");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f5f5f5;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Nombre</th>";
        echo "<th style='padding: 8px;'>Email</th>";
        echo "<th style='padding: 8px;'>Tipo</th>";
        echo "<th style='padding: 8px;'>Estado</th>";
        echo "<th style='padding: 8px;'>Registro</th>";
        echo "</tr>";
        
        foreach ($usuarios as $usuario) {
            $color = $usuario['activo'] ? '#e8f5e8' : '#ffe8e8';
            echo "<tr style='background: $color;'>";
            echo "<td style='padding: 8px;'>" . $usuario['id_usuario'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($usuario['nombre']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($usuario['correo']) . "</td>";
            echo "<td style='padding: 8px;'>";
            
            switch($usuario['tipo_usuario']) {
                case 'admin': echo "🛡️ Admin"; break;
                case 'vendedor': echo "👨‍💼 Vendedor"; break;
                case 'cliente': echo "👤 Cliente"; break;
                default: echo $usuario['tipo_usuario'];
            }
            
            echo "</td>";
            echo "<td style='padding: 8px;'>" . ($usuario['activo'] ? '✅ Activo' : '❌ Inactivo') . "</td>";
            echo "<td style='padding: 8px;'>" . date('d/m/Y', strtotime($usuario['fecha_registro'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Mostrar credenciales de prueba
        echo "<h3>🔑 Credenciales para pruebas:</h3>";
        echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p><strong>📧 Todos los usuarios de prueba usan la contraseña:</strong> <code style='background: #e0e0e0; padding: 2px 6px;'>prueba123</code></p>";
        
        foreach ($usuarios as $usuario) {
            if (strpos($usuario['correo'], '@test.com') !== false) {
                $icon = '';
                switch($usuario['tipo_usuario']) {
                    case 'admin': $icon = '🛡️'; break;
                    case 'vendedor': $icon = '👨‍💼'; break;
                    case 'cliente': $icon = '👤'; break;
                }
                echo "<p>$icon <strong>" . ucfirst($usuario['tipo_usuario']) . ":</strong> " . $usuario['correo'] . "</p>";
            }
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "❌ Error consultando usuarios: " . $e->getMessage();
}

// Enlaces de acción
echo "<hr>";
echo "<h3>🔗 Enlaces útiles:</h3>";
echo "<p>";
echo "<a href='login.php' style='background: #2196F3; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>🔐 Iniciar Sesión</a>";
echo "<a href='crear-usuarios-prueba.php' style='background: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>👥 Crear Usuarios</a>";
echo "<a href='dashboard.php' style='background: #FF9800; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>📊 Dashboard</a>";
echo "</p>";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Sistema - AgroConecta</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
            background-color: #f8f9fa;
        }
        h2, h3 { color: #2c3e50; }
        table { 
            background: white; 
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #34495e; color: white; }
        code { 
            background: #e8e8e8; 
            padding: 2px 6px; 
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
</html>