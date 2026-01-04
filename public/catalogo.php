<?php
/**
 * Catálogo de Productos - Clientes
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
    'tipo' => $_SESSION['user_tipo'] ?? $_SESSION['tipo'] ?? 'cliente',
    'tipo_usuario' => $_SESSION['user_tipo'] ?? $_SESSION['tipo'] ?? 'cliente'
];

// Verificar que sea cliente
if ($user['tipo'] !== 'cliente') {
    ob_end_clean();
    header('Location: dashboard.php');
    exit;
}

// Datos de ejemplo para productos del catálogo
$productos = [
    [
        'id' => 1,
        'nombre' => 'Tomates Cherry Orgánicos',
        'descripcion' => 'Tomates cherry cultivados de forma orgánica, perfectos para ensaladas y snacks saludables',
        'precio' => 45.50,
        'precio_anterior' => 52.00,
        'stock' => 25,
        'categoria' => 'Verduras',
        'vendedor' => 'Granja Verde SA',
        'nombre_vendedor' => 'Granja Verde SA',
        'imagen' => 'tomates-cherry.jpg',
        'calificacion' => 4.8,
        'reviews' => 24,
        'origen' => 'Michoacán',
        'ciudad' => 'Morelia',
        'estado' => 'Michoacán',
        'unidad_medida' => 'kg',
        'organico' => true,
        'descuento' => 12,
        'tags' => ['Orgánico', 'Local', 'Fresco'],
        'disponible' => true
    ],
    [
        'id' => 2,
        'nombre' => 'Lechugas Hidropónicas',
        'descripcion' => 'Lechugas frescas cultivadas hidropónicamente, crujientes y llenas de sabor',
        'precio' => 35.00,
        'precio_anterior' => 0,
        'stock' => 18,
        'categoria' => 'Verduras',
        'vendedor' => 'Hidropónicos del Norte',
        'nombre_vendedor' => 'Hidropónicos del Norte',
        'imagen' => 'lechugas.jpg',
        'calificacion' => 4.6,
        'reviews' => 18,
        'origen' => 'Jalisco',
        'ciudad' => 'Guadalajara',
        'estado' => 'Jalisco',
        'unidad_medida' => 'pza',
        'organico' => false,
        'descuento' => 0,
        'tags' => ['Hidropónico', 'Crujiente'],
        'disponible' => true
    ],
    [
        'id' => 3,
        'nombre' => 'Zanahorias Baby Premium',
        'descripcion' => 'Zanahorias baby tiernas y dulces, perfectas para cocinar o comer crudas',
        'precio' => 28.75,
        'precio_anterior' => 32.00,
        'stock' => 12,
        'categoria' => 'Verduras',
        'vendedor' => 'Productos Frescos Ltda',
        'nombre_vendedor' => 'Productos Frescos Ltda',
        'imagen' => 'zanahorias-baby.jpg',
        'calificacion' => 4.7,
        'reviews' => 31,
        'origen' => 'Guanajuato',
        'ciudad' => 'León',
        'estado' => 'Guanajuato',
        'unidad_medida' => 'kg',
        'organico' => true,
        'descuento' => 10,
        'tags' => ['Orgánico', 'Premium', 'Dulce'],
        'disponible' => true
    ],
    [
        'id' => 4,
        'nombre' => 'Espinacas Frescas',
        'descripcion' => 'Espinacas tiernas y nutritivas, ricas en hierro y vitaminas',
        'precio' => 38.50,
        'precio_anterior' => 0,
        'stock' => 8,
        'categoria' => 'Verduras',
        'vendedor' => 'Granja Verde SA',
        'nombre_vendedor' => 'Granja Verde SA',
        'imagen' => 'espinacas.jpg',
        'calificacion' => 4.5,
        'reviews' => 16,
        'origen' => 'Puebla',
        'ciudad' => 'Puebla',
        'estado' => 'Puebla',
        'unidad_medida' => 'manojo',
        'organico' => true,
        'descuento' => 0,
        'tags' => ['Orgánico', 'Nutritivo', 'Fresco'],
        'disponible' => true
    ],
    [
        'id' => 5,
        'nombre' => 'Brócoli Orgánico',
        'descripcion' => 'Brócoli fresco y orgánico, rico en nutrientes y antioxidantes',
        'precio' => 42.00,
        'precio_anterior' => 48.00,
        'stock' => 15,
        'categoria' => 'Verduras',
        'vendedor' => 'Eco Vegetales',
        'nombre_vendedor' => 'Eco Vegetales',
        'imagen' => 'brocoli.jpg',
        'calificacion' => 4.9,
        'reviews' => 22,
        'origen' => 'Estado de México',
        'ciudad' => 'Toluca',
        'estado' => 'Estado de México',
        'unidad_medida' => 'pza',
        'organico' => true,
        'descuento' => 12,
        'tags' => ['Orgánico', 'Antioxidante', 'Premium'],
        'disponible' => true
    ],
    [
        'id' => 6,
        'nombre' => 'Aguacates Hass',
        'descripcion' => 'Aguacates Hass maduros, perfectos para guacamole y ensaladas',
        'precio' => 65.00,
        'precio_anterior' => 0,
        'stock' => 20,
        'categoria' => 'Frutas',
        'vendedor' => 'Aguacates del Sur',
        'nombre_vendedor' => 'Aguacates del Sur',
        'imagen' => 'aguacates.jpg',
        'calificacion' => 4.8,
        'reviews' => 45,
        'origen' => 'Michoacán',
        'ciudad' => 'Uruapan',
        'estado' => 'Michoacán',
        'unidad_medida' => 'kg',
        'organico' => false,
        'descuento' => 0,
        'tags' => ['Premium', 'Cremoso', 'Nutritivo'],
        'disponible' => true
    ]
];

// Inicializar variables necesarias
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Procesar filtros
$filtros = [
    'busqueda' => $_GET['busqueda'] ?? '',
    'categoria' => $_GET['categoria'] ?? '',
    'ciudad' => $_GET['ciudad'] ?? '',
    'precio_min' => $_GET['precio_min'] ?? '',
    'precio_max' => $_GET['precio_max'] ?? '',
    'orden' => $_GET['orden'] ?? 'recientes',
    'pagina' => max(1, intval($_GET['pagina'] ?? 1))
];

// Aplicar filtros a los productos
$productosFiltrados = $productos;

// Filtro por búsqueda
if (!empty($filtros['busqueda'])) {
    $productosFiltrados = array_filter($productosFiltrados, function($producto) use ($filtros) {
        return stripos($producto['nombre'], $filtros['busqueda']) !== false ||
               stripos($producto['descripcion'], $filtros['busqueda']) !== false ||
               stripos($producto['vendedor'], $filtros['busqueda']) !== false;
    });
}

// Filtro por categoría
if (!empty($filtros['categoria'])) {
    $productosFiltrados = array_filter($productosFiltrados, function($producto) use ($filtros) {
        return $producto['categoria'] === $filtros['categoria'];
    });
}

// Filtro por precio
if (!empty($filtros['precio_min'])) {
    $productosFiltrados = array_filter($productosFiltrados, function($producto) use ($filtros) {
        return $producto['precio'] >= floatval($filtros['precio_min']);
    });
}

if (!empty($filtros['precio_max'])) {
    $productosFiltrados = array_filter($productosFiltrados, function($producto) use ($filtros) {
        return $producto['precio'] <= floatval($filtros['precio_max']);
    });
}

// Ordenar productos
switch ($filtros['orden']) {
    case 'precio_asc':
        usort($productosFiltrados, function($a, $b) {
            return $a['precio'] <=> $b['precio'];
        });
        break;
    case 'precio_desc':
        usort($productosFiltrados, function($a, $b) {
            return $b['precio'] <=> $a['precio'];
        });
        break;
    case 'popularidad':
        usort($productosFiltrados, function($a, $b) {
            return ($b['calificacion'] * $b['reviews']) <=> ($a['calificacion'] * $a['reviews']);
        });
        break;
    case 'calificacion':
        usort($productosFiltrados, function($a, $b) {
            return $b['calificacion'] <=> $a['calificacion'];
        });
        break;
    default: // recientes
        // Mantener orden original
        break;
}

$totalProductos = count($productosFiltrados);

// Paginación
$productosPorPagina = 12;
$paginaActual = $filtros['pagina'];
$totalPaginas = ceil($totalProductos / $productosPorPagina);
$offset = ($paginaActual - 1) * $productosPorPagina;

$productosPaginados = array_slice($productosFiltrados, $offset, $productosPorPagina);

// Agregar campos faltantes a los productos
foreach ($productosPaginados as &$producto) {
    if (!isset($producto['unidad_medida'])) {
        $producto['unidad_medida'] = 'kg';
    }
    if (!isset($producto['ciudad'])) {
        $ciudades = ['Guadalajara', 'Monterrey', 'Puebla', 'Querétaro', 'Morelia', 'Toluca'];
        $producto['ciudad'] = $ciudades[array_rand($ciudades)];
    }
    if (!isset($producto['estado'])) {
        $estados = ['Jalisco', 'Nuevo León', 'Puebla', 'Querétaro', 'Michoacán', 'Estado de México'];
        $producto['estado'] = $estados[array_rand($estados)];
    }
    if (!isset($producto['nombre_vendedor'])) {
        $producto['nombre_vendedor'] = $producto['vendedor'];
    }
}

// Función helper para construir URLs de paginación
function buildPaginationUrl($page) {
    $params = $_GET;
    $params['pagina'] = $page;
    return 'catalogo.php?' . http_build_query($params);
}

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos - AgroConecta</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .catalog-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .search-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            margin: -1rem 0 2rem 0;
        }

        .filter-sidebar {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .filter-section {
            margin-bottom: 1.5rem;
        }

        .filter-title {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            height: 200px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: white;
            position: relative;
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.95);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .product-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .product-description {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .product-location {
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .product-vendor {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: rgba(40, 167, 69, 0.05);
            border-radius: 10px;
        }

        .vendor-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .product-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-cart {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 0.75rem 1rem;
            font-weight: 600;
            flex-grow: 1;
            transition: all 0.3s ease;
        }

        .btn-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .btn-favorite {
            background: white;
            color: var(--danger-color);
            border: 2px solid var(--danger-color);
            border-radius: 15px;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-favorite:hover,
        .btn-favorite.active {
            background: var(--danger-color);
            color: white;
        }

        .pagination-custom {
            display: flex;
            justify-content: center;
            margin-top: 3rem;
        }

        .pagination-custom .page-link {
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            margin: 0 0.25rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination-custom .page-link:hover,
        .pagination-custom .page-item.active .page-link {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .results-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .sort-select {
            border-radius: 15px;
            border: 2px solid var(--primary-color);
            padding: 0.5rem 1rem;
            background: white;
            color: var(--primary-color);
            font-weight: 600;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(40, 167, 69, 0.1);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 25px;
            font-size: 0.8rem;
            margin: 0.25rem;
            font-weight: 600;
        }

        .filter-chip .remove {
            cursor: pointer;
            opacity: 0.7;
        }

        .filter-chip .remove:hover {
            opacity: 1;
        }

        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .no-results i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .catalog-header {
                padding: 2rem 0;
            }
            
            .search-section {
                margin: -0.5rem 0 1rem 0;
                padding: 1.5rem;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1rem;
            }
            
            .filter-sidebar {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span>🌱</span>
                AgroConecta
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="catalogo.php">
                            <i class="fas fa-store me-1"></i>
                            Catálogo
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <?php if ($user['tipo_usuario'] === 'cliente'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="carrito.php">
                                    <i class="fas fa-shopping-cart me-1"></i>
                                    Carrito
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-1"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                Iniciar Sesión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="registro.php">
                                <i class="fas fa-user-plus me-1"></i>
                                Registrarse
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <section class="catalog-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Catálogo de Productos</h1>
                    <p class="lead opacity-90">
                        Encuentra productos frescos directamente de los agricultores locales
                    </p>
                </div>
                <div class="col-lg-4 text-end">
                    <div class="text-white-50">
                        <i class="fas fa-seedling me-2"></i>
                        <?= number_format($totalProductos) ?> productos disponibles
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" action="catalogo.php">
                <div class="row align-items-end">
                    <div class="col-lg-6">
                        <label for="busqueda" class="form-label fw-bold">
                            <i class="fas fa-search text-primary me-1"></i>
                            Buscar productos
                        </label>
                        <input type="text" class="form-control form-control-lg" 
                               id="busqueda" name="busqueda" 
                               placeholder="Ej: tomates, manzanas, lechuga..."
                               value="<?= htmlspecialchars($filtros['busqueda']) ?>">
                    </div>
                    <div class="col-lg-3">
                        <label for="categoria" class="form-label fw-bold">
                            <i class="fas fa-tags text-primary me-1"></i>
                            Categoría
                        </label>
                        <select class="form-select form-select-lg" id="categoria" name="categoria">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= htmlspecialchars($categoria['categoria']) ?>"
                                        <?= $filtros['categoria'] === $categoria['categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['categoria']) ?> (<?= $categoria['cantidad'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <button type="submit" class="btn btn-lg btn-cart w-100">
                            <i class="fas fa-search me-2"></i>
                            Buscar
                        </button>
                    </div>
                </div>
                
                <!-- Active Filters -->
                <?php if ($filtros['busqueda'] || $filtros['categoria'] || $filtros['ciudad'] || $filtros['precio_min'] || $filtros['precio_max']): ?>
                    <div class="mt-3">
                        <strong class="me-2">Filtros activos:</strong>
                        <?php if ($filtros['busqueda']): ?>
                            <span class="filter-chip">
                                Búsqueda: "<?= htmlspecialchars($filtros['busqueda']) ?>"
                                <span class="remove" onclick="removeFilter('busqueda')">&times;</span>
                            </span>
                        <?php endif; ?>
                        <?php if ($filtros['categoria']): ?>
                            <span class="filter-chip">
                                Categoría: <?= htmlspecialchars($filtros['categoria']) ?>
                                <span class="remove" onclick="removeFilter('categoria')">&times;</span>
                            </span>
                        <?php endif; ?>
                        <?php if ($filtros['ciudad']): ?>
                            <span class="filter-chip">
                                Ciudad: <?= htmlspecialchars($filtros['ciudad']) ?>
                                <span class="remove" onclick="removeFilter('ciudad')">&times;</span>
                            </span>
                        <?php endif; ?>
                        <?php if ($filtros['precio_min'] || $filtros['precio_max']): ?>
                            <span class="filter-chip">
                                Precio: $<?= $filtros['precio_min'] ?: '0' ?> - $<?= $filtros['precio_max'] ?: '∞' ?>
                                <span class="remove" onclick="removeFilter('precio')">&times;</span>
                            </span>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-danger btn-sm ms-2" onclick="clearAllFilters()">
                            <i class="fas fa-times me-1"></i>
                            Limpiar filtros
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <form method="GET" action="catalogo.php" id="filterForm">
                        <!-- Mantener búsqueda actual -->
                        <input type="hidden" name="busqueda" value="<?= htmlspecialchars($filtros['busqueda']) ?>">
                        <input type="hidden" name="categoria" value="<?= htmlspecialchars($filtros['categoria']) ?>">
                        
                        <!-- Location Filter -->
                        <div class="filter-section">
                            <div class="filter-title">
                                <i class="fas fa-map-marker-alt"></i>
                                Ubicación
                            </div>
                            <div class="mb-2">
                                <select class="form-select" name="estado" onchange="loadCities(this.value)">
                                    <option value="">Todos los estados</option>
                                    <?php foreach ($ubicaciones['estados'] as $estado): ?>
                                        <option value="<?= htmlspecialchars($estado['estado']) ?>"
                                                <?= $filtros['estado'] === $estado['estado'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($estado['estado']) ?> (<?= $estado['cantidad'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <select class="form-select" name="ciudad" id="ciudadSelect">
                                    <option value="">Todas las ciudades</option>
                                    <?php if ($filtros['estado']): ?>
                                        <?php foreach ($ubicaciones['ciudades'] as $ciudad): ?>
                                            <?php if ($ciudad['estado'] === $filtros['estado']): ?>
                                                <option value="<?= htmlspecialchars($ciudad['ciudad']) ?>"
                                                        <?= $filtros['ciudad'] === $ciudad['ciudad'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($ciudad['ciudad']) ?> (<?= $ciudad['cantidad'] ?>)
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Price Filter -->
                        <div class="filter-section">
                            <div class="filter-title">
                                <i class="fas fa-dollar-sign"></i>
                                Rango de Precio
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" 
                                           name="precio_min" placeholder="Mín $"
                                           value="<?= htmlspecialchars($filtros['precio_min']) ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" 
                                           name="precio_max" placeholder="Máx $"
                                           value="<?= htmlspecialchars($filtros['precio_max']) ?>">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-cart w-100">
                            <i class="fas fa-filter me-2"></i>
                            Aplicar Filtros
                        </button>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Results Header -->
                <div class="results-header">
                    <div>
                        <strong><?= number_format($totalProductos) ?></strong> productos encontrados
                        <?php if ($filtros['busqueda']): ?>
                            para "<em><?= htmlspecialchars($filtros['busqueda']) ?></em>"
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="orden" class="form-label mb-0 fw-bold">Ordenar por:</label>
                        <select class="sort-select" id="orden" name="orden" onchange="changeSort()">
                            <option value="fecha_desc" <?= $filtros['orden'] === 'fecha_desc' ? 'selected' : '' ?>>
                                Más recientes
                            </option>
                            <option value="precio_asc" <?= $filtros['orden'] === 'precio_asc' ? 'selected' : '' ?>>
                                Menor precio
                            </option>
                            <option value="precio_desc" <?= $filtros['orden'] === 'precio_desc' ? 'selected' : '' ?>>
                                Mayor precio
                            </option>
                            <option value="nombre_asc" <?= $filtros['orden'] === 'nombre_asc' ? 'selected' : '' ?>>
                                Nombre A-Z
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (empty($productos)): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No se encontraron productos</h3>
                        <p class="text-muted">
                            Intenta ajustar los filtros de búsqueda o explorar diferentes categorías
                        </p>
                        <a href="catalogo.php" class="btn btn-cart">
                            <i class="fas fa-arrow-left me-2"></i>
                            Ver todos los productos
                        </a>
                    </div>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($productosPaginados as $producto): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <?php
                                    $iconos = [
                                        'Frutas' => '🍎',
                                        'Verduras' => '🥬',
                                        'Hortalizas' => '🥕',
                                        'Cereales' => '🌾',
                                        'Legumbres' => '🫘',
                                        'Hierbas' => '🌿'
                                    ];
                                    echo $iconos[$producto['categoria']] ?? '🌱';
                                    ?>
                                    <span class="product-badge">
                                        <?= htmlspecialchars($producto['categoria']) ?>
                                    </span>
                                </div>
                                
                                <div class="product-info">
                                    <h5 class="product-title"><?= htmlspecialchars($producto['nombre']) ?></h5>
                                    
                                    <div class="product-price">
                                        $<?= number_format($producto['precio'], 2) ?>
                                        <small class="text-muted">/ <?= htmlspecialchars($producto['unidad_medida']) ?></small>
                                    </div>
                                    
                                    <p class="product-description">
                                        <?= htmlspecialchars(substr($producto['descripcion'], 0, 100)) ?>
                                        <?= strlen($producto['descripcion']) > 100 ? '...' : '' ?>
                                    </p>
                                    
                                    <div class="product-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($producto['ciudad']) ?>, <?= htmlspecialchars($producto['estado']) ?>
                                    </div>
                                    
                                    <div class="product-vendor">
                                        <div class="vendor-avatar">
                                            <?= strtoupper(substr($producto['nombre_vendedor'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($producto['nombre_vendedor']) ?></div>
                                            <small class="text-muted">Vendedor verificado</small>
                                        </div>
                                    </div>
                                    
                                    <?php if ($isLoggedIn && $user['tipo_usuario'] === 'cliente'): ?>
                                        <div class="product-actions">
                                            <button class="btn btn-cart" 
                                                    onclick="agregarAlCarrito(<?= $producto['id'] ?>)">
                                                <i class="fas fa-cart-plus me-2"></i>
                                                Agregar al Carrito
                                            </button>
                                            <button class="btn btn-favorite" 
                                                    onclick="toggleFavorito(<?= $producto['id'] ?>)">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center">
                                            <?php if (!$isLoggedIn): ?>
                                                <a href="login.php" class="btn btn-cart w-100">
                                                    <i class="fas fa-sign-in-alt me-2"></i>
                                                    Inicia sesión para comprar
                                                </a>
                                            <?php else: ?>
                                                <div class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Solo clientes pueden comprar
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPaginas > 1): ?>
                        <nav class="pagination-custom">
                            <ul class="pagination">
                                <?php if ($filtros['pagina'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildPaginationUrl($filtros['pagina'] - 1) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $start = max(1, $filtros['pagina'] - 2);
                                $end = min($totalPaginas, $filtros['pagina'] + 2);
                                ?>

                                <?php if ($start > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildPaginationUrl(1) ?>">1</a>
                                    </li>
                                    <?php if ($start > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?= $i === $filtros['pagina'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildPaginationUrl($i) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($end < $totalPaginas): ?>
                                    <?php if ($end < $totalPaginas - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildPaginationUrl($totalPaginas) ?>"><?= $totalPaginas ?></a>
                                    </li>
                                <?php endif; ?>

                                <?php if ($filtros['pagina'] < $totalPaginas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildPaginationUrl($filtros['pagina'] + 1) ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función para construir URLs de paginación
        function buildPaginationUrl(page) {
            const params = new URLSearchParams(window.location.search);
            params.set('pagina', page);
            return 'catalogo.php?' + params.toString();
        }
        
        // Cambiar ordenamiento
        function changeSort() {
            const select = document.getElementById('orden');
            const params = new URLSearchParams(window.location.search);
            params.set('orden', select.value);
            params.delete('pagina'); // Reset to first page
            window.location.href = 'catalogo.php?' + params.toString();
        }
        
        // Remover filtro específico
        function removeFilter(filterType) {
            const params = new URLSearchParams(window.location.search);
            
            if (filterType === 'busqueda') {
                params.delete('busqueda');
            } else if (filterType === 'categoria') {
                params.delete('categoria');
            } else if (filterType === 'ciudad') {
                params.delete('ciudad');
            } else if (filterType === 'precio') {
                params.delete('precio_min');
                params.delete('precio_max');
            }
            
            params.delete('pagina');
            window.location.href = 'catalogo.php?' + params.toString();
        }
        
        // Limpiar todos los filtros
        function clearAllFilters() {
            window.location.href = 'catalogo.php';
        }
        
        // Cargar ciudades según el estado seleccionado
        function loadCities(estado) {
            const ciudadSelect = document.getElementById('ciudadSelect');
            ciudadSelect.innerHTML = '<option value="">Todas las ciudades</option>';
            
            if (!estado) return;
            
            // Aquí puedes implementar una llamada AJAX para cargar ciudades
            // Por ahora, recarga la página para aplicar el filtro
            const params = new URLSearchParams(window.location.search);
            params.set('estado', estado);
            params.delete('ciudad');
            params.delete('pagina');
            window.location.href = 'catalogo.php?' + params.toString();
        }
        
        // Agregar al carrito (solo para clientes logueados)
        function agregarAlCarrito(idProducto) {
            fetch('api/carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'agregar',
                    id: idProducto,
                    cantidad: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Producto agregado al carrito', 'success');
                } else {
                    showToast(data.message || 'Error al agregar al carrito', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al conectar con el servidor', 'error');
            });
        }
        
        // Toggle favorito
        function toggleFavorito(idProducto) {
            fetch('api/favoritos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'toggle',
                    id: idProducto
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    // Actualizar icono del botón
                    const btn = event.target.closest('.btn-favorite');
                    btn.classList.toggle('active');
                } else {
                    showToast(data.message || 'Error al actualizar favoritos', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al conectar con el servidor', 'error');
            });
        }
        
        // Sistema de notificaciones toast
        function showToast(message, type = 'info') {
            if (!document.querySelector('.toast-container')) {
                const container = document.createElement('div');
                container.className = 'toast-container position-fixed top-0 end-0 p-3';
                container.style.zIndex = '1055';
                document.body.appendChild(container);
            }

            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;

            const container = document.querySelector('.toast-container');
            container.insertAdjacentHTML('beforeend', toastHtml);
            
            const toast = container.lastElementChild;
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
    </script>
</body>
</html>