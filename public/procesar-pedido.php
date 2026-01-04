<?php
session_start();
require_once '../config/database.php';

// Verificar autenticación
if (!isset($_SESSION['user_id']) || 
    (!isset($_SESSION['user_tipo']) && !isset($_SESSION['tipo']))) {
    header('Location: login.php');
    exit();
}

$user_tipo = $_SESSION['user_tipo'] ?? $_SESSION['tipo'] ?? 'cliente';
if ($user_tipo !== 'cliente') {
    header('Location: dashboard.php');
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carrito.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => '', 'pedido_id' => null];

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // Validar datos del formulario
    $direccion_id = filter_input(INPUT_POST, 'direccion_entrega', FILTER_VALIDATE_INT);
    $metodo_pago = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_STRING);
    $notas_cliente = filter_input(INPUT_POST, 'notas_cliente', FILTER_SANITIZE_STRING);
    
    if (!$direccion_id || !$metodo_pago) {
        throw new Exception('Datos incompletos. Por favor verifica la información.');
    }
    
    // Validar métodos de pago permitidos
    $metodos_validos = ['mercado_pago', 'transferencia', 'efectivo', 'tarjeta'];
    if (!in_array($metodo_pago, $metodos_validos)) {
        throw new Exception('Método de pago no válido.');
    }
    
    // Verificar que la dirección pertenece al usuario
    $stmt = $pdo->prepare("SELECT id_direccion FROM direccion WHERE id_direccion = ? AND id_usuario = ? AND activa = 1");
    $stmt->execute([$direccion_id, $user_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Dirección de entrega no válida.');
    }
    
    // Obtener items del carrito
    $stmt = $pdo->prepare("
        SELECT c.*, p.nombre, p.precio, p.stock 
        FROM carrito c 
        JOIN producto p ON c.id_producto = p.id_producto 
        WHERE c.id_usuario = ? AND p.activo = 1
    ");
    $stmt->execute([$user_id]);
    $items_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($items_carrito)) {
        throw new Exception('Tu carrito está vacío.');
    }
    
    // Verificar stock disponible
    foreach ($items_carrito as $item) {
        if ($item['cantidad'] > $item['stock']) {
            throw new Exception('Stock insuficiente para el producto: ' . $item['nombre']);
        }
    }
    
    // Calcular total
    $total = 0;
    foreach ($items_carrito as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    
    if ($total <= 0) {
        throw new Exception('Error en el cálculo del total.');
    }
    
    // Generar número de pedido único
    $numero_pedido = 'AGC-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    
    // Verificar que el número de pedido no exista
    $stmt = $pdo->prepare("SELECT id_pedido FROM pedido WHERE numero_pedido = ?");
    $stmt->execute([$numero_pedido]);
    while ($stmt->fetch()) {
        $numero_pedido = 'AGC-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT id_pedido FROM pedido WHERE numero_pedido = ?");
        $stmt->execute([$numero_pedido]);
    }
    
    // Crear el pedido
    $stmt = $pdo->prepare("
        INSERT INTO pedido (id_usuario, id_direccion, numero_pedido, total, estado, notas_cliente) 
        VALUES (?, ?, ?, ?, 'pendiente', ?)
    ");
    $stmt->execute([$user_id, $direccion_id, $numero_pedido, $total, $notas_cliente]);
    $pedido_id = $pdo->lastInsertId();
    
    // Crear detalles del pedido
    foreach ($items_carrito as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        
        $stmt = $pdo->prepare("
            INSERT INTO detallepedido (id_pedido, id_producto, nombre_producto, precio_unitario, cantidad, subtotal) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $pedido_id,
            $item['id_producto'],
            $item['nombre'],
            $item['precio'],
            $item['cantidad'],
            $subtotal
        ]);
        
        // Actualizar stock del producto
        $stmt = $pdo->prepare("UPDATE producto SET stock = stock - ? WHERE id_producto = ?");
        $stmt->execute([$item['cantidad'], $item['id_producto']]);
    }
    
    // Crear registro de pago
    $stmt = $pdo->prepare("
        INSERT INTO pago (id_pedido, metodo, monto, estado, transaccion_id) 
        VALUES (?, ?, ?, 'pendiente', ?)
    ");
    $transaccion_id = 'TXN-' . $pedido_id . '-' . date('YmdHis');
    $stmt->execute([$pedido_id, $metodo_pago, $total, $transaccion_id]);
    $pago_id = $pdo->lastInsertId();
    
    // Limpiar carrito del usuario
    $stmt = $pdo->prepare("DELETE FROM carrito WHERE id_usuario = ?");
    $stmt->execute([$user_id]);
    
    // Crear notificaciones
    // Notificación para el cliente
    $stmt = $pdo->prepare("
        INSERT INTO notificacion (id_usuario, tipo, titulo, mensaje) 
        VALUES (?, 'pedido', 'Pedido confirmado', ?)
    ");
    $mensaje_cliente = "Tu pedido #{$numero_pedido} ha sido confirmado por un total de $" . number_format($total, 2) . ". Te notificaremos cuando sea procesado.";
    $stmt->execute([$user_id, $mensaje_cliente]);
    
    // Notificaciones para los vendedores
    $vendedores = [];
    foreach ($items_carrito as $item) {
        $stmt = $pdo->prepare("SELECT id_usuario FROM producto WHERE id_producto = ?");
        $stmt->execute([$item['id_producto']]);
        $vendedor_id = $stmt->fetchColumn();
        if ($vendedor_id && !in_array($vendedor_id, $vendedores)) {
            $vendedores[] = $vendedor_id;
            
            $stmt = $pdo->prepare("
                INSERT INTO notificacion (id_usuario, tipo, titulo, mensaje) 
                VALUES (?, 'pedido', 'Nuevo pedido recibido', ?)
            ");
            $mensaje_vendedor = "Has recibido un nuevo pedido #{$numero_pedido}. Revisa los detalles en tu panel de vendedor.";
            $stmt->execute([$vendedor_id, $mensaje_vendedor]);
        }
    }
    
    // Crear ticket/recibo
    $detalles_ticket = [
        'numero_pedido' => $numero_pedido,
        'fecha' => date('Y-m-d H:i:s'),
        'total' => $total,
        'metodo_pago' => $metodo_pago,
        'items' => $items_carrito
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO ticket (id_pedido, codigo, detalles) 
        VALUES (?, ?, ?)
    ");
    $codigo_ticket = 'TKT-' . date('YmdHis') . '-' . $pedido_id;
    $stmt->execute([$pedido_id, $codigo_ticket, json_encode($detalles_ticket)]);
    
    $pdo->commit();
    
    $response['success'] = true;
    $response['message'] = 'Pedido confirmado exitosamente';
    $response['pedido_id'] = $pedido_id;
    $response['numero_pedido'] = $numero_pedido;
    $response['redirect'] = 'pedido-confirmado.php?id=' . $pedido_id;
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error al procesar pedido: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

// Si es una petición AJAX, devolver JSON
if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Redirigir según el resultado
if ($response['success']) {
    $_SESSION['pedido_confirmado'] = true;
    $_SESSION['numero_pedido'] = $response['numero_pedido'];
    header('Location: ' . $response['redirect']);
} else {
    $_SESSION['error_pedido'] = $response['message'];
    header('Location: confirmacion-compra.php');
}
exit();
?>