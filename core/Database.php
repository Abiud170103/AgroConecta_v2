<?php
/**
 * Database - Clase para manejo de base de datos con PDO
 * Implementa patrón Singleton para manejo eficiente de conexiones
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

class Database {
    private static $instance = null;
    private $pdo;
    
    /**
     * Constructor privado para Singleton
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Obtener instancia única de la base de datos
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    /**
     * Establecer conexión con la base de datos
     */
    private function connect() {
        $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
        $dbname = defined('DB_NAME') ? DB_NAME : 'agroconecta_db';
        $username = defined('DB_USER') ? DB_USER : 'root';
        $password = defined('DB_PASS') ? DB_PASS : '';
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
        
        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];
        
        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    /**
     * Obtener conexión PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Ejecutar consulta SELECT
     */
    public function select($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database Select Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecutar consulta SELECT y obtener un solo resultado
     */
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database SelectOne Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecutar INSERT
     */
    public function insert($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute($params);
            return $result ? $this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Database Insert Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecutar UPDATE
     */
    public function update($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecutar DELETE
     */
    public function delete($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database Delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecutar cualquier consulta
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Revertir transacción
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Verificar si estamos en una transacción
     */
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }
    
    /**
     * Prevenir clonación del Singleton
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización del Singleton
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize a singleton.");
    }
}
?>