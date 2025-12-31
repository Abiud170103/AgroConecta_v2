<?php 
$title = "Inicio";
$currentPage = "home";
$metaDescription = "AgroConecta - Conecta con agricultores locales y disfruta de productos frescos y naturales directo del campo a tu mesa";
$metaKeywords = "agricultura, productos frescos, verduras, frutas, orgánicos, local, campo, natural";
$additionalCSS = ['home.css', 'modern-enhancements.css', 'sidebar-modern.css', 'home-layout.css'];
$additionalJS = ['home.js'];
?>

<!-- Categorías Section -->
<section class="categories-section">
    <div class="container">
        <h2 class="section-title">Categorías</h2>
        <div class="categories-grid">
            <div class="category-card">
                <div class="category-icon">
                    <img src="<?= asset('img/categories/frutas.jpg') ?>" alt="Frutas">
                </div>
                <h3>Frutas</h3>
            </div>
            <div class="category-card">
                <div class="category-icon">
                    <img src="<?= asset('img/categories/verduras.jpg') ?>" alt="Verduras">
                </div>
                <h3>Verduras</h3>
            </div>
            <div class="category-card">
                <div class="category-icon">
                    <img src="<?= asset('img/categories/carnes.jpg') ?>" alt="Carnes">
                </div>
                <h3>Carnes</h3>
            </div>
            <div class="category-card">
                <div class="category-icon">
                    <img src="<?= asset('img/categories/lacteos.jpg') ?>" alt="Lácteos">
                </div>
                <h3>Lácteos</h3>
            </div>
            <div class="category-card">
                <div class="category-icon">
                    <img src="<?= asset('img/categories/panaderia.jpg') ?>" alt="Panadería">
                </div>
                <h3>Panadería</h3>
            </div>
            <div class="category-card">
                <div class="category-icon">
                    <img src="<?= asset('img/categories/bebidas.jpg') ?>" alt="Bebidas">
                </div>
                <h3>Bebidas</h3>
            </div>
        </div>
    </div>
</section>

<!-- Productos Destacados Section -->
<section class="featured-products-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Productos Destacados</h2>
            <a href="<?= url('/productos') ?>" class="btn-link">Ver todos</a>
        </div>
        <div class="products-grid">
            <div class="product-card">
                <div class="product-image">
                    <img src="<?= asset('img/products/tomate-cherry.jpg') ?>" alt="Tomates Orgánicos">
                </div>
                <div class="product-info">
                    <h3 class="product-title">Tomates Orgánicos</h3>
                    <p class="product-farmer">Agricultor: Juan Pérez</p>
                    <div class="product-price">$5.50/kg</div>
                </div>
            </div>
            
            <div class="product-card">
                <div class="product-image">
                    <img src="<?= asset('img/products/placeholder.jpg') ?>" alt="Manzanas Rojas">
                </div>
                <div class="product-info">
                    <h3 class="product-title">Manzanas Rojas</h3>
                    <p class="product-farmer">Agricultor: María González</p>
                    <div class="product-price">$3.20/kg</div>
                </div>
            </div>
            
            <div class="product-card">
                <div class="product-image">
                    <img src="<?= asset('img/products/placeholder.jpg') ?>" alt="Lechuga Fresca">
                </div>
                <div class="product-info">
                    <h3 class="product-title">Lechuga Fresca</h3>
                    <p class="product-farmer">Agricultor: Carlos Ruiz</p>
                    <div class="product-price">$2.80/kg</div>
                </div>
            </div>
            
            <div class="product-card">
                <div class="product-image">
                    <img src="<?= asset('img/products/placeholder.jpg') ?>" alt="Zanahorias">
                </div>
                <div class="product-info">
                    <h3 class="product-title">Zanahorias</h3>
                    <p class="product-farmer">Agricultor: Ana Martínez</p>
                    <div class="product-price">$4.00/kg</div>
                </div>
            </div>

</section>

<!-- Newsletter Section -->
<section class="newsletter-cta-section">
    <div class="newsletter-overlay"></div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="newsletter-content">
                    <h2 class="newsletter-title">
                        <i class="fas fa-envelope"></i>
                        ¡Mantente Informado!
                    </h2>
                    <p class="newsletter-description">
                        Suscríbete a nuestro newsletter y recibe ofertas exclusivas, 
                        recetas deliciosas y tips de alimentación saludable.
                    </p>
                    <ul class="newsletter-benefits">
                        <li><i class="fas fa-check"></i> Ofertas exclusivas para suscriptores</li>
                        <li><i class="fas fa-check"></i> Recetas con productos de temporada</li>
                        <li><i class="fas fa-check"></i> Tips de alimentación saludable</li>
                        <li><i class="fas fa-check"></i> Noticias del mundo agrícola</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="newsletter-form-container">
                    <form action="<?= url('/newsletter/suscribir') ?>" method="POST" class="newsletter-form" id="newsletterForm">
                        <?= csrf_field() ?>
                        <h3 class="form-title">Únete a Nuestra Comunidad</h3>
                        <div class="form-group">
                            <input type="text" name="nombre" class="form-control" placeholder="Tu nombre" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" placeholder="Tu correo electrónico" required>
                        </div>
                        <div class="form-group">
                            <label class="form-check">
                                <input type="checkbox" name="acepto_terminos" class="form-check-input" required>
                                <span class="form-check-label">
                                    Acepto recibir comunicaciones de AgroConecta y he leído la 
                                    <a href="<?= url('/politicas/privacidad') ?>" target="_blank">política de privacidad</a>
                                </span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> 
                            Suscribirse Gratis
                        </button>
                        <p class="form-note">
                            <i class="fas fa-shield-alt"></i>
                            No compartimos tu información. Puedes cancelar cuando quieras.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>