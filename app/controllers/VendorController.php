<?php
/**
 * VendorController - Controlador para vendedores
 * Dashboard y gestión específica para usuarios vendedores
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'BaseController.php';
require_once APP_PATH . '/models/Usuario.php';
require_once APP_PATH . '/models/Producto.php';
require_once APP_PATH . '/models/Pedido.php';
require_once APP_PATH . '/models/DetallePedido.php';
require_once APP_PATH . '/models/Resena.php';
require_once APP_PATH . '/models/Notificacion.php';

class VendorController extends BaseController {
    
    /**
     * Verificar que el usuario es vendedor
     */
    private function requireVendor() {
        if (!$this->requireAuth()) return false;
        
        $currentUser = $this->getCurrentUser();
        if ($currentUser['tipo_usuario'] !== 'vendedor') {
            $this->setFlashMessage('error', 'Acceso denegado. Se requiere cuenta de vendedor.');
            $this->redirect('/dashboard');
            return false;
        }
        
        return true;
    }
    
    /**
     * Dashboard principal del vendedor
     */
    public function dashboard() {
        if (!$this->requireVendor()) return;
        
        try {
            $productoModel = new Producto();
            $pedidoModel = new Pedido();
            $resenaModel = new Resena();
            $vendorId = $this->getCurrentUserId();
            
            // Estadísticas del vendedor
            $estadisticas = [
                'productos_activos' => $productoModel->count(['id_vendedor' => $vendorId, 'activo' => 1]),
                'productos_total' => $productoModel->count(['id_vendedor' => $vendorId]),
                'productos_stock_bajo' => $productoModel->getProductosStockBajo(10, null, $vendorId),
                'ventas_mes' => $pedidoModel->getVentasVendedorMes($vendorId),
                'ventas_total' => $pedidoModel->getVentasVendedorTotal($vendorId),
                'pedidos_pendientes' => $pedidoModel->getPedidosPendientesVendedor($vendorId),
                'calificacion_promedio' => $resenaModel->getCalificacionPromedioVendedor($vendorId),
                'total_resenas' => $resenaModel->getTotalResenasVendedor($vendorId)
            ];
            
            // Últimos pedidos del vendedor
            $ultimosPedidos = $pedidoModel->getPedidosVendedor($vendorId, 5);
            
            // Productos más vendidos
            $productosMasVendidos = $pedidoModel->getProductosMasVendidosVendedor($vendorId, 5);
            
            // Reseñas recientes
            $resenasRecientes = $resenaModel->getResenasVendedor($vendorId, 5);
            
            // Productos con stock bajo
            $productosStockBajo = $productoModel->getProductosStockBajoVendedor($vendorId, 5);
            
            $this->setViewData('pageTitle', 'Dashboard de Vendedor');
            $this->setViewData('estadisticas', $estadisticas);
            $this->setViewData('ultimosPedidos', $ultimosPedidos);
            $this->setViewData('productosMasVendidos', $productosMasVendidos);
            $this->setViewData('resenasRecientes', $resenasRecientes);
            $this->setViewData('productosStockBajo', $productosStockBajo);
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            
        } catch (Exception $e) {
            error_log("Error in VendorController::dashboard: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar el dashboard');
            $this->setViewData('estadisticas', []);
        }
        
        $this->render('vendor/dashboard');
    }
    
    /**
     * Gestión de productos del vendedor
     */
    public function products() {
        if (!$this->requireVendor()) return;
        
        try {
            $productoModel = new Producto();
            $vendorId = $this->getCurrentUserId();
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 15;
            $filtro = $this->sanitizeInput($_GET['filtro'] ?? '');
            $categoria = $this->sanitizeInput($_GET['categoria'] ?? '');
            $estado = $_GET['estado'] ?? '';
            
            $filtros = ['id_vendedor' => $vendorId];
            if ($filtro) {
                $filtros['search'] = $filtro;
            }
            if ($categoria) {
                $filtros['categoria'] = $categoria;
            }
            if ($estado !== '') {
                $filtros['activo'] = intval($estado);
            }
            
            $offset = ($page - 1) * $perPage;
            $productos = $productoModel->getProductosVendedor($vendorId, $filtros, $perPage, $offset);
            $totalProductos = $productoModel->countProductos($filtros);
            $totalPaginas = ceil($totalProductos / $perPage);
            
            // Obtener categorías
            $categorias = $productoModel->getCategorias();
            
            $this->setViewData('pageTitle', 'Mis Productos');
            $this->setViewData('productos', $productos);
            $this->setViewData('categorias', $categorias);
            $this->setViewData('filtros', [
                'filtro' => $filtro,
                'categoria' => $categoria,
                'estado' => $estado
            ]);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'totalItems' => $totalProductos
            ]);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Dashboard', 'url' => '/vendedor/dashboard'],
                ['name' => 'Mis Productos', 'url' => '/vendedor/productos']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in VendorController::products: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar productos');
            $this->setViewData('productos', []);
        }
        
        $this->render('vendor/products');
    }
    
    /**
     * Crear nuevo producto
     */
    public function createProduct() {
        if (!$this->requireVendor()) return;
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $productoModel = new Producto();
            $categorias = $productoModel->getCategorias();
            
            $this->setViewData('pageTitle', 'Crear Producto');
            $this->setViewData('categorias', $categorias);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('errors', $_SESSION['product_errors'] ?? []);
            $this->setViewData('oldData', $_SESSION['product_data'] ?? []);
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Dashboard', 'url' => '/vendedor/dashboard'],
                ['name' => 'Mis Productos', 'url' => '/vendedor/productos'],
                ['name' => 'Crear Producto', 'url' => '/vendedor/productos/crear']
            ]);
            
            // Limpiar errores de sesión
            unset($_SESSION['product_errors'], $_SESSION['product_data']);
            
            $this->render('vendor/create-product');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $data = $this->sanitizeInput([
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'categoria' => $_POST['categoria'] ?? '',
            'precio' => floatval($_POST['precio'] ?? 0),
            'stock' => intval($_POST['stock'] ?? 0),
            'unidad_medida' => $_POST['unidad_medida'] ?? '',
            'origen' => $_POST['origen'] ?? '',
            'fecha_cosecha' => $_POST['fecha_cosecha'] ?? '',
            'caracteristicas' => $_POST['caracteristicas'] ?? '',
            'instrucciones_cuidado' => $_POST['instrucciones_cuidado'] ?? ''
        ]);
        
        // Validaciones
        $errors = $this->validateProductData($data);
        
        // Validar archivo de imagen
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            $errors['imagen'] = 'La imagen del producto es requerida';
        } else {
            $uploadResult = $this->validateAndUploadImage($_FILES['imagen']);
            if (!$uploadResult['success']) {
                $errors['imagen'] = $uploadResult['error'];
            } else {
                $data['imagen_url'] = $uploadResult['url'];
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['product_errors'] = $errors;
            $_SESSION['product_data'] = $data;
            $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            $this->redirect('/vendedor/productos/crear');
            return;
        }
        
        try {
            $productoModel = new Producto();
            $vendorId = $this->getCurrentUserId();
            
            $productData = array_merge($data, [
                'id_vendedor' => $vendorId,
                'activo' => 1,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]);
            
            $productId = $productoModel->create($productData);
            
            if ($productId) {
                $this->logActivity('product_created', "Product created: {$data['nombre']} (ID: {$productId})");
                $this->setFlashMessage('success', 'Producto creado correctamente');
                $this->redirect('/vendedor/productos/editar/' . $productId);
            } else {
                $this->setFlashMessage('error', 'Error al crear el producto');
                $this->redirect('/vendedor/productos/crear');
            }
            
        } catch (Exception $e) {
            error_log("Error creating product: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al crear el producto');
            $this->redirect('/vendedor/productos/crear');
        }
    }
    
    /**
     * Editar producto existente
     */
    public function editProduct($id) {
        if (!$this->requireVendor()) return;
        
        $id = intval($id);
        $vendorId = $this->getCurrentUserId();
        
        try {
            $productoModel = new Producto();
            $producto = $productoModel->find($id);
            
            if (!$producto || $producto['id_vendedor'] != $vendorId) {
                $this->setFlashMessage('error', 'Producto no encontrado o no tienes permisos');
                $this->redirect('/vendedor/productos');
                return;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $categorias = $productoModel->getCategorias();
                
                $this->setViewData('pageTitle', 'Editar Producto: ' . $producto['nombre']);
                $this->setViewData('producto', $producto);
                $this->setViewData('categorias', $categorias);
                $this->setViewData('csrf_token', $this->generateCSRF());
                $this->setViewData('errors', $_SESSION['product_errors'] ?? []);
                $this->setViewData('oldData', $_SESSION['product_data'] ?? []);
                $this->setViewData('success', $this->getFlashMessage('success'));
                $this->setViewData('error', $this->getFlashMessage('error'));
                $this->setViewData('breadcrumb', [
                    ['name' => 'Dashboard', 'url' => '/vendedor/dashboard'],
                    ['name' => 'Mis Productos', 'url' => '/vendedor/productos'],
                    ['name' => 'Editar: ' . $producto['nombre'], 'url' => '/vendedor/productos/editar/' . $id]
                ]);
                
                unset($_SESSION['product_errors'], $_SESSION['product_data']);
                $this->render('vendor/edit-product');
                return;
            }
            
            if (!$this->validateCSRF()) return;
            
            $data = $this->sanitizeInput([
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'categoria' => $_POST['categoria'] ?? '',
                'precio' => floatval($_POST['precio'] ?? 0),
                'stock' => intval($_POST['stock'] ?? 0),
                'unidad_medida' => $_POST['unidad_medida'] ?? '',
                'origen' => $_POST['origen'] ?? '',
                'fecha_cosecha' => $_POST['fecha_cosecha'] ?? '',
                'caracteristicas' => $_POST['caracteristicas'] ?? '',
                'instrucciones_cuidado' => $_POST['instrucciones_cuidado'] ?? '',
                'activo' => isset($_POST['activo']) ? 1 : 0
            ]);
            
            // Validaciones
            $errors = $this->validateProductData($data, true);
            
            // Validar imagen (opcional en edición)
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->validateAndUploadImage($_FILES['imagen']);
                if (!$uploadResult['success']) {
                    $errors['imagen'] = $uploadResult['error'];
                } else {
                    $data['imagen_url'] = $uploadResult['url'];
                }
            }
            
            if (!empty($errors)) {
                $_SESSION['product_errors'] = $errors;
                $_SESSION['product_data'] = $data;
                $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
                $this->redirect('/vendedor/productos/editar/' . $id);
                return;
            }
            
            $data['fecha_actualizacion'] = date('Y-m-d H:i:s');
            $updated = $productoModel->update($id, $data);
            
            if ($updated) {
                $this->logActivity('product_updated', "Product updated: {$data['nombre']} (ID: {$id})");
                $this->setFlashMessage('success', 'Producto actualizado correctamente');
            } else {
                $this->setFlashMessage('error', 'No se realizaron cambios');
            }
            
            $this->redirect('/vendedor/productos/editar/' . $id);
            
        } catch (Exception $e) {
            error_log("Error editing product: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al editar el producto');
            $this->redirect('/vendedor/productos');
        }
    }
    
    /**
     * Ver pedidos del vendedor
     */
    public function orders() {
        if (!$this->requireVendor()) return;
        
        try {
            $pedidoModel = new Pedido();
            $vendorId = $this->getCurrentUserId();
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 15;
            $estado = $this->sanitizeInput($_GET['estado'] ?? '');
            $fechaDesde = $this->sanitizeInput($_GET['fecha_desde'] ?? '');
            $fechaHasta = $this->sanitizeInput($_GET['fecha_hasta'] ?? '');
            
            $filtros = ['vendedor_id' => $vendorId];
            if ($estado) $filtros['estado'] = $estado;
            if ($fechaDesde) $filtros['fecha_desde'] = $fechaDesde;
            if ($fechaHasta) $filtros['fecha_hasta'] = $fechaHasta;
            
            $offset = ($page - 1) * $perPage;
            $pedidos = $pedidoModel->getPedidosVendedor($vendorId, $filtros, $perPage, $offset);
            $totalPedidos = $pedidoModel->countPedidosVendedor($vendorId, $filtros);
            $totalPaginas = ceil($totalPedidos / $perPage);
            
            // Estadísticas de pedidos del vendedor
            $estadisticas = [
                'total_pedidos' => $pedidoModel->countPedidosVendedor($vendorId),
                'pedidos_pendientes' => $pedidoModel->countPedidosVendedor($vendorId, ['estado' => 'pendiente']),
                'ventas_mes' => $pedidoModel->getVentasVendedorMes($vendorId),
                'promedio_pedido' => $pedidoModel->getPromedioVentaPorPedidoVendedor($vendorId)
            ];
            
            $this->setViewData('pageTitle', 'Mis Ventas');
            $this->setViewData('pedidos', $pedidos);
            $this->setViewData('estadisticas', $estadisticas);
            $this->setViewData('filtros', [
                'estado' => $estado,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta
            ]);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'totalItems' => $totalPedidos
            ]);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Dashboard', 'url' => '/vendedor/dashboard'],
                ['name' => 'Mis Ventas', 'url' => '/vendedor/pedidos']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in VendorController::orders: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar pedidos');
            $this->setViewData('pedidos', []);
        }
        
        $this->render('vendor/orders');
    }
    
    /**
     * Ver reseñas de productos del vendedor
     */
    public function reviews() {
        if (!$this->requireVendor()) return;
        
        try {
            $resenaModel = new Resena();
            $vendorId = $this->getCurrentUserId();
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 15;
            $calificacion = intval($_GET['calificacion'] ?? 0);
            
            $filtros = ['vendedor_id' => $vendorId];
            if ($calificacion > 0) $filtros['calificacion'] = $calificacion;
            
            $offset = ($page - 1) * $perPage;
            $resenas = $resenaModel->getResenasVendedor($vendorId, $filtros, $perPage, $offset);
            $totalResenas = $resenaModel->countResenasVendedor($vendorId, $filtros);
            $totalPaginas = ceil($totalResenas / $perPage);
            
            // Estadísticas de reseñas
            $estadisticas = [
                'total_resenas' => $resenaModel->getTotalResenasVendedor($vendorId),
                'calificacion_promedio' => $resenaModel->getCalificacionPromedioVendedor($vendorId),
                'distribucion_calificaciones' => $resenaModel->getDistribucionCalificacionesVendedor($vendorId)
            ];
            
            $this->setViewData('pageTitle', 'Reseñas de mis Productos');
            $this->setViewData('resenas', $resenas);
            $this->setViewData('estadisticas', $estadisticas);
            $this->setViewData('calificacionFiltro', $calificacion);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'totalItems' => $totalResenas
            ]);
            $this->setViewData('breadcrumb', [
                ['name' => 'Dashboard', 'url' => '/vendedor/dashboard'],
                ['name' => 'Reseñas', 'url' => '/vendedor/resenas']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in VendorController::reviews: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar reseñas');
            $this->setViewData('resenas', []);
        }
        
        $this->render('vendor/reviews');
    }
    
    /**
     * Reportes del vendedor
     */
    public function reports() {
        if (!$this->requireVendor()) return;
        
        try {
            $pedidoModel = new Pedido();
            $productoModel = new Producto();
            $vendorId = $this->getCurrentUserId();
            
            // Parámetros de fecha
            $fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01');
            $fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
            
            // Reportes del vendedor
            $ventasPorDia = $pedidoModel->getVentasVendedorPorPeriodo($vendorId, $fechaDesde, $fechaHasta, 'day');
            $productosMasVendidos = $pedidoModel->getProductosMasVendidosVendedor($vendorId, $fechaDesde, $fechaHasta);
            
            // Estadísticas del período
            $estadisticasPeriodo = [
                'total_ventas' => $pedidoModel->getVentasVendedorPeriodo($vendorId, $fechaDesde, $fechaHasta),
                'total_pedidos' => $pedidoModel->getPedidosVendedorPeriodo($vendorId, $fechaDesde, $fechaHasta),
                'productos_vendidos' => $pedidoModel->getProductosVendidosPeriodo($vendorId, $fechaDesde, $fechaHasta),
                'promedio_pedido' => $pedidoModel->getPromedioVentaPorPedidoVendedor($vendorId, $fechaDesde, $fechaHasta)
            ];
            
            $this->setViewData('pageTitle', 'Reportes de Ventas');
            $this->setViewData('fechaDesde', $fechaDesde);
            $this->setViewData('fechaHasta', $fechaHasta);
            $this->setViewData('ventasPorDia', $ventasPorDia);
            $this->setViewData('productosMasVendidos', $productosMasVendidos);
            $this->setViewData('estadisticasPeriodo', $estadisticasPeriodo);
            $this->setViewData('breadcrumb', [
                ['name' => 'Dashboard', 'url' => '/vendedor/dashboard'],
                ['name' => 'Reportes', 'url' => '/vendedor/reportes']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in VendorController::reports: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar reportes');
            $this->setViewData('estadisticasPeriodo', []);
        }
        
        $this->render('vendor/reports');
    }
    
    // MÉTODOS PRIVADOS
    
    /**
     * Validar datos del producto
     */
    private function validateProductData($data, $isEdit = false) {
        $errors = [];
        
        if (empty($data['nombre'])) {
            $errors['nombre'] = 'El nombre del producto es requerido';
        }
        
        if (empty($data['descripcion'])) {
            $errors['descripcion'] = 'La descripción es requerida';
        }
        
        if (empty($data['categoria'])) {
            $errors['categoria'] = 'La categoría es requerida';
        }
        
        if ($data['precio'] <= 0) {
            $errors['precio'] = 'El precio debe ser mayor a 0';
        }
        
        if ($data['stock'] < 0) {
            $errors['stock'] = 'El stock no puede ser negativo';
        }
        
        if (empty($data['unidad_medida'])) {
            $errors['unidad_medida'] = 'La unidad de medida es requerida';
        }
        
        if (!empty($data['fecha_cosecha'])) {
            $fecha = DateTime::createFromFormat('Y-m-d', $data['fecha_cosecha']);
            if (!$fecha) {
                $errors['fecha_cosecha'] = 'Fecha de cosecha no válida';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validar y subir imagen del producto
     */
    private function validateAndUploadImage($file) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Verificar tipo de archivo
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Tipo de archivo no válido. Solo se permiten JPG, PNG y WebP'];
        }
        
        // Verificar tamaño
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'El archivo es muy grande. Máximo 5MB'];
        }
        
        // Crear directorio si no existe
        $uploadDir = '../public/uploads/productos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('producto_') . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => true, 'url' => '/uploads/productos/' . $fileName];
        } else {
            return ['success' => false, 'error' => 'Error al subir el archivo'];
        }
    }
}
?>