<?php
/**
 * Página de debug para forgot password
 */
session_start();

// Cargar SessionManager para ver los mensajes flash
require_once '../core/SessionManager.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Debug - Forgot Password</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <h1>🔍 Debug - Forgot Password Status</h1>
    
    <h2>💾 Session Flash Messages</h2>
    <?php
    $successMessage = SessionManager::getFlash('success');
    $errorMessage = SessionManager::getFlash('error');
    
    if ($successMessage): ?>
        <div class="success">
            <strong>✅ Success:</strong> <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <div class="error">
            <strong>❌ Error:</strong> <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!$successMessage && !$errorMessage): ?>
        <div class="info">
            <strong>ℹ️ Info:</strong> No hay mensajes flash en la sesión.
        </div>
    <?php endif; ?>

    <h2>🗄️ Database Check</h2>
    <?php
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=agroconecta_db;charset=utf8mb4", 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $stmt = $pdo->prepare("SELECT correo, token_reset, fecha_actualizacion FROM Usuario WHERE token_reset IS NOT NULL ORDER BY fecha_actualizacion DESC LIMIT 3");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($results) {
            echo "<div class='success'>";
            echo "<strong>✅ Últimos tokens de reset en BD:</strong><br>";
            foreach ($results as $result) {
                $resetUrl = "http://localhost/AgroConecta_v2/public/reset-password.php?token=" . urlencode($result['token_reset']);
                echo "<p>";
                echo "<strong>Email:</strong> " . htmlspecialchars($result['correo']) . "<br>";
                echo "<strong>Token (16 chars):</strong> " . htmlspecialchars(substr($result['token_reset'], 0, 16)) . "...<br>";
                echo "<strong>Fecha:</strong> " . htmlspecialchars($result['fecha_actualizacion']) . "<br>";
                echo "<strong>🔗 URL Reset:</strong> <a href='" . htmlspecialchars($resetUrl) . "' target='_blank'>Probar Reset</a>";
                echo "</p><hr>";
            }
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<strong>❌ No hay tokens de reset en la base de datos</strong>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<strong>❌ Error conectando a BD:</strong> " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    ?>

    <h2>📋 Actions</h2>
    <p><a href="forgot-password.php">🔄 Probar forgot-password.php</a></p>
    <p><a href="login.php">🔐 Ir a login.php</a></p>
    
    <h2>📝 Session Info</h2>
    <pre><?php print_r($_SESSION); ?></pre>
</body>
</html>