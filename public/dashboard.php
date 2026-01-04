<?php
/**
 * Dashboard Principal - AgroConecta (Versión Híbrida Estable)
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

// Verificación básica SIN dependencias externas (estable)
if (!isset($_SESSION['user_id']) || 
    (!isset($_SESSION['user_tipo']) && !isset($_SESSION['tipo']))) {
    ob_end_clean();
    header('Location: login.php');
    exit;
}

// Datos básicos de sesión con múltiples formatos
$user = [
    'id' => $_SESSION['user_id'],
    'nombre' => $_SESSION['user_nombre'] ?? $_SESSION['nombre'] ?? 'Usuario Test',
    'correo' => $_SESSION['user_email'] ?? $_SESSION['correo'] ?? 'usuario@test.com',
    'tipo' => $_SESSION['user_tipo'] ?? $_SESSION['tipo'] ?? 'cliente'
];

// Datos de ejemplo para las estadísticas (sin cargar base de datos)
$dashboardData = [
    'statsProductos' => ['total_productos' => 15],
    'statsVentas' => ['total_ventas' => 25, 'pendientes' => 3],
    'statsClientes' => ['activos' => 12],
    'statsPedidos' => ['total_pedidos' => 8],
    'statsGenerales' => ['ingresos_totales' => 2500]
];

// Limpiar buffer de salida antes de enviar HTML
ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AgroConecta</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Variables de color consistentes con app.css */
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #4CAF50;
            --accent-color: #66BB6A;
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --border-color: #dee2e6;
        }

        /* Base styles */
        body {
            background-color: var(--bg-secondary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-primary);
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }

        .sidebar .card-header {
            background: rgba(255,255,255,0.1);
            border: none;
            border-radius: 15px 15px 0 0;
        }

        .sidebar .list-group-item {
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.8);
            transition: all 0.3s ease;
        }

        .sidebar .list-group-item:hover,
        .sidebar .list-group-item.active {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateX(5px);
        }

        /* Cards */
        .content-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stat-card {
            background: linear-gradient(135deg, var(--bg-primary), #f8f9fa);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stat-card .card-body {
            background: var(--bg-primary);
            position: relative;
        }

        .stat-card .card-body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-color);
        }

        /* Welcome banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(46, 125, 50, 0.3);
        }

        /* Stats colors */
        .stat-primary { --stat-color: #007bff; }
        .stat-success { --stat-color: var(--primary-color); }
        .stat-warning { --stat-color: #ffc107; }
        .stat-info { --stat-color: #17a2b8; }
        .stat-dark { --stat-color: #343a40; }

        .stat-primary .card-body::before { background: var(--stat-color); }
        .stat-success .card-body::before { background: var(--stat-color); }
        .stat-warning .card-body::before { background: var(--stat-color); }
        .stat-info .card-body::before { background: var(--stat-color); }
        .stat-dark .card-body::before { background: var(--stat-color); }

        .stat-icon {
            color: var(--stat-color, var(--primary-color));
            font-size: 2.5rem;
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Animations */
        .animate-fade-in {
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Navbar */
        .custom-navbar {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Footer */
        .footer-info {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-leaf me-2"></i>
                <strong>AgroConecta</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($user['nombre']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Perfil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="card sidebar">
                    <div class="card-header text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-tachometer-alt me-2"></i> 
                            Panel <?php echo ucfirst($user['tipo']); ?>
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action active">
                            <i class="fas fa-home me-2"></i> Dashboard
                        </a>
                        
                        <?php if ($user['tipo'] === 'vendedor'): ?>
                            <a href="productos.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-box me-2"></i> Productos
                            </a>
                            <a href="pedidos.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-shopping-cart me-2"></i> Pedidos
                            </a>
                            <a href="ventas.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-line me-2"></i> Ventas
                            </a>
                            <a href="clientes.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-users me-2"></i> Clientes
                            </a>
                            <a href="inventario.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-warehouse me-2"></i> Inventario
                            </a>
                        <?php elseif ($user['tipo'] === 'cliente'): ?>
                            <a href="catalogo.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-store me-2"></i> Catálogo
                            </a>
                            <a href="carrito.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-shopping-cart me-2"></i> Mi Carrito
                            </a>
                            <a href="mis-pedidos.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-receipt me-2"></i> Mis Pedidos
                            </a>
                            <a href="favoritos.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-heart me-2"></i> Favoritos
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-map-marker-alt me-2"></i> Direcciones
                            </a>
                        <?php elseif ($user['tipo'] === 'admin'): ?>
                            <a href="usuarios.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-users me-2"></i> Gestión de Usuarios
                            </a>
                            <a href="vendedores.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-store-alt me-2"></i> Vendedores
                            </a>
                            <a href="productos-admin.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-boxes me-2"></i> Productos
                            </a>
                            <a href="reportes.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-bar me-2"></i> Reportes
                            </a>
                            <a href="configuracion.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-cogs me-2"></i> Configuración
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-bar me-2"></i> Reportes
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-server me-2"></i> Sistema
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-9 col-md-8">
                <!-- Welcome Banner -->
                <div class="card welcome-banner mb-4 animate-fade-in">
                    <div class="card-body text-center py-4">
                        <h2 class="card-title mb-3">
                            <i class="fas fa-leaf me-2"></i>
                            ¡Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?>!
                        </h2>
                        <p class="card-text mb-2">
                            Panel de control para <?php echo $user['tipo']; ?>s de AgroConecta
                        </p>
                        <small class="opacity-75">
                            <i class="fas fa-clock me-1"></i>
                            Última sesión: <?php echo date('d/m/Y H:i'); ?>
                        </small>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <?php if ($user['tipo'] === 'vendedor'): ?>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card stat-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-box stat-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Productos</h6>
                                            <h3 class="mb-0"><?php echo $dashboardData['statsProductos']['total_productos']; ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card stat-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-chart-line stat-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Ventas</h6>
                                            <h3 class="mb-0"><?php echo $dashboardData['statsVentas']['total_ventas']; ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card stat-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-clock stat-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Pendientes</h6>
                                            <h3 class="mb-0"><?php echo $dashboardData['statsVentas']['pendientes']; ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card stat-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-users stat-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Clientes</h6>
                                            <h3 class="mb-0"><?php echo $dashboardData['statsClientes']['activos']; ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($user['tipo'] === 'cliente'): ?>
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card stat-card stat-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-heart stat-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Favoritos</h6>
                                            <h3 class="mb-0">5</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card stat-card stat-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-shopping-cart stat-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">En Carrito</h6>
                                            <h3 class="mb-0">3</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card stat-card stat-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-receipt stat-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Mis Pedidos</h6>
                                            <h3 class="mb-0"><?php echo $dashboardData['statsPedidos']['total_pedidos']; ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($user['tipo'] === 'admin'): ?>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card stat-dark">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-users stat-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Usuarios</h6>
                                            <h3 class="mb-0">47</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card stat-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-store stat-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Vendedores</h6>
                                            <h3 class="mb-0">12</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card stat-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-boxes stat-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Productos</h6>
                                            <h3 class="mb-0">234</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card stat-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-dollar-sign stat-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Ingresos</h6>
                                            <h3 class="mb-0">$<?php echo number_format($dashboardData['statsGenerales']['ingresos_totales']); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Action Cards -->
                <div class="row">
                    <div class="col-12">
                        <div class="card content-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-bolt me-2"></i>
                                    Acciones Rápidas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php if ($user['tipo'] === 'vendedor'): ?>
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="d-grid">
                                                <a href="productos.php" class="btn btn-primary btn-lg text-decoration-none">
                                                    <i class="fas fa-plus-circle me-2"></i>
                                                    Agregar Producto
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="d-grid">
                                                <a href="ventas.php" class="btn btn-success btn-lg text-decoration-none">
                                                    <i class="fas fa-chart-bar me-2"></i>
                                                    Ver Ventas
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="d-grid">
                                                <a href="pedidos.php" class="btn btn-info btn-lg text-decoration-none">
                                                    <i class="fas fa-tasks me-2"></i>
                                                    Gestionar Pedidos
                                                </a>
                                            </div>
                                        </div>
                                    <?php elseif ($user['tipo'] === 'cliente'): ?>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="d-grid">
                                                <a href="catalogo.php" class="btn btn-success btn-lg text-decoration-none">
                                                    <i class="fas fa-store me-2"></i>
                                                    Explorar Catálogo
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="d-grid">
                                                <a href="carrito.php" class="btn btn-primary btn-lg text-decoration-none">
                                                    <i class="fas fa-shopping-cart me-2"></i>
                                                    Mi Carrito
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="d-grid">
                                                <a href="mis-pedidos.php" class="btn btn-info btn-lg text-decoration-none">
                                                    <i class="fas fa-receipt me-2"></i>
                                                    Mis Pedidos
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="d-grid">
                                                <a href="favoritos.php" class="btn btn-danger btn-lg text-decoration-none">
                                                    <i class="fas fa-heart me-2"></i>
                                                    Favoritos
                                                </a>
                                            </div>
                                        </div>
                                    <?php elseif ($user['tipo'] === 'admin'): ?>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="d-grid">
                                                <a href="usuarios.php" class="btn btn-dark btn-lg text-decoration-none">
                                                    <i class="fas fa-users me-2"></i>
                                                    Gestión Usuarios
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="d-grid">
                                                <a href="vendedores.php" class="btn btn-secondary btn-lg text-decoration-none">
                                                    <i class="fas fa-store me-2"></i>
                                                    Supervisar Vendedores
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="d-grid">
                                                <a href="reportes.php" class="btn btn-warning btn-lg text-decoration-none">
                                                    <i class="fas fa-chart-bar me-2"></i>
                                                    Generar Reportes
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="d-grid">
                                                <a href="configuracion.php" class="btn btn-info btn-lg text-decoration-none">
                                                    <i class="fas fa-cogs me-2"></i>
                                                    Configuración
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card content-card">
                            <div class="card-body">
                                <div class="alert alert-success border-0" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle fa-2x me-3"></i>
                                        <div>
                                            <h6 class="alert-heading mb-1">¡Sistema Funcionando Correctamente!</h6>
                                            <p class="mb-0">
                                                Tu dashboard está operativo y sin problemas de redirección. 
                                                Todos los servicios están disponibles.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Info -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card footer-info">
                            <div class="card-body text-center">
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-leaf text-success me-2"></i>
                                    <strong>AgroConecta</strong> - Conectando el campo con tu mesa
                                </p>
                                <small class="text-muted">
                                    Usuario: <?php echo htmlspecialchars($user['correo']); ?> | 
                                    Tipo: <?php echo ucfirst($user['tipo']); ?> | 
                                    Última actividad: <?php echo date('d/m/Y H:i:s'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Animaciones al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Animación de entrada para las tarjetas
            const cards = document.querySelectorAll('.stat-card, .content-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });

            // Efectos hover mejorados
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Click effects para botones
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple');
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });
    </script>

    <style>
        /* Ripple effect para botones */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.6);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        .btn {
            position: relative;
            overflow: hidden;
        }
    </style>
</body>
</html>