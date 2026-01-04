<?php
/**
 * Lista de Clientes - Vendedores
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

// Datos de ejemplo para clientes
$clientes = [
    [
        'id' => 1,
        'nombre' => 'María González',
        'email' => 'maria.gonzalez@email.com',
        'telefono' => '+52 555 123 4567',
        'direccion' => 'Av. Principal 123, Col. Centro',
        'fecha_registro' => '2024-11-15',
        'ultima_compra' => '2025-01-03',
        'total_compras' => 1250.75,
        'pedidos_completados' => 8,
        'estado' => 'activo',
        'preferencias' => ['Verduras orgánicas', 'Productos frescos'],
        'notas' => 'Cliente frecuente, prefiere entregas matutinas'
    ],
    [
        'id' => 2,
        'nombre' => 'Carlos Ruiz',
        'email' => 'carlos.ruiz@email.com',
        'telefono' => '+52 555 987 6543',
        'direccion' => 'Calle Secundaria 456, Col. Norte',
        'fecha_registro' => '2024-12-01',
        'ultima_compra' => '2025-01-02',
        'total_compras' => 680.25,
        'pedidos_completados' => 5,
        'estado' => 'activo',
        'preferencias' => ['Zanahorias', 'Productos locales'],
        'notas' => 'Paga siempre en efectivo'
    ],
    [
        'id' => 3,
        'nombre' => 'Ana Martínez',
        'email' => 'ana.martinez@email.com',
        'telefono' => '+52 555 456 7890',
        'direccion' => 'Blvd. Sur 789, Col. Residencial',
        'fecha_registro' => '2024-10-20',
        'ultima_compra' => '2025-01-02',
        'total_compras' => 2150.00,
        'pedidos_completados' => 12,
        'estado' => 'activo',
        'preferencias' => ['Lechugas hidropónicas', 'Tomates cherry'],
        'notas' => 'Casa color azul, muy puntual en pagos'
    ],
    [
        'id' => 4,
        'nombre' => 'Luis Hernández',
        'email' => 'luis.hernandez@email.com',
        'telefono' => '+52 555 321 0987',
        'direccion' => 'Av. Oriente 321, Col. Industrial',
        'fecha_registro' => '2024-09-10',
        'ultima_compra' => '2024-12-28',
        'total_compras' => 890.50,
        'pedidos_completados' => 6,
        'estado' => 'inactivo',
        'preferencias' => ['Productos económicos'],
        'notas' => 'Cliente ocasional, contactar por ofertas'
    ],
    [
        'id' => 5,
        'nombre' => 'Sofía López',
        'email' => 'sofia.lopez@email.com',
        'telefono' => '+52 555 654 3210',
        'direccion' => 'Calle Nueva 987, Col. Moderna',
        'fecha_registro' => '2024-12-20',
        'ultima_compra' => '2024-12-30',
        'total_compras' => 325.00,
        'pedidos_completados' => 2,
        'estado' => 'nuevo',
        'preferencias' => ['Productos orgánicos'],
        'notas' => 'Cliente nuevo, ofrecer descuentos de bienvenida'
    ]
];

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Clientes - AgroConecta</title>
    
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

        .stats-total::before { background: #007bff; }
        .stats-activos::before { background: #28a745; }
        .stats-nuevos::before { background: #17a2b8; }
        .stats-ingresos::before { background: #ffc107; }

        .cliente-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .cliente-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }

        .cliente-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .badge-activo { background-color: #28a745; }
        .badge-inactivo { background-color: #6c757d; }
        .badge-nuevo { background-color: #17a2b8; }

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

        .preferencia-tag {
            background-color: rgba(46, 125, 50, 0.1);
            color: var(--primary-color);
            border-radius: 15px;
            padding: 4px 12px;
            font-size: 0.8rem;
            margin: 2px;
            display: inline-block;
        }

        .rating-stars {
            color: #ffc107;
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
                        <a class="nav-link active" href="clientes.php">
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
                            <i class="fas fa-users text-primary me-2"></i>
                            Lista de Clientes
                        </h2>
                        <p class="text-muted mb-0">Gestiona tu cartera de clientes y sus preferencias</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2">
                            <i class="fas fa-download me-1"></i>
                            Exportar Lista
                        </button>
                        <button class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>
                            Nuevo Cliente
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-total">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Clientes</h6>
                                <h3 class="mb-0"><?php echo count($clientes); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-activos">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-user-check fa-2x text-success"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Activos</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($clientes, fn($c) => $c['estado'] === 'activo')); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card stats-nuevos">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-user-plus fa-2x text-info"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Nuevos</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($clientes, fn($c) => $c['estado'] === 'nuevo')); ?></h3>
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
                                <i class="fas fa-dollar-sign fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Ingresos Totales</h6>
                                <h3 class="mb-0">$<?php echo number_format(array_sum(array_column($clientes, 'total_compras')), 0); ?></h3>
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
                                <input type="text" class="form-control" placeholder="Buscar clientes..." id="searchClientes">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterEstado">
                                <option value="">Todos los estados</option>
                                <option value="activo">Activos</option>
                                <option value="inactivo">Inactivos</option>
                                <option value="nuevo">Nuevos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterOrden">
                                <option value="nombre">Ordenar por Nombre</option>
                                <option value="fecha_registro">Fecha Registro</option>
                                <option value="total_compras">Total Compras</option>
                                <option value="ultima_compra">Última Compra</option>
                            </select>
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

        <!-- Clients List -->
        <div class="row" id="clientesContainer">
            <?php foreach ($clientes as $cliente): ?>
                <div class="col-xl-6 col-lg-12 mb-4 cliente-item" 
                     data-nombre="<?php echo strtolower($cliente['nombre']); ?>"
                     data-estado="<?php echo $cliente['estado']; ?>">
                    <div class="card cliente-card">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-3 text-center mb-3">
                                    <div class="cliente-avatar mx-auto mb-2">
                                        <?php echo strtoupper(substr($cliente['nombre'], 0, 2)); ?>
                                    </div>
                                    <span class="badge badge-<?php echo $cliente['estado']; ?>">
                                        <?php echo ucfirst($cliente['estado']); ?>
                                    </span>
                                </div>
                                
                                <div class="col-md-9">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($cliente['nombre']); ?></h5>
                                        <div class="rating-stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-envelope me-1"></i>
                                                <?php echo $cliente['email']; ?>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-phone me-1"></i>
                                                <?php echo $cliente['telefono']; ?>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo $cliente['direccion']; ?>
                                            </small>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <div class="mb-1">
                                                <strong class="text-success">$<?php echo number_format($cliente['total_compras'], 2); ?></strong>
                                                <small class="text-muted d-block">Total comprado</small>
                                            </div>
                                            <div>
                                                <strong><?php echo $cliente['pedidos_completados']; ?></strong>
                                                <small class="text-muted d-block">Pedidos</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">Preferencias:</small>
                                        <?php foreach ($cliente['preferencias'] as $preferencia): ?>
                                            <span class="preferencia-tag"><?php echo $preferencia; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-plus me-1"></i>
                                                Cliente desde: <?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <small class="text-muted">
                                                <i class="fas fa-shopping-bag me-1"></i>
                                                Última compra: <?php echo date('d/m/Y', strtotime($cliente['ultima_compra'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($cliente['notas'])): ?>
                                        <div class="p-2 bg-light rounded mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-sticky-note me-1"></i>
                                                <?php echo htmlspecialchars($cliente['notas']); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button class="btn btn-outline-primary btn-sm" onclick="verPerfilCliente(<?php echo $cliente['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Ver Perfil
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="crearPedido(<?php echo $cliente['id']; ?>)">
                                            <i class="fas fa-plus me-1"></i>Nuevo Pedido
                                        </button>
                                        <button class="btn btn-outline-info btn-sm" onclick="contactarCliente('<?php echo $cliente['telefono']; ?>')">
                                            <i class="fas fa-phone me-1"></i>Contactar
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="editarCliente(<?php echo $cliente['id']; ?>)">
                                            <i class="fas fa-edit me-1"></i>Editar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Funciones de búsqueda y filtrado
        document.getElementById('searchClientes').addEventListener('input', filtrarClientes);
        document.getElementById('filterEstado').addEventListener('change', filtrarClientes);

        function filtrarClientes() {
            const busqueda = document.getElementById('searchClientes').value.toLowerCase();
            const estado = document.getElementById('filterEstado').value;
            
            const clientes = document.querySelectorAll('.cliente-item');
            
            clientes.forEach(cliente => {
                const nombre = cliente.dataset.nombre;
                const clienteEstado = cliente.dataset.estado;
                
                const matchNombre = nombre.includes(busqueda);
                const matchEstado = !estado || clienteEstado === estado;
                
                if (matchNombre && matchEstado) {
                    cliente.style.display = 'block';
                } else {
                    cliente.style.display = 'none';
                }
            });
        }

        function limpiarFiltros() {
            document.getElementById('searchClientes').value = '';
            document.getElementById('filterEstado').value = '';
            document.getElementById('filterOrden').value = 'nombre';
            filtrarClientes();
        }

        // Funciones de gestión de clientes
        function verPerfilCliente(id) {
            alert('Ver perfil completo del cliente #' + id + ' - Funcionalidad en desarrollo');
        }

        function crearPedido(id) {
            alert('Crear nuevo pedido para cliente #' + id + ' - Funcionalidad en desarrollo');
        }

        function contactarCliente(telefono) {
            if (confirm('¿Contactar al cliente al número ' + telefono + '?')) {
                window.open('tel:' + telefono);
            }
        }

        function editarCliente(id) {
            alert('Editar información del cliente #' + id + ' - Funcionalidad en desarrollo');
        }

        // Animaciones de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stats-card, .cliente-card');
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