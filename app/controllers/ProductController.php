<?php
/**
 * ProductController - Controlador de productos
 * Maneja la visualización, búsqueda y gestión de productos
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'BaseController.php';
require_once APP_PATH . '/models/Producto.php';
require_once APP_PATH . '/models/Usuario.php';
require_once APP_PATH . '/models/Resena.php';

class ProductController extends BaseController {
    
    /**
     * Lista todos los productos con filtros
     */
    public function index() {
        $productoModel = new Producto();
        
        // Parámetros de filtrado y paginación
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 12;
        $categoria = $this->sanitizeInput($_GET['categoria'] ?? '');
        $ordenar = $this->sanitizeInput($_GET['ordenar'] ?? 'reciente');
        $precioMin = floatval($_GET['precio_min'] ?? 0);
        $precioMax = floatval($_GET['precio_max'] ?? 0);
        $vendedor = intval($_GET['vendedor'] ?? 0);
        
        try {
            // Obtener productos con filtros
            $filtros = [
                'activo' => 1,
                'categoria' => $categoria,
                'precio_min' => $precioMin > 0 ? $precioMin : null,
                'precio_max' => $precioMax > 0 ? $precioMax : null,
                'vendedor' => $vendedor > 0 ? $vendedor : null
            ];
            
            $offset = ($page - 1) * $perPage;
            $productos = $productoModel->getProductosPaginados($filtros, $ordenar, $perPage, $offset);
            $totalProductos = $productoModel->countProductos($filtros);
            $totalPaginas = ceil($totalProductos / $perPage);
            
            // Obtener categorías para el filtro
            $categorias = $productoModel->getCategorias();
            
            // Obtener rangos de precios para el filtro
            $rangosPrecios = $productoModel->getRangosPrecios();
            
            $this->setViewData('pageTitle', 'Productos Agrícolas Frescos');
            $this->setViewData('productos', $productos);
            $this->setViewData('categorias', $categorias);
            $this->setViewData('rangosPrecios', $rangosPrecios);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'perPage' => $perPage,
                'totalItems' => $totalProductos
            ]);
            $this->setViewData('filtros', [
                'categoria' => $categoria,
                'ordenar' => $ordenar,
                'precio_min' => $precioMin,
                'precio_max' => $precioMax,
                'vendedor' => $vendedor
            ]);
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Productos', 'url' => '/productos']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in ProductController::index: " . $e->getMessage());
            
            $this->setFlashMessage('error', 'Error al cargar productos');
            $this->setViewData('productos', []);
            $this->setViewData('categorias', []);
            $this->setViewData('pagination', ['current' => 1, 'total' => 1]);
        }
        
        $this->render('products/index');
    }
    
    /**
     * Muestra un producto específico
     */
    public function show($id) {
        $id = intval($id);
        
        if ($id <= 0) {
            $this->setFlashMessage('error', 'Producto no encontrado');
            $this->redirect('/productos');
            return;
        }
        
        try {
            $productoModel = new Producto();
            $resenaModel = new Resena();
            
            $producto = $productoModel->getProductoCompleto($id);
            
            if (!$producto || !$producto['activo']) {
                $this->setFlashMessage('error', 'Producto no disponible');
                $this->redirect('/productos');
                return;
            }
            
            // Obtener reseñas del producto
            $resenas = $resenaModel->getResenasByProducto($id, 1, 10);
            $estadisticasResenas = $resenaModel->getEstadisticasProducto($id);
            
            // Productos relacionados
            $productosRelacionados = $productoModel->getProductosRelacionados($id, $producto['categoria'], 4);
            
            // Verificar si el usuario puede reseñar
            $puedeResenar = false;
            if ($this->isAuthenticated()) {
                $puedeResenar = $resenaModel->puedeResenar($this->getCurrentUserId(), $id);
            }
            
            // Incrementar vista del producto
            $productoModel->incrementarVistas($id);
            
            $this->setViewData('pageTitle', $producto['nombre']);
            $this->setViewData('producto', $producto);
            $this->setViewData('resenas', $resenas);
            $this->setViewData('estadisticasResenas', $estadisticasResenas);
            $this->setViewData('productosRelacionados', $productosRelacionados);
            $this->setViewData('puedeResenar', $puedeResenar);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Productos', 'url' => '/productos'],
                ['name' => $producto['nombre'], 'url' => '/productos/ver/' . $id]
            ]);
            
        } catch (Exception $e) {
            error_log("Error in ProductController::show: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar el producto');
            $this->redirect('/productos');
            return;
        }
        
        $this->render('products/show');
    }
    
    /**
     * Buscar productos
     */
    public function search() {
        $query = $this->sanitizeInput($_GET['q'] ?? '');
        $categoria = $this->sanitizeInput($_GET['categoria'] ?? '');
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 12;
        
        if (strlen($query) < 2 && empty($categoria)) {
            $this->redirect('/productos');
            return;
        }
        
        try {
            $productoModel = new Producto();
            
            $offset = ($page - 1) * $perPage;
            $productos = $productoModel->buscarProductos($query, $categoria, $perPage, $offset);
            $totalProductos = $productoModel->countBusqueda($query, $categoria);
            $totalPaginas = ceil($totalProductos / $perPage);
            
            $categorias = $productoModel->getCategorias();
            
            $this->setViewData('pageTitle', 'Búsqueda: ' . ($query ?: $categoria));
            $this->setViewData('productos', $productos);
            $this->setViewData('categorias', $categorias);
            $this->setViewData('query', $query);
            $this->setViewData('categoriaActual', $categoria);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'totalItems' => $totalProductos
            ]);
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Productos', 'url' => '/productos'],
                ['name' => 'Búsqueda', 'url' => '/productos/buscar']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in ProductController::search: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error en la búsqueda');
            $this->redirect('/productos');
            return;
        }
        
        $this->render('products/search');
    }
    
    /**
     * Lista productos por categoría
     */
    public function category($categoria) {
        $categoria = urldecode($categoria);
        
        try {
            $productoModel = new Producto();
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 12;
            $ordenar = $this->sanitizeInput($_GET['ordenar'] ?? 'reciente');
            
            $filtros = [
                'activo' => 1,
                'categoria' => $categoria
            ];
            
            $offset = ($page - 1) * $perPage;
            $productos = $productoModel->getProductosPaginados($filtros, $ordenar, $perPage, $offset);
            $totalProductos = $productoModel->countProductos($filtros);
            $totalPaginas = ceil($totalProductos / $perPage);
            
            // Verificar que la categoría existe
            $categorias = $productoModel->getCategorias();
            if (!in_array($categoria, $categorias)) {
                $this->setFlashMessage('error', 'Categoría no encontrada');
                $this->redirect('/productos');
                return;
            }
            
            $this->setViewData('pageTitle', 'Productos de ' . ucfirst($categoria));
            $this->setViewData('productos', $productos);
            $this->setViewData('categoria', $categoria);
            $this->setViewData('ordenar', $ordenar);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'totalItems' => $totalProductos
            ]);
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Productos', 'url' => '/productos'],
                ['name' => ucfirst($categoria), 'url' => '/productos/categoria/' . urlencode($categoria)]
            ]);
            
        } catch (Exception $e) {
            error_log("Error in ProductController::category: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar productos de la categoría');
            $this->redirect('/productos');
            return;
        }
        
        $this->render('products/category');
    }
    
    /**
     * Agregar reseña a un producto (requiere autenticación)
     */
    public function addReview() {
        if (!$this->requireAuth()) return;
        
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
            'calificacion' => intval($_POST['calificacion'] ?? 0),
            'comentario' => $_POST['comentario'] ?? ''
        ]);
        
        // Validaciones
        if ($data['producto_id'] <= 0) {
            $this->jsonError('Producto no válido');
            return;
        }
        
        if ($data['calificacion'] < 1 || $data['calificacion'] > 5) {
            $this->jsonError('La calificación debe ser entre 1 y 5 estrellas');
            return;
        }
        
        if (empty($data['comentario']) || strlen($data['comentario']) < 10) {
            $this->jsonError('El comentario debe tener al menos 10 caracteres');
            return;
        }
        
        try {
            $resenaModel = new Resena();
            $productoModel = new Producto();
            
            // Verificar que el producto existe
            $producto = $productoModel->find($data['producto_id']);
            if (!$producto) {
                $this->jsonError('Producto no encontrado');
                return;
            }
            
            // Verificar que el usuario puede reseñar
            if (!$resenaModel->puedeResenar($this->getCurrentUserId(), $data['producto_id'])) {
                $this->jsonError('No puedes reseñar este producto');
                return;
            }
            
            // Crear la reseña
            $resenaData = [
                'id_usuario' => $this->getCurrentUserId(),
                'id_producto' => $data['producto_id'],
                'calificacion' => $data['calificacion'],
                'comentario' => $data['comentario'],
                'fecha_resena' => date('Y-m-d H:i:s')
            ];
            
            $resenaId = $resenaModel->create($resenaData);
            
            if ($resenaId) {
                $this->logActivity('product_review_added', "Product: {$data['producto_id']} - Rating: {$data['calificacion']}");
                $this->jsonSuccess('Reseña agregada correctamente', ['id' => $resenaId]);
            } else {
                $this->jsonError('Error al guardar la reseña');
            }
            
        } catch (Exception $e) {
            error_log("Error adding review: " . $e->getMessage());
            $this->jsonError('Error interno del servidor', 500);
        }
    }
    
    /**
     * Obtener productos para autocompletado (AJAX)
     */
    public function autocomplete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonError('Método no permitido', 405);
            return;
        }
        
        $query = $this->sanitizeInput($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            $this->jsonSuccess('OK', []);
            return;
        }
        
        try {
            $productoModel = new Producto();
            $productos = $productoModel->buscarProductos($query, null, 8);
            
            $results = [];
            foreach ($productos as $producto) {
                $results[] = [
                    'id' => $producto['id_producto'],
                    'name' => $producto['nombre'],
                    'category' => $producto['categoria'],
                    'price' => number_format($producto['precio'], 2),
                    'vendor' => $producto['vendedor_nombre'] ?? 'Vendedor',
                    'image' => $producto['imagen_url'] ?? '/images/default-product.jpg'
                ];
            }
            
            $this->jsonSuccess('OK', $results);
            
        } catch (Exception $e) {
            error_log("Error in autocomplete: " . $e->getMessage());
            $this->jsonError('Error interno del servidor', 500);
        }
    }
    
    /**
     * Comparar productos (máximo 3)
     */
    public function compare() {
        $ids = $_GET['ids'] ?? '';
        $productIds = array_filter(array_map('intval', explode(',', $ids)));
        
        if (count($productIds) < 2 || count($productIds) > 3) {
            $this->setFlashMessage('error', 'Puedes comparar entre 2 y 3 productos');
            $this->redirect('/productos');
            return;
        }
        
        try {
            $productoModel = new Producto();
            $productos = [];
            
            foreach ($productIds as $id) {
                $producto = $productoModel->getProductoCompleto($id);
                if ($producto && $producto['activo']) {
                    $productos[] = $producto;
                }
            }
            
            if (count($productos) < 2) {
                $this->setFlashMessage('error', 'No se encontraron productos válidos para comparar');
                $this->redirect('/productos');
                return;
            }
            
            $this->setViewData('pageTitle', 'Comparar Productos');
            $this->setViewData('productos', $productos);
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Productos', 'url' => '/productos'],
                ['name' => 'Comparar', 'url' => '/productos/comparar']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in ProductController::compare: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al comparar productos');
            $this->redirect('/productos');
            return;
        }
        
        $this->render('products/compare');
    }
    
    // MÉTODOS PARA VENDEDORES (requieren autenticación y permisos)
    
    /**
     * Formulario para crear nuevo producto (solo vendedores)
     */
    public function create() {
        if (!$this->requireAuth()) return;
        
        $currentUser = $this->getCurrentUser();
        if ($currentUser['tipo_usuario'] !== 'vendedor') {
            $this->setFlashMessage('error', 'Solo los vendedores pueden crear productos');
            $this->redirect('/dashboard');
            return;
        }
        
        $this->setViewData('pageTitle', 'Crear Producto');
        $this->setViewData('csrf_token', $this->generateCSRF());
        $this->setViewData('errors', $_SESSION['product_errors'] ?? []);
        $this->setViewData('oldData', $_SESSION['product_data'] ?? []);
        $this->setViewData('breadcrumb', [
            ['name' => 'Inicio', 'url' => '/'],
            ['name' => 'Dashboard', 'url' => '/dashboard'],
            ['name' => 'Crear Producto', 'url' => '/productos/crear']
        ]);
        
        // Limpiar errores de sesión
        unset($_SESSION['product_errors'], $_SESSION['product_data']);
        
        $this->render('products/create');
    }
    
    /**
     * Procesar creación de producto
     */
    public function store() {
        if (!$this->requireAuth()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/productos/crear');
            return;
        }
        
        $currentUser = $this->getCurrentUser();
        if ($currentUser['tipo_usuario'] !== 'vendedor') {
            $this->setFlashMessage('error', 'No tienes permisos para crear productos');
            $this->redirect('/dashboard');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        // TODO: Implementar lógica de creación de productos
        // Validar datos, subir imágenes, crear producto en BD
        
        $this->setFlashMessage('success', 'Producto creado correctamente');
        $this->redirect('/dashboard/productos');
    }
}
?>