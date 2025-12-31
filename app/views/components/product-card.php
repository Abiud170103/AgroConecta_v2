<?php 
// Componente reutilizable para tarjetas de producto
// Requiere la variable $producto con los datos del producto
?>
<div class="product-card" data-product-id="<?= $producto['id'] ?? '' ?>">
    <div class="product-image-container">
        <img src="<?= asset('img/products/' . ($producto['imagen'] ?? 'placeholder.jpg')) ?>" 
             alt="<?= htmlspecialchars($producto['nombre'] ?? 'Producto') ?>" 
             class="product-image"
             loading="lazy">
        
        <!-- Badge de producto -->
        <?php if(isset($producto['en_oferta']) && $producto['en_oferta']): ?>
            <div class="product-badge sale">
                -<?= $producto['descuento'] ?? 0 ?>% OFF
            </div>
        <?php elseif(isset($producto['es_nuevo']) && $producto['es_nuevo']): ?>
            <div class="product-badge new">Nuevo</div>
        <?php elseif(isset($producto['es_organico']) && $producto['es_organico']): ?>
            <div class="product-badge organic">Orgánico</div>
        <?php endif; ?>
        
        <!-- Stock bajo -->
        <?php if(isset($producto['stock']) && $producto['stock'] > 0 && $producto['stock'] <= 5): ?>
            <div class="product-badge low-stock">¡Últimas unidades!</div>
        <?php elseif(isset($producto['stock']) && $producto['stock'] == 0): ?>
            <div class="product-badge out-of-stock">Agotado</div>
        <?php endif; ?>
        
        <!-- Acciones del producto -->
        <div class="product-actions">
            <button class="action-btn wishlist <?= in_wishlist($producto['id'] ?? 0) ? 'active' : '' ?>" 
                    title="<?= in_wishlist($producto['id'] ?? 0) ? 'Quitar de favoritos' : 'Agregar a favoritos' ?>"
                    data-product-id="<?= $producto['id'] ?? '' ?>">
                <i class="<?= in_wishlist($producto['id'] ?? 0) ? 'fas' : 'far' ?> fa-heart"></i>
            </button>
            <button class="action-btn quick-view" 
                    title="Vista rápida"
                    data-product-id="<?= $producto['id'] ?? '' ?>">
                <i class="fas fa-eye"></i>
            </button>
            <button class="action-btn compare" 
                    title="Comparar producto"
                    data-product-id="<?= $producto['id'] ?? '' ?>">
                <i class="fas fa-balance-scale"></i>
            </button>
        </div>
        
        <!-- Overlay para producto agotado -->
        <?php if(isset($producto['stock']) && $producto['stock'] == 0): ?>
            <div class="out-of-stock-overlay">
                <span>Producto Agotado</span>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="product-content">
        <!-- Información del vendedor -->
        <?php if(isset($producto['vendedor'])): ?>
            <div class="vendor-info">
                <img src="<?= asset('img/vendors/' . ($producto['vendedor']['avatar'] ?? 'default.png')) ?>" 
                     alt="<?= htmlspecialchars($producto['vendedor']['nombre'] ?? 'Vendedor') ?>" 
                     class="vendor-avatar">
                <span class="vendor-name"><?= htmlspecialchars($producto['vendedor']['nombre'] ?? 'Vendedor') ?></span>
                <div class="vendor-rating">
                    <?php 
                    $vendorRating = $producto['vendedor']['rating'] ?? 0;
                    for($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?= $i <= $vendorRating ? 'active' : '' ?>"></i>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Título del producto -->
        <h3 class="product-title">
            <a href="<?= url('/productos/' . ($producto['slug'] ?? $producto['id'])) ?>">
                <?= htmlspecialchars($producto['nombre'] ?? 'Producto sin nombre') ?>
            </a>
        </h3>
        
        <!-- Descripción corta -->
        <p class="product-description">
            <?= htmlspecialchars(substr($producto['descripcion'] ?? '', 0, 100)) ?>
            <?= strlen($producto['descripcion'] ?? '') > 100 ? '...' : '' ?>
        </p>
        
        <!-- Categoría -->
        <?php if(isset($producto['categoria'])): ?>
            <div class="product-category">
                <a href="<?= url('/productos/categoria/' . $producto['categoria']['slug']) ?>" class="category-link">
                    <i class="fas fa-tag"></i>
                    <?= htmlspecialchars($producto['categoria']['nombre']) ?>
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Rating y reviews -->
        <?php if(isset($producto['rating']) && $producto['rating'] > 0): ?>
            <div class="product-rating">
                <div class="stars">
                    <?php 
                    $rating = floatval($producto['rating']);
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                    
                    for($i = 1; $i <= 5; $i++): ?>
                        <?php if($i <= $fullStars): ?>
                            <i class="fas fa-star active"></i>
                        <?php elseif($i == $fullStars + 1 && $hasHalfStar): ?>
                            <i class="fas fa-star-half-alt active"></i>
                        <?php else: ?>
                            <i class="far fa-star"></i>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <span class="rating-value"><?= number_format($rating, 1) ?></span>
                <span class="rating-count">(<?= $producto['reviews_count'] ?? 0 ?> reviews)</span>
            </div>
        <?php else: ?>
            <div class="product-rating">
                <div class="stars">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="far fa-star"></i>
                    <?php endfor; ?>
                </div>
                <span class="rating-text">Sin calificaciones</span>
            </div>
        <?php endif; ?>
        
        <!-- Información adicional -->
        <div class="product-meta">
            <?php if(isset($producto['unidad_medida'])): ?>
                <span class="unit-measure">
                    <i class="fas fa-weight-hanging"></i>
                    Por <?= htmlspecialchars($producto['unidad_medida']) ?>
                </span>
            <?php endif; ?>
            
            <?php if(isset($producto['origen'])): ?>
                <span class="product-origin">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($producto['origen']) ?>
                </span>
            <?php endif; ?>
            
            <?php if(isset($producto['fecha_cosecha'])): ?>
                <span class="harvest-date">
                    <i class="fas fa-calendar-alt"></i>
                    Cosechado: <?= date('d/m/Y', strtotime($producto['fecha_cosecha'])) ?>
                </span>
            <?php endif; ?>
        </div>
        
        <!-- Precios -->
        <div class="product-pricing">
            <?php if(isset($producto['precio_oferta']) && $producto['precio_oferta'] > 0 && $producto['precio_oferta'] < $producto['precio']): ?>
                <div class="price-container">
                    <span class="current-price">$<?= number_format($producto['precio_oferta'], 2) ?></span>
                    <span class="original-price">$<?= number_format($producto['precio'], 2) ?></span>
                    <span class="discount-percentage">
                        -<?= round((($producto['precio'] - $producto['precio_oferta']) / $producto['precio']) * 100) ?>%
                    </span>
                </div>
                <div class="savings">
                    Ahorras: $<?= number_format($producto['precio'] - $producto['precio_oferta'], 2) ?>
                </div>
            <?php else: ?>
                <div class="price-container">
                    <span class="current-price">$<?= number_format($producto['precio'] ?? 0, 2) ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Precio por unidad si aplica -->
            <?php if(isset($producto['precio_por_unidad']) && $producto['precio_por_unidad'] != $producto['precio']): ?>
                <div class="unit-price">
                    $<?= number_format($producto['precio_por_unidad'], 2) ?> por unidad
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Stock disponible -->
        <?php if(isset($producto['stock'])): ?>
            <div class="stock-info">
                <?php if($producto['stock'] > 10): ?>
                    <span class="stock-available">
                        <i class="fas fa-check-circle text-success"></i>
                        Disponible
                    </span>
                <?php elseif($producto['stock'] > 0): ?>
                    <span class="stock-low">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Solo quedan <?= $producto['stock'] ?> unidades
                    </span>
                <?php else: ?>
                    <span class="stock-out">
                        <i class="fas fa-times-circle text-danger"></i>
                        Agotado
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Acciones del producto (parte inferior) -->
        <div class="product-actions-bottom">
            <?php if(isset($producto['stock']) && $producto['stock'] > 0): ?>
                <!-- Selector de cantidad -->
                <div class="quantity-selector">
                    <button type="button" class="quantity-btn minus" data-action="decrease">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" class="quantity-input" value="1" min="1" max="<?= $producto['stock'] ?>" data-product-id="<?= $producto['id'] ?? '' ?>">
                    <button type="button" class="quantity-btn plus" data-action="increase">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                
                <!-- Botón agregar al carrito -->
                <button class="btn btn-primary add-to-cart flex-1" 
                        data-product-id="<?= $producto['id'] ?? '' ?>"
                        data-product-name="<?= htmlspecialchars($producto['nombre'] ?? '') ?>"
                        data-product-price="<?= $producto['precio_oferta'] ?? $producto['precio'] ?? 0 ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="btn-text">Agregar al Carrito</span>
                </button>
            <?php else: ?>
                <button class="btn btn-secondary disabled" disabled>
                    <i class="fas fa-times"></i>
                    No Disponible
                </button>
            <?php endif; ?>
            
            <!-- Compra rápida -->
            <?php if(isset($producto['stock']) && $producto['stock'] > 0): ?>
                <button class="btn btn-outline buy-now" 
                        data-product-id="<?= $producto['id'] ?? '' ?>">
                    <i class="fas fa-bolt"></i>
                    Comprar Ahora
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Características adicionales -->
        <?php if(isset($producto['caracteristicas']) && !empty($producto['caracteristicas'])): ?>
            <div class="product-features">
                <?php foreach(array_slice($producto['caracteristicas'], 0, 3) as $feature): ?>
                    <span class="feature-tag">
                        <i class="fas fa-check"></i>
                        <?= htmlspecialchars($feature) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Beneficios del producto -->
        <?php if(isset($producto['es_organico']) && $producto['es_organico']): ?>
            <div class="product-benefits">
                <span class="benefit-tag organic">
                    <i class="fas fa-leaf"></i>
                    100% Orgánico
                </span>
            </div>
        <?php endif; ?>
        
        <?php if(isset($producto['es_local']) && $producto['es_local']): ?>
            <div class="product-benefits">
                <span class="benefit-tag local">
                    <i class="fas fa-map-marker-alt"></i>
                    Producto Local
                </span>
            </div>
        <?php endif; ?>
        
        <?php if(isset($producto['entrega_inmediata']) && $producto['entrega_inmediata']): ?>
            <div class="product-benefits">
                <span class="benefit-tag fast-delivery">
                    <i class="fas fa-shipping-fast"></i>
                    Entrega Inmediata
                </span>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Loading overlay para acciones -->
    <div class="product-loading" style="display: none;">
        <div class="loading-spinner"></div>
    </div>
</div>

<!-- Estilos inline para desarrollo (mover a CSS principal después) -->
<style>
.product-card {
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-loading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.quantity-selector {
    display: flex;
    align-items: center;
    border: 1px solid var(--border-color, #ddd);
    border-radius: 4px;
    overflow: hidden;
}

.quantity-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: #f8f9fa;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
}

.quantity-btn:hover {
    background: var(--primary-color, #27ae60);
    color: white;
}

.quantity-input {
    width: 50px;
    height: 32px;
    text-align: center;
    border: none;
    border-left: 1px solid #ddd;
    border-right: 1px solid #ddd;
    font-size: 14px;
}

.product-actions-bottom {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 12px;
}

.flex-1 {
    flex: 1;
}

.feature-tag, .benefit-tag {
    display: inline-block;
    font-size: 12px;
    padding: 2px 6px;
    border-radius: 3px;
    margin: 2px;
    background: #e9ecef;
    color: #495057;
}

.benefit-tag.organic {
    background: #d4edda;
    color: #155724;
}

.benefit-tag.local {
    background: #d1ecf1;
    color: #0c5460;
}

.benefit-tag.fast-delivery {
    background: #fff3cd;
    color: #856404;
}

.out-of-stock-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    z-index: 2;
}

.vendor-info {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    font-size: 12px;
}

.vendor-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin-right: 6px;
}

.vendor-rating {
    margin-left: 6px;
}

.vendor-rating .fa-star {
    font-size: 10px;
    color: #ffc107;
}

.product-meta {
    font-size: 12px;
    color: #6c757d;
    margin: 8px 0;
}

.product-meta span {
    display: block;
    margin: 2px 0;
}

.product-meta i {
    width: 12px;
    text-align: center;
    margin-right: 4px;
}

.price-container {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.current-price {
    font-size: 18px;
    font-weight: bold;
    color: var(--primary-color, #27ae60);
}

.original-price {
    font-size: 14px;
    color: #6c757d;
    text-decoration: line-through;
}

.discount-percentage {
    font-size: 12px;
    background: #dc3545;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
}

.savings {
    font-size: 12px;
    color: #28a745;
    font-weight: 500;
}

.unit-price {
    font-size: 12px;
    color: #6c757d;
    margin-top: 2px;
}

.stock-info {
    margin: 8px 0;
    font-size: 13px;
}

.stock-available {
    color: #28a745;
}

.stock-low {
    color: #ffc107;
}

.stock-out {
    color: #dc3545;
}

.product-features {
    margin-top: 8px;
}

.product-benefits {
    margin-top: 8px;
}
</style>