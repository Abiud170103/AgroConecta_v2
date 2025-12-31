<?php 
// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('/auth/login');
}

// Obtener datos del usuario
$user = $this->userModel->findById($_SESSION['user_id']);
$userStats = $this->userModel->getUserStats($_SESSION['user_id']);
$recentOrders = $this->orderModel->getUserRecentOrders($_SESSION['user_id'], 5);
$favoriteProducts = $this->productModel->getUserFavorites($_SESSION['user_id'], 6);
?>

<div class="user-dashboard">
    <!-- === HEADER DEL DASHBOARD === -->
    <div class="dashboard-header">
        <div class="container">
            <div class="header-content">
                <div class="user-welcome">
                    <div class="user-avatar">
                        <?php if (!empty($user['foto_perfil'])): ?>
                            <img src="<?= asset('uploads/avatars/' . $user['foto_perfil']) ?>" alt="<?= h($user['nombre']) ?>">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <div class="status-indicator online"></div>
                    </div>
                    
                    <div class="welcome-text">
                        <h1>¡Hola, <?= h($user['nombre']) ?>! 👋</h1>
                        <p class="welcome-subtitle">
                            <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                                Bienvenido a tu panel de productor. Gestiona tus productos y ventas desde aquí.
                            <?php else: ?>
                                Bienvenido a tu cuenta. Explora productos frescos del campo y gestiona tus pedidos.
                            <?php endif; ?>
                        </p>
                        
                        <div class="user-badges">
                            <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-store"></i> Productor Verificado
                                </span>
                            <?php else: ?>
                                <span class="badge badge-primary">
                                    <i class="fas fa-user"></i> Cliente Premium
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($user['email_verificado']): ?>
                                <span class="badge badge-info">
                                    <i class="fas fa-check-circle"></i> Email Verificado
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <a href="/user/profile" class="btn btn-outline-primary">
                        <i class="fas fa-user-edit"></i>
                        Editar Perfil
                    </a>
                    
                    <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                        <a href="/vendor/products/create" class="btn btn-success">
                            <i class="fas fa-plus"></i>
                            Nuevo Producto
                        </a>
                    <?php else: ?>
                        <a href="/products" class="btn btn-success">
                            <i class="fas fa-shopping-cart"></i>
                            Seguir Comprando
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- === ESTADÍSTICAS PRINCIPALES === -->
    <div class="dashboard-stats">
        <div class="container">
            <div class="stats-grid">
                <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                    <!-- Stats para vendedores -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= number_format($userStats['total_productos']) ?></h3>
                            <p>Productos Publicados</p>
                            <span class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> +<?= $userStats['productos_mes'] ?> este mes
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= number_format($userStats['total_ventas']) ?></h3>
                            <p>Ventas Totales</p>
                            <span class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> +<?= $userStats['ventas_mes'] ?> este mes
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3>$<?= number_format($userStats['ingresos_totales'], 2) ?></h3>
                            <p>Ingresos Totales</p>
                            <span class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> $<?= number_format($userStats['ingresos_mes'], 2) ?> este mes
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= number_format($userStats['calificacion_promedio'], 1) ?></h3>
                            <p>Calificación Promedio</p>
                            <span class="stat-change">
                                <i class="fas fa-trophy"></i> <?= $userStats['total_resenas'] ?> reseñas
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Stats para clientes -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= number_format($userStats['total_pedidos']) ?></h3>
                            <p>Pedidos Realizados</p>
                            <span class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> +<?= $userStats['pedidos_mes'] ?> este mes
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= number_format($userStats['productos_favoritos']) ?></h3>
                            <p>Productos Favoritos</p>
                            <span class="stat-change">
                                <i class="fas fa-bookmark"></i> En tu lista de deseos
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3>$<?= number_format($userStats['total_gastado'], 2) ?></h3>
                            <p>Total Gastado</p>
                            <span class="stat-change">
                                <i class="fas fa-wallet"></i> Ahorrado: $<?= number_format($userStats['descuentos'], 2) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= number_format($userStats['pedidos_entregados']) ?></h3>
                            <p>Pedidos Entregados</p>
                            <span class="stat-change positive">
                                <i class="fas fa-check-circle"></i> <?= number_format($userStats['tasa_entrega'], 1) ?>% exitoso
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- === CONTENIDO PRINCIPAL DEL DASHBOARD === -->
    <div class="dashboard-content">
        <div class="container">
            <div class="content-grid">
                <!-- === COLUMNA PRINCIPAL === -->
                <div class="main-content">
                    
                    <!-- Pedidos Recientes / Ventas Recientes -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>
                                <i class="fas fa-clock"></i>
                                <?= $user['tipo_usuario'] === 'vendedor' ? 'Ventas Recientes' : 'Pedidos Recientes' ?>
                            </h2>
                            <a href="<?= $user['tipo_usuario'] === 'vendedor' ? '/vendor/orders' : '/user/orders' ?>" class="btn btn-outline-primary btn-sm">
                                Ver Todos
                            </a>
                        </div>
                        
                        <div class="orders-list">
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <div class="order-info">
                                                <strong>#<?= $order['numero_pedido'] ?></strong>
                                                <span class="order-date">
                                                    <i class="far fa-calendar"></i>
                                                    <?= date('d/m/Y', strtotime($order['fecha_creacion'])) ?>
                                                </span>
                                            </div>
                                            
                                            <div class="order-status">
                                                <span class="status-badge status-<?= strtolower($order['estado']) ?>">
                                                    <?= ucfirst($order['estado']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="order-details">
                                            <div class="order-products">
                                                <i class="fas fa-box"></i>
                                                <?= $order['total_productos'] ?> productos
                                            </div>
                                            
                                            <div class="order-total">
                                                <i class="fas fa-dollar-sign"></i>
                                                $<?= number_format($order['total'], 2) ?>
                                            </div>
                                            
                                            <?php if ($user['tipo_usuario'] === 'cliente'): ?>
                                                <div class="order-customer">
                                                    <i class="fas fa-user"></i>
                                                    Para: <?= h($order['direccion_entrega']) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="order-customer">
                                                    <i class="fas fa-user"></i>
                                                    Cliente: <?= h($order['cliente_nombre']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="order-actions">
                                            <a href="/orders/<?= $order['id'] ?>" class="btn btn-outline-primary btn-sm">
                                                Ver Detalles
                                            </a>
                                            
                                            <?php if ($order['estado'] === 'pendiente'): ?>
                                                <?php if ($user['tipo_usuario'] === 'cliente'): ?>
                                                    <a href="/orders/<?= $order['id'] ?>/cancel" class="btn btn-outline-danger btn-sm">
                                                        Cancelar
                                                    </a>
                                                <?php else: ?>
                                                    <a href="/vendor/orders/<?= $order['id'] ?>/process" class="btn btn-success btn-sm">
                                                        Procesar
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-box-open"></i>
                                    <h3>No tienes <?= $user['tipo_usuario'] === 'vendedor' ? 'ventas' : 'pedidos' ?> recientes</h3>
                                    <p>
                                        <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                                            Cuando tengas ventas, aparecerán aquí para que puedas gestionarlas fácilmente.
                                        <?php else: ?>
                                            ¡Explora nuestros productos frescos del campo y realiza tu primer pedido!
                                        <?php endif; ?>
                                    </p>
                                    
                                    <?php if ($user['tipo_usuario'] === 'cliente'): ?>
                                        <a href="/products" class="btn btn-success">
                                            <i class="fas fa-shopping-cart"></i>
                                            Explorar Productos
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($user['tipo_usuario'] === 'cliente' && !empty($favoriteProducts)): ?>
                        <!-- Productos Favoritos -->
                        <div class="dashboard-section">
                            <div class="section-header">
                                <h2>
                                    <i class="fas fa-heart"></i>
                                    Tus Productos Favoritos
                                </h2>
                                <a href="/user/favorites" class="btn btn-outline-primary btn-sm">
                                    Ver Todos
                                </a>
                            </div>
                            
                            <div class="products-grid">
                                <?php foreach ($favoriteProducts as $product): ?>
                                    <?php include 'app/views/components/product-card.php'; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                </div>
                
                <!-- === BARRA LATERAL === -->
                <div class="sidebar">
                    
                    <!-- Navegación Rápida -->
                    <div class="sidebar-section">
                        <h3>Navegación Rápida</h3>
                        
                        <div class="quick-nav">
                            <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                                <!-- Menú para vendedores -->
                                <a href="/vendor/products" class="nav-item">
                                    <div class="nav-icon">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                    <div class="nav-content">
                                        <strong>Mis Productos</strong>
                                        <small><?= $userStats['total_productos'] ?> productos publicados</small>
                                    </div>
                                    <i class="fas fa-chevron-right nav-arrow"></i>
                                </a>
                                
                                <a href="/vendor/orders" class="nav-item">
                                    <div class="nav-icon">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>
                                    <div class="nav-content">
                                        <strong>Gestionar Ventas</strong>
                                        <small><?= $userStats['pedidos_pendientes'] ?> pedidos pendientes</small>
                                    </div>
                                    <i class="fas fa-chevron-right nav-arrow"></i>
                                </a>
                                
                                <a href="/vendor/analytics" class="nav-item">
                                    <div class="nav-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="nav-content">
                                        <strong>Analíticas</strong>
                                        <small>Ver reportes de ventas</small>
                                    </div>
                                    <i class="fas fa-chevron-right nav-arrow"></i>
                                </a>
                                
                                <a href="/vendor/profile" class="nav-item">
                                    <div class="nav-icon">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div class="nav-content">
                                        <strong>Mi Tienda</strong>
                                        <small>Configurar perfil público</small>
                                    </div>
                                    <i class="fas fa-chevron-right nav-arrow"></i>
                                </a>
                            <?php else: ?>
                                <!-- Menú para clientes -->
                                <a href="/user/orders" class="nav-item">
                                    <div class="nav-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="nav-content">
                                        <strong>Mis Pedidos</strong>
                                        <small><?= $userStats['pedidos_activos'] ?> pedidos activos</small>
                                    </div>
                                    <i class="fas fa-chevron-right nav-arrow"></i>
                                </a>
                                
                                <a href="/user/favorites" class="nav-item">
                                    <div class="nav-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <div class="nav-content">
                                        <strong>Favoritos</strong>
                                        <small><?= $userStats['productos_favoritos'] ?> productos guardados</small>
                                    </div>
                                    <i class="fas fa-chevron-right nav-arrow"></i>
                                </a>
                                
                                <a href="/user/addresses" class="nav-item">
                                    <div class="nav-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="nav-content">
                                        <strong>Direcciones</strong>
                                        <small>Gestionar direcciones de entrega</small>
                                    </div>
                                    <i class="fas fa-chevron-right nav-arrow"></i>
                                </a>
                                
                                <a href="/user/payment-methods" class="nav-item">
                                    <div class="nav-icon">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="nav-content">
                                        <strong>Métodos de Pago</strong>
                                        <small>Tarjetas y formas de pago</small>
                                    </div>
                                    <i class="fas fa-chevron-right nav-arrow"></i>
                                </a>
                            <?php endif; ?>
                            
                            <a href="/user/profile" class="nav-item">
                                <div class="nav-icon">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div class="nav-content">
                                    <strong>Configuración</strong>
                                    <small>Perfil y preferencias</small>
                                </div>
                                <i class="fas fa-chevron-right nav-arrow"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Notificaciones Recientes -->
                    <?php if (!empty($notifications = getRecentNotifications($_SESSION['user_id']))): ?>
                        <div class="sidebar-section">
                            <h3>
                                Notificaciones
                                <span class="notification-count"><?= count($notifications) ?></span>
                            </h3>
                            
                            <div class="notifications-list">
                                <?php foreach (array_slice($notifications, 0, 3) as $notification): ?>
                                    <div class="notification-item <?= $notification['leida'] ? '' : 'unread' ?>">
                                        <div class="notification-icon">
                                            <i class="<?= $notification['icono'] ?>"></i>
                                        </div>
                                        <div class="notification-content">
                                            <p><?= h($notification['mensaje']) ?></p>
                                            <small><?= timeAgo($notification['fecha']) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <a href="/user/notifications" class="btn btn-outline-primary btn-sm btn-block">
                                Ver Todas las Notificaciones
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Tips y Consejos -->
                    <div class="sidebar-section">
                        <h3>
                            <i class="fas fa-lightbulb"></i>
                            Tips para Ti
                        </h3>
                        
                        <div class="tips-list">
                            <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                                <div class="tip-item">
                                    <div class="tip-icon">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <div class="tip-content">
                                        <strong>Fotos de calidad</strong>
                                        <p>Los productos con mejores fotos venden 3x más.</p>
                                    </div>
                                </div>
                                
                                <div class="tip-item">
                                    <div class="tip-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="tip-content">
                                        <strong>Responde rápido</strong>
                                        <p>Responder en menos de 2 horas mejora tu calificación.</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="tip-item">
                                    <div class="tip-icon">
                                        <i class="fas fa-leaf"></i>
                                    </div>
                                    <div class="tip-content">
                                        <strong>Productos de temporada</strong>
                                        <p>Los productos de temporada son más frescos y económicos.</p>
                                    </div>
                                </div>
                                
                                <div class="tip-item">
                                    <div class="tip-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <div class="tip-content">
                                        <strong>Guarda tus favoritos</strong>
                                        <p>Te notificaremos cuando bajen de precio.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="dashboardLoading" class="loading-overlay" style="display: none;">
    <div class="loading-spinner">
        <div class="spinner-border" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <p>Actualizando dashboard...</p>
    </div>
</div>

<script src="<?= asset('js/dashboard.js') ?>"></script>