<?php
/**
 * API para el manejo del carrito de compras
 * AgroConecta - Endpoints AJAX para operaciones del carrito
 */

// Configuración de headers para API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Iniciar sesión
session_start();

// Incluir configuración de base de datos
require_once '../../config/database.php';

// Función para enviar respuesta JSON
function sendResponse($success, $message = '', $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Usuario no autenticado', null, 401);
}

$user_id = $_SESSION['user_id'];
$user_tipo = $_SESSION['user_tipo'] ?? $_SESSION['tipo'] ?? 'cliente';

// Solo los clientes pueden agregar al carrito
if ($user_tipo !== 'cliente') {
    sendResponse(false, 'Solo los clientes pueden agregar productos al carrito', null, 403);
}

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Método no permitido', null, 405);
}

// Leer datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    sendResponse(false, 'Datos JSON inválidos', null, 400);
}

$action = $data['action'] ?? '';

try {
    $pdo = getDBConnection();
    
    switch ($action) {
        case 'agregar':
            $producto_id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
            $cantidad = filter_var($data['cantidad'] ?? 1, FILTER_VALIDATE_INT);
            
            if (!$producto_id || $cantidad <= 0) {
                sendResponse(false, 'ID de producto o cantidad inválidos');
            }
            
            // Verificar que el producto existe y está activo
            $stmt = $pdo->prepare("
                SELECT p.*, u.nombre as vendedor_nombre 
                FROM producto p 
                JOIN usuario u ON p.id_usuario = u.id_usuario 
                WHERE p.id_producto = ? AND p.activo = 1
            ");
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$producto) {
                sendResponse(false, 'Producto no encontrado o no disponible');
            }
            
            // Verificar stock disponible
            if ($producto['stock'] < $cantidad) {
                sendResponse(false, 'Stock insuficiente. Disponible: ' . $producto['stock']);
            }
            
            // Verificar si ya existe en el carrito
            $stmt = $pdo->prepare("
                SELECT id_carrito, cantidad 
                FROM carrito 
                WHERE id_usuario = ? AND id_producto = ?
            ");
            $stmt->execute([$user_id, $producto_id]);
            $item_existente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($item_existente) {
                // Actualizar cantidad existente
                $nueva_cantidad = $item_existente['cantidad'] + $cantidad;
                
                // Verificar stock para la nueva cantidad
                if ($producto['stock'] < $nueva_cantidad) {
                    sendResponse(false, 'Stock insuficiente. En carrito: ' . $item_existente['cantidad'] . ', Disponible: ' . $producto['stock']);
                }
                
                $stmt = $pdo->prepare("
                    UPDATE carrito 
                    SET cantidad = ?, fecha_actualizacion = CURRENT_TIMESTAMP 
                    WHERE id_carrito = ?
                ");
                $stmt->execute([$nueva_cantidad, $item_existente['id_carrito']]);
                
                sendResponse(true, 'Cantidad actualizada en el carrito', [
                    'producto' => $producto['nombre'],
                    'cantidad_total' => $nueva_cantidad,
                    'vendedor' => $producto['vendedor_nombre']
                ]);
            } else {
                // Agregar nuevo item al carrito
                $stmt = $pdo->prepare("
                    INSERT INTO carrito (id_usuario, id_producto, cantidad) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$user_id, $producto_id, $cantidad]);
                
                sendResponse(true, 'Producto agregado al carrito', [
                    'producto' => $producto['nombre'],
                    'cantidad' => $cantidad,
                    'vendedor' => $producto['vendedor_nombre']
                ]);
            }
            break;
            
        case 'actualizar':
            $carrito_id = filter_var($data['carrito_id'] ?? 0, FILTER_VALIDATE_INT);
            $cantidad = filter_var($data['cantidad'] ?? 1, FILTER_VALIDATE_INT);
            
            if (!$carrito_id || $cantidad <= 0) {
                sendResponse(false, 'ID de carrito o cantidad inválidos');
            }
            
            // Verificar que el item pertenece al usuario
            $stmt = $pdo->prepare("
                SELECT c.*, p.stock, p.nombre 
                FROM carrito c 
                JOIN producto p ON c.id_producto = p.id_producto 
                WHERE c.id_carrito = ? AND c.id_usuario = ?
            ");
            $stmt->execute([$carrito_id, $user_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$item) {
                sendResponse(false, 'Item de carrito no encontrado');
            }
            
            // Verificar stock
            if ($item['stock'] < $cantidad) {
                sendResponse(false, 'Stock insuficiente. Disponible: ' . $item['stock']);
            }
            
            // Actualizar cantidad
            $stmt = $pdo->prepare("
                UPDATE carrito 
                SET cantidad = ?, fecha_actualizacion = CURRENT_TIMESTAMP 
                WHERE id_carrito = ?
            ");
            $stmt->execute([$cantidad, $carrito_id]);
            
            sendResponse(true, 'Cantidad actualizada', [
                'producto' => $item['nombre'],
                'cantidad' => $cantidad
            ]);
            break;
            
        case 'eliminar':
            $carrito_id = filter_var($data['carrito_id'] ?? 0, FILTER_VALIDATE_INT);
            
            if (!$carrito_id) {
                sendResponse(false, 'ID de carrito inválido');
            }
            
            // Verificar que el item pertenece al usuario
            $stmt = $pdo->prepare("
                SELECT c.*, p.nombre 
                FROM carrito c 
                JOIN producto p ON c.id_producto = p.id_producto 
                WHERE c.id_carrito = ? AND c.id_usuario = ?
            ");
            $stmt->execute([$carrito_id, $user_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$item) {
                sendResponse(false, 'Item de carrito no encontrado');
            }
            
            // Eliminar del carrito
            $stmt = $pdo->prepare("DELETE FROM carrito WHERE id_carrito = ?");
            $stmt->execute([$carrito_id]);
            
            sendResponse(true, 'Producto eliminado del carrito', [
                'producto' => $item['nombre']
            ]);
            break;
            
        case 'obtener':
            // Obtener items del carrito
            $stmt = $pdo->prepare("
                SELECT c.*, p.nombre, p.precio, p.stock, p.imagen_url, p.unidad_medida,
                       u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
                FROM carrito c 
                JOIN producto p ON c.id_producto = p.id_producto 
                JOIN usuario u ON p.id_usuario = u.id_usuario
                WHERE c.id_usuario = ? AND p.activo = 1
                ORDER BY c.fecha_actualizacion DESC
            ");
            $stmt->execute([$user_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total = 0;
            foreach ($items as &$item) {
                $item['subtotal'] = $item['precio'] * $item['cantidad'];
                $total += $item['subtotal'];
            }
            
            sendResponse(true, 'Carrito obtenido exitosamente', [
                'items' => $items,
                'total_items' => count($items),
                'total_productos' => array_sum(array_column($items, 'cantidad')),
                'total_precio' => $total
            ]);
            break;
            
        case 'limpiar':
            // Limpiar todo el carrito
            $stmt = $pdo->prepare("DELETE FROM carrito WHERE id_usuario = ?");
            $stmt->execute([$user_id]);
            
            sendResponse(true, 'Carrito vaciado exitosamente');
            break;
            
        default:
            sendResponse(false, 'Acción no válida', null, 400);
            break;
    }
    
} catch (PDOException $e) {
    error_log("Error en API carrito: " . $e->getMessage());
    sendResponse(false, 'Error interno del servidor', null, 500);
} catch (Exception $e) {
    error_log("Error general en API carrito: " . $e->getMessage());
    sendResponse(false, 'Error interno del servidor', null, 500);
}
?>