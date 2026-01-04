<?php
/**
 * Configuración de base de datos para AgroConecta
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

// Definir rutas principales solo si no están definidas
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
    define('APP_PATH', ROOT_PATH . '/app');
    define('PUBLIC_PATH', ROOT_PATH . '/public');
    define('CONFIG_PATH', ROOT_PATH . '/config');
}

// Configuración de la base de datos
define('DB_HOST', '127.0.0.1');  // Cambiado de localhost
define('DB_NAME', 'agroconecta_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// Configuración del sistema
define('ENVIRONMENT', 'development'); // development, testing, production
define('DEBUG_MODE', true);

// URLs y rutas
define('SITE_URL', 'http://localhost/AgroConecta_v2');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configuración de seguridad
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos
define('CSRF_TOKEN_EXPIRE', 1800); // 30 minutos

// Configuración de correo (PHPMailer)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'agroconecta@gmail.com'); // Cambiar por email real
define('MAIL_PASSWORD', ''); // Configurar password de app
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_EMAIL', 'agroconecta@gmail.com');
define('MAIL_FROM_NAME', 'AgroConecta');

// Configuración de Mercado Pago
define('MP_ACCESS_TOKEN', ''); // Token de acceso de MercadoPago
define('MP_PUBLIC_KEY', ''); // Clave pública de MercadoPago
define('MP_SANDBOX_MODE', true); // true para pruebas, false para producción

// Configuraciones adicionales
define('DEFAULT_TIMEZONE', 'America/Mexico_City');
define('DEFAULT_LANGUAGE', 'es');
define('ITEMS_PER_PAGE', 12);

// Establecer zona horaria
date_default_timezone_set(DEFAULT_TIMEZONE);

// Configurar locale para México
setlocale(LC_ALL, 'es_MX.UTF-8');

/**
 * Función para obtener conexión a la base de datos
 * @return PDO Instancia de conexión PDO
 * @throws Exception Si no se puede conectar
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATION
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            if (DEBUG_MODE) {
                error_log("Conexión a BD establecida correctamente");
            }
        } catch (PDOException $e) {
            error_log("Error de conexión a BD: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    return $pdo;
}

/**
 * Función alternativa para obtener conexión (alias)
 * @return PDO
 */
function db() {
    return getDBConnection();
}
?>