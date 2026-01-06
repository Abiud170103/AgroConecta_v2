<?php
/**
 * Layout Base - Componente reutilizable para mantener consistencia visual
 */

class LayoutBase {
    
    public static function renderHeader($title = 'AgroConecta', $user = null) {
        return '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
            <meta http-equiv="Pragma" content="no-cache">
            <meta http-equiv="Expires" content="0">
            <title>' . htmlspecialchars($title) . ' - AgroConecta</title>
            
            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <!-- Custom CSS -->
            <link rel="stylesheet" href="css/app.css">
            
            <style>
                body {
                    background-color: var(--bg-secondary);
                    padding-top: 80px;
                    font-family: var(--font-family-primary);
                }
                
                /* Navbar consistente */
                .navbar-custom {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    position: fixed;
                    top: 0;
                    width: 100%;
                    z-index: 1000;
                    box-shadow: var(--shadow-md);
                    height: var(--header-height);
                }
                
                .navbar-brand {
                    color: var(--text-primary) !important;
                    font-family: var(--font-family-heading);
                    font-weight: 700;
                    font-size: var(--font-size-xl);
                    text-decoration: none;
                }
                
                .navbar-brand .text-success {
                    color: var(--primary-color) !important;
                }
                
                /* Sidebar moderno */
                .sidebar {
                    background: var(--bg-primary);
                    box-shadow: var(--shadow-md);
                    border-radius: var(--border-radius-lg);
                    border: none;
                }
                
                .sidebar-header {
                    background: var(--primary-gradient);
                    color: var(--text-white);
                    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
                    padding: var(--spacing-lg);
                }
                
                .sidebar .list-group-item {
                    border: none;
                    padding: var(--spacing-md) var(--spacing-lg);
                    color: var(--text-secondary);
                    transition: var(--transition-normal);
                    font-weight: 500;
                }
                
                .sidebar .list-group-item:hover {
                    background-color: var(--bg-secondary);
                    color: var(--primary-color);
                    transform: translateX(5px);
                }
                
                .sidebar .list-group-item.active {
                    background-color: var(--primary-color);
                    color: var(--text-white);
                    border-radius: var(--border-radius-md);
                    margin: 0 var(--spacing-sm);
                }
                
                /* Cards estadísticas */
                .stat-card {
                    background: var(--bg-primary);
                    border: none;
                    border-radius: var(--border-radius-lg);
                    box-shadow: var(--shadow-md);
                    transition: var(--transition-normal);
                    overflow: hidden;
                    position: relative;
                }
                
                .stat-card::before {
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: var(--primary-gradient);
                }
                
                .stat-card:hover {
                    transform: translateY(-5px);
                    box-shadow: var(--shadow-lg);
                }
                
                .stat-icon {
                    width: 60px;
                    height: 60px;
                    border-radius: var(--border-radius-lg);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: var(--font-size-2xl);
                    margin-bottom: var(--spacing-md);
                }
                
                .stat-number {
                    font-size: var(--font-size-3xl);
                    font-weight: 700;
                    color: var(--text-primary);
                    font-family: var(--font-family-heading);
                }
                
                .stat-label {
                    color: var(--text-secondary);
                    font-weight: 500;
                    font-size: var(--font-size-sm);
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                
                /* Colores para estadísticas */
                .stat-success { background: rgba(40, 167, 69, 0.1); color: var(--success-color); }
                .stat-warning { background: rgba(255, 193, 7, 0.1); color: var(--warning-color); }
                .stat-info { background: rgba(23, 162, 184, 0.1); color: var(--info-color); }
                .stat-primary { background: rgba(40, 167, 69, 0.1); color: var(--primary-color); }
                
                /* Cards de contenido */
                .content-card {
                    background: var(--bg-primary);
                    border: none;
                    border-radius: var(--border-radius-lg);
                    box-shadow: var(--shadow-md);
                    transition: var(--transition-normal);
                }
                
                .content-card:hover {
                    box-shadow: var(--shadow-lg);
                }
                
                .content-card .card-header {
                    background: var(--bg-secondary);
                    border: none;
                    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
                    padding: var(--spacing-lg);
                }
                
                .content-card .card-title {
                    color: var(--text-primary);
                    font-family: var(--font-family-heading);
                    font-weight: 600;
                    font-size: var(--font-size-lg);
                    margin: 0;
                }
                
                /* Botones modernos */
                .btn-modern {
                    border-radius: var(--border-radius-md);
                    font-weight: 500;
                    padding: var(--spacing-md) var(--spacing-lg);
                    transition: var(--transition-normal);
                    border: none;
                    box-shadow: var(--shadow-sm);
                }
                
                .btn-modern:hover {
                    transform: translateY(-2px);
                    box-shadow: var(--shadow-md);
                }
                
                .btn-modern.btn-primary {
                    background: var(--primary-gradient);
                }
                
                /* Títulos y espaciado */
                .main-content {
                    padding: var(--spacing-xl);
                }
                
                .page-title {
                    color: var(--text-primary);
                    font-family: var(--font-family-heading);
                    font-weight: 700;
                    font-size: var(--font-size-3xl);
                    margin-bottom: var(--spacing-xl);
                }
                
                .section-title {
                    color: var(--text-primary);
                    font-family: var(--font-family-heading);
                    font-weight: 600;
                    font-size: var(--font-size-xl);
                    margin-bottom: var(--spacing-lg);
                }
                
                /* Alertas */
                .alert-modern {
                    border: none;
                    border-radius: var(--border-radius-lg);
                    box-shadow: var(--shadow-sm);
                    padding: var(--spacing-lg);
                }
            </style>
        </head>
        <body>';
    }
    
    public static function renderNavbar($user) {
        return '
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">
                    <i class="fas fa-seedling text-success me-2"></i>
                    <strong>Agro</strong><span class="text-success">Conecta</span>
                </a>
                
                <div class="navbar-nav ms-auto">
                    <span class="navbar-text me-3">
                        <i class="fas fa-user-circle me-1"></i>
                        ' . htmlspecialchars($user['nombre']) . '
                        <small class="text-muted">(' . ucfirst($user['tipo']) . ')</small>
                    </span>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
                    </a>
                </div>
            </div>
        </nav>';
    }
    
    public static function renderSidebar($user, $activeItem = 'dashboard') {
        $menuItems = self::getMenuItems($user['tipo']);
        
        $sidebar = '
        <div class="sidebar">
            <div class="sidebar-header">
                <h5 class="mb-0">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Panel ' . ucfirst($user['tipo']) . '
                </h5>
            </div>
            <div class="list-group list-group-flush">';
        
        foreach ($menuItems as $item) {
            $active = ($item['key'] === $activeItem) ? 'active' : '';
            $sidebar .= '
                <a href="' . $item['url'] . '" class="list-group-item list-group-item-action ' . $active . '">
                    <i class="' . $item['icon'] . ' me-2"></i>' . $item['label'] . '
                </a>';
        }
        
        $sidebar .= '
            </div>
        </div>';
        
        return $sidebar;
    }
    
    private static function getMenuItems($userType) {
        switch ($userType) {
            case 'vendedor':
                return [
                    ['key' => 'dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home', 'label' => 'Dashboard'],
                    ['key' => 'productos', 'url' => 'productos.php', 'icon' => 'fas fa-box', 'label' => 'Mis Productos'],
                    ['key' => 'pedidos', 'url' => 'pedidos-vendedor.php', 'icon' => 'fas fa-shopping-cart', 'label' => 'Pedidos Recibidos'],
                    ['key' => 'ventas', 'url' => 'ventas.php', 'icon' => 'fas fa-chart-line', 'label' => 'Reportes de Ventas'],
                    ['key' => 'perfil', 'url' => 'perfil-vendedor.php', 'icon' => 'fas fa-user-tie', 'label' => 'Mi Perfil']
                ];
            case 'cliente':
                return [
                    ['key' => 'dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home', 'label' => 'Dashboard'],
                    ['key' => 'catalogo', 'url' => 'catalogo.php', 'icon' => 'fas fa-store', 'label' => 'Catálogo'],
                    ['key' => 'pedidos', 'url' => 'mis-pedidos.php', 'icon' => 'fas fa-shopping-bag', 'label' => 'Mis Pedidos'],
                    ['key' => 'favoritos', 'url' => 'favoritos.php', 'icon' => 'fas fa-heart', 'label' => 'Favoritos'],
                    ['key' => 'perfil', 'url' => 'perfil-cliente.php', 'icon' => 'fas fa-user', 'label' => 'Mi Perfil']
                ];
            case 'admin':
                return [
                    ['key' => 'dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home', 'label' => 'Dashboard'],
                    ['key' => 'usuarios', 'url' => 'usuarios.php', 'icon' => 'fas fa-users', 'label' => 'Gestión de Usuarios'],
                    ['key' => 'productos', 'url' => 'productos.php', 'icon' => 'fas fa-boxes', 'label' => 'Todos los Productos'],
                    ['key' => 'pedidos', 'url' => 'pedidos-admin.php', 'icon' => 'fas fa-clipboard-list', 'label' => 'Todos los Pedidos'],
                    ['key' => 'reportes', 'url' => 'reportes.php', 'icon' => 'fas fa-chart-bar', 'label' => 'Reportes del Sistema'],
                    ['key' => 'configuracion', 'url' => 'configuracion.php', 'icon' => 'fas fa-cogs', 'label' => 'Configuración']
                ];
            default:
                return [];
        }
    }
    
    public static function renderFooter() {
        return '
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <script>
            // Animaciones suaves para los cards
            document.addEventListener("DOMContentLoaded", function() {
                const cards = document.querySelectorAll(".stat-card, .content-card");
                cards.forEach((card, index) => {
                    card.style.opacity = "0";
                    card.style.transform = "translateY(20px)";
                    setTimeout(() => {
                        card.style.transition = "all 0.5s ease";
                        card.style.opacity = "1";
                        card.style.transform = "translateY(0)";
                    }, index * 100);
                });
            });
        </script>
        </body>
        </html>';
    }
    
    public static function renderAlert($type = 'success', $message = '', $dismissible = true) {
        $icons = [
            'success' => 'fas fa-check-circle',
            'error' => 'fas fa-exclamation-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'info' => 'fas fa-info-circle'
        ];
        
        $icon = $icons[$type] ?? $icons['info'];
        $dismissBtn = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' : '';
        
        return '
        <div class="alert alert-' . $type . ' alert-modern alert-dismissible fade show" role="alert">
            <i class="' . $icon . ' me-2"></i>
            ' . $message . '
            ' . $dismissBtn . '
        </div>';
    }
}
?>