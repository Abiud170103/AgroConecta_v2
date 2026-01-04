<?php
/**
 * Dashboard Sin Controlador - Diagnóstico
 * AgroConecta - Versión que evita usar DashboardController
 */

require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';

// Inicializar sesión
SessionManager::startSecureSession();

// Verificar que el usuario esté autenticado
if (!SessionManager::isLoggedIn()) {
    SessionManager::setFlash('error', 'Debes iniciar sesión para acceder al dashboard');
    header('Location: login.php');
    exit;
}

$user = SessionManager::getUserData();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AgroConecta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-leaf"></i> AgroConecta
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="#"><?php echo htmlspecialchars($user['nombre']); ?></a>
                <a class="nav-link" href="logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="alert alert-success">
            <h4><i class="bi bi-check-circle"></i> Dashboard Cargado Sin Problemas</h4>
            <p>Usuario: <strong><?php echo htmlspecialchars($user['correo']); ?></strong></p>
            <p>Tipo: <strong><?php echo $user['tipo']; ?></strong></p>
        </div>

        <?php if ($user['tipo'] === 'vendedor'): ?>
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cart-check fs-1 me-3"></i>
                                <div>
                                    <h5 class="card-title">Productos</h5>
                                    <p class="card-text display-6">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-bag-check fs-1 me-3"></i>
                                <div>
                                    <h5 class="card-title">Ventas</h5>
                                    <p class="card-text display-6">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock fs-1 me-3"></i>
                                <div>
                                    <h5 class="card-title">Pendientes</h5>
                                    <p class="card-text display-6">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cash-coin fs-1 me-3"></i>
                                <div>
                                    <h5 class="card-title">Ingresos</h5>
                                    <p class="card-text display-6">$0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-graph-up"></i> Panel de Vendedor</h5>
                        </div>
                        <div class="card-body">
                            <p>Bienvenido al panel de vendedor. Aquí puedes gestionar tus productos y ventas.</p>
                            <div class="btn-group" role="group">
                                <button class="btn btn-primary">Mis Productos</button>
                                <button class="btn btn-success">Nueva Venta</button>
                                <button class="btn btn-info">Ver Reportes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($user['tipo'] === 'cliente'): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-shop"></i> Panel de Cliente</h5>
                        </div>
                        <div class="card-body">
                            <p>Bienvenido al panel de cliente. Explora productos y realiza compras.</p>
                            <div class="btn-group" role="group">
                                <button class="btn btn-success">Ver Catálogo</button>
                                <button class="btn btn-primary">Mi Carrito</button>
                                <button class="btn btn-info">Mis Pedidos</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-heart"></i> Productos Favoritos</h5>
                        </div>
                        <div class="card-body">
                            <p>No tienes productos favoritos aún.</p>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($user['tipo'] === 'admin'): ?>
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people fs-1 me-3"></i>
                                <div>
                                    <h5 class="card-title">Usuarios</h5>
                                    <p class="card-text display-6">5</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-box fs-1 me-3"></i>
                                <div>
                                    <h5 class="card-title">Productos</h5>
                                    <p class="card-text display-6">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-gear"></i> Panel de Administración</h5>
                        </div>
                        <div class="card-body">
                            <p>Panel de administración del sistema AgroConecta.</p>
                            <div class="btn-group" role="group">
                                <button class="btn btn-dark">Gestión Usuarios</button>
                                <button class="btn btn-secondary">Sistema</button>
                                <button class="btn btn-warning">Reportes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

        <div class="mt-4">
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle"></i> Información de Diagnóstico</h6>
                <p><strong>Este dashboard NO usa DashboardController</strong> - Si funciona sin problemas, 
                el issue está en el controlador o en su interacción con las vistas.</p>
                <hr>
                <p class="mb-0">
                    <a href="dashboard.php" class="btn btn-danger btn-sm">Probar Dashboard Original</a>
                    <a href="diagnostico-bucle.php" class="btn btn-warning btn-sm">Diagnóstico Completo</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>