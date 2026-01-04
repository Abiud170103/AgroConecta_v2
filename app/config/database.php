<?php
/**
 * Configuración de Base de Datos - AgroConecta
 * Manejo de conexión con patrón Singleton
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Configuración de base de datos
    private $host = 'localhost';
    private $database = 'agroconecta_v2';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';

    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            // Log del error
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            
            // Mostrar error en desarrollo (cambiar en producción)
            if ($_ENV['APP_ENV'] ?? 'development' === 'development') {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            } else {
                die("Error interno del servidor. Intenta más tarde.");
            }
        }
    }

    /**
     * Obtener instancia única de la base de datos
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Verificar si la base de datos está conectada
     */
    public function isConnected() {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtener información de la conexión
     */
    public function getConnectionInfo() {
        try {
            $stmt = $this->connection->query('SELECT VERSION() as version');
            $result = $stmt->fetch();
            
            return [
                'host' => $this->host,
                'database' => $this->database,
                'version' => $result['version'] ?? 'Desconocida',
                'charset' => $this->charset,
                'connected' => $this->isConnected()
            ];
        } catch (Exception $e) {
            return [
                'host' => $this->host,
                'database' => $this->database,
                'version' => 'Error al obtener versión',
                'charset' => $this->charset,
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cerrar conexión (opcional, se cierra automáticamente)
     */
    public function closeConnection() {
        $this->connection = null;
    }

    // Prevenir clonación
    private function __clone() {}

    // Prevenir deserialización
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton");
    }
}

// Función helper para obtener conexión rápida
function getDB() {
    return Database::getInstance()->getConnection();
}

// Función helper para verificar conexión
function checkDBConnection() {
    return Database::getInstance()->isConnected();
}
?>