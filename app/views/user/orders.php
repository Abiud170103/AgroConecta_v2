<?php 
// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('/auth/login');
}

// Obtener parámetros de filtrado y paginación
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$limit = 10;
$offset = ($page - 1) * $limit;

// Obtener pedidos del usuario con filtros
$orders = $this->orderModel->getUserOrders($_SESSION['user_id'], [
    'status' => $status,
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
    'search' => $search,
    'limit' => $limit,
    'offset' => $offset
]);

$totalOrders = $this->orderModel->getUserOrdersCount($_SESSION['user_id'], [
    'status' => $status,
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
    'search' => $search
]);

$totalPages = ceil($totalOrders / $limit);

// Obtener estadísticas del usuario
$orderStats = $this->orderModel->getUserOrderStats($_SESSION['user_id']);
$user = $this->userModel->findById($_SESSION['user_id']);

// Estados de pedidos disponibles
$orderStatuses = [
    'all' => 'Todos los pedidos',
    'pendiente' => 'Pendientes',
    'confirmado' => 'Confirmados',
    'preparando' => 'En preparación',
    'enviado' => 'Enviados',
    'entregado' => 'Entregados',
    'cancelado' => 'Cancelados'
];
?>

<div class="user-orders">
    <!-- === BREADCRUMB === -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/user/dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-shopping-cart"></i> Mis Pedidos
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- === HEADER DE PEDIDOS === -->
    <div class="orders-header">
        <div class="container">
            <div class="header-content">
                <div class="page-title">
                    <h1>
                        <i class="fas fa-shopping-cart"></i>
                        Mis Pedidos
                    </h1>
                    <p class="subtitle">Gestiona y revisa el estado de todos tus pedidos</p>
                </div>
                
                <div class="header-actions">
                    <a href="/products" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        Hacer Nuevo Pedido
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- === ESTADÍSTICAS DE PEDIDOS === -->
    <div class="orders-stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= number_format($orderStats['total_pedidos']) ?></h3>
                        <p>Pedidos Totales</p>
                        <small class="text-muted">Desde <?= date('M Y', strtotime($user['fecha_registro'])) ?></small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= number_format($orderStats['pedidos_pendientes']) ?></h3>
                        <p>Pedidos Activos</p>
                        <small class="stat-change">
                            <?php if ($orderStats['pedidos_pendientes'] > 0): ?>
                                <span class="text-warning">
                                    <i class="fas fa-exclamation-circle"></i> Requieren atención
                                </span>
                            <?php else: ?>
                                <span class="text-success">
                                    <i class="fas fa-check-circle"></i> Todo al día
                                </span>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>$<?= number_format($orderStats['total_gastado'], 2) ?></h3>
                        <p>Total Gastado</p>
                        <small class="text-success">
                            <i class="fas fa-arrow-down"></i> Ahorrado: $<?= number_format($orderStats['total_ahorrado'], 2) ?>
                        </small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= number_format($orderStats['pedidos_entregados']) ?></h3>
                        <p>Entregas Exitosas</p>
                        <small class="text-success">
                            <i class="fas fa-percentage"></i> <?= number_format($orderStats['tasa_exito'], 1) ?>% éxito
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- === FILTROS Y BÚSQUEDA === -->
    <div class="orders-filters">
        <div class="container">
            <form method="GET" class="filters-form" id="filtersForm">
                <div class="filters-grid">
                    <!-- Búsqueda por número de pedido o producto -->
                    <div class="filter-group">
                        <label for="search" class="filter-label">
                            <i class="fas fa-search"></i>
                            Buscar
                        </label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               class="form-control" 
                               placeholder="Número de pedido, producto..."
                               value="<?= h($search) ?>">
                    </div>
                    
                    <!-- Filtro por estado -->
                    <div class="filter-group">
                        <label for="status" class="filter-label">
                            <i class="fas fa-filter"></i>
                            Estado
                        </label>
                        <select id="status" name="status" class="form-control">
                            <?php foreach ($orderStatuses as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Filtro por fecha desde -->
                    <div class="filter-group">
                        <label for="date_from" class="filter-label">
                            <i class="fas fa-calendar"></i>
                            Desde
                        </label>
                        <input type="date" 
                               id="date_from" 
                               name="date_from" 
                               class="form-control" 
                               value="<?= h($dateFrom) ?>">
                    </div>
                    
                    <!-- Filtro por fecha hasta -->
                    <div class="filter-group">
                        <label for="date_to" class="filter-label">
                            <i class="fas fa-calendar"></i>
                            Hasta
                        </label>
                        <input type="date" 
                               id="date_to" 
                               name="date_to" 
                               class="form-control" 
                               value="<?= h($dateTo) ?>">
                    </div>
                </div>
                
                <div class="filters-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>
                    
                    <a href="/user/orders" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        Limpiar Filtros
                    </a>
                    
                    <div class="results-info">
                        <span class="results-count">
                            <?= number_format($totalOrders) ?> pedidos encontrados
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- === LISTA DE PEDIDOS === -->
    <div class="orders-content">
        <div class="container">
            <?php if (!empty($orders)): ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card" data-order-id="<?= $order['id'] ?>">
                            <!-- Header del pedido -->
                            <div class="order-header">
                                <div class="order-info">
                                    <div class="order-number">
                                        <strong>#<?= $order['numero_pedido'] ?></strong>
                                        <span class="order-date">
                                            <i class="far fa-calendar"></i>
                                            <?= date('d/m/Y H:i', strtotime($order['fecha_creacion'])) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="vendor-info">
                                        <i class="fas fa-store"></i>
                                        <a href="/vendor/<?= $order['vendedor_id'] ?>" class="vendor-link">
                                            <?= h($order['vendedor_nombre']) ?>
                                        </a>
                                        
                                        <?php if ($order['vendedor_calificacion']): ?>
                                            <div class="vendor-rating">
                                                <div class="stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?= $i <= $order['vendedor_calificacion'] ? 'filled' : '' ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <span>(<?= number_format($order['vendedor_calificacion'], 1) ?>)</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="order-status-section">
                                    <div class="order-status">
                                        <span class="status-badge status-<?= strtolower($order['estado']) ?>">
                                            <?= ucfirst($order['estado']) ?>
                                        </span>
                                        
                                        <?php if ($order['estado'] === 'enviado' && $order['tracking_number']): ?>
                                            <small class="tracking-info">
                                                <i class="fas fa-truck"></i>
                                                Tracking: <?= $order['tracking_number'] ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="order-total">
                                        <span class="total-label">Total:</span>
                                        <span class="total-amount">$<?= number_format($order['total'], 2) ?></span>
                                        
                                        <?php if ($order['descuento'] > 0): ?>
                                            <small class="discount-info">
                                                <i class="fas fa-tag"></i>
                                                Descuento: $<?= number_format($order['descuento'], 2) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Productos del pedido -->
                            <div class="order-products">
                                <div class="products-summary">
                                    <div class="products-count">
                                        <i class="fas fa-box"></i>
                                        <?= $order['total_productos'] ?> productos
                                    </div>
                                    
                                    <div class="products-preview">
                                        <?php 
                                        $orderProducts = $this->orderModel->getOrderProducts($order['id'], 3);
                                        foreach ($orderProducts as $product): 
                                        ?>
                                            <div class="product-preview-item">
                                                <div class="product-image">
                                                    <?php if ($product['imagen_principal']): ?>
                                                        <img src="<?= asset('uploads/products/' . $product['imagen_principal']) ?>" 
                                                             alt="<?= h($product['nombre']) ?>">
                                                    <?php else: ?>
                                                        <div class="image-placeholder">
                                                            <i class="fas fa-image"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="product-info">
                                                    <h5><?= h($product['nombre']) ?></h5>
                                                    <p class="product-details">
                                                        <?= number_format($product['cantidad'], 2) ?> <?= h($product['unidad']) ?>
                                                        • $<?= number_format($product['precio_unitario'], 2) ?> c/u
                                                    </p>
                                                    <p class="product-subtotal">
                                                        <strong>$<?= number_format($product['subtotal'], 2) ?></strong>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if ($order['total_productos'] > 3): ?>
                                            <div class="more-products">
                                                <i class="fas fa-ellipsis-h"></i>
                                                +<?= $order['total_productos'] - 3 ?> productos más
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información de entrega -->
                            <div class="order-delivery">
                                <div class="delivery-info">
                                    <div class="delivery-address">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <div class="address-details">
                                            <strong>Dirección de entrega:</strong>
                                            <span><?= h($order['direccion_entrega']) ?></span>
                                            <?php if ($order['notas_entrega']): ?>
                                                <small class="delivery-notes">
                                                    <i class="fas fa-comment"></i>
                                                    <?= h($order['notas_entrega']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="delivery-date">
                                        <i class="fas fa-calendar-check"></i>
                                        <div class="date-details">
                                            <strong>Fecha estimada:</strong>
                                            <span><?= date('d/m/Y', strtotime($order['fecha_entrega_estimada'])) ?></span>
                                            
                                            <?php if ($order['fecha_entrega_real']): ?>
                                                <small class="actual-date">
                                                    <i class="fas fa-check-circle"></i>
                                                    Entregado: <?= date('d/m/Y H:i', strtotime($order['fecha_entrega_real'])) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress tracker -->
                            <?php if (in_array($order['estado'], ['confirmado', 'preparando', 'enviado', 'entregado'])): ?>
                                <div class="order-progress">
                                    <div class="progress-steps">
                                        <div class="progress-step <?= in_array($order['estado'], ['confirmado', 'preparando', 'enviado', 'entregado']) ? 'completed' : '' ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <div class="step-label">Confirmado</div>
                                        </div>
                                        
                                        <div class="progress-line <?= in_array($order['estado'], ['preparando', 'enviado', 'entregado']) ? 'completed' : '' ?>"></div>
                                        
                                        <div class="progress-step <?= in_array($order['estado'], ['preparando', 'enviado', 'entregado']) ? 'completed' : '' ?> <?= $order['estado'] === 'preparando' ? 'current' : '' ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <div class="step-label">Preparando</div>
                                        </div>
                                        
                                        <div class="progress-line <?= in_array($order['estado'], ['enviado', 'entregado']) ? 'completed' : '' ?>"></div>
                                        
                                        <div class="progress-step <?= in_array($order['estado'], ['enviado', 'entregado']) ? 'completed' : '' ?> <?= $order['estado'] === 'enviado' ? 'current' : '' ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-truck"></i>
                                            </div>
                                            <div class="step-label">En camino</div>
                                        </div>
                                        
                                        <div class="progress-line <?= $order['estado'] === 'entregado' ? 'completed' : '' ?>"></div>
                                        
                                        <div class="progress-step <?= $order['estado'] === 'entregado' ? 'completed' : '' ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-home"></i>
                                            </div>
                                            <div class="step-label">Entregado</div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Acciones del pedido -->
                            <div class="order-actions">
                                <div class="actions-left">
                                    <a href="/orders/<?= $order['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                        Ver Detalles
                                    </a>
                                    
                                    <?php if ($order['estado'] === 'entregado' && !$order['calificado']): ?>
                                        <button type="button" 
                                                class="btn btn-outline-success btn-sm" 
                                                onclick="openRatingModal(<?= $order['id'] ?>, '<?= h($order['vendedor_nombre']) ?>')">
                                            <i class="fas fa-star"></i>
                                            Calificar Pedido
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($order['estado'], ['entregado']) && $order['factura_disponible']): ?>
                                        <a href="/orders/<?= $order['id'] ?>/invoice" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-file-invoice"></i>
                                            Descargar Factura
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="actions-right">
                                    <?php if ($order['estado'] === 'pendiente'): ?>
                                        <button type="button" 
                                                class="btn btn-outline-warning btn-sm" 
                                                onclick="editOrder(<?= $order['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                            Modificar
                                        </button>
                                        
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm" 
                                                onclick="cancelOrder(<?= $order['id'] ?>)">
                                            <i class="fas fa-times"></i>
                                            Cancelar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($order['estado'], ['confirmado', 'preparando', 'enviado'])): ?>
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm" 
                                                onclick="trackOrder(<?= $order['id'] ?>)">
                                            <i class="fas fa-map-marker-alt"></i>
                                            Rastrear
                                        </button>
                                    <?php endif; ?>
                                    
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                type="button" 
                                                id="orderActions<?= $order['id'] ?>" 
                                                data-bs-toggle="dropdown" 
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="orderActions<?= $order['id'] ?>">
                                            <li>
                                                <a class="dropdown-item" href="/orders/<?= $order['id'] ?>/repeat">
                                                    <i class="fas fa-redo"></i>
                                                    Volver a Pedir
                                                </a>
                                            </li>
                                            
                                            <?php if ($order['estado'] === 'entregado'): ?>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="reportProblem(<?= $order['id'] ?>)">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Reportar Problema
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <li>
                                                <a class="dropdown-item" href="/orders/<?= $order['id'] ?>/share">
                                                    <i class="fas fa-share"></i>
                                                    Compartir Pedido
                                                </a>
                                            </li>
                                            
                                            <li><hr class="dropdown-divider"></li>
                                            
                                            <li>
                                                <a class="dropdown-item" href="/support?order_id=<?= $order['id'] ?>">
                                                    <i class="fas fa-headset"></i>
                                                    Contactar Soporte
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- === PAGINACIÓN === -->
                <?php if ($totalPages > 1): ?>
                    <div class="orders-pagination">
                        <nav aria-label="Navegación de pedidos">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildUrl('/user/orders', array_merge($_GET, ['page' => $page - 1])) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                            Anterior
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildUrl('/user/orders', array_merge($_GET, ['page' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildUrl('/user/orders', array_merge($_GET, ['page' => $page + 1])) ?>">
                                            Siguiente
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        
                        <div class="pagination-info">
                            Mostrando <?= ($offset + 1) ?> - <?= min($offset + $limit, $totalOrders) ?> de <?= number_format($totalOrders) ?> pedidos
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Estado vacío -->
                <div class="empty-orders">
                    <div class="empty-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    
                    <h3>No tienes pedidos aún</h3>
                    
                    <p>
                        <?php if (!empty($search) || $status !== 'all' || !empty($dateFrom) || !empty($dateTo)): ?>
                            No se encontraron pedidos que coincidan con los filtros seleccionados.
                            <br>
                            Intenta ajustar los criterios de búsqueda.
                        <?php else: ?>
                            ¡Es hora de hacer tu primer pedido! Explora nuestros productos frescos del campo
                            y encuentra los mejores ingredientes para tu cocina.
                        <?php endif; ?>
                    </p>
                    
                    <div class="empty-actions">
                        <?php if (!empty($search) || $status !== 'all' || !empty($dateFrom) || !empty($dateTo)): ?>
                            <a href="/user/orders" class="btn btn-outline-primary">
                                <i class="fas fa-times"></i>
                                Limpiar Filtros
                            </a>
                        <?php endif; ?>
                        
                        <a href="/products" class="btn btn-success">
                            <i class="fas fa-shopping-cart"></i>
                            Explorar Productos
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- === MODALES === -->

<!-- Modal para calificar pedido -->
<div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ratingModalLabel">
                    <i class="fas fa-star"></i>
                    Calificar Pedido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="ratingForm">
                <div class="modal-body">
                    <div class="rating-section">
                        <h6>¿Cómo fue tu experiencia con <span id="vendorName"></span>?</h6>
                        
                        <div class="star-rating" id="starRating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star" data-rating="<?= $i ?>"></i>
                            <?php endfor; ?>
                        </div>
                        
                        <input type="hidden" id="ratingValue" name="rating" value="0">
                        <input type="hidden" id="orderIdRating" name="order_id" value="">
                    </div>
                    
                    <div class="review-section">
                        <label for="reviewComment" class="form-label">
                            Cuéntanos más sobre tu experiencia (opcional)
                        </label>
                        <textarea id="reviewComment" 
                                  name="comment" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="¿Cómo estaba la calidad de los productos? ¿La entrega fue puntual? ¿Recomendarías este vendedor?"></textarea>
                        
                        <div class="char-counter">
                            <span id="commentCharCount">0</span>/500 caracteres
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="submitRating" disabled>
                        <i class="fas fa-paper-plane"></i>
                        Enviar Calificación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="ordersLoading" class="loading-overlay" style="display: none;">
    <div class="loading-spinner">
        <div class="spinner-border" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <p>Procesando pedido...</p>
    </div>
</div>

<script src="<?= asset('js/orders.js') ?>"></script>