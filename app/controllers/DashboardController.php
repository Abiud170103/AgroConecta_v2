<?php
/**
 * Controlador de Dashboard - AgroConecta
 * Dashboards específicos por tipo de usuario con funcionalidades de negocio
 */

// Obtener la ruta raíz del proyecto
$root = dirname(dirname(__DIR__));

require_once $root . '/core/SessionManager.php';
require_once $root . '/core/Database.php';
require_once dirname(__DIR__) . '/models/Model.php';
require_once dirname(__DIR__) . '/models/Usuario.php';
require_once dirname(__DIR__) . '/models/Producto.php';
require_once dirname(__DIR__) . '/models/Pedido.php';

class DashboardController {
    private $userModel;
    private $productoModel;
    private $pedidoModel;
    
    public function __construct() {
        $this->userModel = new Usuario();
        $this->productoModel = new Producto();
        $this->pedidoModel = new Pedido();
        SessionManager::startSecureSession();
    }
    
    /**
     * Verifica que el usuario esté autenticado
     */
    private function requireAuth() {
        if (!SessionManager::isLoggedIn()) {
            SessionManager::setFlash('error', 'Debes iniciar sesión para acceder');
            header('Location: login.php');
            exit;
        }
        return $_SESSION['user_id'];
    }
    
    /**
     * Dashboard principal - redirige según tipo de usuario
     */
    public function index() {
        $userId = $this->requireAuth();
        
        $user = $this->userModel->find($userId);
        if (!$user) {
            SessionManager::setFlash('error', 'Usuario no encontrado');
            header('Location: login.php');
            exit;
        }
        
        // Redirigir según tipo de usuario
        switch ($user['tipo_usuario']) {
            case 'vendedor':
                $this->dashboardVendedor();
                break;
            case 'cliente':
                $this->dashboardCliente();
                break;
            case 'admin':
                $this->dashboardAdmin();
                break;
            default:
                $this->dashboardGeneral();
        }
    }
    
    /**
     * Dashboard específico para vendedores
     */
    public function dashboardVendedor() {
        $userId = $this->requireAuth();
        
        $user = $this->userModel->find($userId);
        
        // Obtener estadísticas de productos
        $statsProductos = $this->productoModel->getStatsVendedor($userId);
        if (!$statsProductos) {
            $statsProductos = [
                'total_productos' => 0,
                'productos_disponibles' => 0,
                'productos_agotados' => 0,
                'precio_promedio' => 0,
                'precio_max' => 0,
                'precio_min' => 0
            ];
        }
        
        // Obtener estadísticas de pedidos/ventas
        $statsPedidos = $this->pedidoModel->getStatsVendedor($userId);
        if (!$statsPedidos) {
            $statsPedidos = [
                'total_pedidos' => 0,
                'ventas_completadas' => 0,
                'pedidos_pendientes' => 0,
                'ingresos_totales' => 0,
                'venta_promedio' => 0
            ];
        }
        
        // Productos recientes del vendedor
        $productosRecientes = $this->productoModel->getByVendedor($userId, 5);
        
        // Pedidos recientes del vendedor
        $pedidosRecientes = $this->pedidoModel->getByVendedor($userId, 5);
        
        // Pedidos pendientes
        $pedidosPendientes = $this->pedidoModel->getByEstado('pendiente', $userId);
        
        // Datos para gráficos (últimos 7 días)
        $fechaInicio = date('Y-m-d', strtotime('-7 days'));
        $fechaFin = date('Y-m-d');
        $ventasPorDia = $this->pedidoModel->getVentasPorPeriodo($userId, $fechaInicio, $fechaFin);
        
        include '../app/views/dashboard/vendedor.php';
    }
    
    /**
     * Dashboard específico para clientes
     */
    public function dashboardCliente() {
        $userId = $this->requireAuth();
        
        $user = $this->userModel->find($userId);
        
        // Obtener estadísticas del cliente
        $statsCliente = $this->pedidoModel->getStatsCliente($userId);
        if (!$statsCliente) {
            $statsCliente = [
                'total_pedidos' => 0,
                'pedidos_completados' => 0,
                'pedidos_activos' => 0,
                'dinero_gastado' => 0,
                'ticket_promedio' => 0
            ];
        }
        
        // Pedidos recientes del cliente
        $pedidosRecientes = $this->pedidoModel->getByCliente($userId, 5);
        
        // Productos recomendados (por ahora los más recientes)
        $productosRecomendados = $this->productoModel->getDisponibles(8);
        
        // Productos destacados
        $productosDestacados = $this->productoModel->getDestacados(4);
        
        // Categorías populares
        $categorias = $this->productoModel->getCategorias();
        
        // Ofertas especiales (productos con descuento o precios bajos)
        $ofertas = $this->productoModel->getByRangoPrecio(0, 100, 6);
        
        include '../app/views/dashboard/cliente.php';
    }
    
    /**
     * Dashboard para administradores
     */
    public function dashboardAdmin() {
        $userId = $this->requireAuth();
        
        // Verificar que sea admin
        $user = $this->userModel->find($userId);
        if ($user['tipo_usuario'] !== 'admin') {
            SessionManager::setFlash('error', 'Acceso denegado');
            header('Location: dashboard.php');
            exit;
        }
        
        // Estadísticas generales del sistema
        $totalUsuarios = $this->userModel->count(['activo' => 1]);
        $totalVendedores = $this->userModel->count(['tipo_usuario' => 'vendedor', 'activo' => 1]);
        $totalClientes = $this->userModel->count(['tipo_usuario' => 'cliente', 'activo' => 1]);
        $totalProductos = $this->productoModel->count(['activo' => 1]);
        $totalPedidos = $this->pedidoModel->count();
        
        // Usuarios recientes
        $usuariosRecientes = $this->userModel->all();
        $usuariosRecientes = array_slice(array_reverse($usuariosRecientes), 0, 10);
        
        // Productos recientes
        $productosRecientes = $this->productoModel->getDisponibles(10);
        
        // Pedidos recientes
        $pedidosRecientes = $this->pedidoModel->getRecientes(10);
        
        $statsGenerales = [
            'total_usuarios' => $totalUsuarios,
            'total_vendedores' => $totalVendedores,
            'total_clientes' => $totalClientes,
            'total_productos' => $totalProductos,
            'total_pedidos' => $totalPedidos
        ];
        
        include '../app/views/dashboard/admin.php';
    }
    
    /**
     * Dashboard general/básico
     */
    public function dashboardGeneral() {
        $userId = $this->requireAuth();
        
        $user = $this->userModel->find($userId);
        
        // Datos básicos
        $productosRecientes = $this->productoModel->getDisponibles(6);
        
        include '../app/views/dashboard/general.php';
    }
    
    /**
     * Vista del catálogo de productos
     */
    public function catalogo() {
        $userId = $this->requireAuth();
        
        // Parámetros de búsqueda y filtros
        $busqueda = $_GET['q'] ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $precioMin = $_GET['precio_min'] ?? '';
        $precioMax = $_GET['precio_max'] ?? '';
        $ubicacion = $_GET['ubicacion'] ?? '';
        
        // Obtener productos
        if (!empty($busqueda)) {
            $filtros = array_filter([
                'categoria' => $categoria,
                'precio_min' => $precioMin,
                'precio_max' => $precioMax,
                'ubicacion' => $ubicacion
            ]);
            
            $productos = $this->productoModel->buscar($busqueda, $filtros);
        } else {
            $productos = $this->productoModel->getDisponibles(50, $categoria);
        }
        
        // Obtener todas las categorías para filtros
        $categorias = $this->productoModel->getCategorias();
        
        include '../app/views/dashboard/catalogo.php';
    }
    
    /**
     * Gestión de productos para vendedores
     */
    public function misProductos() {
        $userId = $this->requireAuth();
        
        // Verificar que sea vendedor
        $user = $this->userModel->find($userId);
        if ($user['tipo_usuario'] !== 'vendedor') {
            SessionManager::setFlash('error', 'Solo los vendedores pueden acceder a esta sección');
            header('Location: dashboard.php');
            exit;
        }
        
        // Obtener productos del vendedor
        $productos = $this->productoModel->getByVendedor($userId);
        
        // Estadísticas
        $stats = $this->productoModel->getStatsVendedor($userId);
        
        include '../app/views/dashboard/mis-productos.php';
    }
    
    /**
     * Gestión de pedidos
     */
    public function misPedidos() {
        $userId = $this->requireAuth();
        
        $user = $this->userModel->find($userId);
        
        if ($user['tipo_usuario'] === 'vendedor') {
            // Vista para vendedores - pedidos a cumplir
            $pedidos = $this->pedidoModel->getByVendedor($userId);
            $titulo = 'Pedidos Recibidos';
            $esVendedor = true;
        } else {
            // Vista para clientes - pedidos realizados
            $pedidos = $this->pedidoModel->getByCliente($userId);
            $titulo = 'Mis Pedidos';
            $esVendedor = false;
        }
        
        include '../app/views/dashboard/pedidos.php';
    }
    
    /**
     * API: Obtener datos para gráficos
     */
    public function apiGraficos() {
        $userId = $this->requireAuth();
        $tipo = $_GET['tipo'] ?? '';
        
        header('Content-Type: application/json');
        
        switch ($tipo) {
            case 'ventas_semana':
                $fechaInicio = date('Y-m-d', strtotime('-7 days'));
                $fechaFin = date('Y-m-d');
                $data = $this->pedidoModel->getVentasPorPeriodo($userId, $fechaInicio, $fechaFin);
                echo json_encode($data);
                break;
                
            default:
                echo json_encode(['error' => 'Tipo de gráfico no válido']);
        }
    }
}
?>