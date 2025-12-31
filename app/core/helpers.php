<?php
/**
 * Helper Functions - Funciones auxiliares para AgroConecta
 * Funciones globales que se utilizan en toda la aplicación
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

/**
 * Genera y retorna un token CSRF
 * @return string
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida un token CSRF
 * @param string $token Token a validar
 * @return bool
 */
function csrf_verify($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Genera URL para assets (CSS, JS, imágenes)
 * @param string $path Ruta del asset
 * @return string URL completa del asset
 */
function asset($path) {
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    $path = ltrim($path, '/');
    return $baseUrl . '/public/' . $path;
}

/**
 * Obtiene la URL actual
 * @return string
 */
function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return $protocol . '://' . $host . $uri;
}

/**
 * Genera URL para rutas de la aplicación
 * @param string $path Ruta
 * @return string URL completa
 */
function url($path = '') {
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    $path = ltrim($path, '/');
    return $path ? $baseUrl . '/' . $path : $baseUrl;
}

/**
 * Redirecciona a una URL
 * @param string $url URL de destino
 * @param int $statusCode Código de estado HTTP
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Obtiene un valor de la sesión
 * @param string $key Clave
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function session($key = null, $default = null) {
    if ($key === null) {
        return $_SESSION;
    }
    return $_SESSION[$key] ?? $default;
}

/**
 * Establece un valor en la sesión
 * @param string $key Clave
 * @param mixed $value Valor
 */
function session_set($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Remueve un valor de la sesión
 * @param string $key Clave
 */
function session_remove($key) {
    unset($_SESSION[$key]);
}

/**
 * Obtiene y remueve un valor flash de la sesión
 * @param string $key Clave
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function session_flash($key, $default = null) {
    $value = session($key, $default);
    session_remove($key);
    return $value;
}

/**
 * Escapa HTML para prevenir XSS
 * @param string $string Cadena a escapar
 * @return string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Formatea un precio en moneda
 * @param float $amount Cantidad
 * @param string $currency Moneda
 * @return string
 */
function format_currency($amount, $currency = '$') {
    return $currency . number_format($amount, 2);
}

/**
 * Formatea una fecha
 * @param string|DateTime $date Fecha
 * @param string $format Formato
 * @return string
 */
function format_date($date, $format = 'd/m/Y') {
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    return $date->format($format);
}

/**
 * Genera un slug desde un texto
 * @param string $text Texto
 * @return string
 */
function slug($text) {
    $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
    $text = preg_replace('/\s+/', '-', trim($text));
    return strtolower($text);
}

/**
 * Trunca un texto
 * @param string $text Texto
 * @param int $length Longitud máxima
 * @param string $suffix Sufijo
 * @return string
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Genera una URL para imagen placeholder
 * @param int $width Ancho
 * @param int $height Alto
 * @param string $text Texto
 * @return string
 */
function placeholder_image($width = 300, $height = 300, $text = 'Sin imagen') {
    return "https://via.placeholder.com/{$width}x{$height}?text=" . urlencode($text);
}

/**
 * Verifica si una imagen existe
 * @param string $path Ruta de la imagen
 * @return bool
 */
function image_exists($path) {
    return file_exists(PUBLIC_PATH . '/' . ltrim($path, '/'));
}

/**
 * Obtiene la URL de una imagen o placeholder si no existe
 * @param string $path Ruta de la imagen
 * @param int $width Ancho del placeholder
 * @param int $height Alto del placeholder
 * @return string
 */
function image_url($path, $width = 300, $height = 300) {
    if ($path && image_exists($path)) {
        return asset($path);
    }
    return placeholder_image($width, $height);
}

/**
 * Debug helper - imprime variables y termina ejecución
 * @param mixed ...$vars Variables a imprimir
 */
function dd(...$vars) {
    echo '<pre style="background: #f4f4f4; padding: 10px; margin: 10px; border: 1px solid #ddd;">';
    foreach ($vars as $var) {
        print_r($var);
        echo "\n---\n";
    }
    echo '</pre>';
    die();
}

/**
 * Obtiene la configuración de la base de datos
 * @param string $key Clave específica o null para todas
 * @return mixed
 */
function config($key = null) {
    static $config = null;
    
    if ($config === null) {
        $config = [
            'db' => [
                'host' => 'localhost',
                'database' => 'agroconecta',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4'
            ],
            'app' => [
                'name' => 'AgroConecta',
                'version' => '1.0',
                'debug' => true
            ]
        ];
    }
    
    if ($key === null) {
        return $config;
    }
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return null;
        }
        $value = $value[$k];
    }
    
    return $value;
}

/**
 * Obtiene el número de items en el carrito
 * @return int
 */
function cart_count() {
    return session('cart_count', 0);
}

/**
 * Obtiene el total del carrito
 * @return float
 */
function cart_total() {
    return session('cart_total', 0.0);
}

// ============================================
// FUNCIONES DE USUARIO
// ============================================

/**
 * Verifica si hay un usuario logueado
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtiene el nombre del usuario actual
 * @return string
 */
function user_name() {
    if (!is_logged_in()) {
        return 'Usuario';
    }
    
    return session('user_name', 'Usuario');
}

/**
 * Obtiene el email del usuario actual
 * @return string
 */
function user_email() {
    if (!is_logged_in()) {
        return '';
    }
    
    return session('user_email', '');
}

/**
 * Obtiene el avatar del usuario actual
 * @return string
 */
function user_avatar() {
    if (!is_logged_in()) {
        return asset('img/default-avatar.png');
    }
    
    $avatar = session('user_avatar', '');
    if (empty($avatar)) {
        return asset('img/default-avatar.png');
    }
    
    return asset('uploads/avatars/' . $avatar);
}

// ============================================
// FUNCIONES DE MENSAJES FLASH
// ============================================

/**
 * Verifica si hay mensajes flash
 * @return bool
 */
function has_flash_messages() {
    return isset($_SESSION['flash_success']) || 
           isset($_SESSION['flash_error']) || 
           isset($_SESSION['flash_warning']) || 
           isset($_SESSION['flash_info']);
}

/**
 * Obtiene un mensaje flash y lo elimina de la sesión
 * @param string $type Tipo de mensaje (success, error, warning, info)
 * @return string|null
 */
function get_flash_message($type) {
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}

/**
 * Establece un mensaje flash
 * @param string $type Tipo de mensaje
 * @param string $message Mensaje
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Obtiene todos los mensajes flash
 * @return array
 */
function get_all_flash_messages() {
    $messages = [];
    $types = ['success', 'error', 'warning', 'info'];
    
    foreach ($types as $type) {
        $message = get_flash_message($type);
        if ($message) {
            $messages[$type] = $message;
        }
    }
    
    return $messages;
}

// ============================================
// FUNCIONES CSRF
// ============================================

/**
 * Genera un campo CSRF hidden para formularios
 * @return string
 */
function csrf_field() {
    $token = csrf_token();
    return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Genera la meta tag CSRF para AJAX
 * @return string
 */
function csrf_meta() {
    $token = csrf_token();
    return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}