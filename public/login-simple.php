<?php
/**
 * Login Simple sin redirecciones - AgroConecta
 * Para evitar bucles de redirección
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión básica PHP
session_start();

echo "<h2>🔐 Login Simple - AgroConecta</h2>";

// Mostrar estado actual
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>📊 Estado Actual:</h4>";
echo "<p><strong>Sesión activa:</strong> " . (session_id() ? 'Sí (' . session_id() . ')' : 'No') . "</p>";

if (!empty($_SESSION)) {
    echo "<p><strong>Datos en sesión:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 4px;'>";
    foreach ($_SESSION as $key => $value) {
        if (is_array($value)) {
            echo "$key: " . print_r($value, true) . "\n";
        } else {
            echo "$key: $value\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p><strong>Sesión:</strong> Vacía</p>";
}
echo "</div>";

// Formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>🔄 Procesando Login...</h3>";
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    echo "<p>📧 Email: <code>" . htmlspecialchars($email) . "</code></p>";
    echo "<p>🔑 Password: " . (empty($password) ? "❌ Vacío" : "✅ Presente") . "</p>";
    
    if (!empty($email) && !empty($password)) {
        try {
            // Cargar solo lo esencial
            require_once '../config/database.php';
            require_once '../core/Database.php';
            
            $db = Database::getInstance()->getConnection();
            
            // Buscar usuario
            $stmt = $db->prepare("SELECT id_usuario, nombre, correo, contraseña, tipo_usuario, activo FROM usuario WHERE correo = ? AND activo = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['contraseña'])) {
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
                echo "<h4>✅ Login Exitoso</h4>";
                echo "<p><strong>Usuario:</strong> " . htmlspecialchars($user['nombre']) . "</p>";
                echo "<p><strong>Tipo:</strong> " . $user['tipo_usuario'] . "</p>";
                
                // Establecer sesión manualmente
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['user_email'] = $user['correo'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_tipo'] = $user['tipo_usuario'];
                $_SESSION['login_time'] = time();
                
                echo "<p>✅ Datos guardados en sesión</p>";
                echo "<p><a href='dashboard.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Dashboard</a></p>";
                echo "</div>";
                
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
                echo "<h4>❌ Credenciales Incorrectas</h4>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
            echo "<h4>❌ Error del Sistema</h4>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
        echo "<h4>⚠️ Campos Requeridos</h4>";
        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Simple - AgroConecta</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
            background-color: #f8f9fa;
            max-width: 800px;
        }
        h2, h3, h4 { color: #2c3e50; }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="email"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #007bff;
        }
        button {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        pre {
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h3>📝 Formulario de Login</h3>
    <form method="POST">
        <div class="form-group">
            <label for="email">📧 Email:</label>
            <input type="email" name="email" id="email" value="vendedor@test.com" required>
        </div>
        
        <div class="form-group">
            <label for="password">🔒 Contraseña:</label>
            <input type="password" name="password" id="password" value="prueba123" required>
        </div>
        
        <button type="submit">🚀 Iniciar Sesión</button>
    </form>
</div>

<div style="margin: 20px 0;">
    <h4>🔗 Enlaces útiles:</h4>
    <a href="diagnostico-sesion.php" style="background: #17a2b8; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;">🔧 Diagnóstico</a>
    <a href="diagnostico-sesion.php?action=clear_session" style="background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-right: 10px;">🧹 Limpiar Sesión</a>
    <a href="login.php" style="background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">🔙 Login Original</a>
</div>

</body>
</html>