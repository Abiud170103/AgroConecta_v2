<?php
/**
 * API para gestión de productos
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';
require_once '../../core/Database.php';
require_once '../../core/SessionManager.php';

SessionManager::startSecureSession();

// Verificar autenticación
if (!SessionManager::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$userData = SessionManager::getUserData();
$user = [
    'id' => $userData['id'] ?? $_SESSION['user_id'],
    'nombre' => $userData['nombre'] ?? $_SESSION['user_nombre'] ?? 'Usuario',
    'tipo' => $userData['tipo'] ?? $_SESSION['user_tipo'] ?? 'cliente'
];

// Solo vendedores y admin pueden gestionar productos
if ($user['tipo'] !== 'vendedor' && $user['tipo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sin permisos para gestionar productos']);
    exit;
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            if ($action === 'obtener') {
                obtenerProducto($pdo, $user);
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'crear':
                    crearProducto($pdo, $user);
                    break;
                case 'actualizar':
                    actualizarProducto($pdo, $user);
                    break;
                case 'eliminar':
                    eliminarProducto($pdo, $user);
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
    
} catch (Exception $e) {
    error_log("Error en API productos: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

function obtenerProducto($pdo, $user) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
        return;
    }
    
    $query = "
        SELECT 
            p.id_producto as id,
            p.nombre,
            p.descripcion,
            p.precio,
            p.stock,
            p.categoria,
            p.unidad_medida,
            p.imagen_url as imagen,
            p.activo,
            p.fecha_publicacion as fecha_creacion,
            u.nombre as vendedor_nombre
        FROM producto p
        INNER JOIN usuario u ON p.id_usuario = u.id_usuario
        WHERE p.id_producto = ?
    ";
    
    $params = [$id];
    
    // Si es vendedor, solo puede ver sus productos
    if ($user['tipo'] === 'vendedor') {
        $query .= " AND p.id_usuario = ?";
        $params[] = $user['id'];
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($producto) {
        // Transformar datos
        $productoTransformado = [
            'id' => $producto['id'],
            'nombre' => $producto['nombre'],
            'descripcion' => $producto['descripcion'],
            'precio' => floatval($producto['precio']),
            'stock' => intval($producto['stock']),
            'categoria' => $producto['categoria'],
            'unidad_medida' => $producto['unidad_medida'],
            'imagen' => $producto['imagen'] ?: 'default-product.jpg',
            'estado' => $producto['activo'] == 1 ? 'activo' : 'inactivo',
            'fecha_creacion' => $producto['fecha_creacion'],
            'vendedor' => $producto['vendedor_nombre']
        ];
        
        echo json_encode(['success' => true, 'producto' => $productoTransformado]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    }
}

function crearProducto($pdo, $user) {
    // Solo vendedores pueden crear productos
    if ($user['tipo'] !== 'vendedor') {
        echo json_encode(['success' => false, 'message' => 'Solo vendedores pueden crear productos']);
        return;
    }
    
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $categoria = trim($_POST['categoria'] ?? '');
    $unidad_medida = trim($_POST['unidad_medida'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre) || empty($descripcion) || empty($categoria) || empty($unidad_medida)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben completarse']);
        return;
    }
    
    if ($precio <= 0 || $stock < 0) {
        echo json_encode(['success' => false, 'message' => 'Precio debe ser mayor a 0 y stock no negativo']);
        return;
    }
    
    // Manejo de imagen
    $imagen_url = 'default-product.jpg';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_url = manejarSubidaImagen($_FILES['imagen']);
        if (!$imagen_url) {
            echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
            return;
        }
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO producto (
            id_usuario, nombre, descripcion, precio, stock, categoria,
            unidad_medida, imagen_url, activo, fecha_publicacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    if ($stmt->execute([
        $user['id'], $nombre, $descripcion, $precio, $stock,
        $categoria, $unidad_medida, $imagen_url, $activo
    ])) {
        echo json_encode(['success' => true, 'message' => 'Producto creado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear el producto']);
    }
}

function actualizarProducto($pdo, $user) {
    $id_producto = intval($_POST['id_producto'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $categoria = trim($_POST['categoria'] ?? '');
    $unidad_medida = trim($_POST['unidad_medida'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if (!$id_producto) {
        echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
        return;
    }
    
    // Verificar que el producto pertenece al usuario (si es vendedor)
    if ($user['tipo'] === 'vendedor') {
        $stmt = $pdo->prepare("SELECT id_usuario FROM producto WHERE id_producto = ?");
        $stmt->execute([$id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto || $producto['id_usuario'] != $user['id']) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para editar este producto']);
            return;
        }
    }
    
    // Validaciones
    if (empty($nombre) || empty($descripcion) || empty($categoria) || empty($unidad_medida)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben completarse']);
        return;
    }
    
    if ($precio <= 0 || $stock < 0) {
        echo json_encode(['success' => false, 'message' => 'Precio debe ser mayor a 0 y stock no negativo']);
        return;
    }
    
    // Preparar query base
    $query = "
        UPDATE producto SET 
            nombre = ?, descripcion = ?, precio = ?, stock = ?, 
            categoria = ?, unidad_medida = ?, activo = ?
    ";
    $params = [$nombre, $descripcion, $precio, $stock, $categoria, $unidad_medida, $activo];
    
    // Manejar imagen si se subió una nueva
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_url = manejarSubidaImagen($_FILES['imagen']);
        if ($imagen_url) {
            $query .= ", imagen_url = ?";
            $params[] = $imagen_url;
        }
    }
    
    $query .= " WHERE id_producto = ?";
    $params[] = $id_producto;
    
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto']);
    }
}

function eliminarProducto($pdo, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id_producto = intval($input['id'] ?? 0);
    
    if (!$id_producto) {
        echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
        return;
    }
    
    // Verificar que el producto pertenece al usuario (si es vendedor)
    if ($user['tipo'] === 'vendedor') {
        $stmt = $pdo->prepare("SELECT id_usuario FROM producto WHERE id_producto = ?");
        $stmt->execute([$id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto || $producto['id_usuario'] != $user['id']) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para eliminar este producto']);
            return;
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM producto WHERE id_producto = ?");
    
    if ($stmt->execute([$id_producto])) {
        echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto']);
    }
}

function manejarSubidaImagen($archivo) {
    $uploadDir = '../uploads/productos/';
    
    // Crear directorio si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validar tipo de archivo
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($archivo['type'], $tiposPermitidos)) {
        return false;
    }
    
    // Validar tamaño (máximo 2MB)
    if ($archivo['size'] > 2 * 1024 * 1024) {
        return false;
    }
    
    // Generar nombre único
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombreArchivo = 'producto_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $rutaCompleta = $uploadDir . $nombreArchivo;
    
    // Mover archivo
    if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        return 'uploads/productos/' . $nombreArchivo;
    }
    
    return false;
}
?>