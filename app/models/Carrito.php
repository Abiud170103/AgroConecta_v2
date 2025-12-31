<?php
/**
 * Modelo Carrito para AgroConecta
 * Gestiona el carrito de compras temporal de los usuarios
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'Model.php';

class Carrito extends Model {
    protected $table = 'Carrito';
    protected $primaryKey = 'id_carrito';
    
    protected $fillable = [
        'id_usuario',
        'id_producto',
        'cantidad'
    ];
    
    protected $timestamps = false;
    
    /**
     * Obtiene items del carrito con información de productos
     */
    public function getItemsCarrito($userId) {
        $query = "SELECT c.*, p.nombre, p.precio, p.stock, p.imagen_url, p.unidad_medida,
                        (c.cantidad * p.precio) as subtotal,
                        u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
                 FROM {$this->table} c
                 INNER JOIN Producto p ON c.id_producto = p.id_producto
                 INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
                 WHERE c.id_usuario = ? AND p.activo = 1 AND u.activo = 1
                 ORDER BY c.fecha_agregado DESC";
        
        return $this->db->select($query, [$userId]);
    }
    
    /**
     * Agrega un producto al carrito
     */
    public function agregarProducto($userId, $productoId, $cantidad = 1) {
        // Verificar si el producto ya está en el carrito
        $itemExistente = $this->findByUserAndProduct($userId, $productoId);
        
        if ($itemExistente) {
            // Si existe, actualizar cantidad
            return $this->actualizarCantidad($itemExistente['id_carrito'], 
                                           $itemExistente['cantidad'] + $cantidad);
        } else {
            // Si no existe, crear nuevo item
            return $this->create([
                'id_usuario' => $userId,
                'id_producto' => $productoId,
                'cantidad' => $cantidad
            ]);
        }
    }
    
    /**
     * Busca item por usuario y producto
     */
    public function findByUserAndProduct($userId, $productoId) {
        $query = "SELECT * FROM {$this->table} WHERE id_usuario = ? AND id_producto = ?";
        return $this->db->selectOne($query, [$userId, $productoId]);
    }
    
    /**
     * Actualiza cantidad de un item del carrito
     */
    public function actualizarCantidad($carritoId, $nuevaCantidad) {
        if ($nuevaCantidad <= 0) {
            return $this->delete($carritoId);
        }
        
        $query = "UPDATE {$this->table} SET cantidad = ? WHERE {$this->primaryKey} = ?";
        return $this->db->update($query, [$nuevaCantidad, $carritoId]);
    }
    
    /**
     * Elimina un producto del carrito
     */
    public function eliminarProducto($userId, $productoId) {
        $query = "DELETE FROM {$this->table} WHERE id_usuario = ? AND id_producto = ?";
        return $this->db->delete($query, [$userId, $productoId]);
    }
    
    /**
     * Vacía el carrito de un usuario
     */
    public function vaciarCarrito($userId) {
        $query = "DELETE FROM {$this->table} WHERE id_usuario = ?";
        return $this->db->delete($query, [$userId]);
    }
    
    /**
     * Calcula el total del carrito
     */
    public function calcularTotal($userId) {
        $query = "SELECT SUM(c.cantidad * p.precio) as total
                 FROM {$this->table} c
                 INNER JOIN Producto p ON c.id_producto = p.id_producto
                 WHERE c.id_usuario = ? AND p.activo = 1";
        
        $result = $this->db->selectOne($query, [$userId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Cuenta items en el carrito
     */
    public function contarItems($userId) {
        $query = "SELECT SUM(cantidad) as total_items FROM {$this->table} WHERE id_usuario = ?";
        $result = $this->db->selectOne($query, [$userId]);
        return $result['total_items'] ?? 0;
    }
    
    /**
     * Verifica disponibilidad de stock para todos los items
     */
    public function verificarDisponibilidad($userId) {
        $query = "SELECT c.*, p.nombre, p.stock
                 FROM {$this->table} c
                 INNER JOIN Producto p ON c.id_producto = p.id_producto
                 WHERE c.id_usuario = ? AND (p.stock < c.cantidad OR p.activo = 0)";
        
        return $this->db->select($query, [$userId]);
    }
    
    /**
     * Obtiene productos agrupados por vendedor
     */
    public function getItemsPorVendedor($userId) {
        $query = "SELECT u.id_usuario as id_vendedor, u.nombre as vendedor_nombre, 
                        u.apellido as vendedor_apellido,
                        GROUP_CONCAT(CONCAT(p.nombre, ' x', c.cantidad) SEPARATOR ', ') as productos,
                        SUM(c.cantidad * p.precio) as subtotal_vendedor
                 FROM {$this->table} c
                 INNER JOIN Producto p ON c.id_producto = p.id_producto
                 INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
                 WHERE c.id_usuario = ? AND p.activo = 1 AND u.activo = 1
                 GROUP BY u.id_usuario, u.nombre, u.apellido
                 ORDER BY subtotal_vendedor DESC";
        
        return $this->db->select($query, [$userId]);
    }
    
    /**
     * Limpia items inactivos o sin stock
     */
    public function limpiarItemsInvalidos($userId) {
        $query = "DELETE c FROM {$this->table} c
                 INNER JOIN Producto p ON c.id_producto = p.id_producto
                 WHERE c.id_usuario = ? AND (p.activo = 0 OR p.stock = 0)";
        
        return $this->db->delete($query, [$userId]);
    }
    
    /**
     * Obtiene resumen del carrito
     */
    public function getResumenCarrito($userId) {
        $items = $this->getItemsCarrito($userId);
        
        $resumen = [
            'items' => $items,
            'total_items' => 0,
            'subtotal' => 0,
            'total_productos' => 0
        ];
        
        foreach ($items as $item) {
            $resumen['total_items'] += $item['cantidad'];
            $resumen['subtotal'] += $item['subtotal'];
            $resumen['total_productos']++;
        }
        
        return $resumen;
    }
    
    /**
     * Limpia carritos antiguos (más de 30 días sin actividad)
     */
    public function limpiarCarritosAntiguos($dias = 30) {
        $query = "DELETE FROM {$this->table} 
                 WHERE fecha_agregado < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        return $this->db->delete($query, [$dias]);
    }
}
?>