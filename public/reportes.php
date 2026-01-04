<?php
/**
 * Reportes Administrativos - AgroConecta
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
    'tipo' => $_SESSION['user_tipo'] ?? $_SESSION['tipo'] ?? 'admin'
];

// Verificar que sea administrador
if ($user['tipo'] !== 'admin') {
    ob_end_clean();
    header('Location: dashboard.php');
    exit;
}

// Datos de ejemplo para reportes
$metricas = [
    'usuarios_totales' => 1247,
    'usuarios_nuevos_mes' => 89,
    'vendedores_activos' => 156,
    'clientes_activos' => 1091,
    'productos_totales' => 2834,
    'productos_nuevos_mes' => 234,
    'ventas_totales' => 15678.50,
    'ventas_mes' => 2456.75,
    'pedidos_completados' => 892,
    'pedidos_pendientes' => 34,
    'rating_promedio' => 4.7
];

$ventasUltimos30Dias = [
    ['fecha' => '2024-12-01', 'ventas' => 1250.00],
    ['fecha' => '2024-12-02', 'ventas' => 980.50],
    ['fecha' => '2024-12-03', 'ventas' => 1560.75],
    ['fecha' => '2024-12-04', 'ventas' => 2100.25],
    ['fecha' => '2024-12-05', 'ventas' => 1875.00]
];

$topVendedores = [
    ['nombre' => 'Carlos Mendoza', 'ventas' => 15678.50, 'productos' => 45, 'rating' => 4.9],
    ['nombre' => 'María García', 'ventas' => 12456.75, 'productos' => 38, 'rating' => 4.8],
    ['nombre' => 'Roberto Silva', 'ventas' => 10234.25, 'productos' => 32, 'rating' => 4.7],
    ['nombre' => 'Ana López', 'ventas' => 8976.00, 'productos' => 28, 'rating' => 4.6],
    ['nombre' => 'Luis Hernández', 'ventas' => 7865.50, 'productos' => 25, 'rating' => 4.8]
];

$topProductos = [
    ['nombre' => 'Tomates Cherry Orgánicos', 'ventas' => 234, 'ingresos' => 4567.80],
    ['nombre' => 'Lechugas Hidropónicas', 'ventas' => 189, 'ingresos' => 3245.67],
    ['nombre' => 'Aguacates Hass', 'ventas' => 156, 'ingresos' => 5678.90],
    ['nombre' => 'Zanahorias Baby Premium', 'ventas' => 145, 'ingresos' => 2890.45],
    ['nombre' => 'Brócoli Orgánico', 'ventas' => 134, 'ingresos' => 3456.78]
];

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Administrativos - AgroConecta</title>
    
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

        .metric-card {
            background: var(--bg-primary);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .metric-card:hover {
            transform: translateY(-5px);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: white;
        }

        .metric-icon.users { background: linear-gradient(135deg, #007bff, #0056b3); }
        .metric-icon.products { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
        .metric-icon.sales { background: linear-gradient(135deg, #28a745, #20c997); }
        .metric-icon.orders { background: linear-gradient(135deg, #ffc107, #fd7e14); }
        .metric-icon.rating { background: linear-gradient(135deg, #dc3545, #e83e8c); }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .top-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .top-item {
            background: var(--bg-secondary);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            transition: background-color 0.3s ease;
        }

        .top-item:hover {
            background: #e9ecef;
        }

        .rank-badge {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 0.9rem;
        }

        .rank-1 { background: #ffd700; }
        .rank-2 { background: #c0c0c0; }
        .rank-3 { background: #cd7f32; }
        .rank-other { background: var(--text-secondary); }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .filter-card {
            background: var(--bg-primary);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .rating-stars {
            color: #ffc107;
        }

        .export-buttons {
            background: var(--bg-primary);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .trend-positive {
            color: #28a745;
        }

        .trend-negative {
            color: #dc3545;
        }

        .trend-neutral {
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-leaf me-2"></i>
                <strong>AgroConecta Admin</strong>
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
                        <a class="nav-link" href="usuarios.php">
                            <i class="fas fa-users me-1"></i>Usuarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vendedores.php">
                            <i class="fas fa-store me-1"></i>Vendedores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reportes.php">
                            <i class="fas fa-chart-bar me-1"></i>Reportes
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
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            Reportes y Analytics
                        </h2>
                        <p class="text-muted mb-0">Métricas y análisis del rendimiento de la plataforma</p>
                    </div>
                    <div class="export-buttons">
                        <button class="btn btn-outline-primary me-2" onclick="exportarPDF()">
                            <i class="fas fa-file-pdf me-1"></i>
                            Exportar PDF
                        </button>
                        <button class="btn btn-outline-success me-2" onclick="exportarExcel()">
                            <i class="fas fa-file-excel me-1"></i>
                            Exportar Excel
                        </button>
                        <button class="btn btn-primary" onclick="programarReporte()">
                            <i class="fas fa-calendar me-1"></i>
                            Programar Reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label for="dateRange" class="form-label">Período de tiempo</label>
                    <select class="form-select" id="dateRange">
                        <option value="7">Últimos 7 días</option>
                        <option value="30" selected>Últimos 30 días</option>
                        <option value="90">Últimos 3 meses</option>
                        <option value="365">Último año</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="categoryFilter" class="form-label">Categoría</label>
                    <select class="form-select" id="categoryFilter">
                        <option value="">Todas las categorías</option>
                        <option value="verduras">Verduras</option>
                        <option value="frutas">Frutas</option>
                        <option value="granos">Granos</option>
                        <option value="lacteos">Lácteos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="regionFilter" class="form-label">Región</label>
                    <select class="form-select" id="regionFilter">
                        <option value="">Todas las regiones</option>
                        <option value="cdmx">Ciudad de México</option>
                        <option value="jalisco">Jalisco</option>
                        <option value="michoacan">Michoacán</option>
                        <option value="puebla">Puebla</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100" onclick="aplicarFiltros()">
                        <i class="fas fa-filter me-1"></i>
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- KPI Metrics -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="metric-card">
                    <div class="metric-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo number_format($metricas['usuarios_totales']); ?></h3>
                    <p class="text-muted mb-2">Usuarios Totales</p>
                    <small class="trend-positive">
                        <i class="fas fa-arrow-up"></i> +<?php echo $metricas['usuarios_nuevos_mes']; ?> este mes
                    </small>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="metric-card">
                    <div class="metric-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3><?php echo number_format($metricas['productos_totales']); ?></h3>
                    <p class="text-muted mb-2">Productos</p>
                    <small class="trend-positive">
                        <i class="fas fa-arrow-up"></i> +<?php echo $metricas['productos_nuevos_mes']; ?> este mes
                    </small>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="metric-card">
                    <div class="metric-icon sales">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3>$<?php echo number_format($metricas['ventas_totales'], 0); ?></h3>
                    <p class="text-muted mb-2">Ventas Totales</p>
                    <small class="trend-positive">
                        <i class="fas fa-arrow-up"></i> +$<?php echo number_format($metricas['ventas_mes'], 0); ?> este mes
                    </small>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="metric-card">
                    <div class="metric-icon orders">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h3><?php echo number_format($metricas['pedidos_completados']); ?></h3>
                    <p class="text-muted mb-2">Pedidos Completados</p>
                    <small class="trend-neutral">
                        <i class="fas fa-clock"></i> <?php echo $metricas['pedidos_pendientes']; ?> pendientes
                    </small>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="metric-card">
                    <div class="metric-icon rating">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3><?php echo $metricas['rating_promedio']; ?></h3>
                    <p class="text-muted mb-2">Rating Promedio</p>
                    <div class="rating-stars">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?php echo $i <= $metricas['rating_promedio'] ? '' : '-o'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="metric-card">
                    <div class="metric-icon users">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3><?php echo $metricas['vendedores_activos']; ?></h3>
                    <p class="text-muted mb-2">Vendedores Activos</p>
                    <small class="trend-positive">
                        <i class="fas fa-check-circle"></i> <?php echo number_format(($metricas['vendedores_activos'] / ($metricas['vendedores_activos'] + 10)) * 100, 1); ?>% aprobados
                    </small>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Sales Chart -->
            <div class="col-lg-8 mb-4">
                <div class="content-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>
                            <i class="fas fa-chart-line me-2"></i>
                            Tendencia de Ventas
                        </h5>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="chartType" id="daily" checked>
                            <label class="btn btn-outline-primary btn-sm" for="daily">Diario</label>
                            
                            <input type="radio" class="btn-check" name="chartType" id="weekly">
                            <label class="btn btn-outline-primary btn-sm" for="weekly">Semanal</label>
                            
                            <input type="radio" class="btn-check" name="chartType" id="monthly">
                            <label class="btn btn-outline-primary btn-sm" for="monthly">Mensual</label>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Users Growth -->
            <div class="col-lg-4 mb-4">
                <div class="content-card p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-user-plus me-2"></i>
                        Crecimiento de Usuarios
                    </h5>
                    <div class="chart-container">
                        <canvas id="usersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Lists Row -->
        <div class="row mb-4">
            <!-- Top Vendors -->
            <div class="col-lg-4 mb-4">
                <div class="content-card p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-trophy me-2"></i>
                        Top Vendedores
                    </h5>
                    <div class="top-list">
                        <?php foreach ($topVendedores as $index => $vendedor): ?>
                            <div class="top-item d-flex align-items-center">
                                <div class="rank-badge rank-<?php echo $index < 3 ? $index + 1 : 'other'; ?> me-3">
                                    <?php echo $index + 1; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($vendedor['nombre']); ?></h6>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted"><?php echo $vendedor['productos']; ?> productos</small>
                                        <small class="text-success">$<?php echo number_format($vendedor['ventas'], 0); ?></small>
                                    </div>
                                    <div class="rating-stars">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $vendedor['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                        <small class="text-muted ms-1"><?php echo $vendedor['rating']; ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="col-lg-4 mb-4">
                <div class="content-card p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-fire me-2"></i>
                        Productos Más Vendidos
                    </h5>
                    <div class="top-list">
                        <?php foreach ($topProductos as $index => $producto): ?>
                            <div class="top-item d-flex align-items-center">
                                <div class="rank-badge rank-<?php echo $index < 3 ? $index + 1 : 'other'; ?> me-3">
                                    <?php echo $index + 1; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($producto['nombre']); ?></h6>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted"><?php echo $producto['ventas']; ?> vendidos</small>
                                        <small class="text-success">$<?php echo number_format($producto['ingresos'], 2); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Regional Distribution -->
            <div class="col-lg-4 mb-4">
                <div class="content-card p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Distribución Regional
                    </h5>
                    <div class="chart-container">
                        <canvas id="regionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Analytics -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="content-card p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-clock me-2"></i>
                        Actividad por Hora
                    </h5>
                    <div class="chart-container">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="content-card p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-tags me-2"></i>
                        Categorías Populares
                    </h5>
                    <div class="chart-container">
                        <canvas id="categoriesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });

        function initializeCharts() {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['1 Dic', '2 Dic', '3 Dic', '4 Dic', '5 Dic', '6 Dic', '7 Dic'],
                    datasets: [{
                        label: 'Ventas ($)',
                        data: [1250, 980, 1560, 2100, 1875, 2300, 1950],
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });

            // Users Chart
            const usersCtx = document.getElementById('usersChart').getContext('2d');
            new Chart(usersCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Clientes', 'Vendedores', 'Admins'],
                    datasets: [{
                        data: [1091, 156, 5],
                        backgroundColor: ['#007bff', '#28a745', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Regional Chart
            const regionCtx = document.getElementById('regionChart').getContext('2d');
            new Chart(regionCtx, {
                type: 'pie',
                data: {
                    labels: ['CDMX', 'Jalisco', 'Michoacán', 'Puebla', 'Otros'],
                    datasets: [{
                        data: [35, 25, 20, 12, 8],
                        backgroundColor: ['#2E7D32', '#4CAF50', '#66BB6A', '#8BC34A', '#CDDC39']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Hourly Activity Chart
            const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
            new Chart(hourlyCtx, {
                type: 'bar',
                data: {
                    labels: ['6-9', '9-12', '12-15', '15-18', '18-21', '21-24'],
                    datasets: [{
                        label: 'Actividad',
                        data: [45, 89, 156, 203, 178, 67],
                        backgroundColor: '#4CAF50'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Categories Chart
            const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
            new Chart(categoriesCtx, {
                type: 'horizontalBar',
                data: {
                    labels: ['Verduras', 'Frutas', 'Granos', 'Lácteos', 'Otros'],
                    datasets: [{
                        label: 'Productos',
                        data: [890, 456, 234, 123, 89],
                        backgroundColor: ['#2E7D32', '#4CAF50', '#66BB6A', '#8BC34A', '#CDDC39']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y'
                }
            });
        }

        function aplicarFiltros() {
            const dateRange = document.getElementById('dateRange').value;
            const category = document.getElementById('categoryFilter').value;
            const region = document.getElementById('regionFilter').value;
            
            showNotification('Aplicando filtros...', 'info');
            
            // Simular actualización de datos
            setTimeout(() => {
                showNotification('Reportes actualizados', 'success');
                // Aquí se actualizarían los gráficos con nuevos datos
            }, 1500);
        }

        function exportarPDF() {
            showNotification('Generando reporte PDF...', 'info');
            setTimeout(() => {
                showNotification('Reporte PDF descargado', 'success');
            }, 2000);
        }

        function exportarExcel() {
            showNotification('Generando reporte Excel...', 'info');
            setTimeout(() => {
                showNotification('Reporte Excel descargado', 'success');
            }, 2000);
        }

        function programarReporte() {
            showNotification('Abriendo configuración de reportes automáticos...', 'info');
            setTimeout(() => {
                alert('Configuración de reportes programados en desarrollo');
            }, 1000);
        }

        function showNotification(message, type) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : type === 'warning' ? 'exclamation' : 'info'}-circle me-2"></i>
                ${message}
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Chart type change handlers
        document.querySelectorAll('input[name="chartType"]').forEach(radio => {
            radio.addEventListener('change', function() {
                showNotification('Actualizando vista del gráfico...', 'info');
                // Aquí se actualizaría el gráfico según el tipo seleccionado
            });
        });
    </script>
</body>
</html>