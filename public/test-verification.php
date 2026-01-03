<?php
require_once '../core/SessionManager.php';
require_once '../core/Database.php';
require_once '../app/models/Usuario.php';

SessionManager::startSecureSession();

echo "<h1>🧪 Prueba del Sistema de Verificación de Email</h1>\n";
echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>\n";

try {
    $userModel = new Usuario();
    
    // Verificar si hay usuarios sin verificar usando método público
    $unverifiedUsers = $userModel->all(['verificado' => 0]);
    
    // Limitar a los últimos 5 para la interfaz
    $unverifiedUsers = array_slice($unverifiedUsers, 0, 5);
    
    if ($unverifiedUsers) {
        echo "<h2>👥 Usuarios Sin Verificar (últimos 5)</h2>\n";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Nombre</th><th>Email</th><th>Token</th><th>Acciones</th>";
        echo "</tr>\n";
        
        foreach ($unverifiedUsers as $user) {
            echo "<tr>\n";
            echo "<td>{$user['id_usuario']}</td>\n";
            echo "<td>{$user['nombre']} {$user['apellido']}</td>\n";
            echo "<td>{$user['correo']}</td>\n";
            echo "<td>" . (empty($user['token_verificacion']) ? 'Sin token' : substr($user['token_verificacion'], 0, 16) . '...') . "</td>\n";
            echo "<td>";
            
            if (!empty($user['token_verificacion'])) {
                $verifyUrl = "verify-email.php?token=" . urlencode($user['token_verificacion']);
                echo "<a href='{$verifyUrl}' style='color: green; font-weight: bold;'>✅ Verificar</a><br>";
            }
            
            echo "<a href='#' onclick=\"generateToken({$user['id_usuario']})\" style='color: blue;'>🔄 Generar Token</a>";
            echo "</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<p>✅ No hay usuarios pendientes de verificación.</p>";
        echo "</div>\n";
    }
    
    // Mostrar estadísticas generales
    $allUsers = $userModel->all();
    $total = count($allUsers);
    $verificados = count(array_filter($allUsers, function($user) { return $user['verificado'] == 1; }));
    $sin_verificar = $total - $verificados;
    
    $stats = [
        'total' => $total,
        'verificados' => $verificados, 
        'sin_verificar' => $sin_verificar
    ];
    
    echo "<h2>📊 Estadísticas</h2>\n";
    echo "<div style='display: flex; gap: 20px;'>\n";
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 8px;'>";
    echo "<h4>Total Usuarios</h4>";
    echo "<h2 style='margin: 0; color: #1976d2;'>{$stats['total']}</h2>";
    echo "</div>\n";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px;'>";
    echo "<h4>Verificados</h4>";
    echo "<h2 style='margin: 0; color: #2e7d32;'>{$stats['verificados']}</h2>";
    echo "</div>\n";
    echo "<div style='background: #fff3e0; padding: 15px; border-radius: 8px;'>";
    echo "<h4>Sin Verificar</h4>";
    echo "<h2 style='margin: 0; color: #f57c00;'>{$stats['sin_verificar']}</h2>";
    echo "</div>\n";
    echo "</div>\n";
    
    // Formulario para crear usuario de prueba
    echo "<h2>🛠️ Crear Usuario de Prueba</h2>\n";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_test_user'])) {
        $testEmail = 'verificacion.test@agroconecta.com';
        
        // Eliminar usuario de prueba existente
        $existingUser = $userModel->findByEmail($testEmail);
        if ($existingUser) {
            // Usar método delete del modelo base
            $userModel->delete($existingUser['id_usuario']);
        }
        
        // Crear nuevo usuario
        $userId = $userModel->createUser([
            'nombre' => 'Test',
            'apellido' => 'Verificacion',
            'correo' => $testEmail,
            'contraseña' => '123456789',
            'telefono' => '5551234567',
            'tipo_usuario' => 'cliente',
            'activo' => 1,
            'verificado' => 0
        ]);
        
        if ($userId) {
            $token = $userModel->generateVerificationToken($userId);
            echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            echo "<p>✅ Usuario de prueba creado exitosamente</p>";
            echo "<p><strong>Email:</strong> {$testEmail}</p>";
            echo "<p><strong>Password:</strong> 123456789</p>";
            if ($token) {
                $verifyUrl = "verify-email.php?token=" . urlencode($token);
                echo "<p><strong>URL de verificación:</strong> <a href='{$verifyUrl}'>{$verifyUrl}</a></p>";
            }
            echo "</div>\n";
        }
        
        // Recargar página para mostrar estadísticas actualizadas
        echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
    }
    
    echo "<form method='POST'>\n";
    echo "<button type='submit' name='create_test_user' style='background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;'>Crear Usuario de Prueba</button>\n";
    echo "</form>\n";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>🔗 Enlaces Útiles</h2>\n";
echo "<ul>\n";
echo "<li><a href='register.php'>📝 Registro</a></li>\n";
echo "<li><a href='login.php'>🔐 Login</a></li>\n";
echo "<li><a href='email-verification.php'>📧 Reenviar Verificación</a></li>\n";
echo "<li><a href='forgot-password.php'>🔑 Recuperar Contraseña</a></li>\n";
echo "</ul>\n";

echo "<script>\n";
echo "function generateToken(userId) {\n";
echo "  if (confirm('¿Generar nuevo token de verificación para el usuario ' + userId + '?')) {\n";
echo "    fetch('generate-verification-token.php', {\n";
echo "      method: 'POST',\n";
echo "      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },\n";
echo "      body: 'user_id=' + userId\n";
echo "    }).then(() => window.location.reload());\n";
echo "  }\n";
echo "}\n";
echo "</script>\n";
?>