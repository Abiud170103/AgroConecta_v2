<?php
require_once '../core/SessionManager.php';
require_once '../core/Database.php';
require_once '../app/models/Usuario.php';

SessionManager::startSecureSession();

// Solo procesar POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// Verificar token CSRF
if (!SessionManager::validateCSRF($_POST['csrf_token'] ?? '')) {
    SessionManager::setFlash('error', 'Token de seguridad inválido');
    header('Location: register.php');
    exit;
}

try {
    // Obtener y validar datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $tipoUsuario = $_POST['tipo_usuario'] ?? '';
    $terminos = isset($_POST['terminos']);

    // Validaciones
    $errors = [];

    if (empty($nombre) || strlen($nombre) < 2) {
        $errors[] = 'El nombre debe tener al menos 2 caracteres';
    }

    if (empty($apellido) || strlen($apellido) < 2) {
        $errors[] = 'El apellido debe tener al menos 2 caracteres';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'Las contraseñas no coinciden';
    }

    if (!$terminos) {
        $errors[] = 'Debes aceptar los términos y condiciones';
    }

    if (!in_array($tipoUsuario, ['cliente', 'vendedor'])) {
        $errors[] = 'Tipo de usuario inválido';
    }

    // Verificar si el email ya existe
    $userModel = new Usuario();
    if ($userModel->emailExists($email)) {
        $errors[] = 'Este email ya está registrado';
    }

    if (!empty($errors)) {
        SessionManager::setFlash('error', implode('<br>', $errors));
        header('Location: register.php');
        exit;
    }

    // Crear usuario
    $userData = [
        'nombre' => $nombre,
        'apellido' => $apellido,
        'correo' => $email,
        'contraseña' => $password, // El modelo se encarga del hash
        'telefono' => $telefono,
        'tipo_usuario' => $tipoUsuario,
        'activo' => 1,
        'verificado' => 0 // Requiere verificación
    ];

    $userId = $userModel->createUser($userData);

    if ($userId) {
        // Generar token de verificación
        $token = $userModel->generateVerificationToken($userId);
        
        if ($token) {
            // Log del token para desarrollo (en producción se enviaría por email)
            $verificationUrl = "http://localhost/AgroConecta_v2/public/verify-email.php?token=" . urlencode($token);
            error_log("Email verification URL for {$email}: {$verificationUrl}");
            
            SessionManager::setFlash('success', '¡Cuenta creada exitosamente! 📧 Revisa tu email para verificar tu cuenta antes de iniciar sesión.');
            error_log("User registered successfully: {$email} (ID: {$userId})");
        } else {
            // Si falla la generación del token, aún así permitir el registro
            SessionManager::setFlash('success', '¡Cuenta creada exitosamente! Puedes iniciar sesión ahora.');
            error_log("User registered but verification token generation failed: {$email}");
        }
        
        header('Location: login.php');
        exit;
    } else {
        throw new Exception('Error al crear la cuenta');
    }

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    SessionManager::setFlash('error', 'Error al crear la cuenta. Inténtalo de nuevo.');
    header('Location: register.php');
    exit;
}
?>