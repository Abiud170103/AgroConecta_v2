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
$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$pedido_id) {
    header('Location: dashboard.php');
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Obtener detalles del pedido
    $stmt = $pdo->prepare("
        SELECT p.*, d.calle, d.numero, d.colonia, d.municipio, d.estado, d.cp, d.referencias,
               pg.metodo as metodo_pago, pg.estado as estado_pago, pg.transaccion_id
        FROM pedido p
        LEFT JOIN direccion d ON p.id_direccion = d.id_direccion
        LEFT JOIN pago pg ON p.id_pedido = pg.id_pedido
        WHERE p.id_pedido = ? AND p.id_usuario = ?
    ");
    $stmt->execute([$pedido_id, $user_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        header('Location: dashboard.php');
        exit();
    }
    
    // Obtener detalles de productos del pedido
    $stmt = $pdo->prepare("
        SELECT dp.*, p.imagen_url, p.unidad_medida 
        FROM detallepedido dp
        LEFT JOIN producto p ON dp.id_producto = p.id_producto
        WHERE dp.id_pedido = ?
        ORDER BY dp.nombre_producto
    ");
    $stmt->execute([$pedido_id]);
    $detalles_pedido = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener información del ticket
    $stmt = $pdo->prepare("SELECT * FROM ticket WHERE id_pedido = ?");
    $stmt->execute([$pedido_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error en pedido confirmado: " . $e->getMessage());
    $error = "Error al cargar la información del pedido.";
}

// Limpiar mensaje de sesión
unset($_SESSION['pedido_confirmado']);
unset($_SESSION['numero_pedido']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - AgroConecta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2d5a27;
            --secondary-color: #5cb85c;
            --success-color: #28a745;
            --accent-color: #8fbc8f;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .success-container {
            margin: 2rem auto;
            max-width: 800px;
        }
        
        .success-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .success-header {
            background: linear-gradient(135deg, var(--success-color), var(--secondary-color));
            color: white;
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .success-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .success-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .order-details {
            padding: 2rem;
        }
        
        .order-number {
            background: linear-gradient(135deg, var(--accent-color), #c8e6c9);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .order-number h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .order-number p {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--success-color);
            margin: 0;
        }
        
        .detail-section {
            margin-bottom: 2rem;
        }
        
        .detail-header {
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .detail-header h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
        }
        
        .product-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: background-color 0.3s;
        }
        
        .product-item:hover {
            background-color: #f8f9fa;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
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
            font-size: 0.9rem;
            color: #666;
        }
        
        .product-price {
            font-weight: 600;
            color: var(--success-color);
            text-align: right;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        
        .status-pendiente {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .address-info, .payment-info {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            border-left: 4px solid var(--secondary-color);
        }
        
        .total-summary {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 1rem;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .total-final {
            border-top: 2px solid var(--primary-color);
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .action-buttons {
            padding: 2rem;
            background-color: #f8f9fa;
            text-align: center;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-secondary-custom {
            background: linear-gradient(135deg, #6c757d, #495057);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-secondary-custom:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--accent-color);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.75rem;
            top: 0.5rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--secondary-color);
        }
        
        .timeline-item.active::before {
            background: var(--success-color);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.3);
        }
        
        @media (max-width: 768px) {
            .success-header {
                padding: 2rem 1rem;
            }
            
            .success-icon {
                font-size: 3rem;
            }
            
            .success-title {
                font-size: 1.5rem;
            }
            
            .product-item {
                flex-direction: column;
                text-align: center;
            }
            
            .product-image {
                margin: 0 auto 1rem auto;
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
        </div>
    </nav>

    <div class="container success-container">
        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php else: ?>
        
        <div class="success-card">
            <!-- Header de éxito -->
            <div class="success-header">
                <i class="fas fa-check-circle success-icon"></i>
                <h1 class="success-title">¡Pedido Confirmado!</h1>
                <p class="success-subtitle">Tu pedido ha sido procesado exitosamente</p>
            </div>

            <div class="order-details">
                <!-- Número de pedido -->
                <div class="order-number">
                    <h3><i class="fas fa-receipt me-2"></i>Número de Pedido</h3>
                    <p><?php echo htmlspecialchars($pedido['numero_pedido']); ?></p>
                </div>

                <div class="row">
                    <!-- Información del pedido -->
                    <div class="col-lg-8">
                        <!-- Estado del pedido -->
                        <div class="detail-section">
                            <div class="detail-header">
                                <h5><i class="fas fa-info-circle me-2"></i>Estado del Pedido</h5>
                            </div>
                            <div class="timeline">
                                <div class="timeline-item active">
                                    <strong>Pedido Confirmado</strong>
                                    <div class="text-muted small">
                                        <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <strong>En Preparación</strong>
                                    <div class="text-muted small">Pendiente</div>
                                </div>
                                <div class="timeline-item">
                                    <strong>Enviado</strong>
                                    <div class="text-muted small">Pendiente</div>
                                </div>
                                <div class="timeline-item">
                                    <strong>Entregado</strong>
                                    <div class="text-muted small">Pendiente</div>
                                </div>
                            </div>
                        </div>

                        <!-- Productos -->
                        <div class="detail-section">
                            <div class="detail-header">
                                <h5><i class="fas fa-box me-2"></i>Productos Pedidos</h5>
                            </div>
                            <?php foreach ($detalles_pedido as $detalle): ?>
                            <div class="product-item">
                                <img src="<?php echo !empty($detalle['imagen_url']) ? htmlspecialchars($detalle['imagen_url']) : 'images/producto-default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($detalle['nombre_producto']); ?>" 
                                     class="product-image">
                                <div class="product-info">
                                    <div class="product-name"><?php echo htmlspecialchars($detalle['nombre_producto']); ?></div>
                                    <div class="product-details">
                                        Cantidad: <?php echo $detalle['cantidad']; ?> 
                                        <?php echo !empty($detalle['unidad_medida']) ? htmlspecialchars($detalle['unidad_medida']) : 'unidades'; ?>
                                        <br>Precio unitario: $<?php echo number_format($detalle['precio_unitario'], 2); ?>
                                    </div>
                                </div>
                                <div class="product-price">
                                    $<?php echo number_format($detalle['subtotal'], 2); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Dirección de entrega -->
                        <div class="detail-section">
                            <div class="detail-header">
                                <h5><i class="fas fa-map-marker-alt me-2"></i>Dirección de Entrega</h5>
                            </div>
                            <div class="address-info">
                                <strong><?php echo htmlspecialchars($pedido['calle'] . ' ' . $pedido['numero']); ?></strong><br>
                                <?php echo htmlspecialchars($pedido['colonia'] . ', ' . $pedido['municipio']); ?><br>
                                <?php echo htmlspecialchars($pedido['estado'] . ' - CP ' . $pedido['cp']); ?>
                                <?php if (!empty($pedido['referencias'])): ?>
                                <br><small><strong>Referencias:</strong> <?php echo htmlspecialchars($pedido['referencias']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($pedido['notas_cliente'])): ?>
                        <!-- Notas del cliente -->
                        <div class="detail-section">
                            <div class="detail-header">
                                <h5><i class="fas fa-comment me-2"></i>Notas Adicionales</h5>
                            </div>
                            <div class="alert alert-info">
                                <?php echo nl2br(htmlspecialchars($pedido['notas_cliente'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar con resumen -->
                    <div class="col-lg-4">
                        <!-- Información de pago -->
                        <div class="detail-section">
                            <div class="detail-header">
                                <h5><i class="fas fa-credit-card me-2"></i>Pago</h5>
                            </div>
                            <div class="payment-info">
                                <div class="mb-2">
                                    <strong>Método:</strong>
                                    <?php 
                                    $metodos = [
                                        'mercado_pago' => 'Mercado Pago',
                                        'transferencia' => 'Transferencia Bancaria',
                                        'efectivo' => 'Efectivo (Contra entrega)',
                                        'tarjeta' => 'Tarjeta'
                                    ];
                                    echo $metodos[$pedido['metodo_pago']] ?? $pedido['metodo_pago'];
                                    ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Estado:</strong>
                                    <span class="status-badge status-pendiente">
                                        <?php echo ucfirst($pedido['estado_pago']); ?>
                                    </span>
                                </div>
                                <?php if (!empty($pedido['transaccion_id'])): ?>
                                <div class="mb-0">
                                    <strong>ID Transacción:</strong><br>
                                    <code><?php echo htmlspecialchars($pedido['transaccion_id']); ?></code>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Resumen del pedido -->
                        <div class="total-summary">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($pedido['total'], 2); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Envío:</span>
                                <span class="text-success">Gratuito</span>
                            </div>
                            <div class="total-row total-final">
                                <span>Total:</span>
                                <span>$<?php echo number_format($pedido['total'], 2); ?></span>
                            </div>
                        </div>

                        <?php if ($ticket): ?>
                        <!-- Información del ticket -->
                        <div class="detail-section">
                            <div class="detail-header">
                                <h5><i class="fas fa-file-invoice me-2"></i>Recibo</h5>
                            </div>
                            <div class="payment-info">
                                <div class="mb-2">
                                    <strong>Código:</strong><br>
                                    <code><?php echo htmlspecialchars($ticket['codigo']); ?></code>
                                </div>
                                <div class="mb-0">
                                    <strong>Fecha de emisión:</strong><br>
                                    <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_emision'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="action-buttons">
                <div class="d-grid gap-2 d-md-block">
                    <a href="mis-pedidos.php" class="btn btn-primary-custom">
                        <i class="fas fa-list me-2"></i>Ver Mis Pedidos
                    </a>
                    <a href="catalogo.php" class="btn btn-secondary-custom">
                        <i class="fas fa-shopping-bag me-2"></i>Seguir Comprando
                    </a>
                    <a href="dashboard.php" class="btn btn-secondary-custom">
                        <i class="fas fa-home me-2"></i>Ir al Dashboard
                    </a>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-scroll suave al cargar
        document.addEventListener('DOMContentLoaded', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</body>
</html>