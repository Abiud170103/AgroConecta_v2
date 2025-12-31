<?php
/**
 * Modelo Notificacion para AgroConecta
 * Gestiona las notificaciones del sistema
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'Model.php';

class Notificacion extends Model {
    protected $table = 'Notificacion';
    protected $primaryKey = 'id_notificacion';
    
    protected $fillable = [
        'id_usuario',
        'titulo',
        'mensaje',
        'tipo',
        'leida',
        'url_accion'
    ];
    
    /**
     * Tipos de notificación válidos
     */
    const TIPOS = [
        'pedido' => 'Pedido',
        'pago' => 'Pago', 
        'producto' => 'Producto',
        'cuenta' => 'Cuenta',
        'sistema' => 'Sistema',
        'promocion' => 'Promoción'
    ];
    
    /**
     * Crea una nueva notificación
     */
    public function crear($userId, $titulo, $mensaje, $tipo = 'sistema', $urlAccion = null) {
        return $this->create([
            'id_usuario' => $userId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
            'url_accion' => $urlAccion,
            'leida' => 0
        ]);
    }
    
    /**
     * Obtiene notificaciones de un usuario
     */
    public function getNotificacionesUsuario($userId, $limite = 20, $soloNoLeidas = false) {
        $query = "SELECT * FROM {$this->table} WHERE id_usuario = ?";
        $params = [$userId];
        
        if ($soloNoLeidas) {
            $query .= " AND leida = 0";
        }
        
        $query .= " ORDER BY fecha_creacion DESC LIMIT ?";
        $params[] = $limite;
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Marca una notificación como leída
     */
    public function marcarLeida($notificacionId) {
        $query = "UPDATE {$this->table} SET leida = 1 WHERE {$this->primaryKey} = ?";
        return $this->db->update($query, [$notificacionId]);
    }
    
    /**
     * Marca todas las notificaciones de un usuario como leídas
     */
    public function marcarTodasLeidas($userId) {
        $query = "UPDATE {$this->table} SET leida = 1 WHERE id_usuario = ?";
        return $this->db->update($query, [$userId]);
    }
    
    /**
     * Cuenta notificaciones no leídas
     */
    public function contarNoLeidas($userId) {
        return $this->count(['id_usuario' => $userId, 'leida' => 0]);
    }
    
    /**
     * Obtiene notificaciones por tipo
     */
    public function getNotificacionesPorTipo($userId, $tipo) {
        return $this->all(['id_usuario' => $userId, 'tipo' => $tipo]);
    }
    
    /**
     * Elimina notificaciones antiguas
     */
    public function eliminarAntiguas($dias = 30) {
        $query = "DELETE FROM {$this->table} 
                 WHERE fecha_creacion < DATE_SUB(NOW(), INTERVAL ? DAY)
                 AND leida = 1";
        
        return $this->db->delete($query, [$dias]);
    }
    
    /**
     * Crea notificación de nuevo pedido
     */
    public function notificarNuevoPedido($userId, $numeroPedido, $total) {
        return $this->crear(
            $userId,
            'Nuevo Pedido Creado',
            "Tu pedido #{$numeroPedido} por $" . number_format($total, 2) . " ha sido creado exitosamente.",
            'pedido',
            '/pedidos/ver/' . $numeroPedido
        );
    }
    
    /**
     * Crea notificación de cambio de estado de pedido
     */
    public function notificarCambioEstadoPedido($userId, $numeroPedido, $nuevoEstado) {
        $estados = [
            'confirmado' => 'confirmado y está siendo preparado',
            'preparando' => 'en preparación',
            'enviado' => 'enviado y en camino',
            'entregado' => 'entregado exitosamente',
            'cancelado' => 'cancelado'
        ];
        
        $mensaje = "Tu pedido #{$numeroPedido} ha sido " . ($estados[$nuevoEstado] ?? $nuevoEstado) . ".";
        
        return $this->crear(
            $userId,
            'Estado de Pedido Actualizado',
            $mensaje,
            'pedido',
            '/pedidos/ver/' . $numeroPedido
        );
    }
    
    /**
     * Crea notificación de pago exitoso
     */
    public function notificarPagoExitoso($userId, $numeroPedido, $monto) {
        return $this->crear(
            $userId,
            'Pago Confirmado',
            "Tu pago de $" . number_format($monto, 2) . " para el pedido #{$numeroPedido} ha sido procesado exitosamente.",
            'pago',
            '/pedidos/ver/' . $numeroPedido
        );
    }
    
    /**
     * Crea notificación de pago fallido
     */
    public function notificarPagoFallido($userId, $numeroPedido, $motivo = '') {
        $mensaje = "Hubo un problema con el pago del pedido #{$numeroPedido}.";
        if ($motivo) {
            $mensaje .= " Motivo: {$motivo}";
        }
        
        return $this->crear(
            $userId,
            'Problema con el Pago',
            $mensaje,
            'pago',
            '/pedidos/pagar/' . $numeroPedido
        );
    }
    
    /**
     * Crea notificación de stock bajo
     */
    public function notificarStockBajo($vendedorId, $nombreProducto, $stockActual) {
        return $this->crear(
            $vendedorId,
            'Stock Bajo',
            "Tu producto '{$nombreProducto}' tiene stock bajo ({$stockActual} unidades restantes).",
            'producto',
            '/productos/gestionar'
        );
    }
    
    /**
     * Crea notificación de nueva venta
     */
    public function notificarNuevaVenta($vendedorId, $nombreProducto, $cantidad, $comprador) {
        return $this->crear(
            $vendedorId,
            'Nueva Venta',
            "{$comprador} compró {$cantidad} unidades de '{$nombreProducto}'.",
            'producto',
            '/ventas/historial'
        );
    }
    
    /**
     * Crea notificación de bienvenida
     */
    public function notificarBienvenida($userId, $nombre) {
        return $this->crear(
            $userId,
            '¡Bienvenido a AgroConecta!',
            "Hola {$nombre}, gracias por unirte a nuestra plataforma. Explora productos frescos y locales.",
            'cuenta',
            '/productos'
        );
    }
    
    /**
     * Crea notificación de cuenta verificada
     */
    public function notificarCuentaVerificada($userId) {
        return $this->crear(
            $userId,
            'Cuenta Verificada',
            'Tu cuenta ha sido verificada exitosamente. Ya puedes acceder a todas las funciones.',
            'cuenta',
            '/perfil'
        );
    }
    
    /**
     * Envía notificaciones masivas por tipo de usuario
     */
    public function enviarMasiva($tipoUsuario, $titulo, $mensaje, $tipo = 'sistema') {
        $query = "SELECT id_usuario FROM Usuario WHERE tipo_usuario = ? AND activo = 1";
        $usuarios = $this->db->select($query, [$tipoUsuario]);
        
        $enviadas = 0;
        foreach ($usuarios as $usuario) {
            if ($this->crear($usuario['id_usuario'], $titulo, $mensaje, $tipo)) {
                $enviadas++;
            }
        }
        
        return $enviadas;
    }
    
    /**
     * Obtiene estadísticas de notificaciones
     */
    public function getEstadisticas() {
        $query = "SELECT 
                    tipo,
                    COUNT(*) as total,
                    SUM(CASE WHEN leida = 1 THEN 1 ELSE 0 END) as leidas,
                    SUM(CASE WHEN leida = 0 THEN 1 ELSE 0 END) as no_leidas
                 FROM {$this->table}
                 GROUP BY tipo
                 ORDER BY total DESC";
        
        return $this->db->select($query);
    }
}
?>