<?php
/**
 * Panel de Ventas - Vendedores
 */

// Configuración básica
if (ob_get_level()) ob_end_clean();
ob_start();

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache'); 
header('Expires: 0');

session_start();

// Verificación de autenticación
if (!isset($_SESSION['user_id']) || 
    (!isset($_SESSION['user_tipo']) && !isset($_SESSION['tipo']))) {
    ob_end_clean();
    header('Location: login.php');
    exit;
}

$user = [
    'id' => $_SESSION['user_id'],
    'nombre' => $_SESSION['user_nombre'] ?? $_SESSION['nombre'] ?? 'Usuario Test',
    'correo' => $_SESSION['user_email'] ?? $_SESSION['correo'] ?? 'usuario@test.com',
    'tipo' => $_SESSION['user_tipo'] ?? $_SESSION['tipo'] ?? 'cliente'
];

// Verificar que sea vendedor
if ($user['tipo'] !== 'vendedor') {
    ob_end_clean();
    header('Location: dashboard.php');
    exit;
}

// Datos de ejemplo para ventas
$ventasRecientes = [
    [
        'id' => 1001,
        'cliente' => 'María González',
        'productos' => 'Tomates Cherry x2, Lechugas x1',
        'total' => 126.50,
        'fecha' => '2025-01-03 14:30:00',
        'estado' => 'completada',
        'metodo_pago' => 'tarjeta'
    ],
    [
        'id' => 1002,
        'cliente' => 'Carlos Ruiz',
        'productos' => 'Zanahorias Baby x3',
        'total' => 86.25,
        'fecha' => '2025-01-02 16:45:00',
        'estado' => 'pendiente',
        'metodo_pago' => 'efectivo'
    ],
    [
        'id' => 1003,
        'cliente' => 'Ana Martínez',
        'productos' => 'Lechugas x4, Tomates x1',
        'total' => 185.50,
        'fecha' => '2025-01-02 09:15:00',
        'estado' => 'completada',
        'metodo_pago' => 'transferencia'
    ]
];

// Estadísticas de ventas
$estadisticas = [
    'ventas_hoy' => 2,
    'ingresos_hoy' => 271.75,
    'ventas_semana' => 8,
    'ingresos_semana' => 1250.00,
    'ventas_mes' => 25,
    'ingresos_mes' => 4500.00,
    'productos_vendidos' => 45
];

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Ventas - AgroConecta</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
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

        body {
            background-color: var(--bg-secondary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .custom-navbar {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .content-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stats-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .stats-card .card-body {
            position: relative;
        }

        .stats-card .card-body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 12px 12px 0 0;
        }

        .stats-ventas::before { background: #28a745; }
        .stats-ingresos::before { background: #17a2b8; }
        .stats-productos::before { background: #ffc107; }
        .stats-promedio::before { background: #6f42c1; }

        .venta-item {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .venta-item:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateX(5px);
        }

        .badge-completada { background-color: #28a745; }
        .badge-pendiente { background-color: #ffc107; color: #000; }
        .badge-cancelada { background-color: #dc3545; }

        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-leaf me-2"></i>
                <strong>AgroConecta</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="productos.php">
                            <i class="fas fa-box me-1"></i>Productos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="ventas.php">
                            <i class="fas fa-chart-line me-1"></i>Ventas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pedidos.php">
                            <i class="fas fa-shopping-cart me-1"></i>Pedidos
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
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
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2>
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Panel de Ventas
                        </h2>
                        <p class="text-muted mb-0">Monitorea tus ventas y rendimiento</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2">
                            <i class="fas fa-download me-1"></i>
                            Exportar Reporte
                        </button>
                        <button class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Nueva Venta
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-ventas">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-shopping-cart fa-2x text-success"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Ventas Hoy</h6>
                                <h3 class="mb-0"><?php echo $estadisticas['ventas_hoy']; ?></h3>
                                <small class="text-success">+12% vs ayer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-ingresos">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-dollar-sign fa-2x text-info"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Ingresos Hoy</h6>
                                <h3 class="mb-0">$<?php echo number_format($estadisticas['ingresos_hoy'], 2); ?></h3>
                                <small class="text-success">+8% vs ayer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-productos">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-box fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Productos Vendidos</h6>
                                <h3 class="mb-0"><?php echo $estadisticas['productos_vendidos']; ?></h3>
                                <small class="text-muted">Este mes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-promedio">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-chart-bar fa-2x text-purple"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Venta Promedio</h6>
                                <h3 class="mb-0">$<?php echo number_format($estadisticas['ingresos_mes'] / $estadisticas['ventas_mes'], 2); ?></h3>
                                <small class="text-muted">Por venta</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Recent Sales -->
        <div class="row">
            <!-- Sales Chart -->
            <div class="col-lg-8 mb-4">
                <div class="card content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-area me-2"></i>
                            Ventas de los Últimos 7 Días
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="col-lg-4 mb-4">
                <div class="card content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Resumen Rápido
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <h4 class="text-primary"><?php echo $estadisticas['ventas_semana']; ?></h4>
                                <small class="text-muted">Ventas Semana</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success">$<?php echo number_format($estadisticas['ingresos_semana'], 0); ?></h4>
                                <small class="text-muted">Ingresos Semana</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <h4 class="text-info"><?php echo $estadisticas['ventas_mes']; ?></h4>
                                <small class="text-muted">Ventas Mes</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-warning">$<?php echo number_format($estadisticas['ingresos_mes'], 0); ?></h4>
                                <small class="text-muted">Ingresos Mes</small>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 75%"></div>
                            </div>
                            <small class="text-muted">Meta mensual: 75%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="row">
            <div class="col-12">
                <div class="card content-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>
                                Ventas Recientes
                            </h5>
                            <a href="#" class="btn btn-outline-primary btn-sm">Ver Todas</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php foreach ($ventasRecientes as $venta): ?>
                            <div class="venta-item p-3 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <h6 class="mb-1">Venta #<?php echo $venta['id']; ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-3">
                                        <strong><?php echo htmlspecialchars($venta['cliente']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $venta['productos']; ?></small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <h5 class="text-success mb-0">$<?php echo number_format($venta['total'], 2); ?></h5>
                                        <small class="text-muted"><?php echo ucfirst($venta['metodo_pago']); ?></small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <span class="badge badge-<?php echo $venta['estado']; ?>">
                                            <?php echo ucfirst($venta['estado']); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button class="btn btn-outline-primary btn-sm me-1" onclick="verDetalle(<?php echo $venta['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm me-1" onclick="imprimirTicket(<?php echo $venta['id']; ?>)">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <?php if ($venta['estado'] === 'pendiente'): ?>
                                            <button class="btn btn-outline-warning btn-sm" onclick="marcarCompletada(<?php echo $venta['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Configurar gráfico de ventas
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Ventas',
                    data: [12, 19, 8, 15, 22, 18, 14],
                    borderColor: '#2E7D32',
                    backgroundColor: 'rgba(46, 125, 50, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Ingresos ($)',
                    data: [450, 680, 320, 580, 820, 750, 620],
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Funciones de gestión de ventas
        function verDetalle(id) {
            alert('Ver detalles de venta #' + id + ' - Funcionalidad en desarrollo');
        }

        function imprimirTicket(id) {
            alert('Imprimir ticket de venta #' + id + ' - Funcionalidad en desarrollo');
        }

        function marcarCompletada(id) {
            if (confirm('¿Marcar esta venta como completada?')) {
                alert('Venta #' + id + ' marcada como completada - Funcionalidad en desarrollo');
            }
        }

        // Animaciones de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stats-card, .content-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>