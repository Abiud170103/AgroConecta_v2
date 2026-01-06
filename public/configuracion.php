<?php
/**
 * Página de Configuración del Sistema
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
    'correo' => $userData['correo'] ?? $_SESSION['user_email'] ?? 'usuario@test.com',
    'tipo' => $userData['tipo'] ?? $_SESSION['user_tipo'] ?? 'cliente'
];

// Solo admin puede acceder a configuración del sistema
$esAdmin = $user['tipo'] === 'admin';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - AgroConecta</title>
    
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
        }

        body {
            background: linear-gradient(135deg, #E8F5E8 0%, #F1F8E9 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-custom {
            background: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .main-container {
            margin-top: 2rem;
        }

        .config-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            background: white;
            margin-bottom: 1.5rem;
        }

        .config-header {
            background: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1rem 1.5rem;
        }

        .page-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 10px;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
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
                        <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
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
                    <i class="fas fa-cogs me-3"></i>
                    <?php if ($esAdmin): ?>
                        Configuración del Sistema
                    <?php else: ?>
                        Configuración de Cuenta
                    <?php endif; ?>
                </h1>
                <p class="lead text-muted">
                    <?php if ($esAdmin): ?>
                        Administra los ajustes y configuraciones de la plataforma
                    <?php else: ?>
                        Gestiona tu perfil y preferencias de cuenta
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="row">
            <!-- Configuración de Perfil -->
            <div class="col-12">
                <div class="config-card">
                    <div class="config-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            Configuración de Perfil
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nombre Completo</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nombre']); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['correo']); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Usuario</label>
                                    <input type="text" class="form-control" value="<?php echo ucfirst($user['tipo']); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-column h-100">
                                    <p class="text-muted">
                                        Para modificar tu información personal, dirígete a la sección de perfil.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="perfil.php" class="btn btn-primary">
                                            <i class="fas fa-edit me-2"></i>
                                            Editar Perfil
                                        </a>
                                        <a href="change-password.php" class="btn btn-outline-secondary ms-2">
                                            <i class="fas fa-key me-2"></i>
                                            Cambiar Contraseña
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($esAdmin): ?>
            <!-- Configuración del Sistema (Solo Admin) -->
            <div class="col-md-6">
                <div class="config-card">
                    <div class="config-header">
                        <h5 class="mb-0">
                            <i class="fas fa-server me-2"></i>
                            Configuración del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="usuarios.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-users me-2"></i>
                                Gestionar Usuarios
                            </a>
                            <a href="productos.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-boxes me-2"></i>
                                Gestionar Productos
                            </a>
                            <a href="reportes.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-bar me-2"></i>
                                Ver Reportes
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración de Plataforma (Solo Admin) -->
            <div class="col-md-6">
                <div class="config-card">
                    <div class="config-header">
                        <h5 class="mb-0">
                            <i class="fas fa-tools me-2"></i>
                            Configuración de Plataforma
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Comisión de la Plataforma (%)</label>
                            <input type="number" class="form-control" value="5" min="0" max="100">
                            <small class="form-text text-muted">Porcentaje de comisión por venta</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Moneda del Sistema</label>
                            <select class="form-control">
                                <option value="MXN" selected>MXN - Peso Mexicano</option>
                                <option value="USD">USD - Dólar Americano</option>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="mantenance" checked>
                            <label class="form-check-label" for="mantenance">
                                Sistema Activo
                            </label>
                        </div>
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Guardar Configuración
                        </button>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Configuración de Notificaciones -->
            <div class="col-md-6">
                <div class="config-card">
                    <div class="config-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bell me-2"></i>
                            Notificaciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                            <label class="form-check-label" for="emailNotifications">
                                Notificaciones por email
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="orderUpdates" checked>
                            <label class="form-check-label" for="orderUpdates">
                                Actualizaciones de pedidos
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="promotions">
                            <label class="form-check-label" for="promotions">
                                Promociones y ofertas
                            </label>
                        </div>
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Guardar Preferencias
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enlaces Rápidos -->
            <div class="col-md-6">
                <div class="config-card">
                    <div class="config-header">
                        <h5 class="mb-0">
                            <i class="fas fa-link me-2"></i>
                            Enlaces Rápidos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="catalogo.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-shopping-bag me-2"></i>
                                Catálogo de Productos
                            </a>
                            <a href="favoritos.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-heart me-2"></i>
                                Mis Favoritos
                            </a>
                            <a href="carrito.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Mi Carrito
                            </a>
                            <a href="mis-pedidos.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-box me-2"></i>
                                Mis Pedidos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
        // Funcionalidad para guardar preferencias
        document.querySelector('.btn-primary').addEventListener('click', function() {
            alert('Configuración guardada exitosamente');
        });
    </script>
</body>
</html>