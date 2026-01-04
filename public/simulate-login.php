<?php
require_once '../core/SessionManager.php';
require_once '../app/models/Usuario.php';

SessionManager::startSecureSession();

// Solo para desarrollo y pruebas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);
    
    if ($userId > 0) {
        $userModel = new Usuario();
        $user = $userModel->find($userId);
        
        if ($user) {
            // Simular login
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_email'] = $user['correo'];
            $_SESSION['user_type'] = $user['tipo_usuario'];
            $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];
            $_SESSION['logged_in'] = true;
            
            echo json_encode(['success' => true, 'message' => 'Login simulado exitoso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>