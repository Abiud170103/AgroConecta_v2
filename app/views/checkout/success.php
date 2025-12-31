<?php
// Verificar que el pedido existe y pertenece al usuario
$orderId = $this->route_params['id'] ?? null;

if (!$orderId) {
    redirect('/');
}

// Obtener información del pedido
$order = $this->orderModel->findById($orderId);

if (!$order) {
    redirect('/');
}

// Verificar permisos (usuario logueado debe ser el dueño del pedido)
$isGuest = !isset($_SESSION['user_id']);
if (!$isGuest && $order['usuario_id'] != $_SESSION['user_id']) {
    redirect('/');
}

// Obtener items del pedido
$orderItems = $this->orderModel->getOrderItems($orderId);

// Obtener información de entrega y pago
$deliveryInfo = $this->orderModel->getDeliveryInfo($orderId);
$paymentInfo = $this->orderModel->getPaymentInfo($orderId);

// Calcular tiempo estimado de entrega
$deliveryDate = date('Y-m-d', strtotime($order['fecha_pedido'] . ' +' . ($deliveryInfo['metodo'] === 'express' ? '2' : '5') . ' days'));

// Estados del pedido
$orderStatuses = [
    'pendiente' => ['icon' => 'clock', 'color' => 'warning', 'text' => 'Pendiente de pago'],
    'pagado' => ['icon' => 'credit-card', 'color' => 'info', 'text' => 'Pago confirmado'],
    'preparando' => ['icon' => 'box-open', 'color' => 'primary', 'text' => 'Preparando pedido'],
    'enviado' => ['icon' => 'shipping-fast', 'color' => 'success', 'text' => 'Enviado'],
    'entregado' => ['icon' => 'check-circle', 'color' => 'success', 'text' => 'Entregado'],
    'cancelado' => ['icon' => 'times-circle', 'color' => 'danger', 'text' => 'Cancelado']
];

$currentStatus = $orderStatuses[$order['estado']] ?? $orderStatuses['pendiente'];
?>

<div class="order-success-page">
    <!-- === HEADER DE CONFIRMACIÓN === -->
    <div class="success-header">
        <div class="container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1>¡Pedido Realizado Exitosamente!</h1>
            
            <div class="order-number">
                <strong>Número de Pedido: #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong>
            </div>
            
            <p class="success-message">
                <?php if ($order['estado'] === 'pendiente'): ?>
                    Hemos recibido tu pedido. Te enviaremos instrucciones de pago en breve.
                <?php else: ?>
                    Tu pedido ha sido confirmado y está siendo procesado.
                <?php endif; ?>
            </p>
            
            <div class="order-actions">
                <a href="/user/orders/<?= $order['id'] ?>" class="btn btn-primary">
                    <i class="fas fa-eye"></i>
                    Ver Detalles del Pedido
                </a>
                
                <a href="/productos" class="btn btn-outline-primary">
                    <i class="fas fa-shopping-bag"></i>
                    Seguir Comprando
                </a>
            </div>
        </div>
    </div>
    
    <!-- === INFORMACIÓN DEL PEDIDO === -->
    <div class="order-info-section">
        <div class="container">
            <div class="order-info-grid">
                
                <!-- === ESTADO DEL PEDIDO === -->
                <div class="info-card order-status-card">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-info-circle"></i>
                            Estado del Pedido
                        </h3>
                    </div>
                    
                    <div class="card-content">
                        <div class="current-status">
                            <div class="status-icon status-<?= $currentStatus['color'] ?>">
                                <i class="fas fa-<?= $currentStatus['icon'] ?>"></i>
                            </div>
                            <div class="status-info">
                                <h4><?= $currentStatus['text'] ?></h4>
                                <p>Realizado el <?= date('d/m/Y \a \l\a\s H:i', strtotime($order['fecha_pedido'])) ?></p>
                            </div>
                        </div>
                        
                        <!-- Progress Timeline -->
                        <div class="order-timeline">
                            <div class="timeline-item <?= in_array($order['estado'], ['pagado', 'preparando', 'enviado', 'entregado']) ? 'completed' : 'pending' ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h5>Pago Confirmado</h5>
                                    <p>
                                        <?php if (in_array($order['estado'], ['pagado', 'preparando', 'enviado', 'entregado'])): ?>
                                            Confirmado el <?= date('d/m/Y H:i', strtotime($order['fecha_pago'] ?? $order['fecha_pedido'])) ?>
                                        <?php else: ?>
                                            Esperando confirmación
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="timeline-item <?= in_array($order['estado'], ['preparando', 'enviado', 'entregado']) ? 'completed' : 'pending' ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h5>Preparando Pedido</h5>
                                    <p>
                                        <?php if (in_array($order['estado'], ['preparando', 'enviado', 'entregado'])): ?>
                                            En preparación
                                        <?php else: ?>
                                            Por iniciar
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="timeline-item <?= in_array($order['estado'], ['enviado', 'entregado']) ? 'completed' : 'pending' ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h5>Enviado</h5>
                                    <p>
                                        <?php if (in_array($order['estado'], ['enviado', 'entregado'])): ?>
                                            Enviado el <?= date('d/m/Y', strtotime($order['fecha_envio'] ?? $deliveryDate)) ?>
                                        <?php else: ?>
                                            Fecha estimada: <?= date('d/m/Y', strtotime($deliveryDate)) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="timeline-item <?= $order['estado'] === 'entregado' ? 'completed' : 'pending' ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h5>Entregado</h5>
                                    <p>
                                        <?php if ($order['estado'] === 'entregado'): ?>
                                            Entregado el <?= date('d/m/Y H:i', strtotime($order['fecha_entrega'])) ?>
                                        <?php else: ?>
                                            Fecha estimada: <?= date('d/m/Y', strtotime($deliveryDate . ' +1 day')) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- === INFORMACIÓN DE ENTREGA === -->
                <div class="info-card delivery-info-card">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-truck"></i>
                            Información de Entrega
                        </h3>
                    </div>
                    
                    <div class="card-content">
                        <div class="delivery-method">
                            <div class="method-icon">
                                <?php if ($deliveryInfo['metodo'] === 'express'): ?>
                                    <i class="fas fa-shipping-fast"></i>
                                <?php elseif ($deliveryInfo['metodo'] === 'pickup'): ?>
                                    <i class="fas fa-store"></i>
                                <?php else: ?>
                                    <i class="fas fa-truck"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="method-details">
                                <h4>
                                    <?php 
                                    switch($deliveryInfo['metodo']) {
                                        case 'express':
                                            echo 'Entrega Express';
                                            break;
                                        case 'pickup':
                                            echo 'Recoger en Tienda';
                                            break;
                                        default:
                                            echo 'Entrega Estándar';
                                    }
                                    ?>
                                </h4>
                                <p>
                                    <?php 
                                    switch($deliveryInfo['metodo']) {
                                        case 'express':
                                            echo '1-2 días hábiles';
                                            break;
                                        case 'pickup':
                                            echo 'Disponible al día siguiente';
                                            break;
                                        default:
                                            echo '3-5 días hábiles';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="delivery-address">
                            <h5>Dirección de Entrega:</h5>
                            <div class="address-details">
                                <p><strong><?= h($deliveryInfo['nombre_completo']) ?></strong></p>
                                <p><?= h($deliveryInfo['direccion_completa']) ?></p>
                                <p><?= h($deliveryInfo['ciudad']) ?>, <?= h($deliveryInfo['estado']) ?> <?= h($deliveryInfo['codigo_postal']) ?></p>
                                
                                <?php if ($deliveryInfo['telefono']): ?>
                                    <p>
                                        <i class="fas fa-phone"></i>
                                        <?= h($deliveryInfo['telefono']) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($deliveryInfo['referencia']): ?>
                                    <p class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= h($deliveryInfo['referencia']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($deliveryInfo['tracking_number']): ?>
                            <div class="tracking-info">
                                <h5>Número de Seguimiento:</h5>
                                <div class="tracking-number">
                                    <code><?= h($deliveryInfo['tracking_number']) ?></code>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('<?= h($deliveryInfo['tracking_number']) ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                
                                <a href="<?= $deliveryInfo['tracking_url'] ?? '#' ?>" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-external-link-alt"></i>
                                    Rastrear Paquete
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- === INFORMACIÓN DE PAGO === -->
                <div class="info-card payment-info-card">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-credit-card"></i>
                            Información de Pago
                        </h3>
                    </div>
                    
                    <div class="card-content">
                        <div class="payment-method">
                            <div class="method-icon">
                                <?php 
                                switch($paymentInfo['metodo']) {
                                    case 'card':
                                        echo '<i class="fas fa-credit-card"></i>';
                                        break;
                                    case 'paypal':
                                        echo '<i class="fab fa-paypal"></i>';
                                        break;
                                    case 'bank_transfer':
                                        echo '<i class="fas fa-university"></i>';
                                        break;
                                    case 'cash':
                                        echo '<i class="fas fa-money-bill-wave"></i>';
                                        break;
                                    default:
                                        echo '<i class="fas fa-credit-card"></i>';
                                }
                                ?>
                            </div>
                            
                            <div class="method-details">
                                <h4>
                                    <?php 
                                    switch($paymentInfo['metodo']) {
                                        case 'card':
                                            echo 'Tarjeta de Crédito/Débito';
                                            break;
                                        case 'paypal':
                                            echo 'PayPal';
                                            break;
                                        case 'bank_transfer':
                                            echo 'Transferencia Bancaria';
                                            break;
                                        case 'cash':
                                            echo 'Pago en Efectivo';
                                            break;
                                        default:
                                            echo 'Método de Pago';
                                    }
                                    ?>
                                </h4>
                                
                                <?php if ($paymentInfo['metodo'] === 'card' && $paymentInfo['ultimos_digitos']): ?>
                                    <p>**** **** **** <?= h($paymentInfo['ultimos_digitos']) ?></p>
                                <?php endif; ?>
                                
                                <?php if ($paymentInfo['referencia_pago']): ?>
                                    <p>Referencia: <?= h($paymentInfo['referencia_pago']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="payment-status">
                            <?php if ($order['estado'] === 'pendiente'): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Pago Pendiente</strong>
                                    
                                    <?php if ($paymentInfo['metodo'] === 'bank_transfer'): ?>
                                        <p>Realiza tu transferencia con los siguientes datos:</p>
                                        <div class="bank-details">
                                            <p><strong>Banco:</strong> BBVA Bancomer</p>
                                            <p><strong>Cuenta:</strong> 0123456789</p>
                                            <p><strong>CLABE:</strong> 012345678901234567</p>
                                            <p><strong>Concepto:</strong> Pedido #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></p>
                                        </div>
                                        
                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#uploadReceiptModal">
                                            <i class="fas fa-upload"></i>
                                            Subir Comprobante
                                        </button>
                                    <?php elseif ($paymentInfo['metodo'] === 'cash'): ?>
                                        <p>Paga en cualquiera de estas tiendas con tu código:</p>
                                        <div class="payment-code">
                                            <h4><?= $paymentInfo['codigo_pago'] ?? 'AGRO' . str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h4>
                                        </div>
                                        
                                        <div class="store-options">
                                            <span class="store-logo">OXXO</span>
                                            <span class="store-logo">7-Eleven</span>
                                            <span class="store-logo">Walmart</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Pago Confirmado</strong>
                                    <p>Recibido el <?= date('d/m/Y \a \l\a\s H:i', strtotime($paymentInfo['fecha_pago'] ?? $order['fecha_pedido'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- === RESUMEN DEL PEDIDO === -->
    <div class="order-summary-section">
        <div class="container">
            <div class="summary-card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-list-alt"></i>
                        Resumen del Pedido
                    </h3>
                </div>
                
                <div class="card-content">
                    <div class="order-items">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <?php if ($item['imagen_principal']): ?>
                                        <img src="<?= asset('uploads/products/' . $item['imagen_principal']) ?>" 
                                             alt="<?= h($item['nombre_producto']) ?>">
                                    <?php else: ?>
                                        <div class="image-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-details">
                                    <h4>
                                        <a href="/productos/<?= $item['producto_id'] ?>" target="_blank">
                                            <?= h($item['nombre_producto']) ?>
                                        </a>
                                    </h4>
                                    
                                    <p class="item-vendor">
                                        <i class="fas fa-store"></i>
                                        <?= h($item['vendedor_nombre']) ?>
                                    </p>
                                    
                                    <div class="item-quantity">
                                        <span>Cantidad: <?= number_format($item['cantidad'], 2) ?> <?= h($item['unidad']) ?></span>
                                        <span>Precio unitario: $<?= number_format($item['precio_unitario'], 2) ?></span>
                                    </div>
                                    
                                    <?php if ($item['descuento'] > 0): ?>
                                        <div class="item-discount">
                                            <span class="badge badge-success">
                                                <i class="fas fa-tag"></i>
                                                <?= number_format($item['descuento'], 0) ?>% descuento
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-price">
                                    <h5>$<?= number_format($item['subtotal'], 2) ?></h5>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="totals-row">
                            <span>Subtotal:</span>
                            <span>$<?= number_format($order['subtotal'], 2) ?></span>
                        </div>
                        
                        <?php if ($order['descuento'] > 0): ?>
                            <div class="totals-row discount">
                                <span>
                                    <i class="fas fa-tag"></i>
                                    Descuento:
                                </span>
                                <span>-$<?= number_format($order['descuento'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="totals-row">
                            <span>Envío:</span>
                            <span>
                                <?php if ($order['costo_envio'] > 0): ?>
                                    $<?= number_format($order['costo_envio'], 2) ?>
                                <?php else: ?>
                                    GRATIS
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="totals-row">
                            <span>IVA (16%):</span>
                            <span>$<?= number_format($order['impuesto'], 2) ?></span>
                        </div>
                        
                        <div class="totals-row total">
                            <span>Total:</span>
                            <span>$<?= number_format($order['total'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- === ACCIONES ADICIONALES === -->
    <div class="additional-actions">
        <div class="container">
            <div class="actions-grid">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    
                    <div class="action-content">
                        <h4>Factura</h4>
                        <p>Descarga o solicita tu factura fiscal</p>
                    </div>
                    
                    <div class="action-button">
                        <a href="/orders/<?= $order['id'] ?>/invoice" class="btn btn-outline-primary">
                            <i class="fas fa-download"></i>
                            Descargar
                        </a>
                    </div>
                </div>
                
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    
                    <div class="action-content">
                        <h4>Soporte</h4>
                        <p>¿Necesitas ayuda con tu pedido?</p>
                    </div>
                    
                    <div class="action-button">
                        <a href="/soporte?order=<?= $order['id'] ?>" class="btn btn-outline-primary">
                            <i class="fas fa-comment-alt"></i>
                            Contactar
                        </a>
                    </div>
                </div>
                
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    
                    <div class="action-content">
                        <h4>Calificar</h4>
                        <p>Comparte tu experiencia</p>
                    </div>
                    
                    <div class="action-button">
                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#ratingModal">
                            <i class="fas fa-star"></i>
                            Calificar
                        </button>
                    </div>
                </div>
                
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    
                    <div class="action-content">
                        <h4>Compartir</h4>
                        <p>Comparte AgroConecta con amigos</p>
                    </div>
                    
                    <div class="action-button">
                        <button type="button" class="btn btn-outline-primary" onclick="shareOrder()">
                            <i class="fas fa-share"></i>
                            Compartir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- === PRODUCTOS RELACIONADOS === -->
    <div class="related-products-section">
        <div class="container">
            <h3>
                <i class="fas fa-shopping-bag"></i>
                También te Puede Interesar
            </h3>
            
            <div class="products-slider" id="relatedProducts">
                <!-- Los productos se cargarán dinámicamente -->
                <div class="loading-products">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Cargando productos...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para subir comprobante de pago -->
<div class="modal fade" id="uploadReceiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload"></i>
                    Subir Comprobante de Pago
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            
            <form id="uploadReceiptForm" enctype="multipart/form-data">
                <?= csrf_token() ?>
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="receipt_file" class="form-label">
                            Selecciona el archivo *
                        </label>
                        <input type="file" 
                               id="receipt_file" 
                               name="receipt_file" 
                               class="form-control-file" 
                               accept=".jpg,.jpeg,.png,.pdf"
                               required>
                        <small class="form-text text-muted">
                            Formatos permitidos: JPG, PNG, PDF (máximo 5MB)
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="receipt_notes" class="form-label">
                            Notas adicionales (opcional)
                        </label>
                        <textarea id="receipt_notes" 
                                  name="receipt_notes" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Agrega cualquier información adicional sobre el pago"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i>
                        Subir Comprobante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de calificación -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-star"></i>
                    Califica tu Experiencia
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            
            <form id="ratingForm">
                <?= csrf_token() ?>
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                
                <div class="modal-body">
                    <!-- Aquí iría el formulario de calificación -->
                    <div class="rating-section">
                        <h6>Califica nuestro servicio:</h6>
                        <div class="star-rating" id="serviceRating">
                            <i class="far fa-star" data-rating="1"></i>
                            <i class="far fa-star" data-rating="2"></i>
                            <i class="far fa-star" data-rating="3"></i>
                            <i class="far fa-star" data-rating="4"></i>
                            <i class="far fa-star" data-rating="5"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="rating_comment" class="form-label">
                            Comentarios (opcional)
                        </label>
                        <textarea id="rating_comment" 
                                  name="rating_comment" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Comparte tu experiencia con nosotros"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Enviar Calificación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Funciones para la página de confirmación
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            showAlert('success', 'Número de seguimiento copiado al portapapeles');
        });
    }
    
    function shareOrder() {
        if (navigator.share) {
            navigator.share({
                title: 'AgroConecta - Productos Frescos del Campo',
                text: '¡Acabo de hacer un pedido en AgroConecta! Productos frescos directamente del campo.',
                url: window.location.origin
            });
        } else {
            // Fallback para navegadores que no soportan Web Share API
            const url = window.location.origin;
            const text = encodeURIComponent('¡Acabo de hacer un pedido en AgroConecta! Productos frescos directamente del campo.');
            
            const shareLinks = {
                facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
                twitter: `https://twitter.com/intent/tweet?text=${text}&url=${url}`,
                whatsapp: `https://wa.me/?text=${text}%20${url}`
            };
            
            // Crear modal con opciones de compartir
            showShareModal(shareLinks);
        }
    }
    
    function showShareModal(links) {
        const modalHtml = `
            <div class="modal fade" id="shareModal" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Compartir</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <a href="${links.facebook}" target="_blank" class="btn btn-primary btn-lg mb-2 btn-block">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                            <a href="${links.twitter}" target="_blank" class="btn btn-info btn-lg mb-2 btn-block">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                            <a href="${links.whatsapp}" target="_blank" class="btn btn-success btn-lg btn-block">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        $('#shareModal').modal('show');
        
        $('#shareModal').on('hidden.bs.modal', function () {
            this.remove();
        });
    }
    
    function showAlert(type, message) {
        const alertEl = document.createElement('div');
        alertEl.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        alertEl.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        
        alertEl.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
            ${message}
            <button type="button" class="close" onclick="this.parentElement.remove()">
                <span>&times;</span>
            </button>
        `;
        
        document.body.appendChild(alertEl);
        
        setTimeout(() => {
            if (alertEl.parentElement) {
                alertEl.remove();
            }
        }, 5000);
    }
    
    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar productos relacionados
        loadRelatedProducts();
        
        // Configurar formularios
        setupUploadReceiptForm();
        setupRatingForm();
        setupStarRating();
    });
    
    function loadRelatedProducts() {
        // Aquí cargarías productos relacionados via AJAX
        setTimeout(() => {
            const container = document.getElementById('relatedProducts');
            container.innerHTML = '<p class="text-center text-muted">Productos relacionados próximamente</p>';
        }, 2000);
    }
    
    function setupUploadReceiptForm() {
        const form = document.getElementById('uploadReceiptForm');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                try {
                    const response = await fetch('/orders/upload-receipt', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        $('#uploadReceiptModal').modal('hide');
                        showAlert('success', 'Comprobante subido exitosamente. Procesaremos tu pago en breve.');
                        
                        // Opcional: recargar la página para mostrar el estado actualizado
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'Error al subir el comprobante');
                    }
                } catch (error) {
                    showAlert('error', error.message);
                }
            });
        }
    }
    
    function setupRatingForm() {
        const form = document.getElementById('ratingForm');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(form);
                const rating = document.querySelector('#serviceRating .fas')?.dataset.rating;
                
                if (rating) {
                    formData.append('rating', rating);
                }
                
                try {
                    const response = await fetch('/orders/rating', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        $('#ratingModal').modal('hide');
                        showAlert('success', '¡Gracias por tu calificación!');
                    } else {
                        throw new Error(data.message || 'Error al enviar la calificación');
                    }
                } catch (error) {
                    showAlert('error', error.message);
                }
            });
        }
    }
    
    function setupStarRating() {
        const stars = document.querySelectorAll('#serviceRating .fa-star');
        
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });
            });
            
            star.addEventListener('mouseover', function() {
                const rating = parseInt(this.dataset.rating);
                
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '';
                    }
                });
            });
        });
        
        document.getElementById('serviceRating').addEventListener('mouseleave', function() {
            stars.forEach(s => {
                if (s.classList.contains('far')) {
                    s.style.color = '';
                }
            });
        });
    }
</script>