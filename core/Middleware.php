<?php
/**
 * Middleware - Sistema de middleware para el router
 * Clases de middleware para autenticación y autorización
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

/**
 * Interfaz base para middleware
 */
interface MiddlewareInterface {
    public function handle();
}

/**
 * Middleware de autenticación
 */
class AuthMiddleware implements MiddlewareInterface {
    public function handle() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            // Guardar URL a la que quería acceder
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';
            
            header('Location: /login');
            exit;
        }
        
        return true;
    }
}

/**
 * Middleware para usuarios invitados (no autenticados)
 */
class GuestMiddleware implements MiddlewareInterface {
    public function handle() {
        session_start();
        
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        return true;
    }
}

/**
 * Middleware para administradores
 */
class AdminMiddleware implements MiddlewareInterface {
    public function handle() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';
            header('Location: /login');
            exit;
        }
        
        // Verificar que es administrador
        require_once '../app/models/Usuario.php';
        $usuarioModel = new Usuario();
        $user = $usuarioModel->find($_SESSION['user_id']);
        
        if (!$user || $user['tipo_usuario'] !== 'admin') {
            http_response_code(403);
            header('Location: /dashboard?error=access_denied');
            exit;
        }
        
        return true;
    }
}

/**
 * Middleware para vendedores
 */
class VendorMiddleware implements MiddlewareInterface {
    public function handle() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';
            header('Location: /login');
            exit;
        }
        
        // Verificar que es vendedor
        require_once '../app/models/Usuario.php';
        $usuarioModel = new Usuario();
        $user = $usuarioModel->find($_SESSION['user_id']);
        
        if (!$user || $user['tipo_usuario'] !== 'vendedor') {
            http_response_code(403);
            header('Location: /dashboard?error=access_denied');
            exit;
        }
        
        return true;
    }
}

/**
 * Middleware para verificación CSRF
 */
class CsrfMiddleware implements MiddlewareInterface {
    public function handle() {
        session_start();
        
        // Solo verificar en métodos POST, PUT, DELETE, PATCH
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return true;
        }
        
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = $_SESSION['_token'] ?? '';
        
        if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
            http_response_code(419); // Page Expired
            
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Token CSRF inválido o expirado',
                    'code' => 'CSRF_INVALID'
                ]);
                exit;
            }
            
            // Redirigir con error
            $_SESSION['error'] = 'Token de seguridad inválido. Por favor, inténtalo de nuevo.';
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$referer}");
            exit;
        }
        
        return true;
    }
    
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

/**
 * Middleware para verificar cuenta activa
 */
class ActiveAccountMiddleware implements MiddlewareInterface {
    public function handle() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            return true; // Dejar que AuthMiddleware maneje esto
        }
        
        require_once '../app/models/Usuario.php';
        $usuarioModel = new Usuario();
        $user = $usuarioModel->find($_SESSION['user_id']);
        
        if (!$user || !$user['activo']) {
            // Cerrar sesión del usuario inactivo
            session_destroy();
            
            $_SESSION['error'] = 'Tu cuenta ha sido suspendida. Contacta al administrador.';
            header('Location: /login');
            exit;
        }
        
        return true;
    }
}

/**
 * Middleware para logging de actividad
 */
class ActivityLogMiddleware implements MiddlewareInterface {
    public function handle() {
        session_start();
        
        // Solo logear para usuarios autenticados
        if (!isset($_SESSION['user_id'])) {
            return true;
        }
        
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userId = $_SESSION['user_id'];
        
        // Log simple (se podría guardar en BD)
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'method' => $method,
            'uri' => $uri,
            'ip' => $ip,
            'user_agent' => $userAgent
        ];
        
        error_log("User Activity: " . json_encode($logEntry));
        
        return true;
    }
}

/**
 * Middleware para throttling/rate limiting
 */
class ThrottleMiddleware implements MiddlewareInterface {
    private $maxAttempts = 60; // requests
    private $decayMinutes = 1; // minuto
    
    public function __construct($maxAttempts = 60, $decayMinutes = 1) {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }
    
    public function handle() {
        $key = $this->getThrottleKey();
        
        if ($this->tooManyAttempts($key)) {
            http_response_code(429); // Too Many Requests
            
            header('Retry-After: ' . $this->getRetryAfter($key));
            
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Demasiadas solicitudes. Intenta de nuevo más tarde.',
                    'code' => 'RATE_LIMIT_EXCEEDED'
                ]);
                exit;
            }
            
            echo "<h1>429 - Demasiadas solicitudes</h1>";
            echo "<p>Has excedido el límite de solicitudes. Intenta de nuevo más tarde.</p>";
            exit;
        }
        
        $this->incrementAttempts($key);
        
        return true;
    }
    
    private function getThrottleKey() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return 'throttle:' . md5($ip . '|' . $uri);
    }
    
    private function tooManyAttempts($key) {
        if (!isset($_SESSION[$key])) {
            return false;
        }
        
        $data = $_SESSION[$key];
        $windowStart = time() - ($this->decayMinutes * 60);
        
        // Limpiar intentos antiguos
        $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        $_SESSION[$key] = $data;
        
        return count($data['attempts']) >= $this->maxAttempts;
    }
    
    private function incrementAttempts($key) {
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => []];
        }
        
        $_SESSION[$key]['attempts'][] = time();
    }
    
    private function getRetryAfter($key) {
        if (!isset($_SESSION[$key]['attempts']) || empty($_SESSION[$key]['attempts'])) {
            return $this->decayMinutes * 60;
        }
        
        $oldestAttempt = min($_SESSION[$key]['attempts']);
        return max(1, ($oldestAttempt + ($this->decayMinutes * 60)) - time());
    }
    
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
?>