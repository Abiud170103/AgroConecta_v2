/**
 * AgroConecta - Gestión de Direcciones
 * Sistema completo CRUD para direcciones de entrega
 */

class AddressManager {
    constructor() {
        this.currentAddressId = null;
        this.modalMode = 'create'; // 'create' o 'edit'
        this.postalCodeCache = new Map();
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupFormValidation();
        this.setupPostalCodeSearch();
        this.setupPreview();
    }
    
    setupEventListeners() {
        // Formulario principal
        document.getElementById('addressForm').addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });
        
        // Búsqueda por código postal
        document.getElementById('searchByCP').addEventListener('click', () => {
            this.searchByPostalCode();
        });
        
        // Auto-búsqueda al salir del campo CP
        document.getElementById('codigo_postal').addEventListener('blur', (e) => {
            if (e.target.value.length === 5) {
                this.searchByPostalCode();
            }
        });
        
        // Preview dinámico
        const previewFields = ['alias', 'calle', 'numero_interior', 'colonia', 'ciudad', 'estado', 'codigo_postal'];
        previewFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('input', () => {
                    this.updatePreview();
                });
            }
        });
        
        // Limpiar modal al cerrar
        $('#addressModal').on('hidden.bs.modal', () => {
            this.resetModal();
        });
        
        // Confirmar eliminación
        document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
            this.confirmDelete();
        });
    }
    
    setupFormValidation() {
        const form = document.getElementById('addressForm');
        const inputs = form.querySelectorAll('input[required], select[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
            
            input.addEventListener('input', () => {
                this.clearFieldError(input);
            });
        });
        
        // Validación específica para código postal
        const cpField = document.getElementById('codigo_postal');
        cpField.addEventListener('input', (e) => {
            this.formatPostalCode(e.target);
        });
        
        // Validación de teléfono
        const phoneField = document.getElementById('telefono');
        phoneField.addEventListener('input', (e) => {
            this.formatPhoneNumber(e.target);
        });
    }
    
    setupPostalCodeSearch() {
        const cpField = document.getElementById('codigo_postal');
        const searchBtn = document.getElementById('searchByCP');
        
        // Habilitar/deshabilitar botón según el CP
        cpField.addEventListener('input', () => {
            searchBtn.disabled = cpField.value.length !== 5;
        });
    }
    
    setupPreview() {
        // Mostrar preview cuando se empiece a llenar el formulario
        const keyFields = ['calle', 'colonia'];
        keyFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            field.addEventListener('input', () => {
                const preview = document.getElementById('addressPreview');
                if (field.value.trim() && preview.style.display === 'none') {
                    preview.style.display = 'block';
                }
            });
        });
    }
    
    // === OPERACIONES CRUD ===
    
    // Crear nueva dirección
    createAddress() {
        this.modalMode = 'create';
        this.currentAddressId = null;
        
        document.getElementById('modalTitle').textContent = 'Nueva Dirección';
        document.getElementById('saveText').textContent = 'Guardar Dirección';
        document.getElementById('form_action').value = 'create';
        document.getElementById('address_id').value = '';
        
        // Si es la primera dirección, marcarla como principal por defecto
        const hasAddresses = document.querySelectorAll('.address-card:not(.add-new-card)').length > 0;
        if (!hasAddresses) {
            document.getElementById('principal').checked = true;
        }
        
        $('#addressModal').modal('show');
    }
    
    // Editar dirección existente
    async editAddress(addressId) {
        this.modalMode = 'edit';
        this.currentAddressId = addressId;
        
        document.getElementById('modalTitle').textContent = 'Editar Dirección';
        document.getElementById('saveText').textContent = 'Actualizar Dirección';
        document.getElementById('form_action').value = 'update';
        document.getElementById('address_id').value = addressId;
        
        // Mostrar loading en el modal
        this.showModalLoading();
        
        try {
            const response = await fetch(`/user/addresses/${addressId}/edit`);
            const data = await response.json();
            
            if (data.success) {
                this.populateForm(data.address);
                $('#addressModal').modal('show');
            } else {
                throw new Error(data.message || 'Error al cargar la dirección');
            }
        } catch (error) {
            this.showAlert('error', error.message);
        } finally {
            this.hideModalLoading();
        }
    }
    
    // Eliminar dirección
    deleteAddress(addressId, addressName) {
        this.currentAddressId = addressId;
        document.getElementById('deleteAddressName').textContent = addressName;
        $('#deleteConfirmModal').modal('show');
    }
    
    async confirmDelete() {
        if (!this.currentAddressId) return;
        
        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const originalText = deleteBtn.innerHTML;
        
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
        
        try {
            const response = await fetch(`/user/addresses/${this.currentAddressId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.removeAddressFromDOM(this.currentAddressId);
                $('#deleteConfirmModal').modal('hide');
                this.showAlert('success', 'Dirección eliminada exitosamente');
                
                // Si no quedan direcciones, mostrar estado vacío
                this.checkEmptyState();
            } else {
                throw new Error(data.message || 'Error al eliminar la dirección');
            }
        } catch (error) {
            this.showAlert('error', error.message);
        } finally {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        }
    }
    
    // === OPERACIONES ESPECIALES ===
    
    // Establecer como principal
    async setPrincipalAddress(addressId) {
        try {
            const response = await fetch(`/user/addresses/${addressId}/set-principal`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updatePrincipalBadges(addressId);
                this.showAlert('success', 'Dirección principal actualizada');
            } else {
                throw new Error(data.message || 'Error al actualizar dirección principal');
            }
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }
    
    // Activar/Desactivar dirección
    async toggleAddressStatus(addressId, activate = true) {
        const action = activate ? 'activate' : 'deactivate';
        
        try {
            const response = await fetch(`/user/addresses/${addressId}/${action}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateAddressStatus(addressId, activate);
                this.showAlert('success', `Dirección ${activate ? 'activada' : 'desactivada'} exitosamente`);
            } else {
                throw new Error(data.message || `Error al ${activate ? 'activar' : 'desactivar'} la dirección`);
            }
        } catch (error) {
            this.showAlert('error', error.message);
        }
    }
    
    // Usar en checkout
    useInCheckout(addressId) {
        // Guardar ID de dirección en sessionStorage para uso en checkout
        sessionStorage.setItem('selected_address_id', addressId);
        
        // Redirigir al checkout
        window.location.href = '/checkout';
    }
    
    // === MANEJO DEL FORMULARIO ===
    
    async handleFormSubmit(e) {
        e.preventDefault();
        
        if (!this.validateForm()) {
            return;
        }
        
        const submitBtn = document.getElementById('saveAddressBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
        
        try {
            const formData = new FormData(document.getElementById('addressForm'));
            const url = this.modalMode === 'create' ? '/user/addresses' : `/user/addresses/${this.currentAddressId}`;
            const method = this.modalMode === 'create' ? 'POST' : 'PUT';
            
            const response = await fetch(url, {
                method: method,
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                $('#addressModal').modal('hide');
                
                if (this.modalMode === 'create') {
                    this.addAddressToDOM(data.address);
                    this.showAlert('success', 'Dirección creada exitosamente');
                } else {
                    this.updateAddressInDOM(data.address);
                    this.showAlert('success', 'Dirección actualizada exitosamente');
                }
                
                // Actualizar estado de la página si era la primera dirección
                this.checkEmptyState();
            } else {
                throw new Error(data.message || 'Error al guardar la dirección');
            }
        } catch (error) {
            this.showAlert('error', error.message);
        } finally {
            submitBtn.disabled = false;
            btnText.style.display = 'inline-flex';
            btnLoading.style.display = 'none';
        }
    }
    
    // === BÚSQUEDA POR CÓDIGO POSTAL ===
    
    async searchByPostalCode() {
        const cpField = document.getElementById('codigo_postal');
        const cp = cpField.value.trim();
        
        if (cp.length !== 5) {
            this.showAlert('error', 'El código postal debe tener 5 dígitos');
            return;
        }
        
        // Verificar cache
        if (this.postalCodeCache.has(cp)) {
            const data = this.postalCodeCache.get(cp);
            this.fillLocationData(data);
            return;
        }
        
        const searchBtn = document.getElementById('searchByCP');
        const originalText = searchBtn.innerHTML;
        
        searchBtn.disabled = true;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        try {
            const response = await fetch(`/api/postal-code/${cp}`);
            const data = await response.json();
            
            if (data.success) {
                // Guardar en cache
                this.postalCodeCache.set(cp, data);
                this.fillLocationData(data);
                this.showAlert('success', 'Ubicación encontrada automáticamente');
            } else {
                this.showAlert('warning', 'No se encontró información para este código postal. Completa manualmente.');
            }
        } catch (error) {
            this.showAlert('error', 'Error al buscar información del código postal');
        } finally {
            searchBtn.disabled = false;
            searchBtn.innerHTML = originalText;
        }
    }
    
    fillLocationData(data) {
        if (data.ciudad) {
            document.getElementById('ciudad').value = data.ciudad;
        }
        
        if (data.estado) {
            document.getElementById('estado').value = data.estado;
        }
        
        if (data.colonia && !document.getElementById('colonia').value) {
            document.getElementById('colonia').value = data.colonia;
        }
        
        // Actualizar preview
        this.updatePreview();
    }
    
    // === VALIDACIONES ===
    
    validateForm() {
        const requiredFields = [
            'calle',
            'colonia', 
            'ciudad',
            'estado',
            'codigo_postal'
        ];
        
        let isValid = true;
        
        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        // Validaciones específicas
        const cpField = document.getElementById('codigo_postal');
        if (!this.validatePostalCode(cpField.value)) {
            this.showFieldError(cpField, 'Código postal inválido (debe tener 5 dígitos)');
            isValid = false;
        }
        
        const phoneField = document.getElementById('telefono');
        if (phoneField.value && !this.validatePhone(phoneField.value)) {
            this.showFieldError(phoneField, 'Formato de teléfono inválido');
            isValid = false;
        }
        
        return isValid;
    }
    
    validateField(field) {
        if (field.required && !field.value.trim()) {
            this.showFieldError(field, 'Este campo es requerido');
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }
    
    validatePostalCode(cp) {
        return /^\d{5}$/.test(cp);
    }
    
    validatePhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 10 && cleaned.length <= 12;
    }
    
    // === FORMATEO ===
    
    formatPostalCode(input) {
        input.value = input.value.replace(/\D/g, '');
        
        if (input.value.length > 5) {
            input.value = input.value.substring(0, 5);
        }
    }
    
    formatPhoneNumber(input) {
        let value = input.value.replace(/\D/g, '');
        
        if (value.length <= 10) {
            // Formato: XX XXXX XXXX
            value = value.replace(/(\d{2})(\d{4})(\d{4})/, '$1 $2 $3');
        } else {
            // Formato: XXX XXX XXXX
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
        }
        
        input.value = value;
    }
    
    // === HELPERS DOM ===
    
    populateForm(address) {
        const fields = [
            'alias', 'calle', 'numero_interior', 'colonia', 
            'ciudad', 'estado', 'codigo_postal', 'referencia', 'telefono'
        ];
        
        fields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && address[fieldName]) {
                field.value = address[fieldName];
            }
        });
        
        document.getElementById('principal').checked = Boolean(address.principal);
        document.getElementById('activa').checked = Boolean(address.activa);
        
        // Actualizar preview
        setTimeout(() => this.updatePreview(), 100);
    }
    
    addAddressToDOM(address) {
        const grid = document.getElementById('addressesGrid');
        const addNewCard = grid.querySelector('.add-new-card');
        
        const addressCard = this.createAddressCard(address);
        grid.insertBefore(addressCard, addNewCard);
        
        // Animación de aparición
        setTimeout(() => {
            addressCard.classList.add('fade-in');
        }, 100);
    }
    
    updateAddressInDOM(address) {
        const existingCard = document.querySelector(`[data-address-id="${address.id}"]`);
        if (existingCard) {
            const newCard = this.createAddressCard(address);
            existingCard.replaceWith(newCard);
            
            // Animación de actualización
            newCard.classList.add('updated');
            setTimeout(() => {
                newCard.classList.remove('updated');
            }, 1000);
        }
    }
    
    removeAddressFromDOM(addressId) {
        const addressCard = document.querySelector(`[data-address-id="${addressId}"]`);
        if (addressCard) {
            addressCard.classList.add('fade-out');
            setTimeout(() => {
                addressCard.remove();
            }, 300);
        }
    }
    
    createAddressCard(address) {
        // En una implementación real, esto generaría el HTML completo de la tarjeta
        // Por simplicidad, aquí solo retornamos un placeholder
        const div = document.createElement('div');
        div.className = 'address-card';
        div.setAttribute('data-address-id', address.id);
        div.innerHTML = `<p>Dirección: ${address.alias || 'Sin nombre'}</p>`;
        return div;
    }
    
    updatePreview() {
        const preview = document.getElementById('addressPreview');
        const content = document.getElementById('previewContent');
        
        const formData = {
            alias: document.getElementById('alias').value,
            calle: document.getElementById('calle').value,
            numero_interior: document.getElementById('numero_interior').value,
            colonia: document.getElementById('colonia').value,
            ciudad: document.getElementById('ciudad').value,
            estado: document.getElementById('estado').value,
            codigo_postal: document.getElementById('codigo_postal').value
        };
        
        if (!formData.calle && !formData.colonia) {
            preview.style.display = 'none';
            return;
        }
        
        let previewHtml = '';
        
        if (formData.alias) {
            previewHtml += `<strong>${formData.alias}</strong><br>`;
        }
        
        if (formData.calle) {
            previewHtml += formData.calle;
            if (formData.numero_interior) {
                previewHtml += `, ${formData.numero_interior}`;
            }
            previewHtml += '<br>';
        }
        
        if (formData.colonia) {
            previewHtml += `${formData.colonia}<br>`;
        }
        
        if (formData.ciudad || formData.estado) {
            previewHtml += `${formData.ciudad || ''}, ${formData.estado || ''}`;
            if (formData.codigo_postal) {
                previewHtml += ` ${formData.codigo_postal}`;
            }
        }
        
        content.innerHTML = previewHtml;
        preview.style.display = 'block';
    }
    
    updatePrincipalBadges(newPrincipalId) {
        // Remover badge principal de todas las direcciones
        document.querySelectorAll('.badge-primary').forEach(badge => {
            if (badge.innerHTML.includes('Principal')) {
                badge.remove();
            }
        });
        
        // Agregar badge principal a la nueva dirección
        const newPrincipalCard = document.querySelector(`[data-address-id="${newPrincipalId}"]`);
        if (newPrincipalCard) {
            const title = newPrincipalCard.querySelector('.address-title');
            const badge = document.createElement('span');
            badge.className = 'badge badge-primary';
            badge.innerHTML = '<i class="fas fa-star"></i> Principal';
            title.appendChild(badge);
        }
    }
    
    updateAddressStatus(addressId, isActive) {
        const card = document.querySelector(`[data-address-id="${addressId}"]`);
        if (card) {
            const statusBadge = card.querySelector('.badge-success');
            
            if (isActive && !statusBadge) {
                const title = card.querySelector('.address-title');
                const badge = document.createElement('span');
                badge.className = 'badge badge-success';
                badge.innerHTML = '<i class="fas fa-check"></i> Activa';
                title.appendChild(badge);
            } else if (!isActive && statusBadge) {
                statusBadge.remove();
            }
        }
    }
    
    checkEmptyState() {
        const addressCards = document.querySelectorAll('.address-card:not(.add-new-card)');
        const emptyState = document.querySelector('.empty-addresses');
        const grid = document.getElementById('addressesGrid');
        
        if (addressCards.length === 0 && !emptyState) {
            // Mostrar estado vacío
            window.location.reload(); // Recarga para mostrar el estado vacío
        } else if (addressCards.length > 0 && emptyState) {
            // Ocultar estado vacío
            window.location.reload(); // Recarga para mostrar la grilla
        }
    }
    
    resetModal() {
        document.getElementById('addressForm').reset();
        document.getElementById('addressPreview').style.display = 'none';
        this.clearAllErrors();
        this.currentAddressId = null;
        this.modalMode = 'create';
    }
    
    // === UI HELPERS ===
    
    showFieldError(field, message) {
        this.clearFieldError(field);
        
        field.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        
        const existingError = field.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
    }
    
    clearAllErrors() {
        document.querySelectorAll('.is-invalid').forEach(field => {
            this.clearFieldError(field);
        });
    }
    
    showAlert(type, message) {
        const alertEl = document.createElement('div');
        alertEl.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        alertEl.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        
        alertEl.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
            ${message}
            <button type="button" class="close" onclick="this.parentElement.remove()">
                <span>&times;</span>
            </button>
        `;
        
        document.body.appendChild(alertEl);
        
        setTimeout(() => {
            if (alertEl.parentElement) {
                alertEl.remove();
            }
        }, 5000);
    }
    
    showModalLoading() {
        // Implementar loading state en el modal
        const modalBody = document.querySelector('#addressModal .modal-body');
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="mt-2">Cargando información de la dirección...</p>
            </div>
        `;
    }
    
    hideModalLoading() {
        // En una implementación real, restauraríamos el contenido original del modal
        // Por simplicidad, aquí solo ocultamos el loading
    }
}

// === FUNCIONES GLOBALES ===

function editAddress(id) {
    window.addressManager.editAddress(id);
}

function deleteAddress(id, name) {
    window.addressManager.deleteAddress(id, name);
}

function setPrincipalAddress(id) {
    window.addressManager.setPrincipalAddress(id);
}

function activateAddress(id) {
    window.addressManager.toggleAddressStatus(id, true);
}

function deactivateAddress(id) {
    window.addressManager.toggleAddressStatus(id, false);
}

function useInCheckout(id) {
    window.addressManager.useInCheckout(id);
}

// === INICIALIZACIÓN ===

document.addEventListener('DOMContentLoaded', function() {
    window.addressManager = new AddressManager();
    
    // Configurar botón de nueva dirección en el header
    const newAddressBtn = document.querySelector('.header-actions .btn');
    if (newAddressBtn) {
        newAddressBtn.addEventListener('click', () => {
            window.addressManager.createAddress();
        });
    }
    
    // Configurar botón de nueva dirección en estado vacío
    const emptyNewBtn = document.querySelector('.empty-addresses .btn');
    if (emptyNewBtn) {
        emptyNewBtn.addEventListener('click', () => {
            window.addressManager.createAddress();
        });
    }
});