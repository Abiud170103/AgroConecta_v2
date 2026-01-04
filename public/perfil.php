<?php
/**
 * Perfil de Usuario - Sistema Adaptativo
 * Muestra perfil de cliente o vendedor según el tipo de usuario
 */

// Configuración básica
if (ob_get_level()) ob_end_clean();
ob_start();

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache'); 
header('Expires: 0');

session_start();

// Verificación de autenticación
if (!isset($_SESSION['user_id']) || 
    (!isset($_SESSION['user_tipo']) && !isset($_SESSION['tipo']))) {
    ob_end_clean();
    header('Location: login.php');
    exit;
}

$user = [
    'id' => $_SESSION['user_id'],
    'nombre' => $_SESSION['user_nombre'] ?? $_SESSION['nombre'] ?? 'Usuario Test',
    'correo' => $_SESSION['user_email'] ?? $_SESSION['correo'] ?? 'usuario@test.com',
    'tipo' => $_SESSION['user_tipo'] ?? $_SESSION['tipo'] ?? 'cliente',
    'tipo_usuario' => $_SESSION['user_tipo'] ?? $_SESSION['tipo'] ?? 'cliente'
];

// Procesar actualizaciones del perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'actualizar_perfil':
                    // Simulación de actualización - en producción conectar a BD
                    $_SESSION['user_nombre'] = $_POST['nombre'] ?? $user['nombre'];
                    $_SESSION['nombre'] = $_POST['nombre'] ?? $user['nombre'];
                    
                    $response = [
                        'success' => true,
                        'message' => 'Perfil actualizado exitosamente'
                    ];
                    break;
                    
                case 'cambiar_password':
                    // Simulación de cambio de contraseña
                    $response = [
                        'success' => true,
                        'message' => 'Contraseña actualizada exitosamente'
                    ];
                    break;
                    
                case 'actualizar_configuracion':
                    // Simulación de actualización de configuración
                    $response = [
                        'success' => true,
                        'message' => 'Configuración actualizada exitosamente'
                    ];
                    break;
            }
        }
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
        ];
    }
    
    if (isset($_POST['ajax'])) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Datos de ejemplo del perfil (en producción vendrían de la BD)
if ($user['tipo'] === 'cliente') {
    $perfilData = [
        'nombre' => $user['nombre'],
        'correo' => $user['correo'],
        'telefono' => '+52 55 1234 5678',
        'fecha_registro' => '2024-01-15',
        'direccion' => [
            'calle' => 'Av. Reforma 123',
            'colonia' => 'Centro',
            'ciudad' => 'Ciudad de México',
            'estado' => 'CDMX',
            'cp' => '06000'
        ],
        'preferencias' => [
            'categoria_favorita' => 'Verduras',
            'notificaciones_email' => true,
            'notificaciones_sms' => false,
            'ofertas_especiales' => true
        ],
        'estadisticas' => [
            'pedidos_realizados' => 15,
            'productos_favoritos' => 8,
            'ahorro_total' => 1250.50,
            'calificacion_promedio' => 4.7
        ]
    ];
} else {
    $perfilData = [
        'nombre' => $user['nombre'],
        'correo' => $user['correo'],
        'telefono' => '+52 55 9876 5432',
        'empresa' => 'Granja Verde SA de CV',
        'fecha_registro' => '2023-08-20',
        'direccion' => [
            'calle' => 'Carretera Nacional Km 45',
            'colonia' => 'Zona Rural',
            'ciudad' => 'Morelia',
            'estado' => 'Michoacán',
            'cp' => '58000'
        ],
        'negocio' => [
            'tipo_produccion' => 'Orgánica',
            'area_cultivo' => '25 hectáreas',
            'productos_principales' => ['Tomates', 'Lechugas', 'Zanahorias'],
            'certificaciones' => ['Orgánico', 'Comercio Justo', 'SENASICA']
        ],
        'estadisticas' => [
            'productos_vendidos' => 342,
            'ventas_totales' => 45300.75,
            'calificacion_promedio' => 4.9,
            'clientes_activos' => 68
        ]
    ];
}

ob_end_clean();
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
            --info-color: #17a2b8;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1rem;
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .profile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }

        .profile-card h5 {
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }

        .stat-icon.orders {
            background: linear-gradient(135deg, var(--info-color), #0d47a1);
            color: white;
        }

        .stat-icon.favorites {
            background: linear-gradient(135deg, var(--danger-color), #b71c1c);
            color: white;
        }

        .stat-icon.savings {
            background: linear-gradient(135deg, var(--success-color), #1b5e20);
            color: white;
        }

        .stat-icon.rating {
            background: linear-gradient(135deg, var(--warning-color), #e65100);
            color: white;
        }

        .stat-icon.products {
            background: linear-gradient(135deg, var(--secondary-color), #00695c);
            color: white;
        }

        .stat-icon.sales {
            background: linear-gradient(135deg, var(--primary-color), #2e7d32);
            color: white;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
            transform: translateY(-1px);
        }

        .tab-content {
            padding: 1.5rem 0;
        }

        .nav-pills .nav-link {
            border-radius: 25px;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-organic {
            background: linear-gradient(135deg, var(--success-color), #2e7d32);
            color: white;
        }

        .badge-certified {
            background: linear-gradient(135deg, var(--info-color), #0277bd);
            color: white;
        }

        .alert-custom {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.5rem;
        }

        @media (max-width: 768px) {
            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
            
            .profile-card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-leaf me-2"></i>
                AgroConecta
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    <?php if ($user['tipo'] === 'cliente'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="catalogo.php">
                            <i class="fas fa-store me-1"></i>
                            Catálogo
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" 
                           role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?= htmlspecialchars($user['nombre']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="perfil.php">
                                <i class="fas fa-user me-2"></i>Mi Perfil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="col-md-9">
                    <h1><?= htmlspecialchars($perfilData['nombre']) ?></h1>
                    <p class="lead mb-1">
                        <?= $user['tipo'] === 'cliente' ? 'Cliente' : 'Vendedor' ?> • 
                        Miembro desde <?= date('d/m/Y', strtotime($perfilData['fecha_registro'])) ?>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        <?= htmlspecialchars($perfilData['correo']) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="container">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php if ($user['tipo'] === 'cliente'): ?>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon orders">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($perfilData['estadisticas']['pedidos_realizados']) ?></h3>
                        <p class="text-muted mb-0">Pedidos Realizados</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon favorites">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($perfilData['estadisticas']['productos_favoritos']) ?></h3>
                        <p class="text-muted mb-0">Productos Favoritos</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon savings">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                        <h3 class="mb-1">$<?= number_format($perfilData['estadisticas']['ahorro_total'], 2) ?></h3>
                        <p class="text-muted mb-0">Ahorro Total</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon rating">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($perfilData['estadisticas']['calificacion_promedio'], 1) ?></h3>
                        <p class="text-muted mb-0">Calificación</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($perfilData['estadisticas']['productos_vendidos']) ?></h3>
                        <p class="text-muted mb-0">Productos Vendidos</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon sales">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3 class="mb-1">$<?= number_format($perfilData['estadisticas']['ventas_totales'], 2) ?></h3>
                        <p class="text-muted mb-0">Ventas Totales</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon rating">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($perfilData['estadisticas']['calificacion_promedio'], 1) ?></h3>
                        <p class="text-muted mb-0">Calificación</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon orders">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($perfilData['estadisticas']['clientes_activos']) ?></h3>
                        <p class="text-muted mb-0">Clientes Activos</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Profile Tabs -->
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-pills justify-content-center mb-4" id="profileTabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="info-tab" data-bs-toggle="pill" href="#info">
                            <i class="fas fa-info-circle me-2"></i>Información Personal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="address-tab" data-bs-toggle="pill" href="#address">
                            <i class="fas fa-map-marker-alt me-2"></i>Dirección
                        </a>
                    </li>
                    <?php if ($user['tipo'] === 'cliente'): ?>
                    <li class="nav-item">
                        <a class="nav-link" id="preferences-tab" data-bs-toggle="pill" href="#preferences">
                            <i class="fas fa-cog me-2"></i>Preferencias
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" id="business-tab" data-bs-toggle="pill" href="#business">
                            <i class="fas fa-building me-2"></i>Información del Negocio
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" id="security-tab" data-bs-toggle="pill" href="#security">
                            <i class="fas fa-shield-alt me-2"></i>Seguridad
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="profileTabsContent">
                    
                    <!-- Personal Information Tab -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <div class="profile-card">
                            <h5><i class="fas fa-user me-2"></i>Información Personal</h5>
                            <form id="personalInfoForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nombre Completo</label>
                                        <input type="text" class="form-control" name="nombre" 
                                               value="<?= htmlspecialchars($perfilData['nombre']) ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Correo Electrónico</label>
                                        <input type="email" class="form-control" name="correo" 
                                               value="<?= htmlspecialchars($perfilData['correo']) ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" name="telefono" 
                                               value="<?= htmlspecialchars($perfilData['telefono']) ?>">
                                    </div>
                                    <?php if ($user['tipo'] === 'vendedor'): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Empresa/Negocio</label>
                                        <input type="text" class="form-control" name="empresa" 
                                               value="<?= htmlspecialchars($perfilData['empresa']) ?>">
                                    </div>
                                    <?php else: ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Fecha de Registro</label>
                                        <input type="text" class="form-control" 
                                               value="<?= date('d/m/Y', strtotime($perfilData['fecha_registro'])) ?>" readonly>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Address Tab -->
                    <div class="tab-pane fade" id="address" role="tabpanel">
                        <div class="profile-card">
                            <h5><i class="fas fa-map-marker-alt me-2"></i>Dirección</h5>
                            <form id="addressForm">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Calle y Número</label>
                                        <input type="text" class="form-control" name="calle" 
                                               value="<?= htmlspecialchars($perfilData['direccion']['calle']) ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Código Postal</label>
                                        <input type="text" class="form-control" name="cp" 
                                               value="<?= htmlspecialchars($perfilData['direccion']['cp']) ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Colonia</label>
                                        <input type="text" class="form-control" name="colonia" 
                                               value="<?= htmlspecialchars($perfilData['direccion']['colonia']) ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ciudad</label>
                                        <input type="text" class="form-control" name="ciudad" 
                                               value="<?= htmlspecialchars($perfilData['direccion']['ciudad']) ?>">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Estado</label>
                                        <select class="form-select" name="estado">
                                            <option value="<?= htmlspecialchars($perfilData['direccion']['estado']) ?>" selected>
                                                <?= htmlspecialchars($perfilData['direccion']['estado']) ?>
                                            </option>
                                            <option value="Aguascalientes">Aguascalientes</option>
                                            <option value="Baja California">Baja California</option>
                                            <option value="Baja California Sur">Baja California Sur</option>
                                            <option value="Campeche">Campeche</option>
                                            <option value="CDMX">Ciudad de México</option>
                                            <option value="Chiapas">Chiapas</option>
                                            <option value="Chihuahua">Chihuahua</option>
                                            <option value="Coahuila">Coahuila</option>
                                            <option value="Colima">Colima</option>
                                            <option value="Durango">Durango</option>
                                            <option value="Guanajuato">Guanajuato</option>
                                            <option value="Guerrero">Guerrero</option>
                                            <option value="Hidalgo">Hidalgo</option>
                                            <option value="Jalisco">Jalisco</option>
                                            <option value="Estado de México">Estado de México</option>
                                            <option value="Michoacán">Michoacán</option>
                                            <option value="Morelos">Morelos</option>
                                            <option value="Nayarit">Nayarit</option>
                                            <option value="Nuevo León">Nuevo León</option>
                                            <option value="Oaxaca">Oaxaca</option>
                                            <option value="Puebla">Puebla</option>
                                            <option value="Querétaro">Querétaro</option>
                                            <option value="Quintana Roo">Quintana Roo</option>
                                            <option value="San Luis Potosí">San Luis Potosí</option>
                                            <option value="Sinaloa">Sinaloa</option>
                                            <option value="Sonora">Sonora</option>
                                            <option value="Tabasco">Tabasco</option>
                                            <option value="Tamaulipas">Tamaulipas</option>
                                            <option value="Tlaxcala">Tlaxcala</option>
                                            <option value="Veracruz">Veracruz</option>
                                            <option value="Yucatán">Yucatán</option>
                                            <option value="Zacatecas">Zacatecas</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Dirección
                                </button>
                            </form>
                        </div>
                    </div>

                    <?php if ($user['tipo'] === 'cliente'): ?>
                    <!-- Preferences Tab (Cliente) -->
                    <div class="tab-pane fade" id="preferences" role="tabpanel">
                        <div class="profile-card">
                            <h5><i class="fas fa-cog me-2"></i>Preferencias</h5>
                            <form id="preferencesForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Categoría Favorita</label>
                                        <select class="form-select" name="categoria_favorita">
                                            <option value="Verduras" <?= $perfilData['preferencias']['categoria_favorita'] === 'Verduras' ? 'selected' : '' ?>>
                                                Verduras
                                            </option>
                                            <option value="Frutas" <?= $perfilData['preferencias']['categoria_favorita'] === 'Frutas' ? 'selected' : '' ?>>
                                                Frutas
                                            </option>
                                            <option value="Granos" <?= $perfilData['preferencias']['categoria_favorita'] === 'Granos' ? 'selected' : '' ?>>
                                                Granos
                                            </option>
                                            <option value="Lácteos" <?= $perfilData['preferencias']['categoria_favorita'] === 'Lácteos' ? 'selected' : '' ?>>
                                                Lácteos
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label mb-3">Notificaciones</label>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="notificaciones_email" 
                                                           <?= $perfilData['preferencias']['notificaciones_email'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label">
                                                        Notificaciones por Email
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="notificaciones_sms" 
                                                           <?= $perfilData['preferencias']['notificaciones_sms'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label">
                                                        Notificaciones por SMS
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="ofertas_especiales" 
                                                           <?= $perfilData['preferencias']['ofertas_especiales'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label">
                                                        Recibir Ofertas Especiales
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Preferencias
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Business Info Tab (Vendedor) -->
                    <div class="tab-pane fade" id="business" role="tabpanel">
                        <div class="profile-card">
                            <h5><i class="fas fa-building me-2"></i>Información del Negocio</h5>
                            <form id="businessForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tipo de Producción</label>
                                        <select class="form-select" name="tipo_produccion">
                                            <option value="Orgánica" <?= $perfilData['negocio']['tipo_produccion'] === 'Orgánica' ? 'selected' : '' ?>>
                                                Orgánica
                                            </option>
                                            <option value="Convencional" <?= $perfilData['negocio']['tipo_produccion'] === 'Convencional' ? 'selected' : '' ?>>
                                                Convencional
                                            </option>
                                            <option value="Hidropónica" <?= $perfilData['negocio']['tipo_produccion'] === 'Hidropónica' ? 'selected' : '' ?>>
                                                Hidropónica
                                            </option>
                                            <option value="Sustentable" <?= $perfilData['negocio']['tipo_produccion'] === 'Sustentable' ? 'selected' : '' ?>>
                                                Sustentable
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Área de Cultivo</label>
                                        <input type="text" class="form-control" name="area_cultivo" 
                                               value="<?= htmlspecialchars($perfilData['negocio']['area_cultivo']) ?>">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Productos Principales</label>
                                        <input type="text" class="form-control" name="productos_principales" 
                                               value="<?= implode(', ', $perfilData['negocio']['productos_principales']) ?>">
                                        <small class="form-text text-muted">Separar con comas</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Certificaciones</label>
                                        <div class="row">
                                            <?php foreach ($perfilData['negocio']['certificaciones'] as $cert): ?>
                                            <div class="col-md-4 mb-2">
                                                <span class="badge badge-custom badge-certified">
                                                    <i class="fas fa-certificate me-1"></i>
                                                    <?= htmlspecialchars($cert) ?>
                                                </span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="fas fa-plus me-1"></i>Agregar Certificación
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Información
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <div class="profile-card">
                            <h5><i class="fas fa-shield-alt me-2"></i>Seguridad</h5>
                            <form id="securityForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contraseña Actual</label>
                                        <input type="password" class="form-control" name="password_actual">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nueva Contraseña</label>
                                        <input type="password" class="form-control" name="password_nueva">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Confirmar Nueva Contraseña</label>
                                        <input type="password" class="form-control" name="password_confirmar">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key me-2"></i>Cambiar Contraseña
                                </button>
                            </form>
                            
                            <hr class="my-4">
                            
                            <!-- Two Factor Authentication -->
                            <div class="row">
                                <div class="col-12">
                                    <h6><i class="fas fa-mobile-alt me-2"></i>Autenticación de Dos Factores</h6>
                                    <p class="text-muted">Agregue una capa extra de seguridad a su cuenta.</p>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="two_factor">
                                        <label class="form-check-label" for="two_factor">
                                            Habilitar autenticación de dos factores
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div id="toast" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong class="me-auto">AgroConecta</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función para mostrar toast
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const toastBody = toast.querySelector('.toast-body');
            const toastIcon = toast.querySelector('.toast-header i');
            
            toastBody.textContent = message;
            
            // Cambiar icono según tipo
            toastIcon.className = `fas ${type === 'success' ? 'fa-check-circle text-success' : 
                                         type === 'error' ? 'fa-exclamation-circle text-danger' : 
                                         'fa-info-circle text-primary'} me-2`;
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }

        // Función para manejar formularios
        function handleForm(formId, action) {
            const form = document.getElementById(formId);
            if (!form) return;
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(form);
                formData.append('action', action);
                formData.append('ajax', '1');
                
                try {
                    const response = await fetch('perfil.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showToast(result.message, 'success');
                    } else {
                        showToast(result.message, 'error');
                    }
                } catch (error) {
                    showToast('Error al procesar la solicitud', 'error');
                }
            });
        }

        // Inicializar manejadores de formularios
        document.addEventListener('DOMContentLoaded', function() {
            handleForm('personalInfoForm', 'actualizar_perfil');
            handleForm('addressForm', 'actualizar_perfil');
            handleForm('preferencesForm', 'actualizar_configuracion');
            handleForm('businessForm', 'actualizar_perfil');
            handleForm('securityForm', 'cambiar_password');
            
            // Validación de contraseñas
            const securityForm = document.getElementById('securityForm');
            if (securityForm) {
                securityForm.addEventListener('submit', (e) => {
                    const newPass = securityForm.password_nueva.value;
                    const confirmPass = securityForm.password_confirmar.value;
                    
                    if (newPass !== confirmPass) {
                        e.preventDefault();
                        showToast('Las contraseñas no coinciden', 'error');
                        return;
                    }
                    
                    if (newPass.length < 8) {
                        e.preventDefault();
                        showToast('La contraseña debe tener al menos 8 caracteres', 'error');
                        return;
                    }
                });
            }
        });
    </script>

</body>
</html>