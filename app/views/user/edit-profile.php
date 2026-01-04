<?php
/**
 * Vista de Edición de Perfil - AgroConecta
 * Formulario completo para editar datos personales
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
    <title>Editar Perfil - AgroConecta</title>
    
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

        .edit-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .edit-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            position: relative;
        }

        .edit-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(50px, -50px);
        }

        .edit-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .edit-header p {
            margin-bottom: 0;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .edit-form {
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e9ecef;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .section-title {
            color: var(--dark-color);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.1);
            background: white;
        }

        .required {
            color: var(--danger-color);
        }

        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .btn-custom {
            border-radius: 25px;
            padding: 0.75rem 2rem;
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

        .btn-secondary-custom {
            background: #6c757d;
            color: white;
        }

        .btn-secondary-custom:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: white;
        }

        .form-actions {
            background: #f8f9fa;
            padding: 1.5rem 2rem;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .verification-notice {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid var(--accent-color);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-color);
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            
            .edit-form {
                padding: 1rem;
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
                    <a class="nav-link" href="../../public/profile.php">
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
        <div class="edit-container">
            <!-- Header -->
            <div class="edit-header">
                <h1>
                    <i class="fas fa-user-edit me-2"></i>
                    Editar Perfil
                </h1>
                <p>Actualiza tu información personal y mantén tu perfil al día</p>
            </div>

            <!-- Flash Messages -->
            <?php if (SessionManager::hasFlash('error')): ?>
                <div class="alert alert-danger m-3">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= SessionManager::getFlash('error') ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="update-profile.php" class="edit-form" id="editProfileForm">
                <input type="hidden" name="csrf_token" value="<?= SessionManager::generateCSRF() ?>">
                
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        Información Personal
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">
                                    Nombre <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="<?= htmlspecialchars($user['nombre']) ?>" 
                                       required
                                       maxlength="50">
                                <div class="help-text">Tu nombre se mostrará públicamente</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="apellido" class="form-label">
                                    Apellido(s) <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="apellido" 
                                       name="apellido" 
                                       value="<?= htmlspecialchars($user['apellido']) ?>" 
                                       required
                                       maxlength="50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        Información de Contacto
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="correo" class="form-label">
                                    Correo Electrónico <span class="required">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="correo" 
                                       name="correo" 
                                       value="<?= htmlspecialchars($user['correo']) ?>" 
                                       required>
                                <?php if ($user['correo'] !== (strtolower(trim($_POST['correo'] ?? '')))): ?>
                                <div class="verification-notice">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Importante:</strong> Si cambias tu email, necesitarás verificar la nueva dirección.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefono" class="form-label">
                                    Teléfono
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="<?= htmlspecialchars($user['telefono'] ?? '') ?>" 
                                       placeholder="Ej: 555-123-4567">
                                <div class="help-text">Incluye código de área (opcional)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        Dirección (Opcional)
                    </h3>
                    
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" 
                               class="form-control" 
                               id="direccion" 
                               name="direccion" 
                               value="<?= htmlspecialchars($user['direccion'] ?? '') ?>" 
                               placeholder="Calle, número, colonia">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="ciudad" class="form-label">Ciudad</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="ciudad" 
                                       name="ciudad" 
                                       value="<?= htmlspecialchars($user['ciudad'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Seleccionar estado</option>
                                    <?php foreach ($estados as $key => $nombre): ?>
                                        <option value="<?= $key ?>" <?= ($user['estado'] ?? '') === $key ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($nombre) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="codigo_postal" class="form-label">Código Postal</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="codigo_postal" 
                                       name="codigo_postal" 
                                       value="<?= htmlspecialchars($user['codigo_postal'] ?? '') ?>" 
                                       pattern="[0-9]{5}"
                                       maxlength="5"
                                       placeholder="12345">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Type Section (Read-only) -->
                <div class="form-section">
                    <h3 class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        Información de la Cuenta
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Usuario</label>
                                <div class="form-control bg-light">
                                    <i class="fas fa-<?= $user['tipo_usuario'] === 'vendedor' ? 'store' : 'shopping-bag' ?> me-2"></i>
                                    <?= ucfirst($user['tipo_usuario']) ?>
                                </div>
                                <div class="help-text">No se puede cambiar después del registro</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estado de Verificación</label>
                                <div class="form-control bg-light">
                                    <?php if ($user['verificado']): ?>
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Email verificado
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                        Email sin verificar
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="profile.php" class="btn btn-secondary-custom">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
                <button type="submit" form="editProfileForm" class="btn btn-primary-custom">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editProfileForm');
            const inputs = form.querySelectorAll('input, select');
            
            // Add change indicators
            inputs.forEach(input => {
                const originalValue = input.value;
                input.addEventListener('input', function() {
                    if (this.value !== originalValue) {
                        this.classList.add('border-warning');
                    } else {
                        this.classList.remove('border-warning');
                    }
                });
            });
            
            // Phone number formatting
            const phoneInput = document.getElementById('telefono');
            phoneInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length >= 10) {
                    value = value.substring(0, 10);
                    value = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
                }
                this.value = value;
            });
            
            // Postal code validation
            const postalInput = document.getElementById('codigo_postal');
            postalInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').substring(0, 5);
            });
            
            // Form submission confirmation
            form.addEventListener('submit', function(e) {
                const changedInputs = form.querySelectorAll('.border-warning');
                if (changedInputs.length === 0) {
                    e.preventDefault();
                    alert('No se han detectado cambios en el formulario.');
                    return false;
                }
                
                const emailInput = document.getElementById('correo');
                const originalEmail = '<?= $user['correo'] ?>';
                if (emailInput.value !== originalEmail) {
                    if (!confirm('Al cambiar tu email necesitarás verificar la nueva dirección. ¿Continuar?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
        });
    </script>
</body>
</html>