<?php
session_start();
require_once '../config/database.php';

// Verificar autenticación
if (!isset($_SESSION['user_id']) || 
    (!isset($_SESSION['user_tipo']) && !isset($_SESSION['tipo']))) {
    header('Location: login.php');
    exit();
}

$user_tipo = $_SESSION['user_tipo'] ?? $_SESSION['tipo'] ?? 'cliente';
if ($user_tipo !== 'cliente') {
    header('Location: dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Obtener items del carrito del usuario
    $stmt = $pdo->prepare("
        SELECT c.*, p.nombre, p.descripcion, p.precio, p.imagen_url, p.unidad_medida,
               u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
        FROM carrito c 
        JOIN producto p ON c.id_producto = p.id_producto 
        JOIN usuario u ON p.id_usuario = u.id_usuario
        WHERE c.id_usuario = ?
        ORDER BY p.nombre
    ");
    $stmt->execute([$user_id]);
    $items_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($items_carrito)) {
        // Debug: mostrar por qué el carrito está vacío
        echo "<!-- Debug: Carrito vacío para usuario ID: $user_id -->";
        $_SESSION['debug_message'] = "No se encontraron items en el carrito para el usuario ID: $user_id";
        header('Location: carrito.php?mensaje=carrito_vacio&debug=1');
        exit();
    }
    
    // Calcular total del carrito
    $total_carrito = 0;
    foreach ($items_carrito as $item) {
        $total_carrito += $item['precio'] * $item['cantidad'];
    }
    
    // Obtener direcciones del usuario
    $stmt = $pdo->prepare("
        SELECT * FROM direccion 
        WHERE id_usuario = ? AND activa = 1 
        ORDER BY es_principal DESC, nombre_direccion ASC
    ");
    $stmt->execute([$user_id]);
    $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener información del usuario
    $stmt = $pdo->prepare("SELECT nombre, apellido, correo, telefono FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error en confirmación de compra: " . $e->getMessage());
    $error = "Error al cargar la información. Intenta nuevamente.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Compra - AgroConecta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2d5a27;
            --secondary-color: #5cb85c;
            --accent-color: #8fbc8f;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .main-container {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        
        .confirmation-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        
        .confirmation-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .section-header {
            background-color: #f8f9fa;
            padding: 1rem 1.5rem;
            border-bottom: 3px solid var(--accent-color);
            margin-bottom: 1rem;
        }
        
        .section-header h4 {
            color: var(--primary-color);
            margin: 0;
            font-weight: 600;
        }
        
        .product-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s;
        }
        
        .product-item:hover {
            background-color: #f8f9fa;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 1rem;
        }
        
        .product-info {
            flex-grow: 1;
        }
        
        .product-name {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }
        
        .product-details {
            color: #666;
            font-size: 0.9rem;
        }
        
        .product-price {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--secondary-color);
            text-align: right;
            min-width: 120px;
        }
        
        .total-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.25rem 0;
        }
        
        .total-final {
            border-top: 2px solid var(--primary-color);
            padding-top: 0.75rem;
            margin-top: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .address-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }
        
        .address-card.selected {
            border-color: var(--secondary-color);
            background-color: #f8fff8;
        }
        
        .address-card:hover {
            border-color: var(--accent-color);
            background-color: #f8f9fa;
        }
        
        .btn-confirm {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
        }
        
        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-back {
            background: linear-gradient(135deg, #6c757d, #495057);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
        }
        
        .payment-methods {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .payment-option {
            flex: 1;
            min-width: 200px;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .payment-option:hover {
            border-color: var(--accent-color);
            background-color: #f8f9fa;
        }
        
        .payment-option.selected {
            border-color: var(--secondary-color);
            background-color: #f8fff8;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin: 0 1rem;
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--secondary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        
        .step-text {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .product-item {
                flex-direction: column;
                text-align: center;
            }
            
            .product-image {
                margin: 0 auto 1rem auto;
            }
            
            .payment-methods {
                flex-direction: column;
            }
            
            .step {
                flex-direction: column;
                margin: 0.5rem;
            }
            
            .step-number {
                margin: 0 0 0.25rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-leaf me-2"></i>AgroConecta
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <!-- Indicador de pasos -->
        <div class="step-indicator">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">Carrito</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">Confirmación</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">Pago</div>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- Card principal de confirmación -->
        <div class="confirmation-card">
            <div class="confirmation-header">
                <h1><i class="fas fa-shopping-cart me-3"></i>Confirmar Compra</h1>
                <p class="mb-0">Revisa los detalles de tu pedido antes de continuar</p>
            </div>

            <form id="confirmOrderForm" method="POST" action="procesar-pedido.php">
                <div class="row p-4">
                    <!-- Columna izquierda: Productos y dirección -->
                    <div class="col-lg-8">
                        <!-- Resumen de productos -->
                        <div class="section-header">
                            <h4><i class="fas fa-box me-2"></i>Productos en tu pedido</h4>
                        </div>

                        <?php if (!empty($items_carrito)): ?>
                        <div class="products-list">
                            <?php foreach ($items_carrito as $item): ?>
                            <div class="product-item">
                                <img src="<?php echo !empty($item['imagen_url']) ? htmlspecialchars($item['imagen_url']) : 'images/producto-default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['nombre']); ?>" 
                                     class="product-image">
                                <div class="product-info">
                                    <div class="product-name"><?php echo htmlspecialchars($item['nombre']); ?></div>
                                    <div class="product-details">
                                        <div>Vendedor: <?php echo htmlspecialchars($item['vendedor_nombre'] . ' ' . $item['vendedor_apellido']); ?></div>
                                        <div>Cantidad: <?php echo $item['cantidad']; ?> <?php echo htmlspecialchars($item['unidad_medida']); ?></div>
                                        <div>Precio unitario: $<?php echo number_format($item['precio'], 2); ?></div>
                                    </div>
                                </div>
                                <div class="product-price">
                                    $<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Selección de dirección -->
                        <div class="section-header mt-4">
                            <h4><i class="fas fa-map-marker-alt me-2"></i>Dirección de entrega</h4>
                        </div>

                        <?php if (!empty($direcciones)): ?>
                        <div class="addresses-list">
                            <?php foreach ($direcciones as $index => $direccion): ?>
                            <div class="address-card" data-address-id="<?php echo $direccion['id_direccion']; ?>">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="direccion_entrega" 
                                           value="<?php echo $direccion['id_direccion']; ?>" 
                                           id="direccion_<?php echo $direccion['id_direccion']; ?>"
                                           <?php echo ($direccion['es_principal'] || $index === 0) ? 'checked' : ''; ?> required>
                                    <label class="form-check-label w-100" for="direccion_<?php echo $direccion['id_direccion']; ?>">
                                        <div class="fw-bold"><?php echo htmlspecialchars($direccion['nombre_direccion']); ?>
                                            <?php if ($direccion['es_principal']): ?>
                                            <span class="badge bg-success ms-2">Principal</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted">
                                            <?php echo htmlspecialchars($direccion['calle'] . ' ' . $direccion['numero']); ?><br>
                                            <?php echo htmlspecialchars($direccion['colonia'] . ', ' . $direccion['municipio']); ?><br>
                                            <?php echo htmlspecialchars($direccion['estado'] . ' - CP ' . $direccion['cp']); ?>
                                            <?php if (!empty($direccion['referencias'])): ?>
                                            <br><small><strong>Referencias:</strong> <?php echo htmlspecialchars($direccion['referencias']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No tienes direcciones registradas. <a href="perfil.php" class="alert-link">Agrega una dirección</a> para continuar.
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Columna derecha: Resumen y pago -->
                    <div class="col-lg-4">
                        <!-- Resumen de compra -->
                        <div class="section-header">
                            <h4><i class="fas fa-receipt me-2"></i>Resumen de compra</h4>
                        </div>

                        <div class="total-section">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($total_carrito, 2); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Envío:</span>
                                <span class="text-success">Gratuito</span>
                            </div>
                            <div class="total-row total-final">
                                <span>Total:</span>
                                <span>$<?php echo number_format($total_carrito, 2); ?></span>
                            </div>
                        </div>

                        <!-- Método de pago -->
                        <div class="section-header mt-4">
                            <h4><i class="fas fa-credit-card me-2"></i>Método de pago</h4>
                        </div>

                        <div class="payment-methods">
                            <div class="payment-option selected" data-method="mercado_pago">
                                <i class="fab fa-paypal fa-2x text-primary mb-2"></i>
                                <div class="fw-bold">Mercado Pago</div>
                                <small class="text-muted">Pago seguro online</small>
                                <input type="radio" name="metodo_pago" value="mercado_pago" checked style="display: none;">
                            </div>
                            <div class="payment-option" data-method="transferencia">
                                <i class="fas fa-university fa-2x text-success mb-2"></i>
                                <div class="fw-bold">Transferencia</div>
                                <small class="text-muted">Transferencia bancaria</small>
                                <input type="radio" name="metodo_pago" value="transferencia" style="display: none;">
                            </div>
                            <div class="payment-option" data-method="efectivo">
                                <i class="fas fa-money-bill-wave fa-2x text-warning mb-2"></i>
                                <div class="fw-bold">Efectivo</div>
                                <small class="text-muted">Pago contra entrega</small>
                                <input type="radio" name="metodo_pago" value="efectivo" style="display: none;">
                            </div>
                        </div>

                        <!-- Notas del cliente -->
                        <div class="mt-4">
                            <label for="notas_cliente" class="form-label fw-bold">
                                <i class="fas fa-comment me-2"></i>Notas adicionales (opcional)
                            </label>
                            <textarea class="form-control" id="notas_cliente" name="notas_cliente" rows="3" 
                                      placeholder="Instrucciones especiales, horarios de entrega, etc."></textarea>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-confirm" <?php echo empty($direcciones) ? 'disabled' : ''; ?>>
                                <i class="fas fa-check me-2"></i>Confirmar Pedido
                            </button>
                            <a href="carrito.php" class="btn btn-secondary btn-back">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Carrito
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Manejo de selección de dirección
        document.querySelectorAll('.address-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remover selección anterior
                document.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected'));
                
                // Agregar selección actual
                this.classList.add('selected');
                
                // Marcar radio button
                this.querySelector('input[type="radio"]').checked = true;
            });
        });

        // Manejo de selección de método de pago
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remover selección anterior
                document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
                
                // Agregar selección actual
                this.classList.add('selected');
                
                // Marcar radio button
                this.querySelector('input[type="radio"]').checked = true;
            });
        });

        // Validación del formulario
        document.getElementById('confirmOrderForm').addEventListener('submit', function(e) {
            const direccionSelected = document.querySelector('input[name="direccion_entrega"]:checked');
            const metodoPagoSelected = document.querySelector('input[name="metodo_pago"]:checked');
            
            if (!direccionSelected) {
                e.preventDefault();
                alert('Por favor selecciona una dirección de entrega.');
                return false;
            }
            
            if (!metodoPagoSelected) {
                e.preventDefault();
                alert('Por favor selecciona un método de pago.');
                return false;
            }
            
            // Mostrar confirmación
            if (!confirm('¿Estás seguro de que deseas confirmar este pedido?')) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>