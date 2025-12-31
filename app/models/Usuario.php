<?php
/**
 * Modelo Usuario para AgroConecta
 * Gestiona la información de usuarios (clientes, vendedores, admin)
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'Model.php';

class Usuario extends Model {
    protected $table = 'Usuario';
    protected $primaryKey = 'id_usuario';
    
    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'contraseña',
        'telefono',
        'tipo_usuario',
        'activo',
        'verificado',
        'token_verificacion',
        'token_reset'
    ];
    
    /**
     * Busca usuario por email
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE correo = ?";
        return $this->db->selectOne($query, [$email]);
    }
    
    /**
     * Verifica si un email ya existe
     */
    public function emailExists($email, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE correo = ?";
        $params = [$email];
        
        if ($excludeId) {
            $query .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->selectOne($query, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Crea un usuario con contraseña hasheada
     */
    public function createUser($data) {
        if (isset($data['contraseña'])) {
            $data['contraseña'] = password_hash($data['contraseña'], PASSWORD_BCRYPT);
        }
        
        if (isset($data['correo'])) {
            $data['correo'] = strtolower(trim($data['correo']));
        }
        
        return $this->create($data);
    }
    
    /**
     * Verifica las credenciales de login
     */
    public function verifyLogin($email, $password) {
        $user = $this->findByEmail(strtolower(trim($email)));
        
        if (!$user) {
            return false;
        }
        
        if (!$user['activo']) {
            return false;
        }
        
        if (password_verify($password, $user['contraseña'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Actualiza la contraseña del usuario
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $query = "UPDATE {$this->table} SET contraseña = ?, token_reset = NULL WHERE {$this->primaryKey} = ?";
        return $this->db->update($query, [$hashedPassword, $userId]);
    }
    
    /**
     * Genera token de verificación
     */
    public function generateVerificationToken($userId) {
        $token = bin2hex(random_bytes(32));
        
        $query = "UPDATE {$this->table} SET token_verificacion = ? WHERE {$this->primaryKey} = ?";
        $this->db->update($query, [$token, $userId]);
        
        return $token;
    }
    
    /**
     * Genera token de reset de contraseña
     */
    public function generateResetToken($email) {
        $token = bin2hex(random_bytes(32));
        
        $query = "UPDATE {$this->table} SET token_reset = ? WHERE correo = ?";
        $result = $this->db->update($query, [$token, $email]);
        
        return $result > 0 ? $token : false;
    }
    
    /**
     * Verifica token de reset
     */
    public function verifyResetToken($token) {
        $query = "SELECT * FROM {$this->table} WHERE token_reset = ? AND activo = 1";
        return $this->db->selectOne($query, [$token]);
    }
    
    /**
     * Marca usuario como verificado
     */
    public function verifyUser($token) {
        $query = "UPDATE {$this->table} SET verificado = 1, token_verificacion = NULL WHERE token_verificacion = ?";
        return $this->db->update($query, [$token]);
    }
    
    /**
     * Obtiene vendedores activos
     */
    public function getVendedores($limit = null) {
        $query = "SELECT {$this->primaryKey}, nombre, apellido, correo, telefono, fecha_registro 
                 FROM {$this->table} 
                 WHERE tipo_usuario = 'vendedor' AND activo = 1 
                 ORDER BY fecha_registro DESC";
        
        if ($limit) {
            $query .= " LIMIT {$limit}";
        }
        
        return $this->db->select($query);
    }
    
    /**
     * Obtiene clientes activos
     */
    public function getClientes($limit = null) {
        $query = "SELECT {$this->primaryKey}, nombre, apellido, correo, telefono, fecha_registro 
                 FROM {$this->table} 
                 WHERE tipo_usuario = 'cliente' AND activo = 1 
                 ORDER BY fecha_registro DESC";
        
        if ($limit) {
            $query .= " LIMIT {$limit}";
        }
        
        return $this->db->select($query);
    }
    
    /**
     * Busca usuarios por nombre o email
     */
    public function searchUsers($term, $tipo = null) {
        $query = "SELECT {$this->primaryKey}, nombre, apellido, correo, tipo_usuario, activo 
                 FROM {$this->table} 
                 WHERE (nombre LIKE ? OR apellido LIKE ? OR correo LIKE ?)";
        
        $params = ["%{$term}%", "%{$term}%", "%{$term}%"];
        
        if ($tipo) {
            $query .= " AND tipo_usuario = ?";
            $params[] = $tipo;
        }
        
        $query .= " ORDER BY nombre, apellido";
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Desactiva usuario (soft delete)
     */
    public function deactivateUser($userId) {
        $query = "UPDATE {$this->table} SET activo = 0 WHERE {$this->primaryKey} = ?";
        return $this->db->update($query, [$userId]);
    }
    
    /**
     * Reactiva usuario
     */
    public function reactivateUser($userId) {
        $query = "UPDATE {$this->table} SET activo = 1 WHERE {$this->primaryKey} = ?";
        return $this->db->update($query, [$userId]);
    }
    
    /**
     * Obtiene estadísticas de usuarios
     */
    public function getStats() {
        $query = "SELECT 
                    tipo_usuario,
                    COUNT(*) as total,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN verificado = 1 THEN 1 ELSE 0 END) as verificados
                 FROM {$this->table} 
                 GROUP BY tipo_usuario";
        
        return $this->db->select($query);
    }
}
?>