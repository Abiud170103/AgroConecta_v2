<?php
/**
 * AdminController - Controlador de administración
 * Panel de administración para gestión del sistema
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'BaseController.php';
require_once APP_PATH . '/models/Usuario.php';
require_once APP_PATH . '/models/Producto.php';
require_once APP_PATH . '/models/Pedido.php';
require_once APP_PATH . '/models/Resena.php';
require_once APP_PATH . '/models/Notificacion.php';

class AdminController extends BaseController {
    
    /**
     * Verificar que el usuario es administrador
     */
    private function requireAdmin() {
        if (!$this->requireAuth()) return false;
        
        $currentUser = $this->getCurrentUser();
        if ($currentUser['tipo_usuario'] !== 'admin') {
            $this->setFlashMessage('error', 'Acceso denegado. Se requieren permisos de administrador.');
            $this->redirect('/dashboard');
            return false;
        }
        
        return true;
    }
    
    /**
     * Dashboard principal de administración
     */
    public function dashboard() {
        if (!$this->requireAdmin()) return;
        
        try {
            $usuarioModel = new Usuario();
            $productoModel = new Producto();
            $pedidoModel = new Pedido();
            $resenaModel = new Resena();
            
            // Estadísticas generales del sistema
            $estadisticas = [
                'total_usuarios' => $usuarioModel->count(['activo' => 1]),
                'total_vendedores' => $usuarioModel->count(['tipo_usuario' => 'vendedor', 'activo' => 1]),
                'total_productos' => $productoModel->count(['activo' => 1]),
                'total_pedidos' => $pedidoModel->count([]),
                'pedidos_pendientes' => $pedidoModel->count(['estado' => 'pendiente']),
                'ventas_mes' => $pedidoModel->getVentasTotalesMes(),
                'usuarios_nuevos_mes' => $usuarioModel->getNuevosUsuariosMes(),
                'productos_nuevos_mes' => $productoModel->getNuevosProductosMes()
            ];
            
            // Últimas actividades
            $ultimosPedidos = $pedidoModel->getPedidosPaginados([], 5);
            $ultimosUsuarios = $usuarioModel->getUsuariosPaginados(['activo' => 1], 'fecha_registro', 5);
            $ultimosProductos = $productoModel->getProductosPaginados(['activo' => 1], 'fecha_creacion', 5);
            
            // Productos con stock bajo
            $productosStockBajo = $productoModel->getProductosStockBajo(10, 5);
            
            // Pedidos que requieren atención
            $pedidosAtencion = $pedidoModel->getPedidosRequierenAtencion();
            
            // Reseñas recientes
            $resenasRecientes = $resenaModel->getResenasRecientes(5);
            
            $this->setViewData('pageTitle', 'Dashboard de Administración');
            $this->setViewData('estadisticas', $estadisticas);
            $this->setViewData('ultimosPedidos', $ultimosPedidos);
            $this->setViewData('ultimosUsuarios', $ultimosUsuarios);
            $this->setViewData('ultimosProductos', $ultimosProductos);
            $this->setViewData('productosStockBajo', $productosStockBajo);
            $this->setViewData('pedidosAtencion', $pedidosAtencion);
            $this->setViewData('resenasRecientes', $resenasRecientes);
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            
        } catch (Exception $e) {
            error_log("Error in AdminController::dashboard: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar el dashboard');
            $this->setViewData('estadisticas', []);
        }
        
        $this->render('admin/dashboard');
    }
    
    /**
     * Gestión de usuarios
     */
    public function users() {
        if (!$this->requireAdmin()) return;
        
        try {
            $usuarioModel = new Usuario();
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 20;
            $filtro = $this->sanitizeInput($_GET['filtro'] ?? '');
            $tipo = $this->sanitizeInput($_GET['tipo'] ?? '');
            $activo = $_GET['activo'] ?? '';
            
            $filtros = [];
            if ($filtro) {
                $filtros['search'] = $filtro; // Búsqueda por nombre o email
            }
            if ($tipo) {
                $filtros['tipo_usuario'] = $tipo;
            }
            if ($activo !== '') {
                $filtros['activo'] = intval($activo);
            }
            
            $offset = ($page - 1) * $perPage;
            $usuarios = $usuarioModel->getUsuariosPaginados($filtros, 'fecha_registro', $perPage, $offset);
            $totalUsuarios = $usuarioModel->countUsuarios($filtros);
            $totalPaginas = ceil($totalUsuarios / $perPage);
            
            $this->setViewData('pageTitle', 'Gestión de Usuarios');
            $this->setViewData('usuarios', $usuarios);
            $this->setViewData('filtros', [
                'filtro' => $filtro,
                'tipo' => $tipo,
                'activo' => $activo
            ]);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'totalItems' => $totalUsuarios
            ]);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Admin', 'url' => '/admin'],
                ['name' => 'Usuarios', 'url' => '/admin/usuarios']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in AdminController::users: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar usuarios');
            $this->setViewData('usuarios', []);
        }
        
        $this->render('admin/users');
    }
    
    /**
     * Ver detalles de un usuario específico
     */
    public function userDetails($id) {
        if (!$this->requireAdmin()) return;
        
        $id = intval($id);
        
        try {
            $usuarioModel = new Usuario();
            $pedidoModel = new Pedido();
            $productoModel = new Producto();
            
            $usuario = $usuarioModel->find($id);
            if (!$usuario) {
                $this->setFlashMessage('error', 'Usuario no encontrado');
                $this->redirect('/admin/usuarios');
                return;
            }
            
            // Estadísticas del usuario
            $estadisticas = [
                'total_pedidos' => $pedidoModel->count(['id_usuario' => $id]),
                'total_gastado' => $pedidoModel->getTotalGastado($id),
                'pedidos_completados' => $pedidoModel->count(['id_usuario' => $id, 'estado' => 'entregado']),
                'ultimo_pedido' => $pedidoModel->getUltimoPedido($id)
            ];
            
            if ($usuario['tipo_usuario'] === 'vendedor') {
                $estadisticas['productos_activos'] = $productoModel->count(['id_vendedor' => $id, 'activo' => 1]);
                $estadisticas['total_ventas'] = $pedidoModel->getTotalVentas($id);
            }
            
            // Últimos pedidos
            $ultimosPedidos = $pedidoModel->getPedidosPaginados(['id_usuario' => $id], 10);
            
            $this->setViewData('pageTitle', 'Usuario: ' . $usuario['nombre_completo']);
            $this->setViewData('usuario', $usuario);
            $this->setViewData('estadisticas', $estadisticas);
            $this->setViewData('ultimosPedidos', $ultimosPedidos);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Admin', 'url' => '/admin'],
                ['name' => 'Usuarios', 'url' => '/admin/usuarios'],
                ['name' => $usuario['nombre_completo'], 'url' => '/admin/usuarios/' . $id]
            ]);
            
        } catch (Exception $e) {
            error_log("Error in AdminController::userDetails: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar detalles del usuario');
            $this->redirect('/admin/usuarios');
        }
        
        $this->render('admin/user-details');
    }
    
    /**
     * Activar/desactivar usuario
     */
    public function toggleUserStatus() {
        if (!$this->requireAdmin()) {
            $this->jsonError('No autorizado', 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', 405);
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->jsonError('Token CSRF inválido', 403);
            return;
        }
        
        $userId = intval($_POST['user_id'] ?? 0);
        
        if ($userId <= 0) {
            $this->jsonError('ID de usuario no válido');
            return;
        }
        
        try {
            $usuarioModel = new Usuario();
            $notificacionModel = new Notificacion();
            
            $usuario = $usuarioModel->find($userId);
            if (!$usuario) {
                $this->jsonError('Usuario no encontrado');
                return;
            }
            
            // No permitir desactivar otros admins
            if ($usuario['tipo_usuario'] === 'admin' && $userId !== $this->getCurrentUserId()) {
                $this->jsonError('No puedes desactivar otros administradores');
                return;
            }
            
            $nuevoEstado = $usuario['activo'] ? 0 : 1;
            $updated = $usuarioModel->update($userId, ['activo' => $nuevoEstado]);
            
            if ($updated) {
                $accion = $nuevoEstado ? 'activado' : 'desactivado';
                
                // Crear notificación para el usuario
                if ($nuevoEstado === 0) {
                    $notificacionModel->create([
                        'id_usuario' => $userId,
                        'titulo' => 'Cuenta Suspendida',
                        'mensaje' => 'Tu cuenta ha sido suspendida por un administrador. Contacta soporte si crees que es un error.',
                        'tipo' => 'sistema',
                        'fecha_creacion' => date('Y-m-d H:i:s')
                    ]);
                }
                
                $this->logActivity('user_status_toggled', "User {$userId} {$accion}");
                $this->jsonSuccess("Usuario {$accion} correctamente", ['new_status' => $nuevoEstado]);
            } else {
                $this->jsonError('Error al actualizar el estado del usuario');
            }
            
        } catch (Exception $e) {
            error_log("Error toggling user status: " . $e->getMessage());
            $this->jsonError('Error interno del servidor', 500);
        }
    }
    
    /**
     * Gestión de productos
     */
    public function products() {
        if (!$this->requireAdmin()) return;
        
        try {
            $productoModel = new Producto();
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 20;
            $filtro = $this->sanitizeInput($_GET['filtro'] ?? '');
            $categoria = $this->sanitizeInput($_GET['categoria'] ?? '');
            $estado = $_GET['estado'] ?? '';
            
            $filtros = [];
            if ($filtro) {
                $filtros['search'] = $filtro;
            }
            if ($categoria) {
                $filtros['categoria'] = $categoria;
            }
            if ($estado !== '') {
                $filtros['activo'] = intval($estado);
            }
            
            $offset = ($page - 1) * $perPage;
            $productos = $productoModel->getProductosPaginados($filtros, 'fecha_creacion', $perPage, $offset);
            $totalProductos = $productoModel->countProductos($filtros);
            $totalPaginas = ceil($totalProductos / $perPage);
            
            // Obtener categorías
            $categorias = $productoModel->getCategorias();
            
            $this->setViewData('pageTitle', 'Gestión de Productos');
            $this->setViewData('productos', $productos);
            $this->setViewData('categorias', $categorias);
            $this->setViewData('filtros', [
                'filtro' => $filtro,
                'categoria' => $categoria,
                'estado' => $estado
            ]);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'totalItems' => $totalProductos
            ]);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Admin', 'url' => '/admin'],
                ['name' => 'Productos', 'url' => '/admin/productos']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in AdminController::products: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar productos');
            $this->setViewData('productos', []);
        }
        
        $this->render('admin/products');
    }
    
    /**
     * Gestión de pedidos
     */
    public function orders() {
        if (!$this->requireAdmin()) return;
        
        try {
            $pedidoModel = new Pedido();
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 20;
            $estado = $this->sanitizeInput($_GET['estado'] ?? '');
            $fechaDesde = $this->sanitizeInput($_GET['fecha_desde'] ?? '');
            $fechaHasta = $this->sanitizeInput($_GET['fecha_hasta'] ?? '');
            
            $filtros = [];
            if ($estado) {
                $filtros['estado'] = $estado;
            }
            if ($fechaDesde) {
                $filtros['fecha_desde'] = $fechaDesde;
            }
            if ($fechaHasta) {
                $filtros['fecha_hasta'] = $fechaHasta;
            }
            
            $offset = ($page - 1) * $perPage;
            $pedidos = $pedidoModel->getPedidosConDetalles($filtros, $perPage, $offset);
            $totalPedidos = $pedidoModel->countPedidos($filtros);
            $totalPaginas = ceil($totalPedidos / $perPage);
            
            // Estadísticas de pedidos
            $estadisticas = [
                'total_pedidos' => $pedidoModel->count([]),
                'pedidos_pendientes' => $pedidoModel->count(['estado' => 'pendiente']),
                'pedidos_completados' => $pedidoModel->count(['estado' => 'entregado']),
                'ventas_totales' => $pedidoModel->getVentasTotales()
            ];
            
            $this->setViewData('pageTitle', 'Gestión de Pedidos');
            $this->setViewData('pedidos', $pedidos);
            $this->setViewData('estadisticas', $estadisticas);
            $this->setViewData('filtros', [
                'estado' => $estado,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta
            ]);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'totalItems' => $totalPedidos
            ]);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Admin', 'url' => '/admin'],
                ['name' => 'Pedidos', 'url' => '/admin/pedidos']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in AdminController::orders: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar pedidos');
            $this->setViewData('pedidos', []);
        }
        
        $this->render('admin/orders');
    }
    
    /**
     * Actualizar estado de pedido
     */
    public function updateOrderStatus() {
        if (!$this->requireAdmin()) {
            $this->jsonError('No autorizado', 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', 405);
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->jsonError('Token CSRF inválido', 403);
            return;
        }
        
        $data = $this->sanitizeInput([
            'pedido_id' => intval($_POST['pedido_id'] ?? 0),
            'nuevo_estado' => $_POST['nuevo_estado'] ?? '',
            'comentario' => $_POST['comentario'] ?? ''
        ]);
        
        if ($data['pedido_id'] <= 0) {
            $this->jsonError('ID de pedido no válido');
            return;
        }
        
        $estadosPermitidos = ['pendiente', 'confirmado', 'preparando', 'enviado', 'entregado', 'cancelado'];
        if (!in_array($data['nuevo_estado'], $estadosPermitidos)) {
            $this->jsonError('Estado no válido');
            return;
        }
        
        try {
            $pedidoModel = new Pedido();
            $notificacionModel = new Notificacion();
            
            $pedido = $pedidoModel->find($data['pedido_id']);
            if (!$pedido) {
                $this->jsonError('Pedido no encontrado');
                return;
            }
            
            // Actualizar estado
            $updateData = [
                'estado' => $data['nuevo_estado'],
                'comentario_admin' => $data['comentario'],
                'fecha_actualizacion' => date('Y-m-d H:i:s')
            ];
            
            if ($data['nuevo_estado'] === 'entregado') {
                $updateData['fecha_entrega'] = date('Y-m-d H:i:s');
            }
            
            $updated = $pedidoModel->update($data['pedido_id'], $updateData);
            
            if ($updated) {
                // Crear notificación para el usuario
                $mensajesEstado = [
                    'confirmado' => 'Tu pedido ha sido confirmado y está en proceso.',
                    'preparando' => 'Tu pedido se está preparando para el envío.',
                    'enviado' => 'Tu pedido ha sido enviado.',
                    'entregado' => 'Tu pedido ha sido entregado exitosamente.',
                    'cancelado' => 'Tu pedido ha sido cancelado.'
                ];
                
                if (isset($mensajesEstado[$data['nuevo_estado']])) {
                    $notificacionModel->create([
                        'id_usuario' => $pedido['id_usuario'],
                        'titulo' => 'Estado del Pedido Actualizado',
                        'mensaje' => $mensajesEstado[$data['nuevo_estado']],
                        'tipo' => 'pedido',
                        'referencia_id' => $data['pedido_id'],
                        'fecha_creacion' => date('Y-m-d H:i:s')
                    ]);
                }
                
                $this->logActivity('order_status_updated', "Order {$data['pedido_id']} updated to {$data['nuevo_estado']}");
                $this->jsonSuccess('Estado del pedido actualizado correctamente');
            } else {
                $this->jsonError('Error al actualizar el estado del pedido');
            }
            
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            $this->jsonError('Error interno del servidor', 500);
        }
    }
    
    /**
     * Reportes y estadísticas
     */
    public function reports() {
        if (!$this->requireAdmin()) return;
        
        try {
            $pedidoModel = new Pedido();
            $usuarioModel = new Usuario();
            $productoModel = new Producto();
            
            // Parámetros de fecha
            $fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01'); // Primer día del mes
            $fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
            
            // Reportes de ventas
            $ventasPorDia = $pedidoModel->getVentasPorPeriodo($fechaDesde, $fechaHasta, 'day');
            $ventasPorMes = $pedidoModel->getVentasPorPeriodo($fechaDesde, $fechaHasta, 'month');
            
            // Productos más vendidos
            $productosMasVendidos = $pedidoModel->getProductosMasVendidos($fechaDesde, $fechaHasta, 10);
            
            // Usuarios más activos
            $usuariosMasActivos = $pedidoModel->getUsuariosMasActivos($fechaDesde, $fechaHasta, 10);
            
            // Estadísticas generales
            $estadisticasGenerales = [
                'total_ventas' => $pedidoModel->getTotalVentasPeriodo($fechaDesde, $fechaHasta),
                'total_pedidos' => $pedidoModel->getTotalPedidosPeriodo($fechaDesde, $fechaHasta),
                'promedio_pedido' => $pedidoModel->getPromedioVentaPorPedido($fechaDesde, $fechaHasta),
                'nuevos_usuarios' => $usuarioModel->getNuevosUsuariosPeriodo($fechaDesde, $fechaHasta)
            ];
            
            $this->setViewData('pageTitle', 'Reportes y Estadísticas');
            $this->setViewData('fechaDesde', $fechaDesde);
            $this->setViewData('fechaHasta', $fechaHasta);
            $this->setViewData('ventasPorDia', $ventasPorDia);
            $this->setViewData('ventasPorMes', $ventasPorMes);
            $this->setViewData('productosMasVendidos', $productosMasVendidos);
            $this->setViewData('usuariosMasActivos', $usuariosMasActivos);
            $this->setViewData('estadisticasGenerales', $estadisticasGenerales);
            $this->setViewData('breadcrumb', [
                ['name' => 'Admin', 'url' => '/admin'],
                ['name' => 'Reportes', 'url' => '/admin/reportes']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in AdminController::reports: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar reportes');
            $this->setViewData('estadisticasGenerales', []);
        }
        
        $this->render('admin/reports');
    }
    
    /**
     * Configuración del sistema
     */
    public function settings() {
        if (!$this->requireAdmin()) return;
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // TODO: Cargar configuraciones desde BD o archivo
            $configuraciones = [
                'sitio_nombre' => 'AgroConecta',
                'sitio_email' => 'admin@agroconecta.com',
                'comision_vendedor' => 5.0,
                'costo_envio_minimo' => 50.0,
                'envio_gratis_desde' => 500.0,
                'mantenimiento_activo' => false
            ];
            
            $this->setViewData('pageTitle', 'Configuración del Sistema');
            $this->setViewData('configuraciones', $configuraciones);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Admin', 'url' => '/admin'],
                ['name' => 'Configuración', 'url' => '/admin/configuracion']
            ]);
            
            $this->render('admin/settings');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        // TODO: Guardar configuraciones en BD
        $this->logActivity('system_settings_updated', 'System settings updated by admin');
        $this->setFlashMessage('success', 'Configuración actualizada correctamente');
        $this->redirect('/admin/configuracion');
    }
}
?>