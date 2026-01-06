<?php
/**
 * Panel de Administración del Sistema - Solo Admins
 */

require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/SessionManager.php';

SessionManager::startSecureSession();

// Verificación de autenticación
if (!SessionManager::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userData = SessionManager::getUserData();
$user = [
    'id' => $userData['id'] ?? $_SESSION['user_id'],
    'nombre' => $userData['nombre'] ?? $_SESSION['user_nombre'] ?? 'Usuario',
    'tipo' => $userData['tipo'] ?? $_SESSION['user_tipo'] ?? 'cliente'
];

// Solo admin puede acceder
if ($user['tipo'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Obtener estadísticas del sistema
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Estadísticas de usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuario");
    $totalUsuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuario WHERE activo = 1");
    $usuariosActivos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Estadísticas de productos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM producto");
    $totalProductos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM producto WHERE activo = 1");
    $productosActivos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Estadísticas de pedidos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedido");
    $totalPedidos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Información de la base de datos
    $stmt = $pdo->query("SELECT version() as version");
    $versionDB = $stmt->fetch(PDO::FETCH_ASSOC)['version'];
    
    // Obtener tablas de la base de datos
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    error_log("Error en sistema: " . $e->getMessage());
    $totalUsuarios = $usuariosActivos = $totalProductos = $productosActivos = $totalPedidos = 0;
    $versionDB = "No disponible";
    $tablas = [];
}

// Información del servidor
$infoServidor = [
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'No disponible',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'No disponible',
    'server_admin' => $_SERVER['SERVER_ADMIN'] ?? 'No disponible',
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size')
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración del Sistema - AgroConecta</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Variables de color consistentes con el sistema */
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #4CAF50;
            --accent-color: #66BB6A;
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --border-color: #dee2e6;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --success-color: #28a745;
        }

        /* Base styles */
        body {
            background-color: var(--bg-secondary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* Navbar consistente */
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .main-container {
            margin-top: 2rem;
        }

        /* Cards del sistema */
        .system-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .system-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .system-header {
            padding: 1.25rem 1.5rem;
            color: white;
            background: var(--primary-color);
            border-radius: 0;
            position: relative;
        }

        .system-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, rgba(255,255,255,0.3), transparent);
        }

        .system-header.info { 
            background: linear-gradient(135deg, var(--info-color), #0bb3d9); 
        }
        .system-header.warning { 
            background: linear-gradient(135deg, var(--warning-color), #ffdb4a); 
            color: #000; 
        }
        .system-header.danger { 
            background: linear-gradient(135deg, var(--danger-color), #e55369); 
        }
        .system-header.success {
            background: linear-gradient(135deg, var(--success-color), var(--secondary-color));
        }

        /* Título de página */
        .page-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 2rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Indicadores de estado */
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .status-online { background: linear-gradient(135deg, var(--success-color), var(--secondary-color)); }
        .status-warning { background: linear-gradient(135deg, var(--warning-color), #ffdb4a); }
        .status-offline { background: linear-gradient(135deg, var(--danger-color), #e55369); }

        /* Métricas */
        .metric-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: none;
        }

        .metric-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Entradas de log */
        .log-entry {
            border-left: 4px solid var(--info-color);
            padding: 0.75rem 0 0.75rem 1rem;
            margin-bottom: 0.75rem;
            background: linear-gradient(90deg, rgba(13, 202, 240, 0.05), transparent);
            border-radius: 0 8px 8px 0;
            transition: all 0.3s ease;
        }

        .log-entry:hover {
            background: linear-gradient(90deg, rgba(13, 202, 240, 0.1), transparent);
            transform: translateX(2px);
        }

        /* Botones del sistema */
        .btn-system {
            border-radius: 10px;
            font-weight: 500;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-system:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* Tabla del servidor */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table td {
            border-color: var(--border-color);
            padding: 0.75rem;
        }

        .table td:first-child {
            background: linear-gradient(90deg, var(--bg-secondary), var(--bg-primary));
            font-weight: 600;
        }

        /* Badges especiales */
        .badge {
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
        }

        /* Efectos especiales para cards de estado */
        .system-card.status-card {
            background: linear-gradient(135deg, var(--bg-primary), #f8f9fa);
            border: 2px solid var(--success-color);
        }

        .system-card.status-card .system-header {
            background: linear-gradient(135deg, var(--success-color), var(--secondary-color));
        }

        /* Responsividad mejorada */
        @media (max-width: 768px) {
            .main-container {
                margin-top: 1rem;
                padding: 0 0.5rem;
            }
            
            .metric-value {
                font-size: 2rem;
            }
            
            .system-header {
                padding: 1rem;
            }
            
            .btn-system {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }
        }

        /* Animaciones adicionales */
        .system-card {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Estado especial para cards principales */
        .system-card.featured {
            border: 2px solid var(--primary-color);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.15);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-seedling me-2"></i>
                AgroConecta
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($user['nombre']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="page-title">
                    <i class="fas fa-server me-3"></i>
                    Administración del Sistema
                    <span class="badge bg-danger ms-2">ADMIN</span>
                </h1>
                <p class="lead text-muted">
                    Panel de control para monitoreo y administración de la plataforma
                </p>
            </div>
        </div>

        <!-- Estado del Sistema -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="system-card featured status-card">
                    <div class="system-header success">
                        <h5 class="mb-0">
                            <i class="fas fa-heartbeat me-2"></i>
                            Estado del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="mb-2">
                                    <span class="status-indicator status-online"></span>
                                    <strong>Sistema Online</strong>
                                </div>
                                <small class="text-muted">Funcionando correctamente</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="mb-2">
                                    <span class="status-indicator status-online"></span>
                                    <strong>Base de Datos</strong>
                                </div>
                                <small class="text-muted">Conexión estable</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="mb-2">
                                    <span class="status-indicator status-warning"></span>
                                    <strong>Almacenamiento</strong>
                                </div>
                                <small class="text-muted">75% utilizado</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="mb-2">
                                    <span class="status-indicator status-online"></span>
                                    <strong>API</strong>
                                </div>
                                <small class="text-muted">Respondiendo</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Estadísticas del Sistema -->
            <div class="col-lg-6">
                <div class="system-card">
                    <div class="system-header info">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Estadísticas del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-4">
                                <div class="p-3 rounded">
                                    <p class="metric-value text-primary"><?php echo $totalUsuarios; ?></p>
                                    <p class="metric-label">Total Usuarios</p>
                                    <small class="badge bg-success"><?php echo $usuariosActivos; ?> activos</small>
                                </div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="p-3 rounded">
                                    <p class="metric-value text-success"><?php echo $totalProductos; ?></p>
                                    <p class="metric-label">Total Productos</p>
                                    <small class="badge bg-success"><?php echo $productosActivos; ?> activos</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded">
                                    <p class="metric-value text-warning"><?php echo $totalPedidos; ?></p>
                                    <p class="metric-label">Total Pedidos</p>
                                    <small class="badge bg-secondary">Histórico</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded">
                                    <p class="metric-value text-info"><?php echo count($tablas); ?></p>
                                    <p class="metric-label">Tablas BD</p>
                                    <small class="badge bg-info">En uso</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Servidor -->
            <div class="col-lg-6">
                <div class="system-card">
                    <div class="system-header warning">
                        <h5 class="mb-0">
                            <i class="fas fa-server me-2"></i>
                            Información del Servidor
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>PHP:</strong></td>
                                    <td><?php echo $infoServidor['php_version']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Servidor:</strong></td>
                                    <td><?php echo $infoServidor['server_software']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Base de Datos:</strong></td>
                                    <td><?php echo substr($versionDB, 0, 20); ?>...</td>
                                </tr>
                                <tr>
                                    <td><strong>Memoria:</strong></td>
                                    <td><?php echo $infoServidor['memory_limit']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tiempo Max:</strong></td>
                                    <td><?php echo $infoServidor['max_execution_time']; ?>s</td>
                                </tr>
                                <tr>
                                    <td><strong>Subida Max:</strong></td>
                                    <td><?php echo $infoServidor['upload_max_filesize']; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Herramientas de Sistema -->
        <div class="row">
            <div class="col-lg-8">
                <div class="system-card">
                    <div class="system-header primary">
                        <h5 class="mb-0">
                            <i class="fas fa-tools me-2"></i>
                            Herramientas de Administración
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-users me-2"></i>Gestión de Usuarios</h6>
                                <div class="d-grid gap-2">
                                    <a href="usuarios.php" class="btn btn-outline-primary btn-system">
                                        <i class="fas fa-list me-2"></i>Ver Todos los Usuarios
                                    </a>
                                    <button class="btn btn-outline-warning btn-system" onclick="exportarUsuarios()">
                                        <i class="fas fa-download me-2"></i>Exportar Usuarios
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-boxes me-2"></i>Gestión de Productos</h6>
                                <div class="d-grid gap-2">
                                    <a href="productos.php" class="btn btn-outline-success btn-system">
                                        <i class="fas fa-eye me-2"></i>Ver Todos los Productos
                                    </a>
                                    <button class="btn btn-outline-info btn-system" onclick="actualizarStock()">
                                        <i class="fas fa-sync me-2"></i>Actualizar Inventario
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-chart-bar me-2"></i>Reportes y Analytics</h6>
                                <div class="d-grid gap-2">
                                    <a href="reportes.php" class="btn btn-outline-info btn-system">
                                        <i class="fas fa-chart-line me-2"></i>Ver Reportes
                                    </a>
                                    <button class="btn btn-outline-secondary btn-system" onclick="generarReporte()">
                                        <i class="fas fa-file-export me-2"></i>Generar Reporte
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-cogs me-2"></i>Mantenimiento</h6>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-warning btn-system" onclick="limpiarCache()">
                                        <i class="fas fa-trash me-2"></i>Limpiar Caché
                                    </button>
                                    <button class="btn btn-outline-danger btn-system" onclick="respaldoDB()">
                                        <i class="fas fa-database me-2"></i>Respaldar BD
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs del Sistema -->
            <div class="col-lg-4">
                <div class="system-card">
                    <div class="system-header danger">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            Logs Recientes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="log-entry">
                            <small class="text-muted">Hace 2 min</small><br>
                            <strong>Sistema:</strong> Usuario admin inició sesión
                        </div>
                        <div class="log-entry">
                            <small class="text-muted">Hace 15 min</small><br>
                            <strong>BD:</strong> Backup automático completado
                        </div>
                        <div class="log-entry">
                            <small class="text-muted">Hace 1 hora</small><br>
                            <strong>API:</strong> 1,234 peticiones procesadas
                        </div>
                        <div class="log-entry">
                            <small class="text-muted">Hace 2 horas</small><br>
                            <strong>Sistema:</strong> Mantenimiento programado
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-sm btn-outline-secondary" onclick="verTodosLogs()">
                                Ver Todos los Logs
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón Volver -->
        <div class="row mt-4">
            <div class="col">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Volver al Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function exportarUsuarios() {
            alert('Función de exportación de usuarios - En desarrollo');
        }
        
        function actualizarStock() {
            if(confirm('¿Actualizar todo el inventario del sistema?')) {
                alert('Inventario actualizado correctamente');
            }
        }
        
        function generarReporte() {
            alert('Generando reporte del sistema...');
        }
        
        function limpiarCache() {
            if(confirm('¿Limpiar la caché del sistema?')) {
                alert('Caché limpiada exitosamente');
            }
        }
        
        function respaldoDB() {
            if(confirm('¿Crear respaldo de la base de datos?')) {
                alert('Respaldo de BD iniciado. Recibirás notificación cuando termine.');
            }
        }
        
        function verTodosLogs() {
            alert('Visor de logs completo - En desarrollo');
        }
        
        // Auto-refresh de estadísticas cada 30 segundos
        setInterval(function() {
            // En una implementación real, aquí harías una petición AJAX
            // para actualizar las estadísticas sin recargar la página
            console.log('Actualizando estadísticas...');
        }, 30000);
    </script>
</body>
</html>