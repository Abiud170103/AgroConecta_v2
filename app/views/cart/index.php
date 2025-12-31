<?php 
// Obtener items del carrito
$cartItems = [];
$cartTotal = 0;
$cartSubtotal = 0;
$shippingCost = 0;
$discountAmount = 0;
$itemCount = 0;

if (isset($_SESSION['user_id'])) {
    // Usuario autenticado - obtener carrito de la base de datos
    $cartItems = $this->cartModel->getUserCartItems($_SESSION['user_id']);
} else {
    // Usuario no autenticado - obtener carrito de la sesión
    $cartItems = $this->cartModel->getSessionCartItems();
}

// Calcular totales
foreach ($cartItems as &$item) {
    $item['subtotal'] = $item['precio'] * $item['cantidad'];
    $cartSubtotal += $item['subtotal'];
    $itemCount += $item['cantidad'];
}

// Calcular envío
$shippingCost = $this->shippingModel->calculateShipping($cartItems, $_SESSION['user_address'] ?? null);

// Aplicar descuentos si hay
$couponCode = $_SESSION['coupon_code'] ?? null;
if ($couponCode) {
    $discount = $this->couponModel->validateAndGetDiscount($couponCode, $cartSubtotal);
    if ($discount) {
        $discountAmount = $discount['amount'];
    }
}

$cartTotal = $cartSubtotal + $shippingCost - $discountAmount;

// Obtener direcciones del usuario si está autenticado
$userAddresses = [];
if (isset($_SESSION['user_id'])) {
    $userAddresses = $this->addressModel->getUserAddresses($_SESSION['user_id']);
}
?>

<div class="shopping-cart">
    <!-- === BREADCRUMB === -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/"><i class="fas fa-home"></i> Inicio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="/products"><i class="fas fa-leaf"></i> Productos</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-shopping-cart"></i> Carrito de Compras
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- === HEADER DEL CARRITO === -->
    <div class="cart-header">
        <div class="container">
            <div class="header-content">
                <div class="page-title">
                    <h1>
                        <i class="fas fa-shopping-cart"></i>
                        Carrito de Compras
                    </h1>
                    <p class="subtitle">
                        <?php if ($itemCount > 0): ?>
                            Tienes <?= number_format($itemCount) ?> productos en tu carrito
                        <?php else: ?>
                            Tu carrito está vacío
                        <?php endif; ?>
                    </p>
                </div>
                
                <?php if (!empty($cartItems)): ?>
                    <div class="cart-actions">
                        <button type="button" class="btn btn-outline-secondary" onclick="clearCart()">
                            <i class="fas fa-trash"></i>
                            Vaciar Carrito
                        </button>
                        
                        <a href="/products" class="btn btn-success">
                            <i class="fas fa-plus"></i>
                            Seguir Comprando
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($cartItems)): ?>
        <!-- === CONTENIDO DEL CARRITO === -->
        <div class="cart-content">
            <div class="container">
                <div class="cart-layout">
                    <!-- === ITEMS DEL CARRITO === -->
                    <div class="cart-items">
                        <div class="cart-items-header">
                            <h2>Productos en tu Carrito</h2>
                            
                            <div class="bulk-actions">
                                <div class="select-all">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                    <label for="selectAll" class="form-check-label">
                                        Seleccionar todos
                                    </label>
                                </div>
                                
                                <button type="button" class="btn btn-outline-danger btn-sm" id="removeSelectedBtn" disabled>
                                    <i class="fas fa-trash"></i>
                                    Eliminar seleccionados
                                </button>
                            </div>
                        </div>
                        
                        <div class="items-list" id="cartItemsList">
                            <?php foreach ($cartItems as $index => $item): ?>
                                <div class="cart-item" data-item-id="<?= $item['id'] ?>" data-product-id="<?= $item['producto_id'] ?>">
                                    <div class="item-select">
                                        <input type="checkbox" 
                                               class="form-check-input item-checkbox" 
                                               id="item_<?= $item['id'] ?>"
                                               data-item-id="<?= $item['id'] ?>">
                                    </div>
                                    
                                    <div class="item-image">
                                        <?php if (!empty($item['imagen_principal'])): ?>
                                            <img src="<?= asset('uploads/products/' . $item['imagen_principal']) ?>" 
                                                 alt="<?= h($item['nombre']) ?>"
                                                 loading="lazy">
                                        <?php else: ?>
                                            <div class="image-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['descuento'] > 0): ?>
                                            <div class="discount-badge">
                                                -<?= number_format($item['descuento']) ?>%
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="item-details">
                                        <div class="item-info">
                                            <h3 class="item-title">
                                                <a href="/products/<?= $item['producto_id'] ?>">
                                                    <?= h($item['nombre']) ?>
                                                </a>
                                            </h3>
                                            
                                            <p class="item-description">
                                                <?= h($item['descripcion_corta']) ?>
                                            </p>
                                            
                                            <div class="item-meta">
                                                <div class="vendor-info">
                                                    <i class="fas fa-store"></i>
                                                    <a href="/vendor/<?= $item['vendedor_id'] ?>" class="vendor-link">
                                                        <?= h($item['vendedor_nombre']) ?>
                                                    </a>
                                                    
                                                    <?php if ($item['vendedor_calificacion']): ?>
                                                        <div class="vendor-rating">
                                                            <div class="stars">
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <i class="fas fa-star <?= $i <= $item['vendedor_calificacion'] ? 'filled' : '' ?>"></i>
                                                                <?php endfor; ?>
                                                            </div>
                                                            <span>(<?= number_format($item['vendedor_calificacion'], 1) ?>)</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="item-category">
                                                    <i class="fas fa-tag"></i>
                                                    <?= h($item['categoria_nombre']) ?>
                                                </div>
                                                
                                                <?php if ($item['organico']): ?>
                                                    <div class="organic-badge">
                                                        <i class="fas fa-leaf"></i>
                                                        Orgánico
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Información de stock -->
                                            <div class="stock-info">
                                                <?php if ($item['stock'] < 10 && $item['stock'] > 0): ?>
                                                    <div class="low-stock-warning">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Solo quedan <?= $item['stock'] ?> disponibles
                                                    </div>
                                                <?php elseif ($item['stock'] <= 0): ?>
                                                    <div class="out-of-stock-warning">
                                                        <i class="fas fa-times-circle"></i>
                                                        Producto agotado
                                                    </div>
                                                <?php else: ?>
                                                    <div class="stock-available">
                                                        <i class="fas fa-check-circle"></i>
                                                        Disponible (<?= $item['stock'] ?> en stock)
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="item-actions">
                                            <!-- Control de cantidad -->
                                            <div class="quantity-control">
                                                <label class="quantity-label">Cantidad:</label>
                                                <div class="quantity-input-group">
                                                    <button type="button" 
                                                            class="btn-quantity-decrease" 
                                                            onclick="updateQuantity(<?= $item['id'] ?>, 'decrease')"
                                                            <?= $item['cantidad'] <= 1 ? 'disabled' : '' ?>>
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    
                                                    <input type="number" 
                                                           class="quantity-input" 
                                                           id="quantity_<?= $item['id'] ?>"
                                                           value="<?= $item['cantidad'] ?>" 
                                                           min="1" 
                                                           max="<?= $item['stock'] ?>"
                                                           data-item-id="<?= $item['id'] ?>"
                                                           onchange="updateQuantity(<?= $item['id'] ?>, 'set', this.value)">
                                                    
                                                    <button type="button" 
                                                            class="btn-quantity-increase" 
                                                            onclick="updateQuantity(<?= $item['id'] ?>, 'increase')"
                                                            <?= $item['cantidad'] >= $item['stock'] ? 'disabled' : '' ?>>
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                
                                                <small class="unit-info">
                                                    por <?= h($item['unidad']) ?>
                                                </small>
                                            </div>
                                            
                                            <!-- Acciones adicionales -->
                                            <div class="item-extra-actions">
                                                <button type="button" 
                                                        class="btn btn-link btn-sm" 
                                                        onclick="saveForLater(<?= $item['id'] ?>)"
                                                        title="Guardar para después">
                                                    <i class="far fa-heart"></i>
                                                    Favoritos
                                                </button>
                                                
                                                <button type="button" 
                                                        class="btn btn-link btn-sm text-danger" 
                                                        onclick="removeItem(<?= $item['id'] ?>)"
                                                        title="Eliminar del carrito">
                                                    <i class="fas fa-trash"></i>
                                                    Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="item-pricing">
                                        <div class="price-section">
                                            <?php if ($item['descuento'] > 0): ?>
                                                <div class="original-price">
                                                    $<?= number_format($item['precio_original'], 2) ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="current-price">
                                                $<?= number_format($item['precio'], 2) ?>
                                            </div>
                                            
                                            <div class="unit-price">
                                                por <?= h($item['unidad']) ?>
                                            </div>
                                        </div>
                                        
                                        <div class="subtotal-section">
                                            <div class="subtotal-label">Subtotal:</div>
                                            <div class="subtotal-amount">
                                                $<span id="subtotal_<?= $item['id'] ?>"><?= number_format($item['subtotal'], 2) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Productos recomendados -->
                        <div class="recommended-products">
                            <h3>
                                <i class="fas fa-star"></i>
                                También te puede interesar
                            </h3>
                            
                            <div class="recommended-grid">
                                <!-- Esta sección se cargará dinámicamente -->
                                <div class="recommended-item-placeholder">
                                    <div class="loading-spinner">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        Cargando recomendaciones...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- === RESUMEN DEL CARRITO === -->
                    <div class="cart-summary">
                        <div class="summary-card">
                            <div class="summary-header">
                                <h3>
                                    <i class="fas fa-calculator"></i>
                                    Resumen del Pedido
                                </h3>
                            </div>
                            
                            <div class="summary-content">
                                <!-- Subtotal -->
                                <div class="summary-row">
                                    <span class="label">Subtotal (<?= $itemCount ?> productos):</span>
                                    <span class="amount" id="summarySubtotal">$<?= number_format($cartSubtotal, 2) ?></span>
                                </div>
                                
                                <!-- Descuentos -->
                                <?php if ($discountAmount > 0): ?>
                                    <div class="summary-row discount">
                                        <span class="label">
                                            <i class="fas fa-tag"></i>
                                            Descuento aplicado:
                                        </span>
                                        <span class="amount">-$<?= number_format($discountAmount, 2) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Código de cupón -->
                                <div class="coupon-section">
                                    <div class="coupon-input-group">
                                        <input type="text" 
                                               id="couponCode" 
                                               class="form-control" 
                                               placeholder="Código de descuento"
                                               value="<?= h($couponCode ?? '') ?>">
                                        <button type="button" class="btn btn-outline-primary" onclick="applyCoupon()">
                                            <i class="fas fa-tag"></i>
                                            Aplicar
                                        </button>
                                    </div>
                                    
                                    <?php if ($couponCode && $discountAmount > 0): ?>
                                        <div class="coupon-applied">
                                            <i class="fas fa-check-circle text-success"></i>
                                            Cupón "<?= h($couponCode) ?>" aplicado
                                            <button type="button" class="btn-remove-coupon" onclick="removeCoupon()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Envío -->
                                <div class="shipping-section">
                                    <div class="summary-row">
                                        <span class="label">
                                            <i class="fas fa-truck"></i>
                                            Envío:
                                        </span>
                                        <span class="amount" id="summaryShipping">
                                            <?php if ($shippingCost > 0): ?>
                                                $<?= number_format($shippingCost, 2) ?>
                                            <?php else: ?>
                                                <span class="text-success">¡Gratis!</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Calculadora de envío -->
                                    <div class="shipping-calculator">
                                        <button type="button" 
                                                class="btn btn-link btn-sm" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#shippingOptions">
                                            <i class="fas fa-calculator"></i>
                                            Calcular envío
                                        </button>
                                        
                                        <div class="collapse" id="shippingOptions">
                                            <div class="shipping-form">
                                                <?php if (isset($_SESSION['user_id']) && !empty($userAddresses)): ?>
                                                    <!-- Usuario con direcciones guardadas -->
                                                    <label for="shippingAddress" class="form-label">
                                                        Selecciona dirección de entrega:
                                                    </label>
                                                    <select id="shippingAddress" class="form-control" onchange="calculateShipping()">
                                                        <option value="">Selecciona una dirección</option>
                                                        <?php foreach ($userAddresses as $address): ?>
                                                            <option value="<?= $address['id'] ?>">
                                                                <?= h($address['direccion_completa']) ?>
                                                                <?= h($address['ciudad']) ?>, <?= h($address['estado']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    
                                                    <button type="button" class="btn btn-link btn-sm" onclick="toggleNewAddress()">
                                                        <i class="fas fa-plus"></i>
                                                        Usar nueva dirección
                                                    </button>
                                                <?php else: ?>
                                                    <!-- Usuario sin direcciones o no autenticado -->
                                                    <div class="address-form" id="newAddressForm">
                                                        <div class="form-grid">
                                                            <div class="form-group">
                                                                <input type="text" 
                                                                       id="shippingCity" 
                                                                       class="form-control" 
                                                                       placeholder="Ciudad">
                                                            </div>
                                                            <div class="form-group">
                                                                <input type="text" 
                                                                       id="shippingState" 
                                                                       class="form-control" 
                                                                       placeholder="Estado">
                                                            </div>
                                                            <div class="form-group">
                                                                <input type="text" 
                                                                       id="shippingZip" 
                                                                       class="form-control" 
                                                                       placeholder="Código Postal">
                                                            </div>
                                                        </div>
                                                        
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="calculateShipping()">
                                                            Calcular Envío
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Información de envío gratuito -->
                                    <?php if ($cartSubtotal < 500 && $cartSubtotal > 0): ?>
                                        <div class="free-shipping-progress">
                                            <div class="progress-info">
                                                <small>
                                                    <i class="fas fa-info-circle"></i>
                                                    Agrega $<?= number_format(500 - $cartSubtotal, 2) ?> más para <strong>envío gratis</strong>
                                                </small>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" 
                                                     role="progressbar" 
                                                     style="width: <?= ($cartSubtotal / 500) * 100 ?>%"
                                                     aria-valuenow="<?= $cartSubtotal ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="500">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Total -->
                                <div class="summary-row total">
                                    <span class="label">Total:</span>
                                    <span class="amount" id="summaryTotal">$<?= number_format($cartTotal, 2) ?></span>
                                </div>
                            </div>
                            
                            <!-- Acciones -->
                            <div class="summary-actions">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <!-- Usuario autenticado -->
                                    <a href="/checkout" class="btn btn-success btn-lg btn-block">
                                        <i class="fas fa-credit-card"></i>
                                        Proceder al Checkout
                                    </a>
                                <?php else: ?>
                                    <!-- Usuario no autenticado -->
                                    <a href="/auth/login?redirect=checkout" class="btn btn-success btn-lg btn-block">
                                        <i class="fas fa-sign-in-alt"></i>
                                        Iniciar Sesión para Continuar
                                    </a>
                                    
                                    <div class="guest-checkout-option">
                                        <hr>
                                        <p class="text-center">
                                            <small>¿No tienes cuenta?</small>
                                        </p>
                                        <a href="/checkout/guest" class="btn btn-outline-primary btn-block">
                                            <i class="fas fa-user"></i>
                                            Continuar como Invitado
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Garantías y seguridad -->
                                <div class="security-badges">
                                    <div class="security-item">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Compra Segura</span>
                                    </div>
                                    <div class="security-item">
                                        <i class="fas fa-undo"></i>
                                        <span>Garantía de Devolución</span>
                                    </div>
                                    <div class="security-item">
                                        <i class="fas fa-truck"></i>
                                        <span>Entrega Rápida</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="additional-info">
                            <div class="payment-methods">
                                <h4>Métodos de Pago Aceptados</h4>
                                <div class="payment-icons">
                                    <i class="fab fa-cc-visa" title="Visa"></i>
                                    <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                                    <i class="fab fa-cc-paypal" title="PayPal"></i>
                                    <i class="fas fa-money-bill-wave" title="Efectivo"></i>
                                    <i class="fas fa-university" title="Transferencia Bancaria"></i>
                                </div>
                            </div>
                            
                            <div class="customer-service">
                                <h4>¿Necesitas Ayuda?</h4>
                                <p>
                                    <i class="fas fa-phone"></i>
                                    <a href="tel:+525551234567">+52 55 1234 5567</a>
                                </p>
                                <p>
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:soporte@agroconecta.mx">soporte@agroconecta.mx</a>
                                </p>
                                <p>
                                    <i class="fas fa-comments"></i>
                                    <a href="/support/chat">Chat en vivo</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- === CARRITO VACÍO === -->
        <div class="empty-cart">
            <div class="container">
                <div class="empty-cart-content">
                    <div class="empty-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    
                    <h2>Tu carrito está vacío</h2>
                    
                    <p>
                        ¡Parece que aún no has agregado productos a tu carrito!
                        <br>
                        Explora nuestra selección de productos frescos del campo y encuentra
                        los mejores ingredientes para tu cocina.
                    </p>
                    
                    <div class="empty-actions">
                        <a href="/products" class="btn btn-success btn-lg">
                            <i class="fas fa-leaf"></i>
                            Explorar Productos
                        </a>
                        
                        <a href="/categories" class="btn btn-outline-primary">
                            <i class="fas fa-th-large"></i>
                            Ver Categorías
                        </a>
                    </div>
                    
                    <!-- Productos populares -->
                    <div class="popular-products">
                        <h3>Productos Populares</h3>
                        <div class="popular-grid">
                            <!-- Esta sección se cargará dinámicamente -->
                            <div class="popular-loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                Cargando productos populares...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Loading Overlay -->
<div id="cartLoading" class="loading-overlay" style="display: none;">
    <div class="loading-spinner">
        <div class="spinner-border" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <p id="loadingMessage">Actualizando carrito...</p>
    </div>
</div>

<script src="<?= asset('js/cart.js') ?>"></script>