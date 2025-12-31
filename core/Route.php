<?php
/**
 * Route - Clase para manejar rutas individuales
 * Representa una ruta específica con su patrón, controlador y middleware
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

class Route {
    private $method;
    private $pattern;
    private $handler;
    private $middleware = [];
    private $parameters = [];
    private $name;
    
    /**
     * Constructor
     */
    public function __construct($method, $pattern, $handler) {
        $this->method = strtoupper($method);
        $this->pattern = $pattern;
        $this->handler = $handler;
    }
    
    /**
     * Agregar middleware a la ruta
     */
    public function middleware($middleware) {
        if (is_string($middleware)) {
            $this->middleware[] = $middleware;
        } elseif (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        }
        
        return $this;
    }
    
    /**
     * Asignar nombre a la ruta
     */
    public function name($name) {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Verificar si la ruta coincide con la URI y método
     */
    public function matches($uri, $method) {
        if ($this->method !== strtoupper($method)) {
            return false;
        }
        
        $pattern = $this->convertPatternToRegex($this->pattern);
        
        if (preg_match($pattern, $uri, $matches)) {
            // Extraer parámetros nombrados
            $this->extractParameters($matches);
            return true;
        }
        
        return false;
    }
    
    /**
     * Ejecutar la ruta
     */
    public function execute() {
        // Ejecutar middleware antes de la acción
        foreach ($this->middleware as $middlewareName) {
            $middleware = $this->resolveMiddleware($middlewareName);
            if ($middleware && !$middleware->handle()) {
                return false; // Middleware bloqueó la ejecución
            }
        }
        
        // Ejecutar el handler
        return $this->callHandler();
    }
    
    /**
     * Obtener parámetros de la ruta
     */
    public function getParameters() {
        return $this->parameters;
    }
    
    /**
     * Obtener un parámetro específico
     */
    public function getParameter($key, $default = null) {
        return $this->parameters[$key] ?? $default;
    }
    
    /**
     * Verificar si tiene middleware específico
     */
    public function hasMiddleware($middleware) {
        return in_array($middleware, $this->middleware);
    }
    
    /**
     * Obtener método HTTP
     */
    public function getMethod() {
        return $this->method;
    }
    
    /**
     * Obtener patrón
     */
    public function getPattern() {
        return $this->pattern;
    }
    
    /**
     * Obtener handler
     */
    public function getHandler() {
        return $this->handler;
    }
    
    /**
     * Obtener nombre de la ruta
     */
    public function getName() {
        return $this->name;
    }
    
    // MÉTODOS PRIVADOS
    
    /**
     * Convertir patrón de ruta a regex
     */
    private function convertPatternToRegex($pattern) {
        // Escapar caracteres especiales excepto los marcadores de parámetros
        $pattern = preg_quote($pattern, '#');
        
        // Convertir {param} a grupos de captura nombrados
        $pattern = preg_replace('/\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\}/', '(?P<$1>[^/]+)', $pattern);
        
        // Convertir {param?} a grupos opcionales
        $pattern = preg_replace('/\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\\\?\\\\}/', '(?P<$1>[^/]*)', $pattern);
        
        // Asegurar coincidencia exacta
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Extraer parámetros de la coincidencia
     */
    private function extractParameters($matches) {
        $this->parameters = [];
        
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $this->parameters[$key] = $value;
            }
        }
    }
    
    /**
     * Resolver middleware por nombre
     */
    private function resolveMiddleware($middlewareName) {
        $middlewareMap = [
            'auth' => 'AuthMiddleware',
            'admin' => 'AdminMiddleware',
            'vendor' => 'VendorMiddleware',
            'guest' => 'GuestMiddleware',
            'csrf' => 'CsrfMiddleware'
        ];
        
        if (isset($middlewareMap[$middlewareName])) {
            $middlewareClass = $middlewareMap[$middlewareName];
            
            if (class_exists($middlewareClass)) {
                return new $middlewareClass();
            }
        }
        
        return null;
    }
    
    /**
     * Ejecutar el handler
     */
    private function callHandler() {
        if (is_string($this->handler)) {
            // Formato: ControllerName@methodName
            if (strpos($this->handler, '@') !== false) {
                list($controllerName, $method) = explode('@', $this->handler);
                
                $controllerClass = $controllerName;
                if (!class_exists($controllerClass)) {
                    throw new Exception("Controller {$controllerClass} not found");
                }
                
                $controller = new $controllerClass();
                
                if (!method_exists($controller, $method)) {
                    throw new Exception("Method {$method} not found in {$controllerClass}");
                }
                
                // Pasar parámetros al método
                $reflection = new ReflectionMethod($controller, $method);
                $methodParams = [];
                
                foreach ($reflection->getParameters() as $param) {
                    $paramName = $param->getName();
                    if (isset($this->parameters[$paramName])) {
                        $methodParams[] = $this->parameters[$paramName];
                    } elseif ($param->isDefaultValueAvailable()) {
                        $methodParams[] = $param->getDefaultValue();
                    } else {
                        $methodParams[] = null;
                    }
                }
                
                return call_user_func_array([$controller, $method], $methodParams);
            }
        } elseif (is_callable($this->handler)) {
            // Closure o función
            return call_user_func_array($this->handler, array_values($this->parameters));
        }
        
        throw new Exception("Invalid route handler");
    }
}
?>