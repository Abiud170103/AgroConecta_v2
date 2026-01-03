<?php
/**
 * RouteMiddleware - Aplicación de middleware por rutas
 * Maneja qué middleware aplicar a cada ruta específica
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

class RouteMiddleware {
    
    /**
     * Configuración de middleware por rutas
     * Define qué middleware aplicar a cada patrón de ruta
     */
    private static $routeMiddleware = [
        // Rutas que requieren autenticación
        'dashboard*' => ['AuthMiddleware'],
        'perfil*' => ['AuthMiddleware'],
        'productos/crear' => ['AuthMiddleware', 'VendorOnlyMiddleware'],
        'productos/editar*' => ['AuthMiddleware', 'VendorOnlyMiddleware'],
        'productos/eliminar*' => ['AuthMiddleware', 'VendorOnlyMiddleware'],
        
        // Rutas de administración
        'admin*' => ['AuthMiddleware', 'AdminMiddleware'],
        'usuarios/gestionar*' => ['AuthMiddleware', 'AdminMiddleware'],
        
        // Rutas que requieren vendedor
        'ventas*' => ['AuthMiddleware', 'VendorOnlyMiddleware'],
        'inventario*' => ['AuthMiddleware', 'VendorOnlyMiddleware'],
        
        // Rutas solo para invitados
        'login' => ['GuestMiddleware'],
        'registro' => ['GuestMiddleware'],
        'forgot-password' => ['GuestMiddleware'],
        
        // Rutas con protección CSRF
        'POST:login' => ['GuestMiddleware', 'CsrfMiddleware'],
        'POST:registro' => ['GuestMiddleware', 'CsrfMiddleware'],
        'POST:productos*' => ['AuthMiddleware', 'CsrfMiddleware'],
        'POST:perfil*' => ['AuthMiddleware', 'CsrfMiddleware'],
    ];
    
    /**
     * Aplicar middleware basado en la ruta actual
     */
    public static function apply($currentRoute, $method = 'GET') {
        $currentRoute = ltrim($currentRoute, '/');
        $fullRoute = $method . ':' . $currentRoute;
        
        // Buscar middleware específico por método y ruta
        if (isset(self::$routeMiddleware[$fullRoute])) {
            self::executeMiddleware(self::$routeMiddleware[$fullRoute]);
            return;
        }
        
        // Buscar middleware por ruta sin método
        foreach (self::$routeMiddleware as $routePattern => $middlewares) {
            // Saltar rutas específicas de método
            if (strpos($routePattern, ':') !== false) {
                continue;
            }
            
            if (self::matchRoute($routePattern, $currentRoute)) {
                self::executeMiddleware($middlewares);
                return;
            }
        }
    }
    
    /**
     * Ejecutar lista de middleware
     */
    private static function executeMiddleware($middlewares) {
        foreach ($middlewares as $middlewareClass) {
            if (class_exists($middlewareClass)) {
                $middleware = new $middlewareClass();
                if (method_exists($middleware, 'handle')) {
                    $result = $middleware->handle();
                    if ($result === false) {
                        break; // Si el middleware retorna false, detener la cadena
                    }
                }
            } else {
                error_log("Middleware class not found: $middlewareClass");
            }
        }
    }
    
    /**
     * Verificar si una ruta coincide con un patrón
     */
    private static function matchRoute($pattern, $route) {
        // Convertir patrón con * a regex
        $regex = str_replace('*', '.*', preg_quote($pattern, '/'));
        $regex = '/^' . $regex . '$/';
        
        return preg_match($regex, $route);
    }
    
    /**
     * Agregar middleware a una ruta específica
     */
    public static function addRouteMiddleware($route, $middlewares) {
        if (!is_array($middlewares)) {
            $middlewares = [$middlewares];
        }
        
        self::$routeMiddleware[$route] = array_merge(
            self::$routeMiddleware[$route] ?? [],
            $middlewares
        );
    }
    
    /**
     * Obtener middleware configurado para una ruta
     */
    public static function getRouteMiddleware($route) {
        return self::$routeMiddleware[$route] ?? [];
    }
    
    /**
     * Obtener toda la configuración de middleware
     */
    public static function getAllRouteMiddleware() {
        return self::$routeMiddleware;
    }
    
    /**
     * Aplicar middleware global (se ejecuta en todas las rutas)
     */
    public static function applyGlobalMiddleware() {
        // Middleware que se ejecuta en todas las solicitudes
        $globalMiddlewares = [
            // Aquí puedes agregar middleware global como rate limiting
        ];
        
        self::executeMiddleware($globalMiddlewares);
    }
    
    /**
     * Verificar si una ruta requiere autenticación
     */
    public static function requiresAuth($route) {
        foreach (self::$routeMiddleware as $routePattern => $middlewares) {
            if (self::matchRoute($routePattern, $route)) {
                return in_array('AuthMiddleware', $middlewares);
            }
        }
        return false;
    }
    
    /**
     * Verificar si una ruta requiere permisos de administrador
     */
    public static function requiresAdmin($route) {
        foreach (self::$routeMiddleware as $routePattern => $middlewares) {
            if (self::matchRoute($routePattern, $route)) {
                return in_array('AdminMiddleware', $middlewares);
            }
        }
        return false;
    }
}
?>