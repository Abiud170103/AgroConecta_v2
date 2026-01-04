<?php
/**
 * Dashboard para Clientes - AgroConecta
 * Panel de control completo para explorar productos y gestionar pedidos
 */

// Verificar autenticación
if (!SessionManager::isLoggedIn() || $user['tipo_usuario'] !== 'cliente') {
    header('Location: ../../public/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - AgroConecta</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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

        .sidebar {
            background: linear-gradient(180deg, var(--dark-color) 0%, #34495e  100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
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
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }

        .welcome-header {
            background: linear-gradient(135deg, var(--info-color) 0%, var(--secondary-color) 100%);
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
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon.primary { background: rgba(40, 167, 69, 0.1); color: var(--primary-color); }
        .stat-icon.info { background: rgba(23, 162, 184, 0.1); color: var(--info-color); }
        .stat-icon.warning { background: rgba(255, 193, 7, 0.1); color: var(--warning-color); }
        .stat-icon.success { background: rgba(40, 167, 69, 0.1); color: var(--success-color); }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .featured-products {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .product-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            height: 200px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-title {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .product-location {
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .recent-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .badge-pendiente { background: rgba(255, 193, 7, 0.2); color: #856404; }
        .badge-confirmado { background: rgba(23, 162, 184, 0.2); color: #0c5460; }
        .badge-enviado { background: rgba(40, 167, 69, 0.2); color: #155724; }
        .badge-entregado { background: rgba(40, 167, 69, 0.2); color: #155724; }

        .quick-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .action-btn {
            background: linear-gradient(135deg, var(--info-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
            color: white;
        }

        .action-btn.primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        .action-btn.primary:hover {
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .cart-badge {
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
            position: absolute;
            top: -8px;
            right: -8px;
            min-width: 20px;
            text-align: center;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .category-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--dark-color);
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            color: var(--primary-color);
        }

        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
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
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="../../public/index.php" class="sidebar-brand">
                <span>🌱</span>
                AgroConecta
            </a>
            <div class="mt-2">
                <small class="text-light opacity-75">Mi Cuenta</small>
                <div class="fw-bold"><?= htmlspecialchars($user['nombre'] . ' ' . $user['apellido']) ?></div>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <a href="#" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="catalogo.php" class="nav-link">
                <i class="fas fa-store"></i>
                Catálogo
            </a>
            <a href="carrito.php" class="nav-link position-relative">
                <i class="fas fa-shopping-cart"></i>
                Mi Carrito
                <?php if ($itemsCarrito > 0): ?>
                    <span class="cart-badge"><?= $itemsCarrito ?></span>
                <?php endif; ?>
            </a>
            <a href="mis-pedidos.php" class="nav-link">
                <i class="fas fa-box"></i>
                Mis Pedidos
            </a>
            <a href="../../public/profile.php" class="nav-link">
                <i class="fas fa-user"></i>
                Mi Perfil
            </a>
            <a href="favoritos.php" class="nav-link">
                <i class="fas fa-heart"></i>
                Favoritos
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
                    <h1 class="mb-2">¡Hola, <?= htmlspecialchars($user['nombre']) ?>! 🛒</h1>
                    <p class="mb-0 opacity-90">
                        Descubre productos frescos directamente de los agricultores
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-white-50">
                        <?= date('d/m/Y') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (SessionManager::hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= SessionManager::getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="catalogo.php" class="action-btn primary">
                <i class="fas fa-search"></i>
                Explorar Catálogo
            </a>
            <?php if ($itemsCarrito > 0): ?>
                <a href="carrito.php" class="action-btn">
                    <i class="fas fa-shopping-cart"></i>
                    Finalizar Compra (<?= $itemsCarrito ?> items)
                </a>
            <?php endif; ?>
            <a href="mis-pedidos.php?estado=activo" class="action-btn">
                <i class="fas fa-truck"></i>
                Rastrear Pedidos
            </a>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-value"><?= $statsPedidos['total_pedidos'] ?></div>
                <div class="stat-label">Pedidos Realizados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">$<?= number_format($statsPedidos['gasto_total'], 0) ?></div>
                <div class="stat-label">Total Gastado</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-value"><?= $statsFavoritos['productos_favoritos'] ?></div>
                <div class="stat-label">Productos Favoritos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?= $itemsCarrito ?></div>
                <div class="stat-label">Items en Carrito</div>
            </div>
        </div>

        <!-- Categorías Populares -->
        <div class="featured-products">
            <h5 class="section-title">
                <i class="fas fa-tags text-primary"></i>
                Categorías Populares
            </h5>
            
            <div class="categories-grid">
                <?php foreach ($categoriasPopulares as $categoria): ?>
                    <a href="catalogo.php?categoria=<?= urlencode($categoria['categoria']) ?>" class="category-card">
                        <div class="category-icon">
                            <?php
                            $iconos = [
                                'Frutas' => '🍎',
                                'Verduras' => '🥬',
                                'Hortalizas' => '🥕',
                                'Cereales' => '🌾',
                                'Legumbres' => '🫘',
                                'Hierbas' => '🌿'
                            ];
                            echo $iconos[$categoria['categoria']] ?? '🌱';
                            ?>
                        </div>
                        <div class="fw-bold"><?= htmlspecialchars($categoria['categoria']) ?></div>
                        <small class="text-muted"><?= $categoria['cantidad'] ?> productos disponibles</small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row">
            <!-- Productos Destacados -->
            <div class="col-lg-8">
                <div class="featured-products">
                    <h5 class="section-title">
                        <i class="fas fa-star text-warning"></i>
                        Productos Destacados
                    </h5>
                    
                    <?php if (empty($productosDestacados)): ?>
                        <div class="no-data">
                            <i class="fas fa-seedling"></i>
                            <p>Pronto habrá productos disponibles</p>
                            <a href="catalogo.php" class="action-btn primary">
                                <i class="fas fa-search"></i>
                                Explorar Catálogo
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach (array_slice($productosDestacados, 0, 6) as $producto): ?>
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <div class="product-card card">
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
                                        </div>
                                        <div class="product-info">
                                            <div class="product-title"><?= htmlspecialchars($producto['nombre']) ?></div>
                                            <div class="product-price">
                                                $<?= number_format($producto['precio'], 2) ?>
                                                <small class="text-muted">/ <?= htmlspecialchars($producto['unidad_medida']) ?></small>
                                            </div>
                                            <div class="product-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?= htmlspecialchars($producto['ciudad']) ?>, <?= htmlspecialchars($producto['estado']) ?>
                                            </div>
                                            <div class="mt-2 d-flex gap-2">
                                                <button class="btn btn-outline-primary btn-sm flex-grow-1" 
                                                        onclick="agregarAlCarrito(<?= $producto['id_producto'] ?>)">
                                                    <i class="fas fa-cart-plus"></i>
                                                    Agregar
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="toggleFavorito(<?= $producto['id_producto'] ?>)">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="catalogo.php" class="action-btn primary">
                                <i class="fas fa-store"></i>
                                Ver Todo el Catálogo
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pedidos Recientes -->
            <div class="col-lg-4">
                <div class="recent-section">
                    <h5 class="section-title">
                        <i class="fas fa-history text-info"></i>
                        Mis Pedidos Recientes
                    </h5>
                    
                    <?php if (empty($pedidosRecientes)): ?>
                        <div class="no-data">
                            <i class="fas fa-box-open"></i>
                            <p>Aún no tienes pedidos</p>
                            <small class="text-muted">¡Explora nuestro catálogo y haz tu primera compra!</small>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($pedidosRecientes, 0, 5) as $pedido): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <div class="fw-bold">Pedido #<?= $pedido['id_pedido'] ?></div>
                                    <small class="text-muted">$<?= number_format($pedido['total'], 2) ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="badge badge-<?= strtolower($pedido['estado']) ?>">
                                        <?= ucfirst($pedido['estado']) ?>
                                    </div>
                                    <small class="d-block text-muted"><?= date('d/m', strtotime($pedido['fecha_creacion'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-3">
                            <a href="mis-pedidos.php" class="btn btn-outline-primary btn-sm">
                                Ver Todos los Pedidos
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($recomendaciones)): ?>
        <!-- Recomendaciones Personalizadas -->
        <div class="featured-products">
            <h5 class="section-title">
                <i class="fas fa-magic text-success"></i>
                Recomendado Para Ti
                <small class="text-muted">Basado en tus compras anteriores</small>
            </h5>
            
            <div class="row">
                <?php foreach (array_slice($recomendaciones, 0, 4) as $producto): ?>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="product-card card">
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
                            </div>
                            <div class="product-info">
                                <div class="product-title"><?= htmlspecialchars($producto['nombre']) ?></div>
                                <div class="product-price">
                                    $<?= number_format($producto['precio'], 2) ?>
                                    <small class="text-muted">/ <?= htmlspecialchars($producto['unidad_medida']) ?></small>
                                </div>
                                <button class="btn btn-outline-primary btn-sm w-100 mt-2" 
                                        onclick="agregarAlCarrito(<?= $producto['id_producto'] ?>)">
                                    <i class="fas fa-cart-plus"></i>
                                    Agregar al Carrito
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Agregar al carrito
        function agregarAlCarrito(idProducto) {
            fetch('api/carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'agregar',
                    id_producto: idProducto,
                    cantidad: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar contador del carrito
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.items_carrito;
                    } else if (data.items_carrito > 0) {
                        // Crear badge si no existe
                        const cartLink = document.querySelector('a[href="carrito.php"]');
                        if (cartLink) {
                            cartLink.classList.add('position-relative');
                            cartLink.innerHTML += `<span class="cart-badge">${data.items_carrito}</span>`;
                        }
                    }
                    
                    // Mostrar mensaje de éxito
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
                    id_producto: idProducto
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    // Actualizar UI según sea necesario
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
            // Crear toast si no existe el contenedor
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

            // Limpiar después de mostrar
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        // Auto-refresh dashboard every 10 minutes
        setTimeout(() => {
            location.reload();
        }, 10 * 60 * 1000);

        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }
    </script>
</body>
</html>