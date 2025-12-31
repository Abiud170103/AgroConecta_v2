<?php

class Product extends BaseModel
{
    protected $table = 'productos';
    protected $fillable = [
        'vendedor_id', 'categoria_id', 'nombre', 'descripcion',
        'descripcion_corta', 'precio', 'precio_anterior', 'stock',
        'stock_minimo', 'peso', 'dimensiones', 'estado',
        'imagen_principal', 'imagenes_adicionales', 'etiquetas',
        'meta_title', 'meta_description', 'destacado'
    ];

    /**
     * Obtener productos del vendedor con filtros y paginación
     */
    public function getVendorProducts($vendorId, $filters = [], $page = 1, $perPage = 12)
    {
        $offset = ($page - 1) * $perPage;
        
        // Query base con joins necesarios
        $query = "
            SELECT 
                p.*,
                c.nombre as categoria_nombre,
                COUNT(DISTINCT r.id) as total_reseñas,
                AVG(r.calificacion) as calificacion_promedio,
                COALESCE(SUM(dp.cantidad), 0) as total_vendido
            FROM {$this->table} p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN reseñas r ON p.id = r.producto_id
            LEFT JOIN detalle_pedidos dp ON p.id = dp.producto_id
            LEFT JOIN pedidos pd ON dp.pedido_id = pd.id AND pd.estado IN ('completado', 'entregado')
            WHERE p.vendedor_id = :vendedor_id
        ";

        $params = ['vendedor_id' => $vendorId];

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $query .= " AND (p.nombre LIKE :search OR p.descripcion LIKE :search OR p.etiquetas LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['category'])) {
            $query .= " AND p.categoria_id = :categoria";
            $params['categoria'] = $filters['category'];
        }

        if (!empty($filters['status'])) {
            $query .= " AND p.estado = :estado";
            $params['estado'] = $filters['status'];
        }

        if (!empty($filters['stock'])) {
            switch ($filters['stock']) {
                case 'disponible':
                    $query .= " AND p.stock > p.stock_minimo";
                    break;
                case 'bajo':
                    $query .= " AND p.stock > 0 AND p.stock <= p.stock_minimo";
                    break;
                case 'agotado':
                    $query .= " AND p.stock <= 0";
                    break;
            }
        }

        $query .= " GROUP BY p.id";

        // Aplicar ordenamiento
        $orderBy = $this->getOrderByClause($filters['sort'] ?? 'newest');
        $query .= " ORDER BY " . $orderBy;

        // Contar total de resultados (sin paginación)
        $countQuery = "SELECT COUNT(*) as total FROM ($query) as counted_products";
        $totalResult = $this->db->query($countQuery, $params);
        $total = $totalResult[0]['total'];

        // Aplicar paginación
        $query .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $products = $this->db->query($query, $params);

        // Procesar datos adicionales
        foreach ($products as &$product) {
            $product['imagenes_adicionales'] = $product['imagenes_adicionales'] ? json_decode($product['imagenes_adicionales'], true) : [];
            $product['etiquetas'] = $product['etiquetas'] ? explode(',', $product['etiquetas']) : [];
            $product['calificacion_promedio'] = $product['calificacion_promedio'] ? (float)$product['calificacion_promedio'] : 0;
            $product['total_vendido'] = (int)$product['total_vendido'];
        }

        return [
            'products' => $products,
            'total' => (int)$total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Obtener estadísticas de productos del vendedor
     */
    public function getVendorProductStats($vendorId)
    {
        $query = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                SUM(CASE WHEN estado = 'borrador' THEN 1 ELSE 0 END) as borradores,
                SUM(CASE WHEN stock > 0 AND stock <= stock_minimo THEN 1 ELSE 0 END) as stock_bajo,
                SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as sin_stock,
                SUM(CASE WHEN destacado = 1 THEN 1 ELSE 0 END) as destacados,
                AVG(precio) as precio_promedio,
                SUM(stock) as stock_total
            FROM {$this->table}
            WHERE vendedor_id = :vendedor_id
        ";

        $result = $this->db->query($query, ['vendedor_id' => $vendorId]);
        
        if (empty($result)) {
            return [
                'total' => 0,
                'activos' => 0,
                'inactivos' => 0,
                'borradores' => 0,
                'stock_bajo' => 0,
                'sin_stock' => 0,
                'destacados' => 0,
                'precio_promedio' => 0,
                'stock_total' => 0
            ];
        }

        $stats = $result[0];
        
        // Convertir a enteros y números apropiados
        foreach (['total', 'activos', 'inactivos', 'borradores', 'stock_bajo', 'sin_stock', 'destacados', 'stock_total'] as $key) {
            $stats[$key] = (int)($stats[$key] ?? 0);
        }
        
        $stats['precio_promedio'] = (float)($stats['precio_promedio'] ?? 0);

        return $stats;
    }

    /**
     * Crear nuevo producto
     */
    public function create($data)
    {
        // Preparar datos
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        $data['fecha_actualizacion'] = date('Y-m-d H:i:s');
        $data['estado'] = $data['estado'] ?? 'borrador';
        
        // Procesar imágenes adicionales
        if (isset($data['imagenes_adicionales']) && is_array($data['imagenes_adicionales'])) {
            $data['imagenes_adicionales'] = json_encode($data['imagenes_adicionales']);
        }

        // Procesar etiquetas
        if (isset($data['etiquetas']) && is_array($data['etiquetas'])) {
            $data['etiquetas'] = implode(',', $data['etiquetas']);
        }

        return parent::create($data);
    }

    /**
     * Actualizar producto
     */
    public function update($id, $data)
    {
        $data['fecha_actualizacion'] = date('Y-m-d H:i:s');
        
        // Procesar imágenes adicionales
        if (isset($data['imagenes_adicionales']) && is_array($data['imagenes_adicionales'])) {
            $data['imagenes_adicionales'] = json_encode($data['imagenes_adicionales']);
        }

        // Procesar etiquetas
        if (isset($data['etiquetas']) && is_array($data['etiquetas'])) {
            $data['etiquetas'] = implode(',', $data['etiquetas']);
        }

        return parent::update($id, $data);
    }

    /**
     * Cambiar estado del producto
     */
    public function changeStatus($id, $status, $vendorId = null)
    {
        $query = "UPDATE {$this->table} SET estado = :estado, fecha_actualizacion = NOW() WHERE id = :id";
        $params = ['estado' => $status, 'id' => $id];

        // Verificar que el producto pertenezca al vendedor si se especifica
        if ($vendorId) {
            $query .= " AND vendedor_id = :vendedor_id";
            $params['vendedor_id'] = $vendorId;
        }

        $result = $this->db->execute($query, $params);
        
        // Registrar actividad
        if ($result) {
            $this->logProductActivity($id, 'status_changed', [
                'old_status' => $this->findById($id)['estado'] ?? null,
                'new_status' => $status
            ]);
        }

        return $result;
    }

    /**
     * Duplicar producto
     */
    public function duplicate($id, $vendorId = null)
    {
        // Obtener producto original
        $original = $this->findById($id);
        if (!$original) {
            throw new Exception('Producto no encontrado');
        }

        // Verificar que pertenezca al vendedor si se especifica
        if ($vendorId && $original['vendedor_id'] != $vendorId) {
            throw new Exception('No tienes permisos para duplicar este producto');
        }

        // Preparar datos para duplicado
        $duplicateData = $original;
        unset($duplicateData['id']);
        unset($duplicateData['fecha_creacion']);
        unset($duplicateData['fecha_actualizacion']);

        // Modificar nombre para indicar que es una copia
        $duplicateData['nombre'] .= ' (Copia)';
        $duplicateData['estado'] = 'borrador';

        // Si tiene imagen principal, copiarla
        if ($original['imagen_principal']) {
            $duplicateData['imagen_principal'] = $this->duplicateProductImage($original['imagen_principal']);
        }

        // Duplicar imágenes adicionales
        if ($original['imagenes_adicionales']) {
            $imagenesAdicionales = json_decode($original['imagenes_adicionales'], true);
            $newImagenes = [];
            
            foreach ($imagenesAdicionales as $imagen) {
                $newImagenes[] = $this->duplicateProductImage($imagen);
            }
            
            $duplicateData['imagenes_adicionales'] = json_encode($newImagenes);
        }

        return $this->create($duplicateData);
    }

    /**
     * Acciones masivas en productos
     */
    public function bulkAction($action, $productIds, $vendorId = null)
    {
        if (empty($productIds)) {
            return ['success' => false, 'message' => 'No se especificaron productos'];
        }

        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $params = $productIds;

        // Query base
        $baseQuery = "UPDATE {$this->table} SET fecha_actualizacion = NOW()";
        
        // Verificar que los productos pertenezcan al vendedor
        $whereClause = " WHERE id IN ($placeholders)";
        if ($vendorId) {
            $whereClause .= " AND vendedor_id = ?";
            $params[] = $vendorId;
        }

        switch ($action) {
            case 'activate':
                $query = $baseQuery . ", estado = 'activo'" . $whereClause;
                break;
            case 'deactivate':
                $query = $baseQuery . ", estado = 'inactivo'" . $whereClause;
                break;
            case 'delete':
                $query = "DELETE FROM {$this->table}" . $whereClause;
                break;
            default:
                return ['success' => false, 'message' => 'Acción no válida'];
        }

        try {
            $affected = $this->db->execute($query, $params);
            
            // Registrar actividad para cada producto
            if ($action !== 'delete') {
                foreach ($productIds as $productId) {
                    $this->logProductActivity($productId, 'bulk_action', ['action' => $action]);
                }
            }

            return ['success' => true, 'affected' => $affected];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error en la operación: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar stock del producto
     */
    public function updateStock($id, $newStock, $vendorId = null)
    {
        $query = "UPDATE {$this->table} SET stock = :stock, fecha_actualizacion = NOW() WHERE id = :id";
        $params = ['stock' => $newStock, 'id' => $id];

        if ($vendorId) {
            $query .= " AND vendedor_id = :vendedor_id";
            $params['vendedor_id'] = $vendorId;
        }

        $result = $this->db->execute($query, $params);
        
        if ($result) {
            $this->logProductActivity($id, 'stock_updated', [
                'old_stock' => $this->findById($id)['stock'] ?? null,
                'new_stock' => $newStock
            ]);
        }

        return $result;
    }

    /**
     * Obtener productos con stock bajo
     */
    public function getLowStockProducts($vendorId, $limit = 10)
    {
        $query = "
            SELECT p.*, c.nombre as categoria_nombre
            FROM {$this->table} p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.vendedor_id = :vendedor_id 
            AND p.stock > 0 
            AND p.stock <= p.stock_minimo
            AND p.estado = 'activo'
            ORDER BY (p.stock / p.stock_minimo) ASC
            LIMIT :limit
        ";

        return $this->db->query($query, [
            'vendedor_id' => $vendorId,
            'limit' => $limit
        ]);
    }

    /**
     * Obtener productos más vendidos
     */
    public function getTopSellingProducts($vendorId, $limit = 10, $dateFrom = null, $dateTo = null)
    {
        $query = "
            SELECT 
                p.*,
                c.nombre as categoria_nombre,
                SUM(dp.cantidad) as total_vendido,
                SUM(dp.cantidad * dp.precio_unitario) as ingresos_totales
            FROM {$this->table} p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN detalle_pedidos dp ON p.id = dp.producto_id
            LEFT JOIN pedidos pd ON dp.pedido_id = pd.id
            WHERE p.vendedor_id = :vendedor_id
            AND pd.estado IN ('completado', 'entregado')
        ";

        $params = ['vendedor_id' => $vendorId];

        if ($dateFrom) {
            $query .= " AND pd.fecha_pedido >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if ($dateTo) {
            $query .= " AND pd.fecha_pedido <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $query .= "
            GROUP BY p.id
            HAVING total_vendido > 0
            ORDER BY total_vendido DESC
            LIMIT :limit
        ";
        
        $params['limit'] = $limit;

        return $this->db->query($query, $params);
    }

    /**
     * Buscar productos para autocompletado
     */
    public function searchProducts($query, $vendorId = null, $limit = 10)
    {
        $sql = "
            SELECT id, nombre, precio, imagen_principal, stock
            FROM {$this->table}
            WHERE (nombre LIKE :query OR etiquetas LIKE :query)
            AND estado = 'activo'
        ";

        $params = ['query' => '%' . $query . '%'];

        if ($vendorId) {
            $sql .= " AND vendedor_id = :vendedor_id";
            $params['vendedor_id'] = $vendorId;
        }

        $sql .= " ORDER BY nombre ASC LIMIT :limit";
        $params['limit'] = $limit;

        return $this->db->query($sql, $params);
    }

    /**
     * Obtener historial de precios del producto
     */
    public function getPriceHistory($productId, $limit = 10)
    {
        $query = "
            SELECT precio, precio_anterior, fecha_actualizacion
            FROM historial_precios
            WHERE producto_id = :producto_id
            ORDER BY fecha_actualizacion DESC
            LIMIT :limit
        ";

        return $this->db->query($query, [
            'producto_id' => $productId,
            'limit' => $limit
        ]);
    }

    /**
     * Registrar cambio de precio en el historial
     */
    public function logPriceChange($productId, $oldPrice, $newPrice)
    {
        $query = "
            INSERT INTO historial_precios (producto_id, precio_anterior, precio_nuevo, fecha_cambio)
            VALUES (:producto_id, :precio_anterior, :precio_nuevo, NOW())
        ";

        return $this->db->execute($query, [
            'producto_id' => $productId,
            'precio_anterior' => $oldPrice,
            'precio_nuevo' => $newPrice
        ]);
    }

    /**
     * Validar datos del producto
     */
    public function validateProductData($data, $isUpdate = false)
    {
        $errors = [];

        // Validaciones requeridas
        if (empty($data['nombre'])) {
            $errors[] = 'El nombre del producto es requerido';
        } elseif (strlen($data['nombre']) < 3) {
            $errors[] = 'El nombre debe tener al menos 3 caracteres';
        } elseif (strlen($data['nombre']) > 255) {
            $errors[] = 'El nombre no debe exceder 255 caracteres';
        }

        if (empty($data['descripcion'])) {
            $errors[] = 'La descripción es requerida';
        } elseif (strlen($data['descripcion']) < 10) {
            $errors[] = 'La descripción debe tener al menos 10 caracteres';
        }

        if (empty($data['categoria_id'])) {
            $errors[] = 'La categoría es requerida';
        }

        // Validaciones numéricas
        if (!isset($data['precio']) || !is_numeric($data['precio']) || $data['precio'] < 0) {
            $errors[] = 'El precio debe ser un número válido mayor o igual a 0';
        }

        if (!isset($data['stock']) || !is_numeric($data['stock']) || $data['stock'] < 0) {
            $errors[] = 'El stock debe ser un número válido mayor o igual a 0';
        }

        if (isset($data['stock_minimo']) && (!is_numeric($data['stock_minimo']) || $data['stock_minimo'] < 0)) {
            $errors[] = 'El stock mínimo debe ser un número válido mayor o igual a 0';
        }

        if (isset($data['peso']) && $data['peso'] !== '' && (!is_numeric($data['peso']) || $data['peso'] < 0)) {
            $errors[] = 'El peso debe ser un número válido mayor o igual a 0';
        }

        // Validar estado
        $validStates = ['activo', 'inactivo', 'borrador'];
        if (isset($data['estado']) && !in_array($data['estado'], $validStates)) {
            $errors[] = 'Estado de producto no válido';
        }

        // Validar unicidad del nombre para el vendedor (si se proporciona vendor_id)
        if (isset($data['vendedor_id']) && !empty($data['nombre'])) {
            $existingQuery = "SELECT id FROM {$this->table} WHERE nombre = :nombre AND vendedor_id = :vendedor_id";
            $params = ['nombre' => $data['nombre'], 'vendedor_id' => $data['vendedor_id']];
            
            if ($isUpdate && isset($data['id'])) {
                $existingQuery .= " AND id != :id";
                $params['id'] = $data['id'];
            }
            
            $existing = $this->db->query($existingQuery, $params);
            if (!empty($existing)) {
                $errors[] = 'Ya tienes un producto con ese nombre';
            }
        }

        return $errors;
    }

    /**
     * Obtener cláusula ORDER BY según el tipo de ordenamiento
     */
    private function getOrderByClause($sort)
    {
        switch ($sort) {
            case 'oldest':
                return 'p.fecha_creacion ASC';
            case 'name':
                return 'p.nombre ASC';
            case 'price_high':
                return 'p.precio DESC';
            case 'price_low':
                return 'p.precio ASC';
            case 'popular':
                return 'total_vendido DESC, p.fecha_creacion DESC';
            case 'rating':
                return 'calificacion_promedio DESC, p.fecha_creacion DESC';
            case 'stock':
                return 'p.stock ASC';
            case 'newest':
            default:
                return 'p.fecha_creacion DESC';
        }
    }

    /**
     * Duplicar imagen de producto
     */
    private function duplicateProductImage($originalImage)
    {
        if (!$originalImage) return null;

        $originalPath = PUBLIC_PATH . '/uploads/products/' . $originalImage;
        if (!file_exists($originalPath)) return null;

        // Generar nuevo nombre
        $pathInfo = pathinfo($originalImage);
        $newName = $pathInfo['filename'] . '_copy_' . time() . '.' . $pathInfo['extension'];
        $newPath = PUBLIC_PATH . '/uploads/products/' . $newName;

        // Copiar archivo
        if (copy($originalPath, $newPath)) {
            return $newName;
        }

        return null;
    }

    /**
     * Registrar actividad del producto
     */
    private function logProductActivity($productId, $action, $data = [])
    {
        try {
            $query = "
                INSERT INTO actividad_productos (producto_id, accion, datos, fecha_actividad)
                VALUES (:producto_id, :accion, :datos, NOW())
            ";

            $this->db->execute($query, [
                'producto_id' => $productId,
                'accion' => $action,
                'datos' => json_encode($data)
            ]);
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log("Error logging product activity: " . $e->getMessage());
        }
    }
}