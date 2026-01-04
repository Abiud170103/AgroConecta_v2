<?php
/**
 * Favoritos - Clientes
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

// Datos de ejemplo para favoritos
$favoritos = [
    [
        'id' => 1,
        'nombre' => 'Tomates Cherry Orgánicos',
        'descripcion' => 'Tomates cherry cultivados de forma orgánica, perfectos para ensaladas y snacks saludables',
        'precio' => 45.50,
        'precio_anterior' => 52.00,
        'stock' => 25,
        'categoria' => 'Verduras',
        'vendedor' => 'Granja Verde SA',
        'imagen' => 'tomates-cherry.jpg',
        'calificacion' => 4.8,
        'reviews' => 24,
        'origen' => 'Michoacán',
        'organico' => true,
        'descuento' => 12,
        'disponible' => true,
        'fecha_agregado' => '2024-12-20',
        'ultimo_precio' => 45.50,
        'precio_minimo' => 42.00,
        'precio_maximo' => 55.00
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
        'imagen' => 'zanahorias-baby.jpg',
        'calificacion' => 4.7,
        'reviews' => 31,
        'origen' => 'Guanajuato',
        'organico' => true,
        'descuento' => 10,
        'disponible' => true,
        'fecha_agregado' => '2024-12-22',
        'ultimo_precio' => 28.75,
        'precio_minimo' => 26.00,
        'precio_maximo' => 34.00
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
        'imagen' => 'brocoli.jpg',
        'calificacion' => 4.9,
        'reviews' => 22,
        'origen' => 'Estado de México',
        'organico' => true,
        'descuento' => 12,
        'disponible' => true,
        'fecha_agregado' => '2024-12-25',
        'ultimo_precio' => 42.00,
        'precio_minimo' => 38.00,
        'precio_maximo' => 48.00
    ],
    [
        'id' => 6,
        'nombre' => 'Aguacates Hass',
        'descripcion' => 'Aguacates Hass maduros, perfectos para guacamole y ensaladas',
        'precio' => 65.00,
        'precio_anterior' => 0,
        'stock' => 0,
        'categoria' => 'Frutas',
        'vendedor' => 'Aguacates del Sur',
        'imagen' => 'aguacates.jpg',
        'calificacion' => 4.8,
        'reviews' => 45,
        'origen' => 'Michoacán',
        'organico' => false,
        'descuento' => 0,
        'disponible' => false,
        'fecha_agregado' => '2024-12-28',
        'ultimo_precio' => 65.00,
        'precio_minimo' => 60.00,
        'precio_maximo' => 70.00
    ]
];

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos - AgroConecta</title>
    
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

        .favorite-card {
            border: 1px solid var(--border-color);
            border-radius: 15px;
            transition: all 0.3s ease;
            overflow: hidden;
            background: var(--bg-primary);
            position: relative;
        }

        .favorite-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .favorite-card.out-of-stock {
            opacity: 0.7;
        }

        .favorite-card.out-of-stock::before {
            content: 'Sin Stock';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            background: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 8px 40px;
            font-weight: bold;
            z-index: 10;
            font-size: 1.2rem;
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
            position: relative;
            overflow: hidden;
        }

        .discount-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #e74c3c;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .organic-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #27ae60;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .favorite-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: #e74c3c;
        }

        .favorite-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .price-tracking {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 10px;
            margin: 10px 0;
            font-size: 0.85rem;
        }

        .price-trend {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .price-up {
            color: #e74c3c;
        }

        .price-down {
            color: #27ae60;
        }

        .rating-stars {
            color: #ffc107;
        }

        .price-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }

        .current-price {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .old-price {
            font-size: 1rem;
            color: var(--text-secondary);
            text-decoration: line-through;
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 20px;
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

        .filter-buttons {
            margin-bottom: 20px;
        }

        .filter-btn {
            border: 1px solid var(--border-color);
            background: var(--bg-primary);
            border-radius: 25px;
            padding: 8px 20px;
            margin: 0 5px 10px 0;
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .bulk-actions {
            background: var(--bg-primary);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .wishlist-tools {
            background: var(--bg-primary);
            border-radius: 15px;
            padding: 20px;
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
                        <a class="nav-link" href="mis-pedidos.php">
                            <i class="fas fa-receipt me-1"></i>Mis Pedidos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="favoritos.php">
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
                            <i class="fas fa-heart text-danger me-2"></i>
                            Mis Productos Favoritos
                        </h2>
                        <p class="text-muted mb-0">Guarda y monitorea tus productos favoritos</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#shareModal">
                            <i class="fas fa-share me-1"></i>
                            Compartir Lista
                        </button>
                        <a href="catalogo.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Explorar Productos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <?php if (!empty($favoritos)): ?>
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <h3><?php echo count($favoritos); ?></h3>
                        <p class="mb-0">Productos Favoritos</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <h3><?php echo count(array_filter($favoritos, function($f) { return $f['disponible']; })); ?></h3>
                        <p class="mb-0">Disponibles</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <h3><?php echo count(array_filter($favoritos, function($f) { return $f['descuento'] > 0; })); ?></h3>
                        <p class="mb-0">Con Descuento</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <h3>$<?php echo number_format(array_sum(array_column($favoritos, 'precio')), 2); ?></h3>
                        <p class="mb-0">Valor Total</p>
                    </div>
                </div>
            </div>

            <!-- Wishlist Tools -->
            <div class="wishlist-tools">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-3">
                            <i class="fas fa-tools me-2"></i>
                            Herramientas de Lista
                        </h6>
                        <div class="filter-buttons">
                            <button class="filter-btn active" data-filter="todos">
                                <i class="fas fa-list me-1"></i>Todos
                            </button>
                            <button class="filter-btn" data-filter="disponibles">
                                <i class="fas fa-check-circle me-1"></i>Disponibles
                            </button>
                            <button class="filter-btn" data-filter="descuentos">
                                <i class="fas fa-tag me-1"></i>Con Descuento
                            </button>
                            <button class="filter-btn" data-filter="organicos">
                                <i class="fas fa-leaf me-1"></i>Orgánicos
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">
                            <i class="fas fa-bolt me-2"></i>
                            Acciones Rápidas
                        </h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-primary btn-sm" onclick="agregarTodosAlCarrito()">
                                <i class="fas fa-cart-plus me-1"></i>
                                Agregar Disponibles al Carrito
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="crearListaDeCompras()">
                                <i class="fas fa-list me-1"></i>
                                Crear Lista de Compras
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="limpiarFavoritos()">
                                <i class="fas fa-trash me-1"></i>
                                Limpiar Lista
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Favorites Grid -->
        <div class="row">
            <?php if (empty($favoritos)): ?>
                <div class="col-12">
                    <div class="content-card p-5">
                        <div class="empty-state">
                            <i class="fas fa-heart"></i>
                            <h3>No tienes productos favoritos</h3>
                            <p class="mb-4">Explora nuestro catálogo y guarda los productos que más te gusten</p>
                            <a href="catalogo.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-store me-2"></i>
                                Explorar Catálogo
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($favoritos as $favorito): ?>
                    <div class="col-lg-3 col-md-6 mb-4 favorite-item" 
                         data-disponible="<?php echo $favorito['disponible'] ? 'true' : 'false'; ?>"
                         data-descuento="<?php echo $favorito['descuento'] > 0 ? 'true' : 'false'; ?>"
                         data-organico="<?php echo $favorito['organico'] ? 'true' : 'false'; ?>">
                        <div class="card favorite-card <?php echo !$favorito['disponible'] ? 'out-of-stock' : ''; ?>">
                            <div class="product-image">
                                <?php if ($favorito['descuento'] > 0): ?>
                                    <div class="discount-badge">-<?php echo $favorito['descuento']; ?>%</div>
                                <?php endif; ?>
                                
                                <?php if ($favorito['organico']): ?>
                                    <div class="organic-badge">
                                        <i class="fas fa-leaf me-1"></i>Orgánico
                                    </div>
                                <?php endif; ?>
                                
                                <i class="fas fa-seedling"></i>
                                
                                <button class="favorite-btn" onclick="eliminarDeFavoritos(<?php echo $favorito['id']; ?>)" title="Eliminar de favoritos">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title mb-2"><?php echo htmlspecialchars($favorito['nombre']); ?></h5>
                                
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rating-stars me-2">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $favorito['calificacion'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted">(<?php echo $favorito['reviews']; ?>)</small>
                                </div>
                                
                                <p class="card-text text-muted small mb-3">
                                    <?php echo htmlspecialchars($favorito['descripcion']); ?>
                                </p>
                                
                                <div class="price-section">
                                    <span class="current-price">$<?php echo number_format($favorito['precio'], 2); ?></span>
                                    <?php if ($favorito['precio_anterior'] > 0): ?>
                                        <span class="old-price">$<?php echo number_format($favorito['precio_anterior'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Price Tracking -->
                                <div class="price-tracking">
                                    <small class="text-muted d-block mb-1">Seguimiento de precio:</small>
                                    <div class="price-trend">
                                        <?php if ($favorito['precio'] < $favorito['ultimo_precio']): ?>
                                            <i class="fas fa-arrow-down price-down"></i>
                                            <small class="price-down">Bajó $<?php echo number_format($favorito['ultimo_precio'] - $favorito['precio'], 2); ?></small>
                                        <?php elseif ($favorito['precio'] > $favorito['ultimo_precio']): ?>
                                            <i class="fas fa-arrow-up price-up"></i>
                                            <small class="price-up">Subió $<?php echo number_format($favorito['precio'] - $favorito['ultimo_precio'], 2); ?></small>
                                        <?php else: ?>
                                            <i class="fas fa-minus text-muted"></i>
                                            <small class="text-muted">Sin cambios</small>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        Min: $<?php echo number_format($favorito['precio_minimo'], 2); ?> | 
                                        Max: $<?php echo number_format($favorito['precio_maximo'], 2); ?>
                                    </small>
                                </div>
                                
                                <small class="text-muted d-block mb-3">
                                    <i class="fas fa-store me-1"></i><?php echo $favorito['vendedor']; ?> 
                                    <span class="ms-2">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo $favorito['origen']; ?>
                                    </span>
                                </small>
                                
                                <small class="text-muted d-block mb-3">
                                    <i class="fas fa-heart me-1"></i>
                                    Agregado el <?php echo date('d/m/Y', strtotime($favorito['fecha_agregado'])); ?>
                                </small>
                                
                                <?php if ($favorito['disponible'] && $favorito['stock'] > 0): ?>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-primary" onclick="agregarAlCarrito(<?php echo $favorito['id']; ?>)">
                                            <i class="fas fa-cart-plus me-2"></i>
                                            Agregar al Carrito
                                        </button>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-outline-success btn-sm flex-fill" onclick="verDetalle(<?php echo $favorito['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>
                                                Ver
                                            </button>
                                            <button class="btn btn-outline-info btn-sm flex-fill" onclick="notificarPrecio(<?php echo $favorito['id']; ?>)">
                                                <i class="fas fa-bell me-1"></i>
                                                Alerta
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-secondary" disabled>
                                            <i class="fas fa-times me-2"></i>
                                            Sin Stock
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="notificarDisponibilidad(<?php echo $favorito['id']; ?>)">
                                            <i class="fas fa-bell me-1"></i>
                                            Notificar cuando esté disponible
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-share me-2"></i>
                        Compartir Lista de Favoritos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Comparte tu lista de productos favoritos con amigos y familiares:</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="shareUrl" value="https://agroconecta.com/lista/<?php echo $user['id']; ?>" readonly>
                        <button class="btn btn-outline-primary" onclick="copiarUrl()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" onclick="compartirWhatsApp()">
                            <i class="fab fa-whatsapp me-1"></i>WhatsApp
                        </button>
                        <button class="btn btn-info" onclick="compartirTwitter()">
                            <i class="fab fa-twitter me-1"></i>Twitter
                        </button>
                        <button class="btn btn-primary" onclick="compartirFacebook()">
                            <i class="fab fa-facebook me-1"></i>Facebook
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Filter products
                const filter = this.dataset.filter;
                const items = document.querySelectorAll('.favorite-item');
                
                items.forEach(item => {
                    let show = false;
                    
                    if (filter === 'todos') {
                        show = true;
                    } else if (filter === 'disponibles') {
                        show = item.dataset.disponible === 'true';
                    } else if (filter === 'descuentos') {
                        show = item.dataset.descuento === 'true';
                    } else if (filter === 'organicos') {
                        show = item.dataset.organico === 'true';
                    }
                    
                    if (show) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        function eliminarDeFavoritos(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este producto de tus favoritos?')) {
                const card = document.querySelector(`[onclick*="${id}"]`).closest('.favorite-item');
                
                card.style.transition = 'all 0.3s ease';
                card.style.transform = 'scale(0.8)';
                card.style.opacity = '0';
                
                setTimeout(() => {
                    card.remove();
                    showNotification('Producto eliminado de favoritos', 'info');
                    
                    // Check if no favorites left
                    if (document.querySelectorAll('.favorite-item').length === 0) {
                        location.reload();
                    }
                }, 300);
            }
        }

        function agregarAlCarrito(id) {
            showNotification('Producto agregado al carrito', 'success');
            // Actualizar contador del carrito si existe
            const counter = document.querySelector('.badge');
            if (counter) {
                counter.textContent = parseInt(counter.textContent) + 1;
            }
        }

        function verDetalle(id) {
            showNotification('Abriendo detalles del producto...', 'info');
            setTimeout(() => {
                alert('Vista detallada del producto #' + id + ' - Funcionalidad en desarrollo');
            }, 1000);
        }

        function notificarPrecio(id) {
            const precio = prompt('¿A qué precio quieres que te notifiquemos?');
            if (precio) {
                showNotification(`Alerta de precio configurada: $${precio}`, 'success');
            }
        }

        function notificarDisponibilidad(id) {
            showNotification('Te notificaremos cuando esté disponible', 'success');
        }

        function agregarTodosAlCarrito() {
            const disponibles = document.querySelectorAll('[data-disponible="true"]').length;
            if (disponibles === 0) {
                showNotification('No hay productos disponibles para agregar', 'warning');
                return;
            }
            
            if (confirm(`¿Agregar ${disponibles} productos disponibles al carrito?`)) {
                showNotification(`${disponibles} productos agregados al carrito`, 'success');
            }
        }

        function crearListaDeCompras() {
            showNotification('Creando lista de compras...', 'info');
            setTimeout(() => {
                alert('Lista de compras creada - Funcionalidad en desarrollo');
            }, 1000);
        }

        function limpiarFavoritos() {
            if (confirm('¿Estás seguro de que quieres eliminar todos los favoritos?')) {
                showNotification('Limpiando lista de favoritos...', 'info');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            }
        }

        function copiarUrl() {
            const url = document.getElementById('shareUrl').value;
            navigator.clipboard.writeText(url).then(() => {
                showNotification('URL copiada al portapapeles', 'success');
            });
        }

        function compartirWhatsApp() {
            const url = encodeURIComponent('¡Mira mi lista de productos favoritos en AgroConecta! https://agroconecta.com/lista/<?php echo $user['id']; ?>');
            window.open(`https://wa.me/?text=${url}`, '_blank');
        }

        function compartirTwitter() {
            const text = encodeURIComponent('Descubre productos frescos en mi lista de favoritos de AgroConecta');
            const url = encodeURIComponent('https://agroconecta.com/lista/<?php echo $user['id']; ?>');
            window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
        }

        function compartirFacebook() {
            const url = encodeURIComponent('https://agroconecta.com/lista/<?php echo $user['id']; ?>');
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
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
            const cards = document.querySelectorAll('.favorite-card');
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