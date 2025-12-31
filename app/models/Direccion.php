<?php
/**
 * Modelo Direccion para AgroConecta
 * Gestiona las direcciones de los usuarios
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'Model.php';

class Direccion extends Model {
    protected $table = 'Direccion';
    protected $primaryKey = 'id_direccion';
    
    protected $fillable = [
        'id_usuario',
        'nombre',
        'calle',
        'numero',
        'colonia',
        'ciudad',
        'estado',
        'codigo_postal',
        'referencias',
        'es_principal'
    ];
    
    /**
     * Obtiene todas las direcciones de un usuario
     */
    public function getDireccionesUsuario($userId) {
        return $this->all(['id_usuario' => $userId]);
    }
    
    /**
     * Obtiene la dirección principal de un usuario
     */
    public function getDireccionPrincipal($userId) {
        $query = "SELECT * FROM {$this->table} 
                 WHERE id_usuario = ? AND es_principal = 1 
                 ORDER BY fecha_creacion DESC 
                 LIMIT 1";
        
        return $this->db->selectOne($query, [$userId]);
    }
    
    /**
     * Establece una dirección como principal
     */
    public function establecerPrincipal($direccionId, $userId) {
        $this->db->beginTransaction();
        
        try {
            // Quitar el estado principal de todas las direcciones del usuario
            $query = "UPDATE {$this->table} SET es_principal = 0 WHERE id_usuario = ?";
            $this->db->update($query, [$userId]);
            
            // Establecer la nueva dirección como principal
            $query = "UPDATE {$this->table} SET es_principal = 1 WHERE {$this->primaryKey} = ?";
            $this->db->update($query, [$direccionId]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Crea una nueva dirección
     */
    public function crearDireccion($data) {
        // Si es la primera dirección del usuario, marcarla como principal
        $direccionesExistentes = $this->count(['id_usuario' => $data['id_usuario']]);
        
        if ($direccionesExistentes === 0 || (isset($data['es_principal']) && $data['es_principal'])) {
            $data['es_principal'] = 1;
            
            // Si hay que marcarla como principal, quitar el estado de las demás
            if ($direccionesExistentes > 0) {
                $query = "UPDATE {$this->table} SET es_principal = 0 WHERE id_usuario = ?";
                $this->db->update($query, [$data['id_usuario']]);
            }
        }
        
        return $this->create($data);
    }
    
    /**
     * Formatea una dirección completa
     */
    public function formatearDireccion($direccion) {
        if (!$direccion) {
            return '';
        }
        
        $partes = [];
        
        if (!empty($direccion['calle'])) {
            $partes[] = $direccion['calle'];
        }
        
        if (!empty($direccion['numero'])) {
            $partes[] = '#' . $direccion['numero'];
        }
        
        if (!empty($direccion['colonia'])) {
            $partes[] = $direccion['colonia'];
        }
        
        if (!empty($direccion['ciudad'])) {
            $partes[] = $direccion['ciudad'];
        }
        
        if (!empty($direccion['estado'])) {
            $partes[] = $direccion['estado'];
        }
        
        if (!empty($direccion['codigo_postal'])) {
            $partes[] = 'C.P. ' . $direccion['codigo_postal'];
        }
        
        return implode(', ', $partes);
    }
    
    /**
     * Busca direcciones por ciudad o estado
     */
    public function buscarPorUbicacion($termino) {
        $query = "SELECT DISTINCT ciudad, estado FROM {$this->table} 
                 WHERE ciudad LIKE ? OR estado LIKE ?
                 ORDER BY ciudad, estado";
        
        $term = "%{$termino}%";
        return $this->db->select($query, [$term, $term]);
    }
    
    /**
     * Obtiene estadísticas de ubicaciones
     */
    public function getEstadisticasUbicaciones() {
        $query = "SELECT 
                    estado,
                    COUNT(*) as total_direcciones,
                    COUNT(DISTINCT ciudad) as ciudades,
                    COUNT(DISTINCT id_usuario) as usuarios
                 FROM {$this->table}
                 GROUP BY estado
                 ORDER BY total_direcciones DESC";
        
        return $this->db->select($query);
    }
    
    /**
     * Valida código postal
     */
    public function validarCodigoPostal($codigoPostal) {
        // Validación básica de código postal mexicano (5 dígitos)
        return preg_match('/^[0-9]{5}$/', $codigoPostal);
    }
    
    /**
     * Obtiene direcciones por código postal
     */
    public function getDireccionesPorCP($codigoPostal) {
        return $this->all(['codigo_postal' => $codigoPostal]);
    }
    
    /**
     * Calcula distancia estimada (simplificada)
     */
    public function calcularDistanciaEstimada($origen, $destino) {
        // Implementación simplificada usando códigos postales
        // En una implementación real, usarías APIs como Google Maps
        
        $diferencia = abs((int)$origen - (int)$destino);
        
        if ($diferencia <= 100) {
            return ['distancia' => 'Local', 'costo_envio' => 50];
        } elseif ($diferencia <= 1000) {
            return ['distancia' => 'Regional', 'costo_envio' => 100];
        } else {
            return ['distancia' => 'Nacional', 'costo_envio' => 200];
        }
    }
    
    /**
     * Obtiene direcciones de entrega más usadas
     */
    public function getDireccionesMasUsadas($limite = 10) {
        $query = "SELECT d.*, COUNT(p.id_pedido) as veces_usada
                 FROM {$this->table} d
                 LEFT JOIN Pedido p ON CONCAT(d.calle, ', ', d.numero, ', ', d.colonia, ', ', d.ciudad) = p.direccion_entrega
                 GROUP BY d.{$this->primaryKey}
                 HAVING veces_usada > 0
                 ORDER BY veces_usada DESC
                 LIMIT ?";
        
        return $this->db->select($query, [$limite]);
    }
    
    /**
     * Limpia direcciones sin usar por más de un año
     */
    public function limpiarDireccionesAntiguas($dias = 365) {
        $query = "DELETE FROM {$this->table} 
                 WHERE es_principal = 0 
                 AND fecha_creacion < DATE_SUB(NOW(), INTERVAL ? DAY)
                 AND {$this->primaryKey} NOT IN (
                     SELECT DISTINCT d.{$this->primaryKey}
                     FROM {$this->table} d
                     INNER JOIN Pedido p ON CONCAT(d.calle, ', ', d.numero, ', ', d.colonia, ', ', d.ciudad) = p.direccion_entrega
                     WHERE p.fecha_pedido > DATE_SUB(NOW(), INTERVAL ? DAY)
                 )";
        
        return $this->db->delete($query, [$dias, $dias]);
    }
}
?>