<?php
/**
 * Gestión de Pedidos - Vendedores
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

// Datos de ejemplo para pedidos
$pedidos = [
    [
        'id' => 2001,
        'cliente' => 'María González',
        'email' => 'maria.gonzalez@email.com',
        'telefono' => '+52 555 123 4567',
        'productos' => [
            ['nombre' => 'Tomates Cherry', 'cantidad' => 2, 'precio' => 45.50],
            ['nombre' => 'Lechugas Hidropónicas', 'cantidad' => 1, 'precio' => 35.00]
        ],
        'total' => 126.00,
        'fecha_pedido' => '2025-01-03 14:30:00',
        'fecha_entrega' => '2025-01-05 10:00:00',
        'estado' => 'pendiente',
        'direccion' => 'Av. Principal 123, Col. Centro',
        'notas' => 'Entregar en horario matutino',
        'metodo_pago' => 'tarjeta'
    ],
    [
        'id' => 2002,
        'cliente' => 'Carlos Ruiz',
        'email' => 'carlos.ruiz@email.com',
        'telefono' => '+52 555 987 6543',
        'productos' => [
            ['nombre' => 'Zanahorias Baby', 'cantidad' => 3, 'precio' => 28.75]
        ],
        'total' => 86.25,
        'fecha_pedido' => '2025-01-02 16:45:00',
        'fecha_entrega' => '2025-01-04 15:00:00',
        'estado' => 'en_preparacion',
        'direccion' => 'Calle Secundaria 456, Col. Norte',
        'notas' => '',
        'metodo_pago' => 'efectivo'
    ],
    [
        'id' => 2003,
        'cliente' => 'Ana Martínez',
        'email' => 'ana.martinez@email.com',
        'telefono' => '+52 555 456 7890',
        'productos' => [
            ['nombre' => 'Lechugas Hidropónicas', 'cantidad' => 4, 'precio' => 35.00],
            ['nombre' => 'Tomates Cherry', 'cantidad' => 1, 'precio' => 45.50]
        ],
        'total' => 185.50,
        'fecha_pedido' => '2025-01-02 09:15:00',
        'fecha_entrega' => '2025-01-03 11:30:00',
        'estado' => 'completado',
        'direccion' => 'Blvd. Sur 789, Col. Residencial',
        'notas' => 'Casa color azul',
        'metodo_pago' => 'transferencia'
    ],
    [
        'id' => 2004,
        'cliente' => 'Luis Hernández',
        'email' => 'luis.hernandez@email.com',
        'telefono' => '+52 555 321 0987',
        'productos' => [
            ['nombre' => 'Tomates Cherry', 'cantidad' => 1, 'precio' => 45.50],
            ['nombre' => 'Zanahorias Baby', 'cantidad' => 2, 'precio' => 28.75]
        ],
        'total' => 103.00,
        'fecha_pedido' => '2025-01-01 12:00:00',
        'fecha_entrega' => '2025-01-02 14:00:00',
        'estado' => 'cancelado',
        'direccion' => 'Av. Oriente 321, Col. Industrial',
        'notas' => 'Cancelado por el cliente',
        'metodo_pago' => 'tarjeta'
    ]
];

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - AgroConecta</title>
    
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

        .stats-pendientes::before { background: #ffc107; }
        .stats-preparacion::before { background: #17a2b8; }
        .stats-completados::before { background: #28a745; }
        .stats-cancelados::before { background: #dc3545; }

        .pedido-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .pedido-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .badge-pendiente { background-color: #ffc107; color: #000; }
        .badge-en_preparacion { background-color: #17a2b8; }
        .badge-completado { background-color: #28a745; }
        .badge-cancelado { background-color: #dc3545; }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-color);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 50px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 14px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        .search-filters {
            background: var(--bg-primary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
                        <a class="nav-link active" href="pedidos.php">
                            <i class="fas fa-shopping-cart me-1"></i>Pedidos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clientes.php">
                            <i class="fas fa-users me-1"></i>Clientes
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
                            <i class="fas fa-shopping-cart text-primary me-2"></i>
                            Gestión de Pedidos
                        </h2>
                        <p class="text-muted mb-0">Administra y procesa los pedidos de tus clientes</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2">
                            <i class="fas fa-download me-1"></i>
                            Exportar Pedidos
                        </button>
                        <button class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Nuevo Pedido
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-pendientes">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Pendientes</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($pedidos, fn($p) => $p['estado'] === 'pendiente')); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-preparacion">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-cog fa-2x text-info"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">En Preparación</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($pedidos, fn($p) => $p['estado'] === 'en_preparacion')); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-completados">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Completados</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($pedidos, fn($p) => $p['estado'] === 'completado')); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-cancelados">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Cancelados</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($pedidos, fn($p) => $p['estado'] === 'cancelado')); ?></h3>
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
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Buscar por cliente o número..." id="searchPedidos">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterEstado">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendientes</option>
                                <option value="en_preparacion">En Preparación</option>
                                <option value="completado">Completados</option>
                                <option value="cancelado">Cancelados</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="filterFecha" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                <i class="fas fa-times me-1"></i>Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="row">
            <div class="col-12">
                <div class="card content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Lista de Pedidos
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($pedidos as $pedido): ?>
                            <div class="pedido-card p-4" data-estado="<?php echo $pedido['estado']; ?>" data-cliente="<?php echo strtolower($pedido['cliente']); ?>" data-id="<?php echo $pedido['id']; ?>">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="mb-1">Pedido #<?php echo $pedido['id']; ?></h5>
                                                <p class="text-muted mb-1">
                                                    <i class="fas fa-user me-1"></i>
                                                    <?php echo htmlspecialchars($pedido['cliente']); ?>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope me-1"></i>
                                                    <?php echo $pedido['email']; ?> |
                                                    <i class="fas fa-phone ms-2 me-1"></i>
                                                    <?php echo $pedido['telefono']; ?>
                                                </small>
                                            </div>
                                            <span class="badge badge-<?php echo $pedido['estado']; ?> fs-6">
                                                <?php echo ucfirst(str_replace('_', ' ', $pedido['estado'])); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">Productos:</h6>
                                            <?php foreach ($pedido['productos'] as $producto): ?>
                                                <div class="d-flex justify-content-between">
                                                    <span><?php echo $producto['nombre']; ?> x<?php echo $producto['cantidad']; ?></span>
                                                    <span class="text-success">$<?php echo number_format($producto['precio'], 2); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    Pedido: <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-truck me-1"></i>
                                                    Entrega: <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_entrega'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-4">
                                        <div class="text-end">
                                            <h4 class="text-success mb-3">$<?php echo number_format($pedido['total'], 2); ?></h4>
                                            
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">
                                                    <i class="fas fa-credit-card me-1"></i>
                                                    <?php echo ucfirst($pedido['metodo_pago']); ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo $pedido['direccion']; ?>
                                                </small>
                                            </div>
                                            
                                            <div class="btn-group-vertical d-grid gap-2">
                                                <button class="btn btn-outline-primary btn-sm" onclick="verDetallePedido(<?php echo $pedido['id']; ?>)">
                                                    <i class="fas fa-eye me-1"></i> Ver Detalle
                                                </button>
                                                
                                                <?php if ($pedido['estado'] === 'pendiente'): ?>
                                                    <button class="btn btn-warning btn-sm" onclick="iniciarPreparacion(<?php echo $pedido['id']; ?>)">
                                                        <i class="fas fa-play me-1"></i> Iniciar
                                                    </button>
                                                <?php elseif ($pedido['estado'] === 'en_preparacion'): ?>
                                                    <button class="btn btn-success btn-sm" onclick="marcarCompletado(<?php echo $pedido['id']; ?>)">
                                                        <i class="fas fa-check me-1"></i> Completar
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-outline-info btn-sm" onclick="contactarCliente('<?php echo $pedido['telefono']; ?>')">
                                                    <i class="fas fa-phone me-1"></i> Contactar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($pedido['notas'])): ?>
                                    <div class="mt-3 p-2 bg-light rounded">
                                        <small class="text-muted">
                                            <i class="fas fa-sticky-note me-1"></i>
                                            <strong>Notas:</strong> <?php echo htmlspecialchars($pedido['notas']); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Pedido -->
    <div class="modal fade" id="modalDetallePedido" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Detalle del Pedido
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <h6>Pedido Recibido</h6>
                            <small class="text-muted">03/01/2025 14:30</small>
                        </div>
                        <div class="timeline-item">
                            <h6>En Preparación</h6>
                            <small class="text-muted">03/01/2025 15:00</small>
                        </div>
                        <div class="timeline-item">
                            <h6>Listo para Entrega</h6>
                            <small class="text-muted">En proceso...</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary">Imprimir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Funciones de búsqueda y filtrado
        document.getElementById('searchPedidos').addEventListener('input', filtrarPedidos);
        document.getElementById('filterEstado').addEventListener('change', filtrarPedidos);

        function filtrarPedidos() {
            const busqueda = document.getElementById('searchPedidos').value.toLowerCase();
            const estado = document.getElementById('filterEstado').value;
            
            const pedidos = document.querySelectorAll('.pedido-card');
            
            pedidos.forEach(pedido => {
                const cliente = pedido.dataset.cliente;
                const id = pedido.dataset.id;
                const pedidoEstado = pedido.dataset.estado;
                
                const matchBusqueda = cliente.includes(busqueda) || id.includes(busqueda);
                const matchEstado = !estado || pedidoEstado === estado;
                
                if (matchBusqueda && matchEstado) {
                    pedido.style.display = 'block';
                } else {
                    pedido.style.display = 'none';
                }
            });
        }

        function limpiarFiltros() {
            document.getElementById('searchPedidos').value = '';
            document.getElementById('filterEstado').value = '';
            document.getElementById('filterFecha').value = new Date().toISOString().split('T')[0];
            filtrarPedidos();
        }

        // Funciones de gestión de pedidos
        function verDetallePedido(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalDetallePedido'));
            modal.show();
        }

        function iniciarPreparacion(id) {
            if (confirm('¿Iniciar la preparación de este pedido?')) {
                alert('Pedido #' + id + ' en preparación - Funcionalidad en desarrollo');
                // Aquí iría la lógica para cambiar el estado
            }
        }

        function marcarCompletado(id) {
            if (confirm('¿Marcar este pedido como completado?')) {
                alert('Pedido #' + id + ' completado - Funcionalidad en desarrollo');
                // Aquí iría la lógica para cambiar el estado
            }
        }

        function contactarCliente(telefono) {
            if (confirm('¿Contactar al cliente al número ' + telefono + '?')) {
                window.open('tel:' + telefono);
            }
        }

        // Animaciones de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stats-card, .pedido-card');
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