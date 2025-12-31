<?php
/**
 * Prueba de conexión MySQL para AgroConecta
 */

echo "=== PRUEBA DE CONEXIÓN MYSQL ===\n\n";

// Configuración
$host = '127.0.0.1';
$username = 'root';
$database = 'agroconecta_db';
$passwords_to_try = ['', 'root', 'mysql', 'password'];

echo "1. Probando diferentes contraseñas...\n";
echo "   Host: {$host}\n";
echo "   Usuario: {$username}\n\n";

$connected = false;
$working_password = '';
$pdo = null;

foreach ($passwords_to_try as $test_password) {
    echo "Probando contraseña: " . ($test_password === '' ? '(vacía)' : "'{$test_password}'") . "\n";
    
    try {
        $pdo = new PDO("mysql:host={$host}", $username, $test_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "   ✅ ¡CONEXIÓN EXITOSA!\n\n";
        $connected = true;
        $working_password = $test_password;
        break;
        
    } catch (PDOException $e) {
        echo "   ❌ Falló\n";
    }
}

if (!$connected) {
    echo "\n❌ No se pudo conectar con ninguna contraseña.\n";
    echo "Verifica que MySQL esté corriendo en el panel de XAMPP.\n";
    echo "=== FIN ===\n";
    exit(1);
}

echo "2. ✅ Conexión establecida con contraseña: " . ($working_password === '' ? '(vacía)' : "'{$working_password}'") . "\n\n";

// Listar bases de datos existentes
echo "3. Bases de datos existentes:\n";
$stmt = $pdo->query("SHOW DATABASES");
$databases = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($databases as $db) {
    echo "   - {$db}\n";
}

echo "\n4. Verificando si existe agroconecta_db...\n";
if (in_array($database, $databases)) {
    echo "   ✅ La base de datos '{$database}' YA EXISTE\n";
} else {
    echo "   ⚠️ La base de datos '{$database}' NO EXISTE\n";
    echo "   Creándola...\n";
    
    $pdo->exec("CREATE DATABASE {$database} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✅ Base de datos '{$database}' creada exitosamente\n";
}

echo "\n=== RESUMEN ===\n";
echo "✅ MySQL está funcionando correctamente\n";
echo "✅ Conexión establecida sin problemas\n";
echo "✅ Contraseña que funciona: " . ($working_password === '' ? '(vacía)' : "'{$working_password}'") . "\n";
echo "✅ Listo para instalar las tablas del proyecto\n";

echo "\n🎯 ACTUALIZA config/database.php con:\n";
echo "define('DB_HOST', '127.0.0.1');\n";
echo "define('DB_USER', 'root');\n";
echo "define('DB_PASS', '{$working_password}');\n";

echo "\n=== FIN ===\n";
?>