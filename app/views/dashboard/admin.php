<?php
/**
 * Dashboard para Administradores - AgroConecta
 * Panel de control completo para supervisar toda la plataforma
 */

// Verificar autenticación y permisos de admin
if (!SessionManager::isLoggedIn() || $user['tipo'] !== 'admin') {
    header('Location: ../../public/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - AgroConecta</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --accent-color: #fd79a8;
            --info-color: #74b9ff;
            --success-color: #00b894;
            --warning-color: #fdcb6e;
            --danger-color: #e84393;
            --dark-color: #2d3436;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 270px;
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
            position: relative;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }

        .nav-link:hover::before, .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: white;
        }

        .main-content {
            margin-left: 270px;
            padding: 2rem;
            min-height: 100vh;
        }

        .welcome-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
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
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }

        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 5px solid var(--primary-color);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(108, 92, 231, 0.1) 0%, transparent 50%);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-card.users { border-left-color: var(--info-color); }
        .stat-card.products { border-left-color: var(--success-color); }
        .stat-card.orders { border-left-color: var(--warning-color); }
        .stat-card.revenue { border-left-color: var(--accent-color); }

        .stat-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .stat-icon.users { background: rgba(116, 185, 255, 0.1); color: var(--info-color); }
        .stat-icon.products { background: rgba(0, 184, 148, 0.1); color: var(--success-color); }
        .stat-icon.orders { background: rgba(253, 203, 110, 0.1); color: var(--warning-color); }
        .stat-icon.revenue { background: rgba(253, 121, 168, 0.1); color: var(--accent-color); }

        .stat-value {
            font-size: 3rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .trend-up { color: var(--success-color); }
        .trend-down { color: var(--danger-color); }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .activity-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .management-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .management-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

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
            border-radius: 30px;
            padding: 1rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.4);
            color: white;
        }

        .table-admin {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .table-admin thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }

        .table-admin thead th {
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .badge-admin {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .badge-activo { background: rgba(0, 184, 148, 0.2); color: #00695c; }
        .badge-inactivo { background: rgba(220, 53, 69, 0.2); color: #b71c1c; }
        .badge-pendiente { background: rgba(253, 203, 110, 0.2); color: #e65100; }
        .badge-completado { background: rgba(0, 184, 148, 0.2); color: #00695c; }
        .badge-vendedor { background: rgba(116, 185, 255, 0.2); color: #1565c0; }
        .badge-cliente { background: rgba(162, 155, 254, 0.2); color: #4527a0; }
        .badge-admin { background: rgba(253, 121, 168, 0.2); color: #ad1457; }

        .activity-item {
            display: flex;
            align-items: start;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .activity-icon.user { background: rgba(116, 185, 255, 0.1); color: var(--info-color); }
        .activity-icon.product { background: rgba(0, 184, 148, 0.1); color: var(--success-color); }
        .activity-icon.order { background: rgba(253, 203, 110, 0.1); color: var(--warning-color); }

        .alert-admin {
            border-radius: 15px;
            border: none;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .no-data i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-stats {
                grid-template-columns: repeat(2, 1fr);
            }
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
            
            .admin-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="../../public/index.php" class="sidebar-brand">
                <span>👑</span>
                AgroConecta Admin
            </a>
            <div class="mt-2">
                <small class="text-light opacity-75">Panel de Administración</small>
                <div class="fw-bold"><?= htmlspecialchars($user['nombre'] . ' ' . $user['apellido']) ?></div>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <a href="#" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="usuarios.php" class="nav-link">
                <i class="fas fa-users"></i>
                Gestión de Usuarios
            </a>
            <a href="productos.php" class="nav-link">
                <i class="fas fa-seedling"></i>
                Productos
            </a>
            <a href="pedidos.php" class="nav-link">
                <i class="fas fa-shopping-bag"></i>
                Pedidos
            </a>
            <a href="reportes.php" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                Reportes
            </a>
            <a href="configuracion.php" class="nav-link">
                <i class="fas fa-cogs"></i>
                Configuración
            </a>
            <a href="../../public/profile.php" class="nav-link">
                <i class="fas fa-user"></i>
                Mi Perfil
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
                    <h1 class="mb-2">Panel de Administración 👑</h1>
                    <p class="mb-0 opacity-90">
                        Bienvenido <?= htmlspecialchars($user['nombre']) ?>, aquí tienes el control total de AgroConecta
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-white-50">
                        Sistema operativo desde <?= date('d/m/Y') ?>
                    </div>
                    <div class="fw-bold">
                        <i class="fas fa-server me-1"></i>
                        Estado: Activo
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (SessionManager::hasFlash('success')): ?>
            <div class="alert alert-success alert-admin alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= SessionManager::getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- System Alerts -->
        <?php if ($alertasImportantes): ?>
            <?php foreach ($alertasImportantes as $alerta): ?>
                <div class="alert alert-<?= $alerta['tipo'] ?> alert-admin alert-dismissible fade show">
                    <i class="fas fa-<?= $alerta['icono'] ?> me-2"></i>
                    <strong><?= htmlspecialchars($alerta['titulo']) ?></strong>
                    <?= htmlspecialchars($alerta['mensaje']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="usuarios.php?action=new" class="action-btn">
                <i class="fas fa-user-plus"></i>
                Nuevo Usuario
            </a>
            <a href="productos.php?moderar=pending" class="action-btn">
                <i class="fas fa-check-circle"></i>
                Moderar Productos
            </a>
            <a href="reportes.php?tipo=ventas" class="action-btn">
                <i class="fas fa-download"></i>
                Exportar Reportes
            </a>
            <a href="configuracion.php" class="action-btn">
                <i class="fas fa-cogs"></i>
                Configuración
            </a>
        </div>

        <!-- Main Statistics -->
        <div class="admin-stats">
            <div class="stat-card users">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?= number_format($statsGenerales['total_usuarios']) ?></div>
                <div class="stat-label">Usuarios Registrados</div>
                <div class="stat-trend trend-<?= $statsGenerales['tendencia_usuarios'] > 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $statsGenerales['tendencia_usuarios'] > 0 ? 'up' : 'down' ?>"></i>
                    <?= abs($statsGenerales['tendencia_usuarios']) ?>% este mes
                </div>
            </div>
            
            <div class="stat-card products">
                <div class="stat-icon products">
                    <i class="fas fa-seedling"></i>
                </div>
                <div class="stat-value"><?= number_format($statsGenerales['total_productos']) ?></div>
                <div class="stat-label">Productos Activos</div>
                <div class="stat-trend trend-<?= $statsGenerales['tendencia_productos'] > 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $statsGenerales['tendencia_productos'] > 0 ? 'up' : 'down' ?>"></i>
                    <?= abs($statsGenerales['tendencia_productos']) ?>% este mes
                </div>
            </div>
            
            <div class="stat-card orders">
                <div class="stat-icon orders">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-value"><?= number_format($statsGenerales['total_pedidos']) ?></div>
                <div class="stat-label">Pedidos Procesados</div>
                <div class="stat-trend trend-<?= $statsGenerales['tendencia_pedidos'] > 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $statsGenerales['tendencia_pedidos'] > 0 ? 'up' : 'down' ?>"></i>
                    <?= abs($statsGenerales['tendencia_pedidos']) ?>% este mes
                </div>
            </div>
            
            <div class="stat-card revenue">
                <div class="stat-icon revenue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">$<?= number_format($statsGenerales['ingresos_totales'], 0) ?></div>
                <div class="stat-label">Ingresos Totales</div>
                <div class="stat-trend trend-<?= $statsGenerales['tendencia_ingresos'] > 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $statsGenerales['tendencia_ingresos'] > 0 ? 'up' : 'down' ?>"></i>
                    <?= abs($statsGenerales['tendencia_ingresos']) ?>% este mes
                </div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Charts Section -->
            <div class="chart-section">
                <h5 class="section-title">
                    <i class="fas fa-chart-line text-primary"></i>
                    Análisis de Crecimiento
                </h5>
                <canvas id="growthChart" width="400" height="200"></canvas>
            </div>
            
            <!-- Recent Activity -->
            <div class="activity-section">
                <h5 class="section-title">
                    <i class="fas fa-clock text-info"></i>
                    Actividad Reciente
                </h5>
                
                <?php if (empty($actividadReciente)): ?>
                    <div class="no-data">
                        <i class="fas fa-history"></i>
                        <p>No hay actividad reciente</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($actividadReciente as $actividad): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= $actividad['tipo'] ?>">
                                <i class="fas fa-<?= $actividad['icono'] ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?= htmlspecialchars($actividad['titulo']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($actividad['descripcion']) ?></div>
                                <div class="text-muted small">
                                    <i class="fas fa-clock me-1"></i>
                                    <?= $actividad['tiempo_transcurrido'] ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Management Sections -->
        <div class="management-grid">
            <!-- User Management Overview -->
            <div class="management-card">
                <h5 class="section-title">
                    <i class="fas fa-users text-info"></i>
                    Gestión de Usuarios
                </h5>
                
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="fw-bold fs-4"><?= $statsUsuarios['vendedores'] ?></div>
                        <small class="text-muted">Vendedores</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-4"><?= $statsUsuarios['clientes'] ?></div>
                        <small class="text-muted">Clientes</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-4"><?= $statsUsuarios['admins'] ?></div>
                        <small class="text-muted">Admins</small>
                    </div>
                </div>
                
                <?php if (!empty($usuariosRecientes)): ?>
                    <h6 class="fw-bold mb-2">Registros Recientes:</h6>
                    <?php foreach (array_slice($usuariosRecientes, 0, 3) as $usuario): ?>
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($usuario['email']) ?></small>
                            </div>
                            <span class="badge badge-<?= strtolower($usuario['tipo_usuario']) ?>">
                                <?= ucfirst($usuario['tipo_usuario']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="usuarios.php" class="btn btn-outline-primary">
                        Ver Todos los Usuarios
                    </a>
                </div>
            </div>

            <!-- Products Management Overview -->
            <div class="management-card">
                <h5 class="section-title">
                    <i class="fas fa-seedling text-success"></i>
                    Gestión de Productos
                </h5>
                
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="fw-bold fs-4"><?= $statsProductos['activos'] ?></div>
                        <small class="text-muted">Activos</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-4"><?= $statsProductos['pendientes'] ?></div>
                        <small class="text-muted">Pendientes</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-4"><?= $statsProductos['agotados'] ?></div>
                        <small class="text-muted">Agotados</small>
                    </div>
                </div>

                <h6 class="fw-bold mb-2">Categorías Populares:</h6>
                <?php foreach (array_slice($categoriasPopulares, 0, 3) as $categoria): ?>
                    <div class="d-flex justify-content-between py-1">
                        <span><?= htmlspecialchars($categoria['categoria']) ?></span>
                        <span class="fw-bold"><?= $categoria['cantidad'] ?></span>
                    </div>
                <?php endforeach; ?>
                
                <div class="text-center mt-3">
                    <a href="productos.php" class="btn btn-outline-success">
                        Gestionar Productos
                    </a>
                </div>
            </div>

            <!-- Orders Management Overview -->
            <div class="management-card">
                <h5 class="section-title">
                    <i class="fas fa-shopping-bag text-warning"></i>
                    Gestión de Pedidos
                </h5>
                
                <div class="row text-center mb-3">
                    <div class="col-3">
                        <div class="fw-bold fs-5"><?= $statsPedidos['pendientes'] ?></div>
                        <small class="text-muted">Pendientes</small>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold fs-5"><?= $statsPedidos['confirmados'] ?></div>
                        <small class="text-muted">Confirmados</small>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold fs-5"><?= $statsPedidos['enviados'] ?></div>
                        <small class="text-muted">Enviados</small>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold fs-5"><?= $statsPedidos['completados'] ?></div>
                        <small class="text-muted">Completados</small>
                    </div>
                </div>

                <?php if (!empty($pedidosRecientes)): ?>
                    <h6 class="fw-bold mb-2">Pedidos Recientes:</h6>
                    <?php foreach (array_slice($pedidosRecientes, 0, 3) as $pedido): ?>
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <div>
                                <div class="fw-bold">Pedido #<?= $pedido['id_pedido'] ?></div>
                                <small class="text-muted">$<?= number_format($pedido['total'], 2) ?></small>
                            </div>
                            <span class="badge badge-<?= strtolower($pedido['estado']) ?>">
                                <?= ucfirst($pedido['estado']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="pedidos.php" class="btn btn-outline-warning">
                        Ver Todos los Pedidos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Growth Chart
        const ctx = document.getElementById('growthChart').getContext('2d');
        const crecimientoData = <?= json_encode($datosGraficoCrecimiento) ?>;
        
        // Preparar datos para el gráfico
        const labels = [];
        const usuariosData = [];
        const productosData = [];
        const pedidosData = [];
        
        // Procesar datos de los últimos 30 días
        for (let i = 29; i >= 0; i--) {
            const fecha = new Date();
            fecha.setDate(fecha.getDate() - i);
            const fechaStr = fecha.toISOString().split('T')[0];
            
            labels.push(fecha.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' }));
            
            // Buscar datos para esta fecha
            const dataDelDia = crecimientoData.find(d => d.fecha === fechaStr);
            usuariosData.push(dataDelDia ? parseInt(dataDelDia.nuevos_usuarios) : 0);
            productosData.push(dataDelDia ? parseInt(dataDelDia.nuevos_productos) : 0);
            pedidosData.push(dataDelDia ? parseInt(dataDelDia.nuevos_pedidos) : 0);
        }
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nuevos Usuarios',
                    data: usuariosData,
                    borderColor: '#74b9ff',
                    backgroundColor: 'rgba(116, 185, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }, {
                    label: 'Nuevos Productos',
                    data: productosData,
                    borderColor: '#00b894',
                    backgroundColor: 'rgba(0, 184, 148, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }, {
                    label: 'Nuevos Pedidos',
                    data: pedidosData,
                    borderColor: '#fdcb6e',
                    backgroundColor: 'rgba(253, 203, 110, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
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
                        radius: 3,
                        hoverRadius: 6
                    }
                }
            }
        });

        // Auto-refresh dashboard every 2 minutes
        setTimeout(() => {
            location.reload();
        }, 2 * 60 * 1000);

        // Real-time notifications (WebSocket simulation)
        function checkNotifications() {
            // Simular verificación de notificaciones
            fetch('api/notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.new_notifications > 0) {
                        // Mostrar notificación
                        showAdminNotification(data.message, data.type);
                    }
                })
                .catch(error => console.error('Error checking notifications:', error));
        }

        function showAdminNotification(message, type = 'info') {
            // Sistema de notificaciones para admin
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-admin alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
            notification.innerHTML = `
                <i class="fas fa-bell me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remove después de 5 segundos
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        // Verificar notificaciones cada 30 segundos
        setInterval(checkNotifications, 30000);

        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }
    </script>
</body>
</html>