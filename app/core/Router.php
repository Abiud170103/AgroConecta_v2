<?php
/**
 * Router - Sistema de enrutamiento para AgroConecta
 * Maneja las rutas y redirecciona a los controladores correspondientes
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

class Router {
    private $routes;
    private $protected_routes;
    private $role_routes;
    private $route_methods;
    
    public function __construct() {
        // Cargar configuración de rutas
        global $routes, $protected_routes, $role_routes, $route_methods;
        $this->routes = $routes;
        $this->protected_routes = $protected_routes;
        $this->role_routes = $role_routes;
        $this->route_methods = $route_methods;
    }
    
    /**
     * Maneja la solicitud HTTP actual
     */
    public function handleRequest() {
        $url = $this->parseUrl();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Verificar si la ruta existe
        $route = $this->findRoute($url);
        
        if (!$route) {
            $this->handleNotFound();
            return;
        }
        
        // Verificar método HTTP
        if (!$this->isMethodAllowed($url, $method)) {
            $this->handleMethodNotAllowed();
            return;
        }
        
        // Verificar autenticación
        if ($this->requiresAuth($url)) {
            if (!$this->isAuthenticated()) {
                $this->redirectToLogin();
                return;
            }
            
            // Verificar permisos de rol
            if (!$this->hasPermission($url)) {
                $this->handleForbidden();
                return;
            }
        }
        
        // Ejecutar el controlador
        $this->executeController($route, $url);
    }
    
    /**
     * Parsea la URL actual
     */
    private function parseUrl() {
        $url = $_GET['url'] ?? '';
        $url = rtrim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return $url;
    }
    
    /**
     * Encuentra la ruta correspondiente a la URL
     */
    private function findRoute($url) {
        // Buscar coincidencia exacta
        if (isset($this->routes[$url])) {
            return $this->routes[$url];
        }
        
        // Buscar coincidencias con parámetros
        foreach ($this->routes as $route => $controller) {
            if ($this->matchWildcardRoute($url, $route)) {
                return $controller;
            }
        }
        
        return null;
    }
    
    /**
     * Verifica si una ruta coincide con patrones wildcard
     */
    private function matchWildcardRoute($url, $route) {
        // Convertir la ruta a expresión regular
        $pattern = str_replace('*', '.*', $route);
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        
        return preg_match($pattern, $url);
    }
    
    /**
     * Verifica si el método HTTP está permitido para la ruta
     */
    private function isMethodAllowed($url, $method) {
        // Si no hay restricciones específicas, permitir GET y POST por defecto
        if (!isset($this->route_methods[$url])) {
            return in_array($method, ['GET', 'POST']);
        }
        
        return in_array($method, $this->route_methods[$url]);
    }
    
    /**
     * Verifica si la ruta requiere autenticación
     */
    private function requiresAuth($url) {
        foreach ($this->protected_routes as $protected) {
            if ($this->matchWildcardRoute($url, $protected)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Verifica si el usuario está autenticado
     */
    private function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Verifica si el usuario tiene permisos para acceder a la ruta
     */
    private function hasPermission($url) {
        if (!isset($_SESSION['user_role'])) {
            return false;
        }
        
        $userRole = $_SESSION['user_role'];
        
        // Si no hay restricciones de rol para esta ruta, permitir acceso
        if (!isset($this->role_routes[$userRole])) {
            return true;
        }
        
        foreach ($this->role_routes[$userRole] as $allowedRoute) {
            if ($this->matchWildcardRoute($url, $allowedRoute)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Ejecuta el controlador correspondiente
     */
    private function executeController($route, $url) {
        list($controllerName, $method) = explode('@', $route);
        
        $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';
        
        if (!file_exists($controllerFile)) {
            $this->handleNotFound();
            return;
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            $this->handleNotFound();
            return;
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $method)) {
            $this->handleNotFound();
            return;
        }
        
        // Extraer parámetros de la URL si los hay
        $params = $this->extractParams($url);
        
        // Ejecutar el método del controlador
        call_user_func_array([$controller, $method], $params);
    }
    
    /**
     * Extrae parámetros de la URL
     */
    private function extractParams($url) {
        $segments = explode('/', $url);
        // Los parámetros suelen estar después de la ruta base
        // Esto puede personalizarse según las necesidades
        return array_slice($segments, 2);
    }
    
    /**
     * Maneja rutas no encontradas (404)
     */
    private function handleNotFound() {
        http_response_code(404);
        require_once APP_PATH . '/views/shared/404.php';
    }
    
    /**
     * Maneja métodos no permitidos (405)
     */
    private function handleMethodNotAllowed() {
        http_response_code(405);
        require_once APP_PATH . '/views/shared/405.php';
    }
    
    /**
     * Maneja acceso prohibido (403)
     */
    private function handleForbidden() {
        http_response_code(403);
        require_once APP_PATH . '/views/shared/403.php';
    }
    
    /**
     * Redirecciona al login
     */
    private function redirectToLogin() {
        $redirectUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        header('Location: ' . BASE_URL . '/login?redirect=' . urlencode($redirectUrl));
        exit;
    }
}
?>