<?php
/**
 * Login Debug - Para identificar errores JavaScript y de redirección
 */

require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';

SessionManager::startSecureSession();

// Si ya está logueado, mostrar info en lugar de redirigir
$isLoggedIn = SessionManager::isLoggedIn();
if ($isLoggedIn) {
    $user = SessionManager::getUserData();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Debug - AgroConecta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4><i class="bi bi-bug"></i> Login Debug - AgroConecta</h4>
                    </div>
                    <div class="card-body">
                        
                        <!-- Estado de Sesión Actual -->
                        <div class="alert <?= $isLoggedIn ? 'alert-success' : 'alert-info' ?>">
                            <h5><i class="bi bi-info-circle"></i> Estado de Sesión</h5>
                            <?php if ($isLoggedIn): ?>
                                <strong>✅ Usuario ya autenticado:</strong><br>
                                - ID: <?= $user['id'] ?? 'N/A' ?><br>
                                - Correo: <?= $user['correo'] ?? 'N/A' ?><br>
                                - Nombre: <?= $user['nombre'] ?? 'N/A' ?><br>
                                - Tipo: <?= $user['tipo'] ?? 'N/A' ?><br>
                                <hr>
                                <a href="dashboard.php" class="btn btn-success btn-sm">Ir al Dashboard</a>
                                <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
                            <?php else: ?>
                                <strong>❌ No hay sesión activa</strong>
                            <?php endif; ?>
                        </div>

                        <!-- Formulario de Login con Debug -->
                        <?php if (!$isLoggedIn): ?>
                        <form id="loginForm" action="process-login-debug.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="vendedor@test.com" required>
                                <small class="form-text text-muted">Pre-cargado: vendedor@test.com</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       value="prueba123" required>
                                <small class="form-text text-muted">Pre-cargado: prueba123</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" id="loginBtn">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                            </button>
                            
                            <div class="mt-3">
                                <h6>Usuarios de prueba:</h6>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="fillUser('vendedor')">Vendedor</button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="fillUser('cliente')">Cliente</button>
                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="fillUser('admin')">Admin</button>
                            </div>
                        </form>
                        <?php endif; ?>

                        <!-- Debug de Errores -->
                        <div class="mt-4">
                            <h5><i class="bi bi-exclamation-triangle"></i> Debug de Errores</h5>
                            <div id="jsErrors" class="alert alert-danger" style="display: none;">
                                <strong>Errores JavaScript:</strong>
                                <ul id="jsErrorList"></ul>
                            </div>
                            
                            <div id="formSubmitLog" class="alert alert-info" style="display: none;">
                                <strong>Log de envío de formulario:</strong>
                                <pre id="submitDetails"></pre>
                            </div>
                        </div>

                        <!-- PHP Errors if any -->
                        <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <strong>Error PHP:</strong> <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <strong>Éxito:</strong> <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        </div>
                        <?php endif; ?>

                        <!-- Enlaces de navegación -->
                        <div class="mt-4">
                            <h6>Enlaces de Debug:</h6>
                            <a href="login-simple.php" class="btn btn-outline-primary btn-sm">Login Simple</a>
                            <a href="limpiar-sesion.php" class="btn btn-outline-warning btn-sm">Limpiar Sesión</a>
                            <a href="dashboard.php" class="btn btn-outline-success btn-sm">Dashboard Directo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Capturar todos los errores JavaScript
        let errorCount = 0;
        const jsErrorList = document.getElementById('jsErrorList');
        const jsErrors = document.getElementById('jsErrors');

        window.addEventListener('error', function(e) {
            errorCount++;
            const li = document.createElement('li');
            li.textContent = `Error ${errorCount}: ${e.message} en ${e.filename}:${e.lineno}`;
            jsErrorList.appendChild(li);
            jsErrors.style.display = 'block';
            console.error('JavaScript Error capturado:', e);
        });

        // Capturar errores de promesas no manejadas
        window.addEventListener('unhandledrejection', function(e) {
            errorCount++;
            const li = document.createElement('li');
            li.textContent = `Promise Error ${errorCount}: ${e.reason}`;
            jsErrorList.appendChild(li);
            jsErrors.style.display = 'block';
            console.error('Unhandled Promise rejection:', e);
        });

        // Debug del envío del formulario
        const form = document.getElementById('loginForm');
        const formSubmitLog = document.getElementById('formSubmitLog');
        const submitDetails = document.getElementById('submitDetails');

        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('Formulario enviado', e);
                
                const formData = new FormData(form);
                let details = 'Enviando formulario:\n';
                details += `Action: ${form.action}\n`;
                details += `Method: ${form.method}\n`;
                details += 'Datos:\n';
                
                for (let [key, value] of formData.entries()) {
                    if (key === 'password') {
                        details += `  ${key}: [OCULTO]\n`;
                    } else {
                        details += `  ${key}: ${value}\n`;
                    }
                }
                
                submitDetails.textContent = details;
                formSubmitLog.style.display = 'block';
                
                // No prevenir el envío por defecto
                return true;
            });
        }

        // Función para llenar datos de usuario
        function fillUser(type) {
            const users = {
                'vendedor': { email: 'vendedor@test.com', password: 'prueba123' },
                'cliente': { email: 'cliente@test.com', password: 'prueba123' },
                'admin': { email: 'admin@test.com', password: 'prueba123' }
            };
            
            if (users[type]) {
                document.getElementById('email').value = users[type].email;
                document.getElementById('password').value = users[type].password;
                console.log(`Datos de ${type} cargados`);
            }
        }

        // Log inicial
        console.log('Login Debug page loaded');
        console.log('Form element:', form);
        console.log('Current URL:', window.location.href);
        console.log('Session state:', <?= json_encode($isLoggedIn) ?>);
        
        <?php if ($isLoggedIn): ?>
        console.log('User data:', <?= json_encode($user) ?>);
        <?php endif; ?>
    </script>
</body>
</html>