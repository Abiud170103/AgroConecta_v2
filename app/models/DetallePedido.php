<?php
/**
 * Modelo DetallePedido para AgroConecta
 * Gestiona los items individuales de cada pedido
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'Model.php';

class DetallePedido extends Model {
    protected $table = 'DetallePedido';
    protected $primaryKey = 'id_detalle';
    
    protected $fillable = [
        'id_pedido',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];
    
    protected $timestamps = false; // Esta tabla no tiene timestamps
    
    /**
     * Obtiene detalles de un pedido con información de productos
     */
    public function getDetallesPorPedido($pedidoId) {
        $query = "SELECT dp.*, p.nombre as producto_nombre, p.imagen_url, p.unidad_medida,
                        u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
                 FROM {$this->table} dp
                 INNER JOIN Producto p ON dp.id_producto = p.id_producto
                 INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
                 WHERE dp.id_pedido = ?
                 ORDER BY dp.{$this->primaryKey}";
        
        return $this->db->select($query, [$pedidoId]);
    }
    
    /**
     * Calcula el subtotal de un pedido
     */
    public function calcularSubtotalPedido($pedidoId) {
        $query = "SELECT SUM(subtotal) as total FROM {$this->table} WHERE id_pedido = ?";
        $result = $this->db->selectOne($query, [$pedidoId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Cuenta items de un pedido
     */
    public function contarItemsPedido($pedidoId) {
        return $this->count(['id_pedido' => $pedidoId]);
    }
    
    /**
     * Obtiene productos más vendidos
     */
    public function getProductosMasVendidos($limite = 10, $fechaInicio = null, $fechaFin = null) {
        $query = "SELECT dp.id_producto, p.nombre, p.imagen_url, p.categoria,
                        SUM(dp.cantidad) as total_vendido,
                        COUNT(DISTINCT dp.id_pedido) as pedidos_incluidos,
                        SUM(dp.subtotal) as ingresos_totales
                 FROM {$this->table} dp
                 INNER JOIN Pedido pe ON dp.id_pedido = pe.id_pedido
                 INNER JOIN Producto p ON dp.id_producto = p.id_producto
                 WHERE pe.estado IN ('entregado', 'enviado')";
        
        $params = [];
        
        if ($fechaInicio && $fechaFin) {
            $query .= " AND DATE(pe.fecha_pedido) BETWEEN ? AND ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin;
        }
        
        $query .= " GROUP BY dp.id_producto, p.nombre, p.imagen_url, p.categoria
                   ORDER BY total_vendido DESC
                   LIMIT ?";
        
        $params[] = $limite;
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Obtiene ventas por vendedor
     */
    public function getVentasPorVendedor($fechaInicio = null, $fechaFin = null) {
        $query = "SELECT u.id_usuario, u.nombre, u.apellido,
                        COUNT(DISTINCT dp.id_pedido) as pedidos_totales,
                        SUM(dp.cantidad) as productos_vendidos,
                        SUM(dp.subtotal) as ingresos_totales
                 FROM {$this->table} dp
                 INNER JOIN Pedido pe ON dp.id_pedido = pe.id_pedido
                 INNER JOIN Producto p ON dp.id_producto = p.id_producto
                 INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
                 WHERE pe.estado IN ('entregado', 'enviado')";
        
        $params = [];
        
        if ($fechaInicio && $fechaFin) {
            $query .= " AND DATE(pe.fecha_pedido) BETWEEN ? AND ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin;
        }
        
        $query .= " GROUP BY u.id_usuario, u.nombre, u.apellido
                   ORDER BY ingresos_totales DESC";
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Elimina todos los detalles de un pedido
     */
    public function eliminarDetallesPedido($pedidoId) {
        $query = "DELETE FROM {$this->table} WHERE id_pedido = ?";
        return $this->db->delete($query, [$pedidoId]);
    }
    
    /**
     * Actualiza cantidad de un item específico
     */
    public function actualizarCantidad($detalleId, $nuevaCantidad) {
        $query = "SELECT precio_unitario FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $detalle = $this->db->selectOne($query, [$detalleId]);
        
        if (!$detalle) {
            return false;
        }
        
        $nuevoSubtotal = $nuevaCantidad * $detalle['precio_unitario'];
        
        $query = "UPDATE {$this->table} 
                 SET cantidad = ?, subtotal = ?
                 WHERE {$this->primaryKey} = ?";
        
        return $this->db->update($query, [$nuevaCantidad, $nuevoSubtotal, $detalleId]);
    }
}
?>