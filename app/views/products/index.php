<?php 
$title = "Productos - " . ($categoria['nombre'] ?? 'Todos los productos');
$currentPage = "productos";
$metaDescription = "Descubre nuestra amplia selección de productos frescos y naturales. " . ($categoria['descripcion'] ?? 'Productos de alta calidad directo del campo.');
$metaKeywords = "productos agrícolas, " . ($categoria['nombre'] ?? 'verduras') . ", frescos, naturales, orgánicos";
$additionalCSS = ['products.css', 'filters.css'];
$additionalJS = ['products.js', 'filters.js'];

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Productos', 'url' => url('/productos')]
];

if (isset($categoria)) {
    $breadcrumbs[] = ['title' => $categoria['nombre']];
}

ob_start();
?>

<!-- Products Header -->
<section class="products-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="header-content">
                    <?php if (isset($categoria)): ?>
                        <div class="category-info">
                            <span class="category-icon"><?= $categoria['icono'] ?? '🥕' ?></span>
                            <div class="category-details">
                                <h1 class="page-title"><?= htmlspecialchars($categoria['nombre']) ?></h1>
                                <p class="page-description"><?= htmlspecialchars($categoria['descripcion']) ?></p>
                                <div class="category-stats">
                                    <span class="product-count">
                                        <i class="fas fa-box"></i>
                                        <?= $totalProductos ?? 0 ?> productos disponibles
                                    </span>
                                    <span class="vendor-count">
                                        <i class="fas fa-store"></i>
                                        <?= $totalVendedores ?? 0 ?> vendedores
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <h1 class="page-title">
                            <i class="fas fa-shopping-basket"></i>
                            Todos los Productos
                        </h1>
                        <p class="page-description">
                            Descubre nuestra amplia selección de productos frescos y naturales, 
                            cultivados con amor por agricultores locales comprometidos con la calidad.
                        </p>
                        <div class="products-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?= $totalProductos ?? 0 ?></span>
                                <span class="stat-label">Productos</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?= count($categorias ?? []) ?></span>
                                <span class="stat-label">Categorías</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?= $totalVendedores ?? 0 ?></span>
                                <span class="stat-label">Vendedores</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="header-actions">
                    <!-- Quick Search -->
                    <form action="<?= url('/productos/buscar') ?>" method="GET" class="quick-search">
                        <div class="search-group">
                            <input type="text" name="q" class="search-input" 
                                   placeholder="Buscar productos..." 
                                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                            <?php if (isset($categoria)): ?>
                                <input type="hidden" name="categoria" value="<?= $categoria['slug'] ?>">
                            <?php endif; ?>
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    
                    <!-- View Toggle -->
                    <div class="view-toggle">
                        <button class="view-btn <?= ($_COOKIE['products_view'] ?? 'grid') === 'grid' ? 'active' : '' ?>" 
                                data-view="grid" title="Vista en cuadrícula">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn <?= ($_COOKIE['products_view'] ?? 'grid') === 'list' ? 'active' : '' ?>" 
                                data-view="list" title="Vista en lista">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filters Bar -->
<section class="filters-section">
    <div class="container">
        <div class="filters-bar">
            <div class="filters-toggle">
                <button class="filters-btn" id="toggleFilters">
                    <i class="fas fa-filter"></i>
                    Filtros
                    <span class="filters-count" style="display: none;">0</span>
                </button>
            </div>
            
            <div class="sorting-options">
                <label for="sortBy">Ordenar por:</label>
                <select id="sortBy" name="sort" class="sort-select">
                    <option value="relevance" <?= ($_GET['sort'] ?? '') === 'relevance' ? 'selected' : '' ?>>Más Relevantes</option>
                    <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Precio: Menor a Mayor</option>
                    <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Precio: Mayor a Menor</option>
                    <option value="name_asc" <?= ($_GET['sort'] ?? '') === 'name_asc' ? 'selected' : '' ?>>Nombre: A-Z</option>
                    <option value="rating" <?= ($_GET['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>Mejor Calificados</option>
                    <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Más Recientes</option>
                </select>
            </div>
            
            <div class="results-info">
                Mostrando <?= $currentPage ?? 1 ?>-<?= min(($currentPage ?? 1) * ($perPage ?? 12), $totalProductos ?? 0) ?> 
                de <?= $totalProductos ?? 0 ?> resultados
            </div>
        </div>
        
        <!-- Active Filters -->
        <div class="active-filters" id="activeFilters" style="display: none;">
            <div class="filters-header">
                <span>Filtros activos:</span>
                <button class="clear-all-btn" id="clearAllFilters">Limpiar todos</button>
            </div>
            <div class="filters-tags" id="filtersTagsContainer">
                <!-- Se llenarán dinámicamente -->
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="products-main">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3">
                <div class="filters-sidebar" id="filtersSidebar">
                    <div class="filters-header">
                        <h3>
                            <i class="fas fa-sliders-h"></i>
                            Filtrar Productos
                        </h3>
                        <button class="filters-close" id="closeFilters">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form class="filters-form" id="filtersForm">
                        <!-- Categorías -->
                        <?php if (!isset($categoria)): ?>
                        <div class="filter-group">
                            <h4 class="filter-title">
                                <i class="fas fa-tags"></i>
                                Categorías
                                <button type="button" class="collapse-btn" data-target="categoryFilters">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h4>
                            <div class="filter-content" id="categoryFilters">
                                <?php if (isset($categorias)): ?>
                                    <?php foreach ($categorias as $cat): ?>
                                        <label class="filter-option">
                                            <input type="checkbox" name="categories[]" value="<?= $cat['slug'] ?>"
                                                   <?= in_array($cat['slug'], $_GET['categories'] ?? []) ? 'checked' : '' ?>>
                                            <span class="checkmark"></span>
                                            <span class="option-text">
                                                <?= $cat['icono'] ?? '📦' ?> <?= htmlspecialchars($cat['nombre']) ?>
                                                <span class="count">(<?= $cat['productos_count'] ?? 0 ?>)</span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Precio -->
                        <div class="filter-group">
                            <h4 class="filter-title">
                                <i class="fas fa-dollar-sign"></i>
                                Rango de Precio
                                <button type="button" class="collapse-btn" data-target="priceFilters">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h4>
                            <div class="filter-content" id="priceFilters">
                                <div class="price-range">
                                    <div class="price-inputs">
                                        <div class="price-input-group">
                                            <label>Mínimo</label>
                                            <input type="number" name="price_min" class="price-input" 
                                                   placeholder="0" min="0" step="10" 
                                                   value="<?= $_GET['price_min'] ?? '' ?>">
                                        </div>
                                        <div class="price-separator">-</div>
                                        <div class="price-input-group">
                                            <label>Máximo</label>
                                            <input type="number" name="price_max" class="price-input" 
                                                   placeholder="1000" min="0" step="10"
                                                   value="<?= $_GET['price_max'] ?? '' ?>">
                                        </div>
                                    </div>
                                    <div class="price-slider">
                                        <input type="range" id="priceRangeMin" min="0" max="1000" step="10" 
                                               value="<?= $_GET['price_min'] ?? 0 ?>">
                                        <input type="range" id="priceRangeMax" min="0" max="1000" step="10" 
                                               value="<?= $_GET['price_max'] ?? 1000 ?>">
                                    </div>
                                </div>
                                
                                <!-- Price presets -->
                                <div class="price-presets">
                                    <button type="button" class="preset-btn" data-min="0" data-max="50">Hasta $50</button>
                                    <button type="button" class="preset-btn" data-min="50" data-max="100">$50 - $100</button>
                                    <button type="button" class="preset-btn" data-min="100" data-max="200">$100 - $200</button>
                                    <button type="button" class="preset-btn" data-min="200" data-max="">Más de $200</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Calificación -->
                        <div class="filter-group">
                            <h4 class="filter-title">
                                <i class="fas fa-star"></i>
                                Calificación
                                <button type="button" class="collapse-btn" data-target="ratingFilters">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h4>
                            <div class="filter-content" id="ratingFilters">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <label class="filter-option">
                                        <input type="radio" name="rating" value="<?= $i ?>"
                                               <?= ($_GET['rating'] ?? '') == $i ? 'checked' : '' ?>>
                                        <span class="radio-mark"></span>
                                        <span class="option-text">
                                            <div class="stars">
                                                <?php for ($j = 1; $j <= 5; $j++): ?>
                                                    <i class="fas fa-star <?= $j <= $i ? 'active' : '' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="rating-text">y superior</span>
                                        </span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Disponibilidad -->
                        <div class="filter-group">
                            <h4 class="filter-title">
                                <i class="fas fa-box"></i>
                                Disponibilidad
                                <button type="button" class="collapse-btn" data-target="availabilityFilters">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h4>
                            <div class="filter-content" id="availabilityFilters">
                                <label class="filter-option">
                                    <input type="checkbox" name="in_stock" value="1"
                                           <?= isset($_GET['in_stock']) ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    <span class="option-text">Solo disponibles</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="on_sale" value="1"
                                           <?= isset($_GET['on_sale']) ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    <span class="option-text">En oferta</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Características -->
                        <div class="filter-group">
                            <h4 class="filter-title">
                                <i class="fas fa-leaf"></i>
                                Características
                                <button type="button" class="collapse-btn" data-target="featuresFilters">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h4>
                            <div class="filter-content" id="featuresFilters">
                                <label class="filter-option">
                                    <input type="checkbox" name="organic" value="1"
                                           <?= isset($_GET['organic']) ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    <span class="option-text">
                                        <i class="fas fa-leaf text-success"></i> Orgánico
                                    </span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="local" value="1"
                                           <?= isset($_GET['local']) ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    <span class="option-text">
                                        <i class="fas fa-map-marker-alt text-primary"></i> Producto Local
                                    </span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="fast_delivery" value="1"
                                           <?= isset($_GET['fast_delivery']) ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    <span class="option-text">
                                        <i class="fas fa-shipping-fast text-info"></i> Entrega Rápida
                                    </span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Vendedores -->
                        <div class="filter-group">
                            <h4 class="filter-title">
                                <i class="fas fa-store"></i>
                                Vendedores
                                <button type="button" class="collapse-btn" data-target="vendorFilters">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h4>
                            <div class="filter-content" id="vendorFilters">
                                <?php if (isset($topVendedores)): ?>
                                    <?php foreach ($topVendedores as $vendor): ?>
                                        <label class="filter-option">
                                            <input type="checkbox" name="vendors[]" value="<?= $vendor['id'] ?>"
                                                   <?= in_array($vendor['id'], $_GET['vendors'] ?? []) ? 'checked' : '' ?>>
                                            <span class="checkmark"></span>
                                            <span class="option-text">
                                                <img src="<?= asset('img/vendors/' . ($vendor['avatar'] ?? 'default.png')) ?>" 
                                                     alt="<?= htmlspecialchars($vendor['nombre']) ?>" class="vendor-avatar">
                                                <?= htmlspecialchars($vendor['nombre']) ?>
                                                <div class="vendor-rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?= $i <= ($vendor['rating'] ?? 0) ? 'active' : '' ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-filter"></i> Aplicar Filtros
                            </button>
                            <button type="reset" class="btn btn-outline btn-block">
                                <i class="fas fa-undo"></i> Restablecer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Products Grid/List -->
            <div class="col-lg-9">
                <div class="products-container">
                    <?php if (isset($productos) && !empty($productos)): ?>
                        <div class="products-grid <?= ($_COOKIE['products_view'] ?? 'grid') === 'list' ? 'list-view' : 'grid-view' ?>" id="productsGrid">
                            <?php foreach ($productos as $producto): ?>
                                <div class="product-item">
                                    <?php include APP_PATH . '/views/components/product-card.php'; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if (($totalPages ?? 1) > 1): ?>
                            <nav aria-label="Navegación de productos" class="products-pagination">
                                <ul class="pagination">
                                    <?php if (($currentPage ?? 1) > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= build_pagination_url($currentPage - 1, $_GET) ?>">
                                                <i class="fas fa-chevron-left"></i>
                                                Anterior
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $startPage = max(1, ($currentPage ?? 1) - 2);
                                    $endPage = min($totalPages ?? 1, ($currentPage ?? 1) + 2);
                                    ?>
                                    
                                    <?php if ($startPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= build_pagination_url(1, $_GET) ?>">1</a>
                                        </li>
                                        <?php if ($startPage > 2): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                        <li class="page-item <?= $i === ($currentPage ?? 1) ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= build_pagination_url($i, $_GET) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($endPage < ($totalPages ?? 1)): ?>
                                        <?php if ($endPage < ($totalPages ?? 1) - 1): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= build_pagination_url($totalPages ?? 1, $_GET) ?>"><?= $totalPages ?? 1 ?></a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php if (($currentPage ?? 1) < ($totalPages ?? 1)): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= build_pagination_url(($currentPage ?? 1) + 1, $_GET) ?>">
                                                Siguiente
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <!-- No products found -->
                        <div class="no-products">
                            <div class="no-products-content">
                                <i class="fas fa-search-minus"></i>
                                <h3>No se encontraron productos</h3>
                                <p>
                                    <?php if (!empty($_GET)): ?>
                                        No hay productos que coincidan con los filtros seleccionados. 
                                        Prueba ajustando o removiendo algunos filtros.
                                    <?php else: ?>
                                        Aún no hay productos disponibles en esta categoría. 
                                        ¡Pronto agregaremos más productos frescos!
                                    <?php endif; ?>
                                </p>
                                <div class="no-products-actions">
                                    <?php if (!empty($_GET)): ?>
                                        <a href="<?= url('/productos' . (isset($categoria) ? '/categoria/' . $categoria['slug'] : '')) ?>" 
                                           class="btn btn-primary">
                                            <i class="fas fa-undo"></i> Ver Todos los Productos
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= url('/') ?>" class="btn btn-outline">
                                        <i class="fas fa-home"></i> Volver al Inicio
                                    </a>
                                </div>
                                
                                <!-- Suggested categories -->
                                <?php if (isset($categoriasAlternativas)): ?>
                                    <div class="suggested-categories">
                                        <h4>Tal vez te interesen estas categorías:</h4>
                                        <div class="categories-suggestions">
                                            <?php foreach ($categoriasAlternativas as $catAlt): ?>
                                                <a href="<?= url('/productos/categoria/' . $catAlt['slug']) ?>" 
                                                   class="category-suggestion">
                                                    <span class="category-icon"><?= $catAlt['icono'] ?? '📦' ?></span>
                                                    <span class="category-name"><?= htmlspecialchars($catAlt['nombre']) ?></span>
                                                    <span class="category-count"><?= $catAlt['productos_count'] ?? 0 ?> productos</span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recently Viewed Products -->
<?php if (!empty($productosVistos)): ?>
<section class="recently-viewed-section">
    <div class="container">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-history"></i>
                Productos Vistos Recientemente
            </h3>
        </div>
        <div class="recently-viewed-carousel">
            <?php foreach ($productosVistos as $producto): ?>
                <div class="product-slide">
                    <?php include APP_PATH . '/views/components/product-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Quick View Modal -->
<div class="modal-overlay" id="quickViewModal">
    <div class="modal quick-view-modal">
        <div class="modal-header">
            <h4 class="modal-title">Vista Rápida del Producto</h4>
            <button class="modal-close" id="closeQuickView">&times;</button>
        </div>
        <div class="modal-body">
            <div id="quickViewContent">
                <div class="loading-spinner-container">
                    <div class="loading-spinner"></div>
                    <p>Cargando producto...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Overlay for Mobile -->
<div class="filter-overlay" id="filterOverlay"></div>

<?php 
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>