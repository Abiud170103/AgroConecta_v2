<?php
/**
 * Vista de Perfil Completo - AgroConecta
 * Perfil avanzado con estadísticas y gestión completa
 */

// Verificar que el usuario esté autenticado
if (!SessionManager::isLoggedIn()) {
    header('Location: ../../public/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - AgroConecta</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #20c997;
            --accent-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
            --light-bg: #f8f9fa;
            --border-radius: 15px;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .main-content {
            padding-top: 100px;
            padding-bottom: 50px;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(50px, -50px);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }

        .profile-info h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .profile-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .badge-custom {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .stat-icon.primary { background: rgba(40, 167, 69, 0.1); color: var(--primary-color); }
        .stat-icon.secondary { background: rgba(32, 201, 151, 0.1); color: var(--secondary-color); }
        .stat-icon.accent { background: rgba(255, 193, 7, 0.1); color: var(--accent-color); }
        .stat-icon.info { background: rgba(13, 202, 240, 0.1); color: #0dcaf0; }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .content-tabs {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .nav-tabs {
            border-bottom: 2px solid #f0f0f0;
            padding: 0 1rem;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background: none;
            border-bottom: 3px solid var(--primary-color);
        }

        .tab-content {
            padding: 2rem;
        }

        .info-group {
            margin-bottom: 1.5rem;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: block;
        }

        .info-value {
            color: #6c757d;
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .btn-custom {
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .btn-outline-custom {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-custom:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .security-section {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 5px solid var(--accent-color);
        }

        .change-password-form {
            background: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-top: 1rem;
            display: none;
        }

        .verification-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .status-verified {
            color: var(--primary-color);
        }

        .status-unverified {
            color: var(--danger-color);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .profile-header {
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../../public/index.php">
                <span class="me-2">🌱</span>
                <span class="fw-bold">AgroConecta</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="../../public/index.php">
                        <i class="fas fa-home me-1"></i>Inicio
                    </a>
                    <a class="nav-link active" href="#">
                        <i class="fas fa-user me-1"></i>Mi Perfil
                    </a>
                    <a class="nav-link" href="../../public/logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container main-content">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="profile-info ms-4">
                            <h1><?= htmlspecialchars($user['nombre'] . ' ' . $user['apellido']) ?></h1>
                            <p class="mb-1">
                                <i class="fas fa-envelope me-2"></i>
                                <?= htmlspecialchars($user['correo']) ?>
                            </p>
                            <div class="verification-status">
                                <?php if ($user['verificado']): ?>
                                    <span class="status-verified">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Email verificado
                                    </span>
                                <?php else: ?>
                                    <span class="status-unverified">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Email sin verificar
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="profile-badges">
                                <span class="badge-custom">
                                    <i class="fas fa-<?= $user['tipo_usuario'] === 'vendedor' ? 'store' : 'shopping-bag' ?> me-1"></i>
                                    <?= ucfirst($user['tipo_usuario']) ?>
                                </span>
                                <span class="badge-custom">
                                    <i class="fas fa-calendar me-1"></i>
                                    Miembro desde <?= date('M Y', strtotime($user['fecha_creacion'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="action-buttons">
                        <a href="edit-profile.php" class="btn btn-primary-custom">
                            <i class="fas fa-edit me-2"></i>Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (SessionManager::hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= SessionManager::getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (SessionManager::hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= SessionManager::getFlash('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (SessionManager::hasFlash('info')): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <i class="fas fa-info-circle me-2"></i>
                <?= SessionManager::getFlash('info') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-value"><?= $stats['productos_publicados'] ?></div>
                    <div class="stat-label">Productos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon secondary">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value"><?= $stats['ventas_totales'] ?></div>
                    <div class="stat-label">Ventas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon accent">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value"><?= number_format($stats['calificacion_promedio'], 1) ?></div>
                    <div class="stat-label">Calificación</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?= $stats['pedidos_pendientes'] ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
            <?php else: ?>
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-value"><?= $stats['compras_totales'] ?></div>
                    <div class="stat-label">Compras</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon secondary">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-value"><?= $stats['pedidos_realizados'] ?></div>
                    <div class="stat-label">Pedidos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon accent">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value">$<?= number_format($stats['dinero_gastado'], 2) ?></div>
                    <div class="stat-label">Gastado</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-value"><?= $stats['productos_favoritos'] ?></div>
                    <div class="stat-label">Favoritos</div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Content Tabs -->
        <div class="content-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info-tab">
                        <i class="fas fa-info-circle me-2"></i>Información Personal
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security-tab">
                        <i class="fas fa-shield-alt me-2"></i>Seguridad
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#settings-tab">
                        <i class="fas fa-cog me-2"></i>Configuración
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Personal Information Tab -->
                <div class="tab-pane fade show active" id="info-tab">
                    <h5 class="mb-4">
                        <i class="fas fa-user me-2 text-primary"></i>
                        Información Personal
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <span class="info-label">Nombre Completo</span>
                                <div class="info-value">
                                    <?= htmlspecialchars($user['nombre'] . ' ' . $user['apellido']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <span class="info-label">Correo Electrónico</span>
                                <div class="info-value">
                                    <?= htmlspecialchars($user['correo']) ?>
                                    <?php if ($user['verificado']): ?>
                                        <i class="fas fa-check-circle text-success ms-2" title="Verificado"></i>
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle text-warning ms-2" title="Sin verificar"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <span class="info-label">Teléfono</span>
                                <div class="info-value">
                                    <?= !empty($user['telefono']) ? htmlspecialchars($user['telefono']) : 'No especificado' ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <span class="info-label">Tipo de Usuario</span>
                                <div class="info-value">
                                    <i class="fas fa-<?= $user['tipo_usuario'] === 'vendedor' ? 'store' : 'shopping-bag' ?> me-2"></i>
                                    <?= ucfirst($user['tipo_usuario']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="edit-profile.php" class="btn btn-primary-custom">
                            <i class="fas fa-edit me-2"></i>Editar Información
                        </a>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="tab-pane fade" id="security-tab">
                    <h5 class="mb-4">
                        <i class="fas fa-shield-alt me-2 text-primary"></i>
                        Seguridad de la Cuenta
                    </h5>

                    <div class="security-section">
                        <h6><i class="fas fa-key me-2"></i>Contraseña</h6>
                        <p class="text-muted">Mantén tu cuenta segura con una contraseña fuerte</p>
                        <button class="btn btn-outline-custom" onclick="togglePasswordForm()">
                            <i class="fas fa-lock me-2"></i>Cambiar Contraseña
                        </button>

                        <!-- Change Password Form -->
                        <div class="change-password-form" id="passwordForm">
                            <form method="POST" action="change-password.php">
                                <input type="hidden" name="csrf_token" value="<?= SessionManager::generateCSRF() ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Contraseña Actual</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" name="new_password" minlength="6" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary-custom">
                                        <i class="fas fa-save me-2"></i>Guardar Cambios
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordForm()">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if (!$user['verificado']): ?>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Email sin verificar:</strong> Tu cuenta no está completamente segura hasta verificar tu email.
                        <a href="../../public/email-verification.php" class="btn btn-sm btn-warning ms-2">
                            Verificar Ahora
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Settings Tab -->
                <div class="tab-pane fade" id="settings-tab">
                    <h5 class="mb-4">
                        <i class="fas fa-cog me-2 text-primary"></i>
                        Configuración de la Cuenta
                    </h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6><i class="fas fa-bell me-2 text-info"></i>Notificaciones</h6>
                                    <p class="text-muted small">Gestiona cómo recibes las notificaciones</p>
                                    <button class="btn btn-outline-info btn-sm">Configurar</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6><i class="fas fa-eye me-2 text-secondary"></i>Privacidad</h6>
                                    <p class="text-muted small">Controla quién puede ver tu información</p>
                                    <button class="btn btn-outline-secondary btn-sm">Configurar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Zona de Peligro
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Esta acción no se puede deshacer. Tu cuenta será desactivada permanentemente.</p>
                                <a href="account-settings.php" class="btn btn-outline-danger">
                                    <i class="fas fa-trash me-2"></i>Eliminar Cuenta
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePasswordForm() {
            const form = document.getElementById('passwordForm');
            form.style.display = form.style.display === 'none' || !form.style.display ? 'block' : 'none';
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
</body>
</html>