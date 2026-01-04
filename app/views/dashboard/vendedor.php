<?php
/**
 * Dashboard para Vendedores - AgroConecta
 * Panel de control completo para gestionar productos y ventas
 */

// Verificar autenticación
if (!SessionManager::isLoggedIn() || $user['tipo_usuario'] !== 'vendedor') {
    header('Location: ../../public/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Vendedor - AgroConecta</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #20c997;
            --accent-color: #ffc107;
            --info-color: #17a2b8;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--dark-color) 0%, #34495e  100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }

        .welcome-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .welcome-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon.primary { background: rgba(40, 167, 69, 0.1); color: var(--primary-color); }
        .stat-icon.info { background: rgba(23, 162, 184, 0.1); color: var(--info-color); }
        .stat-icon.warning { background: rgba(255, 193, 7, 0.1); color: var(--warning-color); }
        .stat-icon.success { background: rgba(40, 167, 69, 0.1); color: var(--success-color); }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .recent-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-custom {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table-custom thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }

        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .badge-pendiente { background: rgba(255, 193, 7, 0.2); color: #856404; }
        .badge-confirmado { background: rgba(23, 162, 184, 0.2); color: #0c5460; }
        .badge-enviado { background: rgba(40, 167, 69, 0.2); color: #155724; }
        .badge-disponible { background: rgba(40, 167, 69, 0.2); color: #155724; }
        .badge-agotado { background: rgba(220, 53, 69, 0.2); color: #721c24; }

        .quick-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .action-btn {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 100%;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="../../public/index.php" class="sidebar-brand">
                <span>🌱</span>
                AgroConecta
            </a>
            <div class="mt-2">
                <small class="text-light opacity-75">Panel Vendedor</small>
                <div class="fw-bold"><?= htmlspecialchars($user['nombre'] . ' ' . $user['apellido']) ?></div>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <a href="#" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="mis-productos.php" class="nav-link">
                <i class="fas fa-seedling"></i>
                Mis Productos
            </a>
            <a href="pedidos.php" class="nav-link">
                <i class="fas fa-shopping-bag"></i>
                Pedidos Recibidos
            </a>
            <a href="../../public/profile.php" class="nav-link">
                <i class="fas fa-user"></i>
                Mi Perfil
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                Reportes
            </a>
            <a href="../../public/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Header -->
        <div class="welcome-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">¡Bienvenido, <?= htmlspecialchars($user['nombre']) ?>! 👋</h1>
                    <p class="mb-0 opacity-90">
                        Aquí tienes un resumen de tu tienda y actividad reciente
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-white-50">
                        <?= date('d/m/Y') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (SessionManager::hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= SessionManager::getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="nuevo-producto.php" class="action-btn">
                <i class="fas fa-plus"></i>
                Agregar Producto
            </a>
            <a href="pedidos.php?estado=pendiente" class="action-btn">
                <i class="fas fa-clock"></i>
                Ver Pedidos Pendientes
            </a>
            <a href="catalogo.php" class="action-btn">
                <i class="fas fa-search"></i>
                Ver Catálogo
            </a>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-seedling"></i>
                </div>
                <div class="stat-value"><?= $statsProductos['total_productos'] ?></div>
                <div class="stat-label">Total Productos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?= $statsProductos['productos_disponibles'] ?></div>
                <div class="stat-label">Disponibles</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value"><?= $statsProductos['productos_agotados'] ?></div>
                <div class="stat-label">Agotados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">$<?= number_format($statsPedidos['ingresos_totales'], 0) ?></div>
                <div class="stat-label">Ingresos Totales</div>
            </div>
        </div>

        <div class="row">
            <!-- Sales Chart -->
            <div class="col-lg-8">
                <div class="chart-container">
                    <h5 class="section-title">
                        <i class="fas fa-chart-line text-primary"></i>
                        Ventas de los Últimos 7 Días
                    </h5>
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- Pedidos Pendientes -->
            <div class="col-lg-4">
                <div class="recent-section">
                    <h5 class="section-title">
                        <i class="fas fa-clock text-warning"></i>
                        Pedidos Pendientes
                        <span class="badge bg-warning text-dark ms-2"><?= count($pedidosPendientes) ?></span>
                    </h5>
                    
                    <?php if (empty($pedidosPendientes)): ?>
                        <div class="no-data">
                            <i class="fas fa-check-circle"></i>
                            <p>¡Excelente! No hay pedidos pendientes</p>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($pedidosPendientes, 0, 5) as $pedido): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($pedido['nombre_cliente']) ?></div>
                                    <small class="text-muted">$<?= number_format($pedido['total'], 2) ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="badge badge-pendiente">Pendiente</div>
                                    <small class="d-block text-muted"><?= date('d/m', strtotime($pedido['fecha_creacion'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($pedidosPendientes) > 5): ?>
                            <div class="text-center mt-3">
                                <a href="pedidos.php?estado=pendiente" class="btn btn-outline-primary btn-sm">
                                    Ver todos (<?= count($pedidosPendientes) ?>)
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Products and Orders -->
        <div class="row">
            <!-- Productos Recientes -->
            <div class="col-lg-6">
                <div class="recent-section">
                    <h5 class="section-title">
                        <i class="fas fa-seedling text-success"></i>
                        Productos Recientes
                    </h5>
                    
                    <?php if (empty($productosRecientes)): ?>
                        <div class="no-data">
                            <i class="fas fa-plus-circle"></i>
                            <p>Aún no tienes productos</p>
                            <a href="nuevo-producto.php" class="action-btn">
                                <i class="fas fa-plus"></i>
                                Agregar Primer Producto
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <tbody>
                                    <?php foreach ($productosRecientes as $producto): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($producto['nombre']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($producto['categoria']) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-bold">$<?= number_format($producto['precio'], 2) ?></div>
                                                <small class="text-muted">por <?= htmlspecialchars($producto['unidad_medida']) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($producto['stock_disponible'] > 0): ?>
                                                    <span class="badge badge-disponible">
                                                        <?= $producto['stock_disponible'] ?> disponibles
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-agotado">Agotado</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="mis-productos.php" class="btn btn-outline-primary">
                                Ver Todos los Productos
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pedidos Recientes -->
            <div class="col-lg-6">
                <div class="recent-section">
                    <h5 class="section-title">
                        <i class="fas fa-shopping-bag text-info"></i>
                        Pedidos Recientes
                    </h5>
                    
                    <?php if (empty($pedidosRecientes)): ?>
                        <div class="no-data">
                            <i class="fas fa-shopping-bag"></i>
                            <p>Aún no tienes pedidos</p>
                            <small class="text-muted">Los pedidos aparecerán aquí cuando los clientes compren tus productos</small>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <tbody>
                                    <?php foreach ($pedidosRecientes as $pedido): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($pedido['nombre_cliente'] . ' ' . $pedido['apellido_cliente']) ?></div>
                                                <small class="text-muted">Pedido #<?= $pedido['id_pedido'] ?></small>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-bold">$<?= number_format($pedido['total'], 2) ?></div>
                                                <small class="text-muted"><?= date('d/m/Y', strtotime($pedido['fecha_creacion'])) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= strtolower($pedido['estado']) ?>">
                                                    <?= ucfirst($pedido['estado']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="pedidos.php" class="btn btn-outline-primary">
                                Ver Todos los Pedidos
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const ventasData = <?= json_encode($ventasPorDia) ?>;
        
        // Preparar datos para el gráfico
        const labels = [];
        const data = [];
        
        // Llenar con los últimos 7 días
        for (let i = 6; i >= 0; i--) {
            const fecha = new Date();
            fecha.setDate(fecha.getDate() - i);
            const fechaStr = fecha.toISOString().split('T')[0];
            
            labels.push(fecha.toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric' }));
            
            // Buscar si hay datos para esta fecha
            const ventaDelDia = ventasData.find(v => v.fecha === fechaStr);
            data.push(ventaDelDia ? parseFloat(ventaDelDia.ventas_dia) : 0);
        }
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ventas ($)',
                    data: data,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 8
                    }
                }
            }
        });

        // Auto-refresh dashboard every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 5 * 60 * 1000);

        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }
    </script>
</body>
</html>