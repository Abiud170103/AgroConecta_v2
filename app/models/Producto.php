<?php
/**
 * Modelo Producto para AgroConecta
 * Gestiona la información de productos ofrecidos por vendedores
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'Model.php';

class Producto extends Model {
    protected $table = 'Producto';
    protected $primaryKey = 'id_producto';
    
    protected $fillable = [
        'id_usuario',
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'categoria',
        'unidad_medida',
        'origen',
        'temporada',
        'imagen_url',
        'activo',
        'destacado'
    ];
    
    /**
     * Obtiene productos con información del vendedor
     */
    public function getProductosConVendedor($conditions = [], $limit = null) {
        $query = "SELECT p.*, u.nombre as vendedor_nombre, u.apellido as vendedor_apellido, u.telefono as vendedor_telefono
                 FROM {$this->table} p
                 INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
                 WHERE p.activo = 1 AND u.activo = 1";
        
        $params = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                $query .= " AND p.{$field} = ?";
                $params[] = $value;
            }
        }
        
        $query .= " ORDER BY p.destacado DESC, p.fecha_publicacion DESC";
        
        if ($limit) {
            $query .= " LIMIT {$limit}";
        }
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Busca productos por término
     */
    public function buscarProductos($termino, $categoria = null, $limit = null) {
        $query = "SELECT p.*, u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
                 FROM {$this->table} p
                 INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
                 WHERE p.activo = 1 AND u.activo = 1
                 AND (MATCH(p.nombre, p.descripcion) AGAINST(? IN BOOLEAN MODE)
                      OR p.nombre LIKE ? 
                      OR p.descripcion LIKE ?)";
        
        $params = [$termino, "%{$termino}%", "%{$termino}%"];
        
        if ($categoria) {
            $query .= " AND p.categoria = ?";
            $params[] = $categoria;
        }
        
        $query .= " ORDER BY p.destacado DESC, p.fecha_publicacion DESC";
        
        if ($limit) {
            $query .= " LIMIT {$limit}";
        }
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Obtiene productos de un vendedor específico
     */
    public function getProductosPorVendedor($vendedorId, $incluirInactivos = false) {
        $query = "SELECT * FROM {$this->table} WHERE id_usuario = ?";
        $params = [$vendedorId];
        
        if (!$incluirInactivos) {
            $query .= " AND activo = 1";
        }
        
        $query .= " ORDER BY destacado DESC, fecha_publicacion DESC";
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Obtiene productos por categoría
     */
    public function getProductosPorCategoria($categoria, $limit = null) {
        return $this->getProductosConVendedor(['categoria' => $categoria], $limit);
    }
    
    /**
     * Obtiene productos destacados
     */
    public function getProductosDestacados($limit = 8) {
        return $this->getProductosConVendedor(['destacado' => 1], $limit);
    }
    
    /**
     * Obtiene todas las categorías disponibles
     */
    public function getCategorias() {
        $query = "SELECT categoria, COUNT(*) as total 
                 FROM {$this->table} p
                 INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
                 WHERE p.activo = 1 AND u.activo = 1
                 GROUP BY categoria 
                 ORDER BY categoria";
        
        return $this->db->select($query);
    }
    
    /**
     * Verifica disponibilidad de stock
     */
    public function verificarStock($productoId, $cantidad) {
        $query = "SELECT stock FROM {$this->table} WHERE {$this->primaryKey} = ? AND activo = 1";
        $producto = $this->db->selectOne($query, [$productoId]);
        
        if (!$producto) {
            return false;
        }
        
        return $producto['stock'] >= $cantidad;
    }
    
    /**
     * Actualiza stock del producto
     */
    public function actualizarStock($productoId, $cantidad, $operacion = 'restar') {
        $operador = $operacion === 'sumar' ? '+' : '-';
        
        $query = "UPDATE {$this->table} 
                 SET stock = stock {$operador} ?, fecha_actualizacion = NOW() 
                 WHERE {$this->primaryKey} = ?";
        
        return $this->db->update($query, [$cantidad, $productoId]);
    }
    
    /**
     * Obtiene productos con stock bajo
     */
    public function getProductosStockBajo($vendedorId = null, $umbral = 10) {
        $query = "SELECT p.*, u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
                 FROM {$this->table} p
                 INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
                 WHERE p.activo = 1 AND u.activo = 1 AND p.stock <= ?";
        
        $params = [$umbral];
        
        if ($vendedorId) {
            $query .= " AND p.id_usuario = ?";
            $params[] = $vendedorId;
        }
        
        $query .= " ORDER BY p.stock ASC";
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Marca/desmarca producto como destacado
     */
    public function toggleDestacado($productoId) {
        $query = "UPDATE {$this->table} 
                 SET destacado = NOT destacado, fecha_actualizacion = NOW() 
                 WHERE {$this->primaryKey} = ?";
        
        return $this->db->update($query, [$productoId]);
    }
    
    /**
     * Desactiva producto (soft delete)
     */
    public function desactivarProducto($productoId) {
        $query = "UPDATE {$this->table} 
                 SET activo = 0, fecha_actualizacion = NOW() 
                 WHERE {$this->primaryKey} = ?";
        
        return $this->db->update($query, [$productoId]);
    }
    
    /**
     * Reactiva producto
     */
    public function reactivarProducto($productoId) {
        $query = "UPDATE {$this->table} 
                 SET activo = 1, fecha_actualizacion = NOW() 
                 WHERE {$this->primaryKey} = ?";
        
        return $this->db->update($query, [$productoId]);
    }
    
    /**
     * Obtiene productos relacionados (misma categoría)
     */
    public function getProductosRelacionados($productoId, $limit = 4) {
        $query = "SELECT p2.*, u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
                 FROM {$this->table} p1
                 INNER JOIN {$this->table} p2 ON p1.categoria = p2.categoria
                 INNER JOIN Usuario u ON p2.id_usuario = u.id_usuario
                 WHERE p1.{$this->primaryKey} = ? 
                 AND p2.{$this->primaryKey} != ? 
                 AND p2.activo = 1 AND u.activo = 1
                 ORDER BY p2.destacado DESC, RAND()
                 LIMIT ?";
        
        return $this->db->select($query, [$productoId, $productoId, $limit]);
    }
    
    /**
     * Obtiene estadísticas de productos
     */
    public function getStats($vendedorId = null) {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN destacado = 1 THEN 1 ELSE 0 END) as destacados,
                    AVG(precio) as precio_promedio,
                    SUM(stock) as stock_total
                 FROM {$this->table}";
        
        $params = [];
        
        if ($vendedorId) {
            $query .= " WHERE id_usuario = ?";
            $params[] = $vendedorId;
        }
        
        return $this->db->selectOne($query, $params);
    }
}
?>