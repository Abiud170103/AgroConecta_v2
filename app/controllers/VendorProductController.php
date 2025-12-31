<?php

require_once 'BaseController.php';
require_once '../app/models/Product.php';
require_once '../app/models/Category.php';
require_once '../app/models/User.php';

class VendorProductController extends BaseController
{
    private $productModel;
    private $categoryModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->userModel = new User();
    }

    /**
     * Lista de productos del vendedor
     */
    public function index()
    {
        // Verificar autenticación de vendedor
        if (!$this->isVendor()) {
            redirect('/auth/login');
        }

        $this->render('vendor/products/index');
    }

    /**
     * Mostrar formulario para crear producto
     */
    public function create()
    {
        if (!$this->isVendor()) {
            redirect('/auth/login');
        }

        // Obtener categorías para el formulario
        $categories = $this->categoryModel->getAll();
        
        $this->render('vendor/products/create', [
            'categories' => $categories,
            'product' => null,
            'errors' => []
        ]);
    }

    /**
     * Procesar creación de producto
     */
    public function store()
    {
        if (!$this->isVendor()) {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/vendor/products/create');
        }

        try {
            // Obtener datos del formulario
            $data = $this->getProductDataFromRequest();
            $data['vendedor_id'] = $_SESSION['user_id'];

            // Validar datos
            $errors = $this->productModel->validateProductData($data);
            if (!empty($errors)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errors
                ], 400);
            }

            // Procesar imagen principal
            if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
                $imageResult = $this->uploadProductImage($_FILES['imagen_principal'], 'main');
                if ($imageResult['success']) {
                    $data['imagen_principal'] = $imageResult['filename'];
                } else {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'Error al subir imagen: ' . $imageResult['message']
                    ], 400);
                }
            }

            // Procesar imágenes adicionales
            $imagenesAdicionales = [];
            if (isset($_FILES['imagenes_adicionales'])) {
                foreach ($_FILES['imagenes_adicionales']['tmp_name'] as $index => $tmpName) {
                    if ($_FILES['imagenes_adicionales']['error'][$index] === UPLOAD_ERR_OK) {
                        $fileData = [
                            'tmp_name' => $tmpName,
                            'name' => $_FILES['imagenes_adicionales']['name'][$index],
                            'type' => $_FILES['imagenes_adicionales']['type'][$index],
                            'size' => $_FILES['imagenes_adicionales']['size'][$index],
                            'error' => $_FILES['imagenes_adicionales']['error'][$index]
                        ];
                        
                        $imageResult = $this->uploadProductImage($fileData, 'additional');
                        if ($imageResult['success']) {
                            $imagenesAdicionales[] = $imageResult['filename'];
                        }
                    }
                }
            }
            
            $data['imagenes_adicionales'] = $imagenesAdicionales;

            // Crear producto
            $productId = $this->productModel->create($data);
            
            if ($productId) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Producto creado exitosamente',
                    'product_id' => $productId,
                    'redirect' => '/vendor/products/' . $productId . '/edit'
                ]);
            } else {
                throw new Exception('Error al crear el producto');
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al crear producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->isVendor()) {
            redirect('/auth/login');
        }

        $product = $this->productModel->findById($id);
        
        if (!$product || $product['vendedor_id'] != $_SESSION['user_id']) {
            redirect('/vendor/products');
        }

        // Procesar campos JSON
        $product['imagenes_adicionales'] = $product['imagenes_adicionales'] ? 
            json_decode($product['imagenes_adicionales'], true) : [];
        $product['etiquetas'] = $product['etiquetas'] ? 
            explode(',', $product['etiquetas']) : [];

        $categories = $this->categoryModel->getAll();
        
        $this->render('vendor/products/edit', [
            'product' => $product,
            'categories' => $categories,
            'errors' => []
        ]);
    }

    /**
     * Procesar actualización de producto
     */
    public function update($id)
    {
        if (!$this->isVendor()) {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        try {
            // Verificar propiedad del producto
            $product = $this->productModel->findById($id);
            if (!$product || $product['vendedor_id'] != $_SESSION['user_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'Producto no encontrado'], 404);
            }

            // Obtener datos del formulario
            $data = $this->getProductDataFromRequest();
            $data['id'] = $id;
            $data['vendedor_id'] = $_SESSION['user_id'];

            // Validar datos
            $errors = $this->productModel->validateProductData($data, true);
            if (!empty($errors)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errors
                ], 400);
            }

            // Procesar nueva imagen principal si se subió
            if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
                // Eliminar imagen anterior si existe
                if ($product['imagen_principal']) {
                    $this->deleteProductImage($product['imagen_principal']);
                }
                
                $imageResult = $this->uploadProductImage($_FILES['imagen_principal'], 'main');
                if ($imageResult['success']) {
                    $data['imagen_principal'] = $imageResult['filename'];
                } else {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'Error al subir imagen: ' . $imageResult['message']
                    ], 400);
                }
            }

            // Procesar imágenes adicionales
            if (isset($_FILES['imagenes_adicionales'])) {
                $imagenesActuales = $product['imagenes_adicionales'] ? 
                    json_decode($product['imagenes_adicionales'], true) : [];
                
                // Mantener imágenes existentes que no se marcaron para eliminar
                $imagenesKeep = $_POST['keep_additional_images'] ?? [];
                $imagenesFinales = [];
                
                foreach ($imagenesActuales as $imagen) {
                    if (in_array($imagen, $imagenesKeep)) {
                        $imagenesFinales[] = $imagen;
                    } else {
                        $this->deleteProductImage($imagen);
                    }
                }

                // Agregar nuevas imágenes
                foreach ($_FILES['imagenes_adicionales']['tmp_name'] as $index => $tmpName) {
                    if ($_FILES['imagenes_adicionales']['error'][$index] === UPLOAD_ERR_OK) {
                        $fileData = [
                            'tmp_name' => $tmpName,
                            'name' => $_FILES['imagenes_adicionales']['name'][$index],
                            'type' => $_FILES['imagenes_adicionales']['type'][$index],
                            'size' => $_FILES['imagenes_adicionales']['size'][$index],
                            'error' => $_FILES['imagenes_adicionales']['error'][$index]
                        ];
                        
                        $imageResult = $this->uploadProductImage($fileData, 'additional');
                        if ($imageResult['success']) {
                            $imagenesFinales[] = $imageResult['filename'];
                        }
                    }
                }
                
                $data['imagenes_adicionales'] = $imagenesFinales;
            }

            // Actualizar producto
            $result = $this->productModel->update($id, $data);
            
            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente'
                ]);
            } else {
                throw new Exception('Error al actualizar el producto');
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al actualizar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar producto
     */
    public function delete($id)
    {
        if (!$this->isVendor()) {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        try {
            // Verificar propiedad del producto
            $product = $this->productModel->findById($id);
            if (!$product || $product['vendedor_id'] != $_SESSION['user_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'Producto no encontrado'], 404);
            }

            // Verificar si tiene pedidos asociados
            if ($this->hasOrders($id)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se puede eliminar un producto que tiene pedidos asociados'
                ], 400);
            }

            // Eliminar imágenes
            if ($product['imagen_principal']) {
                $this->deleteProductImage($product['imagen_principal']);
            }
            
            if ($product['imagenes_adicionales']) {
                $imagenesAdicionales = json_decode($product['imagenes_adicionales'], true);
                foreach ($imagenesAdicionales as $imagen) {
                    $this->deleteProductImage($imagen);
                }
            }

            // Eliminar producto
            $result = $this->productModel->delete($id);
            
            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Producto eliminado exitosamente'
                ]);
            } else {
                throw new Exception('Error al eliminar el producto');
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado del producto
     */
    public function updateStatus($id)
    {
        if (!$this->isVendor()) {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $status = $input['status'] ?? '';

            $validStatuses = ['activo', 'inactivo', 'borrador'];
            if (!in_array($status, $validStatuses)) {
                $this->jsonResponse(['success' => false, 'message' => 'Estado no válido'], 400);
            }

            $result = $this->productModel->changeStatus($id, $status, $_SESSION['user_id']);
            
            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Estado actualizado exitosamente'
                ]);
            } else {
                throw new Exception('Error al cambiar el estado');
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicar producto
     */
    public function duplicate($id)
    {
        if (!$this->isVendor()) {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        try {
            $newProductId = $this->productModel->duplicate($id, $_SESSION['user_id']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Producto duplicado exitosamente',
                'newProductId' => $newProductId
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al duplicar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Acciones masivas en productos
     */
    public function bulkAction()
    {
        if (!$this->isVendor()) {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
            $productIds = $input['productIds'] ?? [];

            if (empty($productIds)) {
                $this->jsonResponse(['success' => false, 'message' => 'No se especificaron productos'], 400);
            }

            $result = $this->productModel->bulkAction($action, $productIds, $_SESSION['user_id']);
            
            $this->jsonResponse($result);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error en acción masiva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de productos
     */
    public function getStats()
    {
        if (!$this->isVendor()) {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            $stats = $this->productModel->getVendorProductStats($_SESSION['user_id']);
            $this->jsonResponse($stats);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar productos para autocompletado
     */
    public function search()
    {
        if (!$this->isVendor()) {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $query = $_GET['q'] ?? '';
        $limit = (int)($_GET['limit'] ?? 10);

        if (strlen($query) < 2) {
            $this->jsonResponse(['products' => []]);
        }

        try {
            $products = $this->productModel->searchProducts($query, $_SESSION['user_id'], $limit);
            $this->jsonResponse(['products' => $products]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error en búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar stock rápido
     */
    public function updateStock($id)
    {
        if (!$this->isVendor()) {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $stock = $input['stock'] ?? null;

            if (!is_numeric($stock) || $stock < 0) {
                $this->jsonResponse(['success' => false, 'message' => 'Stock no válido'], 400);
            }

            $result = $this->productModel->updateStock($id, (int)$stock, $_SESSION['user_id']);
            
            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Stock actualizado exitosamente'
                ]);
            } else {
                throw new Exception('Error al actualizar el stock');
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al actualizar stock: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== MÉTODOS PRIVADOS ==========

    /**
     * Verificar si el usuario es vendedor
     */
    private function isVendor()
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $user = $this->userModel->findById($_SESSION['user_id']);
        return $user && $user['tipo_usuario'] === 'vendedor';
    }

    /**
     * Obtener datos del producto desde la request
     */
    private function getProductDataFromRequest()
    {
        $data = [];
        
        // Campos básicos
        $fields = [
            'nombre', 'descripcion', 'descripcion_corta', 'categoria_id',
            'precio', 'precio_anterior', 'stock', 'stock_minimo', 'peso',
            'dimensiones', 'estado', 'destacado', 'meta_title', 'meta_description'
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = trim($_POST[$field]);
                
                // Convertir valores vacíos a null para campos numéricos opcionales
                if (in_array($field, ['precio_anterior', 'stock_minimo', 'peso']) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
        }

        // Procesar etiquetas
        if (isset($_POST['etiquetas'])) {
            if (is_array($_POST['etiquetas'])) {
                $data['etiquetas'] = $_POST['etiquetas'];
            } else {
                $data['etiquetas'] = array_map('trim', explode(',', $_POST['etiquetas']));
            }
            // Filtrar etiquetas vacías
            $data['etiquetas'] = array_filter($data['etiquetas']);
        }

        // Procesar checkbox destacado
        $data['destacado'] = isset($_POST['destacado']) ? 1 : 0;

        return $data;
    }

    /**
     * Subir imagen de producto
     */
    private function uploadProductImage($file, $type = 'main')
    {
        // Validaciones básicas
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir el archivo'];
        }

        // Validar tipo MIME
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
        }

        // Validar tamaño (máximo 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'El archivo es demasiado grande (máximo 5MB)'];
        }

        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid($type . '_') . '.' . $extension;
        $uploadPath = PUBLIC_PATH . '/uploads/products/' . $filename;

        // Crear directorio si no existe
        $uploadDir = dirname($uploadPath);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Optimizar imagen
            $this->optimizeImage($uploadPath, $mimeType);
            
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'message' => 'Error al guardar el archivo'];
        }
    }

    /**
     * Optimizar imagen
     */
    private function optimizeImage($path, $mimeType)
    {
        try {
            $maxWidth = 800;
            $maxHeight = 600;
            $quality = 85;

            list($width, $height) = getimagesize($path);
            
            // No redimensionar si ya es pequeña
            if ($width <= $maxWidth && $height <= $maxHeight) {
                return;
            }

            // Calcular nuevas dimensiones manteniendo proporción
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            // Crear imagen desde el archivo original
            switch ($mimeType) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($path);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($path);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($path);
                    break;
                default:
                    return;
            }

            // Crear imagen redimensionada
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preservar transparencia para PNG y GIF
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }

            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Guardar imagen optimizada
            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($resized, $path, $quality);
                    break;
                case 'image/png':
                    imagepng($resized, $path, 9);
                    break;
                case 'image/gif':
                    imagegif($resized, $path);
                    break;
            }

            imagedestroy($source);
            imagedestroy($resized);

        } catch (Exception $e) {
            error_log("Error optimizing image: " . $e->getMessage());
        }
    }

    /**
     * Eliminar imagen de producto
     */
    private function deleteProductImage($filename)
    {
        if ($filename) {
            $path = PUBLIC_PATH . '/uploads/products/' . $filename;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    /**
     * Verificar si el producto tiene pedidos asociados
     */
    private function hasOrders($productId)
    {
        $query = "SELECT COUNT(*) as count FROM detalle_pedidos WHERE producto_id = :producto_id";
        $result = $this->productModel->db->query($query, ['producto_id' => $productId]);
        return $result && $result[0]['count'] > 0;
    }

    /**
     * Obtener color del badge según el estado del producto
     */
    public function getProductStatusColor($status)
    {
        switch ($status) {
            case 'activo':
                return 'success';
            case 'inactivo':
                return 'secondary';
            case 'borrador':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}