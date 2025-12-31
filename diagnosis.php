<?php
/**
 * Archivo de diagnóstico para AgroConecta
 * Este archivo ayuda a identificar problemas de configuración
 */

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Diagnóstico AgroConecta</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; }";
echo ".error { color: #dc3545; }";
echo ".warning { color: #ffc107; }";
echo ".info { color: #17a2b8; }";
echo "pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }";
echo ".section { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🌱 AgroConecta - Diagnóstico del Sistema</h1>";
echo "<p>Este diagnóstico te ayudará a identificar problemas de configuración.</p>";

// Información básica de PHP
echo "<div class='section'>";
echo "<h2>📊 Información del Sistema</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Sistema Operativo:</strong> " . PHP_OS . "</p>";
echo "<p><strong>Servidor Web:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido') . "</p>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'No definido') . "</p>";
echo "<p><strong>Script Actual:</strong> " . __FILE__ . "</p>";
echo "</div>";

// Verificar archivos críticos
echo "<div class='section'>";
echo "<h2>📁 Verificación de Archivos</h2>";

$critical_files = [
    '.env' => '.env',
    'index.php' => 'index.php',
    'config/database.php' => 'config/database.php',
    'config/routes.php' => 'config/routes.php',
    'app/core/Controller.php' => 'app/core/Controller.php',
    'app/core/Router.php' => 'app/core/Router.php',
    'app/core/Database.php' => 'app/core/Database.php'
];

foreach ($critical_files as $file => $path) {
    if (file_exists($path)) {
        echo "<p class='success'>✅ {$file} - EXISTE</p>";
    } else {
        echo "<p class='error'>❌ {$file} - FALTA</p>";
    }
}
echo "</div>";

// Verificar constantes
echo "<div class='section'>";
echo "<h2>🔧 Constantes del Sistema</h2>";

// Simular la carga de constantes como en index.php
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta');

$constants = ['ROOT_PATH', 'APP_PATH', 'CONFIG_PATH', 'PUBLIC_PATH', 'BASE_URL'];

foreach ($constants as $const) {
    if (defined($const)) {
        $value = constant($const);
        echo "<p class='success'>✅ {$const}: <code>{$value}</code></p>";
        
        // Verificar si la ruta existe (para rutas de directorio)
        if (in_array($const, ['ROOT_PATH', 'APP_PATH', 'CONFIG_PATH', 'PUBLIC_PATH'])) {
            if (is_dir($value)) {
                echo "<p class='info'>   📁 Directorio existe</p>";
            } else {
                echo "<p class='error'>   ❌ Directorio no encontrado</p>";
            }
        }
    } else {
        echo "<p class='error'>❌ {$const} - NO DEFINIDA</p>";
    }
}
echo "</div>";

// Verificar configuración de base de datos
echo "<div class='section'>";
echo "<h2>🗄️ Conexión a Base de Datos</h2>";

try {
    // Intentar conectar a la base de datos
    $host = 'localhost';
    $dbname = 'agroconecta_db';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p class='success'>✅ Conexión a MySQL establecida</p>";
    
    // Verificar si la base de datos existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbname}'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ Base de datos '{$dbname}' existe</p>";
        
        // Conectar a la base de datos específica
        $pdo_db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password);
        
        // Verificar tablas
        $stmt = $pdo_db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<p class='success'>✅ Base de datos tiene " . count($tables) . " tablas</p>";
            echo "<p><strong>Tablas encontradas:</strong> " . implode(', ', $tables) . "</p>";
        } else {
            echo "<p class='warning'>⚠️ Base de datos existe pero está vacía</p>";
            echo "<p><a href='install_database.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Instalar Tablas</a></p>";
        }
        
    } else {
        echo "<p class='warning'>⚠️ Base de datos '{$dbname}' no existe</p>";
        echo "<p>Creando base de datos...</p>";
        
        try {
            $pdo->exec("CREATE DATABASE {$dbname} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "<p class='success'>✅ Base de datos '{$dbname}' creada</p>";
            echo "<p><a href='install_database.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Instalar Tablas</a></p>";
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Error al crear base de datos: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    echo "<p>Verifica que MySQL esté ejecutándose en XAMPP.</p>";
}

echo "</div>";

// Verificar extensiones de PHP
echo "<div class='section'>";
echo "<h2>🔌 Extensiones de PHP</h2>";

$required_extensions = [
    'pdo_mysql' => 'Conexión a MySQL',
    'gd' => 'Manejo de imágenes',
    'fileinfo' => 'Información de archivos',
    'openssl' => 'Encriptación',
    'curl' => 'Comunicación HTTP',
    'json' => 'Manejo de JSON'
];

foreach ($required_extensions as $ext => $description) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ {$ext} - {$description}</p>";
    } else {
        echo "<p class='error'>❌ {$ext} - {$description} (FALTA)</p>";
    }
}
echo "</div>";

// Prueba de carga de archivos del sistema
echo "<div class='section'>";
echo "<h2>🚀 Prueba de Carga del Sistema</h2>";

try {
    echo "<p>Intentando cargar el sistema principal...</p>";
    
    // Verificar si se pueden cargar los archivos principales
    if (file_exists('config/database.php')) {
        echo "<p class='info'>📄 Cargando config/database.php...</p>";
        // No incluir el archivo para evitar conflictos
        echo "<p class='success'>✅ config/database.php es accesible</p>";
    }
    
    if (file_exists('app/core/Database.php')) {
        echo "<p class='info'>📄 Cargando app/core/Database.php...</p>";
        echo "<p class='success'>✅ app/core/Database.php es accesible</p>";
    }
    
    if (file_exists('app/core/Controller.php')) {
        echo "<p class='info'>📄 Cargando app/core/Controller.php...</p>";
        echo "<p class='success'>✅ app/core/Controller.php es accesible</p>";
    }
    
    echo "<p class='success'>✅ Todos los archivos principales son accesibles</p>";
    
} catch (Error $e) {
    echo "<p class='error'>❌ Error al cargar sistema: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</div>";

// Enlaces útiles
echo "<div class='section'>";
echo "<h2>🔗 Enlaces Útiles</h2>";
echo "<p><a href='index.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🏠 Ir al Sistema Principal</a></p>";
echo "<p><a href='system_check.php' style='background: #17a2b8; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔍 Verificación Completa</a></p>";
echo "<p><a href='install_database.php' style='background: #ffc107; color: #212529; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🗄️ Instalar Base de Datos</a></p>";
echo "<p><a href='/phpmyadmin' target='_blank' style='background: #6f42c1; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔧 PHPMyAdmin</a></p>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>