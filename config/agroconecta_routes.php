<?php
/**
 * routes.php - Definición de todas las rutas de AgroConecta
 * Mapeo de URLs a controladores y acciones
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

// ============================================
// RUTAS PÚBLICAS (sin autenticación)
// ============================================

// Página principal y páginas estáticas
$router->get('/', 'HomeController@index')->name('home');
$router->get('/acerca', 'HomeController@about')->name('about');
$router->get('/contacto', 'HomeController@contact')->name('contact');
$router->post('/contacto', 'HomeController@processContact')->name('contact.process');
$router->get('/faq', 'HomeController@faq')->name('faq');
$router->get('/terminos', 'HomeController@terms')->name('terms');
$router->get('/privacidad', 'HomeController@privacy')->name('privacy');

// Búsqueda rápida
$router->get('/buscar', 'HomeController@search')->name('search');
$router->get('/api/sugerencias', 'HomeController@searchSuggestions')->name('search.suggestions');

// ============================================
// PRODUCTOS (públicos)
// ============================================

$router->get('/productos', 'ProductController@index')->name('products');
$router->get('/productos/buscar', 'ProductController@search')->name('products.search');
$router->get('/productos/ver/{id}', 'ProductController@show')->name('products.show');
$router->get('/productos/categoria/{categoria}', 'ProductController@category')->name('products.category');
$router->get('/productos/comparar', 'ProductController@compare')->name('products.compare');

// API para productos
$router->get('/api/productos/autocompletar', 'ProductController@autocomplete')->name('products.autocomplete');

// ============================================
// AUTENTICACIÓN
// ============================================

// Rutas para invitados (no autenticados)
$router->group(['middleware' => ['guest']], function($router) {
    $router->get('/login', 'AuthController@showLogin')->name('login');
    $router->post('/login', 'AuthController@processLogin')->name('login.process');
    $router->get('/registro', 'AuthController@showRegister')->name('register');
    $router->post('/registro', 'AuthController@processRegister')->name('register.process');
    $router->get('/olvide-password', 'AuthController@showForgotPassword')->name('password.forgot');
    $router->post('/olvide-password', 'AuthController@processForgotPassword')->name('password.forgot.process');
    $router->get('/reset-password/{token}', 'AuthController@showResetPassword')->name('password.reset');
    $router->post('/reset-password', 'AuthController@processResetPassword')->name('password.reset.process');
});

// Verificación de email (accesible sin login)
$router->get('/verificar-email/{token}', 'AuthController@verifyEmail')->name('email.verify');

// Logout (solo para autenticados)
$router->get('/logout', 'AuthController@logout')->name('logout')->middleware('auth');

// ============================================
// RUTAS AUTENTICADAS
// ============================================

$router->group(['middleware' => ['auth']], function($router) {
    
    // Dashboard principal
    $router->get('/dashboard', 'UserController@dashboard')->name('dashboard');
    
    // ============================================
    // PERFIL DE USUARIO
    // ============================================
    
    $router->get('/usuario/perfil', 'UserController@profile')->name('user.profile');
    $router->post('/usuario/perfil', 'UserController@updateProfile')->name('user.profile.update');
    $router->get('/usuario/cambiar-password', 'UserController@changePassword')->name('user.password');
    $router->post('/usuario/cambiar-password', 'UserController@changePassword')->name('user.password.update');
    $router->get('/usuario/notificaciones', 'UserController@notifications')->name('user.notifications');
    $router->post('/usuario/notificaciones', 'UserController@notifications')->name('user.notifications.update');
    $router->get('/usuario/notificaciones/lista', 'UserController@notificationList')->name('user.notifications.list');
    $router->get('/usuario/resenas', 'UserController@reviews')->name('user.reviews');
    $router->get('/usuario/eliminar-cuenta', 'UserController@deleteAccount')->name('user.delete');
    $router->post('/usuario/eliminar-cuenta', 'UserController@deleteAccount')->name('user.delete.process');
    
    // ============================================
    // CARRITO DE COMPRAS
    // ============================================
    
    $router->get('/carrito', 'CartController@index')->name('cart');
    $router->post('/carrito/agregar', 'CartController@add')->name('cart.add');
    $router->post('/carrito/actualizar', 'CartController@update')->name('cart.update');
    $router->post('/carrito/remover', 'CartController@remove')->name('cart.remove');
    $router->post('/carrito/limpiar', 'CartController@clear')->name('cart.clear');
    $router->get('/carrito/checkout', 'CartController@checkout')->name('cart.checkout');
    
    // API del carrito
    $router->get('/api/carrito/info', 'CartController@info')->name('cart.info');
    $router->get('/api/carrito/preview', 'CartController@preview')->name('cart.preview');
    
    // ============================================
    // PEDIDOS
    // ============================================
    
    $router->get('/pedidos', 'OrderController@index')->name('orders');
    $router->get('/pedidos/{id}', 'OrderController@show')->name('orders.show');
    $router->get('/pedidos/checkout', 'OrderController@checkout')->name('orders.checkout');
    $router->post('/pedidos/procesar', 'OrderController@processOrder')->name('orders.process');
    $router->post('/pedidos/{id}/cancelar', 'OrderController@cancel')->name('orders.cancel');
    $router->get('/pedidos/{id}/reordenar', 'OrderController@reorder')->name('orders.reorder');
    
    // ============================================
    // RESEÑAS
    // ============================================
    
    $router->post('/productos/{id}/resena', 'ProductController@addReview')->name('products.review.add');
    $router->post('/api/notificaciones/marcar-leida', 'UserController@markNotificationRead')->name('notifications.mark-read');
    
});

// ============================================
// RUTAS DE VENDEDOR
// ============================================

$router->group(['prefix' => '/vendedor', 'middleware' => ['auth', 'vendor']], function($router) {
    
    $router->get('/dashboard', 'VendorController@dashboard')->name('vendor.dashboard');
    
    // Gestión de productos
    $router->get('/productos', 'VendorController@products')->name('vendor.products');
    $router->get('/productos/crear', 'VendorController@createProduct')->name('vendor.products.create');
    $router->post('/productos/crear', 'VendorController@createProduct')->name('vendor.products.store');
    $router->get('/productos/editar/{id}', 'VendorController@editProduct')->name('vendor.products.edit');
    $router->post('/productos/editar/{id}', 'VendorController@editProduct')->name('vendor.products.update');
    
    // Ventas y pedidos
    $router->get('/pedidos', 'VendorController@orders')->name('vendor.orders');
    $router->get('/resenas', 'VendorController@reviews')->name('vendor.reviews');
    $router->get('/reportes', 'VendorController@reports')->name('vendor.reports');
    
});

// ============================================
// RUTAS DE ADMINISTRADOR
// ============================================

$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function($router) {
    
    $router->get('/', 'AdminController@dashboard')->name('admin.dashboard');
    $router->get('/dashboard', 'AdminController@dashboard')->name('admin.dashboard.alt');
    
    // Gestión de usuarios
    $router->get('/usuarios', 'AdminController@users')->name('admin.users');
    $router->get('/usuarios/{id}', 'AdminController@userDetails')->name('admin.users.show');
    $router->post('/usuarios/toggle-status', 'AdminController@toggleUserStatus')->name('admin.users.toggle');
    
    // Gestión de productos
    $router->get('/productos', 'AdminController@products')->name('admin.products');
    
    // Gestión de pedidos
    $router->get('/pedidos', 'AdminController@orders')->name('admin.orders');
    $router->post('/pedidos/actualizar-estado', 'AdminController@updateOrderStatus')->name('admin.orders.update-status');
    
    // Reportes y estadísticas
    $router->get('/reportes', 'AdminController@reports')->name('admin.reports');
    
    // Configuración del sistema
    $router->get('/configuracion', 'AdminController@settings')->name('admin.settings');
    $router->post('/configuracion', 'AdminController@settings')->name('admin.settings.update');
    
});

// ============================================
// RUTAS API (con throttling)
// ============================================

$router->group(['prefix' => '/api', 'middleware' => ['throttle']], function($router) {
    
    // API públicas
    $router->get('/productos/buscar', 'ProductController@apiSearch')->name('api.products.search');
    $router->get('/categorias', 'ProductController@apiCategories')->name('api.categories');
    
    // API autenticadas
    $router->group(['middleware' => ['auth']], function($router) {
        $router->get('/usuario', 'UserController@apiProfile')->name('api.user.profile');
        $router->get('/carrito/resumen', 'CartController@apiSummary')->name('api.cart.summary');
        $router->get('/pedidos/estados', 'OrderController@apiStatuses')->name('api.orders.statuses');
    });
    
});

// ============================================
// RUTAS DE DESARROLLO (solo en modo debug)
// ============================================

if (defined('DEBUG') && DEBUG) {
    $router->get('/debug/rutas', function() {
        global $router;
        echo "<h1>Rutas Registradas</h1>";
        echo "<table border='1'>";
        echo "<tr><th>Método</th><th>Patrón</th><th>Handler</th><th>Nombre</th></tr>";
        
        foreach ($router->getRoutes() as $route) {
            echo "<tr>";
            echo "<td>" . $route->getMethod() . "</td>";
            echo "<td>" . $route->getPattern() . "</td>";
            echo "<td>" . $route->getHandler() . "</td>";
            echo "<td>" . ($route->getName() ?? '-') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    })->name('debug.routes');
    
    $router->get('/debug/phpinfo', function() {
        phpinfo();
    })->name('debug.phpinfo');
    
    $router->get('/debug/session', function() {
        session_start();
        echo "<h1>Información de Sesión</h1>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    })->name('debug.session');
}

// ============================================
// MANEJADORES DE ERRORES PERSONALIZADOS
// ============================================

// Ruta comodín para manejar 404s personalizados
$router->get('/404', function() {
    http_response_code(404);
    if (file_exists('../app/views/errors/404.php')) {
        include '../app/views/errors/404.php';
    } else {
        echo "<h1>404 - Página no encontrada</h1>";
    }
})->name('error.404');

$router->get('/500', function() {
    http_response_code(500);
    if (file_exists('../app/views/errors/500.php')) {
        include '../app/views/errors/500.php';
    } else {
        echo "<h1>500 - Error interno del servidor</h1>";
    }
})->name('error.500');
?>