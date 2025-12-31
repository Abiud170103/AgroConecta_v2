// =========================================
// CART.JS - Funcionalidad del carrito de compras
// =========================================

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // === ELEMENTOS DEL DOM ===
    const cartItemsList = document.getElementById('cartItemsList');
    const selectAllCheckbox = document.getElementById('selectAll');
    const removeSelectedBtn = document.getElementById('removeSelectedBtn');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const summarySubtotal = document.getElementById('summarySubtotal');
    const summaryShipping = document.getElementById('summaryShipping');
    const summaryTotal = document.getElementById('summaryTotal');
    const couponCodeInput = document.getElementById('couponCode');
    
    let cartData = {
        items: [],
        subtotal: 0,
        shipping: 0,
        discount: 0,
        total: 0
    };
    
    // === GESTIÓN DE SELECCIÓN DE ITEMS ===
    const SelectionManager = {
        init: function() {
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', this.handleSelectAll.bind(this));
            }
            
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', this.handleItemSelect.bind(this));
            });
            
            if (removeSelectedBtn) {
                removeSelectedBtn.addEventListener('click', this.removeSelected.bind(this));
            }
        },
        
        handleSelectAll: function() {
            const isChecked = selectAllCheckbox.checked;
            
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            
            this.updateRemoveButton();
        },
        
        handleItemSelect: function() {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            const totalItems = itemCheckboxes.length;
            
            // Update select all checkbox state
            if (selectAllCheckbox) {
                if (checkedItems.length === totalItems) {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else if (checkedItems.length === 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                } else {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = true;
                }
            }
            
            this.updateRemoveButton();
        },
        
        updateRemoveButton: function() {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            
            if (removeSelectedBtn) {
                removeSelectedBtn.disabled = checkedItems.length === 0;
                removeSelectedBtn.innerHTML = checkedItems.length > 0 
                    ? `<i class="fas fa-trash"></i> Eliminar seleccionados (${checkedItems.length})`
                    : `<i class="fas fa-trash"></i> Eliminar seleccionados`;
            }
        },
        
        removeSelected: async function() {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            const itemIds = Array.from(checkedItems).map(checkbox => checkbox.dataset.itemId);
            
            if (itemIds.length === 0) return;
            
            const confirmMessage = `¿Estás seguro de que quieres eliminar ${itemIds.length} producto${itemIds.length > 1 ? 's' : ''} del carrito?`;
            
            if (confirm(confirmMessage)) {
                try {
                    await this.removeMultipleItems(itemIds);
                } catch (error) {
                    console.error('Error removing selected items:', error);
                    if (window.AgroConectaUtils) {
                        window.AgroConectaUtils.showToast('Error al eliminar productos', 'error');
                    }
                }
            }
        },
        
        removeMultipleItems: async function(itemIds) {
            LoadingManager.show('Eliminando productos...');
            
            try {
                const response = await fetch('/cart/remove-multiple', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        item_ids: itemIds,
                        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Remove items from DOM
                    itemIds.forEach(itemId => {
                        const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                        if (itemElement) {
                            itemElement.remove();
                        }
                    });
                    
                    // Update cart summary
                    this.updateCartSummary(result.cart_data);
                    
                    // Update header cart counter
                    if (window.updateCartCounter) {
                        window.updateCartCounter(result.cart_data.item_count);
                    }
                    
                    // Reset selection state
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    }
                    
                    this.updateRemoveButton();
                    
                    if (window.AgroConectaUtils) {
                        window.AgroConectaUtils.showToast(
                            `${itemIds.length} producto${itemIds.length > 1 ? 's eliminados' : ' eliminado'} del carrito`,
                            'success'
                        );
                    }
                    
                    // Check if cart is empty now
                    if (result.cart_data.item_count === 0) {
                        location.reload(); // Reload to show empty cart state
                    }
                    
                } else {
                    throw new Error(result.message || 'Error al eliminar productos');
                }
                
            } finally {
                LoadingManager.hide();
            }
        },
        
        updateCartSummary: function(cartData) {
            if (summarySubtotal) {
                summarySubtotal.textContent = `$${cartData.subtotal.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
            }
            
            if (summaryShipping) {
                summaryShipping.innerHTML = cartData.shipping > 0 
                    ? `$${cartData.shipping.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`
                    : '<span class="text-success">¡Gratis!</span>';
            }
            
            if (summaryTotal) {
                summaryTotal.textContent = `$${cartData.total.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
            }
            
            // Update item count in summary
            const itemCountElement = document.querySelector('.summary-row .label');
            if (itemCountElement) {
                itemCountElement.textContent = `Subtotal (${cartData.item_count} productos):`;
            }
        }
    };
    
    // === GESTIÓN DE CANTIDADES ===
    const QuantityManager = {
        updateQuantity: async function(itemId, action, value = null) {
            const quantityInput = document.getElementById(`quantity_${itemId}`);
            const subtotalElement = document.getElementById(`subtotal_${itemId}`);
            
            if (!quantityInput) return;
            
            let newQuantity = parseInt(quantityInput.value);
            const maxStock = parseInt(quantityInput.max);
            
            // Calculate new quantity
            switch (action) {
                case 'increase':
                    newQuantity = Math.min(newQuantity + 1, maxStock);
                    break;
                case 'decrease':
                    newQuantity = Math.max(newQuantity - 1, 1);
                    break;
                case 'set':
                    newQuantity = Math.max(1, Math.min(parseInt(value) || 1, maxStock));
                    break;
            }
            
            // Don't update if quantity hasn't changed
            if (newQuantity === parseInt(quantityInput.value)) {
                return;
            }
            
            LoadingManager.show('Actualizando cantidad...');
            
            try {
                const response = await fetch('/cart/update-quantity', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        item_id: itemId,
                        quantity: newQuantity,
                        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update quantity input
                    quantityInput.value = newQuantity;
                    
                    // Update quantity buttons state
                    this.updateQuantityButtons(itemId, newQuantity, maxStock);
                    
                    // Update item subtotal
                    if (subtotalElement && result.item_subtotal) {
                        subtotalElement.textContent = result.item_subtotal.toLocaleString('es-MX', { minimumFractionDigits: 2 });
                    }
                    
                    // Update cart summary
                    SelectionManager.updateCartSummary(result.cart_data);
                    
                    // Update header cart counter
                    if (window.updateCartCounter) {
                        window.updateCartCounter(result.cart_data.item_count);
                    }
                    
                    if (window.AgroConectaUtils) {
                        window.AgroConectaUtils.showToast('Cantidad actualizada', 'success');
                    }
                    
                } else {
                    throw new Error(result.message || 'Error al actualizar cantidad');
                }
                
            } catch (error) {
                console.error('Quantity update error:', error);
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('Error al actualizar cantidad', 'error');
                }
                
                // Revert quantity input value
                quantityInput.value = quantityInput.dataset.originalValue || quantityInput.value;
                
            } finally {
                LoadingManager.hide();
            }
        },
        
        updateQuantityButtons: function(itemId, quantity, maxStock) {
            const decreaseBtn = document.querySelector(`[onclick="updateQuantity(${itemId}, 'decrease')"]`);
            const increaseBtn = document.querySelector(`[onclick="updateQuantity(${itemId}, 'increase')"]`);
            
            if (decreaseBtn) {
                decreaseBtn.disabled = quantity <= 1;
            }
            
            if (increaseBtn) {
                increaseBtn.disabled = quantity >= maxStock;
            }
        }
    };
    
    // === GESTIÓN DE CUPONES ===
    const CouponManager = {
        applyCoupon: async function() {
            const couponCode = couponCodeInput?.value.trim();
            
            if (!couponCode) {
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('Ingresa un código de cupón', 'warning');
                }
                return;
            }
            
            LoadingManager.show('Aplicando cupón...');
            
            try {
                const response = await fetch('/cart/apply-coupon', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        coupon_code: couponCode,
                        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update summary with discount
                    this.showCouponApplied(couponCode, result.discount_amount);
                    
                    // Update cart summary
                    SelectionManager.updateCartSummary(result.cart_data);
                    
                    if (window.AgroConectaUtils) {
                        window.AgroConectaUtils.showToast(
                            `Cupón aplicado! Descuento: $${result.discount_amount.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`,
                            'success'
                        );
                    }
                    
                } else {
                    throw new Error(result.message || 'Cupón no válido');
                }
                
            } catch (error) {
                console.error('Coupon application error:', error);
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast(error.message, 'error');
                }
                
            } finally {
                LoadingManager.hide();
            }
        },
        
        removeCoupon: async function() {
            LoadingManager.show('Removiendo cupón...');
            
            try {
                const response = await fetch('/cart/remove-coupon', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Hide coupon applied section
                    this.hideCouponApplied();
                    
                    // Clear coupon input
                    if (couponCodeInput) {
                        couponCodeInput.value = '';
                    }
                    
                    // Update cart summary
                    SelectionManager.updateCartSummary(result.cart_data);
                    
                    if (window.AgroConectaUtils) {
                        window.AgroConectaUtils.showToast('Cupón removido', 'success');
                    }
                    
                } else {
                    throw new Error(result.message || 'Error al remover cupón');
                }
                
            } catch (error) {
                console.error('Coupon removal error:', error);
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('Error al remover cupón', 'error');
                }
                
            } finally {
                LoadingManager.hide();
            }
        },
        
        showCouponApplied: function(couponCode, discountAmount) {
            // Add discount row if it doesn't exist
            let discountRow = document.querySelector('.summary-row.discount');
            
            if (!discountRow) {
                const subtotalRow = document.querySelector('.summary-row');
                discountRow = document.createElement('div');
                discountRow.className = 'summary-row discount';
                discountRow.innerHTML = `
                    <span class="label">
                        <i class="fas fa-tag"></i>
                        Descuento aplicado:
                    </span>
                    <span class="amount">-$${discountAmount.toLocaleString('es-MX', { minimumFractionDigits: 2 })}</span>
                `;
                subtotalRow.parentNode.insertBefore(discountRow, subtotalRow.nextSibling);
            } else {
                discountRow.querySelector('.amount').textContent = `-$${discountAmount.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
            }
            
            // Show coupon applied message
            let couponApplied = document.querySelector('.coupon-applied');
            if (!couponApplied) {
                const couponSection = document.querySelector('.coupon-section');
                couponApplied = document.createElement('div');
                couponApplied.className = 'coupon-applied';
                couponApplied.innerHTML = `
                    <i class="fas fa-check-circle text-success"></i>
                    Cupón "${couponCode}" aplicado
                    <button type="button" class="btn-remove-coupon" onclick="removeCoupon()">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                couponSection.appendChild(couponApplied);
            }
        },
        
        hideCouponApplied: function() {
            const discountRow = document.querySelector('.summary-row.discount');
            const couponApplied = document.querySelector('.coupon-applied');
            
            if (discountRow) {
                discountRow.remove();
            }
            
            if (couponApplied) {
                couponApplied.remove();
            }
        }
    };
    
    // === CALCULADORA DE ENVÍO ===
    const ShippingCalculator = {
        calculateShipping: async function() {
            const addressSelect = document.getElementById('shippingAddress');
            const cityInput = document.getElementById('shippingCity');
            const stateInput = document.getElementById('shippingState');
            const zipInput = document.getElementById('shippingZip');
            
            let shippingData = {};
            
            if (addressSelect && addressSelect.value) {
                // Usuario con dirección guardada
                shippingData.address_id = addressSelect.value;
            } else if (cityInput && stateInput) {
                // Nueva dirección
                shippingData.city = cityInput.value.trim();
                shippingData.state = stateInput.value.trim();
                shippingData.zip = zipInput?.value.trim() || '';
                
                if (!shippingData.city || !shippingData.state) {
                    if (window.AgroConectaUtils) {
                        window.AgroConectaUtils.showToast('Completa la información de envío', 'warning');
                    }
                    return;
                }
            }
            
            LoadingManager.show('Calculando envío...');
            
            try {
                const response = await fetch('/cart/calculate-shipping', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        ...shippingData,
                        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update shipping cost in summary
                    if (summaryShipping) {
                        summaryShipping.innerHTML = result.shipping_cost > 0 
                            ? `$${result.shipping_cost.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`
                            : '<span class="text-success">¡Gratis!</span>';
                    }
                    
                    // Update cart summary
                    SelectionManager.updateCartSummary(result.cart_data);
                    
                    if (window.AgroConectaUtils) {
                        window.AgroConectaUtils.showToast('Costo de envío actualizado', 'success');
                    }
                    
                } else {
                    throw new Error(result.message || 'Error al calcular envío');
                }
                
            } catch (error) {
                console.error('Shipping calculation error:', error);
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('Error al calcular envío', 'error');
                }
                
            } finally {
                LoadingManager.hide();
            }
        },
        
        toggleNewAddress: function() {
            const newAddressForm = document.getElementById('newAddressForm');
            const addressSelect = document.getElementById('shippingAddress');
            
            if (newAddressForm) {
                const isVisible = newAddressForm.style.display !== 'none';
                newAddressForm.style.display = isVisible ? 'none' : 'block';
                
                if (!isVisible && addressSelect) {
                    addressSelect.value = '';
                }
            }
        }
    };
    
    // === GESTIÓN DE ITEMS INDIVIDUALES ===
    const ItemManager = {
        removeItem: async function(itemId) {
            if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
                return;
            }
            
            LoadingManager.show('Eliminando producto...');
            
            try {
                const response = await fetch('/cart/remove-item', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        item_id: itemId,
                        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Remove item from DOM
                    const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                    if (itemElement) {
                        itemElement.remove();
                    }
                    
                    // Update cart summary
                    SelectionManager.updateCartSummary(result.cart_data);
                    
                    // Update header cart counter
                    if (window.updateCartCounter) {
                        window.updateCartCounter(result.cart_data.item_count);
                    }
                    
                    if (window.AgroConectaUtils) {
                        window.AgroConectaUtils.showToast('Producto eliminado del carrito', 'success');
                    }
                    
                    // Check if cart is empty now
                    if (result.cart_data.item_count === 0) {
                        location.reload(); // Reload to show empty cart state
                    }
                    
                } else {
                    throw new Error(result.message || 'Error al eliminar producto');
                }
                
            } catch (error) {
                console.error('Item removal error:', error);
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('Error al eliminar producto', 'error');
                }
                
            } finally {
                LoadingManager.hide();
            }
        },
        
        saveForLater: async function(itemId) {
            LoadingManager.show('Guardando en favoritos...');
            
            try {
                const response = await fetch('/cart/save-for-later', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        item_id: itemId,
                        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Remove item from cart
                    const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                    if (itemElement) {
                        itemElement.remove();
                    }
                    
                    // Update cart summary
                    SelectionManager.updateCartSummary(result.cart_data);
                    
                    // Update header cart counter
                    if (window.updateCartCounter) {
                        window.updateCartCounter(result.cart_data.item_count);
                    }
                    
                    if (window.AgroConectaUtils) {
                        window.AgroConectaUtils.showToast('Producto guardado en favoritos', 'success');
                    }
                    
                    // Check if cart is empty now
                    if (result.cart_data.item_count === 0) {
                        location.reload(); // Reload to show empty cart state
                    }
                    
                } else {
                    throw new Error(result.message || 'Error al guardar producto');
                }
                
            } catch (error) {
                console.error('Save for later error:', error);
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('Error al guardar producto', 'error');
                }
                
            } finally {
                LoadingManager.hide();
            }
        }
    };
    
    // === GESTIÓN DE CARGA ===
    const LoadingManager = {
        show: function(message = 'Cargando...') {
            const loading = document.getElementById('cartLoading');
            const loadingMessage = document.getElementById('loadingMessage');
            
            if (loading) {
                loading.style.display = 'flex';
            }
            
            if (loadingMessage) {
                loadingMessage.textContent = message;
            }
        },
        
        hide: function() {
            const loading = document.getElementById('cartLoading');
            if (loading) {
                loading.style.display = 'none';
            }
        }
    };
    
    // === RECOMENDACIONES ===
    const RecommendationsManager = {
        loadRecommendations: async function() {
            const recommendedGrid = document.querySelector('.recommended-grid');
            const popularGrid = document.querySelector('.popular-grid');
            
            if (!recommendedGrid && !popularGrid) return;
            
            try {
                const response = await fetch('/cart/recommendations', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (recommendedGrid && result.recommendations) {
                        this.renderRecommendations(recommendedGrid, result.recommendations);
                    }
                    
                    if (popularGrid && result.popular) {
                        this.renderRecommendations(popularGrid, result.popular);
                    }
                }
                
            } catch (error) {
                console.error('Recommendations loading error:', error);
                
                // Hide recommendations section on error
                if (recommendedGrid) {
                    recommendedGrid.innerHTML = '';
                }
                if (popularGrid) {
                    popularGrid.innerHTML = '';
                }
            }
        },
        
        renderRecommendations: function(container, products) {
            if (!products || products.length === 0) {
                container.innerHTML = '<p class="text-muted">No hay recomendaciones disponibles</p>';
                return;
            }
            
            const productsHTML = products.map(product => `
                <div class="recommended-product">
                    <div class="product-image">
                        ${product.imagen_principal 
                            ? `<img src="/uploads/products/${product.imagen_principal}" alt="${product.nombre}">`
                            : `<div class="image-placeholder"><i class="fas fa-image"></i></div>`
                        }
                    </div>
                    <div class="product-info">
                        <h5><a href="/products/${product.id}">${product.nombre}</a></h5>
                        <p class="price">$${product.precio.toLocaleString('es-MX', { minimumFractionDigits: 2 })}</p>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addToCart(${product.id})">
                            <i class="fas fa-cart-plus"></i> Agregar
                        </button>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = productsHTML;
        }
    };
    
    // === FUNCIONES GLOBALES EXPUESTAS ===
    window.updateQuantity = function(itemId, action, value) {
        QuantityManager.updateQuantity(itemId, action, value);
    };
    
    window.removeItem = function(itemId) {
        ItemManager.removeItem(itemId);
    };
    
    window.saveForLater = function(itemId) {
        ItemManager.saveForLater(itemId);
    };
    
    window.applyCoupon = function() {
        CouponManager.applyCoupon();
    };
    
    window.removeCoupon = function() {
        CouponManager.removeCoupon();
    };
    
    window.calculateShipping = function() {
        ShippingCalculator.calculateShipping();
    };
    
    window.toggleNewAddress = function() {
        ShippingCalculator.toggleNewAddress();
    };
    
    window.clearCart = async function() {
        if (!confirm('¿Estás seguro de que quieres vaciar todo el carrito?')) {
            return;
        }
        
        LoadingManager.show('Vaciando carrito...');
        
        try {
            const response = await fetch('/cart/clear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                location.reload(); // Reload to show empty cart state
            } else {
                throw new Error(result.message || 'Error al vaciar carrito');
            }
            
        } catch (error) {
            console.error('Clear cart error:', error);
            if (window.AgroConectaUtils) {
                window.AgroConectaUtils.showToast('Error al vaciar carrito', 'error');
            }
        } finally {
            LoadingManager.hide();
        }
    };
    
    // === INICIALIZACIÓN ===
    try {
        SelectionManager.init();
        
        // Load recommendations after a short delay
        setTimeout(() => {
            RecommendationsManager.loadRecommendations();
        }, 1000);
        
        // Save original quantity values for potential revert
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.dataset.originalValue = input.value;
        });
        
        console.log('Cart page initialized successfully');
    } catch (error) {
        console.error('Cart page initialization error:', error);
    }
});

// === CSS ADICIONAL PARA COMPONENTES ===
const additionalStyles = `
.shopping-cart {
    min-height: calc(100vh - 200px);
    background-color: #f8f9fa;
}

.cart-header {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
    padding: 2rem 0;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    margin-top: 2rem;
}

.cart-items {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.cart-items-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.bulk-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.cart-item {
    display: grid;
    grid-template-columns: auto 80px 1fr auto;
    gap: 1rem;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.cart-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.item-select {
    display: flex;
    align-items: center;
    justify-content: center;
}

.item-image {
    position: relative;
    width: 80px;
    height: 80px;
}

.item-image img,
.image-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 8px;
    object-fit: cover;
}

.image-placeholder {
    background: #f1f3f4;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9aa0a6;
}

.discount-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.item-details {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.item-title {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
}

.item-title a {
    color: #2c3e50;
    text-decoration: none;
}

.item-title a:hover {
    color: #27ae60;
}

.item-description {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 0 0 1rem 0;
}

.item-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

.vendor-info,
.item-category {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.vendor-link {
    color: #27ae60;
    text-decoration: none;
}

.vendor-rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-left: 0.5rem;
}

.vendor-rating .stars {
    display: flex;
    gap: 1px;
}

.vendor-rating .fa-star {
    color: #ddd;
    font-size: 0.8rem;
}

.vendor-rating .fa-star.filled {
    color: #ffc107;
}

.organic-badge {
    background: #d4edda;
    color: #155724;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.stock-info {
    margin-top: 0.5rem;
}

.low-stock-warning {
    color: #856404;
    background: #fff3cd;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
}

.out-of-stock-warning {
    color: #721c24;
    background: #f8d7da;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
}

.stock-available {
    color: #155724;
    background: #d4edda;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
}

.item-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.quantity-control {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.quantity-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: #495057;
}

.quantity-input-group {
    display: flex;
    align-items: center;
    border: 1px solid #ced4da;
    border-radius: 6px;
    overflow: hidden;
}

.btn-quantity-decrease,
.btn-quantity-increase {
    background: #f8f9fa;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-quantity-decrease:hover,
.btn-quantity-increase:hover {
    background: #e9ecef;
}

.btn-quantity-decrease:disabled,
.btn-quantity-increase:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.quantity-input {
    border: none;
    text-align: center;
    width: 60px;
    padding: 0.5rem;
    font-weight: 500;
}

.unit-info {
    font-size: 0.8rem;
    color: #6c757d;
}

.item-extra-actions {
    display: flex;
    gap: 0.5rem;
}

.item-pricing {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: space-between;
    min-width: 120px;
}

.price-section {
    text-align: right;
}

.original-price {
    text-decoration: line-through;
    color: #6c757d;
    font-size: 0.9rem;
}

.current-price {
    font-size: 1.2rem;
    font-weight: 600;
    color: #27ae60;
    margin: 0.25rem 0;
}

.unit-price {
    font-size: 0.8rem;
    color: #6c757d;
}

.subtotal-section {
    text-align: right;
    margin-top: 1rem;
}

.subtotal-label {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.subtotal-amount {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c3e50;
}

.cart-summary {
    position: sticky;
    top: 2rem;
    height: fit-content;
}

.summary-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.summary-header {
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.summary-header h3 {
    margin: 0;
    font-size: 1.2rem;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.summary-content {
    padding: 1.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
}

.summary-row:not(:last-child) {
    border-bottom: 1px solid #f8f9fa;
}

.summary-row.discount .label {
    color: #28a745;
}

.summary-row.discount .amount {
    color: #28a745;
    font-weight: 600;
}

.summary-row.total {
    font-size: 1.2rem;
    font-weight: 700;
    border-top: 2px solid #e9ecef;
    margin-top: 1rem;
    padding-top: 1rem;
}

.coupon-section {
    margin: 1rem 0;
    padding: 1rem 0;
    border-top: 1px solid #f8f9fa;
    border-bottom: 1px solid #f8f9fa;
}

.coupon-input-group {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.coupon-applied {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #28a745;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.btn-remove-coupon {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 0.25rem;
    margin-left: auto;
}

.shipping-section {
    margin: 1rem 0;
}

.shipping-calculator {
    margin-top: 0.5rem;
}

.shipping-form {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
    margin-top: 0.5rem;
}

.address-form .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.free-shipping-progress {
    margin-top: 1rem;
}

.progress-info {
    margin-bottom: 0.5rem;
}

.summary-actions {
    padding: 1.5rem;
    background: #f8f9fa;
}

.btn-block {
    width: 100%;
}

.guest-checkout-option {
    margin-top: 1rem;
}

.security-badges {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
    margin-top: 1rem;
}

.security-item {
    text-align: center;
    padding: 0.5rem;
    background: white;
    border-radius: 6px;
    font-size: 0.8rem;
}

.security-item i {
    display: block;
    margin-bottom: 0.25rem;
    color: #27ae60;
}

.additional-info {
    margin-top: 1rem;
}

.payment-methods,
.customer-service {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.payment-methods h4,
.customer-service h4 {
    font-size: 1rem;
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.payment-icons {
    display: flex;
    gap: 0.5rem;
    font-size: 1.5rem;
}

.payment-icons i {
    color: #6c757d;
}

.customer-service p {
    margin: 0.25rem 0;
    font-size: 0.9rem;
}

.customer-service a {
    color: #27ae60;
    text-decoration: none;
}

.empty-cart {
    text-align: center;
    padding: 4rem 0;
}

.empty-icon i {
    font-size: 4rem;
    color: #9aa0a6;
    margin-bottom: 1rem;
}

.empty-cart h2 {
    margin-bottom: 1rem;
    color: #2c3e50;
}

.empty-cart p {
    color: #6c757d;
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.empty-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 3rem;
}

.popular-products,
.recommended-products {
    margin-top: 2rem;
}

.popular-products h3,
.recommended-products h3 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.recommended-grid,
.popular-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.recommended-product {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    text-align: center;
}

.recommended-product .product-image {
    width: 60px;
    height: 60px;
    margin: 0 auto 0.5rem;
}

.recommended-product .product-image img,
.recommended-product .image-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 6px;
    object-fit: cover;
}

.recommended-product h5 {
    font-size: 0.9rem;
    margin: 0 0 0.5rem 0;
}

.recommended-product h5 a {
    color: #2c3e50;
    text-decoration: none;
}

.recommended-product .price {
    color: #27ae60;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .cart-item {
        grid-template-columns: auto 60px 1fr;
        grid-template-rows: auto auto;
    }
    
    .item-pricing {
        grid-column: 1 / -1;
        align-items: flex-start;
        margin-top: 1rem;
    }
    
    .header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .cart-items-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .address-form .form-grid {
        grid-template-columns: 1fr;
    }
    
    .security-badges {
        grid-template-columns: 1fr;
    }
    
    .empty-actions {
        flex-direction: column;
        align-items: center;
    }
}
`;

// Inject additional styles
if (document.head) {
    const styleSheet = document.createElement('style');
    styleSheet.textContent = additionalStyles;
    document.head.appendChild(styleSheet);
}