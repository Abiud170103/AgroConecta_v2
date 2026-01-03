<?php
require_once '../core/SessionManager.php';
require_once '../core/Database.php';
SessionManager::startSecureSession();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado AgroConecta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-success text-center">
            <h1>🌱 AgroConecta - Sistema Completo Operativo</h1>
            <p class="lead">Versión completa con todas las funcionalidades</p>
            <div class="mt-3">
                <strong>Hora del servidor:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
                <strong>Usuario logueado:</strong> <?php echo SessionManager::isLoggedIn() ? '✅ Sí' : '❌ No'; ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>🏠 Acceso Principal</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-primary">📱 Aplicación Principal</a>
                            <a href="login.php" class="btn btn-outline-primary">🔐 Login Directo</a>
                            <a href="register.php" class="btn btn-outline-secondary">📝 Registro Directo</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>🔧 Herramientas Debug</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="debug-login.php" class="btn btn-info">🐛 Debug Login</a>
                            <a href="debug-router.php" class="btn btn-info">🛣️ Debug Router</a>
                            <a href="test-auth.php" class="btn btn-warning">🧪 Test Auth</a>
                            <a href="test-csrf.php" class="btn btn-warning">🛡️ Test CSRF</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>📊 Sistema Utilities</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="../system_check.php" class="btn btn-secondary">⚙️ System Check</a>
                            <a href="../quick_test.php" class="btn btn-secondary">⚡ Quick Test</a>
                            <a href="diagnostico.php" class="btn btn-secondary">🔍 Diagnóstico</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>📋 Estado Core Files</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li>🛡️ SessionManager: <?php echo file_exists('../core/SessionManager.php') ? '✅' : '❌'; ?></li>
                            <li>🗄️ Database: <?php echo file_exists('../core/Database.php') ? '✅' : '❌'; ?></li>
                            <li>⚙️ Middleware: <?php echo file_exists('../core/Middleware.php') ? '✅' : '❌'; ?></li>
                            <li>🛣️ Router: <?php echo file_exists('../core/Router.php') ? '✅' : '❌'; ?></li>
                            <li>🔗 RouteMiddleware: <?php echo file_exists('../core/RouteMiddleware.php') ? '✅' : '❌'; ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>📂 Estado Aplicación</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li>🎮 AuthController: <?php echo file_exists('../app/controllers/AuthController.php') ? '✅' : '❌'; ?></li>
                            <li>👤 Usuario Model: <?php echo file_exists('../app/models/Usuario.php') ? '✅' : '❌'; ?></li>
                            <li>🔧 Config Database: <?php echo file_exists('../config/database.php') ? '✅' : '❌'; ?></li>
                            <li>📊 Routes Config: <?php echo file_exists('../config/agroconecta_routes.php') ? '✅' : '❌'; ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info mt-4">
            <h6>🚀 Funcionalidades Implementadas:</h6>
            <div class="row">
                <div class="col-md-6">
                    <ul class="mb-0">
                        <li>✅ Sistema de Sesiones Seguras con regeneración</li>
                        <li>✅ Middleware de Autenticación y Autorización</li>
                        <li>✅ Base de datos PDO Singleton</li>
                        <li>✅ Router con manejo de rutas</li>
                        <li>✅ Sistema de middleware por rutas</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="mb-0">
                        <li>✅ Protección CSRF</li>
                        <li>✅ Rate Limiting</li>
                        <li>✅ Headers de seguridad</li>
                        <li>✅ Flash messages</li>
                        <li>✅ Control de permisos por roles</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <?php if (SessionManager::isLoggedIn()): ?>
        <div class="alert alert-primary">
            <h6>👤 Usuario Actual:</h6>
            <?php $user = SessionManager::getUserData(); ?>
            <strong>Email:</strong> <?php echo $user['email']; ?><br>
            <strong>Nombre:</strong> <?php echo $user['nombre']; ?><br>
            <strong>Tipo:</strong> <?php echo ucfirst($user['tipo']); ?><br>
            <strong>Login:</strong> <?php echo date('Y-m-d H:i:s', $user['login_time']); ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>