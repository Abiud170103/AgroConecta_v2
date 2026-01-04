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
        
        // Obtener datos del usuario desde SessionManager
        $userData = SessionManager::getUserData();
        if (!$userData || !isset($userData['id'])) {
            SessionManager::setFlash('error', 'Error en los datos de sesión');
            header('Location: login.php');
            exit;
        }
        
        return $userData['id'];  // Retornar el ID del usuario
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
        switch ($user['tipo']) {
            case 'vendedor':
                return $this->dashboardVendedor();
                break;
            case 'cliente':
                return $this->dashboardCliente();
                break;
            case 'admin':
                return $this->dashboardAdmin();
                break;
            default:
                return $this->dashboardGeneral();
        }
    }
    
    /**
     * Dashboard específico para vendedores
     */
    public function dashboardVendedor() {
        $userId = $this->requireAuth();
        
        // Datos básicos para evitar errores en consultas complejas
        $statsProductos = [
            'total_productos' => 0,
            'productos_disponibles' => 0,
            'productos_agotados' => 0,
            'precio_promedio' => 0,
            'precio_max' => 0,
            'precio_min' => 0
        ];
        
        $statsPedidos = [
            'total_pedidos' => 0,
            'ventas_completadas' => 0,
            'pedidos_pendientes' => 0,
            'ingresos_totales' => 0,
            'venta_promedio' => 0
        ];
        
        // Datos vacíos por ahora para evitar errores
        $productosRecientes = [];
        $pedidosRecientes = [];
        $pedidosPendientes = [];
        $ventasPorDia = [];
        
        // Obtener datos del usuario desde SessionManager
        $user = SessionManager::getUserData();
        
        return [
            'user' => $user,
            'statsProductos' => $statsProductos,
            'statsPedidos' => $statsPedidos,
            'ventasPorDia' => $ventasPorDia,
            'pedidosPendientes' => $pedidosPendientes,
            'productosRecientes' => $productosRecientes,
            'pedidosRecientes' => $pedidosRecientes
        ];
    }
    
    /**
     * Dashboard específico para clientes
     */
    public function dashboardCliente() {
        $userId = $this->requireAuth();
        
        // Datos básicos para evitar errores en consultas complejas
        $statsPedidos = [
            'total_pedidos' => 0,
            'pedidos_completados' => 0,
            'pedidos_activos' => 0,
            'dinero_gastado' => 0,
            'ticket_promedio' => 0
        ];
        
        $statsFavoritos = [
            'productos_favoritos' => 0
        ];
        
        // Datos vacíos por ahora
        $itemsCarrito = 0;
        $categoriasPopulares = [];
        $productosDestacados = [];
        $pedidosRecientes = [];
        $recomendaciones = [];
        
        // Obtener datos del usuario desde SessionManager
        $user = SessionManager::getUserData();
        
        return [
            'user' => $user,
            'statsPedidos' => $statsPedidos,
            'statsFavoritos' => $statsFavoritos,
            'itemsCarrito' => $itemsCarrito,
            'categoriasPopulares' => $categoriasPopulares,
            'productosDestacados' => $productosDestacados,
            'pedidosRecientes' => $pedidosRecientes,
            'recomendaciones' => $recomendaciones
        ];
    }
    
    /**
     * Dashboard para administradores
     */
    public function dashboardAdmin() {
        $userId = $this->requireAuth();
        
        // Obtener datos del usuario desde SessionManager
        $user = SessionManager::getUserData();
        
        // Verificar que sea admin
        if ($user['tipo'] !== 'admin') {
            SessionManager::setFlash('error', 'Acceso denegado');
            header('Location: login.php');
            exit;
        }
        
        // Estadísticas básicas para admin
        $statsGenerales = [
            'total_usuarios' => 5,
            'tendencia_usuarios' => 25,
            'total_productos' => 0,
            'tendencia_productos' => 15,
            'total_pedidos' => 0,
            'tendencia_pedidos' => 10,
            'ingresos_totales' => 0,
            'tendencia_ingresos' => 20
        ];
        
        // Datos vacíos por ahora
        $alertasImportantes = [];
        $actividadReciente = [];
        $datosGraficoCrecimiento = [];
        $statsUsuarios = ['vendedores' => 2, 'clientes' => 2, 'admins' => 1];
        $statsProductos = ['activos' => 0, 'pendientes' => 0, 'agotados' => 0];
        $statsPedidos = ['pendientes' => 0, 'confirmados' => 0, 'enviados' => 0, 'completados' => 0];
        $categoriasPopulares = [];
        $usuariosRecientes = [];
        $pedidosRecientes = [];
        
        return [
            'user' => $user,
            'statsGenerales' => $statsGenerales,
            'alertasImportantes' => $alertasImportantes,
            'actividadReciente' => $actividadReciente,
            'datosGraficoCrecimiento' => $datosGraficoCrecimiento,
            'statsUsuarios' => $statsUsuarios,
            'statsProductos' => $statsProductos,
            'statsPedidos' => $statsPedidos,
            'categoriasPopulares' => $categoriasPopulares,
            'usuariosRecientes' => $usuariosRecientes,
            'pedidosRecientes' => $pedidosRecientes
        ];
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