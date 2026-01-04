<?php
/**
 * SessionManager - Gestor avanzado de sesiones
 * Proporciona funcionalidades seguras para manejo de sesiones
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

class SessionManager {
    
    /**
     * Iniciar sesión segura
     */
    public static function startSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurar parámetros de sesión seguros
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            
            // Configurar duración de sesión con fallback
            $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 3600;
            ini_set('session.gc_maxlifetime', $timeout);
            
            // Iniciar la sesión
            session_start();
            
            // Regenerar ID de sesión cada 30 minutos para seguridad
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutos
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
    }
    
    /**
     * Establecer datos de usuario en la sesión
     */
    public static function setUserData($userData) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_email'] = $userData['correo'];
        $_SESSION['user_nombre'] = $userData['nombre'];
        
        // Manejar tanto 'tipo' como 'tipo_usuario' para compatibilidad
        if (isset($userData['tipo'])) {
            $_SESSION['user_tipo'] = $userData['tipo'];
        } elseif (isset($userData['tipo_usuario'])) {
            $_SESSION['user_tipo'] = $userData['tipo_usuario'];
        }
        
        $_SESSION['login_time'] = time();
    }
    
    /**
     * Obtener datos de usuario de la sesión
     */
    public static function getUserData() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'correo' => $_SESSION['user_email'],  // Usar 'correo' para consistencia
            'nombre' => $_SESSION['user_nombre'],
            'tipo' => $_SESSION['user_tipo'],
            'login_time' => $_SESSION['login_time']
        ];
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Verificar si el usuario es admin
     */
    public static function isAdmin() {
        return self::isLoggedIn() && $_SESSION['user_tipo'] === 'admin';
    }
    
    /**
     * Verificar si el usuario es vendedor
     */
    public static function isVendor() {
        return self::isLoggedIn() && $_SESSION['user_tipo'] === 'vendedor';
    }
    
    /**
     * Verificar si el usuario es cliente
     */
    public static function isCliente() {
        return self::isLoggedIn() && $_SESSION['user_tipo'] === 'cliente';
    }
    
    /**
     * Cerrar sesión completamente
     */
    public static function logout() {
        // Limpiar todas las variables de sesión
        $_SESSION = [];
        
        // Invalidar la cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destruir la sesión
        session_destroy();
        
        // Iniciar nueva sesión limpia
        self::startSecureSession();
    }
    
    /**
     * Establecer mensaje flash
     */
    public static function setFlash($type, $message) {
        $_SESSION["flash_{$type}"] = $message;
    }
    
    /**
     * Obtener mensaje flash
     */
    public static function getFlash($type) {
        if (isset($_SESSION["flash_{$type}"])) {
            $message = $_SESSION["flash_{$type}"];
            unset($_SESSION["flash_{$type}"]);
            return $message;
        }
        return null;
    }
    
    /**
     * Generar token CSRF
     */
    public static function generateCSRF() {
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }
    
    /**
     * Validar token CSRF
     */
    public static function validateCSRF($token) {
        return isset($_SESSION['_token']) && hash_equals($_SESSION['_token'], $token);
    }
    
    /**
     * Verificar timeout de sesión
     */
    public static function checkTimeout() {
        if (self::isLoggedIn()) {
            $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 3600;
            if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
                self::setFlash('warning', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                self::logout();
                return false;
            }
        }
        return true;
    }
}
?>