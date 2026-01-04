<?php
/**
 * Procesamiento de Login Simple - AgroConecta
 * Autentica usuarios y redirige al dashboard correspondiente
 */

require_once '../config/database.php';
require_once '../core/SessionManager.php';
require_once '../core/Database.php';

// Inicializar sesión
SessionManager::startSecureSession();

// Solo procesar si es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Obtener datos del formulario
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

// Validaciones básicas
if (empty($email) || empty($password)) {
    SessionManager::setFlash('error', 'Por favor, completa todos los campos');
    header('Location: login.php');
    exit;
}

try {
    // Conectar directamente a la base de datos
    $db = Database::getInstance()->getConnection();
    
    // Buscar usuario por email
    $stmt = $db->prepare("SELECT id_usuario, nombre, correo, contraseña, tipo_usuario, activo FROM usuario WHERE correo = ? AND activo = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['contraseña'])) {
        // Login exitoso - mapear campos correctos para SessionManager
        $userData = [
            'id' => $user['id_usuario'],
            'correo' => $user['correo'], 
            'nombre' => $user['nombre'],
            'tipo_usuario' => $user['tipo_usuario']
        ];
        
        SessionManager::setUserData($userData);
        SessionManager::setFlash('success', '¡Bienvenido de vuelta, ' . $user['nombre'] . '!');
        
        // Redirigir al dashboard
        header('Location: dashboard.php');
        exit;
        
    } else {
        // Credenciales incorrectas
        SessionManager::setFlash('error', 'Email o contraseña incorrectos');
        header('Location: login.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error en login: " . $e->getMessage());
    SessionManager::setFlash('error', 'Error interno del sistema. Inténtalo más tarde.');
    header('Location: login.php');
    exit;
}
?>