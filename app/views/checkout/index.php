<?php 
// Verificar que el usuario esté autenticado o permitir checkout como invitado
$isGuest = !isset($_SESSION['user_id']);
$user = null;

if (!$isGuest) {
    $user = $this->userModel->findById($_SESSION['user_id']);
    $userAddresses = $this->addressModel->getUserAddresses($_SESSION['user_id']);
    $userPaymentMethods = $this->paymentMethodModel->getUserPaymentMethods($_SESSION['user_id']);
} else {
    $userAddresses = [];
    $userPaymentMethods = [];
}

// Obtener items del carrito
$cartItems = [];
$cartSubtotal = 0;
$shippingCost = 0;
$taxAmount = 0;
$discountAmount = 0;

if (!$isGuest) {
    $cartItems = $this->cartModel->getUserCartItems($_SESSION['user_id']);
} else {
    $cartItems = $this->cartModel->getSessionCartItems();
}

// Calcular totales
foreach ($cartItems as &$item) {
    $item['subtotal'] = $item['precio'] * $item['cantidad'];
    $cartSubtotal += $item['subtotal'];
}

// Verificar que hay productos en el carrito
if (empty($cartItems)) {
    redirect('/cart');
}

// Aplicar descuentos si hay
$couponCode = $_SESSION['coupon_code'] ?? null;
if ($couponCode) {
    $discount = $this->couponModel->validateAndGetDiscount($couponCode, $cartSubtotal);
    if ($discount) {
        $discountAmount = $discount['amount'];
    }
}

// Calcular impuestos (IVA 16% en México)
$taxRate = 0.16;
$taxableAmount = $cartSubtotal - $discountAmount;
$taxAmount = $taxableAmount * $taxRate;

// El cálculo final del total se hará dinámicamente con la dirección seleccionada
$cartTotal = $cartSubtotal + $taxAmount - $discountAmount;

// Estados de México para el formulario
$estados = [
    'aguascalientes' => 'Aguascalientes',
    'baja_california' => 'Baja California',
    'baja_california_sur' => 'Baja California Sur',
    'campeche' => 'Campeche',
    'chiapas' => 'Chiapas',
    'chihuahua' => 'Chihuahua',
    'coahuila' => 'Coahuila',
    'colima' => 'Colima',
    'cdmx' => 'Ciudad de México',
    'durango' => 'Durango',
    'guanajuato' => 'Guanajuato',
    'guerrero' => 'Guerrero',
    'hidalgo' => 'Hidalgo',
    'jalisco' => 'Jalisco',
    'mexico' => 'Estado de México',
    'michoacan' => 'Michoacán',
    'morelos' => 'Morelos',
    'nayarit' => 'Nayarit',
    'nuevo_leon' => 'Nuevo León',
    'oaxaca' => 'Oaxaca',
    'puebla' => 'Puebla',
    'queretaro' => 'Querétaro',
    'quintana_roo' => 'Quintana Roo',
    'san_luis_potosi' => 'San Luis Potosí',
    'sinaloa' => 'Sinaloa',
    'sonora' => 'Sonora',
    'tabasco' => 'Tabasco',
    'tamaulipas' => 'Tamaulipas',
    'tlaxcala' => 'Tlaxcala',
    'veracruz' => 'Veracruz',
    'yucatan' => 'Yucatán',
    'zacatecas' => 'Zacatecas'
];
?>

<div class="checkout-page">
    <!-- === BREADCRUMB === -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/"><i class="fas fa-home"></i> Inicio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="/cart"><i class="fas fa-shopping-cart"></i> Carrito</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-credit-card"></i> Checkout
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- === HEADER DEL CHECKOUT === -->
    <div class="checkout-header">
        <div class="container">
            <h1>
                <i class="fas fa-credit-card"></i>
                Finalizar Compra
            </h1>
            
            <!-- Progress Steps -->
            <div class="checkout-progress">
                <div class="progress-steps">
                    <div class="progress-step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Información</div>
                    </div>
                    
                    <div class="progress-line"></div>
                    
                    <div class="progress-step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Entrega</div>
                    </div>
                    
                    <div class="progress-line"></div>
                    
                    <div class="progress-step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Pago</div>
                    </div>
                    
                    <div class="progress-line"></div>
                    
                    <div class="progress-step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Confirmación</div>
                    </div>
                </div>
            </div>
            
            <!-- Security Badge -->
            <div class="security-info">
                <i class="fas fa-shield-alt"></i>
                <span>Conexión segura SSL de 256 bits</span>
            </div>
        </div>
    </div>
    
    <!-- === CONTENIDO DEL CHECKOUT === -->
    <div class="checkout-content">
        <div class="container">
            <form id="checkoutForm" method="POST" action="/checkout/process">
                <?= csrf_token() ?>
                
                <div class="checkout-layout">
                    <!-- === FORMULARIO DE CHECKOUT === -->
                    <div class="checkout-form">
                        
                        <!-- === PASO 1: INFORMACIÓN PERSONAL === -->
                        <div class="checkout-step active" data-step="1">
                            <div class="step-header">
                                <h2>
                                    <span class="step-number">1</span>
                                    Información Personal
                                </h2>
                                <p>Proporciona tus datos para la facturación y contacto</p>
                            </div>
                            
                            <div class="step-content">
                                <?php if ($isGuest): ?>
                                    <!-- Información para invitados -->
                                    <div class="guest-info">
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="guest_nombre" class="form-label">
                                                    Nombre(s) *
                                                </label>
                                                <input type="text" 
                                                       id="guest_nombre" 
                                                       name="guest_nombre" 
                                                       class="form-control" 
                                                       required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="guest_apellido" class="form-label">
                                                    Apellido(s) *
                                                </label>
                                                <input type="text" 
                                                       id="guest_apellido" 
                                                       name="guest_apellido" 
                                                       class="form-control" 
                                                       required>
                                            </div>
                                        </div>
                                        
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="guest_email" class="form-label">
                                                    Correo Electrónico *
                                                </label>
                                                <input type="email" 
                                                       id="guest_email" 
                                                       name="guest_email" 
                                                       class="form-control" 
                                                       required>
                                                <small class="form-text">Te enviaremos la confirmación y seguimiento del pedido</small>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="guest_telefono" class="form-label">
                                                    Teléfono *
                                                </label>
                                                <input type="tel" 
                                                       id="guest_telefono" 
                                                       name="guest_telefono" 
                                                       class="form-control" 
                                                       placeholder="55 1234 5678"
                                                       required>
                                                <small class="form-text">Para coordinar la entrega</small>
                                            </div>
                                        </div>
                                        
                                        <!-- Opción de crear cuenta -->
                                        <div class="account-creation-option">
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       id="create_account" 
                                                       name="create_account" 
                                                       class="form-check-input" 
                                                       value="1">
                                                <label for="create_account" class="form-check-label">
                                                    <strong>Crear cuenta para futuras compras</strong>
                                                    <small class="text-muted">Podrás rastrear tus pedidos y comprar más rápido</small>
                                                </label>
                                            </div>
                                            
                                            <div class="password-section" id="passwordSection" style="display: none;">
                                                <div class="form-group">
                                                    <label for="guest_password" class="form-label">
                                                        Contraseña *
                                                    </label>
                                                    <input type="password" 
                                                           id="guest_password" 
                                                           name="guest_password" 
                                                           class="form-control">
                                                    
                                                    <div class="password-strength">
                                                        <div class="strength-meter">
                                                            <div class="strength-bar" id="passwordStrengthBar"></div>
                                                        </div>
                                                        <small id="passwordStrengthText" class="strength-text">Ingresa tu contraseña</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="login-option">
                                        <hr>
                                        <p class="text-center">
                                            ¿Ya tienes cuenta? 
                                            <a href="/auth/login?redirect=checkout" class="btn btn-link">
                                                Iniciar sesión para checkout más rápido
                                            </a>
                                        </p>
                                    </div>
                                    
                                <?php else: ?>
                                    <!-- Usuario autenticado -->
                                    <div class="user-info">
                                        <div class="info-card">
                                            <div class="user-avatar">
                                                <?php if ($user['foto_perfil']): ?>
                                                    <img src="<?= asset('uploads/avatars/' . $user['foto_perfil']) ?>" alt="<?= h($user['nombre']) ?>">
                                                <?php else: ?>
                                                    <div class="avatar-placeholder">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="user-details">
                                                <h3><?= h($user['nombre'] . ' ' . $user['apellido']) ?></h3>
                                                <p><?= h($user['email']) ?></p>
                                                <p><?= h($user['telefono'] ?? 'Sin teléfono registrado') ?></p>
                                            </div>
                                            
                                            <div class="edit-info">
                                                <a href="/user/profile" class="btn btn-outline-secondary btn-sm" target="_blank">
                                                    <i class="fas fa-edit"></i>
                                                    Editar
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="step-actions">
                                    <button type="button" class="btn btn-success" onclick="nextStep(2)">
                                        Continuar a Entrega
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- === PASO 2: DIRECCIÓN DE ENTREGA === -->
                        <div class="checkout-step" data-step="2">
                            <div class="step-header">
                                <h2>
                                    <span class="step-number">2</span>
                                    Dirección de Entrega
                                </h2>
                                <p>Selecciona o proporciona la dirección donde quieres recibir tu pedido</p>
                            </div>
                            
                            <div class="step-content">
                                <?php if (!$isGuest && !empty($userAddresses)): ?>
                                    <!-- Usuario con direcciones guardadas -->
                                    <div class="saved-addresses">
                                        <h3>Mis Direcciones Guardadas</h3>
                                        
                                        <div class="addresses-grid">
                                            <?php foreach ($userAddresses as $index => $address): ?>
                                                <div class="address-card">
                                                    <input type="radio" 
                                                           id="address_<?= $address['id'] ?>" 
                                                           name="delivery_address" 
                                                           value="<?= $address['id'] ?>"
                                                           <?= $index === 0 ? 'checked' : '' ?>
                                                           onchange="selectAddress('saved', <?= $address['id'] ?>)">
                                                    
                                                    <label for="address_<?= $address['id'] ?>" class="address-label">
                                                        <div class="address-header">
                                                            <strong><?= h($address['alias'] ?? 'Dirección ' . ($index + 1)) ?></strong>
                                                            <?php if ($address['principal']): ?>
                                                                <span class="badge badge-primary">Principal</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <div class="address-details">
                                                            <p><?= h($address['direccion_completa']) ?></p>
                                                            <p><?= h($address['ciudad']) ?>, <?= h($address['estado']) ?> <?= h($address['codigo_postal']) ?></p>
                                                            
                                                            <?php if ($address['referencia']): ?>
                                                                <p class="text-muted">
                                                                    <i class="fas fa-map-marker-alt"></i>
                                                                    <?= h($address['referencia']) ?>
                                                                </p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                            
                                            <!-- Opción de nueva dirección -->
                                            <div class="address-card new-address">
                                                <input type="radio" 
                                                       id="new_address" 
                                                       name="delivery_address" 
                                                       value="new"
                                                       onchange="selectAddress('new', null)">
                                                
                                                <label for="new_address" class="address-label">
                                                    <div class="new-address-content">
                                                        <i class="fas fa-plus-circle"></i>
                                                        <strong>Usar Nueva Dirección</strong>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Formulario de nueva dirección -->
                                <div class="new-address-form" id="newAddressForm" 
                                     <?= (!$isGuest && !empty($userAddresses)) ? 'style="display: none;"' : '' ?>>
                                    
                                    <h3>
                                        <?= (!$isGuest && !empty($userAddresses)) ? 'Nueva Dirección' : 'Dirección de Entrega' ?>
                                    </h3>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="direccion_calle" class="form-label">
                                                Calle y Número *
                                            </label>
                                            <input type="text" 
                                                   id="direccion_calle" 
                                                   name="direccion_calle" 
                                                   class="form-control" 
                                                   placeholder="Ej: Av. Insurgentes 123">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="direccion_colonia" class="form-label">
                                                Colonia/Barrio *
                                            </label>
                                            <input type="text" 
                                                   id="direccion_colonia" 
                                                   name="direccion_colonia" 
                                                   class="form-control" 
                                                   placeholder="Ej: Roma Norte">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="direccion_ciudad" class="form-label">
                                                Ciudad *
                                            </label>
                                            <input type="text" 
                                                   id="direccion_ciudad" 
                                                   name="direccion_ciudad" 
                                                   class="form-control" 
                                                   placeholder="Ej: Ciudad de México">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="direccion_estado" class="form-label">
                                                Estado *
                                            </label>
                                            <select id="direccion_estado" name="direccion_estado" class="form-control">
                                                <option value="">Selecciona un estado</option>
                                                <?php foreach ($estados as $key => $nombre): ?>
                                                    <option value="<?= $key ?>"><?= $nombre ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="direccion_cp" class="form-label">
                                                Código Postal *
                                            </label>
                                            <input type="text" 
                                                   id="direccion_cp" 
                                                   name="direccion_cp" 
                                                   class="form-control" 
                                                   placeholder="Ej: 06700"
                                                   maxlength="5"
                                                   pattern="[0-9]{5}">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="direccion_referencia" class="form-label">
                                            Referencias (Opcional)
                                        </label>
                                        <textarea id="direccion_referencia" 
                                                  name="direccion_referencia" 
                                                  class="form-control" 
                                                  rows="2" 
                                                  placeholder="Ej: Casa azul con portón blanco, entre la farmacia y la panadería"></textarea>
                                    </div>
                                    
                                    <?php if (!$isGuest): ?>
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   id="save_address" 
                                                   name="save_address" 
                                                   class="form-check-input" 
                                                   value="1">
                                            <label for="save_address" class="form-check-label">
                                                Guardar esta dirección para futuras compras
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Opciones de entrega -->
                                <div class="delivery-options">
                                    <h3>Opciones de Entrega</h3>
                                    
                                    <div class="delivery-methods">
                                        <div class="delivery-method">
                                            <input type="radio" 
                                                   id="delivery_standard" 
                                                   name="delivery_method" 
                                                   value="standard" 
                                                   checked
                                                   onchange="updateDeliveryMethod('standard')">
                                            
                                            <label for="delivery_standard" class="method-label">
                                                <div class="method-icon">
                                                    <i class="fas fa-truck"></i>
                                                </div>
                                                <div class="method-info">
                                                    <strong>Entrega Estándar</strong>
                                                    <p>3-5 días hábiles</p>
                                                    <span class="method-price" id="standardPrice">$0.00</span>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <div class="delivery-method">
                                            <input type="radio" 
                                                   id="delivery_express" 
                                                   name="delivery_method" 
                                                   value="express"
                                                   onchange="updateDeliveryMethod('express')">
                                            
                                            <label for="delivery_express" class="method-label">
                                                <div class="method-icon">
                                                    <i class="fas fa-shipping-fast"></i>
                                                </div>
                                                <div class="method-info">
                                                    <strong>Entrega Express</strong>
                                                    <p>1-2 días hábiles</p>
                                                    <span class="method-price" id="expressPrice">$0.00</span>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <div class="delivery-method">
                                            <input type="radio" 
                                                   id="delivery_pickup" 
                                                   name="delivery_method" 
                                                   value="pickup"
                                                   onchange="updateDeliveryMethod('pickup')">
                                            
                                            <label for="delivery_pickup" class="method-label">
                                                <div class="method-icon">
                                                    <i class="fas fa-store"></i>
                                                </div>
                                                <div class="method-info">
                                                    <strong>Recoger en Tienda</strong>
                                                    <p>Disponible al día siguiente</p>
                                                    <span class="method-price">GRATIS</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="step-actions">
                                    <button type="button" class="btn btn-outline-secondary" onclick="previousStep(1)">
                                        <i class="fas fa-arrow-left"></i>
                                        Regresar
                                    </button>
                                    
                                    <button type="button" class="btn btn-success" onclick="nextStep(3)">
                                        Continuar a Pago
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- === PASO 3: MÉTODO DE PAGO === -->
                        <div class="checkout-step" data-step="3">
                            <div class="step-header">
                                <h2>
                                    <span class="step-number">3</span>
                                    Método de Pago
                                </h2>
                                <p>Selecciona cómo quieres pagar tu pedido</p>
                            </div>
                            
                            <div class="step-content">
                                <!-- Métodos de pago guardados -->
                                <?php if (!$isGuest && !empty($userPaymentMethods)): ?>
                                    <div class="saved-payment-methods">
                                        <h3>Mis Métodos de Pago</h3>
                                        
                                        <div class="payment-methods-grid">
                                            <?php foreach ($userPaymentMethods as $index => $method): ?>
                                                <div class="payment-method-card">
                                                    <input type="radio" 
                                                           id="payment_<?= $method['id'] ?>" 
                                                           name="payment_method" 
                                                           value="saved_<?= $method['id'] ?>"
                                                           <?= $index === 0 ? 'checked' : '' ?>
                                                           onchange="selectPaymentMethod('saved', <?= $method['id'] ?>)">
                                                    
                                                    <label for="payment_<?= $method['id'] ?>" class="payment-label">
                                                        <div class="card-icon">
                                                            <i class="fab fa-cc-<?= strtolower($method['tipo']) ?>"></i>
                                                        </div>
                                                        <div class="card-info">
                                                            <strong><?= ucfirst($method['tipo']) ?></strong>
                                                            <p>**** **** **** <?= $method['ultimos_digitos'] ?></p>
                                                            <small><?= $method['mes_expiracion'] ?>/<?= $method['ano_expiracion'] ?></small>
                                                        </div>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="payment-divider">
                                        <span>O elige otro método</span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Nuevos métodos de pago -->
                                <div class="new-payment-methods">
                                    <div class="payment-options">
                                        <!-- Tarjeta de Crédito/Débito -->
                                        <div class="payment-option">
                                            <input type="radio" 
                                                   id="payment_card" 
                                                   name="payment_method" 
                                                   value="card"
                                                   <?= ($isGuest || empty($userPaymentMethods)) ? 'checked' : '' ?>
                                                   onchange="selectPaymentMethod('card', null)">
                                            
                                            <label for="payment_card" class="payment-option-label">
                                                <div class="option-header">
                                                    <div class="option-icon">
                                                        <i class="fas fa-credit-card"></i>
                                                    </div>
                                                    <div class="option-info">
                                                        <strong>Tarjeta de Crédito/Débito</strong>
                                                        <p>Visa, Mastercard, American Express</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <!-- PayPal -->
                                        <div class="payment-option">
                                            <input type="radio" 
                                                   id="payment_paypal" 
                                                   name="payment_method" 
                                                   value="paypal"
                                                   onchange="selectPaymentMethod('paypal', null)">
                                            
                                            <label for="payment_paypal" class="payment-option-label">
                                                <div class="option-header">
                                                    <div class="option-icon">
                                                        <i class="fab fa-paypal"></i>
                                                    </div>
                                                    <div class="option-info">
                                                        <strong>PayPal</strong>
                                                        <p>Pago rápido y seguro con tu cuenta PayPal</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <!-- Transferencia Bancaria -->
                                        <div class="payment-option">
                                            <input type="radio" 
                                                   id="payment_transfer" 
                                                   name="payment_method" 
                                                   value="bank_transfer"
                                                   onchange="selectPaymentMethod('transfer', null)">
                                            
                                            <label for="payment_transfer" class="payment-option-label">
                                                <div class="option-header">
                                                    <div class="option-icon">
                                                        <i class="fas fa-university"></i>
                                                    </div>
                                                    <div class="option-info">
                                                        <strong>Transferencia Bancaria</strong>
                                                        <p>SPEI - Mismo día o siguiente día hábil</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <!-- Pago en Efectivo -->
                                        <div class="payment-option">
                                            <input type="radio" 
                                                   id="payment_cash" 
                                                   name="payment_method" 
                                                   value="cash"
                                                   onchange="selectPaymentMethod('cash', null)">
                                            
                                            <label for="payment_cash" class="payment-option-label">
                                                <div class="option-header">
                                                    <div class="option-icon">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </div>
                                                    <div class="option-info">
                                                        <strong>Pago en Efectivo</strong>
                                                        <p>OXXO, 7-Eleven, Walmart y más</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Formulario de tarjeta (mostrado por defecto) -->
                                <div class="payment-form" id="cardPaymentForm">
                                    <div class="card-form">
                                        <h4>Información de la Tarjeta</h4>
                                        
                                        <div class="form-group">
                                            <label for="card_number" class="form-label">
                                                Número de Tarjeta *
                                            </label>
                                            <input type="text" 
                                                   id="card_number" 
                                                   name="card_number" 
                                                   class="form-control" 
                                                   placeholder="1234 5678 9012 3456"
                                                   maxlength="19">
                                            <div class="card-icons">
                                                <i class="fab fa-cc-visa"></i>
                                                <i class="fab fa-cc-mastercard"></i>
                                                <i class="fab fa-cc-amex"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="card_expiry" class="form-label">
                                                    MM/AA *
                                                </label>
                                                <input type="text" 
                                                       id="card_expiry" 
                                                       name="card_expiry" 
                                                       class="form-control" 
                                                       placeholder="MM/AA"
                                                       maxlength="5">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="card_cvv" class="form-label">
                                                    CVV *
                                                    <i class="fas fa-question-circle" 
                                                       title="Código de 3 dígitos en el reverso de tu tarjeta"></i>
                                                </label>
                                                <input type="text" 
                                                       id="card_cvv" 
                                                       name="card_cvv" 
                                                       class="form-control" 
                                                       placeholder="123"
                                                       maxlength="4">
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="card_name" class="form-label">
                                                Nombre en la Tarjeta *
                                            </label>
                                            <input type="text" 
                                                   id="card_name" 
                                                   name="card_name" 
                                                   class="form-control" 
                                                   placeholder="Nombre como aparece en la tarjeta">
                                        </div>
                                        
                                        <?php if (!$isGuest): ?>
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       id="save_card" 
                                                       name="save_card" 
                                                       class="form-check-input" 
                                                       value="1">
                                                <label for="save_card" class="form-check-label">
                                                    Guardar esta tarjeta para futuras compras
                                                </label>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Información adicional para otros métodos -->
                                <div class="payment-form" id="paypalPaymentForm" style="display: none;">
                                    <div class="paypal-info">
                                        <p>Serás redirigido a PayPal para completar tu pago de forma segura.</p>
                                        <div class="paypal-logo">
                                            <i class="fab fa-paypal"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="payment-form" id="transferPaymentForm" style="display: none;">
                                    <div class="transfer-info">
                                        <h4>Instrucciones para Transferencia</h4>
                                        <p>Al confirmar tu pedido, recibirás los datos bancarios para realizar la transferencia.</p>
                                        <ul>
                                            <li>El pedido se procesará al recibir el comprobante</li>
                                            <li>Tiempo límite: 24 horas</li>
                                            <li>Envía el comprobante a: pagos@agroconecta.mx</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="payment-form" id="cashPaymentForm" style="display: none;">
                                    <div class="cash-info">
                                        <h4>Pago en Tiendas de Conveniencia</h4>
                                        <p>Recibirás un código para pagar en las siguientes tiendas:</p>
                                        <div class="store-logos">
                                            <span class="store-logo">OXXO</span>
                                            <span class="store-logo">7-Eleven</span>
                                            <span class="store-logo">Walmart</span>
                                            <span class="store-logo">Soriana</span>
                                        </div>
                                        <p><strong>Tiempo límite para pagar:</strong> 48 horas</p>
                                    </div>
                                </div>
                                
                                <div class="step-actions">
                                    <button type="button" class="btn btn-outline-secondary" onclick="previousStep(2)">
                                        <i class="fas fa-arrow-left"></i>
                                        Regresar
                                    </button>
                                    
                                    <button type="button" class="btn btn-success" onclick="nextStep(4)">
                                        Revisar Pedido
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- === PASO 4: CONFIRMACIÓN === -->
                        <div class="checkout-step" data-step="4">
                            <div class="step-header">
                                <h2>
                                    <span class="step-number">4</span>
                                    Revisar y Confirmar
                                </h2>
                                <p>Verifica que toda la información sea correcta antes de realizar tu pedido</p>
                            </div>
                            
                            <div class="step-content">
                                <!-- Resumen del pedido -->
                                <div class="order-review">
                                    <div class="review-section">
                                        <h4>Información Personal</h4>
                                        <div class="review-info" id="personalInfoReview">
                                            <!-- Se llenará dinámicamente -->
                                        </div>
                                        <button type="button" class="btn btn-link btn-sm" onclick="previousStep(1)">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </div>
                                    
                                    <div class="review-section">
                                        <h4>Dirección de Entrega</h4>
                                        <div class="review-info" id="deliveryInfoReview">
                                            <!-- Se llenará dinámicamente -->
                                        </div>
                                        <button type="button" class="btn btn-link btn-sm" onclick="previousStep(2)">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </div>
                                    
                                    <div class="review-section">
                                        <h4>Método de Pago</h4>
                                        <div class="review-info" id="paymentInfoReview">
                                            <!-- Se llenará dinámicamente -->
                                        </div>
                                        <button type="button" class="btn btn-link btn-sm" onclick="previousStep(3)">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Términos y condiciones -->
                                <div class="terms-section">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               id="accept_terms" 
                                               name="accept_terms" 
                                               class="form-check-input" 
                                               required>
                                        <label for="accept_terms" class="form-check-label">
                                            Acepto los 
                                            <a href="/terms" target="_blank">Términos y Condiciones</a> 
                                            y la 
                                            <a href="/privacy" target="_blank">Política de Privacidad</a>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               id="accept_newsletter" 
                                               name="accept_newsletter" 
                                               class="form-check-input">
                                        <label for="accept_newsletter" class="form-check-label">
                                            Quiero recibir ofertas especiales y novedades por email
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="step-actions">
                                    <button type="button" class="btn btn-outline-secondary" onclick="previousStep(3)">
                                        <i class="fas fa-arrow-left"></i>
                                        Regresar
                                    </button>
                                    
                                    <button type="submit" class="btn btn-success btn-lg" id="finalizeOrderBtn">
                                        <span class="btn-text">
                                            <i class="fas fa-check-circle"></i>
                                            Realizar Pedido
                                        </span>
                                        <span class="btn-loading" style="display: none;">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            Procesando...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- === RESUMEN DEL PEDIDO (SIDEBAR) === -->
                    <div class="order-summary">
                        <div class="summary-card">
                            <div class="summary-header">
                                <h3>
                                    <i class="fas fa-shopping-bag"></i>
                                    Resumen del Pedido
                                </h3>
                            </div>
                            
                            <div class="summary-items">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="summary-item">
                                        <div class="item-image">
                                            <?php if ($item['imagen_principal']): ?>
                                                <img src="<?= asset('uploads/products/' . $item['imagen_principal']) ?>" 
                                                     alt="<?= h($item['nombre']) ?>">
                                            <?php else: ?>
                                                <div class="image-placeholder">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="item-details">
                                            <h5><?= h($item['nombre']) ?></h5>
                                            <p class="item-vendor">
                                                <i class="fas fa-store"></i>
                                                <?= h($item['vendedor_nombre']) ?>
                                            </p>
                                            <div class="item-quantity">
                                                Cantidad: <?= number_format($item['cantidad'], 2) ?> <?= h($item['unidad']) ?>
                                            </div>
                                        </div>
                                        
                                        <div class="item-price">
                                            $<?= number_format($item['subtotal'], 2) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="summary-calculations">
                                <div class="calc-row">
                                    <span>Subtotal:</span>
                                    <span id="orderSubtotal">$<?= number_format($cartSubtotal, 2) ?></span>
                                </div>
                                
                                <?php if ($discountAmount > 0): ?>
                                    <div class="calc-row discount">
                                        <span>
                                            <i class="fas fa-tag"></i>
                                            Descuento:
                                        </span>
                                        <span>-$<?= number_format($discountAmount, 2) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="calc-row">
                                    <span>Envío:</span>
                                    <span id="orderShipping">$0.00</span>
                                </div>
                                
                                <div class="calc-row">
                                    <span>IVA (16%):</span>
                                    <span id="orderTax">$<?= number_format($taxAmount, 2) ?></span>
                                </div>
                                
                                <div class="calc-row total">
                                    <span>Total:</span>
                                    <span id="orderTotal">$<?= number_format($cartTotal, 2) ?></span>
                                </div>
                            </div>
                            
                            <!-- Información de garantías -->
                            <div class="guarantees">
                                <div class="guarantee-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Compra Protegida</span>
                                </div>
                                
                                <div class="guarantee-item">
                                    <i class="fas fa-undo"></i>
                                    <span>Garantía de Devolución</span>
                                </div>
                                
                                <div class="guarantee-item">
                                    <i class="fas fa-truck"></i>
                                    <span>Envío Asegurado</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="checkoutLoading" class="loading-overlay" style="display: none;">
    <div class="loading-spinner">
        <div class="spinner-border" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <p id="checkoutLoadingMessage">Procesando...</p>
    </div>
</div>

<script src="<?= asset('js/checkout.js') ?>"></script>