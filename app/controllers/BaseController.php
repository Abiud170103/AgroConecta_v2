<?php
/**
 * Controlador base para AgroConecta
 * Funcionalidades comunes para todos los controladores
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

class BaseController extends Controller {
    protected $currentUser = null;
    protected $isLoggedIn = false;
    
    public function __construct() {
        parent::__construct();
        $this->initializeAuth();
        $this->setCommonViewData();
    }
    
    /**
     * Inicializa el sistema de autenticación
     */
    protected function initializeAuth() {
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
            $userModel = new Usuario();
            $this->currentUser = $userModel->find($_SESSION['user_id']);
            $this->isLoggedIn = ($this->currentUser !== null && $this->currentUser['activo'] == 1);
            
            if (!$this->isLoggedIn) {
                // Usuario inactivo o no encontrado, cerrar sesión
                $this->logout();
            }
        }
    }
    
    /**
     * Establece datos comunes para todas las vistas
     */
    protected function setCommonViewData() {
        $this->setViewData('currentUser', $this->currentUser);
        $this->setViewData('isLoggedIn', $this->isLoggedIn);
        $this->setViewData('siteName', 'AgroConecta');
        $this->setViewData('baseUrl', BASE_URL);
        $this->setViewData('currentPage', $this->getCurrentPageName());
        
        // Contador de notificaciones no leídas
        if ($this->isLoggedIn) {
            $notifModel = new Notificacion();
            $unreadCount = $notifModel->contarNoLeidas($this->currentUser['id_usuario']);
            $this->setViewData('unreadNotifications', $unreadCount);
            
            // Items en carrito
            $carritoModel = new Carrito();
            $cartItemsCount = $carritoModel->contarItems($this->currentUser['id_usuario']);
            $this->setViewData('cartItemsCount', $cartItemsCount);
        } else {
            $this->setViewData('unreadNotifications', 0);
            $this->setViewData('cartItemsCount', 0);
        }
    }
    
    /**
     * Requiere autenticación para acceder al método
     */
    protected function requireAuth() {
        if (!$this->isLoggedIn) {
            $this->redirectToLogin();
            return false;
        }
        return true;
    }
    
    /**
     * Requiere rol específico para acceder
     */
    protected function requireRole($requiredRole) {
        if (!$this->requireAuth()) {
            return false;
        }
        
        if ($this->currentUser['tipo_usuario'] !== $requiredRole) {
            $this->redirect('/error/403');
            return false;
        }
        
        return true;
    }
    
    /**
     * Verifica si el usuario es admin
     */
    protected function isAdmin() {
        return $this->isLoggedIn && $this->currentUser['tipo_usuario'] === 'admin';
    }
    
    /**
     * Verifica si el usuario es vendedor
     */
    protected function isVendedor() {
        return $this->isLoggedIn && $this->currentUser['tipo_usuario'] === 'vendedor';
    }
    
    /**
     * Verifica si el usuario es cliente
     */
    protected function isCliente() {
        return $this->isLoggedIn && $this->currentUser['tipo_usuario'] === 'cliente';
    }
    
    /**
     * Redirecciona al login
     */
    protected function redirectToLogin($message = '') {
        if (!empty($message)) {
            $_SESSION['error_message'] = $message;
        }
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        $this->redirect('/auth/login');
    }
    
    /**
     * Cierra la sesión del usuario
     */
    protected function logout() {
        session_destroy();
        session_start();
        $this->currentUser = null;
        $this->isLoggedIn = false;
    }
    
    /**
     * Valida token CSRF
     */
    protected function validateCSRF($token = null) {
        // Si no se proporciona token, obtenerlo del POST
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? '';
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                $this->jsonError('Token CSRF inválido', 403);
                return false;
            }
        }
        return true;
    }
    
    /**
     * Genera token CSRF
     */
    protected function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Respuesta JSON de éxito
     */
    protected function jsonSuccess($message = 'Operación exitosa', $data = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
    
    /**
     * Respuesta JSON de error
     */
    protected function jsonError($message = 'Error en la operación', $code = 400, $data = []) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
    
    /**
     * Valida datos del formulario
     */
    protected function validateForm($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            // Required
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = $rule['message'] ?? "El campo {$field} es requerido";
                continue;
            }
            
            // Skip other validations if empty and not required
            if (empty($value)) continue;
            
            // Email
            if (isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = $rule['message'] ?? 'Email inválido';
            }
            
            // Min length
            if (isset($rule['min']) && strlen($value) < $rule['min']) {
                $errors[$field] = $rule['message'] ?? "Mínimo {$rule['min']} caracteres";
            }
            
            // Max length
            if (isset($rule['max']) && strlen($value) > $rule['max']) {
                $errors[$field] = $rule['message'] ?? "Máximo {$rule['max']} caracteres";
            }
            
            // Unique (for email typically)
            if (isset($rule['unique'])) {
                $model = new $rule['unique']['model']();
                if ($model->emailExists($value, $rule['unique']['except'] ?? null)) {
                    $errors[$field] = $rule['message'] ?? 'Este email ya está registrado';
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitiza entrada de usuario
     */
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Establece mensaje flash
     */
    protected function setFlashMessage($type, $message) {
        $_SESSION['flash_' . $type] = $message;
    }
    
    /**
     * Obtiene y limpia mensaje flash
     */
    protected function getFlashMessage($type) {
        if (isset($_SESSION['flash_' . $type])) {
            $message = $_SESSION['flash_' . $type];
            unset($_SESSION['flash_' . $type]);
            return $message;
        }
        return null;
    }
    
    /**
     * Obtiene el nombre de la página actual basado en la URL
     */
    protected function getCurrentPageName() {
        $url = $_SERVER['REQUEST_URI'];
        $path = parse_url($url, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        
        // Si hay al menos un segmento después de la ruta base
        if (!empty($segments) && $segments[0] !== '') {
            return $segments[0];
        }
        
        return 'home';
    }
    
    /**
     * Log de actividad del usuario
     */
    protected function logActivity($activity, $details = '') {
        if ($this->isLoggedIn) {
            error_log("AgroConecta Activity - User: {$this->currentUser['id_usuario']} ({$this->currentUser['correo']}) - Activity: {$activity} - Details: {$details}");
        }
    }
}
?>