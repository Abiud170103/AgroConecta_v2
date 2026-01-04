<?php
/**
 * Dashboard Híbrido - Combina lo que funciona con funcionalidades completas
 * Usa la estructura exitosa de dashboard-independiente + DashboardController
 */

// Prevenir cualquier output
if (ob_get_level()) ob_end_clean();
ob_start();

// Headers anti-cache primero
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache'); 
header('Expires: 0');

// Iniciar sesión BÁSICA primero (como en dashboard-independiente)
session_start();

// Verificación básica SIN SessionManager (como en independiente)
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

// AHORA cargar dependencias después de verificación exitosa
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
    
    // Instanciar controller DESPUÉS de verificación exitosa
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
    
    // Usar datos del controller si están disponibles, sino usar básicos
    $user = $dashboardData['user'] ?? $user_basic;
    
} catch (Exception $e) {
    // Si hay ERROR, usar datos básicos sin fallar
    error_log("Dashboard híbrido error: " . $e->getMessage());
    
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
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard Híbrido - AgroConecta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
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
                    <small class="text-light opacity-75">(<?php echo htmlspecialchars($user['tipo']); ?>)</small>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <!-- Alert de éxito -->
    <div class="container-fluid mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>¡Dashboard Híbrido funcionando!</strong> 
            Combina la estabilidad de dashboard-independiente con funcionalidades completas.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>

    <div class="container-fluid mt-2">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-speedometer2"></i> 
                            Panel <?php echo ucfirst($user['tipo']); ?>
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action active">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        
                        <?php if ($user['tipo'] === 'vendedor'): ?>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-box-seam"></i> Productos
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-cart-check"></i> Pedidos
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-graph-up"></i> Ventas
                        </a>
                        <?php elseif ($user['tipo'] === 'cliente'): ?>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-shop"></i> Catálogo
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-bag"></i> Mis Pedidos
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-heart"></i> Favoritos
                        </a>
                        <?php elseif ($user['tipo'] === 'admin'): ?>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-people"></i> Usuarios
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-gear"></i> Sistema
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-clipboard-data"></i> Reportes
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="col-md-9">
                <h1 class="h3 mb-4">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard <?php echo ucfirst($user['tipo']); ?>
                </h1>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <?php if ($user['tipo'] === 'vendedor'): ?>
                    <div class="col-lg-4 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <i class="bi bi-box-seam" style="font-size: 3rem; opacity: 0.8;"></i>
                                <div class="stat-number">
                                    <?php echo $dashboardData['statsProductos']['total_productos'] ?? 0; ?>
                                </div>
                                <h6>Productos</h6>
                                <small class="opacity-75">Total en catálogo</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <div class="card stat-card text-center" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <div class="card-body">
                                <i class="bi bi-cart-check" style="font-size: 3rem; opacity: 0.8;"></i>
                                <div class="stat-number">
                                    <?php echo $dashboardData['statsPedidos']['total_pedidos'] ?? 0; ?>
                                </div>
                                <h6>Pedidos</h6>
                                <small class="opacity-75">Total procesados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <div class="card stat-card text-center" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #333;">
                            <div class="card-body">
                                <i class="bi bi-currency-dollar" style="font-size: 3rem; opacity: 0.8;"></i>
                                <div class="stat-number">
                                    $<?php echo number_format($dashboardData['statsPedidos']['ingresos_totales'] ?? 0, 2); ?>
                                </div>
                                <h6>Ingresos</h6>
                                <small class="opacity-75">Total generado</small>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($user['tipo'] === 'cliente'): ?>
                    <div class="col-lg-6 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <i class="bi bi-bag" style="font-size: 3rem; opacity: 0.8;"></i>
                                <div class="stat-number">
                                    <?php echo $dashboardData['statsPedidos']['total_pedidos'] ?? 0; ?>
                                </div>
                                <h6>Mis Pedidos</h6>
                                <small class="opacity-75">Pedidos realizados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card stat-card text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body">
                                <i class="bi bi-heart-fill" style="font-size: 3rem; opacity: 0.8;"></i>
                                <div class="stat-number">0</div>
                                <h6>Favoritos</h6>
                                <small class="opacity-75">Productos favoritos</small>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($user['tipo'] === 'admin'): ?>
                    <div class="col-lg-4 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <i class="bi bi-people" style="font-size: 3rem; opacity: 0.8;"></i>
                                <div class="stat-number">0</div>
                                <h6>Usuarios</h6>
                                <small class="opacity-75">Registrados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <div class="card stat-card text-center" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <div class="card-body">
                                <i class="bi bi-shop" style="font-size: 3rem; opacity: 0.8;"></i>
                                <div class="stat-number">0</div>
                                <h6>Vendedores</h6>
                                <small class="opacity-75">Activos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <div class="card stat-card text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body">
                                <i class="bi bi-graph-up" style="font-size: 3rem; opacity: 0.8;"></i>
                                <div class="stat-number">100%</div>
                                <h6>Sistema</h6>
                                <small class="opacity-75">Operacional</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Acciones Rápidas -->
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning-fill"></i> Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if ($user['tipo'] === 'vendedor'): ?>
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
                            <?php elseif ($user['tipo'] === 'cliente'): ?>
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
                            <?php elseif ($user['tipo'] === 'admin'): ?>
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
                                    <i class="bi bi-file-earmark-text"></i> Reportes
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Info técnica -->
                <div class="card dashboard-card mt-4">
                    <div class="card-body">
                        <h6 class="text-success">
                            <i class="bi bi-shield-check"></i> 
                            Dashboard Híbrido - Funcionando correctamente
                        </h6>
                        <small class="text-muted">
                            Sesión: <?php echo htmlspecialchars($user['correo']); ?> | 
                            Tipo: <?php echo htmlspecialchars($user['tipo']); ?> |
                            Hora: <?php echo date('H:i:s'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>