<?php

/**
 * Modelo para gestión de direcciones de usuario
 */
class Address extends BaseModel {
    
    protected $table = 'direcciones_usuario';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'usuario_id',
        'alias',
        'calle',
        'numero_interior',
        'colonia',
        'ciudad',
        'estado',
        'codigo_postal',
        'referencia',
        'telefono',
        'principal',
        'activa',
        'fecha_creacion',
        'fecha_actualizacion'
    ];
    
    /**
     * Obtener todas las direcciones de un usuario
     */
    public function getUserAddresses($userId) {
        $sql = "SELECT 
                    d.*,
                    COUNT(p.id) as total_pedidos,
                    MAX(p.fecha_pedido) as ultimo_uso
                FROM {$this->table} d
                LEFT JOIN pedidos p ON d.id = p.direccion_entrega_id
                WHERE d.usuario_id = :user_id
                ORDER BY d.principal DESC, d.activa DESC, d.fecha_creacion DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener direcciones activas de un usuario
     */
    public function getActiveAddresses($userId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_id = :user_id AND activa = 1
                ORDER BY principal DESC, fecha_creacion DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener la dirección principal de un usuario
     */
    public function getPrincipalAddress($userId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_id = :user_id AND principal = 1 AND activa = 1
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si no hay dirección principal, obtener la primera activa
        if (!$result) {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE usuario_id = :user_id AND activa = 1
                    ORDER BY fecha_creacion ASC
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return $result;
    }
    
    /**
     * Crear nueva dirección
     */
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ':' . $field;
        }, $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            return $this->db->lastInsertId();
            
        } catch (PDOException $e) {
            $this->logError('Error creating address', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw new Exception('Error al crear la dirección');
        }
    }
    
    /**
     * Actualizar dirección
     */
    public function update($id, $data) {
        $fields = array_keys($data);
        $setClause = implode(', ', array_map(function($field) {
            return $field . ' = :' . $field;
        }, $fields));
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            $this->logError('Error updating address', [
                'error' => $e->getMessage(),
                'address_id' => $id,
                'data' => $data
            ]);
            throw new Exception('Error al actualizar la dirección');
        }
    }
    
    /**
     * Eliminar dirección
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            $this->logError('Error deleting address', [
                'error' => $e->getMessage(),
                'address_id' => $id
            ]);
            throw new Exception('Error al eliminar la dirección');
        }
    }
    
    /**
     * Buscar dirección por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Desmarcar todas las direcciones principales de un usuario
     */
    public function unsetPrincipalAddresses($userId) {
        $sql = "UPDATE {$this->table} SET principal = 0 WHERE usuario_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Asignar nueva dirección principal (la primera activa disponible)
     */
    public function assignNewPrincipal($userId) {
        $sql = "UPDATE {$this->table} SET principal = 1 
                WHERE id = (
                    SELECT id FROM (
                        SELECT id FROM {$this->table} 
                        WHERE usuario_id = :user_id AND activa = 1 
                        ORDER BY fecha_creacion ASC 
                        LIMIT 1
                    ) as temp
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar si una dirección está siendo utilizada en pedidos
     */
    public function getOrdersUsingAddress($addressId) {
        $sql = "SELECT p.id, p.numero_pedido, p.estado, p.fecha_pedido
                FROM pedidos p
                WHERE p.direccion_entrega_id = :address_id 
                AND p.estado IN ('pendiente', 'pagado', 'preparando', 'enviado')
                ORDER BY p.fecha_pedido DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':address_id', $addressId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de uso de una dirección
     */
    public function getAddressStats($addressId) {
        $sql = "SELECT 
                    COUNT(*) as total_pedidos,
                    COUNT(CASE WHEN p.estado = 'entregado' THEN 1 END) as pedidos_entregados,
                    MAX(p.fecha_pedido) as ultimo_pedido,
                    MIN(p.fecha_pedido) as primer_pedido,
                    SUM(p.total) as valor_total_pedidos
                FROM pedidos p
                WHERE p.direccion_entrega_id = :address_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':address_id', $addressId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar direcciones por código postal
     */
    public function findByPostalCode($postalCode, $userId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE codigo_postal = :postal_code";
        $params = [':postal_code' => $postalCode];
        
        if ($userId) {
            $sql .= " AND usuario_id = :user_id";
            $params[':user_id'] = $userId;
        }
        
        $sql .= " ORDER BY fecha_creacion DESC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener direcciones por estado
     */
    public function findByState($state, $userId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE estado = :state";
        $params = [':state' => $state];
        
        if ($userId) {
            $sql .= " AND usuario_id = :user_id";
            $params[':user_id'] = $userId;
        }
        
        $sql .= " ORDER BY ciudad, fecha_creacion DESC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Validar si una dirección pertenece a un usuario
     */
    public function belongsToUser($addressId, $userId) {
        $sql = "SELECT id FROM {$this->table} 
                WHERE id = :address_id AND usuario_id = :user_id 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':address_id', $addressId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Obtener dirección completa formateada
     */
    public function getFormattedAddress($addressId) {
        $address = $this->findById($addressId);
        
        if (!$address) {
            return null;
        }
        
        $formatted = [];
        
        // Línea 1: Calle y número
        $line1 = $address['calle'];
        if (!empty($address['numero_interior'])) {
            $line1 .= ', ' . $address['numero_interior'];
        }
        $formatted[] = $line1;
        
        // Línea 2: Colonia
        $formatted[] = $address['colonia'];
        
        // Línea 3: Ciudad, Estado y CP
        $line3 = $address['ciudad'] . ', ' . $this->getStateName($address['estado']);
        $line3 .= ' ' . $address['codigo_postal'];
        $formatted[] = $line3;
        
        return [
            'formatted' => implode("\n", $formatted),
            'lines' => $formatted,
            'original' => $address
        ];
    }
    
    /**
     * Obtener nombre del estado por clave
     */
    private function getStateName($stateKey) {
        $states = [
            'aguascalientes' => 'Aguascalientes',
            'baja_california' => 'Baja California',
            'baja_california_sur' => 'Baja California Sur',
            'campeche' => 'Campeche',
            'chiapas' => 'Chiapas',
            'chihuahua' => 'Chihuahua',
            'coahuila' => 'Coahuila',
            'colima' => 'Colima',
            'cdmx' => 'Ciudad de México',
            'durango' => 'Durango',
            'guanajuato' => 'Guanajuato',
            'guerrero' => 'Guerrero',
            'hidalgo' => 'Hidalgo',
            'jalisco' => 'Jalisco',
            'mexico' => 'Estado de México',
            'michoacan' => 'Michoacán',
            'morelos' => 'Morelos',
            'nayarit' => 'Nayarit',
            'nuevo_leon' => 'Nuevo León',
            'oaxaca' => 'Oaxaca',
            'puebla' => 'Puebla',
            'queretaro' => 'Querétaro',
            'quintana_roo' => 'Quintana Roo',
            'san_luis_potosi' => 'San Luis Potosí',
            'sinaloa' => 'Sinaloa',
            'sonora' => 'Sonora',
            'tabasco' => 'Tabasco',
            'tamaulipas' => 'Tamaulipas',
            'tlaxcala' => 'Tlaxcala',
            'veracruz' => 'Veracruz',
            'yucatan' => 'Yucatán',
            'zacatecas' => 'Zacatecas'
        ];
        
        return $states[$stateKey] ?? $stateKey;
    }
    
    /**
     * Calcular distancia aproximada entre dos direcciones (básico por código postal)
     */
    public function calculateDistance($fromAddressId, $toAddressId) {
        // En una implementación real, esto usaría una API de geocodificación
        // Por simplicidad, calculamos una distancia aproximada basada en códigos postales
        
        $fromAddress = $this->findById($fromAddressId);
        $toAddress = $this->findById($toAddressId);
        
        if (!$fromAddress || !$toAddress) {
            return null;
        }
        
        $fromCP = intval($fromAddress['codigo_postal']);
        $toCP = intval($toAddress['codigo_postal']);
        
        // Aproximación básica: diferencia de CP * factor
        $cpDifference = abs($fromCP - $toCP);
        $approximateDistance = $cpDifference * 2; // ~2km por cada 1000 de diferencia en CP
        
        return [
            'distance_km' => $approximateDistance,
            'from_cp' => $fromCP,
            'to_cp' => $toCP,
            'same_state' => $fromAddress['estado'] === $toAddress['estado'],
            'same_city' => $fromAddress['ciudad'] === $toAddress['ciudad']
        ];
    }
    
    /**
     * Limpiar direcciones inactivas antiguas (mantenimiento)
     */
    public function cleanupInactiveAddresses($daysOld = 365) {
        $sql = "DELETE FROM {$this->table} 
                WHERE activa = 0 
                AND fecha_actualizacion < DATE_SUB(NOW(), INTERVAL :days DAY)
                AND id NOT IN (
                    SELECT DISTINCT direccion_entrega_id 
                    FROM pedidos 
                    WHERE direccion_entrega_id IS NOT NULL
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':days', $daysOld, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Registrar log de errores
     */
    private function logError($message, $context = []) {
        $logMessage = date('Y-m-d H:i:s') . ' - ' . $message . ' - ' . json_encode($context);
        error_log($logMessage);
    }
}