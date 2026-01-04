/**
 * Confirmación de Compra - JavaScript Avanzado
 * AgroConecta - Manejo avanzado del proceso de confirmación de compras
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Inicializar elementos del DOM
    const confirmOrderForm = document.getElementById('confirmOrderForm');
    const addressCards = document.querySelectorAll('.address-card');
    const paymentOptions = document.querySelectorAll('.payment-option');
    const notasTextarea = document.getElementById('notas_cliente');
    
    // Contador de caracteres para notas
    if (notasTextarea) {
        const maxChars = 500;
        const charCounter = document.createElement('div');
        charCounter.className = 'char-counter text-muted small mt-1';
        charCounter.textContent = `0/${maxChars} caracteres`;
        notasTextarea.parentNode.appendChild(charCounter);
        
        notasTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCounter.textContent = `${currentLength}/${maxChars} caracteres`;
            
            if (currentLength > maxChars * 0.9) {
                charCounter.classList.add('text-warning');
            } else {
                charCounter.classList.remove('text-warning');
            }
            
            if (currentLength >= maxChars) {
                charCounter.classList.add('text-danger');
                this.value = this.value.substring(0, maxChars);
            } else {
                charCounter.classList.remove('text-danger');
            }
        });
    }
    
    // Animaciones de entrada
    animateElements();
    
    // Manejo de selección de dirección con animaciones
    addressCards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.type !== 'radio') {
                selectAddress(this);
            }
        });
        
        // Hover effects
        card.addEventListener('mouseenter', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '';
            }
        });
    });
    
    // Manejo de selección de método de pago
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            selectPaymentMethod(this);
        });
        
        // Hover effects
        option.addEventListener('mouseenter', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            }
        });
        
        option.addEventListener('mouseleave', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '';
            }
        });
    });
    
    // Validación en tiempo real del formulario
    if (confirmOrderForm) {
        confirmOrderForm.addEventListener('submit', handleFormSubmission);
        
        // Validación de campos en tiempo real
        const requiredFields = confirmOrderForm.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', validateField);
            field.addEventListener('input', clearFieldError);
        });
    }
    
    // Auto-save temporal del formulario
    setupAutoSave();
    
    // Notification system
    window.showNotification = showNotification;
    
    // Verificar conectividad
    setupConnectivityCheck();
});

function selectAddress(addressCard) {
    // Remover selección anterior
    document.querySelectorAll('.address-card').forEach(card => {
        card.classList.remove('selected');
        card.style.transform = '';
        card.style.boxShadow = '';
    });
    
    // Agregar selección actual con animación
    addressCard.classList.add('selected');
    addressCard.style.transform = 'scale(1.02)';
    addressCard.style.boxShadow = '0 8px 25px rgba(92, 184, 92, 0.3)';
    
    // Marcar radio button
    const radioButton = addressCard.querySelector('input[type="radio"]');
    if (radioButton) {
        radioButton.checked = true;
        radioButton.dispatchEvent(new Event('change'));
    }
    
    // Animación de confirmación
    setTimeout(() => {
        addressCard.style.transform = 'scale(1)';
        addressCard.style.boxShadow = '0 5px 15px rgba(92, 184, 92, 0.2)';
    }, 150);
    
    showNotification('Dirección seleccionada', 'success', 2000);
}

function selectPaymentMethod(paymentOption) {
    // Remover selección anterior
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('selected');
        option.style.transform = '';
        option.style.boxShadow = '';
    });
    
    // Agregar selección actual
    paymentOption.classList.add('selected');
    paymentOption.style.transform = 'scale(1.05)';
    paymentOption.style.boxShadow = '0 8px 25px rgba(92, 184, 92, 0.3)';
    
    // Marcar radio button
    const radioButton = paymentOption.querySelector('input[type="radio"]');
    if (radioButton) {
        radioButton.checked = true;
        radioButton.dispatchEvent(new Event('change'));
    }
    
    // Animación de confirmación
    setTimeout(() => {
        paymentOption.style.transform = 'scale(1)';
        paymentOption.style.boxShadow = '0 5px 15px rgba(92, 184, 92, 0.2)';
    }, 150);
    
    const metodosTexto = {
        'mercado_pago': 'Mercado Pago',
        'transferencia': 'Transferencia Bancaria',
        'efectivo': 'Pago en Efectivo'
    };
    
    const metodo = radioButton ? radioButton.value : '';
    const textoMetodo = metodosTexto[metodo] || metodo;
    
    showNotification(`Método de pago: ${textoMetodo}`, 'success', 2000);
}

function handleFormSubmission(e) {
    e.preventDefault();
    
    // Validación completa
    if (!validateForm()) {
        return false;
    }
    
    // Mostrar loading
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
    
    // Preparar datos del formulario
    const formData = new FormData(e.target);
    
    // Enviar formulario con AJAX
    fetch('procesar-pedido.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('¡Pedido confirmado exitosamente!', 'success');
            
            // Animación de éxito
            document.body.style.transition = 'opacity 0.5s';
            document.body.style.opacity = '0.7';
            
            setTimeout(() => {
                window.location.href = data.redirect || 'pedido-confirmado.php?id=' + data.pedido_id;
            }, 1500);
        } else {
            throw new Error(data.message || 'Error al procesar el pedido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message || 'Error al procesar el pedido', 'error');
        
        // Restaurar botón
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
    
    return false;
}

function validateForm() {
    let isValid = true;
    
    // Validar dirección
    const direccionSelected = document.querySelector('input[name="direccion_entrega"]:checked');
    if (!direccionSelected) {
        showNotification('Por favor selecciona una dirección de entrega', 'error');
        highlightRequiredSection('Dirección de entrega');
        isValid = false;
    }
    
    // Validar método de pago
    const metodoPagoSelected = document.querySelector('input[name="metodo_pago"]:checked');
    if (!metodoPagoSelected) {
        showNotification('Por favor selecciona un método de pago', 'error');
        highlightRequiredSection('Método de pago');
        isValid = false;
    }
    
    return isValid;
}

function validateField(e) {
    const field = e.target;
    clearFieldError(e);
    
    if (field.hasAttribute('required') && !field.value.trim()) {
        showFieldError(field, 'Este campo es obligatorio');
        return false;
    }
    
    return true;
}

function clearFieldError(e) {
    const field = e.target;
    const errorElement = field.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
    field.classList.remove('is-invalid');
}

function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error text-danger small mt-1';
    errorElement.textContent = message;
    
    field.parentNode.appendChild(errorElement);
}

function highlightRequiredSection(sectionName) {
    const sectionHeaders = document.querySelectorAll('.section-header h4');
    sectionHeaders.forEach(header => {
        if (header.textContent.includes(sectionName)) {
            const section = header.closest('.section-header').nextElementSibling;
            if (section) {
                section.style.border = '2px solid #dc3545';
                section.style.borderRadius = '10px';
                section.style.padding = '1rem';
                
                // Scroll to section
                section.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Remove highlight after 3 seconds
                setTimeout(() => {
                    section.style.border = '';
                    section.style.borderRadius = '';
                    section.style.padding = '';
                }, 3000);
            }
        }
    });
}

function animateElements() {
    // Animación de productos
    const productItems = document.querySelectorAll('.product-item');
    productItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-30px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.6s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 100);
    });
    
    // Animación de cards de dirección
    const addressCards = document.querySelectorAll('.address-card');
    addressCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, (index * 150) + 300);
    });
    
    // Animación de opciones de pago
    const paymentOptions = document.querySelectorAll('.payment-option');
    paymentOptions.forEach((option, index) => {
        option.style.opacity = '0';
        option.style.transform = 'scale(0.8)';
        
        setTimeout(() => {
            option.style.transition = 'all 0.6s ease';
            option.style.opacity = '1';
            option.style.transform = 'scale(1)';
        }, (index * 100) + 600);
    });
}

function setupAutoSave() {
    const notasTextarea = document.getElementById('notas_cliente');
    if (!notasTextarea) return;
    
    let autoSaveTimer;
    const AUTO_SAVE_KEY = 'agroconecta_order_notes';
    
    // Cargar notas guardadas
    const savedNotes = localStorage.getItem(AUTO_SAVE_KEY);
    if (savedNotes && !notasTextarea.value) {
        notasTextarea.value = savedNotes;
    }
    
    // Auto-save mientras escribe
    notasTextarea.addEventListener('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            localStorage.setItem(AUTO_SAVE_KEY, this.value);
        }, 1000);
    });
    
    // Limpiar al enviar formulario exitosamente
    window.addEventListener('beforeunload', function() {
        localStorage.removeItem(AUTO_SAVE_KEY);
    });
}

function setupConnectivityCheck() {
    let isOnline = navigator.onLine;
    
    window.addEventListener('online', function() {
        if (!isOnline) {
            isOnline = true;
            showNotification('Conexión restaurada', 'success', 3000);
        }
    });
    
    window.addEventListener('offline', function() {
        isOnline = false;
        showNotification('Sin conexión a internet. Revisa tu conexión.', 'error', 5000);
    });
}

function showNotification(message, type = 'info', duration = 4000) {
    // Remover notificaciones anteriores
    const existingNotifications = document.querySelectorAll('.custom-notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `custom-notification alert alert-${getBootstrapAlertClass(type)} position-fixed`;
    notification.style.cssText = `
        top: 20px; 
        right: 20px; 
        z-index: 9999; 
        min-width: 350px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        border: none;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.4s ease;
    `;
    
    const icon = getNotificationIcon(type);
    notification.innerHTML = `
        <i class="${icon} me-2"></i>
        <span>${message}</span>
        <button type="button" class="btn-close ms-auto" onclick="this.parentNode.remove()"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-remove
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 400);
    }, duration);
}

function getBootstrapAlertClass(type) {
    const classes = {
        'success': 'success',
        'error': 'danger',
        'warning': 'warning',
        'info': 'info'
    };
    return classes[type] || 'info';
}

function getNotificationIcon(type) {
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-triangle',
        'warning': 'fas fa-exclamation-circle',
        'info': 'fas fa-info-circle'
    };
    return icons[type] || 'fas fa-info-circle';
}

// Utilidades globales
window.AgroConectaConfirmacion = {
    selectAddress,
    selectPaymentMethod,
    showNotification,
    validateForm
};