<?php
/**
 * Script para agregar productos al vendedor ID 46
 */

require_once '../config/database.php';
require_once '../core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "<h2>Verificando estructura de la tabla producto:</h2>\n";
    
    // Verificar estructura de la tabla producto
    $stmt = $pdo->query("DESCRIBE producto");
    $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>\n";
    foreach ($estructura as $campo) {
        echo "<tr>";
        echo "<td>" . $campo['Field'] . "</td>";
        echo "<td>" . $campo['Type'] . "</td>";
        echo "<td>" . $campo['Null'] . "</td>";
        echo "<td>" . $campo['Key'] . "</td>";
        echo "<td>" . $campo['Default'] . "</td>";
        echo "</tr>\n";
    }
    echo "</table><br>\n";
    
    // Verificar si el usuario ID 46 existe
    echo "<h2>Verificando usuario ID 46:</h2>\n";
    $stmt = $pdo->prepare("SELECT id_usuario, nombre, tipo_usuario FROM usuario WHERE id_usuario = ?");
    $stmt->execute([46]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "Usuario encontrado: " . $usuario['nombre'] . " (Tipo: " . $usuario['tipo_usuario'] . ")<br><br>\n";
        
        // Verificar productos existentes
        echo "<h2>Productos actuales del vendedor:</h2>\n";
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM producto WHERE id_usuario = ?");
        $stmt->execute([46]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "Total productos actuales: " . $count . "<br><br>\n";
        
        // Agregar productos de ejemplo (verificando duplicados)
        echo "<h2>Agregando productos de ejemplo:</h2>\n";
        
        $productos = [
            [
                'nombre' => 'Tomates Orgánicos Premium',
                'descripcion' => 'Tomates frescos cultivados de manera orgánica, perfectos para ensaladas y cocina gourmet. Sin pesticidas ni químicos.',
                'precio' => 45.50,
                'stock' => 150,
                'categoria' => 'Verduras',
                'unidad_medida' => 'kg',
                'imagen_url' => 'tomates-organicos.jpg',
                'activo' => 1
            ],
            [
                'nombre' => 'Aguacates Hass Extra',
                'descripcion' => 'Aguacates Hass de primera calidad, cremosos y nutritivos. Ideales para guacamole y platillos saludables.',
                'precio' => 65.00,
                'stock' => 80,
                'categoria' => 'Frutas',
                'unidad_medida' => 'kg',
                'imagen_url' => 'aguacates-hass.jpg',
                'activo' => 1
            ],
            [
                'nombre' => 'Lechugas Hidropónicas',
                'descripcion' => 'Lechugas frescas cultivadas en sistema hidropónico, crujientes y libres de tierra. Perfectas para ensaladas.',
                'precio' => 25.00,
                'stock' => 200,
                'categoria' => 'Verduras',
                'unidad_medida' => 'pza',
                'imagen_url' => 'lechugas-hidroponicas.jpg',
                'activo' => 1
            ],
            [
                'nombre' => 'Fresas de Temporada',
                'descripcion' => 'Fresas rojas y jugosas de temporada, cultivadas localmente. Ideales para postres y consumo directo.',
                'precio' => 85.00,
                'stock' => 50,
                'categoria' => 'Frutas',
                'unidad_medida' => 'kg',
                'imagen_url' => 'fresas-temporada.jpg',
                'activo' => 1
            ],
            [
                'nombre' => 'Zanahorias Baby',
                'descripcion' => 'Zanahorias baby tiernas y dulces, perfectas para snacks saludables y guarniciones. Rica en vitamina A.',
                'precio' => 35.00,
                'stock' => 120,
                'categoria' => 'Verduras',
                'unidad_medida' => 'kg',
                'imagen_url' => 'zanahorias-baby.jpg',
                'activo' => 1
            ],
            [
                'nombre' => 'Manzanas Red Delicious',
                'descripcion' => 'Manzanas rojas crujientes y dulces, perfectas para snacks y postres saludables. Rica en fibra y antioxidantes.',
                'precio' => 55.00,
                'stock' => 90,
                'categoria' => 'Frutas',
                'unidad_medida' => 'kg',
                'imagen_url' => 'manzanas-red.jpg',
                'activo' => 1
            ]
        ];
        
        // Verificar si ya existen productos duplicados
        echo "<h3>Eliminando productos duplicados existentes:</h3>\n";
        $stmtDelete = $pdo->prepare("DELETE FROM producto WHERE id_usuario = ? AND nombre = ?");
        $eliminados = 0;
        
        foreach ($productos as $producto) {
            $stmtDelete->execute([46, $producto['nombre']]);
            if ($stmtDelete->rowCount() > 0) {
                echo "🗑️ Eliminados duplicados de: " . $producto['nombre'] . " (" . $stmtDelete->rowCount() . " registros)<br>\n";
                $eliminados += $stmtDelete->rowCount();
            }
        }
        echo "<strong>Total duplicados eliminados: " . $eliminados . "</strong><br><br>\n";
        
        // Ahora insertar los productos únicos
        echo "<h3>Insertando productos únicos:</h3>\n";
        $stmt = $pdo->prepare("
            INSERT INTO producto (
                id_usuario, nombre, descripcion, precio, stock, categoria, 
                unidad_medida, imagen_url, activo, fecha_publicacion
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ");
        
        $insertados = 0;
        foreach ($productos as $producto) {
            try {
                $stmt->execute([
                    46,
                    $producto['nombre'],
                    $producto['descripcion'],
                    $producto['precio'],
                    $producto['stock'],
                    $producto['categoria'],
                    $producto['unidad_medida'],
                    $producto['imagen_url'],
                    $producto['activo']
                ]);
                echo "✅ Producto insertado: " . $producto['nombre'] . "<br>\n";
                $insertados++;
            } catch (Exception $e) {
                echo "❌ Error insertando " . $producto['nombre'] . ": " . $e->getMessage() . "<br>\n";
            }
        }
        
        echo "<br><strong>Total productos insertados: " . $insertados . "</strong><br><br>\n";
        
        // Verificar productos después de la inserción
        echo "<h2>Verificación final:</h2>\n";
        $stmt = $pdo->prepare("
            SELECT nombre, precio, stock, categoria, activo 
            FROM producto 
            WHERE id_usuario = ? 
            ORDER BY fecha_publicacion DESC
        ");
        $stmt->execute([46]);
        $productosUsuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($productosUsuario) {
            echo "<table border='1' style='border-collapse: collapse;'>\n";
            echo "<tr><th>Nombre</th><th>Precio</th><th>Stock</th><th>Categoría</th><th>Activo</th></tr>\n";
            foreach ($productosUsuario as $prod) {
                echo "<tr>";
                echo "<td>" . $prod['nombre'] . "</td>";
                echo "<td>$" . number_format($prod['precio'], 2) . "</td>";
                echo "<td>" . $prod['stock'] . "</td>";
                echo "<td>" . $prod['categoria'] . "</td>";
                echo "<td>" . ($prod['activo'] ? 'Sí' : 'No') . "</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
        }
        
    } else {
        echo "❌ Usuario con ID 46 no encontrado.<br>\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>

<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    table { margin: 10px 0; }
    th, td { padding: 8px 12px; text-align: left; }
    th { background: #f0f0f0; }
    h2 { color: #2E7D32; margin-top: 20px; }
</style>