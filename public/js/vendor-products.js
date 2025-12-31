class VendorProductsManager {
    constructor() {
        this.init();
        this.selectedProducts = new Set();
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    init() {
        this.setupEventListeners();
        this.initializeComponents();
    }

    setupEventListeners() {
        // Selección masiva
        this.setupBulkSelection();
        
        // Filtros automáticos
        this.setupAutoFilters();
        
        // Búsqueda en tiempo real
        this.setupSearchFilter();
        
        // Acciones de productos
        this.setupProductActions();
        
        // Confirmación de eliminación
        this.setupDeleteConfirmation();
    }

    setupBulkSelection() {
        // Seleccionar todos - vista grid
        const selectAllGrid = document.getElementById('selectAll');
        if (selectAllGrid) {
            selectAllGrid.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('.product-select');
                checkboxes.forEach(cb => {
                    cb.checked = e.target.checked;
                    this.updateProductSelection(cb);
                });
                this.updateBulkActionsButton();
            });
        }

        // Seleccionar todos - vista tabla
        const selectAllTable = document.getElementById('selectAllTable');
        if (selectAllTable) {
            selectAllTable.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('.product-select');
                checkboxes.forEach(cb => {
                    cb.checked = e.target.checked;
                    this.updateProductSelection(cb);
                });
                this.updateBulkActionsButton();
            });
        }

        // Checkboxes individuales
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('product-select')) {
                this.updateProductSelection(e.target);
                this.updateSelectAllState();
                this.updateBulkActionsButton();
            }
        });
    }

    updateProductSelection(checkbox) {
        const productId = parseInt(checkbox.value);
        if (checkbox.checked) {
            this.selectedProducts.add(productId);
        } else {
            this.selectedProducts.delete(productId);
        }
    }

    updateSelectAllState() {
        const allCheckboxes = document.querySelectorAll('.product-select');
        const checkedCount = document.querySelectorAll('.product-select:checked').length;
        
        const selectAllGrid = document.getElementById('selectAll');
        const selectAllTable = document.getElementById('selectAllTable');
        
        [selectAllGrid, selectAllTable].forEach(selectAll => {
            if (selectAll) {
                if (checkedCount === 0) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                } else if (checkedCount === allCheckboxes.length) {
                    selectAll.checked = true;
                    selectAll.indeterminate = false;
                } else {
                    selectAll.checked = false;
                    selectAll.indeterminate = true;
                }
            }
        });
    }

    updateBulkActionsButton() {
        const bulkActionBtn = document.getElementById('bulkActionsBtn');
        if (bulkActionBtn) {
            bulkActionBtn.disabled = this.selectedProducts.size === 0;
            
            // Actualizar texto del botón
            if (this.selectedProducts.size > 0) {
                bulkActionBtn.innerHTML = `
                    <i class="fas fa-edit"></i>
                    Acciones Masivas (${this.selectedProducts.size})
                `;
            } else {
                bulkActionBtn.innerHTML = `
                    <i class="fas fa-edit"></i>
                    Acciones Masivas
                `;
            }
        }
    }

    setupAutoFilters() {
        // Auto-submit cuando cambian los filtros
        const filterSelects = document.querySelectorAll('#category, #status, #stock, #sort');
        filterSelects.forEach(select => {
            select.addEventListener('change', () => {
                document.getElementById('filtersForm').submit();
            });
        });
    }

    setupSearchFilter() {
        let searchTimeout;
        const searchInput = document.getElementById('search');
        
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // Auto-submit después de 500ms de inactividad
                    if (searchInput.value.length === 0 || searchInput.value.length >= 3) {
                        document.getElementById('filtersForm').submit();
                    }
                }, 500);
            });
        }
    }

    setupProductActions() {
        // Hacer disponibles las funciones globalmente
        window.toggleProductStatus = this.toggleProductStatus.bind(this);
        window.duplicateProduct = this.duplicateProduct.bind(this);
        window.deleteProduct = this.deleteProduct.bind(this);
        window.bulkAction = this.bulkAction.bind(this);
    }

    setupDeleteConfirmation() {
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                const productId = confirmDeleteBtn.dataset.productId;
                this.executeDelete(productId);
            });
        }
    }

    async toggleProductStatus(productId, newStatus) {
        try {
            this.showLoadingIndicator(`Cambiando estado del producto...`);
            
            const response = await fetch(`/vendor/products/${productId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ status: newStatus })
            });

            const data = await response.json();
            
            this.hideLoadingIndicator();

            if (data.success) {
                this.showNotification('Estado actualizado correctamente', 'success');
                // Recargar la página para mostrar cambios
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(data.message || 'Error al cambiar el estado');
            }

        } catch (error) {
            this.hideLoadingIndicator();
            this.showNotification('Error al cambiar el estado: ' + error.message, 'error');
        }
    }

    async duplicateProduct(productId) {
        try {
            this.showLoadingIndicator('Duplicando producto...');
            
            const response = await fetch(`/vendor/products/${productId}/duplicate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            const data = await response.json();
            
            this.hideLoadingIndicator();

            if (data.success) {
                this.showNotification('Producto duplicado correctamente', 'success');
                // Redirigir al producto duplicado para editar
                setTimeout(() => {
                    window.location.href = `/vendor/products/${data.newProductId}/edit`;
                }, 1000);
            } else {
                throw new Error(data.message || 'Error al duplicar el producto');
            }

        } catch (error) {
            this.hideLoadingIndicator();
            this.showNotification('Error al duplicar: ' + error.message, 'error');
        }
    }

    deleteProduct(productId, productName) {
        // Mostrar modal de confirmación
        const modal = document.getElementById('deleteConfirmModal');
        const productNameElement = document.getElementById('deleteProductName');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        if (modal && productNameElement && confirmBtn) {
            productNameElement.textContent = productName;
            confirmBtn.dataset.productId = productId;
            
            $(modal).modal('show');
        }
    }

    async executeDelete(productId) {
        try {
            this.showLoadingIndicator('Eliminando producto...');
            
            const response = await fetch(`/vendor/products/${productId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            const data = await response.json();
            
            this.hideLoadingIndicator();

            if (data.success) {
                this.showNotification('Producto eliminado correctamente', 'success');
                
                // Cerrar modal
                $('#deleteConfirmModal').modal('hide');
                
                // Recargar página
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(data.message || 'Error al eliminar el producto');
            }

        } catch (error) {
            this.hideLoadingIndicator();
            this.showNotification('Error al eliminar: ' + error.message, 'error');
        }
    }

    async bulkAction(action) {
        if (this.selectedProducts.size === 0) {
            this.showNotification('Selecciona al menos un producto', 'warning');
            return;
        }

        const productIds = Array.from(this.selectedProducts);
        let actionText = '';
        
        switch (action) {
            case 'activate':
                actionText = 'Activando productos';
                break;
            case 'deactivate':
                actionText = 'Desactivando productos';
                break;
            case 'delete':
                if (!confirm(`¿Estás seguro de eliminar ${productIds.length} producto(s)? Esta acción no se puede deshacer.`)) {
                    return;
                }
                actionText = 'Eliminando productos';
                break;
            default:
                return;
        }

        try {
            this.showLoadingIndicator(`${actionText}...`);
            
            const response = await fetch(`/vendor/products/bulk-action`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    action: action,
                    productIds: productIds
                })
            });

            const data = await response.json();
            
            this.hideLoadingIndicator();

            if (data.success) {
                this.showNotification(`Acción completada: ${data.affected} producto(s) procesado(s)`, 'success');
                
                // Limpiar selección
                this.selectedProducts.clear();
                
                // Recargar página
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(data.message || 'Error en la acción masiva');
            }

        } catch (error) {
            this.hideLoadingIndicator();
            this.showNotification('Error en acción masiva: ' + error.message, 'error');
        }
    }

    initializeComponents() {
        // Inicializar tooltips
        this.initializeTooltips();
        
        // Configurar lazy loading de imágenes
        this.initializeLazyLoading();
        
        // Configurar auto-refresh de estadísticas
        this.initializeStatsRefresh();
    }

    initializeTooltips() {
        if (typeof $().tooltip === 'function') {
            $('[data-toggle="tooltip"]').tooltip();
        }
    }

    initializeLazyLoading() {
        // Implementar lazy loading para imágenes de productos
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            observer.unobserve(img);
                        }
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    initializeStatsRefresh() {
        // Refrescar estadísticas cada 5 minutos
        setInterval(() => {
            this.refreshStats();
        }, 5 * 60 * 1000);
    }

    async refreshStats() {
        try {
            const response = await fetch('/vendor/products/stats', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            if (!response.ok) return;

            const stats = await response.json();
            
            // Actualizar estadísticas en la página
            const statCards = {
                'total': stats.total,
                'activos': stats.activos,
                'stock_bajo': stats.stock_bajo,
                'sin_stock': stats.sin_stock
            };

            Object.entries(statCards).forEach(([key, value]) => {
                const element = document.querySelector(`[data-stat="${key}"] h3`);
                if (element) {
                    element.textContent = this.formatNumber(value);
                }
            });

        } catch (error) {
            console.warn('Error al refrescar estadísticas:', error);
        }
    }

    // Utilidades UI
    showLoadingIndicator(message = 'Procesando...') {
        // Crear o mostrar indicador de carga
        let loader = document.getElementById('globalLoader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'globalLoader';
            loader.className = 'global-loader';
            loader.innerHTML = `
                <div class="loader-content">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="loader-message">${message}</p>
                </div>
            `;
            document.body.appendChild(loader);
        } else {
            loader.querySelector('.loader-message').textContent = message;
        }
        
        loader.style.display = 'flex';
    }

    hideLoadingIndicator() {
        const loader = document.getElementById('globalLoader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    showNotification(message, type = 'info') {
        // Crear notificación toast
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show notification-toast`;
        toast.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;

        // Agregar al contenedor de notificaciones
        let container = document.getElementById('notificationContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notificationContainer';
            container.className = 'notification-container';
            document.body.appendChild(container);
        }

        container.appendChild(toast);

        // Auto-remove después de 5 segundos
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }

    formatNumber(num) {
        return new Intl.NumberFormat('es-MX').format(num);
    }

    // Exportar funciones para uso global
    getSelectedProducts() {
        return Array.from(this.selectedProducts);
    }

    clearSelection() {
        this.selectedProducts.clear();
        document.querySelectorAll('.product-select').forEach(cb => cb.checked = false);
        this.updateSelectAllState();
        this.updateBulkActionsButton();
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.vendorProductsManager = new VendorProductsManager();
});

// Estilos CSS adicionales
const additionalStyles = `
    .global-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loader-content {
        text-align: center;
        color: white;
    }

    .loader-message {
        margin-top: 1rem;
        margin-bottom: 0;
    }

    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9998;
        max-width: 400px;
    }

    .notification-toast {
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .products-grid .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    .products-table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .stock-indicator.in-stock {
        color: #28a745;
        font-weight: 600;
    }

    .stock-indicator.low-stock {
        color: #ffc107;
        font-weight: 600;
    }

    .stock-indicator.out-of-stock {
        color: #dc3545;
        font-weight: 600;
    }

    .rating-stars .fa-star.active {
        color: #ffd700;
    }

    .rating-stars .fa-star:not(.active) {
        color: #e0e0e0;
    }
`;

// Agregar estilos al documento
if (!document.getElementById('vendor-products-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'vendor-products-styles';
    styleSheet.textContent = additionalStyles;
    document.head.appendChild(styleSheet);
}