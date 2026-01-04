<?php
/**
 * Gestión de Productos - Vendedores
 */

// Configuración básica
if (ob_get_level()) ob_end_clean();
ob_start();

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache'); 
header('Expires: 0');

session_start();

// Verificación de autenticación y tipo de usuario
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

// Datos de ejemplo para productos
$productos = [
    [
        'id' => 1,
        'nombre' => 'Tomates Cherry Orgánicos',
        'descripcion' => 'Tomates cherry cultivados de forma orgánica',
        'precio' => 45.50,
        'stock' => 25,
        'categoria' => 'Verduras',
        'imagen' => 'tomates-cherry.jpg',
        'estado' => 'activo',
        'fecha_creacion' => '2024-12-15'
    ],
    [
        'id' => 2,
        'nombre' => 'Lechugas Hidropónicas',
        'descripcion' => 'Lechugas frescas cultivadas hidropónicamente',
        'precio' => 35.00,
        'stock' => 18,
        'categoria' => 'Verduras',
        'imagen' => 'lechugas.jpg',
        'estado' => 'activo',
        'fecha_creacion' => '2024-12-10'
    ],
    [
        'id' => 3,
        'nombre' => 'Zanahorias Baby',
        'descripcion' => 'Zanahorias baby tiernas y dulces',
        'precio' => 28.75,
        'stock' => 0,
        'categoria' => 'Verduras',
        'imagen' => 'zanahorias-baby.jpg',
        'estado' => 'agotado',
        'fecha_creacion' => '2024-12-08'
    ]
];

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

        .product-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .badge-stock-alto {
            background-color: #28a745;
        }

        .badge-stock-medio {
            background-color: #ffc107;
        }

        .badge-stock-bajo {
            background-color: #dc3545;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .stats-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .action-buttons .btn {
            margin: 0 2px;
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
                        <a class="nav-link active" href="productos.php">
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
                            <i class="fas fa-box text-primary me-2"></i>
                            Gestión de Productos
                        </h2>
                        <p class="text-muted mb-0">Administra tu catálogo de productos</p>
                    </div>
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nuevo Producto
                    </button>
                </div>
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
                                <h3 class="mb-0"><?php echo count(array_filter($productos, fn($p) => $p['estado'] === 'activo')); ?></h3>
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
                                <h3 class="mb-0"><?php echo count(array_filter($productos, fn($p) => $p['stock'] < 10 && $p['stock'] > 0)); ?></h3>
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
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Agotados</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($productos, fn($p) => $p['stock'] == 0)); ?></h3>
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
                                <input type="text" class="form-control" placeholder="Buscar productos..." id="searchProducts">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterCategoria">
                                <option value="">Todas las categorías</option>
                                <option value="Verduras">Verduras</option>
                                <option value="Frutas">Frutas</option>
                                <option value="Granos">Granos</option>
                                <option value="Lácteos">Lácteos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterEstado">
                                <option value="">Todos los estados</option>
                                <option value="activo">Activos</option>
                                <option value="agotado">Agotados</option>
                                <option value="inactivo">Inactivos</option>
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

        <!-- Products Grid -->
        <div class="row" id="productosContainer">
            <?php foreach ($productos as $producto): ?>
                <div class="col-xl-4 col-lg-6 mb-4 producto-item" 
                     data-categoria="<?php echo $producto['categoria']; ?>"
                     data-estado="<?php echo $producto['estado']; ?>"
                     data-nombre="<?php echo strtolower($producto['nombre']); ?>">
                    <div class="card product-card">
                        <div class="product-image">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                <?php
                                $stockClass = 'badge-stock-alto';
                                if ($producto['stock'] == 0) {
                                    $stockClass = 'badge-stock-bajo';
                                } elseif ($producto['stock'] < 10) {
                                    $stockClass = 'badge-stock-medio';
                                }
                                ?>
                                <span class="badge <?php echo $stockClass; ?>">
                                    <?php echo $producto['stock']; ?> disponibles
                                </span>
                            </div>
                            
                            <p class="card-text text-muted small mb-2">
                                <?php echo htmlspecialchars($producto['descripcion']); ?>
                            </p>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong class="text-success">$<?php echo number_format($producto['precio'], 2); ?></strong>
                                    <small class="text-muted d-block">por unidad</small>
                                </div>
                                <div class="col-6 text-end">
                                    <small class="text-muted">
                                        <i class="fas fa-tag me-1"></i><?php echo $producto['categoria']; ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <button class="btn btn-outline-primary btn-sm" onclick="editarProducto(<?php echo $producto['id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="verDetalles(<?php echo $producto['id']; ?>)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarProducto(<?php echo $producto['id']; ?>)">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Creado: <?php echo date('d/m/Y', strtotime($producto['fecha_creacion'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal Nuevo Producto -->
    <div class="modal fade" id="modalNuevoProducto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nuevo Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoProducto">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría *</label>
                                <select class="form-select" name="categoria" required>
                                    <option value="">Seleccionar categoría</option>
                                    <option value="Verduras">Verduras</option>
                                    <option value="Frutas">Frutas</option>
                                    <option value="Granos">Granos</option>
                                    <option value="Lácteos">Lácteos</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio (MXN) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="precio" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Inicial *</label>
                                <input type="number" class="form-control" name="stock" min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Imagen del Producto</label>
                            <input type="file" class="form-control" name="imagen" accept="image/*">
                            <div class="form-text">Formatos permitidos: JPG, PNG, GIF (máximo 2MB)</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarNuevoProducto()">
                        <i class="fas fa-save me-1"></i>Guardar Producto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Funciones de búsqueda y filtrado
        document.getElementById('searchProducts').addEventListener('input', filtrarProductos);
        document.getElementById('filterCategoria').addEventListener('change', filtrarProductos);
        document.getElementById('filterEstado').addEventListener('change', filtrarProductos);

        function filtrarProductos() {
            const busqueda = document.getElementById('searchProducts').value.toLowerCase();
            const categoria = document.getElementById('filterCategoria').value;
            const estado = document.getElementById('filterEstado').value;
            
            const productos = document.querySelectorAll('.producto-item');
            
            productos.forEach(producto => {
                const nombre = producto.dataset.nombre;
                const prodCategoria = producto.dataset.categoria;
                const prodEstado = producto.dataset.estado;
                
                const matchNombre = nombre.includes(busqueda);
                const matchCategoria = !categoria || prodCategoria === categoria;
                const matchEstado = !estado || prodEstado === estado;
                
                if (matchNombre && matchCategoria && matchEstado) {
                    producto.style.display = 'block';
                } else {
                    producto.style.display = 'none';
                }
            });
        }

        function limpiarFiltros() {
            document.getElementById('searchProducts').value = '';
            document.getElementById('filterCategoria').value = '';
            document.getElementById('filterEstado').value = '';
            filtrarProductos();
        }

        // Funciones de gestión de productos
        function editarProducto(id) {
            alert('Función de editar producto: ' + id + ' - En desarrollo');
        }

        function verDetalles(id) {
            alert('Función de ver detalles producto: ' + id + ' - En desarrollo');
        }

        function eliminarProducto(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
                alert('Función de eliminar producto: ' + id + ' - En desarrollo');
            }
        }

        function guardarNuevoProducto() {
            const form = document.getElementById('formNuevoProducto');
            const formData = new FormData(form);
            
            // Validación básica
            const nombre = formData.get('nombre');
            const categoria = formData.get('categoria');
            const precio = formData.get('precio');
            const stock = formData.get('stock');
            
            if (!nombre || !categoria || !precio || !stock) {
                alert('Por favor, completa todos los campos requeridos.');
                return;
            }
            
            // Aquí iría la lógica para guardar en la base de datos
            alert('Nuevo producto guardado correctamente (funcionalidad en desarrollo)');
            
            // Cerrar modal y resetear formulario
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoProducto'));
            modal.hide();
            form.reset();
        }

        // Animaciones de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.product-card, .stats-card');
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