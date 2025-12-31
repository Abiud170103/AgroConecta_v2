<?php
/**
 * Modelo Pago para AgroConecta
 * Gestiona los pagos y transacciones del sistema
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'Model.php';

class Pago extends Model {
    protected $table = 'Pago';
    protected $primaryKey = 'id_pago';
    
    protected $fillable = [
        'id_pedido',
        'monto',
        'metodo_pago',
        'estado',
        'transaccion_id',
        'referencia_banco'
    ];
    
    /**
     * Estados válidos del pago
     */
    const ESTADOS = [
        'pendiente' => 'Pendiente',
        'procesando' => 'Procesando',
        'completado' => 'Completado',
        'fallido' => 'Fallido',
        'cancelado' => 'Cancelado',
        'reembolsado' => 'Reembolsado'
    ];
    
    /**
     * Métodos de pago válidos
     */
    const METODOS = [
        'tarjeta_credito' => 'Tarjeta de Crédito',
        'tarjeta_debito' => 'Tarjeta de Débito',
        'paypal' => 'PayPal',
        'mercado_pago' => 'Mercado Pago',
        'transferencia' => 'Transferencia Bancaria',
        'efectivo' => 'Efectivo'
    ];
    
    /**
     * Crea un nuevo pago para un pedido
     */
    public function crearPago($pedidoId, $monto, $metodoPago) {
        $transaccionId = $this->generarTransaccionId();
        
        $pagoData = [
            'id_pedido' => $pedidoId,
            'monto' => $monto,
            'metodo_pago' => $metodoPago,
            'estado' => 'pendiente',
            'transaccion_id' => $transaccionId
        ];
        
        return $this->create($pagoData);
    }
    
    /**
     * Busca pago por ID de transacción
     */
    public function findByTransaccion($transaccionId) {
        $query = "SELECT * FROM {$this->table} WHERE transaccion_id = ?";
        return $this->db->selectOne($query, [$transaccionId]);
    }
    
    /**
     * Obtiene pago por ID de pedido
     */
    public function getPagoPorPedido($pedidoId) {
        $query = "SELECT * FROM {$this->table} WHERE id_pedido = ? ORDER BY fecha_pago DESC LIMIT 1";
        return $this->db->selectOne($query, [$pedidoId]);
    }
    
    /**
     * Actualiza el estado del pago
     */
    public function actualizarEstado($pagoId, $nuevoEstado, $referenciaBanco = null) {
        if (!array_key_exists($nuevoEstado, self::ESTADOS)) {
            return false;
        }
        
        $query = "UPDATE {$this->table} 
                 SET estado = ?, fecha_pago = NOW()";
        
        $params = [$nuevoEstado];
        
        if ($referenciaBanco) {
            $query .= ", referencia_banco = ?";
            $params[] = $referenciaBanco;
        }
        
        $query .= " WHERE {$this->primaryKey} = ?";
        $params[] = $pagoId;
        
        return $this->db->update($query, $params);
    }
    
    /**
     * Confirma un pago exitoso
     */
    public function confirmarPago($transaccionId, $referenciaBanco = null) {
        $pago = $this->findByTransaccion($transaccionId);
        
        if (!$pago) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            // Actualizar estado del pago
            $this->actualizarEstado($pago['id_pago'], 'completado', $referenciaBanco);
            
            // Actualizar estado del pedido
            $pedidoModel = new Pedido();
            $pedidoModel->actualizarEstado($pago['id_pedido'], 'confirmado');
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Marca un pago como fallido
     */
    public function marcarFallido($transaccionId, $motivo = '') {
        $pago = $this->findByTransaccion($transaccionId);
        
        if (!$pago) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            // Actualizar estado del pago
            $this->actualizarEstado($pago['id_pago'], 'fallido');
            
            // Cancelar el pedido y restaurar stock
            $pedidoModel = new Pedido();
            $pedidoModel->cancelarPedido($pago['id_pedido'], "Pago fallido: {$motivo}");
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Obtiene pagos con información del pedido
     */
    public function getPagosConPedido($conditions = [], $limit = null) {
        $query = "SELECT p.*, pe.numero_pedido, pe.total as pedido_total,
                        u.nombre, u.apellido, u.correo
                 FROM {$this->table} p
                 INNER JOIN Pedido pe ON p.id_pedido = pe.id_pedido
                 INNER JOIN Usuario u ON pe.id_usuario = u.id_usuario
                 WHERE 1=1";
        
        $params = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                $query .= " AND p.{$field} = ?";
                $params[] = $value;
            }
        }
        
        $query .= " ORDER BY p.fecha_pago DESC";
        
        if ($limit) {
            $query .= " LIMIT {$limit}";
        }
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Obtiene pagos por método
     */
    public function getPagosPorMetodo($metodo, $fechaInicio = null, $fechaFin = null) {
        $query = "SELECT * FROM {$this->table} WHERE metodo_pago = ?";
        $params = [$metodo];
        
        if ($fechaInicio && $fechaFin) {
            $query .= " AND DATE(fecha_pago) BETWEEN ? AND ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin;
        }
        
        $query .= " ORDER BY fecha_pago DESC";
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Obtiene estadísticas de pagos
     */
    public function getEstadisticasPagos($fechaInicio = null, $fechaFin = null) {
        $query = "SELECT 
                    COUNT(*) as total_pagos,
                    SUM(monto) as monto_total,
                    AVG(monto) as monto_promedio,
                    COUNT(CASE WHEN estado = 'completado' THEN 1 END) as pagos_exitosos,
                    COUNT(CASE WHEN estado = 'fallido' THEN 1 END) as pagos_fallidos,
                    metodo_pago,
                    COUNT(*) as cantidad_por_metodo,
                    SUM(monto) as monto_por_metodo
                 FROM {$this->table}
                 WHERE 1=1";
        
        $params = [];
        
        if ($fechaInicio && $fechaFin) {
            $query .= " AND DATE(fecha_pago) BETWEEN ? AND ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin;
        }
        
        return $this->db->selectOne($query, $params);
    }
    
    /**
     * Obtiene estadísticas por método de pago
     */
    public function getEstadisticasPorMetodo($fechaInicio = null, $fechaFin = null) {
        $query = "SELECT 
                    metodo_pago,
                    COUNT(*) as cantidad,
                    SUM(monto) as monto_total,
                    AVG(monto) as monto_promedio,
                    COUNT(CASE WHEN estado = 'completado' THEN 1 END) as exitosos,
                    COUNT(CASE WHEN estado = 'fallido' THEN 1 END) as fallidos
                 FROM {$this->table}
                 WHERE 1=1";
        
        $params = [];
        
        if ($fechaInicio && $fechaFin) {
            $query .= " AND DATE(fecha_pago) BETWEEN ? AND ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin;
        }
        
        $query .= " GROUP BY metodo_pago ORDER BY monto_total DESC";
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Busca pagos por referencia o transacción
     */
    public function buscarPagos($termino) {
        $query = "SELECT p.*, pe.numero_pedido, u.nombre, u.apellido
                 FROM {$this->table} p
                 INNER JOIN Pedido pe ON p.id_pedido = pe.id_pedido
                 INNER JOIN Usuario u ON pe.id_usuario = u.id_usuario
                 WHERE p.transaccion_id LIKE ?
                 OR p.referencia_banco LIKE ?
                 OR pe.numero_pedido LIKE ?
                 ORDER BY p.fecha_pago DESC";
        
        $term = "%{$termino}%";
        return $this->db->select($query, [$term, $term, $term]);
    }
    
    /**
     * Genera ID único de transacción
     */
    private function generarTransaccionId() {
        $prefijo = 'TXN';
        $timestamp = time();
        $random = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        return $prefijo . $timestamp . $random;
    }
    
    /**
     * Verifica si un pago puede ser reembolsado
     */
    public function puedeReembolsar($pagoId) {
        $pago = $this->find($pagoId);
        
        if (!$pago) {
            return false;
        }
        
        // Solo pagos completados pueden ser reembolsados
        if ($pago['estado'] !== 'completado') {
            return false;
        }
        
        // Verificar que no haya pasado más de 30 días
        $fechaPago = new DateTime($pago['fecha_pago']);
        $fechaActual = new DateTime();
        $diferencia = $fechaActual->diff($fechaPago)->days;
        
        return $diferencia <= 30;
    }
    
    /**
     * Procesa reembolso
     */
    public function procesarReembolso($pagoId, $motivo = '') {
        if (!$this->puedeReembolsar($pagoId)) {
            return false;
        }
        
        return $this->actualizarEstado($pagoId, 'reembolsado');
    }
}
?>