<?php
require_once '../core/SessionManager.php';
require_once '../core/Database.php';
require_once '../app/models/Usuario.php';

SessionManager::startSecureSession();

echo "<h1>🧪 Prueba del Sistema de Perfiles</h1>\n";
echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>\n";

// Verificar si hay usuarios para probar
$userModel = new Usuario();
$users = $userModel->all();

if (empty($users)) {
    echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<p>⚠️ No hay usuarios en el sistema. Crea algunos usuarios primero.</p>";
    echo "<a href='test-verification.php' style='color: blue;'>Ir a Crear Usuarios</a>";
    echo "</div>\n";
} else {
    echo "<h2>👥 Usuarios Disponibles</h2>\n";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Nombre</th><th>Email</th><th>Tipo</th><th>Verificado</th><th>Acciones</th>";
    echo "</tr>\n";
    
    foreach ($users as $user) {
        echo "<tr>\n";
        echo "<td>{$user['id_usuario']}</td>\n";
        echo "<td>{$user['nombre']} {$user['apellido']}</td>\n";
        echo "<td>{$user['correo']}</td>\n";
        echo "<td>" . ucfirst($user['tipo_usuario']) . "</td>\n";
        echo "<td>" . ($user['verificado'] ? '✅' : '❌') . "</td>\n";
        echo "<td>";
        echo "<a href='#' onclick=\"loginAs({$user['id_usuario']}, '{$user['correo']}')\" style='color: green; font-weight: bold;'>🔑 Login</a>";
        echo "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
}

echo "<hr>";
echo "<h2>🔗 Enlaces del Sistema de Perfiles</h2>\n";
echo "<ul>\n";
echo "<li><a href='profile.php'>👤 Ver Perfil (requiere login)</a></li>\n";
echo "<li><a href='edit-profile.php'>✏️ Editar Perfil (requiere login)</a></li>\n";
echo "<li><a href='login.php'>🔐 Login</a></li>\n";
echo "<li><a href='register.php'>📝 Registro</a></li>\n";
echo "</ul>\n";

echo "<script>\n";
echo "function loginAs(userId, email) {\n";
echo "  if (confirm('¿Hacer login como ' + email + '?')) {\n";
echo "    // Simular login temporal para pruebas\n";
echo "    fetch('simulate-login.php', {\n";
echo "      method: 'POST',\n";
echo "      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },\n";
echo "      body: 'user_id=' + userId\n";
echo "    }).then(() => {\n";
echo "      window.location.href = 'profile.php';\n";
echo "    });\n";
echo "  }\n";
echo "}\n";
echo "</script>\n";
?>