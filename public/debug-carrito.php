<?php
session_start();

echo "<h3>Información de Sesión</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Verificar conexión a base de datos
try {
    require_once '../config/database.php';
    $pdo = getDBConnection();
    
    echo "<h3>Conexión a Base de Datos</h3>";
    echo "Conectado correctamente<br>";
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Verificar carrito
        $stmt = $pdo->prepare("SELECT COUNT(*) as items FROM carrito WHERE id_usuario = ?");
        $stmt->execute([$user_id]);
        $carrito_count = $stmt->fetchColumn();
        
        echo "<h3>Carrito en Base de Datos</h3>";
        echo "Items en carrito: " . $carrito_count . "<br>";
        
        if ($carrito_count > 0) {
            $stmt = $pdo->prepare("
                SELECT c.*, p.nombre, p.precio 
                FROM carrito c 
                JOIN producto p ON c.id_producto = p.id_producto 
                WHERE c.id_usuario = ?
            ");
            $stmt->execute([$user_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h4>Detalles del Carrito:</h4>";
            echo "<pre>";
            print_r($items);
            echo "</pre>";
        }
        
        // Verificar direcciones
        $stmt = $pdo->prepare("SELECT COUNT(*) as direcciones FROM direccion WHERE id_usuario = ? AND activa = 1");
        $stmt->execute([$user_id]);
        $direcciones_count = $stmt->fetchColumn();
        
        echo "<h3>Direcciones</h3>";
        echo "Direcciones activas: " . $direcciones_count . "<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>Error de Base de Datos</h3>";
    echo "Error: " . $e->getMessage();
}
?>