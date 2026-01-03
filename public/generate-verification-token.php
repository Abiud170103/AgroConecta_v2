<?php
require_once '../core/SessionManager.php';
require_once '../core/Database.php';
require_once '../app/models/Usuario.php';

SessionManager::startSecureSession();

// Solo procesar POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$userId = intval($_POST['user_id'] ?? 0);

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user ID']);
    exit;
}

try {
    $userModel = new Usuario();
    
    // Verificar que el usuario existe
    $user = $userModel->find($userId);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    // Generar nuevo token
    $token = $userModel->generateVerificationToken($userId);
    
    if ($token) {
        error_log("New verification token generated for user ID {$userId}: " . substr($token, 0, 16) . "...");
        echo json_encode([
            'success' => true, 
            'message' => 'Token generated successfully',
            'token' => substr($token, 0, 16) . '...'
        ]);
    } else {
        throw new Exception('Failed to generate token');
    }
    
} catch (Exception $e) {
    error_log("Error generating verification token: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>