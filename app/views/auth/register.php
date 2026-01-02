<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - AgroConecta</title>
    <meta name="description" content="Regístrate en AgroConecta como comprador o vendedor de productos agrícolas">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/css/app.css">
    <link rel="stylesheet" href="public/css/auth.css">
</head>
<body class="auth-page">

    <section class="auth-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="auth-container">
                        <!-- Header -->
                        <div class="auth-header">
                            <div class="auth-logo">
                                <img src="public/img/logo.png" alt="AgroConecta" class="logo-image">
                                <h1 class="logo-text">AgroConecta</h1>
                            </div>
                            <h2 class="auth-title">Crear Cuenta</h2>
                            <p class="auth-subtitle">
                                Únete a nuestra comunidad de agricultores y compradores
                            </p>
                        </div>

                        <!-- Alertas -->
                        <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        </div>
                        <?php endif; ?>

                        <!-- Register Form -->
                        <form class="auth-form" id="registerForm" action="registro" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            
                            <!-- Tipo de Usuario Toggle -->
                            <div class="user-type-toggle">
                                <div class="user-type-option">
                                    <input type="radio" id="tipo_cliente" name="tipo_usuario" value="cliente" checked>
                                    <label for="tipo_cliente" class="user-type-label">
                                        <i class="fas fa-shopping-basket user-type-icon"></i>
                                        <strong>Cliente</strong>
                                        <small>Quiero comprar productos</small>
                                    </label>
                                </div>
                                <div class="user-type-option">
                                    <input type="radio" id="tipo_vendedor" name="tipo_usuario" value="vendedor">
                                    <label for="tipo_vendedor" class="user-type-label">
                                        <i class="fas fa-store user-type-icon"></i>
                                        <strong>Vendedor</strong>
                                        <small>Quiero vender mis productos</small>
                                    </label>
                                </div>
                            </div>

                            <!-- Datos Personales -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre">
                                            Nombre <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-user"></i>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="nombre" 
                                                   name="nombre" 
                                                   placeholder="Tu nombre"
                                                   required 
                                                   autofocus>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="apellido">
                                            Apellido <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-user"></i>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="apellido" 
                                                   name="apellido" 
                                                   placeholder="Tu apellido"
                                                   required>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           placeholder="tu@correo.com"
                                           required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Teléfono -->
                            <div class="form-group">
                                <label for="telefono">
                                    Teléfono <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="telefono" 
                                           name="telefono" 
                                           placeholder="55 1234 5678"
                                           required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Contraseña -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">
                                            Contraseña <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-lock"></i>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password" 
                                                   name="password" 
                                                   placeholder="Mínimo 8 caracteres"
                                                   required>
                                            <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirm">
                                            Confirmar Contraseña <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-lock"></i>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password_confirm" 
                                                   name="password_confirm" 
                                                   placeholder="Repite tu contraseña"
                                                   required>
                                            <i class="fas fa-eye toggle-password" onclick="togglePassword('password_confirm')"></i>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Campos adicionales para Vendedor -->
                            <div id="vendorFields" class="vendor-fields">
                                <div class="auth-divider">
                                    <span>Información del Negocio</span>
                                </div>

                                <div class="form-group">
                                    <label for="nombre_negocio">
                                        Nombre del Negocio <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-store"></i>
                                        <input type="text" 
                                               class="form-control" 
                                               id="nombre_negocio" 
                                               name="nombre_negocio" 
                                               placeholder="Nombre de tu negocio">
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="form-group">
                                    <label for="descripcion_negocio">
                                        Descripción del Negocio
                                    </label>
                                    <textarea class="form-control" 
                                              id="descripcion_negocio" 
                                              name="descripcion_negocio" 
                                              rows="3" 
                                              placeholder="Cuéntanos sobre tus productos y tu negocio"></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ciudad">
                                                Ciudad <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-icon-wrapper">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="ciudad" 
                                                       name="ciudad" 
                                                       placeholder="Tu ciudad">
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="estado">
                                                Estado <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-icon-wrapper">
                                                <i class="fas fa-map"></i>
                                                <select class="form-control" id="estado" name="estado">
                                                    <option value="">Selecciona tu estado</option>
                                                    <option value="Aguascalientes">Aguascalientes</option>
                                                    <option value="Baja California">Baja California</option>
                                                    <option value="Baja California Sur">Baja California Sur</option>
                                                    <option value="Campeche">Campeche</option>
                                                    <option value="Chiapas">Chiapas</option>
                                                    <option value="Chihuahua">Chihuahua</option>
                                                    <option value="Ciudad de México">Ciudad de México</option>
                                                    <option value="Coahuila">Coahuila</option>
                                                    <option value="Colima">Colima</option>
                                                    <option value="Durango">Durango</option>
                                                    <option value="Estado de México">Estado de México</option>
                                                    <option value="Guanajuato">Guanajuato</option>
                                                    <option value="Guerrero">Guerrero</option>
                                                    <option value="Hidalgo">Hidalgo</option>
                                                    <option value="Jalisco">Jalisco</option>
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
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Términos y Condiciones -->
                            <div class="terms-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="terminos" name="terminos" required>
                                    <label class="form-check-label" for="terminos">
                                        Acepto los <a href="terminos" target="_blank">Términos y Condiciones</a> 
                                        y la <a href="privacidad" target="_blank">Política de Privacidad</a>
                                    </label>
                                </div>
                            </div>

                            <!-- Botón de Registro -->
                            <button type="submit" class="btn btn-primary" id="registerBtn">
                                Crear Cuenta
                            </button>
                        </form>

                        <!-- Footer -->
                        <div class="auth-footer">
                            <p>
                                ¿Ya tienes cuenta? 
                                <a href="login">Inicia sesión aquí</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="public/js/auth.js"></script>
    <script>
        // Toggle campos de vendedor
        document.querySelectorAll('input[name="tipo_usuario"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const vendorFields = document.getElementById('vendorFields');
                if (this.value === 'vendedor') {
                    vendorFields.classList.add('active');
                    // Hacer campos de vendedor requeridos
                    document.getElementById('nombre_negocio').required = true;
                    document.getElementById('ciudad').required = true;
                    document.getElementById('estado').required = true;
                } else {
                    vendorFields.classList.remove('active');
                    // Quitar requeridos de campos de vendedor
                    document.getElementById('nombre_negocio').required = false;
                    document.getElementById('ciudad').required = false;
                    document.getElementById('estado').required = false;
                }
            });
        });
    </script>
</body>
</html>
