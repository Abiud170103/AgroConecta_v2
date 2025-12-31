<?php
/**
 * Modelo Pedido para AgroConecta
 * Gestiona las órdenes de compra de los clientes
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'Model.php';

class Pedido extends Model {
    protected $table = 'Pedido';
    protected $primaryKey = 'id_pedido';
    
    protected $fillable = [
        'id_usuario',
        'numero_pedido',
        'total',
        'estado',
        'direccion_entrega',
        'telefono_contacto',
        'notas'
    ];
    
    /**
     * Estados válidos del pedido
     */
    const ESTADOS = [
        'pendiente' => 'Pendiente',
        'confirmado' => 'Confirmado',
        'preparando' => 'Preparando',
        'enviado' => 'Enviado',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado'
    ];
    
    /**
     * Obtiene pedidos con información completa
     */
    public function getPedidosCompletos($conditions = []) {
        $query = "SELECT p.*, u.nombre, u.apellido, u.correo, u.telefono,
                        COUNT(dp.id_detalle) as total_items,
                        pago.estado as estado_pago
                 FROM {$this->table} p
                 INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
                 LEFT JOIN DetallePedido dp ON p.{$this->primaryKey} = dp.id_pedido
                 LEFT JOIN Pago pago ON p.{$this->primaryKey} = pago.id_pedido
                 WHERE 1=1";
        
        $params = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                $query .= " AND p.{$field} = ?";
                $params[] = $value;
            }
        }
        
        $query .= " GROUP BY p.{$this->primaryKey}
                   ORDER BY p.fecha_pedido DESC";
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Obtiene pedidos de un usuario específico
     */
    public function getPedidosUsuario($userId, $estado = null) {
        $query = "SELECT p.*, pago.estado as estado_pago, pago.metodo_pago
                 FROM {$this->table} p
                 LEFT JOIN Pago pago ON p.{$this->primaryKey} = pago.id_pedido
                 WHERE p.id_usuario = ?";
        
        $params = [$userId];
        
        if ($estado) {
            $query .= " AND p.estado = ?";
            $params[] = $estado;
        }
        
        $query .= " ORDER BY p.fecha_pedido DESC";
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Obtiene detalles completos de un pedido
     */
    public function getPedidoCompleto($pedidoId) {
        $query = "SELECT p.*, u.nombre, u.apellido, u.correo, u.telefono as telefono_usuario,
                        pago.estado as estado_pago, pago.metodo_pago, pago.transaccion_id,
                        pago.fecha_pago
                 FROM {$this->table} p
                 INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
                 LEFT JOIN Pago pago ON p.{$this->primaryKey} = pago.id_pedido
                 WHERE p.{$this->primaryKey} = ?";
        
        return $this->db->selectOne($query, [$pedidoId]);
    }
    
    /**
     * Obtiene items del pedido
     */
    public function getItemsPedido($pedidoId) {
        $query = "SELECT dp.*, pr.nombre as producto_nombre, pr.imagen_url,
                        pr.unidad_medida, u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
                 FROM DetallePedido dp
                 INNER JOIN Producto pr ON dp.id_producto = pr.id_producto
                 INNER JOIN Usuario u ON pr.id_usuario = u.id_usuario
                 WHERE dp.id_pedido = ?
                 ORDER BY dp.id_detalle";
        
        return $this->db->select($query, [$pedidoId]);
    }
    
    /**
     * Crea un nuevo pedido con sus detalles
     */
    public function crearPedido($datosUsuario, $items, $total, $direccionEntrega, $telefonoContacto, $notas = '') {
        $this->db->beginTransaction();
        
        try {
            // Generar número de pedido único
            $numeroPedido = $this->generarNumeroPedido();
            
            // Crear pedido principal
            $pedidoData = [
                'id_usuario' => $datosUsuario['id_usuario'],
                'numero_pedido' => $numeroPedido,
                'total' => $total,
                'estado' => 'pendiente',
                'direccion_entrega' => $direccionEntrega,
                'telefono_contacto' => $telefonoContacto,
                'notas' => $notas
            ];
            
            $pedidoId = $this->create($pedidoData);
            
            if (!$pedidoId) {
                throw new Exception("Error al crear el pedido");
            }
            
            // Crear detalles del pedido
            $detalleModel = new DetallePedido();
            
            foreach ($items as $item) {
                $detalleData = [
                    'id_pedido' => $pedidoId,
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $item['cantidad'] * $item['precio']
                ];
                
                $detalleModel->create($detalleData);
                
                // Actualizar stock del producto
                $productoModel = new Producto();
                $productoModel->actualizarStock($item['id_producto'], $item['cantidad'], 'restar');
            }
            
            $this->db->commit();
            return $pedidoId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Actualiza el estado del pedido
     */
    public function actualizarEstado($pedidoId, $nuevoEstado) {
        if (!array_key_exists($nuevoEstado, self::ESTADOS)) {
            return false;
        }
        
        $query = "UPDATE {$this->table} 
                 SET estado = ?, fecha_actualizacion = NOW() 
                 WHERE {$this->primaryKey} = ?";
        
        return $this->db->update($query, [$nuevoEstado, $pedidoId]);
    }
    
    /**
     * Cancela un pedido y restaura stock
     */
    public function cancelarPedido($pedidoId, $motivo = '') {
        $this->db->beginTransaction();
        
        try {
            // Obtener items del pedido
            $items = $this->getItemsPedido($pedidoId);
            
            // Restaurar stock de productos
            $productoModel = new Producto();
            
            foreach ($items as $item) {
                $productoModel->actualizarStock($item['id_producto'], $item['cantidad'], 'sumar');
            }
            
            // Actualizar estado del pedido
            $this->actualizarEstado($pedidoId, 'cancelado');
            
            // Registrar motivo de cancelación en notas
            if ($motivo) {
                $query = "UPDATE {$this->table} 
                         SET notas = CONCAT(COALESCE(notas, ''), '\nCancelado: {$motivo}') 
                         WHERE {$this->primaryKey} = ?";
                $this->db->update($query, [$pedidoId]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Obtiene pedidos por estado
     */
    public function getPedidosPorEstado($estado) {
        return $this->getPedidosCompletos(['estado' => $estado]);
    }
    
    /**
     * Obtiene pedidos recientes
     */
    public function getPedidosRecientes($limite = 10) {
        return $this->getPedidosCompletos() ? array_slice($this->getPedidosCompletos(), 0, $limite) : [];
    }
    
    /**
     * Busca pedidos por número o datos del cliente
     */
    public function buscarPedidos($termino) {
        $query = "SELECT p.*, u.nombre, u.apellido, u.correo
                 FROM {$this->table} p
                 INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
                 WHERE p.numero_pedido LIKE ?
                 OR u.nombre LIKE ?
                 OR u.apellido LIKE ?
                 OR u.correo LIKE ?
                 ORDER BY p.fecha_pedido DESC";
        
        $term = "%{$termino}%";
        return $this->db->select($query, [$term, $term, $term, $term]);
    }
    
    /**
     * Genera número de pedido único
     */
    private function generarNumeroPedido() {
        $prefijo = 'AGC';
        $timestamp = date('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefijo . $timestamp . $random;
    }
    
    /**
     * Obtiene estadísticas de pedidos
     */
    public function getEstadisticas($fechaInicio = null, $fechaFin = null) {
        $query = "SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(total) as total_ventas,
                    AVG(total) as ticket_promedio,
                    COUNT(CASE WHEN estado = 'entregado' THEN 1 END) as pedidos_completados,
                    COUNT(CASE WHEN estado = 'cancelado' THEN 1 END) as pedidos_cancelados
                 FROM {$this->table}
                 WHERE 1=1";
        
        $params = [];
        
        if ($fechaInicio && $fechaFin) {
            $query .= " AND DATE(fecha_pedido) BETWEEN ? AND ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin;
        }
        
        return $this->db->selectOne($query, $params);
    }
}
?>