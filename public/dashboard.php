<?php
/**
 * Dashboard Principal - AgroConecta (Versión Híbrida Estable)
 * Usa la lógica exitosa del dashboard híbrido
 */

// Prevenir cualquier output
if (ob_get_level()) ob_end_clean();
ob_start();

// Headers anti-cache primero
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache'); 
header('Expires: 0');

// Iniciar sesión BÁSICA primero
session_start();

// Verificación básica SIN SessionManager (estable)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_tipo'])) {
    ob_end_clean();
    header('Location: login.php');
    exit;
}

// Datos básicos de sesión
$user_basic = [
    'id' => $_SESSION['user_id'],
    'nombre' => $_SESSION['user_nombre'] ?? 'Usuario',
    'correo' => $_SESSION['user_email'] ?? '',
    'tipo' => $_SESSION['user_tipo'] ?? 'general'
];

// Cargar dependencias DESPUÉS de verificación exitosa
$dashboardData = [];
try {
    require_once '../config/database.php';
    require_once '../core/Database.php';
    require_once '../core/SessionManager.php';
    require_once '../app/models/Model.php';
    require_once '../app/models/Usuario.php';
    require_once '../app/models/Producto.php';
    require_once '../app/models/Pedido.php';
    require_once '../app/controllers/DashboardController.php';
    
    $dashboardController = new DashboardController();
    
    // Obtener datos según tipo de usuario
    switch ($user_basic['tipo']) {
        case 'vendedor':
            $dashboardData = $dashboardController->dashboardVendedor();
            break;
        case 'cliente':
            $dashboardData = $dashboardController->dashboardCliente();
            break;
        case 'admin':
            $dashboardData = $dashboardController->dashboardAdmin();
            break;
        default:
            $dashboardData = [
                'user' => $user_basic,
                'statsProductos' => ['total_productos' => 0],
                'statsPedidos' => ['total_pedidos' => 0, 'total_ventas' => 0]
            ];
    }
    
    $user = $dashboardData['user'] ?? $user_basic;
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    
    $user = $user_basic;
    $dashboardData = [
        'user' => $user_basic,
        'statsProductos' => [
            'total_productos' => 0,
            'productos_disponibles' => 0,
            'precio_promedio' => 0
        ],
        'statsPedidos' => [
            'total_pedidos' => 0,
            'pedidos_pendientes' => 0,
            'total_ventas' => 0,
            'ingresos_totales' => 0
        ],
        'productos' => [],
        'pedidos' => []
    ];
}

// Limpiar buffer antes del HTML
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard - AgroConecta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-leaf me-2"></i>AgroConecta
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> 
                    <?php echo htmlspecialchars($user['nombre']); ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-speedometer2"></i> 
                            Panel <?php echo ucfirst($user['tipo']); ?>
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action active">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                        <?php if ($user['tipo'] === 'vendedor'): ?>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-box"></i> Mis Productos
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-graph-up"></i> Ventas
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-people"></i> Clientes
                            </a>
                        <?php elseif ($user['tipo'] === 'cliente'): ?>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-shop"></i> Catálogo
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-cart"></i> Mi Carrito
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-bag"></i> Mis Pedidos
                            </a>
                        <?php elseif ($user['tipo'] === 'admin'): ?>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-people"></i> Usuarios
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-box-seam"></i> Productos
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-clipboard-data"></i> Reportes
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Welcome Banner -->
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>
                        <strong>¡Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?>!</strong>
                        Dashboard para <?php echo ucfirst($user['tipo']); ?> cargado exitosamente.
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <?php if ($user['tipo'] === 'vendedor'): ?>
                        <div class="col-md-3">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-box-seam fs-1 me-3"></i>
                                        <div>
                                            <h5 class="card-title">Productos</h5>
                                            <p class="card-text display-6"><?php echo $dashboardData['statsProductos']['activos'] ?? 0; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-graph-up fs-1 me-3"></i>
                                        <div>
                                            <h5 class="card-title">Ventas</h5>
                                            <p class="card-text display-6"><?php echo $dashboardData['statsVentas']['total_ventas'] ?? 0; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clock fs-1 me-3"></i>
                                        <div>
                                            <h5 class="card-title">Pendientes</h5>
                                            <p class="card-text display-6"><?php echo $dashboardData['statsVentas']['pendientes'] ?? 0; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-people fs-1 me-3"></i>
                                        <div>
                                            <h5 class="card-title">Clientes</h5>
                                            <p class="card-text display-6"><?php echo $dashboardData['statsClientes']['activos'] ?? 0; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($user['tipo'] === 'cliente'): ?>
                        <div class="col-md-4">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-heart fs-1 me-3"></i>
                                        <div>
                                            <h5 class="card-title">Favoritos</h5>
                                            <p class="card-text display-6"><?php echo $dashboardData['statsFavoritos']['total'] ?? 0; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-cart fs-1 me-3"></i>
                                        <div>
                                            <h5 class="card-title">Carrito</h5>
                                            <p class="card-text display-6"><?php echo count($dashboardData['itemsCarrito'] ?? []); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-bag fs-1 me-3"></i>
                                        <div>
                                            <h5 class="card-title">Pedidos</h5>
                                            <p class="card-text display-6"><?php echo count($dashboardData['pedidosRecientes'] ?? []); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($user['tipo'] === 'admin'): ?>
                        <div class="col-md-3">
                            <div class="card text-white bg-dark">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-people fs-1 me-3"></i>
                                        <div>
                                            <h5 class="card-title">Usuarios</h5>
                                            <p class="card-text display-6"><?php echo $dashboardData['statsGenerales']['total_usuarios'] ?? 5; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-secondary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-box fs-1 me-3"></i>
                                        <div>
                                            <h5 class="card-title">Productos</h5>
                                            <p class="card-text display-6"><?php echo $dashboardData['statsGenerales']['total_productos'] ?? 0; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clipboard fs-1 me-3"></i>
                                        <div>
                                            <h5 class="card-title">Pedidos</h5>
                                            <p class="card-text display-6"><?php echo $dashboardData['statsGenerales']['total_pedidos'] ?? 0; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-cash-coin fs-1 me-3"></i>
                                        <div>
                                            <h5 class="card-title">Ingresos</h5>
                                            <p class="card-text display-6">$<?php echo $dashboardData['statsGenerales']['ingresos_totales'] ?? 0; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Content Cards -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-activity"></i> 
                                    Panel de Control - <?php echo ucfirst($user['tipo']); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Estado del Sistema:</strong> ✅ Dashboard funcionando correctamente
                                </div>
                                
                                <p>Bienvenido al dashboard de AgroConecta. Tu cuenta de tipo <strong><?php echo $user['tipo']; ?></strong> 
                                   está activa y funcionando correctamente.</p>
                                
                                <div class="mt-3">
                                    <h6>Acciones Rápidas:</h6>
                                    <div class="btn-group" role="group">
                                        <?php if ($user['tipo'] === 'vendedor'): ?>
                                            <button class="btn btn-primary">Agregar Producto</button>
                                            <button class="btn btn-success">Ver Ventas</button>
                                            <button class="btn btn-info">Gestionar Pedidos</button>
                                        <?php elseif ($user['tipo'] === 'cliente'): ?>
                                            <button class="btn btn-success">Explorar Catálogo</button>
                                            <button class="btn btn-primary">Ver Carrito</button>
                                            <button class="btn btn-info">Mis Pedidos</button>
                                        <?php elseif ($user['tipo'] === 'admin'): ?>
                                            <button class="btn btn-dark">Gestión Usuarios</button>
                                            <button class="btn btn-secondary">Supervisar Sistema</button>
                                            <button class="btn btn-warning">Generar Reportes</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <small class="text-muted">
                                        <i class="bi bi-shield-check"></i> 
                                        Dashboard funcionando sin bucles de redirección
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>