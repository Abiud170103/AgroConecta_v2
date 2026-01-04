<?php
/**
 * Procesamiento de Login con Debug - AgroConecta
 * Versión de debug para identificar problemas
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';
require_once '../core/SessionManager.php';
require_once '../core/Database.php';

echo "<h2>🔍 Debug de Login - AgroConecta</h2>";

// Inicializar sesión
SessionManager::startSecureSession();
echo "<p>✅ Sesión iniciada</p>";

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<p>❌ Método no es POST. Método actual: " . $_SERVER['REQUEST_METHOD'] . "</p>";
    echo "<p><a href='login.php'>Volver al login</a></p>";
    exit;
}

echo "<p>✅ Método POST detectado</p>";

// Obtener datos del formulario
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

echo "<p>📧 Email recibido: <code>" . htmlspecialchars($email) . "</code></p>";
echo "<p>🔑 Password recibido: " . (empty($password) ? "❌ VACÍO" : "✅ Con contenido (" . strlen($password) . " caracteres)") . "</p>";

// Validaciones básicas
if (empty($email) || empty($password)) {
    echo "<p>❌ Error: Campos vacíos</p>";
    echo "<ul>";
    if (empty($email)) echo "<li>Email está vacío</li>";
    if (empty($password)) echo "<li>Password está vacío</li>";
    echo "</ul>";
    echo "<p><a href='login.php'>Volver al login</a></p>";
    exit;
}

echo "<p>✅ Validación de campos completos</p>";

try {
    // Conectar a la base de datos
    echo "<h3>🔌 Conectando a la base de datos...</h3>";
    $db = Database::getInstance()->getConnection();
    echo "<p>✅ Conexión establecida</p>";
    
    // Buscar usuario por email
    echo "<h3>👤 Buscando usuario...</h3>";
    $stmt = $db->prepare("SELECT id_usuario, nombre, correo, contraseña, tipo_usuario, activo FROM usuario WHERE correo = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p>❌ Usuario no encontrado con email: <code>" . htmlspecialchars($email) . "</code></p>";
        echo "<h4>📋 Usuarios disponibles en la base de datos:</h4>";
        
        // Mostrar todos los usuarios disponibles
        $stmt = $db->query("SELECT correo, tipo_usuario, activo FROM usuario ORDER BY tipo_usuario");
        $todos_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($todos_usuarios) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Email</th><th>Tipo</th><th>Activo</th></tr>";
            foreach ($todos_usuarios as $u) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($u['correo']) . "</td>";
                echo "<td>" . $u['tipo_usuario'] . "</td>";
                echo "<td>" . ($u['activo'] ? 'Sí' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ No hay usuarios en la base de datos</p>";
            echo "<p><a href='crear-usuarios-prueba.php'>Crear usuarios de prueba</a></p>";
        }
        
        echo "<p><a href='login.php'>Volver al login</a></p>";
        exit;
    }
    
    echo "<p>✅ Usuario encontrado: <strong>" . htmlspecialchars($user['nombre']) . "</strong></p>";
    echo "<p>📊 Detalles del usuario:</p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $user['id_usuario'] . "</li>";
    echo "<li><strong>Nombre:</strong> " . htmlspecialchars($user['nombre']) . "</li>";
    echo "<li><strong>Email:</strong> " . htmlspecialchars($user['correo']) . "</li>";
    echo "<li><strong>Tipo:</strong> " . $user['tipo_usuario'] . "</li>";
    echo "<li><strong>Activo:</strong> " . ($user['activo'] ? 'Sí' : 'No') . "</li>";
    echo "</ul>";
    
    // Verificar si está activo
    if (!$user['activo']) {
        echo "<p>❌ Usuario inactivo</p>";
        echo "<p><a href='login.php'>Volver al login</a></p>";
        exit;
    }
    
    echo "<p>✅ Usuario activo</p>";
    
    // Verificar password
    echo "<h3>🔐 Verificando contraseña...</h3>";
    
    if (password_verify($password, $user['contraseña'])) {
        echo "<p>✅ Contraseña correcta</p>";
        
        // Preparar datos para sesión
        echo "<h3>📝 Configurando sesión...</h3>";
        $userData = [
            'id' => $user['id_usuario'],
            'correo' => $user['correo'], 
            'nombre' => $user['nombre'],
            'tipo_usuario' => $user['tipo_usuario']
        ];
        
        echo "<p>📊 Datos para sesión:</p>";
        echo "<pre>" . print_r($userData, true) . "</pre>";
        
        // Establecer datos en sesión
        SessionManager::setUserData($userData);
        echo "<p>✅ Datos establecidos en sesión</p>";
        
        // Verificar que se guardaron
        echo "<h3>✅ Verificando datos en sesión...</h3>";
        $sessionData = SessionManager::getUserData();
        if ($sessionData) {
            echo "<p>✅ Datos recuperados de sesión:</p>";
            echo "<pre>" . print_r($sessionData, true) . "</pre>";
            
            // Verificar si está logueado
            if (SessionManager::isLoggedIn()) {
                echo "<p>✅ Usuario autenticado correctamente</p>";
                
                // Mostrar enlaces de prueba
                echo "<hr>";
                echo "<h3>🔗 Opciones de navegación:</h3>";
                echo "<p>";
                echo "<a href='dashboard.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Ir al Dashboard</a>";
                echo "<a href='test-dashboard.php' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Dashboard</a>";
                echo "</p>";
                
            } else {
                echo "<p>❌ Error: Usuario no está logueado según SessionManager::isLoggedIn()</p>";
            }
        } else {
            echo "<p>❌ Error: No se pudieron recuperar los datos de sesión</p>";
        }
        
    } else {
        echo "<p>❌ Contraseña incorrecta</p>";
        echo "<p>Password ingresado: <code>" . htmlspecialchars($password) . "</code></p>";
        echo "<p><a href='login.php'>Volver al login</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error de sistema:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Debug Login - AgroConecta</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h2, h3 { color: #2c3e50; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        code { background: #e8e8e8; padding: 2px 6px; border-radius: 3px; }
        table { background: white; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px 12px; border: 1px solid #ddd; }
        th { background: #34495e; color: white; }
    </style>
</head>
</html>