<?php
/**
 * Gestión de Usuarios - Administradores
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

// Datos de ejemplo para usuarios
$usuarios = [
    [
        'id' => 1,
        'nombre' => 'Carlos Mendoza',
        'correo' => 'carlos@granjamendoza.com',
        'tipo' => 'vendedor',
        'estado' => 'activo',
        'fecha_registro' => '2024-11-15',
        'ultimo_acceso' => '2024-12-30',
        'productos' => 25,
        'ventas' => 156,
        'calificacion' => 4.8,
        'verificado' => true,
        'telefono' => '+52 555 123 4567',
        'ubicacion' => 'Guadalajara, Jalisco'
    ],
    [
        'id' => 2,
        'nombre' => 'María González',
        'correo' => 'maria@cliente.com',
        'tipo' => 'cliente',
        'estado' => 'activo',
        'fecha_registro' => '2024-12-01',
        'ultimo_acceso' => '2024-12-30',
        'productos' => 0,
        'ventas' => 0,
        'pedidos' => 12,
        'gasto_total' => 2450.00,
        'verificado' => true,
        'telefono' => '+52 555 987 6543',
        'ubicacion' => 'Ciudad de México, CDMX'
    ],
    [
        'id' => 3,
        'nombre' => 'Roberto Jiménez',
        'correo' => 'roberto@organicosjim.com',
        'tipo' => 'vendedor',
        'estado' => 'pendiente',
        'fecha_registro' => '2024-12-28',
        'ultimo_acceso' => '2024-12-29',
        'productos' => 0,
        'ventas' => 0,
        'calificacion' => 0,
        'verificado' => false,
        'telefono' => '+52 555 456 7890',
        'ubicacion' => 'Morelia, Michoacán'
    ],
    [
        'id' => 4,
        'nombre' => 'Ana López',
        'correo' => 'ana@cliente.com',
        'tipo' => 'cliente',
        'estado' => 'suspendido',
        'fecha_registro' => '2024-10-20',
        'ultimo_acceso' => '2024-12-15',
        'productos' => 0,
        'ventas' => 0,
        'pedidos' => 3,
        'gasto_total' => 340.00,
        'verificado' => false,
        'telefono' => '+52 555 321 0987',
        'ubicacion' => 'Monterrey, Nuevo León'
    ]
];

// Estadísticas generales
$stats = [
    'total_usuarios' => count($usuarios),
    'vendedores' => count(array_filter($usuarios, function($u) { return $u['tipo'] === 'vendedor'; })),
    'clientes' => count(array_filter($usuarios, function($u) { return $u['tipo'] === 'cliente'; })),
    'pendientes_aprobacion' => count(array_filter($usuarios, function($u) { return $u['estado'] === 'pendiente'; })),
    'usuarios_activos' => count(array_filter($usuarios, function($u) { return $u['estado'] === 'activo'; })),
    'usuarios_suspendidos' => count(array_filter($usuarios, function($u) { return $u['estado'] === 'suspendido'; }))
];

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - AgroConecta Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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

        .stat-card {
            background: var(--bg-primary);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .stat-card.success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .stat-card.warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }

        .stat-card.danger {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
            color: white;
        }

        .user-card {
            border: 1px solid var(--border-color);
            border-radius: 15px;
            background: var(--bg-primary);
            transition: all 0.3s ease;
        }

        .user-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-activo {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-pendiente {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-suspendido {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .type-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .type-vendedor {
            background-color: rgba(46, 125, 50, 0.1);
            color: var(--primary-color);
        }

        .type-cliente {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .type-admin {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .filter-tabs {
            background: var(--bg-primary);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .filter-tab {
            border: none;
            background: transparent;
            padding: 10px 20px;
            border-radius: 25px;
            margin: 0 5px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .filter-tab.active {
            background: var(--primary-color);
            color: white;
        }

        .search-bar {
            background: var(--bg-primary);
            border-radius: 25px;
            padding: 15px 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .bulk-actions {
            background: var(--bg-primary);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .rating-stars {
            color: #ffc107;
        }

        .verification-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .verification-badge.verified {
            background: #28a745;
            color: white;
        }

        .verification-badge.unverified {
            background: #6c757d;
            color: white;
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
                        <a class="nav-link active" href="usuarios.php">
                            <i class="fas fa-users me-1"></i>Usuarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vendedores.php">
                            <i class="fas fa-store me-1"></i>Vendedores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reportes.php">
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
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2>
                            <i class="fas fa-users text-primary me-2"></i>
                            Gestión de Usuarios
                        </h2>
                        <p class="text-muted mb-0">Administra usuarios, vendedores y permisos del sistema</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#bulkModal">
                            <i class="fas fa-tasks me-1"></i>
                            Acciones en Lote
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUserModal">
                            <i class="fas fa-plus me-1"></i>
                            Nuevo Usuario
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="stat-card primary">
                    <h3><?php echo $stats['total_usuarios']; ?></h3>
                    <p class="mb-0">Total Usuarios</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="stat-card success">
                    <h3><?php echo $stats['vendedores']; ?></h3>
                    <p class="mb-0">Vendedores</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="stat-card">
                    <h3><?php echo $stats['clientes']; ?></h3>
                    <p class="mb-0 text-primary">Clientes</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="stat-card warning">
                    <h3><?php echo $stats['pendientes_aprobacion']; ?></h3>
                    <p class="mb-0">Pendientes</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="stat-card">
                    <h3><?php echo $stats['usuarios_activos']; ?></h3>
                    <p class="mb-0 text-success">Activos</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="stat-card danger">
                    <h3><?php echo $stats['usuarios_suspendidos']; ?></h3>
                    <p class="mb-0">Suspendidos</p>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-bar">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-0" placeholder="Buscar usuarios por nombre, email..." id="searchUsers">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select border-0" id="filterTipo">
                        <option value="">Todos los tipos</option>
                        <option value="vendedor">Vendedores</option>
                        <option value="cliente">Clientes</option>
                        <option value="admin">Administradores</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select border-0" id="filterEstado">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="suspendido">Suspendidos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                        <i class="fas fa-times me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <div class="d-flex justify-content-center flex-wrap">
                <button class="filter-tab active" data-filter="todos">
                    <i class="fas fa-list me-1"></i>Todos (<?php echo $stats['total_usuarios']; ?>)
                </button>
                <button class="filter-tab" data-filter="vendedor">
                    <i class="fas fa-store me-1"></i>Vendedores (<?php echo $stats['vendedores']; ?>)
                </button>
                <button class="filter-tab" data-filter="cliente">
                    <i class="fas fa-user me-1"></i>Clientes (<?php echo $stats['clientes']; ?>)
                </button>
                <button class="filter-tab" data-filter="pendiente">
                    <i class="fas fa-clock me-1"></i>Pendientes (<?php echo $stats['pendientes_aprobacion']; ?>)
                </button>
            </div>
        </div>

        <!-- Users Grid -->
        <div class="row">
            <?php foreach ($usuarios as $usuario): ?>
                <div class="col-lg-6 col-xl-4 mb-4 user-item" 
                     data-tipo="<?php echo $usuario['tipo']; ?>"
                     data-estado="<?php echo $usuario['estado']; ?>"
                     data-nombre="<?php echo strtolower($usuario['nombre'] . ' ' . $usuario['correo']); ?>">
                    <div class="card user-card">
                        <div class="card-body p-4 position-relative">
                            <div class="verification-badge <?php echo $usuario['verificado'] ? 'verified' : 'unverified'; ?>" title="<?php echo $usuario['verificado'] ? 'Verificado' : 'Sin verificar'; ?>">
                                <i class="fas fa-<?php echo $usuario['verificado'] ? 'check' : 'times'; ?>"></i>
                            </div>
                            
                            <div class="d-flex align-items-start mb-3">
                                <div class="user-avatar me-3">
                                    <?php echo strtoupper(substr($usuario['nombre'], 0, 2)); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($usuario['nombre']); ?></h5>
                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($usuario['correo']); ?></p>
                                    <div class="d-flex gap-2 mb-2">
                                        <span class="type-badge type-<?php echo $usuario['tipo']; ?>">
                                            <?php echo ucfirst($usuario['tipo']); ?>
                                        </span>
                                        <span class="status-badge status-<?php echo $usuario['estado']; ?>">
                                            <?php echo ucfirst($usuario['estado']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Registro:</small><br>
                                    <small><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Último acceso:</small><br>
                                    <small><?php echo date('d/m/Y', strtotime($usuario['ultimo_acceso'])); ?></small>
                                </div>
                            </div>

                            <?php if ($usuario['tipo'] === 'vendedor'): ?>
                                <div class="mb-3">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <strong class="text-primary"><?php echo $usuario['productos']; ?></strong><br>
                                            <small class="text-muted">Productos</small>
                                        </div>
                                        <div class="col-4">
                                            <strong class="text-success"><?php echo $usuario['ventas']; ?></strong><br>
                                            <small class="text-muted">Ventas</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="rating-stars">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?php echo $i <= $usuario['calificacion'] ? '' : '-o'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted d-block"><?php echo $usuario['calificacion']; ?>/5</small>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($usuario['tipo'] === 'cliente'): ?>
                                <div class="mb-3">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <strong class="text-info"><?php echo $usuario['pedidos'] ?? 0; ?></strong><br>
                                            <small class="text-muted">Pedidos</small>
                                        </div>
                                        <div class="col-6">
                                            <strong class="text-success">$<?php echo number_format($usuario['gasto_total'] ?? 0, 2); ?></strong><br>
                                            <small class="text-muted">Total gastado</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <small class="text-muted d-block">
                                    <i class="fas fa-phone me-1"></i><?php echo $usuario['telefono']; ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo $usuario['ubicacion']; ?>
                                </small>
                            </div>

                            <div class="d-grid gap-2">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-primary btn-sm" onclick="verPerfilUsuario(<?php echo $usuario['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="editarUsuario(<?php echo $usuario['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($usuario['estado'] === 'pendiente'): ?>
                                        <button class="btn btn-outline-success btn-sm" onclick="aprobarUsuario(<?php echo $usuario['id']; ?>)" title="Aprobar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($usuario['estado'] === 'activo'): ?>
                                        <button class="btn btn-outline-warning btn-sm" onclick="suspenderUsuario(<?php echo $usuario['id']; ?>)" title="Suspender">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php elseif ($usuario['estado'] === 'suspendido'): ?>
                                        <button class="btn btn-outline-info btn-sm" onclick="reactivarUsuario(<?php echo $usuario['id']; ?>)" title="Reactivar">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-danger btn-sm" onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- New User Modal -->
    <div class="modal fade" id="newUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>
                        Crear Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newUserForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="newUserName" class="form-label">Nombre completo</label>
                                <input type="text" class="form-control" id="newUserName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="newUserEmail" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="newUserEmail" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="newUserType" class="form-label">Tipo de usuario</label>
                                <select class="form-select" id="newUserType" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="vendedor">Vendedor</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="newUserPhone" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="newUserPhone">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="newUserLocation" class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="newUserLocation" placeholder="Ciudad, Estado">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="newUserPassword" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="newUserPassword" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirmPassword" class="form-label">Confirmar contraseña</label>
                                <input type="password" class="form-control" id="confirmPassword" required>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="autoVerify">
                            <label class="form-check-label" for="autoVerify">
                                Verificar automáticamente
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="crearUsuario()">Crear Usuario</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Filter functionality
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                filtrarUsuarios(filter);
            });
        });

        // Search functionality
        document.getElementById('searchUsers').addEventListener('input', function() {
            filtrarPorBusqueda(this.value);
        });

        document.getElementById('filterTipo').addEventListener('change', function() {
            filtrarPorTipo(this.value);
        });

        document.getElementById('filterEstado').addEventListener('change', function() {
            filtrarPorEstado(this.value);
        });

        function filtrarUsuarios(filter) {
            const items = document.querySelectorAll('.user-item');
            items.forEach(item => {
                if (filter === 'todos' || 
                    item.dataset.tipo === filter || 
                    item.dataset.estado === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function filtrarPorBusqueda(termino) {
            const items = document.querySelectorAll('.user-item');
            const terminoLower = termino.toLowerCase();
            
            items.forEach(item => {
                const nombre = item.dataset.nombre;
                if (nombre.includes(terminoLower)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function filtrarPorTipo(tipo) {
            const items = document.querySelectorAll('.user-item');
            items.forEach(item => {
                if (!tipo || item.dataset.tipo === tipo) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function filtrarPorEstado(estado) {
            const items = document.querySelectorAll('.user-item');
            items.forEach(item => {
                if (!estado || item.dataset.estado === estado) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function limpiarFiltros() {
            document.getElementById('searchUsers').value = '';
            document.getElementById('filterTipo').value = '';
            document.getElementById('filterEstado').value = '';
            
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            document.querySelector('.filter-tab[data-filter="todos"]').classList.add('active');
            
            filtrarUsuarios('todos');
        }

        function verPerfilUsuario(id) {
            showNotification(`Abriendo perfil del usuario #${id}...`, 'info');
            setTimeout(() => {
                alert('Vista de perfil en desarrollo');
            }, 1000);
        }

        function editarUsuario(id) {
            showNotification(`Editando usuario #${id}...`, 'info');
            setTimeout(() => {
                alert('Editor de usuario en desarrollo');
            }, 1000);
        }

        function aprobarUsuario(id) {
            if (confirm('¿Aprobar este usuario?')) {
                showNotification('Usuario aprobado correctamente', 'success');
                // Actualizar UI
                setTimeout(() => location.reload(), 1500);
            }
        }

        function suspenderUsuario(id) {
            if (confirm('¿Suspender este usuario?')) {
                showNotification('Usuario suspendido', 'warning');
                setTimeout(() => location.reload(), 1500);
            }
        }

        function reactivarUsuario(id) {
            if (confirm('¿Reactivar este usuario?')) {
                showNotification('Usuario reactivado correctamente', 'success');
                setTimeout(() => location.reload(), 1500);
            }
        }

        function eliminarUsuario(id) {
            if (confirm('¿ELIMINAR definitivamente este usuario? Esta acción no se puede deshacer.')) {
                showNotification('Usuario eliminado', 'danger');
                setTimeout(() => location.reload(), 1500);
            }
        }

        function crearUsuario() {
            const form = document.getElementById('newUserForm');
            const formData = new FormData(form);
            
            // Validar formulario
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Validar contraseñas coincidan
            const password = document.getElementById('newUserPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                showNotification('Las contraseñas no coinciden', 'warning');
                return;
            }
            
            showNotification('Creando usuario...', 'info');
            
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('newUserModal'));
                modal.hide();
                showNotification('Usuario creado correctamente', 'success');
                
                // Limpiar formulario
                form.reset();
                
                setTimeout(() => location.reload(), 1500);
            }, 1500);
        }

        function showNotification(message, type) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : type === 'danger' ? 'danger' : 'info'} position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : type === 'warning' ? 'exclamation' : type === 'danger' ? 'times' : 'info'}-circle me-2"></i>
                ${message}
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            // Animaciones de entrada
            const cards = document.querySelectorAll('.user-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
</body>
</html>