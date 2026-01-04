<?php
/**
 * Controlador de Perfiles de Usuario - AgroConecta
 * Gestiona perfiles completos, configuraciones y datos personales
 */

require_once '../core/SessionManager.php';
require_once '../app/models/Usuario.php';

class ProfileController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new Usuario();
        SessionManager::startSecureSession();
    }
    
    /**
     * Verifica que el usuario esté autenticado
     */
    private function requireAuth() {
        if (!SessionManager::isLoggedIn()) {
            SessionManager::setFlash('error', 'Debes iniciar sesión para acceder a esta página');
            header('Location: login.php');
            exit;
        }
        return $_SESSION['user_id'];
    }
    
    /**
     * Muestra el perfil completo del usuario
     */
    public function showProfile() {
        $userId = $this->requireAuth();
        
        // Obtener datos del usuario
        $user = $this->userModel->find($userId);
        if (!$user) {
            SessionManager::setFlash('error', 'Usuario no encontrado');
            header('Location: login.php');
            exit;
        }
        
        // Obtener estadísticas adicionales si es vendedor
        $stats = [];
        if ($user['tipo_usuario'] === 'vendedor') {
            $stats = $this->getVendedorStats($userId);
        } else {
            $stats = $this->getClienteStats($userId);
        }
        
        include '../app/views/user/profile-complete.php';
    }
    
    /**
     * Muestra formulario de edición de perfil
     */
    public function editProfile() {
        $userId = $this->requireAuth();
        
        $user = $this->userModel->find($userId);
        if (!$user) {
            SessionManager::setFlash('error', 'Usuario no encontrado');
            header('Location: login.php');
            exit;
        }
        
        // Estados de México para el formulario
        $estados = $this->getEstadosMexico();
        
        include '../app/views/user/edit-profile.php';
    }
    
    /**
     * Procesa actualización de perfil
     */
    public function updateProfile() {
        $userId = $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: profile.php');
            exit;
        }
        
        // Validar CSRF
        if (!SessionManager::validateCSRF($_POST['csrf_token'] ?? '')) {
            SessionManager::setFlash('error', 'Token de seguridad inválido');
            header('Location: edit-profile.php');
            exit;
        }
        
        try {
            // Obtener y validar datos
            $data = $this->validateProfileData($_POST);
            
            // Verificar si el email cambió y si ya existe
            $currentUser = $this->userModel->find($userId);
            if ($data['correo'] !== $currentUser['correo']) {
                if ($this->userModel->emailExists($data['correo'], $userId)) {
                    throw new Exception('Este email ya está en uso');
                }
                // Si cambia email, requerir verificación nuevamente
                $data['verificado'] = 0;
                $data['token_verificacion'] = null;
            }
            
            // Actualizar usuario
            if ($this->userModel->update($userId, $data)) {
                // Actualizar datos de sesión si es necesario
                $_SESSION['user_email'] = $data['correo'];
                $_SESSION['user_name'] = $data['nombre'] . ' ' . $data['apellido'];
                
                SessionManager::setFlash('success', '¡Perfil actualizado correctamente!');
                
                // Si cambió email, mostrar mensaje especial
                if ($data['correo'] !== $currentUser['correo']) {
                    SessionManager::setFlash('info', 'Tu email cambió. Necesitas verificar tu nueva dirección.');
                    // Generar nuevo token de verificación
                    $this->userModel->generateVerificationToken($userId);
                }
            } else {
                throw new Exception('Error al actualizar el perfil');
            }
            
        } catch (Exception $e) {
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: edit-profile.php');
            exit;
        }
        
        header('Location: profile.php');
        exit;
    }
    
    /**
     * Procesa cambio de contraseña
     */
    public function changePassword() {
        $userId = $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: profile.php');
            exit;
        }
        
        // Validar CSRF
        if (!SessionManager::validateCSRF($_POST['csrf_token'] ?? '')) {
            SessionManager::setFlash('error', 'Token de seguridad inválido');
            header('Location: profile.php');
            exit;
        }
        
        try {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validaciones
            if (empty($currentPassword)) {
                throw new Exception('Debes ingresar tu contraseña actual');
            }
            
            if (strlen($newPassword) < 6) {
                throw new Exception('La nueva contraseña debe tener al menos 6 caracteres');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('Las contraseñas no coinciden');
            }
            
            // Verificar contraseña actual
            $user = $this->userModel->find($userId);
            if (!password_verify($currentPassword, $user['contraseña'])) {
                throw new Exception('La contraseña actual es incorrecta');
            }
            
            // Actualizar contraseña
            if ($this->userModel->updatePassword($userId, $newPassword)) {
                SessionManager::setFlash('success', '¡Contraseña actualizada correctamente!');
                error_log("Password changed successfully for user ID: {$userId}");
            } else {
                throw new Exception('Error al actualizar la contraseña');
            }
            
        } catch (Exception $e) {
            SessionManager::setFlash('error', $e->getMessage());
        }
        
        header('Location: profile.php');
        exit;
    }
    
    /**
     * Gestiona configuraciones de cuenta
     */
    public function accountSettings() {
        $userId = $this->requireAuth();
        
        $user = $this->userModel->find($userId);
        if (!$user) {
            SessionManager::setFlash('error', 'Usuario no encontrado');
            header('Location: login.php');
            exit;
        }
        
        include '../app/views/user/account-settings.php';
    }
    
    /**
     * Procesa eliminación de cuenta
     */
    public function deleteAccount() {
        $userId = $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: account-settings.php');
            exit;
        }
        
        // Validar CSRF
        if (!SessionManager::validateCSRF($_POST['csrf_token'] ?? '')) {
            SessionManager::setFlash('error', 'Token de seguridad inválido');
            header('Location: account-settings.php');
            exit;
        }
        
        try {
            $password = $_POST['confirm_password'] ?? '';
            
            if (empty($password)) {
                throw new Exception('Debes confirmar tu contraseña');
            }
            
            // Verificar contraseña
            $user = $this->userModel->find($userId);
            if (!password_verify($password, $user['contraseña'])) {
                throw new Exception('Contraseña incorrecta');
            }
            
            // Desactivar cuenta en lugar de eliminar (soft delete)
            $data = [
                'activo' => 0,
                'correo' => $user['correo'] . '_deleted_' . time(),
                'token_reset' => null,
                'token_verificacion' => null
            ];
            
            if ($this->userModel->update($userId, $data)) {
                // Cerrar sesión
                SessionManager::destroy();
                SessionManager::setFlash('success', 'Tu cuenta ha sido desactivada correctamente');
                header('Location: login.php');
                exit;
            } else {
                throw new Exception('Error al desactivar la cuenta');
            }
            
        } catch (Exception $e) {
            SessionManager::setFlash('error', $e->getMessage());
        }
        
        header('Location: account-settings.php');
        exit;
    }
    
    // MÉTODOS PRIVADOS
    
    /**
     * Valida datos del perfil
     */
    private function validateProfileData($data) {
        $validated = [];
        
        // Nombre
        $validated['nombre'] = trim($data['nombre'] ?? '');
        if (empty($validated['nombre']) || strlen($validated['nombre']) < 2) {
            throw new Exception('El nombre debe tener al menos 2 caracteres');
        }
        
        // Apellido
        $validated['apellido'] = trim($data['apellido'] ?? '');
        if (empty($validated['apellido']) || strlen($validated['apellido']) < 2) {
            throw new Exception('El apellido debe tener al menos 2 caracteres');
        }
        
        // Email
        $validated['correo'] = strtolower(trim($data['correo'] ?? ''));
        if (!filter_var($validated['correo'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }
        
        // Teléfono (opcional)
        $validated['telefono'] = trim($data['telefono'] ?? '');
        if (!empty($validated['telefono'])) {
            // Validación básica de teléfono mexicano
            if (!preg_match('/^[\d\s\-\+\(\)]{10,15}$/', $validated['telefono'])) {
                throw new Exception('Formato de teléfono inválido');
            }
        }
        
        // Campos adicionales opcionales
        $validated['direccion'] = trim($data['direccion'] ?? '');
        $validated['ciudad'] = trim($data['ciudad'] ?? '');
        $validated['estado'] = trim($data['estado'] ?? '');
        $validated['codigo_postal'] = trim($data['codigo_postal'] ?? '');
        
        return $validated;
    }
    
    /**
     * Obtiene estadísticas para vendedores
     */
    private function getVendedorStats($userId) {
        // TODO: Implementar cuando tengamos tablas de productos/pedidos
        return [
            'productos_publicados' => 0,
            'ventas_totales' => 0,
            'calificacion_promedio' => 0,
            'pedidos_pendientes' => 0
        ];
    }
    
    /**
     * Obtiene estadísticas para clientes
     */
    private function getClienteStats($userId) {
        // TODO: Implementar cuando tengamos tablas de pedidos
        return [
            'compras_totales' => 0,
            'pedidos_realizados' => 0,
            'dinero_gastado' => 0,
            'productos_favoritos' => 0
        ];
    }
    
    /**
     * Estados de México
     */
    private function getEstadosMexico() {
        return [
            'aguascalientes' => 'Aguascalientes',
            'baja_california' => 'Baja California',
            'baja_california_sur' => 'Baja California Sur',
            'campeche' => 'Campeche',
            'chiapas' => 'Chiapas',
            'chihuahua' => 'Chihuahua',
            'coahuila' => 'Coahuila',
            'colima' => 'Colima',
            'cdmx' => 'Ciudad de México',
            'durango' => 'Durango',
            'guanajuato' => 'Guanajuato',
            'guerrero' => 'Guerrero',
            'hidalgo' => 'Hidalgo',
            'jalisco' => 'Jalisco',
            'mexico' => 'Estado de México',
            'michoacan' => 'Michoacán',
            'morelos' => 'Morelos',
            'nayarit' => 'Nayarit',
            'nuevo_leon' => 'Nuevo León',
            'oaxaca' => 'Oaxaca',
            'puebla' => 'Puebla',
            'queretaro' => 'Querétaro',
            'quintana_roo' => 'Quintana Roo',
            'san_luis_potosi' => 'San Luis Potosí',
            'sinaloa' => 'Sinaloa',
            'sonora' => 'Sonora',
            'tabasco' => 'Tabasco',
            'tamaulipas' => 'Tamaulipas',
            'tlaxcala' => 'Tlaxcala',
            'veracruz' => 'Veracruz',
            'yucatan' => 'Yucatán',
            'zacatecas' => 'Zacatecas'
        ];
    }
}
?>