<?php
/**
 * Gestión de Productos - Vendedores y Admins
 */

// Configuración básica
if (ob_get_level()) ob_end_clean();
ob_start();

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache'); 
header('Expires: 0');

// Incluir dependencias
require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';

SessionManager::startSecureSession();

// Verificación de autenticación
if (!SessionManager::isLoggedIn()) {
    ob_end_clean();
    header('Location: login.php');
    exit;
}

$userData = SessionManager::getUserData();
$user = [
    'id' => $userData['id'] ?? $_SESSION['user_id'],
    'nombre' => $userData['nombre'] ?? $_SESSION['user_nombre'] ?? 'Usuario',
    'correo' => $userData['correo'] ?? $_SESSION['user_email'] ?? 'usuario@test.com',
    'tipo' => $userData['tipo'] ?? $_SESSION['user_tipo'] ?? 'vendedor'
];

// Verificar que sea vendedor o admin
if ($user['tipo'] !== 'vendedor' && $user['tipo'] !== 'admin') {
    ob_end_clean();
    header('Location: dashboard.php');
    exit;
}

// Obtener productos reales del vendedor actual
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $query = "
        SELECT 
            p.id_producto as id,
            p.nombre,
            p.descripcion,
            p.precio,
            p.stock,
            p.categoria,
            p.unidad_medida,
            p.imagen_url as imagen,
            p.activo,
            p.fecha_publicacion as fecha_creacion,
            u.nombre as vendedor_nombre
        FROM producto p
        INNER JOIN usuario u ON p.id_usuario = u.id_usuario
    ";
    
    $params = [];
    
    // Si es vendedor, solo mostrar sus productos
    if ($user['tipo'] === 'vendedor') {
        $query .= " WHERE p.id_usuario = ?";
        $params[] = $user['id'];
    }
    // Si es admin, mostrar todos los productos (sin WHERE)
    
    $query .= " ORDER BY p.fecha_publicacion DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $productos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transformar datos para compatibilidad con frontend
    $productos = [];
    foreach ($productos_db as $prod) {
        $productos[] = [
            'id' => $prod['id'],
            'nombre' => $prod['nombre'],
            'descripcion' => $prod['descripcion'],
            'precio' => floatval($prod['precio']),
            'stock' => intval($prod['stock']),
            'categoria' => $prod['categoria'],
            'imagen' => $prod['imagen'] ?: 'default-product.jpg',
            'estado' => $prod['activo'] == 1 ? 'activo' : 'inactivo',
            'fecha_creacion' => $prod['fecha_creacion'],
            'unidad_medida' => $prod['unidad_medida'] ?: 'kg',
            'vendedor' => $prod['vendedor_nombre'] ?? 'Vendedor'
        ];
    }
    
} catch (Exception $e) {
    error_log("Error en productos: " . $e->getMessage());
    $productos = []; // Array vacío en caso de error
}

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - AgroConecta</title>
    
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
        }

        body {
            background: linear-gradient(135deg, #E8F5E8 0%, #F1F8E9 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-custom {
            background: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .main-container {
            margin-top: 2rem;
        }

        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .product-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 10px;
            font-weight: 500;
            padding: 0.5rem 1.5rem;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .page-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-seedling me-2"></i>
                AgroConecta
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($user['nombre']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="page-title">
                    <i class="fas fa-boxes me-3"></i>
                    Gestión de Productos
                    <?php if ($user['tipo'] === 'admin'): ?>
                        <span class="badge bg-warning text-dark ms-2">Admin</span>
                    <?php endif; ?>
                </h1>
                <p class="lead text-muted">
                    <?php if ($user['tipo'] === 'admin'): ?>
                        Administra todos los productos de la plataforma
                    <?php else: ?>
                        Administra tus productos y inventory
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-auto">
                <?php if ($user['tipo'] === 'vendedor'): ?>
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nuevo Producto
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-boxes fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Productos</h6>
                                <h3 class="mb-0"><?php echo count($productos); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Activos</h6>
                                <h3 class="mb-0">
                                    <?php 
                                    $activos = array_filter($productos, function($p) { return $p['estado'] === 'activo'; });
                                    echo count($activos); 
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Stock Bajo</h6>
                                <h3 class="mb-0">
                                    <?php 
                                    $stockBajo = array_filter($productos, function($p) { return $p['stock'] < 10; });
                                    echo count($stockBajo); 
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-dollar-sign fa-2x text-info"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Valor Total</h6>
                                <h3 class="mb-0">
                                    $<?php 
                                    $valorTotal = array_reduce($productos, function($sum, $p) { 
                                        return $sum + ($p['precio'] * $p['stock']); 
                                    }, 0);
                                    echo number_format($valorTotal, 2); 
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row">
            <?php if (empty($productos)): ?>
                <div class="col-12">
                    <div class="card product-card text-center py-5">
                        <div class="card-body">
                            <i class="fas fa-seedling fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted">
                                <?php if ($user['tipo'] === 'admin'): ?>
                                    No hay productos en la plataforma
                                <?php else: ?>
                                    No tienes productos todavía
                                <?php endif; ?>
                            </h4>
                            <p class="text-muted mb-4">
                                <?php if ($user['tipo'] === 'vendedor'): ?>
                                    Comienza agregando tu primer producto para vender en AgroConecta
                                <?php endif; ?>
                            </p>
                            <?php if ($user['tipo'] === 'vendedor'): ?>
                                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                                    <i class="fas fa-plus-circle me-2"></i>
                                    Agregar Primer Producto
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($productos as $producto): ?>
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                        <div class="card product-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                    <span class="badge bg-<?php echo $producto['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($producto['estado']); ?>
                                    </span>
                                </div>
                                
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 100)) . '...'; ?>
                                </p>
                                
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Precio</small>
                                            <div class="fw-bold text-success fs-5">
                                                $<?php echo number_format($producto['precio'], 2); ?>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Stock</small>
                                            <div class="fw-bold <?php echo $producto['stock'] < 10 ? 'text-warning' : 'text-primary'; ?>">
                                                <?php echo $producto['stock']; ?> <?php echo $producto['unidad_medida']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted d-block">Categoría</small>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-tag me-1"></i><?php echo $producto['categoria']; ?>
                                    </span>
                                    <?php if ($user['tipo'] === 'admin'): ?>
                                        <span class="badge bg-info text-white ms-2">
                                            <i class="fas fa-user me-1"></i><?php echo $producto['vendedor']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($producto['fecha_creacion'])); ?>
                                    </small>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($user['tipo'] === 'vendedor'): ?>
                                            <button class="btn btn-outline-primary" onclick="editarProducto(<?php echo $producto['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="eliminarProducto(<?php echo $producto['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-outline-info" onclick="verDetalles(<?php echo $producto['id']; ?>)">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function editarProducto(id) {
            console.log('Editando producto:', id);
            // Implementar funcionalidad de edición
        }
        
        function eliminarProducto(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
                console.log('Eliminando producto:', id);
                // Implementar funcionalidad de eliminación
            }
        }
        
        function verDetalles(id) {
            console.log('Viendo detalles del producto:', id);
            // Implementar vista de detalles para admin
        }
    </script>
</body>
</html>