<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="<?= $metaDescription ?? 'AgroConecta - Conectando agricultores con consumidores. Productos frescos y naturales directo del campo.' ?>">
    <meta name="keywords" content="<?= $metaKeywords ?? 'agricultura, productos frescos, verduras, frutas, orgánicos, directamente del campo' ?>">
    <meta name="author" content="AgroConecta">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= $title ?? 'AgroConecta' ?>">
    <meta property="og:description" content="<?= $metaDescription ?? 'Productos frescos directo del campo' ?>">
    <meta property="og:image" content="<?= asset('img/logo-social.png') ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'AgroConecta' ?>">
    <meta name="twitter:description" content="<?= $metaDescription ?? 'Productos frescos directo del campo' ?>">
    <meta name="twitter:image" content="<?= asset('img/logo-social.png') ?>">
    
    <title><?= $title ?? 'AgroConecta' ?> - Conectando el campo contigo</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('img/favicon.ico') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('img/apple-touch-icon.png') ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/modern-enhancements.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/sidebar-modern.css') ?>?v=<?= time() ?>">
    <?php if(isset($additionalCSS)): ?>
        <?php foreach($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= asset("css/{$css}") ?>?v=<?= time() ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Additional head content -->
    <?= $headContent ?? '' ?>
</head>
<body class="<?= $bodyClass ?? '' ?>">
    <!-- Skip to main content for accessibility -->
    <a class="skip-link" href="#main-content">Saltar al contenido principal</a>
    
    <!-- Header -->
    <header class="site-header">
        <!-- Top Bar -->
        <div class="header-top">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="header-contact">
                            <a href="tel:+5255123456788"><i class="fas fa-phone"></i> +52 55 1234-5678</a>
                            <a href="mailto:contacto@agroconecta.com"><i class="fas fa-envelope"></i> contacto@agroconecta.com</a>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="social-links">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Navigation -->
        <nav class="navbar" role="navigation" aria-label="Navegación principal">
            <div class="container">
                <div class="navbar-content">
                    <!-- Brand Logo -->
                    <a href="<?= url('/') ?>" class="navbar-brand">
                        <img src="<?= asset('img/logo.png') ?>" alt="AgroConecta" class="brand-logo">
                        <span class="brand-text">AgroConecta</span>
                    </a>
                    
                    <!-- Mobile Menu Button -->
                    <button class="mobile-menu-btn" type="button" aria-label="Abrir menú de navegación" aria-expanded="false">
                        <span class="hamburger"></span>
                        <span class="hamburger"></span>
                        <span class="hamburger"></span>
                    </button>
                    
                    <!-- Search Bar -->
                    <div class="search-container">
                        <form action="<?= url('/productos/buscar') ?>" method="GET" class="search-form">
                            <input type="text" name="q" class="search-input" placeholder="Buscar productos frescos..." value="<?= $_GET['q'] ?? '' ?>" aria-label="Buscar productos">
                            <select name="categoria" class="search-select" aria-label="Categoría">
                                <option value="">Todas las categorías</option>
                                <option value="vegetales" <?= ($_GET['categoria'] ?? '') === 'vegetales' ? 'selected' : '' ?>>🥕 Vegetales</option>
                                <option value="frutas" <?= ($_GET['categoria'] ?? '') === 'frutas' ? 'selected' : '' ?>>🍎 Frutas</option>
                                <option value="granos" <?= ($_GET['categoria'] ?? '') === 'granos' ? 'selected' : '' ?>>🌾 Granos</option>
                                <option value="hierbas" <?= ($_GET['categoria'] ?? '') === 'hierbas' ? 'selected' : '' ?>>🌿 Hierbas</option>
                            </select>
                            <button type="submit" class="search-btn" aria-label="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Main Navigation Menu -->
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a href="<?= url('/') ?>" class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>">
                                <i class="fas fa-home"></i> Inicio
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="<?= url('/productos') ?>" class="nav-link <?= $currentPage === 'productos' ? 'active' : '' ?>">
                                <i class="fas fa-shopping-basket"></i> Productos
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= url('/productos/categoria/vegetales') ?>" class="dropdown-link">🥕 Vegetales</a></li>
                                <li><a href="<?= url('/productos/categoria/frutas') ?>" class="dropdown-link">🍎 Frutas</a></li>
                                <li><a href="<?= url('/productos/categoria/granos') ?>" class="dropdown-link">🌾 Granos</a></li>
                                <li><a href="<?= url('/productos/categoria/hierbas') ?>" class="dropdown-link">🌿 Hierbas</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a href="<?= url('/productos/ofertas') ?>" class="dropdown-link">🔥 Ofertas</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/sobre-nosotros') ?>" class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>">
                                <i class="fas fa-users"></i> Nosotros
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/contacto') ?>" class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>">
                                <i class="fas fa-envelope"></i> Contacto
                            </a>
                        </li>
                    </ul>
                    
                    <!-- User Actions -->
                    <div class="user-actions">
                        <!-- Carrito -->
                        <div class="cart-dropdown">
                            <a href="<?= url('/carrito') ?>" class="cart-widget" aria-label="Ver carrito">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-count" id="cartCount"><?= cart_count() ?></span>
                                <span class="cart-total">$<?= number_format(cart_total(), 2) ?></span>
                            </a>
                            <div class="cart-preview">
                                <div id="cartPreview">
                                    <!-- Contenido del carrito se carga dinámicamente -->
                                    <div class="cart-loading">
                                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Usuario -->
                        <?php if(is_logged_in()): ?>
                            <div class="user-dropdown">
                                <button class="user-btn" aria-label="Menú de usuario" aria-expanded="false">
                                    <img src="<?= user_avatar() ?>" alt="Avatar" class="user-avatar">
                                    <span class="user-name"><?= user_name() ?></span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu user-menu">
                                    <li class="user-info">
                                        <img src="<?= user_avatar() ?>" alt="Avatar" class="avatar-lg">
                                        <div class="user-details">
                                            <strong><?= user_name() ?></strong>
                                            <span><?= user_email() ?></span>
                                        </div>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li><a href="<?= url('/perfil') ?>" class="dropdown-link"><i class="fas fa-user"></i> Mi Perfil</a></li>
                                    <li><a href="<?= url('/pedidos') ?>" class="dropdown-link"><i class="fas fa-box"></i> Mis Pedidos</a></li>
                                    <li><a href="<?= url('/favoritos') ?>" class="dropdown-link"><i class="fas fa-heart"></i> Favoritos</a></li>
                                    <li><a href="<?= url('/direcciones') ?>" class="dropdown-link"><i class="fas fa-map-marker-alt"></i> Direcciones</a></li>
                                    <li class="dropdown-divider"></li>
                                    <?php if(is_admin()): ?>
                                        <li><a href="<?= url('/admin') ?>" class="dropdown-link"><i class="fas fa-cog"></i> Administración</a></li>
                                        <li class="dropdown-divider"></li>
                                    <?php endif; ?>
                                    <?php if(is_vendor()): ?>
                                        <li><a href="<?= url('/vendor') ?>" class="dropdown-link"><i class="fas fa-store"></i> Mi Tienda</a></li>
                                        <li class="dropdown-divider"></li>
                                    <?php endif; ?>
                                    <li><a href="<?= url('/logout') ?>" class="dropdown-link text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="auth-buttons">
                                <a href="<?= url('/login') ?>" class="btn-login">
                                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                                </a>
                                <a href="<?= url('/registro') ?>" class="btn-register">
                                    <i class="fas fa-user-plus"></i> Registrarse
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Mobile Navigation Overlay -->
    <div class="mobile-nav-overlay">
        <div class="mobile-nav">
            <div class="mobile-nav-header">
                <img src="<?= asset('img/logo.png') ?>" alt="AgroConecta" class="mobile-logo">
                <button class="mobile-nav-close" aria-label="Cerrar menú">&times;</button>
            </div>
            <ul class="mobile-nav-menu">
                <li><a href="<?= url('/') ?>"><i class="fas fa-home"></i> Inicio</a></li>
                <li><a href="<?= url('/productos') ?>"><i class="fas fa-shopping-basket"></i> Productos</a></li>
                <li class="mobile-submenu">
                    <a href="#" class="submenu-toggle"><i class="fas fa-list"></i> Categorías <i class="fas fa-chevron-down"></i></a>
                    <ul class="submenu">
                        <li><a href="<?= url('/productos/categoria/vegetales') ?>">🥕 Vegetales</a></li>
                        <li><a href="<?= url('/productos/categoria/frutas') ?>">🍎 Frutas</a></li>
                        <li><a href="<?= url('/productos/categoria/granos') ?>">🌾 Granos</a></li>
                        <li><a href="<?= url('/productos/categoria/hierbas') ?>">🌿 Hierbas</a></li>
                    </ul>
                </li>
                <li><a href="<?= url('/sobre-nosotros') ?>"><i class="fas fa-users"></i> Sobre Nosotros</a></li>
                <li><a href="<?= url('/contacto') ?>"><i class="fas fa-envelope"></i> Contacto</a></li>
                <?php if(is_logged_in()): ?>
                    <li class="mobile-divider"></li>
                    <li><a href="<?= url('/perfil') ?>"><i class="fas fa-user"></i> Mi Perfil</a></li>
                    <li><a href="<?= url('/pedidos') ?>"><i class="fas fa-box"></i> Mis Pedidos</a></li>
                    <li><a href="<?= url('/carrito') ?>"><i class="fas fa-shopping-cart"></i> Carrito (<?= cart_count() ?>)</a></li>
                    <li><a href="<?= url('/logout') ?>"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                <?php else: ?>
                    <li class="mobile-divider"></li>
                    <li><a href="<?= url('/login') ?>"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a></li>
                    <li><a href="<?= url('/registro') ?>"><i class="fas fa-user-plus"></i> Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <!-- Breadcrumbs -->
    <?php if(isset($breadcrumbs) && !empty($breadcrumbs)): ?>
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>">Inicio</a></li>
                    <?php foreach($breadcrumbs as $breadcrumb): ?>
                        <?php if(isset($breadcrumb['url'])): ?>
                            <li class="breadcrumb-item"><a href="<?= $breadcrumb['url'] ?>"><?= $breadcrumb['title'] ?></a></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= $breadcrumb['title'] ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </div>
        </nav>
    <?php endif; ?>
    
    <!-- Flash Messages -->
    <?php if(has_flash_messages()): ?>
        <div class="flash-messages">
            <div class="container">
                <?php foreach(get_flash_messages() as $type => $messages): ?>
                    <?php foreach($messages as $message): ?>
                        <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $type === 'success' ? 'check-circle' : ($type === 'error' ? 'exclamation-circle' : ($type === 'warning' ? 'exclamation-triangle' : 'info-circle')) ?>"></i>
                            <?= $message ?>
                            <button type="button" class="alert-close" aria-label="Cerrar">&times;</button>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main id="main-content" class="main-content" role="main">
        <?= $content ?>
    </main>
    
    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="row">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-section">
                        <h3 class="footer-title">
                            <img src="<?= asset('img/logo-white.png') ?>" alt="AgroConecta" class="footer-logo">
                            AgroConecta
                        </h3>
                        <p class="footer-description">
                            Conectamos agricultores locales con consumidores conscientes, 
                            ofreciendo productos frescos y naturales directo del campo a tu mesa.
                        </p>
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 col-sm-6">
                    <div class="footer-section">
                        <h4 class="footer-title">Enlaces Rápidos</h4>
                        <ul class="footer-links">
                            <li><a href="<?= url('/') ?>" class="footer-link">Inicio</a></li>
                            <li><a href="<?= url('/productos') ?>" class="footer-link">Productos</a></li>
                            <li><a href="<?= url('/sobre-nosotros') ?>" class="footer-link">Sobre Nosotros</a></li>
                            <li><a href="<?= url('/contacto') ?>" class="footer-link">Contacto</a></li>
                            <li><a href="<?= url('/blog') ?>" class="footer-link">Blog</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Categories -->
                <div class="col-lg-2 col-md-6 col-sm-6">
                    <div class="footer-section">
                        <h4 class="footer-title">Categorías</h4>
                        <ul class="footer-links">
                            <li><a href="<?= url('/productos/categoria/vegetales') ?>" class="footer-link">Vegetales</a></li>
                            <li><a href="<?= url('/productos/categoria/frutas') ?>" class="footer-link">Frutas</a></li>
                            <li><a href="<?= url('/productos/categoria/granos') ?>" class="footer-link">Granos</a></li>
                            <li><a href="<?= url('/productos/categoria/hierbas') ?>" class="footer-link">Hierbas</a></li>
                            <li><a href="<?= url('/productos/ofertas') ?>" class="footer-link">Ofertas</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Support -->
                <div class="col-lg-2 col-md-6 col-sm-6">
                    <div class="footer-section">
                        <h4 class="footer-title">Soporte</h4>
                        <ul class="footer-links">
                            <li><a href="<?= url('/ayuda') ?>" class="footer-link">Centro de Ayuda</a></li>
                            <li><a href="<?= url('/politicas/envio') ?>" class="footer-link">Envíos</a></li>
                            <li><a href="<?= url('/politicas/devolucion') ?>" class="footer-link">Devoluciones</a></li>
                            <li><a href="<?= url('/politicas/privacidad') ?>" class="footer-link">Privacidad</a></li>
                            <li><a href="<?= url('/terminos') ?>" class="footer-link">Términos</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-2 col-md-6 col-sm-6">
                    <div class="footer-section">
                        <h4 class="footer-title">Contacto</h4>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Ciudad de México, México</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:+525512345678" class="footer-link">+52 55 1234-5678</a>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:contacto@agroconecta.com" class="footer-link">contacto@agroconecta.com</a>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-clock"></i>
                                <span>Lun - Vie: 9:00 - 18:00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Newsletter -->
            <div class="newsletter-section">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="newsletter-title">
                            <i class="fas fa-envelope"></i>
                            Suscríbete a nuestro newsletter
                        </h4>
                        <p class="newsletter-text">
                            Recibe ofertas exclusivas y novedades sobre productos frescos
                        </p>
                    </div>
                    <div class="col-md-6">
                        <form action="<?= url('/newsletter/suscribir') ?>" method="POST" class="newsletter-form">
                            <?= csrf_field() ?>
                            <div class="newsletter-input-group">
                                <input type="email" name="email" class="newsletter-input" placeholder="Tu correo electrónico" required>
                                <button type="submit" class="newsletter-btn">
                                    <i class="fas fa-paper-plane"></i> Suscribirse
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="copyright">
                            &copy; <?= date('Y') ?> AgroConecta. Todos los derechos reservados.
                        </p>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="payment-methods">
                            <i class="fab fa-cc-visa" title="Visa"></i>
                            <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                            <i class="fab fa-cc-paypal" title="PayPal"></i>
                            <i class="fas fa-credit-card" title="Tarjetas de crédito"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" aria-label="Volver al inicio" style="display: none;">
        <i class="fas fa-chevron-up"></i>
    </button>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-spinner-container">
            <div class="loading-spinner"></div>
            <p>Cargando...</p>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>
    
    <!-- JavaScript -->
    <script src="<?= asset('js/app.js') ?>"></script>
    <?php if(isset($additionalJS)): ?>
        <?php foreach($additionalJS as $js): ?>
            <script src="<?= asset("js/{$js}") ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Additional JS content -->
    <?= $jsContent ?? '' ?>
    
    <!-- Google Analytics -->
    <?php if(isset($_ENV['GOOGLE_ANALYTICS_ID'])): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $_ENV['GOOGLE_ANALYTICS_ID'] ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?= $_ENV['GOOGLE_ANALYTICS_ID'] ?>');
        </script>
    <?php endif; ?>
</body>
</html>