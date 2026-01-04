<?php
session_start();
require_once '../config/database.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo "Error: No hay sesión activa. <a href='login.php'>Iniciar sesión</a>";
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    echo "<h2>Agregando Items de Prueba al Carrito</h2>";
    
    // Verificar productos disponibles
    $stmt = $pdo->prepare("SELECT * FROM producto WHERE activo = 1 LIMIT 3");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($productos)) {
        echo "No hay productos activos en la base de datos.<br>";
        echo "Necesitas tener productos en la tabla 'producto' para probar el carrito.<br>";
        exit();
    }
    
    echo "<h3>Productos disponibles:</h3>";
    foreach ($productos as $producto) {
        echo "- ID: {$producto['id_producto']}, Nombre: {$producto['nombre']}, Precio: ${$producto['precio']}<br>";
    }
    
    // Limpiar carrito anterior
    $stmt = $pdo->prepare("DELETE FROM carrito WHERE id_usuario = ?");
    $stmt->execute([$user_id]);
    echo "<br>Carrito limpio.<br>";
    
    // Agregar productos al carrito
    foreach ($productos as $index => $producto) {
        $cantidad = $index + 1; // 1, 2, 3 items respectivamente
        
        $stmt = $pdo->prepare("
            INSERT INTO carrito (id_usuario, id_producto, cantidad) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            cantidad = VALUES(cantidad), 
            fecha_actualizacion = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([$user_id, $producto['id_producto'], $cantidad]);
        
        echo "Agregado al carrito: {$producto['nombre']} (Cantidad: $cantidad)<br>";
    }
    
    // Verificar carrito
    $stmt = $pdo->prepare("
        SELECT c.*, p.nombre, p.precio 
        FROM carrito c 
        JOIN producto p ON c.id_producto = p.id_producto 
        WHERE c.id_usuario = ?
    ");
    $stmt->execute([$user_id]);
    $items_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Carrito actual:</h3>";
    $total = 0;
    foreach ($items_carrito as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        $total += $subtotal;
        echo "- {$item['nombre']}: {$item['cantidad']} x ${$item['precio']} = ${$subtotal}<br>";
    }
    
    echo "<strong>Total: $" . number_format($total, 2) . "</strong><br><br>";
    
    echo "<a href='carrito.php' class='btn btn-primary'>Ver Carrito</a> ";
    echo "<a href='confirmacion-compra.php' class='btn btn-success'>Ir a Confirmación</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>