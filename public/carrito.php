<?php
/**
 * Carrito de Compras - Clientes
 */

// Configuración básica
if (ob_get_level()) ob_end_clean();
ob_start();

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache'); 
header('Expires: 0');

require_once '../core/SessionManager.php';
require_once '../core/Database.php';

SessionManager::startSecureSession();

// Verificación de autenticación
if (!SessionManager::isLoggedIn()) {
    ob_end_clean();
    header('Location: login.php');
    exit;
}

$userData = SessionManager::getUserData();

// Verificar que getUserData no sea null
if (!$userData) {
    ob_end_clean();
    header('Location: login.php');
    exit;
}

// Verificar que sea cliente
if ($userData['tipo'] !== 'cliente') {
    ob_end_clean();
    header('Location: dashboard.php');
    exit;
}

// Obtener carrito real de la base de datos
$db = Database::getInstance();

// Consulta para obtener items del carrito
$carritoQuery = "SELECT c.*, p.nombre, p.precio, p.imagen_url, p.stock, u.nombre as vendedor_nombre
                FROM carrito c
                JOIN producto p ON c.id_producto = p.id_producto
                JOIN usuario u ON p.id_usuario = u.id_usuario
                WHERE c.id_usuario = ?
                ORDER BY c.fecha_agregado DESC";

$carritoItems = $db->select($carritoQuery, [$userData['id']]);

$carrito = [];
$total_productos = 0;
$subtotal = 0;

if ($carritoItems) {
    foreach ($carritoItems as $item) {
        $itemSubtotal = $item['cantidad'] * $item['precio'];
        $carrito[] = [
            'id_carrito' => $item['id_carrito'],
            'producto_id' => $item['id_producto'],
            'nombre' => $item['nombre'],
            'cantidad' => $item['cantidad'],
            'precio_unitario' => $item['precio'],
            'subtotal' => $itemSubtotal,
            'imagen' => $item['imagen_url'] ?? 'default-product.jpg',
            'vendedor' => $item['vendedor_nombre'],
            'stock_disponible' => $item['stock']
        ];
        
        $total_productos += $item['cantidad'];
        $subtotal += $itemSubtotal;
    }
}

$envio = ($subtotal > 500) ? 0 : 50.00;  // Envío gratis por compras mayores a $500
$descuentos = 0; // Por ahora sin descuentos, se puede agregar cupones después
$total = $subtotal + $envio - $descuentos;

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - AgroConecta</title>
    
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

        .product-image {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 25px;
            overflow: hidden;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border: none;
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .quantity-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .quantity-input {
            width: 50px;
            border: none;
            text-align: center;
            background: transparent;
            outline: none;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .price-row {
            border-bottom: 1px solid var(--border-color);
            padding: 10px 0;
        }

        .price-row.total-row {
            border-bottom: 2px solid var(--primary-color);
            font-weight: bold;
            font-size: 1.1rem;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-cart i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .shipping-options {
            background: var(--bg-secondary);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .promo-code {
            border-radius: 25px;
            border: 1px solid var(--border-color);
            padding: 10px 20px;
        }

        .recommended-products {
            background: var(--bg-primary);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }

        .product-card-mini {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .product-card-mini:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .progress-checkout {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .step.active .step-number {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .step-line {
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: var(--border-color);
            z-index: -1;
        }

        .step:last-child .step-line {
            display: none;
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
                        <a class="nav-link" href="favoritos.php">
                            <i class="fas fa-heart me-1"></i>Favoritos
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item me-3">
                        <a class="nav-link active position-relative" href="carrito.php">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $total_productos; ?>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($userData['nombre'] ?? 'Usuario'); ?>
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
        <?php if (isset($_GET['debug']) && isset($_SESSION['debug_message'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Debug:</strong> <?php echo htmlspecialchars($_SESSION['debug_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['debug_message']); endif; ?>
        
        <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php 
            if ($_GET['mensaje'] === 'carrito_vacio') {
                echo 'Tu carrito está vacío. <a href="catalogo.php" class="alert-link">¡Empieza a comprar aquí!</a>';
                echo '<br><small>Si crees que esto es un error, puedes <a href="agregar-carrito-prueba.php" class="alert-link">agregar items de prueba</a> o <a href="debug-carrito.php" class="alert-link">ver información de debug</a>.</small>';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2>
                    <i class="fas fa-shopping-cart text-primary me-2"></i>
                    Mi Carrito de Compras
                </h2>
                <p class="text-muted mb-0">Revisa y confirma tus productos antes de finalizar la compra</p>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="progress-checkout mb-4">
            <div class="step active">
                <div class="step-number">1</div>
                <small>Carrito</small>
                <div class="step-line"></div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <small>Envío</small>
                <div class="step-line"></div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <small>Pago</small>
                <div class="step-line"></div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <small>Confirmación</small>
            </div>
        </div>

        <?php if (empty($carrito)): ?>
            <!-- Empty Cart -->
            <div class="content-card p-5">
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Tu carrito está vacío</h3>
                    <p class="mb-4">¿Qué esperas? Agrega productos frescos y deliciosos a tu carrito</p>
                    <a href="catalogo.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-store me-2"></i>
                        Explorar Catálogo
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8 mb-4">
                    <div class="content-card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Productos en tu carrito (<?php echo $total_productos; ?> artículos)
                            </h5>
                            <button class="btn btn-outline-danger btn-sm" onclick="limpiarCarrito()">
                                <i class="fas fa-trash me-1"></i>Limpiar Carrito
                            </button>
                        </div>

                        <?php foreach ($carrito as $index => $item): ?>
                            <div class="row align-items-center p-3 mb-3" style="border: 1px solid var(--border-color); border-radius: 10px;" data-product-id="<?php echo $item['producto_id']; ?>">
                                <div class="col-md-2">
                                    <div class="product-image">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['nombre']); ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-store me-1"></i><?php echo htmlspecialchars($item['vendedor']); ?>
                                    </small>
                                    <br>
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>En stock (<?php echo $item['stock_disponible']; ?> disponibles)
                                    </small>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="cambiarCantidad(<?php echo $item['producto_id']; ?>, -1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="quantity-input" value="<?php echo $item['cantidad']; ?>" min="1" max="<?php echo $item['stock_disponible']; ?>">
                                        <button class="quantity-btn" onclick="cambiarCantidad(<?php echo $item['producto_id']; ?>, 1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-2 text-center">
                                    <div class="text-muted small">Precio unitario</div>
                                    <strong>$<?php echo number_format($item['precio_unitario'], 2); ?></strong>
                                </div>
                                
                                <div class="col-md-2 text-center">
                                    <div class="text-muted small">Subtotal</div>
                                    <strong class="text-primary subtotal-item">$<?php echo number_format($item['subtotal'], 2); ?></strong>
                                    <br>
                                    <button class="btn btn-link text-danger p-0 mt-1" onclick="eliminarProducto(<?php echo $item['producto_id']; ?>)" title="Eliminar producto">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Promo Code -->
                        <div class="mt-4 p-3" style="background: var(--bg-secondary); border-radius: 10px;">
                            <h6 class="mb-3">
                                <i class="fas fa-tag me-2"></i>
                                Código de Descuento
                            </h6>
                            <div class="input-group">
                                <input type="text" class="form-control promo-code" placeholder="Ingresa tu código de descuento" id="promoCode">
                                <button class="btn btn-primary" onclick="aplicarDescuento()">
                                    <i class="fas fa-check me-1"></i>Aplicar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="content-card p-4 sticky-top" style="top: 20px;">
                        <h5 class="mb-4">
                            <i class="fas fa-receipt me-2"></i>
                            Resumen del Pedido
                        </h5>
                        
                        <div class="price-row d-flex justify-content-between">
                            <span>Subtotal (<?php echo $total_productos; ?> productos)</span>
                            <span id="subtotal-display">$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="price-row d-flex justify-content-between">
                            <span>Envío</span>
                            <span class="text-success">$<?php echo number_format($envio, 2); ?></span>
                        </div>
                        
                        <div class="price-row d-flex justify-content-between text-success">
                            <span>
                                <i class="fas fa-tag me-1"></i>Descuentos
                            </span>
                            <span>-$<?php echo number_format($descuentos, 2); ?></span>
                        </div>
                        
                        <div class="price-row total-row d-flex justify-content-between">
                            <span>Total</span>
                            <span class="text-primary" id="total-display">$<?php echo number_format($total, 2); ?></span>
                        </div>

                        <!-- Shipping Options -->
                        <div class="shipping-options">
                            <h6 class="mb-3">
                                <i class="fas fa-truck me-2"></i>
                                Opciones de Envío
                            </h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="shipping" id="shipping1" value="50" checked>
                                <label class="form-check-label" for="shipping1">
                                    <strong>Envío Estándar</strong> - $50.00<br>
                                    <small class="text-muted">3-5 días hábiles</small>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="shipping" id="shipping2" value="100">
                                <label class="form-check-label" for="shipping2">
                                    <strong>Envío Express</strong> - $100.00<br>
                                    <small class="text-muted">1-2 días hábiles</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="shipping" id="shipping3" value="0">
                                <label class="form-check-label" for="shipping3">
                                    <strong>Recolección en Tienda</strong> - Gratis<br>
                                    <small class="text-muted">Disponible hoy</small>
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button class="btn btn-primary btn-lg" onclick="procederAlPago()">
                                <i class="fas fa-credit-card me-2"></i>
                                Proceder al Pago
                            </button>
                            <a href="catalogo.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Continuar Comprando
                            </a>
                        </div>

                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Compra segura y protegida
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommended Products -->
            <div class="recommended-products">
                <h5 class="mb-4">
                    <i class="fas fa-thumbs-up me-2"></i>
                    Productos Recomendados
                </h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="product-card-mini">
                            <div class="product-image mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fas fa-apple-alt"></i>
                            </div>
                            <h6 class="mb-2">Manzanas Rojas</h6>
                            <div class="text-primary mb-2">$32.50</div>
                            <button class="btn btn-outline-primary btn-sm" onclick="agregarAlCarrito(7)">
                                <i class="fas fa-plus me-1"></i>Agregar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="product-card-mini">
                            <div class="product-image mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fas fa-lemon"></i>
                            </div>
                            <h6 class="mb-2">Limones Frescos</h6>
                            <div class="text-primary mb-2">$18.00</div>
                            <button class="btn btn-outline-primary btn-sm" onclick="agregarAlCarrito(8)">
                                <i class="fas fa-plus me-1"></i>Agregar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="product-card-mini">
                            <div class="product-image mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fas fa-pepper-hot"></i>
                            </div>
                            <h6 class="mb-2">Chiles Jalapeños</h6>
                            <div class="text-primary mb-2">$25.75</div>
                            <button class="btn btn-outline-primary btn-sm" onclick="agregarAlCarrito(9)">
                                <i class="fas fa-plus me-1"></i>Agregar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="product-card-mini">
                            <div class="product-image mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fas fa-carrot"></i>
                            </div>
                            <h6 class="mb-2">Cebollas Blancas</h6>
                            <div class="text-primary mb-2">$22.00</div>
                            <button class="btn btn-outline-primary btn-sm" onclick="agregarAlCarrito(10)">
                                <i class="fas fa-plus me-1"></i>Agregar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Variables globales
        let carritoData = <?php echo json_encode($carrito); ?>;

        function cambiarCantidad(productId, cambio) {
            const row = document.querySelector(`[data-product-id="${productId}"]`);
            if (!row) return;
            
            const input = row.querySelector('.quantity-input');
            const cantidadActual = parseInt(input.value);
            const nuevaCantidad = Math.max(1, cantidadActual + cambio);
            
            // Buscar el item en el carrito para obtener stock
            const item = carritoData.find(item => item.producto_id == productId);
            if (!item) return;
            
            // Verificar stock disponible
            if (nuevaCantidad > item.stock_disponible) {
                showNotification('No hay suficiente stock disponible', 'warning');
                return;
            }
            
            // Mostrar loading en el input
            input.disabled = true;
            
            // Enviar actualización a la API
            fetch('api/carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'actualizar',
                    id: productId,
                    cantidad: nuevaCantidad
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar la interfaz
                    input.value = nuevaCantidad;
                    actualizarSubtotal(productId, nuevaCantidad, item.precio_unitario);
                    actualizarTotales();
                    showNotification('Cantidad actualizada', 'success');
                    
                    // Actualizar datos locales
                    item.cantidad = nuevaCantidad;
                    item.subtotal = item.precio_unitario * nuevaCantidad;
                } else {
                    showNotification('Error al actualizar cantidad: ' + (data.message || 'Error desconocido'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al actualizar cantidad', 'error');
            })
            .finally(() => {
                input.disabled = false;
            });
        }

        function actualizarSubtotal(productId, cantidad, precioUnitario) {
            const subtotal = precioUnitario * cantidad;
            
            const row = document.querySelector(`[data-product-id="${productId}"]`);
            const subtotalElement = row.querySelector('.subtotal-item');
            if (subtotalElement) {
                subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
            }
        }

        function eliminarProducto(productId) {
            if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
                return;
            }
            
            const row = document.querySelector(`[data-product-id="${productId}"]`);
            if (!row) return;
            
            // Efecto visual de eliminación
            row.style.transition = 'all 0.3s ease';
            row.style.transform = 'translateX(-100%)';
            row.style.opacity = '0';
            
            // Enviar eliminación a la API
            fetch('api/carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'eliminar',
                    id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setTimeout(() => {
                        row.remove();
                        carritoData = carritoData.filter(item => item.producto_id != productId);
                        actualizarTotales();
                        showNotification('Producto eliminado del carrito', 'success');
                        
                        // Verificar si el carrito está vacío
                        if (carritoData.length === 0) {
                            setTimeout(() => location.reload(), 1000);
                        }
                    }, 300);
                } else {
                    // Revertir el efecto visual si hay error
                    row.style.transform = 'translateX(0)';
                    row.style.opacity = '1';
                    showNotification('Error al eliminar producto: ' + (data.message || 'Error desconocido'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revertir el efecto visual si hay error
                row.style.transform = 'translateX(0)';
                row.style.opacity = '1';
                showNotification('Error al eliminar producto', 'error');
            });
        }

        function limpiarCarrito() {
            // Usar la función clearCart del cart.js
            if (typeof clearCart === 'function') {
                clearCart();
            } else {
                // Fallback si clearCart no está disponible
                if (confirm('¿Estás seguro de que quieres limpiar todo el carrito?')) {
                    fetch('api/carrito.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            action: 'limpiar'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Carrito limpiado exitosamente', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showNotification('Error al limpiar carrito: ' + (data.message || 'Error desconocido'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error al limpiar carrito', 'error');
                    });
                }
            }
        }

        function actualizarTotales() {
            const subtotal = carritoData.reduce((sum, item) => sum + item.subtotal, 0);
            const totalProductos = carritoData.reduce((sum, item) => sum + item.cantidad, 0);
            
            // Cálculo de envío (gratis si es mayor a $500)
            const envio = subtotal > 500 ? 0 : 50.00;
            const descuentos = 0; // Por ahora sin descuentos
            const total = subtotal + envio - descuentos;
            
            // Actualizar elementos del resumen si existen
            const subtotalElement = document.querySelector('[data-subtotal]') || document.getElementById('subtotal-display');
            if (subtotalElement) {
                subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
            }
            
            const envioElement = document.querySelector('[data-envio]') || document.getElementById('envio-display');
            if (envioElement) {
                envioElement.textContent = envio === 0 ? 'GRATIS' : `$${envio.toFixed(2)}`;
            }
            
            const totalElement = document.querySelector('[data-total]') || document.getElementById('total-display');
            if (totalElement) {
                totalElement.textContent = `$${total.toFixed(2)}`;
            }
            
            // Actualizar contador de productos en el badge del carrito
            const contadores = document.querySelectorAll('.badge');
            contadores.forEach(contador => {
                contador.textContent = totalProductos;
            });
            
            // Actualizar título del carrito
            const tituloCarrito = document.querySelector('h5:contains("Productos en tu carrito")');
            if (tituloCarrito) {
                tituloCarrito.innerHTML = `<i class="fas fa-list me-2"></i>Productos en tu carrito (${totalProductos} artículos)`;
            }
        }

        function aplicarDescuento() {
            const codigo = document.getElementById('promoCode').value.trim().toLowerCase();
            
            const codigosValidos = {
                'welcome10': 0.10,
                'fresh15': 0.15,
                'organic20': 0.20
            };
            
            if (codigosValidos[codigo]) {
                const descuento = codigosValidos[codigo];
                showNotification(`Código aplicado: ${(descuento * 100)}% de descuento`, 'success');
                actualizarTotales();
            } else {
                showNotification('Código de descuento no válido', 'warning');
            }
        }

        function agregarAlCarrito(productId) {
            showNotification('Producto agregado al carrito', 'success');
        }

        function procederAlPago() {
            console.log('Función procederAlPago ejecutada');
            console.log('Carrito datos:', carritoData);
            
            if (carritoData.length === 0) {
                showNotification('Tu carrito está vacío', 'warning');
                return;
            }
            
            // Redireccionar a la página de confirmación de compra
            showNotification('Redirigiendo a confirmación de compra...', 'info');
            console.log('Redirigiendo a confirmacion-compra.php');
            setTimeout(() => {
                window.location.href = 'confirmacion-compra.php';
            }, 1000);
        }

        // Event listeners para opciones de envío
        document.querySelectorAll('input[name="shipping"]').forEach(radio => {
            radio.addEventListener('change', actualizarTotales);
        });

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
            const items = document.querySelectorAll('[data-product-id]');
            items.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.6s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>