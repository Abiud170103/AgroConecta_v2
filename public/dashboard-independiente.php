<?php
/**
 * Dashboard INDEPENDIENTE - Sin ninguna dependencia externa
 * Para evitar CUALQUIER posible redirect
 */

// Prevenir output previo
if (ob_get_level()) ob_end_clean();
ob_start();

// Headers anti-cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Iniciar sesión básica PHP
session_start();

// Verificación básica de sesión SIN SessionManager
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_tipo'])) {
    ob_end_clean();
    header('Location: login.php');
    exit;
}

// Obtener datos básicos de sesión
$user_id = $_SESSION['user_id'];
$user_nombre = $_SESSION['user_nombre'] ?? 'Usuario';
$user_tipo = $_SESSION['user_tipo'] ?? 'general';
$user_email = $_SESSION['user_email'] ?? '';

// Limpiar buffer y enviar
ob_end_clean();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard Independiente - AgroConecta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .dashboard-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .navbar-brand {
            font-size: 1.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-leaf me-2"></i>AgroConecta
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> 
                    <?php echo htmlspecialchars($user_nombre); ?>
                    <small class="text-light opacity-75">(<?php echo htmlspecialchars($user_tipo); ?>)</small>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container-fluid mt-4">
        <!-- Alert de éxito -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>¡Éxito!</strong> Dashboard funcionando perfectamente sin bucles infinitos.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card dashboard-card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-speedometer2"></i> 
                            Panel de Control
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action active">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <?php if ($user_tipo === 'vendedor'): ?>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-box"></i> Productos
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-cart"></i> Pedidos
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-graph-up"></i> Reportes
                        </a>
                        <?php elseif ($user_tipo === 'cliente'): ?>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-shop"></i> Catálogo
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-bag"></i> Mis Pedidos
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-heart"></i> Favoritos
                        </a>
                        <?php elseif ($user_tipo === 'admin'): ?>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-people"></i> Usuarios
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-gear"></i> Sistema
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-clipboard-data"></i> Estadísticas
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info del usuario -->
                <div class="card dashboard-card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-info-circle"></i> Información de Sesión
                        </h6>
                        <p class="card-text small mb-1">
                            <strong>ID:</strong> <?php echo htmlspecialchars($user_id); ?>
                        </p>
                        <p class="card-text small mb-1">
                            <strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?>
                        </p>
                        <p class="card-text small mb-0">
                            <strong>Hora:</strong> <?php echo date('H:i:s'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="col-lg-9">
                <h1 class="h3 mb-4">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard <?php echo ucfirst($user_tipo); ?>
                </h1>

                <!-- Estadísticas Rápidas -->
                <div class="row mb-4">
                    <?php if ($user_tipo === 'vendedor'): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-body">
                                <i class="bi bi-box-seam text-primary" style="font-size: 3rem;"></i>
                                <div class="stat-number text-primary">0</div>
                                <h6 class="card-title">Productos</h6>
                                <p class="text-muted small">Total productos activos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-body">
                                <i class="bi bi-cart-check text-success" style="font-size: 3rem;"></i>
                                <div class="stat-number text-success">0</div>
                                <h6 class="card-title">Ventas</h6>
                                <p class="text-muted small">Pedidos completados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-body">
                                <i class="bi bi-currency-dollar text-warning" style="font-size: 3rem;"></i>
                                <div class="stat-number text-warning">$0</div>
                                <h6 class="card-title">Ingresos</h6>
                                <p class="text-muted small">Total del mes</p>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($user_tipo === 'cliente'): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-body">
                                <i class="bi bi-bag text-primary" style="font-size: 3rem;"></i>
                                <div class="stat-number text-primary">0</div>
                                <h6 class="card-title">Mis Pedidos</h6>
                                <p class="text-muted small">Pedidos realizados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-body">
                                <i class="bi bi-heart-fill text-danger" style="font-size: 3rem;"></i>
                                <div class="stat-number text-danger">0</div>
                                <h6 class="card-title">Favoritos</h6>
                                <p class="text-muted small">Productos guardados</p>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($user_tipo === 'admin'): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-body">
                                <i class="bi bi-people text-info" style="font-size: 3rem;"></i>
                                <div class="stat-number text-info">0</div>
                                <h6 class="card-title">Usuarios</h6>
                                <p class="text-muted small">Total registrados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-body">
                                <i class="bi bi-shop text-success" style="font-size: 3rem;"></i>
                                <div class="stat-number text-success">0</div>
                                <h6 class="card-title">Vendedores</h6>
                                <p class="text-muted small">Activos en plataforma</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card text-center">
                            <div class="card-body">
                                <i class="bi bi-graph-up text-primary" style="font-size: 3rem;"></i>
                                <div class="stat-number text-primary">100%</div>
                                <h6 class="card-title">Sistema</h6>
                                <p class="text-muted small">Estado operacional</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Acciones Rápidas -->
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning"></i> Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if ($user_tipo === 'vendedor'): ?>
                            <div class="col-md-4 mb-2">
                                <button class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle"></i> Agregar Producto
                                </button>
                            </div>
                            <div class="col-md-4 mb-2">
                                <button class="btn btn-success w-100">
                                    <i class="bi bi-graph-up"></i> Ver Reportes
                                </button>
                            </div>
                            <div class="col-md-4 mb-2">
                                <button class="btn btn-info w-100">
                                    <i class="bi bi-cart-check"></i> Gestionar Pedidos
                                </button>
                            </div>
                            <?php elseif ($user_tipo === 'cliente'): ?>
                            <div class="col-md-6 mb-2">
                                <button class="btn btn-success w-100">
                                    <i class="bi bi-search"></i> Explorar Catálogo
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button class="btn btn-primary w-100">
                                    <i class="bi bi-cart"></i> Ver Carrito
                                </button>
                            </div>
                            <?php elseif ($user_tipo === 'admin'): ?>
                            <div class="col-md-4 mb-2">
                                <button class="btn btn-dark w-100">
                                    <i class="bi bi-person-gear"></i> Gestión Usuarios
                                </button>
                            </div>
                            <div class="col-md-4 mb-2">
                                <button class="btn btn-secondary w-100">
                                    <i class="bi bi-gear"></i> Configuración
                                </button>
                            </div>
                            <div class="col-md-4 mb-2">
                                <button class="btn btn-warning w-100">
                                    <i class="bi bi-file-earmark-text"></i> Reportes Generales
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Debug Info -->
                <div class="card dashboard-card mt-4">
                    <div class="card-body">
                        <h6 class="text-muted">
                            <i class="bi bi-shield-check"></i> 
                            Dashboard Independiente - Sin bucles de redirección
                        </h6>
                        <small class="text-muted">
                            Generado el <?php echo date('Y-m-d H:i:s'); ?> | 
                            Usuario: <?php echo htmlspecialchars($user_nombre); ?> (<?php echo htmlspecialchars($user_tipo); ?>)
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>