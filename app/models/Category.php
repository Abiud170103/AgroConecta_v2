<?php

class Category extends BaseModel
{
    protected $table = 'categorias';
    protected $fillable = [
        'nombre', 'descripcion', 'slug', 'icono', 'imagen',
        'activo', 'orden', 'meta_title', 'meta_description'
    ];

    /**
     * Obtener todas las categorías activas
     */
    public function getAll($activeOnly = true)
    {
        $query = "SELECT * FROM {$this->table}";
        $params = [];

        if ($activeOnly) {
            $query .= " WHERE activo = 1";
        }

        $query .= " ORDER BY orden ASC, nombre ASC";

        return $this->db->query($query, $params);
    }

    /**
     * Obtener categorías con contador de productos
     */
    public function getCategoriesWithProductCount($vendorId = null)
    {
        $query = "
            SELECT 
                c.*,
                COUNT(p.id) as productos_count,
                COUNT(CASE WHEN p.estado = 'activo' THEN 1 END) as productos_activos
            FROM {$this->table} c
            LEFT JOIN productos p ON c.id = p.categoria_id
        ";

        $params = [];

        if ($vendorId) {
            $query .= " AND p.vendedor_id = :vendedor_id";
            $params['vendedor_id'] = $vendorId;
        }

        $query .= "
            WHERE c.activo = 1
            GROUP BY c.id
            ORDER BY c.orden ASC, c.nombre ASC
        ";

        return $this->db->query($query, $params);
    }

    /**
     * Obtener categoría por slug
     */
    public function findBySlug($slug)
    {
        $query = "SELECT * FROM {$this->table} WHERE slug = :slug AND activo = 1";
        $result = $this->db->query($query, ['slug' => $slug]);
        
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Crear nueva categoría
     */
    public function create($data)
    {
        // Generar slug automático si no se proporciona
        if (empty($data['slug']) && !empty($data['nombre'])) {
            $data['slug'] = $this->generateSlug($data['nombre']);
        }

        $data['activo'] = $data['activo'] ?? 1;
        $data['orden'] = $data['orden'] ?? $this->getNextOrder();

        return parent::create($data);
    }

    /**
     * Actualizar categoría
     */
    public function update($id, $data)
    {
        // Generar nuevo slug si cambió el nombre
        if (!empty($data['nombre']) && empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['nombre'], $id);
        }

        return parent::update($id, $data);
    }

    /**
     * Obtener categorías populares (con más productos)
     */
    public function getPopularCategories($limit = 10)
    {
        $query = "
            SELECT 
                c.*,
                COUNT(p.id) as productos_count
            FROM {$this->table} c
            LEFT JOIN productos p ON c.id = p.categoria_id AND p.estado = 'activo'
            WHERE c.activo = 1
            GROUP BY c.id
            HAVING productos_count > 0
            ORDER BY productos_count DESC, c.nombre ASC
            LIMIT :limit
        ";

        return $this->db->query($query, ['limit' => $limit]);
    }

    /**
     * Obtener estadísticas de la categoría
     */
    public function getCategoryStats($categoryId)
    {
        $query = "
            SELECT 
                COUNT(p.id) as total_productos,
                COUNT(CASE WHEN p.estado = 'activo' THEN 1 END) as productos_activos,
                COUNT(DISTINCT p.vendedor_id) as vendedores_count,
                AVG(p.precio) as precio_promedio,
                MIN(p.precio) as precio_minimo,
                MAX(p.precio) as precio_maximo
            FROM productos p
            WHERE p.categoria_id = :categoria_id
        ";

        $result = $this->db->query($query, ['categoria_id' => $categoryId]);
        
        if (empty($result)) {
            return [
                'total_productos' => 0,
                'productos_activos' => 0,
                'vendedores_count' => 0,
                'precio_promedio' => 0,
                'precio_minimo' => 0,
                'precio_maximo' => 0
            ];
        }

        $stats = $result[0];
        
        // Convertir a tipos apropiados
        foreach (['total_productos', 'productos_activos', 'vendedores_count'] as $key) {
            $stats[$key] = (int)($stats[$key] ?? 0);
        }
        
        foreach (['precio_promedio', 'precio_minimo', 'precio_maximo'] as $key) {
            $stats[$key] = (float)($stats[$key] ?? 0);
        }

        return $stats;
    }

    /**
     * Buscar categorías
     */
    public function search($query, $limit = 10)
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE (nombre LIKE :query OR descripcion LIKE :query)
            AND activo = 1
            ORDER BY 
                CASE 
                    WHEN nombre LIKE :exact_query THEN 1
                    WHEN nombre LIKE :start_query THEN 2
                    ELSE 3
                END,
                nombre ASC
            LIMIT :limit
        ";

        return $this->db->query($sql, [
            'query' => '%' . $query . '%',
            'exact_query' => $query,
            'start_query' => $query . '%',
            'limit' => $limit
        ]);
    }

    /**
     * Validar datos de categoría
     */
    public function validateCategoryData($data, $isUpdate = false, $currentId = null)
    {
        $errors = [];

        // Validar nombre
        if (empty($data['nombre'])) {
            $errors[] = 'El nombre de la categoría es requerido';
        } elseif (strlen($data['nombre']) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres';
        } elseif (strlen($data['nombre']) > 100) {
            $errors[] = 'El nombre no debe exceder 100 caracteres';
        }

        // Validar unicidad del nombre
        if (!empty($data['nombre'])) {
            $existingQuery = "SELECT id FROM {$this->table} WHERE nombre = :nombre";
            $params = ['nombre' => $data['nombre']];
            
            if ($isUpdate && $currentId) {
                $existingQuery .= " AND id != :id";
                $params['id'] = $currentId;
            }
            
            $existing = $this->db->query($existingQuery, $params);
            if (!empty($existing)) {
                $errors[] = 'Ya existe una categoría con ese nombre';
            }
        }

        // Validar slug si se proporciona
        if (!empty($data['slug'])) {
            if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $data['slug'])) {
                $errors[] = 'El slug debe contener solo letras minúsculas, números y guiones';
            }
            
            // Validar unicidad del slug
            $existingSlugQuery = "SELECT id FROM {$this->table} WHERE slug = :slug";
            $slugParams = ['slug' => $data['slug']];
            
            if ($isUpdate && $currentId) {
                $existingSlugQuery .= " AND id != :id";
                $slugParams['id'] = $currentId;
            }
            
            $existingSlug = $this->db->query($existingSlugQuery, $slugParams);
            if (!empty($existingSlug)) {
                $errors[] = 'Ya existe una categoría con ese slug';
            }
        }

        // Validar orden
        if (isset($data['orden']) && (!is_numeric($data['orden']) || $data['orden'] < 0)) {
            $errors[] = 'El orden debe ser un número entero positivo';
        }

        // Validar descripción
        if (!empty($data['descripcion']) && strlen($data['descripcion']) > 500) {
            $errors[] = 'La descripción no debe exceder 500 caracteres';
        }

        return $errors;
    }

    /**
     * Generar slug único
     */
    private function generateSlug($text, $excludeId = null)
    {
        // Convertir a minúsculas y reemplazar caracteres especiales
        $slug = strtolower($text);
        $slug = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Verificar unicidad
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Verificar si existe un slug
     */
    private function slugExists($slug, $excludeId = null)
    {
        $query = "SELECT id FROM {$this->table} WHERE slug = :slug";
        $params = ['slug' => $slug];

        if ($excludeId) {
            $query .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = $this->db->query($query, $params);
        return !empty($result);
    }

    /**
     * Obtener el siguiente número de orden
     */
    private function getNextOrder()
    {
        $query = "SELECT COALESCE(MAX(orden), 0) + 1 as next_order FROM {$this->table}";
        $result = $this->db->query($query);
        
        return $result ? (int)$result[0]['next_order'] : 1;
    }

    /**
     * Reordenar categorías
     */
    public function reorder($categoryOrders)
    {
        if (empty($categoryOrders) || !is_array($categoryOrders)) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            foreach ($categoryOrders as $categoryId => $order) {
                $query = "UPDATE {$this->table} SET orden = :orden WHERE id = :id";
                $this->db->execute($query, [
                    'orden' => (int)$order,
                    'id' => (int)$categoryId
                ]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Obtener productos de una categoría
     */
    public function getProducts($categoryId, $limit = 12, $offset = 0, $activeOnly = true)
    {
        $query = "
            SELECT p.*, u.nombre as vendedor_nombre
            FROM productos p
            LEFT JOIN usuarios u ON p.vendedor_id = u.id
            WHERE p.categoria_id = :categoria_id
        ";

        $params = ['categoria_id' => $categoryId];

        if ($activeOnly) {
            $query .= " AND p.estado = 'activo'";
        }

        $query .= " ORDER BY p.fecha_creacion DESC LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->db->query($query, $params);
    }

    /**
     * Contar productos de una categoría
     */
    public function countProducts($categoryId, $activeOnly = true)
    {
        $query = "SELECT COUNT(*) as count FROM productos WHERE categoria_id = :categoria_id";
        $params = ['categoria_id' => $categoryId];

        if ($activeOnly) {
            $query .= " AND estado = 'activo'";
        }

        $result = $this->db->query($query, $params);
        return $result ? (int)$result[0]['count'] : 0;
    }

    /**
     * Activar/desactivar categoría
     */
    public function toggleStatus($id, $status)
    {
        $validStatuses = [0, 1];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET activo = :activo WHERE id = :id";
        return $this->db->execute($query, [
            'activo' => $status,
            'id' => $id
        ]);
    }

    /**
     * Eliminar categoría (solo si no tiene productos)
     */
    public function delete($id)
    {
        // Verificar si tiene productos asociados
        $productCount = $this->countProducts($id, false); // Contar todos, incluso inactivos
        
        if ($productCount > 0) {
            throw new Exception('No se puede eliminar una categoría que tiene productos asociados');
        }

        return parent::delete($id);
    }

    /**
     * Obtener breadcrumb de la categoría
     */
    public function getBreadcrumb($categoryId)
    {
        $category = $this->findById($categoryId);
        if (!$category) {
            return [];
        }

        // Por ahora solo devolvemos la categoría actual
        // En el futuro se puede implementar jerarquía de categorías
        return [
            [
                'id' => $category['id'],
                'nombre' => $category['nombre'],
                'slug' => $category['slug']
            ]
        ];
    }
}