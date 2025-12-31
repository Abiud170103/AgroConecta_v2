<?php
/**
 * Modelo base para AgroConecta
 * Clase base de la cual heredan todos los modelos del sistema
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Encuentra un registro por ID
     */
    public function find($id) {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->selectOne($query, [$id]);
    }
    
    /**
     * Encuentra todos los registros
     */
    public function all($conditions = []) {
        $query = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(' AND ', $where);
        }
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Crea un nuevo registro
     */
    public function create($data) {
        // Filtrar solo campos permitidos
        $filteredData = $this->filterFillable($data);
        
        // Agregar timestamps si está habilitado
        if ($this->timestamps) {
            $filteredData['fecha_creacion'] = date('Y-m-d H:i:s');
            $filteredData['fecha_actualizacion'] = date('Y-m-d H:i:s');
        }
        
        $fields = array_keys($filteredData);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        
        $query = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ({$placeholders})";
        
        return $this->db->insert($query, array_values($filteredData));
    }
    
    /**
     * Actualiza un registro por ID
     */
    public function update($id, $data) {
        $filteredData = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $filteredData['fecha_actualizacion'] = date('Y-m-d H:i:s');
        }
        
        $fields = [];
        $params = [];
        
        foreach ($filteredData as $field => $value) {
            $fields[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        
        $query = "UPDATE {$this->table} SET " . implode(',', $fields) . " WHERE {$this->primaryKey} = ?";
        
        return $this->db->update($query, $params);
    }
    
    /**
     * Elimina un registro por ID
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->delete($query, [$id]);
    }
    
    /**
     * Cuenta registros con condiciones
     */
    public function count($conditions = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->db->selectOne($query, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Busca registros con LIKE
     */
    public function search($field, $term) {
        $query = "SELECT * FROM {$this->table} WHERE {$field} LIKE ?";
        return $this->db->select($query, ["%{$term}%"]);
    }
    
    /**
     * Paginación de resultados
     */
    public function paginate($page = 1, $perPage = 10, $conditions = []) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(' AND ', $where);
        }
        
        $query .= " LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Filtra datos basado en campos permitidos
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        $filtered = [];
        foreach ($this->fillable as $field) {
            if (isset($data[$field])) {
                $filtered[$field] = $data[$field];
            }
        }
        
        return $filtered;
    }
    
    /**
     * Ejecuta una consulta personalizada
     */
    public function query($sql, $params = []) {
        return $this->db->select($sql, $params);
    }
    
    /**
     * Inicia una transacción
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Confirma una transacción
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Revierte una transacción
     */
    public function rollback() {
        return $this->db->rollback();
    }
}
?>