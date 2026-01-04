<?php
/**
 * Gestión de Inventario - Vendedores
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

// Datos de ejemplo para inventario
$inventario = [
    [
        'id' => 1,
        'nombre' => 'Tomates Cherry Orgánicos',
        'sku' => 'TCO-001',
        'categoria' => 'Verduras',
        'stock_actual' => 25,
        'stock_minimo' => 10,
        'stock_maximo' => 100,
        'precio_compra' => 30.00,
        'precio_venta' => 45.50,
        'ubicacion' => 'Almacén A - Estante 1',
        'fecha_vencimiento' => '2025-01-10',
        'proveedor' => 'Granja Verde SA',
        'estado' => 'disponible',
        'movimientos_recientes' => [
            ['fecha' => '2025-01-03', 'tipo' => 'venta', 'cantidad' => 5, 'motivo' => 'Venta #1001'],
            ['fecha' => '2025-01-02', 'tipo' => 'entrada', 'cantidad' => 30, 'motivo' => 'Compra proveedor'],
        ]
    ],
    [
        'id' => 2,
        'nombre' => 'Lechugas Hidropónicas',
        'sku' => 'LH-002',
        'categoria' => 'Verduras',
        'stock_actual' => 18,
        'stock_minimo' => 15,
        'stock_maximo' => 80,
        'precio_compra' => 22.00,
        'precio_venta' => 35.00,
        'ubicacion' => 'Almacén A - Estante 2',
        'fecha_vencimiento' => '2025-01-08',
        'proveedor' => 'Hidropónicos del Norte',
        'estado' => 'disponible',
        'movimientos_recientes' => [
            ['fecha' => '2025-01-02', 'tipo' => 'venta', 'cantidad' => 3, 'motivo' => 'Venta #1002'],
            ['fecha' => '2025-01-01', 'tipo' => 'entrada', 'cantidad' => 25, 'motivo' => 'Compra proveedor'],
        ]
    ],
    [
        'id' => 3,
        'nombre' => 'Zanahorias Baby',
        'sku' => 'ZB-003',
        'categoria' => 'Verduras',
        'stock_actual' => 0,
        'stock_minimo' => 20,
        'stock_maximo' => 150,
        'precio_compra' => 18.00,
        'precio_venta' => 28.75,
        'ubicacion' => 'Almacén B - Estante 1',
        'fecha_vencimiento' => '2025-01-15',
        'proveedor' => 'Productos Frescos Ltda',
        'estado' => 'agotado',
        'movimientos_recientes' => [
            ['fecha' => '2025-01-02', 'tipo' => 'venta', 'cantidad' => 8, 'motivo' => 'Venta #1003'],
            ['fecha' => '2024-12-30', 'tipo' => 'salida', 'cantidad' => 12, 'motivo' => 'Productos vencidos'],
        ]
    ],
    [
        'id' => 4,
        'nombre' => 'Espinacas Frescas',
        'sku' => 'EF-004',
        'categoria' => 'Verduras',
        'stock_actual' => 8,
        'stock_minimo' => 12,
        'stock_maximo' => 60,
        'precio_compra' => 25.00,
        'precio_venta' => 38.50,
        'ubicacion' => 'Almacén A - Estante 3',
        'fecha_vencimiento' => '2025-01-06',
        'proveedor' => 'Granja Verde SA',
        'estado' => 'stock_bajo',
        'movimientos_recientes' => [
            ['fecha' => '2025-01-01', 'tipo' => 'venta', 'cantidad' => 4, 'motivo' => 'Venta #1000'],
            ['fecha' => '2024-12-28', 'tipo' => 'entrada', 'cantidad' => 20, 'motivo' => 'Compra proveedor'],
        ]
    ]
];

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - AgroConecta</title>
    
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

        .stats-card .card-body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 12px 12px 0 0;
        }

        .stats-productos::before { background: #007bff; }
        .stats-valor::before { background: #28a745; }
        .stats-alertas::before { background: #dc3545; }
        .stats-rotacion::before { background: #17a2b8; }

        .inventario-item {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .inventario-item:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .stock-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
            overflow: hidden;
        }

        .stock-progress {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .stock-alto { background-color: #28a745; }
        .stock-medio { background-color: #ffc107; }
        .stock-bajo { background-color: #dc3545; }
        .stock-agotado { background-color: #6c757d; }

        .badge-disponible { background-color: #28a745; }
        .badge-stock_bajo { background-color: #ffc107; color: #000; }
        .badge-agotado { background-color: #dc3545; }
        .badge-vencido { background-color: #6c757d; }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .search-filters {
            background: var(--bg-primary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .movimiento-item {
            border-left: 3px solid var(--primary-color);
            padding-left: 10px;
            margin-bottom: 8px;
        }

        .movimiento-entrada { border-left-color: #28a745; }
        .movimiento-salida { border-left-color: #dc3545; }
        .movimiento-venta { border-left-color: #17a2b8; }

        .precio-margen {
            font-size: 0.8rem;
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
                        <a class="nav-link" href="ventas.php">
                            <i class="fas fa-chart-line me-1"></i>Ventas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pedidos.php">
                            <i class="fas fa-shopping-cart me-1"></i>Pedidos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clientes.php">
                            <i class="fas fa-users me-1"></i>Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="inventario.php">
                            <i class="fas fa-warehouse me-1"></i>Inventario
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
                            <i class="fas fa-warehouse text-primary me-2"></i>
                            Gestión de Inventario
                        </h2>
                        <p class="text-muted mb-0">Controla tu stock y optimiza tu almacén</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2">
                            <i class="fas fa-download me-1"></i>
                            Reporte Inventario
                        </button>
                        <button class="btn btn-success me-2">
                            <i class="fas fa-plus me-1"></i>
                            Entrada Stock
                        </button>
                        <button class="btn btn-primary">
                            <i class="fas fa-barcode me-1"></i>
                            Escanear Código
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-productos">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-boxes fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Productos Únicos</h6>
                                <h3 class="mb-0"><?php echo count($inventario); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-valor">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-dollar-sign fa-2x text-success"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Valor Inventario</h6>
                                <h3 class="mb-0">$<?php echo number_format(array_sum(array_map(fn($i) => $i['stock_actual'] * $i['precio_compra'], $inventario)), 0); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-alertas">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Alertas Stock</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($inventario, fn($i) => $i['estado'] === 'stock_bajo' || $i['estado'] === 'agotado')); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-rotacion">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-sync-alt fa-2x text-info"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Rotación</h6>
                                <h3 class="mb-0">12.5</h3>
                                <small class="text-muted">días promedio</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="row">
            <div class="col-12">
                <div class="search-filters">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Buscar productos..." id="searchInventario">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterCategoria">
                                <option value="">Categorías</option>
                                <option value="Verduras">Verduras</option>
                                <option value="Frutas">Frutas</option>
                                <option value="Granos">Granos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterEstado">
                                <option value="">Estados</option>
                                <option value="disponible">Disponible</option>
                                <option value="stock_bajo">Stock Bajo</option>
                                <option value="agotado">Agotado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterUbicacion">
                                <option value="">Ubicaciones</option>
                                <option value="Almacén A">Almacén A</option>
                                <option value="Almacén B">Almacén B</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" id="filterVencimiento" title="Vencen antes de">
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory List -->
        <div class="row">
            <div class="col-12">
                <div class="card content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Control de Stock
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($inventario as $item): ?>
                            <?php
                            // Calcular porcentaje de stock
                            $porcentaje_stock = $item['stock_maximo'] > 0 ? ($item['stock_actual'] / $item['stock_maximo']) * 100 : 0;
                            $clase_stock = 'stock-alto';
                            if ($item['stock_actual'] == 0) $clase_stock = 'stock-agotado';
                            elseif ($item['stock_actual'] <= $item['stock_minimo']) $clase_stock = 'stock-bajo';
                            elseif ($porcentaje_stock < 50) $clase_stock = 'stock-medio';
                            
                            $margen = (($item['precio_venta'] - $item['precio_compra']) / $item['precio_compra']) * 100;
                            ?>
                            <div class="inventario-item p-4" data-nombre="<?php echo strtolower($item['nombre']); ?>" data-categoria="<?php echo $item['categoria']; ?>" data-estado="<?php echo $item['estado']; ?>">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="mb-1"><?php echo htmlspecialchars($item['nombre']); ?></h5>
                                                <div class="d-flex align-items-center gap-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-barcode me-1"></i>
                                                        SKU: <?php echo $item['sku']; ?>
                                                    </small>
                                                    <span class="badge badge-<?php echo $item['estado']; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $item['estado'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Stock Bar -->
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small text-muted">Stock Actual: <strong><?php echo $item['stock_actual']; ?></strong> / <?php echo $item['stock_maximo']; ?></span>
                                                <span class="small text-muted"><?php echo number_format($porcentaje_stock, 1); ?>%</span>
                                            </div>
                                            <div class="stock-bar">
                                                <div class="stock-progress <?php echo $clase_stock; ?>" style="width: <?php echo min($porcentaje_stock, 100); ?>%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Ubicación</small>
                                                <strong><?php echo $item['ubicacion']; ?></strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Proveedor</small>
                                                <strong><?php echo $item['proveedor']; ?></strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Vencimiento</small>
                                                <strong class="<?php echo strtotime($item['fecha_vencimiento']) < strtotime('+3 days') ? 'text-danger' : ''; ?>">
                                                    <?php echo date('d/m/Y', strtotime($item['fecha_vencimiento'])); ?>
                                                </strong>
                                            </div>
                                        </div>
                                        
                                        <!-- Últimos Movimientos -->
                                        <div class="mb-2">
                                            <small class="text-muted d-block mb-2"><strong>Últimos Movimientos:</strong></small>
                                            <?php foreach (array_slice($item['movimientos_recientes'], 0, 2) as $movimiento): ?>
                                                <div class="movimiento-item movimiento-<?php echo $movimiento['tipo']; ?>">
                                                    <small>
                                                        <strong><?php echo ucfirst($movimiento['tipo']); ?>:</strong>
                                                        <?php echo $movimiento['cantidad']; ?> unidades
                                                        <span class="text-muted">
                                                            - <?php echo date('d/m', strtotime($movimiento['fecha'])); ?>
                                                            (<?php echo $movimiento['motivo']; ?>)
                                                        </span>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-4">
                                        <div class="text-end">
                                            <!-- Precios y Margen -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">Costo:</small>
                                                    <strong>$<?php echo number_format($item['precio_compra'], 2); ?></strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">Venta:</small>
                                                    <strong class="text-success">$<?php echo number_format($item['precio_venta'], 2); ?></strong>
                                                </div>
                                                <div class="d-flex justify-content-between precio-margen">
                                                    <small>Margen:</small>
                                                    <small class="text-info"><strong><?php echo number_format($margen, 1); ?>%</strong></small>
                                                </div>
                                            </div>
                                            
                                            <!-- Alertas de Stock -->
                                            <?php if ($item['stock_actual'] <= $item['stock_minimo']): ?>
                                                <div class="alert alert-warning py-2 px-3 mb-3" role="alert">
                                                    <small>
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        <?php echo $item['stock_actual'] == 0 ? 'Sin stock' : 'Stock bajo'; ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Botones de Acción -->
                                            <div class="d-grid gap-2">
                                                <button class="btn btn-success btn-sm" onclick="agregarStock(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-plus me-1"></i>
                                                    Agregar Stock
                                                </button>
                                                <button class="btn btn-warning btn-sm" onclick="ajustarStock(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-edit me-1"></i>
                                                    Ajustar
                                                </button>
                                                <button class="btn btn-outline-primary btn-sm" onclick="verMovimientos(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-history me-1"></i>
                                                    Historial
                                                </button>
                                                <button class="btn btn-outline-info btn-sm" onclick="generarQR(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-qrcode me-1"></i>
                                                    QR Code
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajustar Stock -->
    <div class="modal fade" id="modalAjustarStock" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Ajustar Stock
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAjustarStock">
                        <div class="mb-3">
                            <label class="form-label">Tipo de Ajuste</label>
                            <select class="form-select" name="tipo_ajuste">
                                <option value="entrada">Entrada de Stock</option>
                                <option value="salida">Salida de Stock</option>
                                <option value="ajuste">Ajuste de Inventario</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control" name="cantidad" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo</label>
                            <textarea class="form-control" name="motivo" rows="3" placeholder="Describe el motivo del ajuste..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="confirmarAjuste()">
                        <i class="fas fa-save me-1"></i>Confirmar Ajuste
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Funciones de búsqueda y filtrado
        document.getElementById('searchInventario').addEventListener('input', filtrarInventario);
        document.getElementById('filterCategoria').addEventListener('change', filtrarInventario);
        document.getElementById('filterEstado').addEventListener('change', filtrarInventario);

        function filtrarInventario() {
            const busqueda = document.getElementById('searchInventario').value.toLowerCase();
            const categoria = document.getElementById('filterCategoria').value;
            const estado = document.getElementById('filterEstado').value;
            
            const items = document.querySelectorAll('.inventario-item');
            
            items.forEach(item => {
                const nombre = item.dataset.nombre;
                const itemCategoria = item.dataset.categoria;
                const itemEstado = item.dataset.estado;
                
                const matchNombre = nombre.includes(busqueda);
                const matchCategoria = !categoria || itemCategoria === categoria;
                const matchEstado = !estado || itemEstado === estado;
                
                if (matchNombre && matchCategoria && matchEstado) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function limpiarFiltros() {
            document.getElementById('searchInventario').value = '';
            document.getElementById('filterCategoria').value = '';
            document.getElementById('filterEstado').value = '';
            document.getElementById('filterUbicacion').value = '';
            document.getElementById('filterVencimiento').value = '';
            filtrarInventario();
        }

        // Funciones de gestión de inventario
        function agregarStock(id) {
            const cantidad = prompt('¿Cuántas unidades deseas agregar?');
            if (cantidad && !isNaN(cantidad) && cantidad > 0) {
                alert('Se agregaron ' + cantidad + ' unidades al producto #' + id + ' - Funcionalidad en desarrollo');
            }
        }

        function ajustarStock(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalAjustarStock'));
            modal.show();
        }

        function verMovimientos(id) {
            alert('Ver historial completo de movimientos del producto #' + id + ' - Funcionalidad en desarrollo');
        }

        function generarQR(id) {
            alert('Generar código QR para producto #' + id + ' - Funcionalidad en desarrollo');
        }

        function confirmarAjuste() {
            const form = document.getElementById('formAjustarStock');
            const formData = new FormData(form);
            
            alert('Ajuste de stock confirmado - Funcionalidad en desarrollo');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAjustarStock'));
            modal.hide();
            form.reset();
        }

        // Animaciones de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stats-card, .inventario-item');
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