<?php
/**
 * Mis Pedidos - Clientes
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

// Verificar que sea cliente
if ($user['tipo'] !== 'cliente') {
    ob_end_clean();
    header('Location: dashboard.php');
    exit;
}

// Datos de ejemplo para pedidos
$pedidos = [
    [
        'id' => 'PED-2024-001',
        'fecha' => '2024-12-28',
        'estado' => 'entregado',
        'total' => 156.75,
        'productos' => [
            ['nombre' => 'Tomates Cherry Orgánicos', 'cantidad' => 2, 'precio' => 45.50],
            ['nombre' => 'Lechugas Hidropónicas', 'cantidad' => 1, 'precio' => 35.00],
            ['nombre' => 'Zanahorias Baby Premium', 'cantidad' => 1, 'precio' => 28.75]
        ],
        'vendedores' => ['Granja Verde SA', 'Hidropónicos del Norte'],
        'direccion_envio' => 'Av. Insurgentes 123, Col. Roma Norte, CDMX',
        'metodo_pago' => 'Tarjeta de Crédito',
        'tracking' => 'TRK123456789',
        'calificacion' => 5,
        'comentario' => 'Productos frescos y de excelente calidad',
        'fecha_entrega' => '2024-12-30'
    ],
    [
        'id' => 'PED-2024-002',
        'fecha' => '2024-12-29',
        'estado' => 'en_transito',
        'total' => 89.50,
        'productos' => [
            ['nombre' => 'Brócoli Orgánico', 'cantidad' => 2, 'precio' => 42.00],
            ['nombre' => 'Espinacas Frescas', 'cantidad' => 1, 'precio' => 38.50]
        ],
        'vendedores' => ['Eco Vegetales'],
        'direccion_envio' => 'Calle Morelos 456, Col. Centro, Guadalajara',
        'metodo_pago' => 'PayPal',
        'tracking' => 'TRK987654321',
        'fecha_estimada' => '2025-01-02'
    ],
    [
        'id' => 'PED-2024-003',
        'fecha' => '2024-12-30',
        'estado' => 'procesando',
        'total' => 125.00,
        'productos' => [
            ['nombre' => 'Aguacates Hass', 'cantidad' => 2, 'precio' => 65.00]
        ],
        'vendedores' => ['Aguacates del Sur'],
        'direccion_envio' => 'Av. Juárez 789, Col. Polanco, CDMX',
        'metodo_pago' => 'Transferencia Bancaria',
        'fecha_estimada' => '2025-01-03'
    ]
];

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - AgroConecta</title>
    
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

        .order-card {
            border: 1px solid var(--border-color);
            border-radius: 15px;
            background: var(--bg-primary);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .order-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .order-header {
            background: linear-gradient(90deg, var(--accent-color), var(--secondary-color));
            color: white;
            padding: 15px 20px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-entregado {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-en_transito {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-procesando {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b3d9ff;
        }

        .status-cancelado {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .product-list {
            max-height: 200px;
            overflow-y: auto;
        }

        .rating-stars {
            color: #ffc107;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-color);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
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
            padding: 10px;
            margin-bottom: 20px;
        }

        .filter-tab {
            border: none;
            background: transparent;
            padding: 10px 20px;
            border-radius: 8px;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .filter-tab.active {
            background: var(--primary-color);
            color: white;
        }

        .order-summary {
            background: var(--bg-secondary);
            border-radius: 10px;
            padding: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 20px;
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
                        <a class="nav-link" href="catalogo.php">
                            <i class="fas fa-store me-1"></i>Catálogo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="mis-pedidos.php">
                            <i class="fas fa-receipt me-1"></i>Mis Pedidos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="favoritos.php">
                            <i class="fas fa-heart me-1"></i>Favoritos
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item me-3">
                        <a class="nav-link position-relative" href="carrito.php">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                        </a>
                    </li>
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
                            <i class="fas fa-receipt text-primary me-2"></i>
                            Mis Pedidos
                        </h2>
                        <p class="text-muted mb-0">Consulta el estado y detalles de todos tus pedidos</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#searchModal">
                            <i class="fas fa-search me-1"></i>
                            Buscar Pedido
                        </button>
                        <a href="catalogo.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Nuevo Pedido
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <div class="d-flex justify-content-center">
                <button class="filter-tab active" data-filter="todos">Todos (<?php echo count($pedidos); ?>)</button>
                <button class="filter-tab" data-filter="entregado">Entregados</button>
                <button class="filter-tab" data-filter="en_transito">En Tránsito</button>
                <button class="filter-tab" data-filter="procesando">Procesando</button>
            </div>
        </div>

        <!-- Orders List -->
        <div class="row">
            <div class="col-12">
                <?php if (empty($pedidos)): ?>
                    <div class="content-card p-5">
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>
                            <h3>No tienes pedidos aún</h3>
                            <p class="mb-4">Cuando realices tu primera compra, aparecerá aquí</p>
                            <a href="catalogo.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-store me-2"></i>
                                Explorar Productos
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <div class="order-card" data-status="<?php echo $pedido['estado']; ?>">
                            <div class="order-header">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <h6 class="mb-1"><?php echo $pedido['id']; ?></h6>
                                        <small>Pedido realizado el <?php echo date('d/m/Y', strtotime($pedido['fecha'])); ?></small>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="status-badge status-<?php echo $pedido['estado']; ?>">
                                            <?php 
                                                $estados = [
                                                    'entregado' => 'Entregado',
                                                    'en_transito' => 'En Tránsito',
                                                    'procesando' => 'Procesando',
                                                    'cancelado' => 'Cancelado'
                                                ];
                                                echo $estados[$pedido['estado']] ?? $pedido['estado'];
                                            ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <div class="text-sm">Total</div>
                                        <strong class="fs-5">$<?php echo number_format($pedido['total'], 2); ?></strong>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button class="btn btn-light btn-sm" onclick="toggleOrderDetails('<?php echo $pedido['id']; ?>')">
                                            <i class="fas fa-chevron-down" id="toggle-<?php echo $pedido['id']; ?>"></i>
                                            Detalles
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-details collapse" id="details-<?php echo $pedido['id']; ?>">
                                <div class="p-4">
                                    <div class="row">
                                        <!-- Productos -->
                                        <div class="col-md-6 mb-4">
                                            <h6 class="mb-3">
                                                <i class="fas fa-box me-2"></i>
                                                Productos (<?php echo count($pedido['productos']); ?>)
                                            </h6>
                                            <div class="product-list">
                                                <?php foreach ($pedido['productos'] as $producto): ?>
                                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2" style="background: var(--bg-secondary); border-radius: 8px;">
                                                        <div>
                                                            <strong><?php echo $producto['nombre']; ?></strong><br>
                                                            <small class="text-muted">Cantidad: <?php echo $producto['cantidad']; ?></small>
                                                        </div>
                                                        <div class="text-primary font-weight-bold">
                                                            $<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Información de envío -->
                                        <div class="col-md-6 mb-4">
                                            <h6 class="mb-3">
                                                <i class="fas fa-shipping-fast me-2"></i>
                                                Información de Envío
                                            </h6>
                                            <div class="order-summary">
                                                <div class="mb-2">
                                                    <i class="fas fa-map-marker-alt me-2"></i>
                                                    <strong>Dirección:</strong><br>
                                                    <span class="ms-4"><?php echo $pedido['direccion_envio']; ?></span>
                                                </div>
                                                <?php if (isset($pedido['tracking'])): ?>
                                                    <div class="mb-2">
                                                        <i class="fas fa-barcode me-2"></i>
                                                        <strong>Tracking:</strong> <?php echo $pedido['tracking']; ?>
                                                        <button class="btn btn-link btn-sm p-0 ms-2" onclick="copiarTracking('<?php echo $pedido['tracking']; ?>')">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="mb-2">
                                                    <i class="fas fa-credit-card me-2"></i>
                                                    <strong>Pago:</strong> <?php echo $pedido['metodo_pago']; ?>
                                                </div>
                                                <div>
                                                    <i class="fas fa-store me-2"></i>
                                                    <strong>Vendedores:</strong> <?php echo implode(', ', $pedido['vendedores']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Acciones -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="d-flex gap-2 flex-wrap">
                                                <?php if ($pedido['estado'] === 'entregado'): ?>
                                                    <?php if (isset($pedido['calificacion'])): ?>
                                                        <div class="me-3">
                                                            <small class="text-muted">Tu calificación:</small><br>
                                                            <div class="rating-stars">
                                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                                    <i class="fas fa-star<?php echo $i <= $pedido['calificacion'] ? '' : '-o'; ?>"></i>
                                                                <?php endfor; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <button class="btn btn-outline-primary btn-sm" onclick="reordenar('<?php echo $pedido['id']; ?>')">
                                                        <i class="fas fa-redo me-1"></i>Volver a Ordenar
                                                    </button>
                                                <?php elseif ($pedido['estado'] === 'en_transito'): ?>
                                                    <button class="btn btn-outline-info btn-sm" onclick="rastrearPedido('<?php echo $pedido['tracking'] ?? ''; ?>')">
                                                        <i class="fas fa-map-marker-alt me-1"></i>Rastrear Pedido
                                                    </button>
                                                <?php elseif ($pedido['estado'] === 'procesando'): ?>
                                                    <button class="btn btn-outline-danger btn-sm" onclick="cancelarPedido('<?php echo $pedido['id']; ?>')">
                                                        <i class="fas fa-times me-1"></i>Cancelar Pedido
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-outline-secondary btn-sm" onclick="descargarFactura('<?php echo $pedido['id']; ?>')">
                                                    <i class="fas fa-download me-1"></i>Descargar Factura
                                                </button>
                                                
                                                <button class="btn btn-outline-success btn-sm" onclick="contactarVendedor('<?php echo $pedido['id']; ?>')">
                                                    <i class="fas fa-comments me-1"></i>Contactar Vendedor
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-search me-2"></i>
                        Buscar Pedido
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="searchOrderId" class="form-label">Número de Pedido</label>
                        <input type="text" class="form-control" id="searchOrderId" placeholder="Ej: PED-2024-001">
                    </div>
                    <div class="mb-3">
                        <label for="searchTracking" class="form-label">Código de Seguimiento</label>
                        <input type="text" class="form-control" id="searchTracking" placeholder="Ej: TRK123456789">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="buscarPedido()">Buscar</button>
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
                // Update active tab
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Filter orders
                const filter = this.dataset.filter;
                const orders = document.querySelectorAll('.order-card');
                
                orders.forEach(order => {
                    if (filter === 'todos' || order.dataset.status === filter) {
                        order.style.display = 'block';
                    } else {
                        order.style.display = 'none';
                    }
                });
            });
        });

        function toggleOrderDetails(orderId) {
            const details = document.getElementById(`details-${orderId}`);
            const toggle = document.getElementById(`toggle-${orderId}`);
            
            if (details.classList.contains('show')) {
                details.classList.remove('show');
                toggle.classList.remove('fa-chevron-up');
                toggle.classList.add('fa-chevron-down');
            } else {
                details.classList.add('show');
                toggle.classList.remove('fa-chevron-down');
                toggle.classList.add('fa-chevron-up');
            }
        }

        function copiarTracking(tracking) {
            navigator.clipboard.writeText(tracking).then(() => {
                showNotification('Código de seguimiento copiado', 'success');
            });
        }

        function reordenar(orderId) {
            showNotification('Agregando productos al carrito...', 'info');
            setTimeout(() => {
                showNotification('Productos agregados al carrito', 'success');
            }, 1500);
        }

        function rastrearPedido(tracking) {
            if (tracking) {
                showNotification('Abriendo página de seguimiento...', 'info');
                // Aquí se abriría la página de seguimiento
                setTimeout(() => {
                    alert('Funcionalidad de seguimiento en desarrollo\nTracking: ' + tracking);
                }, 1000);
            }
        }

        function cancelarPedido(orderId) {
            if (confirm('¿Estás seguro de que deseas cancelar este pedido?')) {
                showNotification('Procesando cancelación...', 'info');
                setTimeout(() => {
                    showNotification('Pedido cancelado correctamente', 'success');
                }, 1500);
            }
        }

        function descargarFactura(orderId) {
            showNotification('Generando factura...', 'info');
            setTimeout(() => {
                alert('Descarga de factura en desarrollo');
            }, 1000);
        }

        function contactarVendedor(orderId) {
            showNotification('Abriendo chat con vendedor...', 'info');
            setTimeout(() => {
                alert('Chat con vendedor en desarrollo');
            }, 1000);
        }

        function buscarPedido() {
            const orderId = document.getElementById('searchOrderId').value;
            const tracking = document.getElementById('searchTracking').value;
            
            if (!orderId && !tracking) {
                showNotification('Ingresa un número de pedido o código de seguimiento', 'warning');
                return;
            }
            
            showNotification('Buscando pedido...', 'info');
            
            // Simular búsqueda
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
                modal.hide();
                showNotification('Pedido encontrado', 'success');
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

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            // Animaciones de entrada
            const orders = document.querySelectorAll('.order-card');
            orders.forEach((order, index) => {
                order.style.opacity = '0';
                order.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    order.style.transition = 'all 0.6s ease';
                    order.style.opacity = '1';
                    order.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>