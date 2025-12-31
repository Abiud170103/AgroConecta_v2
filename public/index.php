<?php
/**
 * index.php - Punto de entrada principal de AgroConecta
 * Inicializa el sistema de rutas y procesa todas las solicitudes
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

// ============================================
// CONFIGURACIÓN INICIAL
// ============================================

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Configurar errores según el entorno
$isDevelopment = true; // Cambiar a false en producción

if ($isDevelopment) {
    define('DEBUG', true);
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    define('DEBUG', false);
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Iniciar sesión
session_start();

// Configurar headers de seguridad
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ============================================
// AUTOLOAD Y DEPENDENCIAS
// ============================================

// Función de autoload simple
spl_autoload_register(function ($className) {
    $directories = [
        '../core/',
        '../app/controllers/',
        '../app/models/',
        '../config/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ============================================
// DEFINICIÓN DE CONSTANTES DE RUTA
// ============================================

// Definir rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta');

// Cargar configuración
require_once '../config/database.php';

// ============================================
// INICIALIZACIÓN DEL ROUTER
// ============================================

try {
    // Crear instancia del router
    $router = new Router();
    
    // Cargar middleware
    require_once '../core/Middleware.php';
    
    // Cargar rutas
    require_once '../config/agroconecta_routes.php';
    
    // Procesar la solicitud
    $router->dispatch();
    
} catch (Exception $e) {
    // Manejar errores críticos
    if (DEBUG) {
        echo "<h1>Error Crítico del Sistema</h1>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        echo "<h2>Stack Trace:</h2>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        // En producción, mostrar página de error genérica
        http_response_code(500);
        if (file_exists('../app/views/errors/500.php')) {
            include '../app/views/errors/500.php';
        } else {
            echo "<h1>Error interno del servidor</h1>";
            echo "<p>Ha ocurrido un error interno. Por favor, inténtalo más tarde.</p>";
        }
    }
    
    // Log del error
    error_log("Critical Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
}

// ============================================
// FUNCIONES AUXILIARES GLOBALES
// ============================================

/**
 * Generar URL para una ruta nombrada
 */
function route($name, $parameters = []) {
    global $router;
    try {
        return $router->url($name, $parameters);
    } catch (Exception $e) {
        if (DEBUG) {
            throw $e;
        }
        return '/';
    }
}

/**
 * Redirigir a una URL
 */
function redirect($url, $statusCode = 302) {
    http_response_code($statusCode);
    header("Location: {$url}");
    exit;
}

/**
 * Verificar si el usuario está autenticado
 */
function auth() {
    return isset($_SESSION['user_id']);
}

/**
 * Obtener usuario actual
 */
function user() {
    if (!auth()) {
        return null;
    }
    
    // Cache del usuario en la sesión para evitar múltiples consultas
    if (!isset($_SESSION['user_data']) || 
        $_SESSION['user_data_timestamp'] < (time() - 300)) { // Cache por 5 minutos
        
        require_once '../app/models/Usuario.php';
        $usuarioModel = new Usuario();
        $userData = $usuarioModel->find($_SESSION['user_id']);
        
        $_SESSION['user_data'] = $userData;
        $_SESSION['user_data_timestamp'] = time();
    }
    
    return $_SESSION['user_data'];
}

/**
 * Generar token CSRF
 */
function csrf_token() {
    if (!isset($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_token'];
}

/**
 * Generar campo hidden para CSRF
 */
function csrf_field() {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

/**
 * Escapar HTML para prevenir XSS
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Obtener un valor de configuración
 */
function config($key, $default = null) {
    $config = [
        'app.name' => 'AgroConecta',
        'app.url' => 'http://localhost',
        'app.timezone' => 'America/Mexico_City',
        'mail.from' => 'noreply@agroconecta.com',
        'pagination.per_page' => 20,
        'upload.max_size' => 5242880, // 5MB
        'cart.session_lifetime' => 3600, // 1 hora
    ];
    
    return $config[$key] ?? $default;
}

/**
 * Formatear precio
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    return $date->format($format);
}

/**
 * Obtener asset URL
 */
function asset($path) {
    $baseUrl = config('app.url', '');
    return $baseUrl . '/' . ltrim($path, '/');
}

/**
 * Mostrar mensaje flash
 */
function flash($type = null) {
    if ($type) {
        $message = $_SESSION["flash_{$type}"] ?? null;
        unset($_SESSION["flash_{$type}"]);
        return $message;
    }
    
    $messages = [];
    foreach (['success', 'error', 'warning', 'info'] as $flashType) {
        if (isset($_SESSION["flash_{$flashType}"])) {
            $messages[$flashType] = $_SESSION["flash_{$flashType}"];
            unset($_SESSION["flash_{$flashType}"]);
        }
    }
    
    return $messages;
}

/**
 * Establecer mensaje flash
 */
function setFlash($type, $message) {
    $_SESSION["flash_{$type}"] = $message;
}

/**
 * Validar archivo subido
 */
function validateUploadedFile($file, $allowedTypes = [], $maxSize = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Error al subir el archivo'];
    }
    
    if (!empty($allowedTypes) && !in_array($file['type'], $allowedTypes)) {
        return ['valid' => false, 'error' => 'Tipo de archivo no permitido'];
    }
    
    $maxSize = $maxSize ?? config('upload.max_size', 5242880);
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'Archivo muy grande'];
    }
    
    return ['valid' => true];
}

// ============================================
// SHUTDOWN HANDLER
// ============================================

register_shutdown_function(function() {
    $error = error_get_last();
    
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Log fatal error
        error_log("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
        
        if (!DEBUG) {
            // En producción, mostrar página de error limpia
            if (!headers_sent()) {
                http_response_code(500);
                if (file_exists('../app/views/errors/500.php')) {
                    include '../app/views/errors/500.php';
                } else {
                    echo "<h1>Error interno del servidor</h1>";
                }
            }
        }
    }
});
?>