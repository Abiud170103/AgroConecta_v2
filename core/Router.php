<?php
/**
 * Router - Sistema de enrutamiento principal
 * Maneja todas las rutas y su resolución a controladores
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'Route.php';

class Router {
    private $routes = [];
    private $namedRoutes = [];
    private $currentRoute = null;
    private $basePath = '';
    
    /**
     * Constructor
     */
    public function __construct($basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }
    
    /**
     * Registrar ruta GET
     */
    public function get($pattern, $handler) {
        return $this->addRoute('GET', $pattern, $handler);
    }
    
    /**
     * Registrar ruta POST
     */
    public function post($pattern, $handler) {
        return $this->addRoute('POST', $pattern, $handler);
    }
    
    /**
     * Registrar ruta PUT
     */
    public function put($pattern, $handler) {
        return $this->addRoute('PUT', $pattern, $handler);
    }
    
    /**
     * Registrar ruta DELETE
     */
    public function delete($pattern, $handler) {
        return $this->addRoute('DELETE', $pattern, $handler);
    }
    
    /**
     * Registrar ruta PATCH
     */
    public function patch($pattern, $handler) {
        return $this->addRoute('PATCH', $pattern, $handler);
    }
    
    /**
     * Registrar múltiples métodos para una ruta
     */
    public function match($methods, $pattern, $handler) {
        $route = null;
        foreach ($methods as $method) {
            $route = $this->addRoute($method, $pattern, $handler);
        }
        return $route;
    }
    
    /**
     * Registrar ruta para todos los métodos
     */
    public function any($pattern, $handler) {
        return $this->match(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $pattern, $handler);
    }
    
    /**
     * Grupo de rutas con prefijo y/o middleware común
     */
    public function group($attributes, $callback) {
        $previousPrefix = $this->currentPrefix ?? '';
        $previousMiddleware = $this->currentMiddleware ?? [];
        
        $this->currentPrefix = $previousPrefix . ($attributes['prefix'] ?? '');
        $this->currentMiddleware = array_merge($previousMiddleware, $attributes['middleware'] ?? []);
        
        call_user_func($callback, $this);
        
        $this->currentPrefix = $previousPrefix;
        $this->currentMiddleware = $previousMiddleware;
    }
    
    /**
     * Procesar la solicitud actual
     */
    public function dispatch($uri = null, $method = null) {
        $uri = $uri ?? $this->getCurrentUri();
        $method = $method ?? $this->getCurrentMethod();
        
        // Limpiar URI
        $uri = $this->cleanUri($uri);
        
        // Buscar ruta que coincida
        foreach ($this->routes as $route) {
            if ($route->matches($uri, $method)) {
                $this->currentRoute = $route;
                
                try {
                    return $route->execute();
                } catch (Exception $e) {
                    return $this->handleException($e);
                }
            }
        }
        
        // No se encontró ruta
        return $this->handleNotFound();
    }
    
    /**
     * Generar URL para ruta nombrada
     */
    public function url($name, $parameters = []) {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Route '{$name}' not found");
        }
        
        $route = $this->namedRoutes[$name];
        $pattern = $route->getPattern();
        
        // Reemplazar parámetros en el patrón
        foreach ($parameters as $key => $value) {
            $pattern = str_replace('{' . $key . '}', $value, $pattern);
            $pattern = str_replace('{' . $key . '?}', $value, $pattern);
        }
        
        // Limpiar parámetros opcionales no utilizados
        $pattern = preg_replace('/\{[^}]+\?\}/', '', $pattern);
        
        return $this->basePath . $pattern;
    }
    
    /**
     * Obtener ruta actual
     */
    public function getCurrentRoute() {
        return $this->currentRoute;
    }
    
    /**
     * Obtener todas las rutas
     */
    public function getRoutes() {
        return $this->routes;
    }
    
    /**
     * Obtener rutas nombradas
     */
    public function getNamedRoutes() {
        return $this->namedRoutes;
    }
    
    /**
     * Verificar si existe una ruta
     */
    public function hasRoute($name) {
        return isset($this->namedRoutes[$name]);
    }
    
    // MÉTODOS PRIVADOS
    
    private $currentPrefix = '';
    private $currentMiddleware = [];
    
    /**
     * Agregar ruta al registro
     */
    private function addRoute($method, $pattern, $handler) {
        // Aplicar prefijo si existe
        $pattern = $this->currentPrefix . $pattern;
        
        $route = new Route($method, $pattern, $handler);
        
        // Aplicar middleware del grupo si existe
        if (!empty($this->currentMiddleware)) {
            $route->middleware($this->currentMiddleware);
        }
        
        $this->routes[] = $route;
        
        return $route;
    }
    
    /**
     * Obtener URI actual
     */
    private function getCurrentUri() {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remover query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Remover base path
        if ($this->basePath && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        
        return $uri ?: '/';
    }
    
    /**
     * Obtener método HTTP actual
     */
    private function getCurrentMethod() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Soportar method spoofing para formularios
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        return $method;
    }
    
    /**
     * Limpiar URI
     */
    private function cleanUri($uri) {
        // Normalizar barras
        $uri = '/' . trim($uri, '/');
        
        // Convertir múltiples barras en una
        $uri = preg_replace('#/+#', '/', $uri);
        
        return $uri;
    }
    
    /**
     * Manejar excepción en ruta
     */
    private function handleException($exception) {
        error_log("Route Exception: " . $exception->getMessage());
        
        // En desarrollo, mostrar error detallado
        if (defined('DEBUG') && DEBUG) {
            echo "<h1>Route Error</h1>";
            echo "<p>" . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
            return;
        }
        
        // En producción, página de error genérica
        http_response_code(500);
        include '../app/views/errors/500.php';
    }
    
    /**
     * Manejar ruta no encontrada
     */
    private function handleNotFound() {
        http_response_code(404);
        
        // Intentar cargar página de error personalizada
        if (file_exists('../app/views/errors/404.php')) {
            include '../app/views/errors/404.php';
        } else {
            echo "<h1>404 - Página no encontrada</h1>";
            echo "<p>La página que buscas no existe.</p>";
            echo "<a href='/'>Volver al inicio</a>";
        }
    }
}
?>