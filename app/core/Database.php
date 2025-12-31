<?php
/**
 * Database - Clase para manejo de base de datos con PDO
 * Implementa el patrón Singleton para conexión única
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

class Database {
    private static $instance = null;
    private $connection;
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    
    private function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
        
        $this->connect();
    }
    
    /**
     * Obtiene la instancia única de la base de datos
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    /**
     * Establece la conexión con la base de datos
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE " . DB_COLLATION
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            $this->handleConnectionError($e);
        }
    }
    
    /**
     * Obtiene la conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Ejecuta una consulta SELECT
     */
    public function select($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleQueryError($e, $query);
            return false;
        }
    }
    
    /**
     * Ejecuta una consulta SELECT que retorna un solo registro
     */
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->handleQueryError($e, $query);
            return false;
        }
    }
    
    /**
     * Ejecuta una consulta INSERT
     */
    public function insert($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $result = $stmt->execute($params);
            
            if ($result) {
                return $this->connection->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            $this->handleQueryError($e, $query);
            return false;
        }
    }
    
    /**
     * Ejecuta una consulta UPDATE
     */
    public function update($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->handleQueryError($e, $query);
            return false;
        }
    }
    
    /**
     * Ejecuta una consulta DELETE
     */
    public function delete($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->handleQueryError($e, $query);
            return false;
        }
    }
    
    /**
     * Ejecuta cualquier tipo de consulta
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->handleQueryError($e, $query);
            return false;
        }
    }
    
    /**
     * Inicia una transacción
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirma una transacción
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Revierte una transacción
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Verifica si hay una transacción activa
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    /**
     * Escapa valores para consultas (aunque se recomienda usar parámetros)
     */
    public function quote($value) {
        return $this->connection->quote($value);
    }
    
    /**
     * Obtiene el último ID insertado
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Maneja errores de conexión
     */
    private function handleConnectionError($e) {
        error_log('Error de conexión a la base de datos: ' . $e->getMessage());
        
        if (ENVIRONMENT === 'development') {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        } else {
            die('Error interno del servidor. Contacte al administrador.');
        }
    }
    
    /**
     * Maneja errores de consulta
     */
    private function handleQueryError($e, $query = '') {
        $errorMsg = 'Error en consulta SQL: ' . $e->getMessage();
        if (!empty($query)) {
            $errorMsg .= ' | Consulta: ' . $query;
        }
        
        error_log($errorMsg);
        
        if (ENVIRONMENT === 'development') {
            throw new Exception($errorMsg);
        } else {
            throw new Exception('Error en la base de datos');
        }
    }
    
    /**
     * Previene la clonación del objeto
     */
    private function __clone() {}
    
    /**
     * Previene la deserialización del objeto
     */
    public function __wakeup() {}
}
?>