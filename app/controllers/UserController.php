<?php
/**
 * UserController - Controlador de usuario
 * Maneja el perfil, dashboard y configuraciones del usuario
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'BaseController.php';
require_once APP_PATH . '/models/Usuario.php';
require_once APP_PATH . '/models/Pedido.php';
require_once APP_PATH . '/models/Producto.php';
require_once APP_PATH . '/models/Resena.php';
require_once APP_PATH . '/models/Notificacion.php';

class UserController extends BaseController {
    
    /**
     * Dashboard principal del usuario
     */
    public function dashboard() {
        if (!$this->requireAuth()) return;
        
        try {
            $userId = $this->getCurrentUserId();
            $currentUser = $this->getCurrentUser();
            
            $pedidoModel = new Pedido();
            $notificacionModel = new Notificacion();
            
            // Estadísticas del usuario
            $estadisticas = [
                'total_pedidos' => $pedidoModel->count(['id_usuario' => $userId]),
                'pedidos_pendientes' => $pedidoModel->count(['id_usuario' => $userId, 'estado' => 'pendiente']),
                'pedidos_completados' => $pedidoModel->count(['id_usuario' => $userId, 'estado' => 'entregado']),
                'gasto_total' => $pedidoModel->getTotalGastado($userId)
            ];
            
            // Últimos pedidos
            $ultimosPedidos = $pedidoModel->getPedidosPaginados(['id_usuario' => $userId], 5);
            
            // Notificaciones recientes no leídas
            $notificaciones = $notificacionModel->getNotificacionesUsuario($userId, false, 10);
            
            // Si es vendedor, agregar estadísticas adicionales
            if ($currentUser['tipo_usuario'] === 'vendedor') {
                $productoModel = new Producto();
                $estadisticas['productos_activos'] = $productoModel->count(['id_vendedor' => $userId, 'activo' => 1]);
                $estadisticas['productos_total'] = $productoModel->count(['id_vendedor' => $userId]);
                $estadisticas['ventas_mes'] = $pedidoModel->getVentasMes($userId);
            }
            
            $this->setViewData('pageTitle', 'Mi Dashboard');
            $this->setViewData('usuario', $currentUser);
            $this->setViewData('estadisticas', $estadisticas);
            $this->setViewData('ultimosPedidos', $ultimosPedidos);
            $this->setViewData('notificaciones', $notificaciones);
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            
        } catch (Exception $e) {
            error_log("Error in UserController::dashboard: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar el dashboard');
            $this->setViewData('estadisticas', []);
            $this->setViewData('ultimosPedidos', []);
            $this->setViewData('notificaciones', []);
        }
        
        $this->render('user/dashboard');
    }
    
    /**
     * Ver y editar perfil de usuario
     */
    public function profile() {
        if (!$this->requireAuth()) return;
        
        $currentUser = $this->getCurrentUser();
        
        $this->setViewData('pageTitle', 'Mi Perfil');
        $this->setViewData('usuario', $currentUser);
        $this->setViewData('csrf_token', $this->generateCSRF());
        $this->setViewData('errors', $_SESSION['profile_errors'] ?? []);
        $this->setViewData('oldData', $_SESSION['profile_data'] ?? []);
        $this->setViewData('success', $this->getFlashMessage('success'));
        $this->setViewData('error', $this->getFlashMessage('error'));
        $this->setViewData('breadcrumb', [
            ['name' => 'Inicio', 'url' => '/'],
            ['name' => 'Dashboard', 'url' => '/dashboard'],
            ['name' => 'Mi Perfil', 'url' => '/usuario/perfil']
        ]);
        
        // Limpiar errores de sesión
        unset($_SESSION['profile_errors'], $_SESSION['profile_data']);
        
        $this->render('user/profile');
    }
    
    /**
     * Actualizar perfil de usuario
     */
    public function updateProfile() {
        if (!$this->requireAuth()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/usuario/perfil');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $data = $this->sanitizeInput([
            'nombre_completo' => $_POST['nombre_completo'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'ciudad' => $_POST['ciudad'] ?? '',
            'codigo_postal' => $_POST['codigo_postal'] ?? '',
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
            'bio' => $_POST['bio'] ?? ''
        ]);
        
        // Validaciones
        $errors = [];
        if (empty($data['nombre_completo'])) {
            $errors['nombre_completo'] = 'El nombre completo es requerido';
        }
        
        if (!empty($data['telefono']) && !preg_match('/^[0-9]{10,15}$/', $data['telefono'])) {
            $errors['telefono'] = 'Teléfono no válido (10-15 dígitos)';
        }
        
        if (!empty($data['fecha_nacimiento'])) {
            $fechaNac = DateTime::createFromFormat('Y-m-d', $data['fecha_nacimiento']);
            if (!$fechaNac || $fechaNac > new DateTime()) {
                $errors['fecha_nacimiento'] = 'Fecha de nacimiento no válida';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['profile_errors'] = $errors;
            $_SESSION['profile_data'] = $data;
            $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            $this->redirect('/usuario/perfil');
            return;
        }
        
        try {
            $usuarioModel = new Usuario();
            $userId = $this->getCurrentUserId();
            
            // Actualizar datos del usuario
            $updated = $usuarioModel->update($userId, $data);
            
            if ($updated) {
                $this->logActivity('profile_updated', 'User profile updated');
                $this->setFlashMessage('success', 'Perfil actualizado correctamente');
            } else {
                $this->setFlashMessage('error', 'No se realizaron cambios');
            }
            
        } catch (Exception $e) {
            error_log("Error updating profile: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al actualizar el perfil');
        }
        
        $this->redirect('/usuario/perfil');
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword() {
        if (!$this->requireAuth()) return;
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->setViewData('pageTitle', 'Cambiar Contraseña');
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('errors', $_SESSION['password_errors'] ?? []);
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Cambiar Contraseña', 'url' => '/usuario/cambiar-password']
            ]);
            
            unset($_SESSION['password_errors']);
            $this->render('user/change-password');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $data = $this->sanitizeInput([
            'current_password' => $_POST['current_password'] ?? '',
            'new_password' => $_POST['new_password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ]);
        
        // Validaciones
        $errors = [];
        if (empty($data['current_password'])) {
            $errors['current_password'] = 'La contraseña actual es requerida';
        }
        
        if (empty($data['new_password'])) {
            $errors['new_password'] = 'La nueva contraseña es requerida';
        } elseif (strlen($data['new_password']) < 8) {
            $errors['new_password'] = 'La nueva contraseña debe tener al menos 8 caracteres';
        }
        
        if ($data['new_password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Las contraseñas no coinciden';
        }
        
        if (!empty($errors)) {
            $_SESSION['password_errors'] = $errors;
            $this->setFlashMessage('error', 'Por favor corrige los errores');
            $this->redirect('/usuario/cambiar-password');
            return;
        }
        
        try {
            $usuarioModel = new Usuario();
            $userId = $this->getCurrentUserId();
            $currentUser = $usuarioModel->find($userId);
            
            // Verificar contraseña actual
            if (!password_verify($data['current_password'], $currentUser['password'])) {
                $errors['current_password'] = 'Contraseña actual incorrecta';
                $_SESSION['password_errors'] = $errors;
                $this->setFlashMessage('error', 'Contraseña actual incorrecta');
                $this->redirect('/usuario/cambiar-password');
                return;
            }
            
            // Actualizar contraseña
            $newPasswordHash = password_hash($data['new_password'], PASSWORD_DEFAULT);
            $updated = $usuarioModel->update($userId, [
                'password' => $newPasswordHash,
                'fecha_actualizacion' => date('Y-m-d H:i:s')
            ]);
            
            if ($updated) {
                $this->logActivity('password_changed', 'User changed password');
                $this->setFlashMessage('success', 'Contraseña cambiada correctamente');
            } else {
                $this->setFlashMessage('error', 'Error al cambiar la contraseña');
            }
            
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error interno del servidor');
        }
        
        $this->redirect('/usuario/cambiar-password');
    }
    
    /**
     * Configuración de notificaciones
     */
    public function notifications() {
        if (!$this->requireAuth()) return;
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $currentUser = $this->getCurrentUser();
            
            $this->setViewData('pageTitle', 'Configurar Notificaciones');
            $this->setViewData('usuario', $currentUser);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Notificaciones', 'url' => '/usuario/notificaciones']
            ]);
            
            $this->render('user/notifications');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $preferences = [
            'email_pedidos' => isset($_POST['email_pedidos']) ? 1 : 0,
            'email_promociones' => isset($_POST['email_promociones']) ? 1 : 0,
            'email_nuevos_productos' => isset($_POST['email_nuevos_productos']) ? 1 : 0,
            'sms_pedidos' => isset($_POST['sms_pedidos']) ? 1 : 0
        ];
        
        try {
            $usuarioModel = new Usuario();
            $userId = $this->getCurrentUserId();
            
            // Guardar preferencias (se podrían tener en una tabla separada)
            $updated = $usuarioModel->update($userId, [
                'preferencias_notificacion' => json_encode($preferences),
                'fecha_actualizacion' => date('Y-m-d H:i:s')
            ]);
            
            if ($updated) {
                $this->logActivity('notification_preferences_updated', 'Notification preferences updated');
                $this->setFlashMessage('success', 'Preferencias de notificación actualizadas');
            } else {
                $this->setFlashMessage('error', 'No se realizaron cambios');
            }
            
        } catch (Exception $e) {
            error_log("Error updating notification preferences: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al actualizar las preferencias');
        }
        
        $this->redirect('/usuario/notificaciones');
    }
    
    /**
     * Lista de notificaciones del usuario
     */
    public function notificationList() {
        if (!$this->requireAuth()) return;
        
        try {
            $notificacionModel = new Notificacion();
            $userId = $this->getCurrentUserId();
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 20;
            $tipo = $this->sanitizeInput($_GET['tipo'] ?? '');
            
            $filtros = ['id_usuario' => $userId];
            if ($tipo) $filtros['tipo'] = $tipo;
            
            $offset = ($page - 1) * $perPage;
            $notificaciones = $notificacionModel->getNotificacionesPaginadas($filtros, $perPage, $offset);
            $totalNotificaciones = $notificacionModel->countNotificaciones($filtros);
            $totalPaginas = ceil($totalNotificaciones / $perPage);
            
            // Marcar como leídas las notificaciones mostradas
            $notificationIds = array_column($notificaciones, 'id_notificacion');
            if (!empty($notificationIds)) {
                $notificacionModel->marcarComoLeidas($notificationIds);
            }
            
            $tipos = ['pedido', 'producto', 'promocion', 'sistema'];
            
            $this->setViewData('pageTitle', 'Mis Notificaciones');
            $this->setViewData('notificaciones', $notificaciones);
            $this->setViewData('tipos', $tipos);
            $this->setViewData('tipoActual', $tipo);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'totalItems' => $totalNotificaciones
            ]);
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Notificaciones', 'url' => '/usuario/notificaciones/lista']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in notification list: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar las notificaciones');
            $this->setViewData('notificaciones', []);
        }
        
        $this->render('user/notification-list');
    }
    
    /**
     * Marcar notificación como leída (AJAX)
     */
    public function markNotificationRead() {
        if (!$this->requireAuth()) {
            $this->jsonError('No autorizado', 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', 405);
            return;
        }
        
        $notificationId = intval($_POST['id'] ?? 0);
        
        if ($notificationId <= 0) {
            $this->jsonError('ID de notificación no válido');
            return;
        }
        
        try {
            $notificacionModel = new Notificacion();
            $userId = $this->getCurrentUserId();
            
            // Verificar que la notificación pertenece al usuario
            $notification = $notificacionModel->find($notificationId);
            if (!$notification || $notification['id_usuario'] != $userId) {
                $this->jsonError('Notificación no encontrada');
                return;
            }
            
            $updated = $notificacionModel->marcarComoLeida($notificationId);
            
            if ($updated) {
                $this->jsonSuccess('Notificación marcada como leída');
            } else {
                $this->jsonError('Error al actualizar la notificación');
            }
            
        } catch (Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            $this->jsonError('Error interno del servidor', 500);
        }
    }
    
    /**
     * Mis reseñas
     */
    public function reviews() {
        if (!$this->requireAuth()) return;
        
        try {
            $resenaModel = new Resena();
            $userId = $this->getCurrentUserId();
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 10;
            
            $offset = ($page - 1) * $perPage;
            $resenas = $resenaModel->getResenasByUsuario($userId, $perPage, $offset);
            $totalResenas = $resenaModel->countResenasByUsuario($userId);
            $totalPaginas = ceil($totalResenas / $perPage);
            
            $this->setViewData('pageTitle', 'Mis Reseñas');
            $this->setViewData('resenas', $resenas);
            $this->setViewData('pagination', [
                'current' => $page,
                'total' => $totalPaginas,
                'totalItems' => $totalResenas
            ]);
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('success', $this->getFlashMessage('success'));
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Mis Reseñas', 'url' => '/usuario/resenas']
            ]);
            
        } catch (Exception $e) {
            error_log("Error in user reviews: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al cargar las reseñas');
            $this->setViewData('resenas', []);
        }
        
        $this->render('user/reviews');
    }
    
    /**
     * Eliminar cuenta de usuario
     */
    public function deleteAccount() {
        if (!$this->requireAuth()) return;
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->setViewData('pageTitle', 'Eliminar Cuenta');
            $this->setViewData('csrf_token', $this->generateCSRF());
            $this->setViewData('error', $this->getFlashMessage('error'));
            $this->setViewData('breadcrumb', [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Dashboard', 'url' => '/dashboard'],
                ['name' => 'Eliminar Cuenta', 'url' => '/usuario/eliminar-cuenta']
            ]);
            
            $this->render('user/delete-account');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $password = $_POST['password'] ?? '';
        $confirmation = $_POST['confirmation'] ?? '';
        
        if ($confirmation !== 'ELIMINAR') {
            $this->setFlashMessage('error', 'Debes escribir ELIMINAR para confirmar');
            $this->redirect('/usuario/eliminar-cuenta');
            return;
        }
        
        try {
            $usuarioModel = new Usuario();
            $userId = $this->getCurrentUserId();
            $currentUser = $usuarioModel->find($userId);
            
            // Verificar contraseña
            if (!password_verify($password, $currentUser['password'])) {
                $this->setFlashMessage('error', 'Contraseña incorrecta');
                $this->redirect('/usuario/eliminar-cuenta');
                return;
            }
            
            // Verificar que no hay pedidos pendientes
            $pedidoModel = new Pedido();
            $pedidosPendientes = $pedidoModel->count([
                'id_usuario' => $userId,
                'estado' => ['pendiente', 'confirmado', 'preparando', 'enviado']
            ]);
            
            if ($pedidosPendientes > 0) {
                $this->setFlashMessage('error', 'No puedes eliminar tu cuenta mientras tengas pedidos activos');
                $this->redirect('/usuario/eliminar-cuenta');
                return;
            }
            
            // Desactivar cuenta en lugar de eliminar para preservar integridad referencial
            $usuarioModel->update($userId, [
                'activo' => 0,
                'email' => $currentUser['email'] . '_deleted_' . time(),
                'fecha_eliminacion' => date('Y-m-d H:i:s')
            ]);
            
            $this->logActivity('account_deleted', 'User account deleted');
            
            // Cerrar sesión
            $this->destroySession();
            
            $this->setFlashMessage('success', 'Tu cuenta ha sido eliminada correctamente');
            $this->redirect('/');
            
        } catch (Exception $e) {
            error_log("Error deleting account: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al eliminar la cuenta');
            $this->redirect('/usuario/eliminar-cuenta');
        }
    }
}
?>