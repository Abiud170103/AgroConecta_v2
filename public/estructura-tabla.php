<?php
/**
 * Verificador de estructura de tabla Usuario
 */

require_once '../config/database.php';
require_once '../core/Database.php';

echo "<h2>📋 Estructura de la tabla Usuario - AgroConecta</h2>";

try {
    $db = Database::getInstance()->getConnection();
    
    // Verificar si la tabla existe
    $stmt = $db->query("SHOW TABLES LIKE 'usuario'");
    if ($stmt->rowCount() == 0) {
        echo "<p>❌ La tabla 'usuario' no existe</p>";
        exit;
    }
    
    echo "<h3>✅ Tabla 'usuario' encontrada</h3>";
    
    // Mostrar estructura de la tabla
    echo "<h4>📋 Columnas disponibles:</h4>";
    $stmt = $db->query("DESCRIBE usuario");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f5f5f5;'>";
    echo "<th style='padding: 10px;'>Columna</th>";
    echo "<th style='padding: 10px;'>Tipo</th>";
    echo "<th style='padding: 10px;'>Null</th>";
    echo "<th style='padding: 10px;'>Key</th>";
    echo "<th style='padding: 10px;'>Default</th>";
    echo "</tr>";
    
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td style='padding: 8px;'><strong>" . $col['Field'] . "</strong></td>";
        echo "<td style='padding: 8px;'>" . $col['Type'] . "</td>";
        echo "<td style='padding: 8px;'>" . $col['Null'] . "</td>";
        echo "<td style='padding: 8px;'>" . $col['Key'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($col['Default'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Contar registros
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuario");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<h4>📊 Registros en la tabla: $total</h4>";
    
    if ($total > 0) {
        echo "<h4>📄 Primeros 5 registros:</h4>";
        $stmt = $db->query("SELECT * FROM usuario LIMIT 5");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($usuarios)) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            echo "<tr style='background: #f5f5f5;'>";
            
            // Encabezados
            foreach (array_keys($usuarios[0]) as $columna) {
                echo "<th style='padding: 8px;'>" . $columna . "</th>";
            }
            echo "</tr>";
            
            // Datos
            foreach ($usuarios as $usuario) {
                echo "<tr>";
                foreach ($usuario as $key => $value) {
                    if ($key === 'contraseña' || $key === 'password') {
                        echo "<td style='padding: 8px;'>****</td>";
                    } else {
                        echo "<td style='padding: 8px;'>" . htmlspecialchars($value) . "</td>";
                    }
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    echo "<hr>";
    echo "<p>";
    echo "<a href='login-debug.php' style='background: #2196F3; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Debug Login</a>";
    echo "<a href='crear-usuarios-prueba.php' style='background: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Crear Usuarios</a>";
    echo "<a href='login.php' style='background: #FF9800; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Volver al Login</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estructura Tabla - AgroConecta</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h2, h3, h4 { color: #2c3e50; }
        table { background: white; border-collapse: collapse; }
        th { background: #34495e; color: white; }
    </style>
</head>
</html>