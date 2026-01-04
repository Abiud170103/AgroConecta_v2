<?php
/**
 * Process Login Debug - Muestra todo el proceso de login paso a paso
 */

// Capturar salida
ob_start();

// Log de inicio
$debugLog = [];
$debugLog[] = "=== INICIO DEL PROCESO DE LOGIN ===";
$debugLog[] = "Timestamp: " . date('Y-m-d H:i:s');
$debugLog[] = "Method: " . $_SERVER['REQUEST_METHOD'];
$debugLog[] = "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A');

try {
    require_once '../config/database.php';
    $debugLog[] = "✅ database.php cargado";
    
    require_once '../core/Database.php';
    $debugLog[] = "✅ Database.php cargado";
    
    require_once '../core/SessionManager.php';
    $debugLog[] = "✅ SessionManager.php cargado";

    // Iniciar sesión
    SessionManager::startSecureSession();
    $debugLog[] = "✅ Sesión iniciada";

    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $debugLog[] = "❌ Método no es POST: " . $_SERVER['REQUEST_METHOD'];
        throw new Exception("Método de solicitud inválido");
    }

    // Log de datos recibidos (sin mostrar contraseña)
    $debugLog[] = "📦 Datos POST recibidos:";
    foreach ($_POST as $key => $value) {
        if ($key === 'password') {
            $debugLog[] = "  - $key: [OCULTO - " . strlen($value) . " caracteres]";
        } else {
            $debugLog[] = "  - $key: " . ($value ? htmlspecialchars($value) : '[VACÍO]');
        }
    }

    // Obtener datos del formulario
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $debugLog[] = "📧 Email procesado: " . ($email ? htmlspecialchars($email) : '[VACÍO]');
    $debugLog[] = "🔐 Password: " . ($password ? "[" . strlen($password) . " caracteres]" : '[VACÍO]');

    // Validaciones
    if (empty($email) || empty($password)) {
        $debugLog[] = "❌ Datos faltantes - Email: " . (empty($email) ? 'VACÍO' : 'OK') . ", Password: " . (empty($password) ? 'VACÍO' : 'OK');
        throw new Exception("Email y contraseña son requeridos");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $debugLog[] = "❌ Email inválido: " . htmlspecialchars($email);
        throw new Exception("Formato de email inválido");
    }

    $debugLog[] = "✅ Validaciones básicas pasadas";

    // Conectar a base de datos
    try {
        $db = Database::getInstance()->getConnection();
        $debugLog[] = "✅ Conexión a BD establecida";
    } catch (Exception $e) {
        $debugLog[] = "❌ Error conectando a BD: " . $e->getMessage();
        throw new Exception("Error de conexión a base de datos: " . $e->getMessage());
    }

    // Buscar usuario
    $debugLog[] = "🔍 Buscando usuario en BD...";
    $stmt = $db->prepare("SELECT id_usuario, nombre, correo, contraseña, tipo_usuario, activo FROM usuario WHERE correo = ? AND activo = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $debugLog[] = "❌ Usuario no encontrado o inactivo: " . htmlspecialchars($email);
        
        // Verificar si el usuario existe pero está inactivo
        $stmt2 = $db->prepare("SELECT activo FROM usuario WHERE correo = ?");
        $stmt2->execute([$email]);
        $inactiveUser = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($inactiveUser) {
            $debugLog[] = "ℹ️ Usuario existe pero está inactivo (activo = " . $inactiveUser['activo'] . ")";
        } else {
            $debugLog[] = "ℹ️ Usuario no existe en la base de datos";
        }
        
        throw new Exception("Credenciales incorrectas");
    }

    $debugLog[] = "✅ Usuario encontrado: " . htmlspecialchars($user['nombre']) . " (" . htmlspecialchars($user['correo']) . ")";
    $debugLog[] = "👤 Tipo de usuario: " . htmlspecialchars($user['tipo_usuario']);

    // Verificar contraseña
    $debugLog[] = "🔐 Verificando contraseña...";
    if (!password_verify($password, $user['contraseña'])) {
        $debugLog[] = "❌ Contraseña incorrecta";
        throw new Exception("Credenciales incorrectas");
    }

    $debugLog[] = "✅ Contraseña correcta";

    // Preparar datos para sesión
    $userData = [
        'id' => $user['id_usuario'],
        'correo' => $user['correo'], 
        'nombre' => $user['nombre'],
        'tipo' => $user['tipo_usuario']  // Mapear tipo_usuario a tipo
    ];
    
    $debugLog[] = "📋 Datos para sesión preparados:";
    foreach ($userData as $key => $value) {
        $debugLog[] = "  - $key: " . htmlspecialchars($value);
    }

    // Guardar en sesión
    try {
        SessionManager::setUserData($userData);
        $debugLog[] = "✅ Datos guardados en sesión";
        
        // Verificar que se guardaron correctamente
        $savedData = SessionManager::getUserData();
        $debugLog[] = "🔍 Verificación de datos guardados:";
        if ($savedData) {
            foreach ($savedData as $key => $value) {
                $debugLog[] = "  - $key: " . htmlspecialchars($value);
            }
        } else {
            $debugLog[] = "❌ No se pudieron recuperar datos de sesión";
        }
        
    } catch (Exception $e) {
        $debugLog[] = "❌ Error guardando en sesión: " . $e->getMessage();
        throw new Exception("Error interno al establecer sesión");
    }

    // Mensaje de éxito
    SessionManager::setFlash('success', '¡Bienvenido de vuelta, ' . $user['nombre'] . '!');
    $debugLog[] = "✅ Mensaje flash establecido";

    // LOG DE REDIRECCIÓN
    $debugLog[] = "🔄 Preparando redirección a dashboard.php";
    $debugLog[] = "🎯 Headers a enviar: Location: dashboard.php";

} catch (Exception $e) {
    $debugLog[] = "❌ EXCEPCIÓN CAPTURADA: " . $e->getMessage();
    $debugLog[] = "📍 Archivo: " . $e->getFile();
    $debugLog[] = "🔢 Línea: " . $e->getLine();
    
    SessionManager::setFlash('error', $e->getMessage());
    $redirectTo = 'login.php';
    
    $debugLog[] = "🔄 Redirigiendo a: " . $redirectTo;
} catch (Error $e) {
    $debugLog[] = "💥 ERROR FATAL: " . $e->getMessage();
    $debugLog[] = "📍 Archivo: " . $e->getFile();
    $debugLog[] = "🔢 Línea: " . $e->getLine();
    
    SessionManager::setFlash('error', 'Error interno del sistema');
    $redirectTo = 'login.php';
}

$debugLog[] = "=== FIN DEL PROCESO ===";

// Capturar cualquier output no deseado
$unexpectedOutput = ob_get_clean();

// Mostrar debug
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Login Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4><i class="bi bi-bug"></i> Process Login Debug</h4>
                    </div>
                    <div class="card-body">
                        
                        <!-- Log del proceso -->
                        <div class="mb-4">
                            <h5>📋 Log del Proceso de Login</h5>
                            <div class="bg-dark text-light p-3" style="font-family: monospace; max-height: 400px; overflow-y: auto;">
                                <?php foreach ($debugLog as $logEntry): ?>
                                    <div><?= htmlspecialchars($logEntry) ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Output inesperado -->
                        <?php if (!empty($unexpectedOutput)): ?>
                        <div class="alert alert-warning">
                            <h6>⚠️ Output inesperado detectado:</h6>
                            <pre><?= htmlspecialchars($unexpectedOutput) ?></pre>
                        </div>
                        <?php endif; ?>

                        <!-- Estado de sesión actual -->
                        <div class="mb-4">
                            <h5>🔍 Estado de Sesión Actual</h5>
                            <?php if (SessionManager::isLoggedIn()): ?>
                                <?php $currentUser = SessionManager::getUserData(); ?>
                                <div class="alert alert-success">
                                    <strong>✅ Usuario autenticado:</strong><br>
                                    <?php foreach ($currentUser as $key => $value): ?>
                                        - <?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?><br>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <strong>❌ No hay sesión activa</strong>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Acciones -->
                        <div class="mt-4">
                            <h6>🎯 Próximos pasos:</h6>
                            <?php if (SessionManager::isLoggedIn()): ?>
                                <a href="dashboard.php" class="btn btn-success">Ir al Dashboard</a>
                                <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
                            <?php else: ?>
                                <a href="login-debug-js.php" class="btn btn-primary">Volver al Login Debug</a>
                            <?php endif; ?>
                            
                            <a href="limpiar-sesion.php" class="btn btn-warning">Limpiar Sesión</a>
                        </div>

                        <!-- Información de headers -->
                        <div class="mt-4">
                            <h6>📡 Información de Headers:</h6>
                            <small class="text-muted">
                                Headers sent: <?= headers_sent($file, $line) ? "SÍ (en $file línea $line)" : "NO" ?><br>
                                <?php if (headers_sent()): ?>
                                    ⚠️ Los headers ya fueron enviados, las redirecciones pueden no funcionar.
                                <?php endif; ?>
                            </small>
                        </div>

                        <!-- Simular redirección -->
                        <?php if (SessionManager::isLoggedIn() && !headers_sent()): ?>
                        <div class="mt-4 alert alert-info">
                            <strong>🔄 Simulando redirección automática...</strong>
                            <div class="progress mt-2">
                                <div class="progress-bar" role="progressbar" style="width: 0%" id="redirectProgress"></div>
                            </div>
                            <small>Serás redirigido al dashboard en <span id="countdown">5</span> segundos...</small>
                        </div>
                        
                        <script>
                            let countdown = 5;
                            const countdownElement = document.getElementById('countdown');
                            const progressBar = document.getElementById('redirectProgress');
                            
                            const timer = setInterval(() => {
                                countdown--;
                                countdownElement.textContent = countdown;
                                progressBar.style.width = ((5 - countdown) * 20) + '%';
                                
                                if (countdown <= 0) {
                                    clearInterval(timer);
                                    window.location.href = 'dashboard.php';
                                }
                            }, 1000);
                        </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>