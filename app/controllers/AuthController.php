<?php
/**
 * AuthController - Controlador de Autenticación
 * Maneja login, registro, logout y verificación de email
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'BaseController.php';
require_once APP_PATH . '/models/Usuario.php';
require_once APP_PATH . '/models/Notificacion.php';

class AuthController extends BaseController {
    
    /**
     * Muestra el formulario de login
     */
    public function login() {
        // Si ya está logueado, redirigir al dashboard
        if ($this->isLoggedIn) {
            $this->redirectToDashboard();
            return;
        }
        
        $this->setViewData('csrf_token', $this->generateCSRF());
        $this->setViewData('pageTitle', 'Iniciar Sesión');
        $this->setViewData('error', $this->getFlashMessage('error'));
        $this->setViewData('success', $this->getFlashMessage('success'));
        
        $this->render('auth/login');
    }
    
    /**
     * Procesa el login
     */
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/login');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Validación básica
        if (empty($email) || empty($password)) {
            $this->setFlashMessage('error', 'Email y contraseña son requeridos');
            $this->redirect('/auth/login');
            return;
        }
        
        $userModel = new Usuario();
        $user = $userModel->verifyLogin($email, $password);
        
        if (!$user) {
            $this->logActivity('login_failed', "Email: {$email}");
            $this->setFlashMessage('error', 'Email o contraseña incorrectos');
            $this->redirect('/auth/login');
            return;
        }
        
        // Verificar si la cuenta está verificada (opcional)
        if (!$user['verificado']) {
            $this->setFlashMessage('error', 'Debes verificar tu cuenta por email antes de continuar');
            $this->redirect('/auth/login');
            return;
        }
        
        // Login exitoso
        $this->startUserSession($user, $remember);
        $this->logActivity('login_success');
        
        // Redirigir a donde intentaba ir o dashboard
        $redirectTo = $_SESSION['redirect_after_login'] ?? $this->getDashboardUrl();
        unset($_SESSION['redirect_after_login']);
        
        $this->redirect($redirectTo);
    }
    
    /**
     * Muestra el formulario de registro
     */
    public function register() {
        if ($this->isLoggedIn) {
            $this->redirectToDashboard();
            return;
        }
        
        $this->setViewData('csrf_token', $this->generateCSRF());
        $this->setViewData('pageTitle', 'Crear Cuenta');
        $this->setViewData('error', $this->getFlashMessage('error'));
        $this->setViewData('success', $this->getFlashMessage('success'));
        
        $this->render('auth/register');
    }
    
    /**
     * Procesa el registro
     */
    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/register');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $data = $this->sanitizeInput([
            'nombre' => $_POST['nombre'] ?? '',
            'apellido' => $_POST['apellido'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'tipo_usuario' => $_POST['tipo_usuario'] ?? 'cliente',
            'telefono' => $_POST['telefono'] ?? '',
            'terminos' => isset($_POST['terminos'])
        ]);
        
        // Validaciones
        $errors = $this->validateRegistration($data);
        
        if (!empty($errors)) {
            $_SESSION['registration_errors'] = $errors;
            $_SESSION['registration_data'] = $data;
            $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            $this->redirect('/auth/register');
            return;
        }
        
        try {
            $userModel = new Usuario();
            
            // Crear usuario
            $userId = $userModel->createUser([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'correo' => strtolower($data['email']),
                'contraseña' => $data['password'],
                'tipo_usuario' => $data['tipo_usuario'],
                'telefono' => $data['telefono'],
                'verificado' => 0  // Requiere verificación por email
            ]);
            
            if ($userId) {
                // Generar token de verificación
                $token = $userModel->generateVerificationToken($userId);
                
                // Enviar email de verificación (implementar después)
                $this->sendVerificationEmail($data['email'], $data['nombre'], $token);
                
                // Notificación de bienvenida
                $notifModel = new Notificacion();
                $notifModel->notificarBienvenida($userId, $data['nombre']);
                
                $this->logActivity('user_registered', "New user: {$data['email']} ({$data['tipo_usuario']})");
                
                $this->setFlashMessage('success', '¡Cuenta creada exitosamente! Revisa tu email para verificar tu cuenta.');
                $this->redirect('/auth/login');
            } else {
                throw new Exception('Error al crear la cuenta');
            }
            
        } catch (Exception $e) {
            $this->logActivity('registration_failed', $e->getMessage());
            $this->setFlashMessage('error', 'Error al crear la cuenta. Inténtalo de nuevo.');
            $this->redirect('/auth/register');
        }
    }
    
    /**
     * Cierra sesión
     */
    public function logout() {
        if ($this->isLoggedIn) {
            $this->logActivity('logout');
        }
        
        $this->logout();
        $this->setFlashMessage('success', 'Has cerrado sesión correctamente');
        $this->redirect('/');
    }
    
    /**
     * Muestra formulario de recuperación de contraseña
     */
    public function forgotPassword() {
        if ($this->isLoggedIn) {
            $this->redirectToDashboard();
            return;
        }
        
        $this->setViewData('csrf_token', $this->generateCSRF());
        $this->setViewData('pageTitle', 'Recuperar Contraseña');
        $this->setViewData('error', $this->getFlashMessage('error'));
        $this->setViewData('success', $this->getFlashMessage('success'));
        
        $this->render('auth/forgot-password');
    }
    
    /**
     * Procesa solicitud de recuperación
     */
    public function processForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/forgot-password');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlashMessage('error', 'Email inválido');
            $this->redirect('/auth/forgot-password');
            return;
        }
        
        $userModel = new Usuario();
        $token = $userModel->generateResetToken($email);
        
        if ($token) {
            $this->sendPasswordResetEmail($email, $token);
            $this->logActivity('password_reset_requested', "Email: {$email}");
        }
        
        // Siempre mostrar mensaje de éxito por seguridad
        $this->setFlashMessage('success', 'Si el email existe, recibirás instrucciones para recuperar tu contraseña');
        $this->redirect('/auth/login');
    }
    
    /**
     * Muestra formulario de reset de contraseña
     */
    public function resetPassword($token = '') {
        if ($this->isLoggedIn) {
            $this->redirectToDashboard();
            return;
        }
        
        if (empty($token)) {
            $this->setFlashMessage('error', 'Token inválido');
            $this->redirect('/auth/login');
            return;
        }
        
        $userModel = new Usuario();
        $user = $userModel->verifyResetToken($token);
        
        if (!$user) {
            $this->setFlashMessage('error', 'Token inválido o expirado');
            $this->redirect('/auth/login');
            return;
        }
        
        $this->setViewData('csrf_token', $this->generateCSRF());
        $this->setViewData('pageTitle', 'Nueva Contraseña');
        $this->setViewData('token', $token);
        $this->setViewData('error', $this->getFlashMessage('error'));
        
        $this->render('auth/reset-password');
    }
    
    /**
     * Procesa el reset de contraseña
     */
    public function processResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/login');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        // Validaciones
        if (empty($password) || strlen($password) < 6) {
            $this->setFlashMessage('error', 'La contraseña debe tener al menos 6 caracteres');
            $this->redirect('/auth/reset-password/' . $token);
            return;
        }
        
        if ($password !== $passwordConfirm) {
            $this->setFlashMessage('error', 'Las contraseñas no coinciden');
            $this->redirect('/auth/reset-password/' . $token);
            return;
        }
        
        $userModel = new Usuario();
        $user = $userModel->verifyResetToken($token);
        
        if (!$user) {
            $this->setFlashMessage('error', 'Token inválido o expirado');
            $this->redirect('/auth/login');
            return;
        }
        
        // Actualizar contraseña
        if ($userModel->updatePassword($user['id_usuario'], $password)) {
            $this->logActivity('password_reset_success', "User ID: {$user['id_usuario']}");
            $this->setFlashMessage('success', 'Contraseña actualizada correctamente');
            $this->redirect('/auth/login');
        } else {
            $this->setFlashMessage('error', 'Error al actualizar la contraseña');
            $this->redirect('/auth/reset-password/' . $token);
        }
    }
    
    /**
     * Verifica email con token
     */
    public function verifyEmail($token = '') {
        if (empty($token)) {
            $this->setFlashMessage('error', 'Token de verificación inválido');
            $this->redirect('/auth/login');
            return;
        }
        
        $userModel = new Usuario();
        $result = $userModel->verifyUser($token);
        
        if ($result) {
            $this->setFlashMessage('success', '¡Email verificado correctamente! Ya puedes iniciar sesión.');
        } else {
            $this->setFlashMessage('error', 'Token de verificación inválido o expirado');
        }
        
        $this->redirect('/auth/login');
    }
    
    // MÉTODOS PRIVADOS
    
    /**
     * Inicia sesión del usuario
     */
    private function startUserSession($user, $remember = false) {
        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['user_email'] = $user['correo'];
        $_SESSION['user_type'] = $user['tipo_usuario'];
        $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];
        
        if ($remember) {
            // Cookie de "recordarme" por 30 días
            setcookie('remember_user', base64_encode($user['id_usuario']), time() + (30 * 24 * 60 * 60), '/');
        }
    }
    
    /**
     * Redirige al dashboard apropiado según el tipo de usuario
     */
    private function redirectToDashboard() {
        $this->redirect($this->getDashboardUrl());
    }
    
    /**
     * Obtiene la URL del dashboard según el tipo de usuario
     */
    private function getDashboardUrl() {
        if (!$this->isLoggedIn) return '/';
        
        switch ($this->currentUser['tipo_usuario']) {
            case 'admin':
                return '/admin/dashboard';
            case 'vendedor':
                return '/vendedor/dashboard';
            case 'cliente':
                return '/usuario/dashboard';
            default:
                return '/';
        }
    }
    
    /**
     * Valida datos de registro
     */
    private function validateRegistration($data) {
        $rules = [
            'nombre' => [
                'required' => true,
                'min' => 2,
                'max' => 50,
                'message' => 'El nombre debe tener entre 2 y 50 caracteres'
            ],
            'apellido' => [
                'required' => true,
                'min' => 2,
                'max' => 50,
                'message' => 'El apellido debe tener entre 2 y 50 caracteres'
            ],
            'email' => [
                'required' => true,
                'email' => true,
                'unique' => ['model' => 'Usuario'],
                'message' => 'Email inválido o ya registrado'
            ]
        ];
        
        $errors = $this->validateForm($data, $rules);
        
        // Validaciones adicionales
        if (strlen($data['password']) < 6) {
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if ($data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'] = 'Las contraseñas no coinciden';
        }
        
        if (!$data['terminos']) {
            $errors['terminos'] = 'Debes aceptar los términos y condiciones';
        }
        
        if (!in_array($data['tipo_usuario'], ['cliente', 'vendedor'])) {
            $errors['tipo_usuario'] = 'Tipo de usuario inválido';
        }
        
        return $errors;
    }
    
    /**
     * Envía email de verificación
     */
    private function sendVerificationEmail($email, $nombre, $token) {
        // TODO: Implementar envío de email
        // Por ahora, log del token para testing
        error_log("Verification email for {$email}: " . BASE_URL . "/auth/verify/{$token}");
    }
    
    /**
     * Envía email de recuperación de contraseña
     */
    private function sendPasswordResetEmail($email, $token) {
        // TODO: Implementar envío de email
        error_log("Password reset email for {$email}: " . BASE_URL . "/auth/reset-password/{$token}");
    }
}
?>