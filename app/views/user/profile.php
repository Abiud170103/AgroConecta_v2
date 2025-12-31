<?php 
// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    redirect('/auth/login');
}

// Obtener datos del usuario
$user = $this->userModel->findById($_SESSION['user_id']);

// Estados y ciudades de México
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

<div class="user-profile">
    <!-- === BREADCRUMB === -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/user/dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-user-edit"></i> Mi Perfil
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- === HEADER DEL PERFIL === -->
    <div class="profile-header">
        <div class="container">
            <div class="header-content">
                <div class="profile-avatar-section">
                    <div class="avatar-container">
                        <?php if (!empty($user['foto_perfil'])): ?>
                            <img id="currentAvatar" src="<?= asset('uploads/avatars/' . $user['foto_perfil']) ?>" alt="<?= h($user['nombre']) ?>">
                        <?php else: ?>
                            <div id="currentAvatar" class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="avatar-overlay">
                            <button type="button" class="btn-change-avatar" onclick="openAvatarModal()">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        
                        <div class="user-status">
                            <span class="status-indicator online"></span>
                            <small>En línea</small>
                        </div>
                    </div>
                    
                    <div class="profile-summary">
                        <h1><?= h($user['nombre'] . ' ' . $user['apellido']) ?></h1>
                        <p class="user-type">
                            <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                                <i class="fas fa-store"></i> Productor Verificado
                            <?php else: ?>
                                <i class="fas fa-user"></i> Cliente Premium
                            <?php endif; ?>
                        </p>
                        
                        <div class="profile-stats">
                            <div class="stat-item">
                                <strong><?= date('M Y', strtotime($user['fecha_registro'])) ?></strong>
                                <small>Miembro desde</small>
                            </div>
                            
                            <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                                <div class="stat-item">
                                    <strong><?= number_format($user['calificacion_promedio'] ?? 0, 1) ?></strong>
                                    <small>Calificación</small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="stat-item">
                                <strong><?= $user['email_verificado'] ? 'Verificado' : 'Pendiente' ?></strong>
                                <small>Estado del email</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <button type="button" class="btn btn-outline-secondary" onclick="toggleEditMode()">
                        <i class="fas fa-edit"></i>
                        <span id="editBtnText">Editar Perfil</span>
                    </button>
                    
                    <?php if (!$user['email_verificado']): ?>
                        <button type="button" class="btn btn-warning" onclick="sendVerificationEmail()">
                            <i class="fas fa-envelope"></i>
                            Verificar Email
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- === FORMULARIO DE PERFIL === -->
    <div class="profile-form">
        <div class="container">
            <form id="profileForm" method="POST" action="/user/profile/update" enctype="multipart/form-data">
                <?= csrf_token() ?>
                
                <div class="form-tabs">
                    <div class="tab-navigation">
                        <button type="button" class="tab-btn active" data-tab="personal">
                            <i class="fas fa-user"></i>
                            Información Personal
                        </button>
                        
                        <button type="button" class="tab-btn" data-tab="contact">
                            <i class="fas fa-phone"></i>
                            Contacto y Ubicación
                        </button>
                        
                        <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                            <button type="button" class="tab-btn" data-tab="business">
                                <i class="fas fa-store"></i>
                                Información del Negocio
                            </button>
                        <?php endif; ?>
                        
                        <button type="button" class="tab-btn" data-tab="security">
                            <i class="fas fa-lock"></i>
                            Seguridad
                        </button>
                        
                        <button type="button" class="tab-btn" data-tab="preferences">
                            <i class="fas fa-cog"></i>
                            Preferencias
                        </button>
                    </div>
                    
                    <!-- === TAB: INFORMACIÓN PERSONAL === -->
                    <div class="tab-content active" data-tab="personal">
                        <div class="form-section">
                            <div class="section-header">
                                <h2>
                                    <i class="fas fa-user"></i>
                                    Información Personal
                                </h2>
                                <p>Actualiza tu información básica y foto de perfil</p>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="nombre" class="form-label">
                                        Nombre(s) *
                                    </label>
                                    <input type="text" 
                                           id="nombre" 
                                           name="nombre" 
                                           class="form-control" 
                                           value="<?= h($user['nombre']) ?>" 
                                           required
                                           disabled>
                                    <small class="form-text">Tu nombre se mostrará públicamente en tus reseñas y perfil</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="apellido" class="form-label">
                                        Apellido(s) *
                                    </label>
                                    <input type="text" 
                                           id="apellido" 
                                           name="apellido" 
                                           class="form-control" 
                                           value="<?= h($user['apellido']) ?>" 
                                           required
                                           disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        Correo Electrónico *
                                        <?php if ($user['email_verificado']): ?>
                                            <span class="verified-badge">
                                                <i class="fas fa-check-circle"></i> Verificado
                                            </span>
                                        <?php else: ?>
                                            <span class="unverified-badge">
                                                <i class="fas fa-exclamation-triangle"></i> Sin verificar
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-control" 
                                           value="<?= h($user['email']) ?>" 
                                           required
                                           disabled>
                                    <small class="form-text">Cambiar tu email requerirá verificación nuevamente</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="fecha_nacimiento" class="form-label">
                                        Fecha de Nacimiento
                                    </label>
                                    <input type="date" 
                                           id="fecha_nacimiento" 
                                           name="fecha_nacimiento" 
                                           class="form-control" 
                                           value="<?= h($user['fecha_nacimiento'] ?? '') ?>"
                                           disabled>
                                    <small class="form-text">Opcional - Nos ayuda a personalizar mejor tu experiencia</small>
                                </div>
                            </div>
                            
                            <!-- Bio / Descripción -->
                            <div class="form-group">
                                <label for="bio" class="form-label">
                                    <?= $user['tipo_usuario'] === 'vendedor' ? 'Descripción de tu Negocio' : 'Acerca de ti' ?>
                                </label>
                                <textarea id="bio" 
                                          name="bio" 
                                          class="form-control" 
                                          rows="4" 
                                          placeholder="<?= $user['tipo_usuario'] === 'vendedor' 
                                              ? 'Cuéntanos sobre tu experiencia como productor, qué cultivas y qué te hace especial...' 
                                              : 'Cuéntanos un poco sobre ti, tus gustos culinarios o preferencias...' ?>"
                                          disabled><?= h($user['bio'] ?? '') ?></textarea>
                                <div class="char-counter">
                                    <span id="bioCharCount">0</span>/500 caracteres
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- === TAB: CONTACTO Y UBICACIÓN === -->
                    <div class="tab-content" data-tab="contact">
                        <div class="form-section">
                            <div class="section-header">
                                <h2>
                                    <i class="fas fa-phone"></i>
                                    Contacto y Ubicación
                                </h2>
                                <p>Información de contacto y ubicación para entregas</p>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="telefono" class="form-label">
                                        Teléfono *
                                    </label>
                                    <input type="tel" 
                                           id="telefono" 
                                           name="telefono" 
                                           class="form-control" 
                                           value="<?= h($user['telefono'] ?? '') ?>" 
                                           placeholder="55 1234 5678"
                                           required
                                           disabled>
                                    <small class="form-text">Necesario para coordinar entregas</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="telefono_alternativo" class="form-label">
                                        Teléfono Alternativo
                                    </label>
                                    <input type="tel" 
                                           id="telefono_alternativo" 
                                           name="telefono_alternativo" 
                                           class="form-control" 
                                           value="<?= h($user['telefono_alternativo'] ?? '') ?>" 
                                           placeholder="55 9876 5432"
                                           disabled>
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="estado" class="form-label">
                                        Estado *
                                    </label>
                                    <select id="estado" name="estado" class="form-control" required disabled>
                                        <option value="">Selecciona tu estado</option>
                                        <?php foreach ($estados as $key => $nombre): ?>
                                            <option value="<?= $key ?>" <?= ($user['estado'] ?? '') === $key ? 'selected' : '' ?>>
                                                <?= $nombre ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="ciudad" class="form-label">
                                        Ciudad *
                                    </label>
                                    <input type="text" 
                                           id="ciudad" 
                                           name="ciudad" 
                                           class="form-control" 
                                           value="<?= h($user['ciudad'] ?? '') ?>" 
                                           placeholder="Nombre de tu ciudad"
                                           required
                                           disabled>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="direccion" class="form-label">
                                    Dirección Completa
                                </label>
                                <textarea id="direccion" 
                                          name="direccion" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Calle, número, colonia, código postal..."
                                          disabled><?= h($user['direccion'] ?? '') ?></textarea>
                                <small class="form-text">Para vendedores, ayuda a calcular costos de envío</small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                        <!-- === TAB: INFORMACIÓN DEL NEGOCIO === -->
                        <div class="tab-content" data-tab="business">
                            <div class="form-section">
                                <div class="section-header">
                                    <h2>
                                        <i class="fas fa-store"></i>
                                        Información del Negocio
                                    </h2>
                                    <p>Detalles específicos de tu actividad como productor</p>
                                </div>
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="nombre_negocio" class="form-label">
                                            Nombre del Negocio/Finca
                                        </label>
                                        <input type="text" 
                                               id="nombre_negocio" 
                                               name="nombre_negocio" 
                                               class="form-control" 
                                               value="<?= h($user['nombre_negocio'] ?? '') ?>" 
                                               placeholder="Ej: Huerto San José, Finca Los Naranjos..."
                                               disabled>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="anos_experiencia" class="form-label">
                                            Años de Experiencia
                                        </label>
                                        <select id="anos_experiencia" name="anos_experiencia" class="form-control" disabled>
                                            <option value="">Selecciona...</option>
                                            <option value="1-2" <?= ($user['anos_experiencia'] ?? '') === '1-2' ? 'selected' : '' ?>>1-2 años</option>
                                            <option value="3-5" <?= ($user['anos_experiencia'] ?? '') === '3-5' ? 'selected' : '' ?>>3-5 años</option>
                                            <option value="6-10" <?= ($user['anos_experiencia'] ?? '') === '6-10' ? 'selected' : '' ?>>6-10 años</option>
                                            <option value="11-20" <?= ($user['anos_experiencia'] ?? '') === '11-20' ? 'selected' : '' ?>>11-20 años</option>
                                            <option value="20+" <?= ($user['anos_experiencia'] ?? '') === '20+' ? 'selected' : '' ?>>Más de 20 años</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        Productos que Cultivas
                                    </label>
                                    
                                    <div class="checkbox-grid">
                                        <?php 
                                        $productos_disponibles = [
                                            'frutas' => 'Frutas',
                                            'verduras' => 'Verduras y Hortalizas',
                                            'cereales' => 'Cereales y Granos',
                                            'legumbres' => 'Legumbres',
                                            'hierbas' => 'Hierbas Aromáticas',
                                            'tuberculos' => 'Tubérculos',
                                            'flores' => 'Flores Comestibles'
                                        ];
                                        
                                        $productos_usuario = !empty($user['productos_cultiva']) 
                                            ? json_decode($user['productos_cultiva'], true) 
                                            : [];
                                        ?>
                                        
                                        <?php foreach ($productos_disponibles as $key => $nombre): ?>
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       id="producto_<?= $key ?>" 
                                                       name="productos_cultiva[]" 
                                                       value="<?= $key ?>" 
                                                       class="form-check-input"
                                                       <?= in_array($key, $productos_usuario) ? 'checked' : '' ?>
                                                       disabled>
                                                <label for="producto_<?= $key ?>" class="form-check-label">
                                                    <?= $nombre ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="certificaciones" class="form-label">
                                        Certificaciones
                                    </label>
                                    <textarea id="certificaciones" 
                                              name="certificaciones" 
                                              class="form-control" 
                                              rows="3" 
                                              placeholder="Ej: Producción Orgánica, SENASICA, Comercio Justo..."
                                              disabled><?= h($user['certificaciones'] ?? '') ?></textarea>
                                    <small class="form-text">Certificaciones que avalen la calidad de tus productos</small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- === TAB: SEGURIDAD === -->
                    <div class="tab-content" data-tab="security">
                        <div class="form-section">
                            <div class="section-header">
                                <h2>
                                    <i class="fas fa-lock"></i>
                                    Seguridad de la Cuenta
                                </h2>
                                <p>Gestiona la seguridad y privacidad de tu cuenta</p>
                            </div>
                            
                            <!-- Cambiar Contraseña -->
                            <div class="security-section">
                                <h3>Cambiar Contraseña</h3>
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="current_password" class="form-label">
                                            Contraseña Actual *
                                        </label>
                                        <div class="password-input-group">
                                            <input type="password" 
                                                   id="current_password" 
                                                   name="current_password" 
                                                   class="form-control" 
                                                   placeholder="Ingresa tu contraseña actual"
                                                   disabled>
                                            <button type="button" class="btn-toggle-password" onclick="togglePassword('current_password')">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="new_password" class="form-label">
                                            Nueva Contraseña *
                                        </label>
                                        <div class="password-input-group">
                                            <input type="password" 
                                                   id="new_password" 
                                                   name="new_password" 
                                                   class="form-control" 
                                                   placeholder="Ingresa tu nueva contraseña"
                                                   disabled>
                                            <button type="button" class="btn-toggle-password" onclick="togglePassword('new_password')">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="password-strength">
                                            <div class="strength-meter">
                                                <div class="strength-bar" id="passwordStrengthBar"></div>
                                            </div>
                                            <small id="passwordStrengthText" class="strength-text">Ingresa tu nueva contraseña</small>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password" class="form-label">
                                            Confirmar Nueva Contraseña *
                                        </label>
                                        <div class="password-input-group">
                                            <input type="password" 
                                                   id="confirm_password" 
                                                   name="confirm_password" 
                                                   class="form-control" 
                                                   placeholder="Confirma tu nueva contraseña"
                                                   disabled>
                                            <button type="button" class="btn-toggle-password" onclick="togglePassword('confirm_password')">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="password-match" id="passwordMatch">
                                            <i class="fas fa-check-circle" style="display: none;"></i>
                                            <i class="fas fa-times-circle" style="display: none;"></i>
                                            <span class="match-text"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Autenticación de Dos Factores -->
                            <div class="security-section">
                                <h3>Autenticación de Dos Factores</h3>
                                <p>Añade una capa extra de seguridad a tu cuenta</p>
                                
                                <div class="two-factor-status">
                                    <?php if ($user['two_factor_enabled'] ?? false): ?>
                                        <div class="status-active">
                                            <i class="fas fa-shield-alt"></i>
                                            <span>Activa - Tu cuenta está protegida</span>
                                            <button type="button" class="btn btn-outline-danger btn-sm" disabled>
                                                Desactivar 2FA
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="status-inactive">
                                            <i class="fas fa-shield"></i>
                                            <span>Inactiva - Recomendamos activarla</span>
                                            <button type="button" class="btn btn-success btn-sm" disabled>
                                                Activar 2FA
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Sesiones Activas -->
                            <div class="security-section">
                                <h3>Sesiones Activas</h3>
                                <p>Gestiona dónde has iniciado sesión</p>
                                
                                <div class="sessions-list">
                                    <div class="session-item current">
                                        <div class="session-icon">
                                            <i class="fas fa-desktop"></i>
                                        </div>
                                        <div class="session-info">
                                            <strong>Sesión Actual</strong>
                                            <small>Windows • Chrome • México</small>
                                            <small class="text-muted">Última actividad: Ahora</small>
                                        </div>
                                        <span class="session-status current-session">Actual</span>
                                    </div>
                                    
                                    <!-- Ejemplo de otras sesiones -->
                                    <!--
                                    <div class="session-item">
                                        <div class="session-icon">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <div class="session-info">
                                            <strong>Mobile App</strong>
                                            <small>Android • Chrome Mobile • México</small>
                                            <small class="text-muted">Hace 2 horas</small>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm">
                                            Cerrar Sesión
                                        </button>
                                    </div>
                                    -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- === TAB: PREFERENCIAS === -->
                    <div class="tab-content" data-tab="preferences">
                        <div class="form-section">
                            <div class="section-header">
                                <h2>
                                    <i class="fas fa-cog"></i>
                                    Preferencias y Notificaciones
                                </h2>
                                <p>Personaliza tu experiencia en AgroConecta</p>
                            </div>
                            
                            <!-- Notificaciones -->
                            <div class="preference-section">
                                <h3>Notificaciones por Email</h3>
                                
                                <div class="notification-preferences">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" 
                                               id="notif_nuevos_productos" 
                                               name="notificaciones[nuevos_productos]" 
                                               class="form-check-input" 
                                               <?= ($user['notificaciones']['nuevos_productos'] ?? true) ? 'checked' : '' ?>
                                               disabled>
                                        <label for="notif_nuevos_productos" class="form-check-label">
                                            <strong>Nuevos Productos</strong>
                                            <small>Notificarme cuando haya productos nuevos de mis categorías favoritas</small>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input type="checkbox" 
                                               id="notif_ofertas_descuentos" 
                                               name="notificaciones[ofertas_descuentos]" 
                                               class="form-check-input" 
                                               <?= ($user['notificaciones']['ofertas_descuentos'] ?? true) ? 'checked' : '' ?>
                                               disabled>
                                        <label for="notif_ofertas_descuentos" class="form-check-label">
                                            <strong>Ofertas y Descuentos</strong>
                                            <small>Recibir notificaciones sobre promociones especiales</small>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input type="checkbox" 
                                               id="notif_estado_pedidos" 
                                               name="notificaciones[estado_pedidos]" 
                                               class="form-check-input" 
                                               <?= ($user['notificaciones']['estado_pedidos'] ?? true) ? 'checked' : '' ?>
                                               disabled>
                                        <label for="notif_estado_pedidos" class="form-check-label">
                                            <strong>Estado de Pedidos</strong>
                                            <small>Actualizaciones sobre el estado de mis pedidos</small>
                                        </label>
                                    </div>
                                    
                                    <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" 
                                                   id="notif_nuevos_pedidos" 
                                                   name="notificaciones[nuevos_pedidos]" 
                                                   class="form-check-input" 
                                                   <?= ($user['notificaciones']['nuevos_pedidos'] ?? true) ? 'checked' : '' ?>
                                                   disabled>
                                            <label for="notif_nuevos_pedidos" class="form-check-label">
                                                <strong>Nuevos Pedidos</strong>
                                                <small>Notificarme inmediatamente cuando reciba un nuevo pedido</small>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check form-switch">
                                            <input type="checkbox" 
                                                   id="notif_resenas" 
                                                   name="notificaciones[resenas]" 
                                                   class="form-check-input" 
                                                   <?= ($user['notificaciones']['resenas'] ?? true) ? 'checked' : '' ?>
                                                   disabled>
                                            <label for="notif_resenas" class="form-check-label">
                                                <strong>Nuevas Reseñas</strong>
                                                <small>Cuando los clientes dejen reseñas en mis productos</small>
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Preferencias de Visualización -->
                            <div class="preference-section">
                                <h3>Visualización</h3>
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="idioma" class="form-label">
                                            Idioma
                                        </label>
                                        <select id="idioma" name="idioma" class="form-control" disabled>
                                            <option value="es" <?= ($user['idioma'] ?? 'es') === 'es' ? 'selected' : '' ?>>Español</option>
                                            <option value="en" <?= ($user['idioma'] ?? 'es') === 'en' ? 'selected' : '' ?>>English</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="zona_horaria" class="form-label">
                                            Zona Horaria
                                        </label>
                                        <select id="zona_horaria" name="zona_horaria" class="form-control" disabled>
                                            <option value="America/Mexico_City" <?= ($user['zona_horaria'] ?? 'America/Mexico_City') === 'America/Mexico_City' ? 'selected' : '' ?>>Ciudad de México (GMT-6)</option>
                                            <option value="America/Cancun" <?= ($user['zona_horaria'] ?? 'America/Mexico_City') === 'America/Cancun' ? 'selected' : '' ?>>Cancún (GMT-5)</option>
                                            <option value="America/Hermosillo" <?= ($user['zona_horaria'] ?? 'America/Mexico_City') === 'America/Hermosillo' ? 'selected' : '' ?>>Hermosillo (GMT-7)</option>
                                            <option value="America/Tijuana" <?= ($user['zona_horaria'] ?? 'America/Mexico_City') === 'America/Tijuana' ? 'selected' : '' ?>>Tijuana (GMT-8)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Privacidad -->
                            <div class="preference-section">
                                <h3>Privacidad</h3>
                                
                                <div class="privacy-preferences">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" 
                                               id="perfil_publico" 
                                               name="configuracion[perfil_publico]" 
                                               class="form-check-input" 
                                               <?= ($user['configuracion']['perfil_publico'] ?? true) ? 'checked' : '' ?>
                                               disabled>
                                        <label for="perfil_publico" class="form-check-label">
                                            <strong>Perfil Público</strong>
                                            <small>Permitir que otros usuarios vean mi perfil básico</small>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input type="checkbox" 
                                               id="mostrar_ubicacion" 
                                               name="configuracion[mostrar_ubicacion]" 
                                               class="form-check-input" 
                                               <?= ($user['configuracion']['mostrar_ubicacion'] ?? true) ? 'checked' : '' ?>
                                               disabled>
                                        <label for="mostrar_ubicacion" class="form-check-label">
                                            <strong>Mostrar Ubicación</strong>
                                            <small>Mostrar mi ciudad en el perfil público (ayuda a los compradores)</small>
                                        </label>
                                    </div>
                                    
                                    <?php if ($user['tipo_usuario'] === 'cliente'): ?>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" 
                                                   id="mostrar_resenas" 
                                                   name="configuracion[mostrar_resenas]" 
                                                   class="form-check-input" 
                                                   <?= ($user['configuracion']['mostrar_resenas'] ?? true) ? 'checked' : '' ?>
                                                   disabled>
                                            <label for="mostrar_resenas" class="form-check-label">
                                                <strong>Mostrar Mis Reseñas</strong>
                                                <small>Permitir que otros vean las reseñas que he escrito</small>
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- === BOTONES DE ACCIÓN === -->
                <div class="form-actions">
                    <div class="actions-left">
                        <button type="button" class="btn btn-outline-danger" onclick="showDeleteAccountModal()" disabled>
                            <i class="fas fa-trash"></i>
                            Eliminar Cuenta
                        </button>
                    </div>
                    
                    <div class="actions-right">
                        <button type="button" class="btn btn-secondary" onclick="cancelEdit()" id="cancelBtn" style="display: none;">
                            Cancelar
                        </button>
                        
                        <button type="submit" class="btn btn-success" id="saveBtn" style="display: none;">
                            <span class="btn-text">
                                <i class="fas fa-save"></i>
                                Guardar Cambios
                            </span>
                            <span class="btn-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i>
                                Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- === MODALES === -->

<!-- Modal para cambiar avatar -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="avatarModalLabel">
                    <i class="fas fa-camera"></i>
                    Cambiar Foto de Perfil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="avatar-upload-section">
                    <div class="upload-methods">
                        <div class="upload-method">
                            <input type="file" id="avatarFileInput" accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('avatarFileInput').click()">
                                <i class="fas fa-upload"></i>
                                Subir desde mi dispositivo
                            </button>
                        </div>
                        
                        <div class="upload-method">
                            <button type="button" class="btn btn-outline-secondary" onclick="openWebcamCapture()">
                                <i class="fas fa-camera"></i>
                                Tomar foto con cámara
                            </button>
                        </div>
                    </div>
                    
                    <div class="avatar-preview" id="avatarPreview" style="display: none;">
                        <img id="previewImage" src="" alt="Vista previa">
                        <div class="preview-actions">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cropAvatar()">
                                <i class="fas fa-crop"></i>
                                Recortar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="uploadAvatar()" id="uploadAvatarBtn" disabled>
                    <i class="fas fa-check"></i>
                    Guardar Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar cuenta -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAccountModalLabel">
                    <i class="fas fa-exclamation-triangle"></i>
                    Eliminar Cuenta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>¡Atención!</strong> Esta acción no se puede deshacer.
                </div>
                
                <p>Al eliminar tu cuenta:</p>
                <ul class="delete-consequences">
                    <li>Se eliminarán permanentemente todos tus datos personales</li>
                    <?php if ($user['tipo_usuario'] === 'vendedor'): ?>
                        <li>Se darán de baja todos tus productos publicados</li>
                        <li>Se cancelarán los pedidos pendientes</li>
                    <?php else: ?>
                        <li>Se cancelarán tus pedidos pendientes</li>
                        <li>Perderás tu historial de compras</li>
                    <?php endif; ?>
                    <li>No podrás recuperar esta información</li>
                </ul>
                
                <div class="form-group mt-3">
                    <label for="deleteConfirmPassword" class="form-label">
                        Confirma tu contraseña para continuar:
                    </label>
                    <input type="password" id="deleteConfirmPassword" class="form-control" placeholder="Tu contraseña actual">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteAccount()" id="confirmDeleteBtn" disabled>
                    <i class="fas fa-trash"></i>
                    Eliminar Cuenta Definitivamente
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/profile.js') ?>"></script>