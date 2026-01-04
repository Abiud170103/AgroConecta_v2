<?php
/**
 * API para el manejo de favoritos
 * AgroConecta - Endpoints AJAX para operaciones de favoritos
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

// Solo los clientes pueden manejar favoritos
if ($user_tipo !== 'cliente') {
    sendResponse(false, 'Solo los clientes pueden manejar favoritos', null, 403);
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
    
    // Crear tabla de favoritos si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS favorito (
            id_favorito INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            id_producto INT NOT NULL,
            fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_product (id_usuario, id_producto),
            FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
            FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    switch ($action) {
        case 'toggle':
            $producto_id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
            
            if (!$producto_id) {
                sendResponse(false, 'ID de producto inválido');
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
            
            // Verificar si ya está en favoritos
            $stmt = $pdo->prepare("
                SELECT id_favorito 
                FROM favorito 
                WHERE id_usuario = ? AND id_producto = ?
            ");
            $stmt->execute([$user_id, $producto_id]);
            $favorito_existente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($favorito_existente) {
                // Eliminar de favoritos
                $stmt = $pdo->prepare("
                    DELETE FROM favorito 
                    WHERE id_usuario = ? AND id_producto = ?
                ");
                $stmt->execute([$user_id, $producto_id]);
                
                sendResponse(true, 'Producto eliminado de favoritos', [
                    'producto' => $producto['nombre'],
                    'vendedor' => $producto['vendedor_nombre'],
                    'es_favorito' => false
                ]);
            } else {
                // Agregar a favoritos
                $stmt = $pdo->prepare("
                    INSERT INTO favorito (id_usuario, id_producto) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$user_id, $producto_id]);
                
                sendResponse(true, 'Producto agregado a favoritos', [
                    'producto' => $producto['nombre'],
                    'vendedor' => $producto['vendedor_nombre'],
                    'es_favorito' => true
                ]);
            }
            break;
            
        case 'agregar':
            $producto_id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
            
            if (!$producto_id) {
                sendResponse(false, 'ID de producto inválido');
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
            
            // Intentar agregar (ignorar si ya existe)
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO favorito (id_usuario, id_producto) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$user_id, $producto_id]);
                
                sendResponse(true, 'Producto agregado a favoritos', [
                    'producto' => $producto['nombre'],
                    'vendedor' => $producto['vendedor_nombre']
                ]);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    sendResponse(true, 'El producto ya estaba en favoritos', [
                        'producto' => $producto['nombre']
                    ]);
                } else {
                    throw $e;
                }
            }
            break;
            
        case 'eliminar':
            $producto_id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
            
            if (!$producto_id) {
                sendResponse(false, 'ID de producto inválido');
            }
            
            // Obtener información del producto antes de eliminar
            $stmt = $pdo->prepare("
                SELECT p.nombre 
                FROM favorito f
                JOIN producto p ON f.id_producto = p.id_producto
                WHERE f.id_usuario = ? AND f.id_producto = ?
            ");
            $stmt->execute([$user_id, $producto_id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$producto) {
                sendResponse(false, 'Favorito no encontrado');
            }
            
            // Eliminar de favoritos
            $stmt = $pdo->prepare("
                DELETE FROM favorito 
                WHERE id_usuario = ? AND id_producto = ?
            ");
            $stmt->execute([$user_id, $producto_id]);
            
            if ($stmt->rowCount() > 0) {
                sendResponse(true, 'Producto eliminado de favoritos', [
                    'producto' => $producto['nombre']
                ]);
            } else {
                sendResponse(false, 'El producto no estaba en favoritos');
            }
            break;
            
        case 'obtener':
            // Obtener lista de favoritos
            $stmt = $pdo->prepare("
                SELECT f.*, p.nombre, p.descripcion, p.precio, p.stock, p.imagen_url, 
                       p.unidad_medida, p.activo, p.categoria,
                       u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
                FROM favorito f 
                JOIN producto p ON f.id_producto = p.id_producto 
                JOIN usuario u ON p.id_usuario = u.id_usuario
                WHERE f.id_usuario = ?
                ORDER BY f.fecha_agregado DESC
            ");
            $stmt->execute([$user_id]);
            $favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Separar por disponibilidad
            $disponibles = [];
            $no_disponibles = [];
            
            foreach ($favoritos as $favorito) {
                if ($favorito['activo'] && $favorito['stock'] > 0) {
                    $disponibles[] = $favorito;
                } else {
                    $no_disponibles[] = $favorito;
                }
            }
            
            sendResponse(true, 'Favoritos obtenidos exitosamente', [
                'favoritos' => $favoritos,
                'total' => count($favoritos),
                'disponibles' => $disponibles,
                'no_disponibles' => $no_disponibles,
                'total_disponibles' => count($disponibles),
                'total_no_disponibles' => count($no_disponibles)
            ]);
            break;
            
        case 'verificar':
            $producto_id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
            
            if (!$producto_id) {
                sendResponse(false, 'ID de producto inválido');
            }
            
            // Verificar si está en favoritos
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as es_favorito
                FROM favorito 
                WHERE id_usuario = ? AND id_producto = ?
            ");
            $stmt->execute([$user_id, $producto_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse(true, 'Estado verificado', [
                'producto_id' => $producto_id,
                'es_favorito' => $result['es_favorito'] > 0
            ]);
            break;
            
        case 'limpiar':
            // Eliminar todos los favoritos del usuario
            $stmt = $pdo->prepare("DELETE FROM favorito WHERE id_usuario = ?");
            $stmt->execute([$user_id]);
            
            sendResponse(true, 'Todos los favoritos han sido eliminados', [
                'eliminados' => $stmt->rowCount()
            ]);
            break;
            
        default:
            sendResponse(false, 'Acción no válida', null, 400);
            break;
    }
    
} catch (PDOException $e) {
    error_log("Error en API favoritos: " . $e->getMessage());
    sendResponse(false, 'Error interno del servidor', null, 500);
} catch (Exception $e) {
    error_log("Error general en API favoritos: " . $e->getMessage());
    sendResponse(false, 'Error interno del servidor', null, 500);
}
?>