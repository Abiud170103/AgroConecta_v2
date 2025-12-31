<?php
/**
 * Instalador de base de datos para AgroConecta
 * Ejecuta los scripts SQL para crear tablas y datos iniciales
 */

echo "=== INSTALADOR BASE DE DATOS AGROCONECTA ===\n\n";

// Configuración
$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'agroconecta_db';

try {
    // Conectar a la base de datos
    echo "1. Conectando a MySQL...\n";
    $pdo = new PDO("mysql:host={$host};dbname={$database};charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✅ Conexión establecida\n\n";
    
    // Leer y ejecutar schema.sql
    echo "2. Instalando estructura de tablas...\n";
    $schema_file = 'database/schema.sql';
    
    if (!file_exists($schema_file)) {
        throw new Exception("No se encontró el archivo {$schema_file}");
    }
    
    $schema_sql = file_get_contents($schema_file);
    
    // Ejecutar cada statement por separado
    $statements = explode(';', $schema_sql);
    $tables_created = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && $statement !== '') {
            try {
                $pdo->exec($statement);
                if (stripos($statement, 'CREATE TABLE') !== false) {
                    $tables_created++;
                    // Extraer nombre de tabla
                    preg_match('/CREATE TABLE `?([a-zA-Z0-9_]+)`?/i', $statement, $matches);
                    $table_name = $matches[1] ?? 'desconocida';
                    echo "   ✅ Tabla '{$table_name}' creada\n";
                }
            } catch (PDOException $e) {
                // Ignorar errores de tabla ya existente
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "   ⚠️ Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n3. Verificando tablas creadas...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   Tablas encontradas: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "   - {$table}\n";
    }
    
    // Instalar datos iniciales si existe seeders.sql
    $seeders_file = 'database/seeders.sql';
    if (file_exists($seeders_file)) {
        echo "\n4. Instalando datos iniciales...\n";
        $seeders_sql = file_get_contents($seeders_file);
        
        $seeder_statements = explode(';', $seeders_sql);
        $records_inserted = 0;
        
        foreach ($seeder_statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && $statement !== '') {
                try {
                    $pdo->exec($statement);
                    if (stripos($statement, 'INSERT') !== false) {
                        $records_inserted++;
                    }
                } catch (PDOException $e) {
                    echo "   ⚠️ Warning en seeders: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "   ✅ {$records_inserted} statements de datos ejecutados\n";
    } else {
        echo "\n4. ⚠️ No se encontró seeders.sql, saltando datos iniciales\n";
    }
    
    // Verificar contenido de algunas tablas
    echo "\n5. Verificando contenido...\n";
    
    $test_tables = ['Usuario', 'Producto', 'Categoria'];
    foreach ($test_tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `{$table}`");
            $count = $stmt->fetchColumn();
            echo "   - {$table}: {$count} registros\n";
        } catch (PDOException $e) {
            echo "   - {$table}: Tabla no existe o error\n";
        }
    }
    
    echo "\n=== INSTALACIÓN COMPLETADA ===\n";
    echo "✅ Base de datos instalada correctamente\n";
    echo "✅ Tablas creadas: " . count($tables) . "\n";
    echo "✅ Listo para usar la aplicación\n";
    echo "\n🚀 PRÓXIMO PASO: Ejecutar 'php app/models/test_models.php' para probar los modelos\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la instalación: " . $e->getMessage() . "\n";
    echo "\nRevisa:\n";
    echo "1. Que MySQL esté corriendo en XAMPP\n";
    echo "2. Que los archivos schema.sql existan\n";
    echo "3. Que la configuración de BD sea correcta\n";
}

echo "\n=== FIN ===\n";
?>