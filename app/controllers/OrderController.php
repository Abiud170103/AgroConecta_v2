<?php
/**
 * OrderController - Controlador de pedidos
 * Maneja el proceso de checkout, creación y gestión de pedidos
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'BaseController.php';
require_once APP_PATH . '/models/Pedido.php';
require_once APP_PATH . '/models/DetallePedido.php';
require_once APP_PATH . '/models/Carrito.php';
require_once APP_PATH . '/models/Producto.php';
require_once APP_PATH . '/models/Usuario.php';
require_once APP_PATH . '/models/Notificacion.php';

class OrderController extends BaseController {
    
    /**
     * Lista de pedidos del usuario
     */
    public function index() {
        if (!$this->requireAuth()) return;
        
        try {
            $pedidoModel = new Pedido();
            $userId = $this->getCurrentUserId();
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 10;
            $estado = $this->sanitizeInput($_GET['estado'] ?? '');
            
            $filtros = ['id_usuario' => $userId];
            if ($estado) $filtros['estado'] = $estado;
            
            $offset = ($page - 1) * $perPage;
            $pedidos = $pedidoModel->getPedidosPaginados($filtros, $perPage, $offset);
            $totalPedidos = $pedidoModel->countPedidos($filtros);
            $totalPaginas = ceil($totalPedidos / $perPage);
            
            // Estados disponibles para filtro
            $estados = ['pendiente', 'confirmado', 'preparando', 'enviado', 'entregado', 'cancelado'];
            
            $this->setViewData('pageTitle', 'Mis Pedidos');
            $this->setViewData('pedidos', $pedidos);
            $this->setViewData('estados', $estados);
            $this->setViewData('estadoActual', $estado);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'totalItems' => $totalPedidos
            ]);
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Mi Cuenta', 'url' => '/usuario/perfil'],
                ['name' => 'Mis Pedidos', 'url' => '/pedidos']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in OrderController::index: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar los pedidos');
            $this->setViewData('pedidos', []);
        }
        
        $this->render('orders/index');
    }
    
    /**
     * Ver detalles de un pedido específico
     */
    public function show($id) {
        if (!$this->requireAuth()) return;
        
        $id = intval($id);
        if ($id <= 0) {
            $this->setFlashMessage('error', 'Pedido no encontrado');
            $this->redirect('/pedidos');
            return;
        }
        
        try {
            $pedidoModel = new Pedido();
            $detallePedidoModel = new DetallePedido();
            $userId = $this->getCurrentUserId();
            
            // Obtener pedido y verificar que pertenece al usuario
            $pedido = $pedidoModel->find($id);
            if (!$pedido || $pedido['id_usuario'] != $userId) {
                $this->setFlashMessage('error', 'Pedido no encontrado o no tienes acceso');
                $this->redirect('/pedidos');
                return;
            }
            
            // Obtener detalles del pedido
            $detalles = $detallePedidoModel->getDetallesByPedido($id);
            
            // Calcular totales
            $subtotal = 0;
            foreach ($detalles as $detalle) {
                $subtotal += $detalle['precio_unitario'] * $detalle['cantidad'];
            }
            
            $this->setViewData('pageTitle', 'Pedido #' . $pedido['numero_pedido']);
            $this->setViewData('pedido', $pedido);
            $this->setViewData('detalles', $detalles);
            $this->setViewData('subtotal', $subtotal);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Mis Pedidos', 'url' => '/pedidos'],
                ['name' => 'Pedido #' . $pedido['numero_pedido'], 'url' => '/pedidos/' . $id]
            ]);
            
        } catch (Exception $e) {
            error_log("Error in OrderController::show: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar el pedido');
            $this->redirect('/pedidos');
        }
        
        $this->render('orders/show');
    }
    
    /**
     * Página de checkout
     */
    public function checkout() {
        if (!$this->requireAuth()) return;
        
        try {
            $carritoModel = new CarritoCompra();
            $productoModel = new Producto();
            $userId = $this->getCurrentUserId();
            $currentUser = $this->getCurrentUser();
            
            // Verificar que hay items en el carrito
            $items = $carritoModel->getItemsCarrito($userId);
            if (empty($items)) {
                $this->setFlashMessage('error', 'Tu carrito está vacío');
                $this->redirect('/carrito');
                return;
            }
            
            // Verificar stock y calcular totales
            $stockErrors = [];
            $subtotal = 0;
            
            foreach ($items as &$item) {
                $producto = $productoModel->find($item['id_producto']);
                if (!$producto || !$producto['activo']) {
                    $stockErrors[] = "El producto '{$item['nombre']}' ya no está disponible";
                    continue;
                }
                
                if ($producto['stock'] < $item['cantidad']) {
                    $stockErrors[] = "Stock insuficiente para '{$item['nombre']}'. Disponible: {$producto['stock']}";
                    continue;
                }
                
                $itemTotal = $item['precio'] * $item['cantidad'];
                $item['total'] = $itemTotal;
                $subtotal += $itemTotal;
            }
            
            if (!empty($stockErrors)) {
                $_SESSION['checkout_errors'] = $stockErrors;
                $this->setFlashMessage('error', 'Hay problemas con algunos productos en tu carrito');
                $this->redirect('/carrito');
                return;
            }
            
            // Calcular envío
            $costoEnvio = $this->calculateShipping($subtotal, $currentUser);
            $total = $subtotal + $costoEnvio;
            
            // Métodos de pago disponibles
            $metodosPago = [
                'tarjeta' => 'Tarjeta de Crédito/Débito',
                'paypal' => 'PayPal',
                'mercado_pago' => 'Mercado Pago',
                'transferencia' => 'Transferencia Bancaria',
                'efectivo' => 'Pago en Efectivo'
            ];
            
            // Opciones de entrega
            $opcionesEntrega = [
                'domicilio' => 'Entrega a domicilio',
                'recoger' => 'Recoger en punto de venta'
            ];
            
            $this->setViewData('pageTitle', 'Finalizar Compra');
            $this->setViewData('items', $items);
            $this->setViewData('subtotal', $subtotal);
            $this->setViewData('costoEnvio', $costoEnvio);
            $this->setViewData('total', $total);
            $this->setViewData('metodosPago', $metodosPago);
            $this->setViewData('opcionesEntrega', $opcionesEntrega);
            $this->setViewData('usuario', $currentUser);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('errors', $_SESSION['checkout_errors'] ?? []);
            $this->setViewData('oldData', $_SESSION['checkout_data'] ?? []);
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Carrito', 'url' => '/carrito'],
                ['name' => 'Checkout', 'url' => '/pedidos/checkout']
            ]);
            
            // Limpiar errores de sesión
            unset($_SESSION['checkout_errors'], $_SESSION['checkout_data']);
            
        } catch (Exception $e) {
            error_log("Error in OrderController::checkout: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar el checkout');
            $this->redirect('/carrito');
            return;
        }
        
        $this->render('orders/checkout');
    }
    
    /**
     * Procesar el pedido
     */
    public function processOrder() {
        if (!$this->requireAuth()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pedidos/checkout');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $data = $this->sanitizeInput([
            'direccion_envio' => $_POST['direccion_envio'] ?? '',
            'ciudad' => $_POST['ciudad'] ?? '',
            'codigo_postal' => $_POST['codigo_postal'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'metodo_pago' => $_POST['metodo_pago'] ?? '',
            'tipo_entrega' => $_POST['tipo_entrega'] ?? '',
            'comentarios' => $_POST['comentarios'] ?? ''
        ]);
        
        // Validaciones
        $errors = $this->validateOrderData($data);
        
        if (!empty($errors)) {
            $_SESSION['checkout_errors'] = $errors;
            $_SESSION['checkout_data'] = $data;
            $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            $this->redirect('/pedidos/checkout');
            return;
        }
        
        try {
            $carritoModel = new CarritoCompra();
            $pedidoModel = new Pedido();
            $detallePedidoModel = new DetallePedido();
            $productoModel = new Producto();
            $notificacionModel = new Notificacion();
            $userId = $this->getCurrentUserId();
            
            // Obtener items del carrito
            $items = $carritoModel->getItemsCarrito($userId);
            if (empty($items)) {
                $this->setFlashMessage('error', 'Tu carrito está vacío');
                $this->redirect('/carrito');
                return;
            }
            
            // Verificar stock final
            foreach ($items as $item) {
                $producto = $productoModel->find($item['id_producto']);
                if (!$producto || $producto['stock'] < $item['cantidad']) {
                    $this->setFlashMessage('error', 'Stock insuficiente para algunos productos');
                    $this->redirect('/carrito');
                    return;
                }
            }
            
            // Calcular totales
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['precio'] * $item['cantidad'];
            }
            
            $currentUser = $this->getCurrentUser();
            $costoEnvio = $this->calculateShipping($subtotal, $currentUser);
            $total = $subtotal + $costoEnvio;
            
            // Iniciar transacción
            $pedidoModel->beginTransaction();
            
            try {
                // Crear pedido
                $numeroPedido = $this->generateOrderNumber();
                $pedidoData = [
                    'numero_pedido' => $numeroPedido,
                    'id_usuario' => $userId,
                    'estado' => 'pendiente',
                    'subtotal' => $subtotal,
                    'costo_envio' => $costoEnvio,
                    'total' => $total,
                    'metodo_pago' => $data['metodo_pago'],
                    'tipo_entrega' => $data['tipo_entrega'],
                    'direccion_envio' => $data['direccion_envio'],
                    'ciudad' => $data['ciudad'],
                    'codigo_postal' => $data['codigo_postal'],
                    'telefono_contacto' => $data['telefono'],
                    'comentarios' => $data['comentarios'],
                    'fecha_pedido' => date('Y-m-d H:i:s')
                ];
                
                $pedidoId = $pedidoModel->create($pedidoData);
                
                if (!$pedidoId) {
                    throw new Exception('Error al crear el pedido');
                }
                
                // Crear detalles del pedido y reducir stock
                foreach ($items as $item) {
                    $detalleData = [
                        'id_pedido' => $pedidoId,
                        'id_producto' => $item['id_producto'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio']
                    ];
                    
                    $detalleId = $detallePedidoModel->create($detalleData);
                    if (!$detalleId) {
                        throw new Exception('Error al crear detalle del pedido');
                    }
                    
                    // Reducir stock
                    $productoModel->reducirStock($item['id_producto'], $item['cantidad']);
                }
                
                // Limpiar carrito
                $carritoModel->clearCart($userId);
                
                // Crear notificación
                $notificacionModel->create([
                    'id_usuario' => $userId,
                    'titulo' => 'Pedido Creado',
                    'mensaje' => "Tu pedido #{$numeroPedido} ha sido creado exitosamente.",
                    'tipo' => 'pedido',
                    'fecha_creacion' => date('Y-m-d H:i:s')
                ]);
                
                // Confirmar transacción
                $pedidoModel->commit();
                
                // Log de actividad
                $this->logActivity('order_created', "Order: {$numeroPedido} - Total: \${$total}");
                
                // Enviar confirmación por email (opcional)
                $this->sendOrderConfirmation($pedidoId, $currentUser);
                
                $this->setFlashMessage('success', "¡Pedido creado exitosamente! Número de pedido: {$numeroPedido}");
                $this->redirect('/pedidos/' . $pedidoId);
                
            } catch (Exception $e) {
                $pedidoModel->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error processing order: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al procesar el pedido. Inténtalo de nuevo.');
            $this->redirect('/pedidos/checkout');
        }
    }
    
    /**
     * Cancelar un pedido
     */
    public function cancel($id) {
        if (!$this->requireAuth()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pedidos');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $id = intval($id);
        
        try {
            $pedidoModel = new Pedido();
            $detallePedidoModel = new DetallePedido();
            $productoModel = new Producto();
            $notificacionModel = new Notificacion();
            $userId = $this->getCurrentUserId();
            
            // Verificar que el pedido pertenece al usuario
            $pedido = $pedidoModel->find($id);
            if (!$pedido || $pedido['id_usuario'] != $userId) {
                $this->setFlashMessage('error', 'Pedido no encontrado');
                $this->redirect('/pedidos');
                return;
            }
            
            // Verificar que se puede cancelar
            if (!in_array($pedido['estado'], ['pendiente', 'confirmado'])) {
                $this->setFlashMessage('error', 'Este pedido no se puede cancelar');
                $this->redirect('/pedidos/' . $id);
                return;
            }
            
            // Iniciar transacción
            $pedidoModel->beginTransaction();
            
            try {
                // Restaurar stock
                $detalles = $detallePedidoModel->getDetallesByPedido($id);
                foreach ($detalles as $detalle) {
                    $productoModel->incrementarStock($detalle['id_producto'], $detalle['cantidad']);
                }
                
                // Actualizar estado del pedido
                $pedidoModel->update($id, [
                    'estado' => 'cancelado',
                    'fecha_cancelacion' => date('Y-m-d H:i:s')
                ]);
                
                // Crear notificación
                $notificacionModel->create([
                    'id_usuario' => $userId,
                    'titulo' => 'Pedido Cancelado',
                    'mensaje' => "Tu pedido #{$pedido['numero_pedido']} ha sido cancelado.",
                    'tipo' => 'pedido',
                    'fecha_creacion' => date('Y-m-d H:i:s')
                ]);
                
                $pedidoModel->commit();
                
                $this->logActivity('order_cancelled', "Order: {$pedido['numero_pedido']}");
                
                $this->setFlashMessage('success', 'Pedido cancelado correctamente');
                
            } catch (Exception $e) {
                $pedidoModel->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error cancelling order: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cancelar el pedido');
        }
        
        $this->redirect('/pedidos/' . $id);
    }
    
    /**
     * Reordenar (agregar productos al carrito desde un pedido anterior)
     */
    public function reorder($id) {
        if (!$this->requireAuth()) return;
        
        $id = intval($id);
        
        try {
            $pedidoModel = new Pedido();
            $detallePedidoModel = new DetallePedido();
            $carritoModel = new CarritoCompra();
            $productoModel = new Producto();
            $userId = $this->getCurrentUserId();
            
            // Verificar pedido
            $pedido = $pedidoModel->find($id);
            if (!$pedido || $pedido['id_usuario'] != $userId) {
                $this->setFlashMessage('error', 'Pedido no encontrado');
                $this->redirect('/pedidos');
                return;
            }
            
            // Obtener detalles del pedido
            $detalles = $detallePedidoModel->getDetallesByPedido($id);
            
            $itemsAgregados = 0;
            $itemsNoDisponibles = [];
            
            foreach ($detalles as $detalle) {
                $producto = $productoModel->find($detalle['id_producto']);
                
                if (!$producto || !$producto['activo']) {
                    $itemsNoDisponibles[] = $detalle['nombre_producto'] ?? 'Producto no disponible';
                    continue;
                }
                
                if ($producto['stock'] < $detalle['cantidad']) {
                    $itemsNoDisponibles[] = "{$producto['nombre']} (stock insuficiente)";
                    continue;
                }
                
                // Verificar si ya está en el carrito
                $itemExistente = $carritoModel->getItem($userId, $detalle['id_producto']);
                
                if ($itemExistente) {
                    $nuevaCantidad = min(
                        $itemExistente['cantidad'] + $detalle['cantidad'],
                        $producto['stock'],
                        99
                    );
                    $carritoModel->updateCantidad($userId, $detalle['id_producto'], $nuevaCantidad);
                } else {
                    $carritoModel->create([
                        'id_usuario' => $userId,
                        'id_producto' => $detalle['id_producto'],
                        'cantidad' => min($detalle['cantidad'], $producto['stock'], 99),
                        'precio' => $producto['precio'],
                        'fecha_agregado' => date('Y-m-d H:i:s')
                    ]);
                }
                
                $itemsAgregados++;
            }
            
            $mensaje = "Se agregaron {$itemsAgregados} productos al carrito";
            if (!empty($itemsNoDisponibles)) {
                $mensaje .= ". Productos no disponibles: " . implode(', ', $itemsNoDisponibles);
            }
            
            $this->logActivity('order_reordered', "Original order: {$pedido['numero_pedido']} - Items: {$itemsAgregados}");
            $this->setFlashMessage('success', $mensaje);
            
        } catch (Exception $e) {
            error_log("Error reordering: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al volver a ordenar');
        }
        
        $this->redirect('/carrito');
    }
    
    // MÉTODOS PRIVADOS
    
    /**
     * Validar datos del pedido
     */
    private function validateOrderData($data) {
        $errors = [];
        
        if ($data['tipo_entrega'] === 'domicilio') {
            if (empty($data['direccion_envio'])) {
                $errors['direccion_envio'] = 'La dirección de envío es requerida';
            }
            if (empty($data['ciudad'])) {
                $errors['ciudad'] = 'La ciudad es requerida';
            }
            if (empty($data['codigo_postal'])) {
                $errors['codigo_postal'] = 'El código postal es requerido';
            }
        }
        
        if (empty($data['telefono'])) {
            $errors['telefono'] = 'El teléfono de contacto es requerido';
        }
        
        if (empty($data['metodo_pago'])) {
            $errors['metodo_pago'] = 'Selecciona un método de pago';
        }
        
        if (empty($data['tipo_entrega'])) {
            $errors['tipo_entrega'] = 'Selecciona el tipo de entrega';
        }
        
        return $errors;
    }
    
    /**
     * Calcular costo de envío
     */
    private function calculateShipping($subtotal, $usuario) {
        // Lógica simple: gratis si es mayor a $500, sino $50
        if ($subtotal >= 500) return 0;
        
        // Se puede hacer más complejo considerando ubicación, peso, etc.
        return 50;
    }
    
    /**
     * Generar número de pedido único
     */
    private function generateOrderNumber() {
        return 'AGR' . date('Ymd') . rand(1000, 9999);
    }
    
    /**
     * Enviar confirmación de pedido por email
     */
    private function sendOrderConfirmation($pedidoId, $usuario) {
        // TODO: Implementar envío de email
        error_log("Order confirmation should be sent to {$usuario['email']} for order {$pedidoId}");
    }
}
?>