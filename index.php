<?php
// Verificar si estamos accediendo a la raíz del proyecto
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';

// Si hay parámetros GET para rutas específicas, redirigir
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    $tipo = $_GET['tipo'] ?? '';
    
    switch($page) {
        case 'login':
            header('Location: public/login.php');
            exit;
        case 'register':
            $location = 'public/register.php';
            if ($tipo) {
                $location .= '?tipo=' . urlencode($tipo);
            }
            header('Location: ' . $location);
            exit;
        case 'forgot-password':
            header('Location: public/forgot-password.php');
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroConecta - Conectando el campo con tu mesa</title>
    <meta name="description" content="Plataforma digital que conecta agricultores locales con compradores directos">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/css/app.css">
    
    <style>
        .hero-section {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
            padding: 100px 0;
            min-height: 80vh;
            display: flex;
            align-items: center;
        }
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .hero-content p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
        }
        .btn-hero {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            margin: 0.5rem;
        }
        .btn-primary-hero {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            backdrop-filter: blur(10px);
        }
        .btn-primary-hero:hover {
            background: white;
            color: #28a745;
        }
        .btn-secondary-hero {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
        }
        .btn-secondary-hero:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }
        .features-section {
            padding: 80px 0;
        }
        .feature-card {
            text-align: center;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            height: 100%;
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 3rem;
            color: #28a745;
            margin-bottom: 1.5rem;
        }
        .stats-section {
            background: #f8f9fa;
            padding: 60px 0;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #28a745;
        }
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand img {
            height: 40px;
        }
        body {
            padding-top: 76px;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#home">
                <img src="public/img/logo.png" alt="AgroConecta" class="me-2">
                <span class="fw-bold text-success">AgroConecta</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#productos">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#nosotros">Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=login">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Iniciar Sesión
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-success text-white px-3 rounded-pill" href="?page=register">
                            <i class="fas fa-user-plus me-1"></i>
                            Registrarse
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1>Cultivando el futuro, conectando cosechas</h1>
                        <p class="lead">
                            Conectamos directamente a agricultores locales con compradores, 
                            eliminando intermediarios y garantizando productos frescos y precios justos.
                        </p>
                        <div class="hero-buttons">
                            <a href="?page=register" class="btn btn-primary-hero btn-hero">
                                <i class="fas fa-rocket me-2"></i>
                                Comenzar Ahora
                            </a>
                            <a href="#productos" class="btn btn-secondary-hero btn-hero">
                                <i class="fas fa-search me-2"></i>
                                Ver Productos
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="public/img/hero-farm.png" alt="Agricultura" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="nosotros" class="features-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-4 fw-bold text-success">¿Por qué AgroConecta?</h2>
                    <p class="lead">Facilitamos la conexión directa entre el campo y tu mesa</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4>Comercio Directo</h4>
                        <p>Conectamos agricultores directamente con compradores, eliminando intermediarios y aumentando las ganancias.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h4>Productos Frescos</h4>
                        <p>Garantizamos productos frescos y de temporada, directo del campo a tu mesa en el menor tiempo posible.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Fácil de Usar</h4>
                        <p>Plataforma intuitiva y responsive, optimizada para dispositivos móviles y fácil navegación.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Pagos Seguros</h4>
                        <p>Sistema de pagos integrado con Mercado Pago para transacciones seguras y confiables.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Comunidad</h4>
                        <p>Fortalecemos la economía local y creamos una red de apoyo entre agricultores y consumidores.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Crecimiento</h4>
                        <p>Herramientas para que los agricultores hagan crecer su negocio y lleguen a más clientes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <p class="mb-0">Agricultores Registrados</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number">2,000+</div>
                        <p class="mb-0">Productos Disponibles</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number">15,000+</div>
                        <p class="mb-0">Pedidos Completados</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <div class="stat-number">98%</div>
                        <p class="mb-0">Satisfacción del Cliente</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="productos" class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h2 class="display-4 fw-bold mb-4">¿Listo para comenzar?</h2>
                    <p class="lead mb-4">
                        Únete a nuestra comunidad y forma parte del cambio hacia una agricultura más conectada
                    </p>
                    <div class="hero-buttons">
                        <a href="?page=register&tipo=vendedor" class="btn btn-primary-hero btn-hero">
                            <i class="fas fa-store me-2"></i>
                            Soy Agricultor
                        </a>
                        <a href="?page=register&tipo=cliente" class="btn btn-secondary-hero btn-hero">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Quiero Comprar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <h5 class="text-success">AgroConecta</h5>
                    <p>Cultivando el futuro, conectando cosechas.</p>
                    <p>Plataforma desarrollada por estudiantes de ESCOM IPN.</p>
                </div>
                <div class="col-lg-4">
                    <h6>Enlaces Rápidos</h6>
                    <ul class="list-unstyled">
                        <li><a href="?page=login" class="text-white-50">Iniciar Sesión</a></li>
                        <li><a href="?page=register" class="text-white-50">Registrarse</a></li>
                        <li><a href="#nosotros" class="text-white-50">Nosotros</a></li>
                        <li><a href="#productos" class="text-white-50">Productos</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6>Contacto</h6>
                    <p class="text-white-50">
                        <i class="fas fa-envelope me-2"></i>
                        contacto@agroconecta.com
                    </p>
                    <p class="text-white-50">
                        <i class="fas fa-phone me-2"></i>
                        +52 55 1234 5678
                    </p>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; 2026 AgroConecta. Desarrollado por Equipo 6CV1 - ESCOM IPN.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Smooth Scrolling -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>