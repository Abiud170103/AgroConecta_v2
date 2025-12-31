<?php
/**
 * CartController - Controlador del carrito de compras
 * Maneja todas las operaciones del carrito de compras
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'BaseController.php';
require_once APP_PATH . '/models/Carrito.php';
require_once APP_PATH . '/models/Producto.php';

class CartController extends BaseController {
    
    /**
     * Mostrar contenido del carrito
     */
    public function index() {
        if (!$this->requireAuth()) return;
        
        try {
            $carritoModel = new CarritoCompra();
            $userId = $this->getCurrentUserId();
            
            // Obtener items del carrito con información de productos
            $items = $carritoModel->getItemsCarrito($userId);
            
            // Calcular totales
            $subtotal = 0;
            $totalItems = 0;
            
            foreach ($items as &$item) {
                $itemTotal = $item['precio'] * $item['cantidad'];
                $item['total'] = $itemTotal;
                $subtotal += $itemTotal;
                $totalItems += $item['cantidad'];
            }
            
            // Calcular envío (ejemplo: $50 si es menor a $500, gratis si es mayor)
            $costoEnvio = $subtotal >= 500 ? 0 : 50;
            $total = $subtotal + $costoEnvio;
            
            $this->setViewData('pageTitle', 'Mi Carrito');
            $this->setViewData('items', $items);
            $this->setViewData('subtotal', $subtotal);
            $this->setViewData('costoEnvio', $costoEnvio);
            $this->setViewData('total', $total);
            $this->setViewData('totalItems', $totalItems);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Mi Carrito', 'url' => '/carrito']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in CartController::index: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar el carrito');
            $this->setViewData('items', []);
            $this->setViewData('subtotal', 0);
            $this->setViewData('total', 0);
        }
        
        $this->render('cart/index');
    }
    
    /**
     * Agregar producto al carrito (AJAX)
     */
    public function add() {
        if (!$this->requireAuth()) {
            $this->jsonError('Debes iniciar sesión para agregar productos al carrito', 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', 405);
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->jsonError('Token CSRF inválido', 403);
            return;
        }
        
        $data = $this->sanitizeInput([
            'producto_id' => intval($_POST['producto_id'] ?? 0),
            'cantidad' => intval($_POST['cantidad'] ?? 1)
        ]);
        
        // Validaciones
        if ($data['producto_id'] <= 0) {
            $this->jsonError('Producto no válido');
            return;
        }
        
        if ($data['cantidad'] <= 0 || $data['cantidad'] > 99) {
            $this->jsonError('Cantidad no válida (1-99)');
            return;
        }
        
        try {
            $carritoModel = new CarritoCompra();
            $productoModel = new Producto();
            $userId = $this->getCurrentUserId();
            
            // Verificar que el producto existe y está activo
            $producto = $productoModel->find($data['producto_id']);
            if (!$producto || !$producto['activo']) {
                $this->jsonError('Producto no disponible');
                return;
            }
            
            // Verificar stock disponible
            if ($producto['stock'] < $data['cantidad']) {
                $this->jsonError('Stock insuficiente. Disponible: ' . $producto['stock']);
                return;
            }
            
            // Verificar si ya existe en el carrito
            $itemExistente = $carritoModel->getItem($userId, $data['producto_id']);
            
            if ($itemExistente) {
                // Actualizar cantidad
                $nuevaCantidad = $itemExistente['cantidad'] + $data['cantidad'];
                
                if ($nuevaCantidad > $producto['stock']) {
                    $this->jsonError('No puedes agregar más cantidad. Stock disponible: ' . $producto['stock']);
                    return;
                }
                
                if ($nuevaCantidad > 99) {
                    $this->jsonError('Cantidad máxima por producto: 99');
                    return;
                }
                
                $updated = $carritoModel->updateCantidad($userId, $data['producto_id'], $nuevaCantidad);
                
                if ($updated) {
                    $this->logActivity('cart_item_updated', "Product: {$data['producto_id']} - New quantity: {$nuevaCantidad}");
                    
                    // Obtener nueva información del carrito
                    $cartInfo = $this->getCartInfo($userId);
                    
                    $this->jsonSuccess('Cantidad actualizada en el carrito', [
                        'action' => 'updated',
                        'product_id' => $data['producto_id'],
                        'new_quantity' => $nuevaCantidad,
                        'cart_info' => $cartInfo
                    ]);
                } else {
                    $this->jsonError('Error al actualizar el carrito');
                }
                
            } else {
                // Agregar nuevo item
                $itemData = [
                    'id_usuario' => $userId,
                    'id_producto' => $data['producto_id'],
                    'cantidad' => $data['cantidad'],
                    'precio' => $producto['precio'],
                    'fecha_agregado' => date('Y-m-d H:i:s')
                ];
                
                $itemId = $carritoModel->create($itemData);
                
                if ($itemId) {
                    $this->logActivity('cart_item_added', "Product: {$data['producto_id']} - Quantity: {$data['cantidad']}");
                    
                    // Obtener información del carrito
                    $cartInfo = $this->getCartInfo($userId);
                    
                    $this->jsonSuccess('Producto agregado al carrito', [
                        'action' => 'added',
                        'product_id' => $data['producto_id'],
                        'product_name' => $producto['nombre'],
                        'quantity' => $data['cantidad'],
                        'cart_info' => $cartInfo
                    ]);
                } else {
                    $this->jsonError('Error al agregar al carrito');
                }
            }
            
        } catch (Exception $e) {
            error_log("Error adding to cart: " . $e->getMessage());
            $this->jsonError('Error interno del servidor', 500);
        }
    }
    
    /**
     * Actualizar cantidad de un item (AJAX)
     */
    public function update() {
        if (!$this->requireAuth()) {
            $this->jsonError('No autorizado', 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', 405);
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->jsonError('Token CSRF inválido', 403);
            return;
        }
        
        $data = $this->sanitizeInput([
            'producto_id' => intval($_POST['producto_id'] ?? 0),
            'cantidad' => intval($_POST['cantidad'] ?? 1)
        ]);
        
        // Validaciones
        if ($data['producto_id'] <= 0) {
            $this->jsonError('Producto no válido');
            return;
        }
        
        if ($data['cantidad'] <= 0 || $data['cantidad'] > 99) {
            $this->jsonError('Cantidad no válida (1-99)');
            return;
        }
        
        try {
            $carritoModel = new CarritoCompra();
            $productoModel = new Producto();
            $userId = $this->getCurrentUserId();
            
            // Verificar que el producto existe en el carrito
            $item = $carritoModel->getItem($userId, $data['producto_id']);
            if (!$item) {
                $this->jsonError('Producto no encontrado en el carrito');
                return;
            }
            
            // Verificar stock
            $producto = $productoModel->find($data['producto_id']);
            if ($producto['stock'] < $data['cantidad']) {
                $this->jsonError('Stock insuficiente. Disponible: ' . $producto['stock']);
                return;
            }
            
            // Actualizar cantidad
            $updated = $carritoModel->updateCantidad($userId, $data['producto_id'], $data['cantidad']);
            
            if ($updated) {
                $this->logActivity('cart_item_quantity_updated', "Product: {$data['producto_id']} - New quantity: {$data['cantidad']}");
                
                // Calcular nuevo total del item
                $nuevoTotal = $item['precio'] * $data['cantidad'];
                
                // Obtener información actualizada del carrito
                $cartInfo = $this->getCartInfo($userId);
                
                $this->jsonSuccess('Cantidad actualizada', [
                    'product_id' => $data['producto_id'],
                    'new_quantity' => $data['cantidad'],
                    'item_total' => $nuevoTotal,
                    'cart_info' => $cartInfo
                ]);
            } else {
                $this->jsonError('Error al actualizar la cantidad');
            }
            
        } catch (Exception $e) {
            error_log("Error updating cart: " . $e->getMessage());
            $this->jsonError('Error interno del servidor', 500);
        }
    }
    
    /**
     * Remover producto del carrito (AJAX)
     */
    public function remove() {
        if (!$this->requireAuth()) {
            $this->jsonError('No autorizado', 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', 405);
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->jsonError('Token CSRF inválido', 403);
            return;
        }
        
        $productoId = intval($_POST['producto_id'] ?? 0);
        
        if ($productoId <= 0) {
            $this->jsonError('Producto no válido');
            return;
        }
        
        try {
            $carritoModel = new CarritoCompra();
            $userId = $this->getCurrentUserId();
            
            // Verificar que el producto está en el carrito
            $item = $carritoModel->getItem($userId, $productoId);
            if (!$item) {
                $this->jsonError('Producto no encontrado en el carrito');
                return;
            }
            
            // Remover item
            $removed = $carritoModel->removeItem($userId, $productoId);
            
            if ($removed) {
                $this->logActivity('cart_item_removed', "Product: {$productoId}");
                
                // Obtener información actualizada del carrito
                $cartInfo = $this->getCartInfo($userId);
                
                $this->jsonSuccess('Producto removido del carrito', [
                    'product_id' => $productoId,
                    'cart_info' => $cartInfo
                ]);
            } else {
                $this->jsonError('Error al remover el producto');
            }
            
        } catch (Exception $e) {
            error_log("Error removing from cart: " . $e->getMessage());
            $this->jsonError('Error interno del servidor', 500);
        }
    }
    
    /**
     * Limpiar todo el carrito
     */
    public function clear() {
        if (!$this->requireAuth()) {
            $this->jsonError('No autorizado', 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', 405);
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->jsonError('Token CSRF inválido', 403);
            return;
        }
        
        try {
            $carritoModel = new CarritoCompra();
            $userId = $this->getCurrentUserId();
            
            $cleared = $carritoModel->clearCart($userId);
            
            if ($cleared) {
                $this->logActivity('cart_cleared', 'User cleared shopping cart');
                
                if (isset($_POST['ajax'])) {
                    $this->jsonSuccess('Carrito limpiado', [
                        'cart_info' => ['total_items' => 0, 'subtotal' => 0, 'total' => 0]
                    ]);
                } else {
                    $this->setFlashMessage('success', 'Carrito limpiado correctamente');
                    $this->redirect('/carrito');
                }
            } else {
                if (isset($_POST['ajax'])) {
                    $this->jsonError('Error al limpiar el carrito');
                } else {
                    $this->setFlashMessage('error', 'Error al limpiar el carrito');
                    $this->redirect('/carrito');
                }
            }
            
        } catch (Exception $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            if (isset($_POST['ajax'])) {
                $this->jsonError('Error interno del servidor', 500);
            } else {
                $this->setFlashMessage('error', 'Error interno del servidor');
                $this->redirect('/carrito');
            }
        }
    }
    
    /**
     * Obtener información del carrito (AJAX)
     */
    public function info() {
        if (!$this->requireAuth()) {
            $this->jsonError('No autorizado', 401);
            return;
        }
        
        try {
            $userId = $this->getCurrentUserId();
            $cartInfo = $this->getCartInfo($userId);
            
            $this->jsonSuccess('OK', $cartInfo);
            
        } catch (Exception $e) {
            error_log("Error getting cart info: " . $e->getMessage());
            $this->jsonError('Error interno del servidor', 500);
        }
    }
    
    /**
     * Vista previa del carrito (para modal/dropdown)
     */
    public function preview() {
        if (!$this->requireAuth()) {
            $this->jsonError('No autorizado', 401);
            return;
        }
        
        try {
            $carritoModel = new CarritoCompra();
            $userId = $this->getCurrentUserId();
            
            // Obtener últimos items agregados (máximo 5)
            $items = $carritoModel->getItemsCarrito($userId, 5);
            $cartInfo = $this->getCartInfo($userId);
            
            $this->jsonSuccess('OK', [
                'items' => $items,
                'cart_info' => $cartInfo
            ]);
            
        } catch (Exception $e) {
            error_log("Error in cart preview: " . $e->getMessage());
            $this->jsonError('Error interno del servidor', 500);
        }
    }
    
    /**
     * Proceder al checkout
     */
    public function checkout() {
        if (!$this->requireAuth()) return;
        
        try {
            $carritoModel = new CarritoCompra();
            $userId = $this->getCurrentUserId();
            
            // Verificar que hay items en el carrito
            $items = $carritoModel->getItemsCarrito($userId);
            
            if (empty($items)) {
                $this->setFlashMessage('error', 'Tu carrito está vacío');
                $this->redirect('/carrito');
                return;
            }
            
            // Verificar stock de todos los items
            $productoModel = new Producto();
            $stockErrors = [];
            
            foreach ($items as $item) {
                $producto = $productoModel->find($item['id_producto']);
                if (!$producto || !$producto['activo']) {
                    $stockErrors[] = "El producto '{$item['nombre']}' ya no está disponible";
                    continue;
                }
                
                if ($producto['stock'] < $item['cantidad']) {
                    $stockErrors[] = "Stock insuficiente para '{$item['nombre']}'. Disponible: {$producto['stock']}";
                }
            }
            
            if (!empty($stockErrors)) {
                $_SESSION['stock_errors'] = $stockErrors;
                $this->setFlashMessage('error', 'Hay problemas con algunos productos en tu carrito');
                $this->redirect('/carrito');
                return;
            }
            
            // Redireccionar al proceso de checkout
            $this->redirect('/pedidos/checkout');
            
        } catch (Exception $e) {
            error_log("Error in checkout redirect: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al proceder al checkout');
            $this->redirect('/carrito');
        }
    }
    
    // MÉTODOS PRIVADOS
    
    /**
     * Obtener información resumida del carrito
     */
    private function getCartInfo($userId) {
        try {
            $carritoModel = new CarritoCompra();
            $items = $carritoModel->getItemsCarrito($userId);
            
            $totalItems = 0;
            $subtotal = 0;
            
            foreach ($items as $item) {
                $totalItems += $item['cantidad'];
                $subtotal += $item['precio'] * $item['cantidad'];
            }
            
            $costoEnvio = $subtotal >= 500 ? 0 : 50;
            $total = $subtotal + $costoEnvio;
            
            return [
                'total_items' => $totalItems,
                'subtotal' => $subtotal,
                'costo_envio' => $costoEnvio,
                'total' => $total,
                'items_count' => count($items)
            ];
            
        } catch (Exception $e) {
            error_log("Error getting cart info: " . $e->getMessage());
            return [
                'total_items' => 0,
                'subtotal' => 0,
                'costo_envio' => 0,
                'total' => 0,
                'items_count' => 0
            ];
        }
    }
}
?>