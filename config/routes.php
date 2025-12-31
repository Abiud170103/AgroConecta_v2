<?php
/**
 * Configuración de rutas para AgroConecta
 * Define las rutas del sistema y sus controladores correspondientes
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

// Rutas del sistema
$routes = [
    // Rutas principales
    '' => 'HomeController@index',
    'home' => 'HomeController@index',
    'buscar' => 'HomeController@buscar',
    
    // Rutas de autenticación
    'login' => 'AuthController@login',
    'logout' => 'AuthController@logout',
    'registro' => 'AuthController@registro',
    'registro/cliente' => 'AuthController@registroCliente',
    'registro/vendedor' => 'AuthController@registroVendedor',
    'recuperar-password' => 'AuthController@recuperarPassword',
    'reset-password' => 'AuthController@resetPassword',
    
    // Rutas del cliente
    'cliente/dashboard' => 'ClienteController@dashboard',
    'cliente/perfil' => 'ClienteController@perfil',
    'cliente/pedidos' => 'ClienteController@pedidos',
    'cliente/carrito' => 'ClienteController@carrito',
    'cliente/checkout' => 'ClienteController@checkout',
    'cliente/direcciones' => 'ClienteController@direcciones',
    
    // Rutas del vendedor
    'vendedor/dashboard' => 'VendedorController@dashboard',
    'vendedor/perfil' => 'VendedorController@perfil',
    
    // Rutas de gestión de productos para vendedores
    'vendor/products' => 'VendorProductController@index',
    'vendor/products/create' => 'VendorProductController@create',
    'vendor/products/store' => 'VendorProductController@store',
    'vendor/products/edit' => 'VendorProductController@edit',
    'vendor/products/update' => 'VendorProductController@update',
    'vendor/products/delete' => 'VendorProductController@delete',
    'vendor/products/duplicate' => 'VendorProductController@duplicate',
    'vendor/products/status' => 'VendorProductController@updateStatus',
    'vendor/products/stock' => 'VendorProductController@updateStock',
    'vendor/products/bulk-action' => 'VendorProductController@bulkAction',
    'vendor/products/stats' => 'VendorProductController@getStats',
    'vendor/products/search' => 'VendorProductController@search',
    
    // Rutas compatibilidad (redirect a las nuevas rutas)
    'vendedor/productos' => 'VendorProductController@index',
    'vendedor/productos/agregar' => 'VendorProductController@create',
    'vendedor/productos/editar' => 'VendorProductController@edit',
    'vendedor/productos/eliminar' => 'VendorProductController@delete',
    'vendedor/pedidos' => 'VendedorController@pedidos',
    'vendedor/inventario' => 'VendedorController@inventario',
    
    // Rutas de productos
    'productos' => 'ProductoController@index',
    'producto' => 'ProductoController@detalle',
    'productos/categoria' => 'ProductoController@porCategoria',
    
    // Rutas de carrito y pagos
    'carrito/agregar' => 'CarritoController@agregar',
    'carrito/actualizar' => 'CarritoController@actualizar',
    'carrito/eliminar' => 'CarritoController@eliminar',
    'carrito/vaciar' => 'CarritoController@vaciar',
    'pago/procesar' => 'PagoController@procesar',
    'pago/confirmacion' => 'PagoController@confirmacion',
    'pago/webhook' => 'PagoController@webhook',
    
    // Rutas de pedidos
    'pedido/crear' => 'PedidoController@crear',
    'pedido/detalle' => 'PedidoController@detalle',
    'pedido/actualizar-estado' => 'PedidoController@actualizarEstado',
    'pedido/cancelar' => 'PedidoController@cancelar',
    
    // Rutas de API (para AJAX)
    'api/productos/buscar' => 'ApiController@buscarProductos',
    'api/carrito/cantidad' => 'ApiController@cantidadCarrito',
    'api/upload/imagen' => 'ApiController@subirImagen',
    'api/direccion/validar' => 'ApiController@validarDireccion',
    
    // Rutas adicionales
    'acerca' => 'HomeController@acerca',
    'contacto' => 'HomeController@contacto',
    'terminos' => 'HomeController@terminos',
    'privacidad' => 'HomeController@privacidad',
    'error' => 'HomeController@error',
    
    // Rutas de administración (futuras)
    'admin' => 'AdminController@index',
    'admin/usuarios' => 'AdminController@usuarios',
    'admin/reportes' => 'AdminController@reportes',
];

// Rutas que requieren autenticación
$protected_routes = [
    'cliente/*',
    'vendedor/*',
    'carrito/*',
    'pedido/*',
    'admin/*'
];

// Rutas específicas para roles
$role_routes = [
    'cliente' => [
        'cliente/*',
        'carrito/*',
        'pago/*'
    ],
    'vendedor' => [
        'vendedor/*',
        'pedido/actualizar-estado'
    ],
    'admin' => [
        'admin/*'
    ]
];

// Métodos HTTP permitidos por ruta
$route_methods = [
    'login' => ['GET', 'POST'],
    'registro/cliente' => ['GET', 'POST'],
    'registro/vendedor' => ['GET', 'POST'],
    'recuperar-password' => ['GET', 'POST'],
    'carrito/agregar' => ['POST'],
    'carrito/actualizar' => ['POST'],
    'carrito/eliminar' => ['POST'],
    'pago/procesar' => ['POST'],
    'pago/webhook' => ['POST'],
    
    // Métodos para gestión de productos de vendedores
    'vendor/products' => ['GET'],
    'vendor/products/create' => ['GET'],
    'vendor/products/store' => ['POST'],
    'vendor/products/edit' => ['GET'],
    'vendor/products/update' => ['PUT'],
    'vendor/products/delete' => ['DELETE'],
    'vendor/products/duplicate' => ['POST'],
    'vendor/products/status' => ['PATCH'],
    'vendor/products/stock' => ['PATCH'],
    'vendor/products/bulk-action' => ['POST'],
    'vendor/products/stats' => ['GET'],
    'vendor/products/search' => ['GET'],
    
    'api/*' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH']
];
?>