<?php 
$title = "Crear Cuenta";
$currentPage = "registro";
$metaDescription = "Únete a AgroConecta y accede a productos frescos y naturales directo del campo. Registro gratuito y fácil";
$metaKeywords = "registro, crear cuenta, agroconecta, productos frescos, agricultor, vendedor";
$additionalCSS = ['auth.css'];
$additionalJS = ['auth.js', 'register.js'];
$bodyClass = "auth-page";

ob_start();
?>

<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="auth-container">
                    <!-- Header -->
                    <div class="auth-header">
                        <div class="auth-logo">
                            <img src="<?= asset('img/logo.png') ?>" alt="AgroConecta" class="logo-image">
                            <h1 class="logo-text">AgroConecta</h1>
                        </div>
                        <h2 class="auth-title">¡Únete a nuestra comunidad!</h2>
                        <p class="auth-subtitle">
                            Crea tu cuenta gratuita y comienza a disfrutar de productos frescos del campo
                        </p>
                    </div>

                    <!-- Account Type Selection -->
                    <div class="account-type-selection">
                        <div class="type-tabs">
                            <button type="button" class="type-tab active" data-type="cliente">
                                <i class="fas fa-user"></i>
                                <span>Soy Cliente</span>
                                <small>Quiero comprar productos</small>
                            </button>
                            <button type="button" class="type-tab" data-type="vendedor">
                                <i class="fas fa-store"></i>
                                <span>Soy Productor</span>
                                <small>Quiero vender mis productos</small>
                            </button>
                        </div>
                    </div>

                    <!-- Social Registration -->
                    <div class="social-login">
                        <button type="button" class="btn btn-social btn-google" onclick="registerWithGoogle()">
                            <i class="fab fa-google"></i>
                            Registrarse con Google
                        </button>
                        <button type="button" class="btn btn-social btn-facebook" onclick="registerWithFacebook()">
                            <i class="fab fa-facebook-f"></i>
                            Registrarse con Facebook
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="auth-divider">
                        <span>o regístrate con tu correo</span>
                    </div>

                    <!-- Registration Form -->
                    <form class="auth-form" id="registerForm" action="<?= url('/registro') ?>" method="POST" novalidate>
                        <?= csrf_field() ?>
                        
                        <input type="hidden" name="tipo_usuario" id="tipoUsuario" value="cliente">
                        
                        <!-- Personal Information -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-user"></i>
                                Información Personal
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre" class="form-label">
                                            <i class="fas fa-user-circle"></i>
                                            Nombre *
                                        </label>
                                        <input type="text" 
                                               id="nombre" 
                                               name="nombre" 
                                               class="form-control <?= has_error('nombre') ? 'is-invalid' : '' ?>" 
                                               placeholder="Tu nombre"
                                               value="<?= old('nombre') ?>"
                                               required>
                                        <?php if (has_error('nombre')): ?>
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?= error('nombre') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="apellidos" class="form-label">
                                            <i class="fas fa-user-circle"></i>
                                            Apellidos *
                                        </label>
                                        <input type="text" 
                                               id="apellidos" 
                                               name="apellidos" 
                                               class="form-control <?= has_error('apellidos') ? 'is-invalid' : '' ?>" 
                                               placeholder="Tus apellidos"
                                               value="<?= old('apellidos') ?>"
                                               required>
                                        <?php if (has_error('apellidos')): ?>
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?= error('apellidos') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope"></i>
                                            Correo Electrónico *
                                        </label>
                                        <input type="email" 
                                               id="email" 
                                               name="email" 
                                               class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" 
                                               placeholder="tu@correo.com"
                                               value="<?= old('email') ?>"
                                               required>
                                        <?php if (has_error('email')): ?>
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?= error('email') ?>
                                            </div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">
                                            Te enviaremos confirmaciones a este correo
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="telefono" class="form-label">
                                            <i class="fas fa-phone"></i>
                                            Teléfono *
                                        </label>
                                        <input type="tel" 
                                               id="telefono" 
                                               name="telefono" 
                                               class="form-control <?= has_error('telefono') ? 'is-invalid' : '' ?>" 
                                               placeholder="55 1234 5678"
                                               value="<?= old('telefono') ?>"
                                               required>
                                        <?php if (has_error('telefono')): ?>
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?= error('telefono') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-map-marker-alt"></i>
                                Ubicación
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="estado" class="form-label">
                                            <i class="fas fa-map"></i>
                                            Estado *
                                        </label>
                                        <select id="estado" 
                                                name="estado" 
                                                class="form-select <?= has_error('estado') ? 'is-invalid' : '' ?>" 
                                                required>
                                            <option value="">Selecciona tu estado</option>
                                            <option value="cdmx" <?= old('estado') === 'cdmx' ? 'selected' : '' ?>>Ciudad de México</option>
                                            <option value="mexico" <?= old('estado') === 'mexico' ? 'selected' : '' ?>>Estado de México</option>
                                            <option value="jalisco" <?= old('estado') === 'jalisco' ? 'selected' : '' ?>>Jalisco</option>
                                            <option value="nuevo_leon" <?= old('estado') === 'nuevo_leon' ? 'selected' : '' ?>>Nuevo León</option>
                                            <option value="puebla" <?= old('estado') === 'puebla' ? 'selected' : '' ?>>Puebla</option>
                                            <option value="guanajuato" <?= old('estado') === 'guanajuato' ? 'selected' : '' ?>>Guanajuato</option>
                                            <option value="veracruz" <?= old('estado') === 'veracruz' ? 'selected' : '' ?>>Veracruz</option>
                                            <option value="chihuahua" <?= old('estado') === 'chihuahua' ? 'selected' : '' ?>>Chihuahua</option>
                                            <option value="michoacan" <?= old('estado') === 'michoacan' ? 'selected' : '' ?>>Michoacán</option>
                                            <option value="oaxaca" <?= old('estado') === 'oaxaca' ? 'selected' : '' ?>>Oaxaca</option>
                                        </select>
                                        <?php if (has_error('estado')): ?>
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?= error('estado') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ciudad" class="form-label">
                                            <i class="fas fa-city"></i>
                                            Ciudad *
                                        </label>
                                        <input type="text" 
                                               id="ciudad" 
                                               name="ciudad" 
                                               class="form-control <?= has_error('ciudad') ? 'is-invalid' : '' ?>" 
                                               placeholder="Tu ciudad"
                                               value="<?= old('ciudad') ?>"
                                               required>
                                        <?php if (has_error('ciudad')): ?>
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?= error('ciudad') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vendor Information (Only for vendors) -->
                        <div class="form-section vendor-section" id="vendorSection" style="display: none;">
                            <h4 class="section-title">
                                <i class="fas fa-store"></i>
                                Información del Productor
                            </h4>
                            
                            <div class="form-group">
                                <label for="nombre_negocio" class="form-label">
                                    <i class="fas fa-store-alt"></i>
                                    Nombre del Negocio/Finca
                                </label>
                                <input type="text" 
                                       id="nombre_negocio" 
                                       name="nombre_negocio" 
                                       class="form-control" 
                                       placeholder="Ej: Finca Los Girasoles"
                                       value="<?= old('nombre_negocio') ?>">
                                <small class="form-text text-muted">
                                    Opcional: Nombre de tu finca, rancho o negocio
                                </small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipo_productor" class="form-label">
                                            <i class="fas fa-seedling"></i>
                                            Tipo de Productor
                                        </label>
                                        <select id="tipo_productor" name="tipo_productor" class="form-select">
                                            <option value="">Selecciona tipo</option>
                                            <option value="individual" <?= old('tipo_productor') === 'individual' ? 'selected' : '' ?>>Productor Individual</option>
                                            <option value="cooperativa" <?= old('tipo_productor') === 'cooperativa' ? 'selected' : '' ?>>Cooperativa</option>
                                            <option value="empresa" <?= old('tipo_productor') === 'empresa' ? 'selected' : '' ?>>Empresa Agrícola</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="anos_experiencia" class="form-label">
                                            <i class="fas fa-calendar-alt"></i>
                                            Años de Experiencia
                                        </label>
                                        <select id="anos_experiencia" name="anos_experiencia" class="form-select">
                                            <option value="">Selecciona</option>
                                            <option value="1-2" <?= old('anos_experiencia') === '1-2' ? 'selected' : '' ?>>1-2 años</option>
                                            <option value="3-5" <?= old('anos_experiencia') === '3-5' ? 'selected' : '' ?>>3-5 años</option>
                                            <option value="6-10" <?= old('anos_experiencia') === '6-10' ? 'selected' : '' ?>>6-10 años</option>
                                            <option value="10+" <?= old('anos_experiencia') === '10+' ? 'selected' : '' ?>>Más de 10 años</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="productos_cultiva" class="form-label">
                                    <i class="fas fa-leaf"></i>
                                    ¿Qué productos cultivas?
                                </label>
                                <div class="checkbox-group">
                                    <label class="form-check">
                                        <input type="checkbox" name="productos_cultiva[]" value="vegetales" class="form-check-input">
                                        <span class="form-check-label">🥕 Vegetales</span>
                                    </label>
                                    <label class="form-check">
                                        <input type="checkbox" name="productos_cultiva[]" value="frutas" class="form-check-input">
                                        <span class="form-check-label">🍎 Frutas</span>
                                    </label>
                                    <label class="form-check">
                                        <input type="checkbox" name="productos_cultiva[]" value="granos" class="form-check-input">
                                        <span class="form-check-label">🌾 Granos y Cereales</span>
                                    </label>
                                    <label class="form-check">
                                        <input type="checkbox" name="productos_cultiva[]" value="hierbas" class="form-check-input">
                                        <span class="form-check-label">🌿 Hierbas Aromáticas</span>
                                    </label>
                                    <label class="form-check">
                                        <input type="checkbox" name="productos_cultiva[]" value="especias" class="form-check-input">
                                        <span class="form-check-label">🌶️ Especias</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Security -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-lock"></i>
                                Seguridad
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-key"></i>
                                            Contraseña *
                                        </label>
                                        <div class="password-input">
                                            <input type="password" 
                                                   id="password" 
                                                   name="password" 
                                                   class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" 
                                                   placeholder="Crear contraseña"
                                                   required>
                                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength" id="passwordStrength">
                                            <div class="strength-meter">
                                                <div class="strength-bar" id="strengthBar"></div>
                                            </div>
                                            <span class="strength-text" id="strengthText">Ingresa tu contraseña</span>
                                        </div>
                                        <?php if (has_error('password')): ?>
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?= error('password') ?>
                                            </div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">
                                            Mínimo 8 caracteres, incluye mayúsculas, minúsculas y números
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation" class="form-label">
                                            <i class="fas fa-lock"></i>
                                            Confirmar Contraseña *
                                        </label>
                                        <div class="password-input">
                                            <input type="password" 
                                                   id="password_confirmation" 
                                                   name="password_confirmation" 
                                                   class="form-control" 
                                                   placeholder="Confirmar contraseña"
                                                   required>
                                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-match" id="passwordMatch">
                                            <i class="fas fa-check-circle text-success" style="display: none;"></i>
                                            <i class="fas fa-times-circle text-danger" style="display: none;"></i>
                                            <span class="match-text"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-section">
                            <div class="terms-section">
                                <label class="form-check terms-check">
                                    <input type="checkbox" 
                                           name="acepto_terminos" 
                                           class="form-check-input" 
                                           required>
                                    <span class="form-check-label">
                                        Acepto los 
                                        <a href="<?= url('/terminos') ?>" target="_blank">Términos y Condiciones</a> 
                                        y la 
                                        <a href="<?= url('/politica-privacidad') ?>" target="_blank">Política de Privacidad</a> 
                                        de AgroConecta *
                                    </span>
                                </label>

                                <label class="form-check">
                                    <input type="checkbox" 
                                           name="acepto_marketing" 
                                           class="form-check-input">
                                    <span class="form-check-label">
                                        Acepto recibir ofertas, promociones y noticias por correo electrónico
                                    </span>
                                </label>

                                <div class="vendor-terms" id="vendorTerms" style="display: none;">
                                    <label class="form-check">
                                        <input type="checkbox" 
                                               name="acepto_terminos_vendedor" 
                                               class="form-check-input">
                                        <span class="form-check-label">
                                            Acepto los 
                                            <a href="<?= url('/terminos-vendedor') ?>" target="_blank">Términos Adicionales para Vendedores</a> 
                                            y entiendo las comisiones aplicables
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Register Button -->
                        <button type="submit" class="btn btn-primary btn-auth" id="registerBtn">
                            <span class="btn-text">
                                <i class="fas fa-user-plus"></i>
                                Crear Mi Cuenta
                            </span>
                            <span class="btn-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i>
                                Creando cuenta...
                            </span>
                        </button>
                    </form>

                    <!-- Additional Options -->
                    <div class="auth-additional">
                        <div class="auth-links">
                            <p>¿Ya tienes una cuenta?</p>
                            <a href="<?= url('/login') ?>" class="login-link">
                                <i class="fas fa-sign-in-alt"></i>
                                Inicia sesión aquí
                            </a>
                        </div>
                    </div>

                    <!-- Security Info -->
                    <div class="security-info">
                        <div class="security-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Registro 100% seguro y gratuito</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-user-shield"></i>
                            <span>Tus datos personales están protegidos</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-envelope-open-text"></i>
                            <span>Verificación por correo electrónico</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Loading Overlay -->
<div class="auth-loading" id="authLoading" style="display: none;">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <p>Creando tu cuenta...</p>
    </div>
</div>

<?php 
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>