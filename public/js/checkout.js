/**
 * AgroConecta - Sistema de Checkout
 * Maneja todo el flujo de checkout multi-paso con validaciones y cálculos dinámicos
 */

class CheckoutManager {
    constructor() {
        this.currentStep = 1;
        this.maxSteps = 4;
        this.formData = {};
        this.shippingCosts = {};
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadShippingCosts();
        this.setupFormValidation();
        this.calculateTotals();
        
        // Auto-save form data
        setInterval(() => this.saveFormProgress(), 30000); // Cada 30 segundos
    }
    
    setupEventListeners() {
        // Formulario principal
        document.getElementById('checkoutForm').addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });
        
        // Checkbox de crear cuenta
        const createAccountCheckbox = document.getElementById('create_account');
        if (createAccountCheckbox) {
            createAccountCheckbox.addEventListener('change', (e) => {
                this.togglePasswordSection(e.target.checked);
            });
        }
        
        // Validador de contraseña en tiempo real
        const passwordField = document.getElementById('guest_password');
        if (passwordField) {
            passwordField.addEventListener('input', (e) => {
                this.validatePasswordStrength(e.target.value);
            });
        }
        
        // Campos de dirección con autocompletado por CP
        const cpField = document.getElementById('direccion_cp');
        if (cpField) {
            cpField.addEventListener('blur', (e) => {
                this.autoFillByPostalCode(e.target.value);
            });
        }
        
        // Campos de tarjeta con formato automático
        this.setupCreditCardFormatting();
        
        // Listeners para cambio de direcciones y métodos de pago
        this.setupAddressListeners();
        this.setupPaymentMethodListeners();
        
        // Escape key para cerrar modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
    }
    
    setupCreditCardFormatting() {
        const cardNumberField = document.getElementById('card_number');
        const cardExpiryField = document.getElementById('card_expiry');
        const cardCvvField = document.getElementById('card_cvv');
        
        if (cardNumberField) {
            cardNumberField.addEventListener('input', (e) => {
                this.formatCardNumber(e.target);
            });
        }
        
        if (cardExpiryField) {
            cardExpiryField.addEventListener('input', (e) => {
                this.formatCardExpiry(e.target);
            });
        }
        
        if (cardCvvField) {
            cardCvvField.addEventListener('input', (e) => {
                this.formatCardCVV(e.target);
            });
        }
    }
    
    setupAddressListeners() {
        const addressRadios = document.querySelectorAll('input[name="delivery_address"]');
        addressRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.handleAddressChange(e.target.value);
            });
        });
    }
    
    setupPaymentMethodListeners() {
        const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.handlePaymentMethodChange(e.target.value);
            });
        });
    }
    
    // === NAVEGACIÓN ENTRE PASOS ===
    
    nextStep(targetStep) {
        if (!this.validateCurrentStep()) {
            return;
        }
        
        this.updateProgressStep(targetStep);
        this.currentStep = targetStep;
        this.showStep(targetStep);
        
        // Scroll al inicio del contenido
        document.querySelector('.checkout-content').scrollIntoView({ 
            behavior: 'smooth' 
        });
    }
    
    previousStep(targetStep) {
        this.updateProgressStep(targetStep);
        this.currentStep = targetStep;
        this.showStep(targetStep);
        
        document.querySelector('.checkout-content').scrollIntoView({ 
            behavior: 'smooth' 
        });
    }
    
    showStep(stepNumber) {
        // Ocultar todos los pasos
        document.querySelectorAll('.checkout-step').forEach(step => {
            step.classList.remove('active');
        });
        
        // Mostrar el paso actual
        const currentStep = document.querySelector(`[data-step="${stepNumber}"]`);
        if (currentStep) {
            currentStep.classList.add('active');
        }
        
        // Actualizar resumen si es el paso final
        if (stepNumber === 4) {
            this.updateOrderReview();
        }
    }
    
    updateProgressStep(stepNumber) {
        document.querySelectorAll('.progress-step').forEach(step => {
            step.classList.remove('active', 'completed');
            
            const stepNum = parseInt(step.dataset.step);
            if (stepNum < stepNumber) {
                step.classList.add('completed');
            } else if (stepNum === stepNumber) {
                step.classList.add('active');
            }
        });
    }
    
    // === VALIDACIONES ===
    
    validateCurrentStep() {
        switch(this.currentStep) {
            case 1:
                return this.validatePersonalInfo();
            case 2:
                return this.validateDeliveryInfo();
            case 3:
                return this.validatePaymentInfo();
            case 4:
                return true; // Solo requiere checkbox de términos
            default:
                return false;
        }
    }
    
    validatePersonalInfo() {
        const isGuest = !document.querySelector('input[name="user_id"]');
        
        if (isGuest) {
            const requiredFields = ['guest_nombre', 'guest_apellido', 'guest_email', 'guest_telefono'];
            
            for (const fieldName of requiredFields) {
                const field = document.getElementById(fieldName);
                if (!field || !field.value.trim()) {
                    this.showFieldError(field, 'Este campo es requerido');
                    return false;
                }
            }
            
            // Validar email
            const emailField = document.getElementById('guest_email');
            if (!this.validateEmail(emailField.value)) {
                this.showFieldError(emailField, 'Por favor ingresa un email válido');
                return false;
            }
            
            // Validar teléfono
            const telefonoField = document.getElementById('guest_telefono');
            if (!this.validatePhone(telefonoField.value)) {
                this.showFieldError(telefonoField, 'Por favor ingresa un teléfono válido');
                return false;
            }
            
            // Si quiere crear cuenta, validar contraseña
            const createAccountCheckbox = document.getElementById('create_account');
            if (createAccountCheckbox && createAccountCheckbox.checked) {
                const passwordField = document.getElementById('guest_password');
                if (!passwordField || !this.validatePasswordStrength(passwordField.value, true)) {
                    this.showFieldError(passwordField, 'La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números');
                    return false;
                }
            }
        }
        
        this.clearStepErrors();
        return true;
    }
    
    validateDeliveryInfo() {
        const selectedAddress = document.querySelector('input[name="delivery_address"]:checked');
        
        if (!selectedAddress) {
            this.showStepError('Por favor selecciona o proporciona una dirección de entrega');
            return false;
        }
        
        // Si es nueva dirección, validar campos
        if (selectedAddress.value === 'new') {
            const requiredFields = [
                'direccion_calle',
                'direccion_colonia', 
                'direccion_ciudad',
                'direccion_estado',
                'direccion_cp'
            ];
            
            for (const fieldName of requiredFields) {
                const field = document.getElementById(fieldName);
                if (!field || !field.value.trim()) {
                    this.showFieldError(field, 'Este campo es requerido');
                    return false;
                }
            }
            
            // Validar código postal
            const cpField = document.getElementById('direccion_cp');
            if (!this.validatePostalCode(cpField.value)) {
                this.showFieldError(cpField, 'Por favor ingresa un código postal válido (5 dígitos)');
                return false;
            }
        }
        
        // Validar método de entrega
        const selectedMethod = document.querySelector('input[name="delivery_method"]:checked');
        if (!selectedMethod) {
            this.showStepError('Por favor selecciona un método de entrega');
            return false;
        }
        
        this.clearStepErrors();
        return true;
    }
    
    validatePaymentInfo() {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        
        if (!selectedPayment) {
            this.showStepError('Por favor selecciona un método de pago');
            return false;
        }
        
        // Si es tarjeta nueva, validar campos
        if (selectedPayment.value === 'card') {
            const requiredFields = ['card_number', 'card_expiry', 'card_cvv', 'card_name'];
            
            for (const fieldName of requiredFields) {
                const field = document.getElementById(fieldName);
                if (!field || !field.value.trim()) {
                    this.showFieldError(field, 'Este campo es requerido');
                    return false;
                }
            }
            
            // Validaciones específicas de tarjeta
            if (!this.validateCreditCard(document.getElementById('card_number').value)) {
                this.showFieldError(document.getElementById('card_number'), 'Número de tarjeta inválido');
                return false;
            }
            
            if (!this.validateCardExpiry(document.getElementById('card_expiry').value)) {
                this.showFieldError(document.getElementById('card_expiry'), 'Fecha de expiración inválida');
                return false;
            }
            
            if (!this.validateCVV(document.getElementById('card_cvv').value)) {
                this.showFieldError(document.getElementById('card_cvv'), 'CVV inválido');
                return false;
            }
        }
        
        this.clearStepErrors();
        return true;
    }
    
    // === HELPERS DE VALIDACIÓN ===
    
    validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    validatePhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 10 && cleaned.length <= 12;
    }
    
    validatePostalCode(cp) {
        const regex = /^\d{5}$/;
        return regex.test(cp);
    }
    
    validateCreditCard(cardNumber) {
        const cleaned = cardNumber.replace(/\s/g, '');
        
        if (!/^\d+$/.test(cleaned) || cleaned.length < 13 || cleaned.length > 19) {
            return false;
        }
        
        // Algoritmo de Luhn
        let sum = 0;
        let alternate = false;
        
        for (let i = cleaned.length - 1; i >= 0; i--) {
            let n = parseInt(cleaned.charAt(i), 10);
            
            if (alternate) {
                n *= 2;
                if (n > 9) {
                    n = (n % 10) + 1;
                }
            }
            
            sum += n;
            alternate = !alternate;
        }
        
        return (sum % 10) === 0;
    }
    
    validateCardExpiry(expiry) {
        const regex = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;
        if (!regex.test(expiry)) return false;
        
        const [month, year] = expiry.split('/');
        const expiryDate = new Date(2000 + parseInt(year), parseInt(month) - 1);
        const today = new Date();
        
        return expiryDate > today;
    }
    
    validateCVV(cvv) {
        return /^[0-9]{3,4}$/.test(cvv);
    }
    
    validatePasswordStrength(password, returnBool = false) {
        const requirements = {
            minLength: password.length >= 8,
            hasUpper: /[A-Z]/.test(password),
            hasLower: /[a-z]/.test(password),
            hasNumber: /\d/.test(password),
            hasSpecial: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };
        
        const score = Object.values(requirements).filter(Boolean).length;
        let strength = '';
        let strengthClass = '';
        
        if (score < 2) {
            strength = 'Muy débil';
            strengthClass = 'very-weak';
        } else if (score < 3) {
            strength = 'Débil';
            strengthClass = 'weak';
        } else if (score < 4) {
            strength = 'Regular';
            strengthClass = 'fair';
        } else if (score < 5) {
            strength = 'Fuerte';
            strengthClass = 'strong';
        } else {
            strength = 'Muy fuerte';
            strengthClass = 'very-strong';
        }
        
        // Actualizar UI
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('passwordStrengthText');
        
        if (strengthBar && strengthText) {
            strengthBar.className = `strength-bar ${strengthClass}`;
            strengthBar.style.width = `${(score / 5) * 100}%`;
            strengthText.textContent = strength;
            strengthText.className = `strength-text ${strengthClass}`;
        }
        
        return returnBool ? score >= 3 : true;
    }
    
    // === FORMATEO DE CAMPOS ===
    
    formatCardNumber(input) {
        let value = input.value.replace(/\s/g, '').replace(/\D/g, '');
        let formattedValue = '';
        
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formattedValue += ' ';
            }
            formattedValue += value[i];
        }
        
        input.value = formattedValue;
        
        // Detectar tipo de tarjeta
        this.detectCardType(value);
    }
    
    formatCardExpiry(input) {
        let value = input.value.replace(/\D/g, '');
        
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        
        input.value = value;
    }
    
    formatCardCVV(input) {
        input.value = input.value.replace(/\D/g, '');
    }
    
    detectCardType(cardNumber) {
        const cardIcons = document.querySelectorAll('.card-icons i');
        cardIcons.forEach(icon => icon.classList.remove('active'));
        
        let cardType = '';
        
        if (/^4/.test(cardNumber)) {
            cardType = 'visa';
        } else if (/^5[1-5]/.test(cardNumber) || /^2(22[1-9]|2[3-9]\d|[3-6]\d{2}|7[01]\d|720)/.test(cardNumber)) {
            cardType = 'mastercard';
        } else if (/^3[47]/.test(cardNumber)) {
            cardType = 'amex';
        }
        
        if (cardType) {
            const activeIcon = document.querySelector(`.fab.fa-cc-${cardType}`);
            if (activeIcon) {
                activeIcon.classList.add('active');
            }
        }
    }
    
    // === FUNCIONALIDADES ESPECÍFICAS ===
    
    togglePasswordSection(show) {
        const passwordSection = document.getElementById('passwordSection');
        const passwordField = document.getElementById('guest_password');
        
        if (passwordSection) {
            passwordSection.style.display = show ? 'block' : 'none';
            
            if (passwordField) {
                passwordField.required = show;
                if (!show) {
                    passwordField.value = '';
                }
            }
        }
    }
    
    async autoFillByPostalCode(cp) {
        if (!this.validatePostalCode(cp)) {
            return;
        }
        
        try {
            const response = await fetch(`/api/postal-code/${cp}`);
            const data = await response.json();
            
            if (data.success) {
                const ciudadField = document.getElementById('direccion_ciudad');
                const estadoField = document.getElementById('direccion_estado');
                
                if (ciudadField && data.ciudad) {
                    ciudadField.value = data.ciudad;
                }
                
                if (estadoField && data.estado) {
                    estadoField.value = data.estado;
                }
                
                // Recalcular costos de envío
                this.calculateShippingCosts();
            }
        } catch (error) {
            console.error('Error al obtener información del código postal:', error);
        }
    }
    
    handleAddressChange(value) {
        const newAddressForm = document.getElementById('newAddressForm');
        
        if (value === 'new') {
            newAddressForm.style.display = 'block';
            this.makeNewAddressFieldsRequired(true);
        } else {
            newAddressForm.style.display = 'none';
            this.makeNewAddressFieldsRequired(false);
        }
        
        this.calculateShippingCosts();
    }
    
    makeNewAddressFieldsRequired(required) {
        const fields = [
            'direccion_calle',
            'direccion_colonia',
            'direccion_ciudad', 
            'direccion_estado',
            'direccion_cp'
        ];
        
        fields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.required = required;
            }
        });
    }
    
    handlePaymentMethodChange(value) {
        // Ocultar todos los formularios
        document.querySelectorAll('.payment-form').forEach(form => {
            form.style.display = 'none';
        });
        
        // Mostrar el formulario correspondiente
        let formId = '';
        
        if (value === 'card') {
            formId = 'cardPaymentForm';
        } else if (value === 'paypal') {
            formId = 'paypalPaymentForm';
        } else if (value === 'bank_transfer') {
            formId = 'transferPaymentForm';
        } else if (value === 'cash') {
            formId = 'cashPaymentForm';
        }
        
        if (formId) {
            const form = document.getElementById(formId);
            if (form) {
                form.style.display = 'block';
            }
        }
        
        // Actualizar campos requeridos
        this.updateRequiredPaymentFields(value);
    }
    
    updateRequiredPaymentFields(paymentMethod) {
        // Limpiar todos los campos required de pago
        document.querySelectorAll('.payment-form input').forEach(input => {
            input.required = false;
        });
        
        // Hacer required los campos necesarios
        if (paymentMethod === 'card') {
            const requiredFields = ['card_number', 'card_expiry', 'card_cvv', 'card_name'];
            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field) {
                    field.required = true;
                }
            });
        }
    }
    
    // === CÁLCULOS Y COSTOS ===
    
    async loadShippingCosts() {
        try {
            const response = await fetch('/api/shipping-costs');
            const data = await response.json();
            
            if (data.success) {
                this.shippingCosts = data.costs;
            }
        } catch (error) {
            console.error('Error al cargar costos de envío:', error);
        }
    }
    
    async calculateShippingCosts() {
        const selectedAddress = document.querySelector('input[name="delivery_address"]:checked');
        const selectedMethod = document.querySelector('input[name="delivery_method"]:checked');
        
        if (!selectedAddress || !selectedMethod) {
            return;
        }
        
        // Mostrar loading
        this.showLoadingOnShipping();
        
        try {
            const addressData = await this.getAddressData(selectedAddress.value);
            const methodData = selectedMethod.value;
            
            const response = await fetch('/api/calculate-shipping', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    address: addressData,
                    method: methodData,
                    items: this.getCartItems()
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateShippingPrices(data.costs);
                this.calculateTotals();
            }
        } catch (error) {
            console.error('Error al calcular envío:', error);
        } finally {
            this.hideLoadingOnShipping();
        }
    }
    
    async getAddressData(addressValue) {
        if (addressValue === 'new') {
            return {
                calle: document.getElementById('direccion_calle')?.value || '',
                colonia: document.getElementById('direccion_colonia')?.value || '',
                ciudad: document.getElementById('direccion_ciudad')?.value || '',
                estado: document.getElementById('direccion_estado')?.value || '',
                codigo_postal: document.getElementById('direccion_cp')?.value || ''
            };
        } else {
            // Obtener datos de dirección guardada
            try {
                const response = await fetch(`/api/addresses/${addressValue}`);
                const data = await response.json();
                return data.address || {};
            } catch (error) {
                console.error('Error al obtener dirección:', error);
                return {};
            }
        }
    }
    
    getCartItems() {
        // En una implementación real, esto vendría del servidor
        // Por ahora retornamos datos de ejemplo
        return [];
    }
    
    updateShippingPrices(costs) {
        const standardPriceEl = document.getElementById('standardPrice');
        const expressPriceEl = document.getElementById('expressPrice');
        
        if (standardPriceEl && costs.standard !== undefined) {
            standardPriceEl.textContent = costs.standard > 0 ? `$${costs.standard.toFixed(2)}` : 'GRATIS';
        }
        
        if (expressPriceEl && costs.express !== undefined) {
            expressPriceEl.textContent = `$${costs.express.toFixed(2)}`;
        }
    }
    
    calculateTotals() {
        const selectedMethod = document.querySelector('input[name="delivery_method"]:checked');
        let shippingCost = 0;
        
        if (selectedMethod) {
            const methodValue = selectedMethod.value;
            
            if (methodValue === 'standard') {
                const priceText = document.getElementById('standardPrice')?.textContent;
                shippingCost = this.extractPriceFromText(priceText);
            } else if (methodValue === 'express') {
                const priceText = document.getElementById('expressPrice')?.textContent;
                shippingCost = this.extractPriceFromText(priceText);
            }
            // pickup es gratis
        }
        
        // Actualizar totales en el resumen
        const orderShippingEl = document.getElementById('orderShipping');
        const orderTotalEl = document.getElementById('orderTotal');
        
        if (orderShippingEl) {
            orderShippingEl.textContent = shippingCost > 0 ? `$${shippingCost.toFixed(2)}` : 'GRATIS';
        }
        
        if (orderTotalEl) {
            const subtotal = this.extractPriceFromText(document.getElementById('orderSubtotal')?.textContent);
            const tax = this.extractPriceFromText(document.getElementById('orderTax')?.textContent);
            const discount = this.extractPriceFromText(document.querySelector('.calc-row.discount span:last-child')?.textContent) || 0;
            
            const total = subtotal + tax + shippingCost - discount;
            orderTotalEl.textContent = `$${total.toFixed(2)}`;
        }
    }
    
    extractPriceFromText(text) {
        if (!text) return 0;
        const match = text.match(/[\d,]+\.?\d*/);
        return match ? parseFloat(match[0].replace(',', '')) : 0;
    }
    
    // === RESUMEN FINAL ===
    
    updateOrderReview() {
        this.updatePersonalInfoReview();
        this.updateDeliveryInfoReview();
        this.updatePaymentInfoReview();
    }
    
    updatePersonalInfoReview() {
        const reviewEl = document.getElementById('personalInfoReview');
        if (!reviewEl) return;
        
        const isGuest = !document.querySelector('input[name="user_id"]');
        let reviewHtml = '';
        
        if (isGuest) {
            const nombre = document.getElementById('guest_nombre')?.value || '';
            const apellido = document.getElementById('guest_apellido')?.value || '';
            const email = document.getElementById('guest_email')?.value || '';
            const telefono = document.getElementById('guest_telefono')?.value || '';
            
            reviewHtml = `
                <p><strong>${nombre} ${apellido}</strong></p>
                <p>${email}</p>
                <p>${telefono}</p>
            `;
        } else {
            const userInfo = document.querySelector('.user-details');
            if (userInfo) {
                reviewHtml = userInfo.innerHTML;
            }
        }
        
        reviewEl.innerHTML = reviewHtml;
    }
    
    updateDeliveryInfoReview() {
        const reviewEl = document.getElementById('deliveryInfoReview');
        if (!reviewEl) return;
        
        const selectedAddress = document.querySelector('input[name="delivery_address"]:checked');
        const selectedMethod = document.querySelector('input[name="delivery_method"]:checked');
        
        let addressHtml = '';
        let methodHtml = '';
        
        if (selectedAddress) {
            if (selectedAddress.value === 'new') {
                const calle = document.getElementById('direccion_calle')?.value || '';
                const colonia = document.getElementById('direccion_colonia')?.value || '';
                const ciudad = document.getElementById('direccion_ciudad')?.value || '';
                const estado = document.getElementById('direccion_estado')?.value || '';
                const cp = document.getElementById('direccion_cp')?.value || '';
                
                addressHtml = `
                    <p>${calle}</p>
                    <p>${colonia}</p>
                    <p>${ciudad}, ${estado} ${cp}</p>
                `;
            } else {
                const addressLabel = document.querySelector(`label[for="address_${selectedAddress.value}"]`);
                if (addressLabel) {
                    addressHtml = addressLabel.querySelector('.address-details').innerHTML;
                }
            }
        }
        
        if (selectedMethod) {
            const methodLabel = document.querySelector(`label[for="delivery_${selectedMethod.value}"]`);
            if (methodLabel) {
                const methodInfo = methodLabel.querySelector('.method-info');
                methodHtml = `
                    <p><strong>${methodInfo.querySelector('strong').textContent}</strong></p>
                    <p>${methodInfo.querySelector('p').textContent}</p>
                    <p>${methodInfo.querySelector('.method-price').textContent}</p>
                `;
            }
        }
        
        reviewEl.innerHTML = `
            <div class="review-address">
                <h5>Dirección:</h5>
                ${addressHtml}
            </div>
            <div class="review-method">
                <h5>Método de Entrega:</h5>
                ${methodHtml}
            </div>
        `;
    }
    
    updatePaymentInfoReview() {
        const reviewEl = document.getElementById('paymentInfoReview');
        if (!reviewEl) return;
        
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        let paymentHtml = '';
        
        if (selectedPayment) {
            const value = selectedPayment.value;
            
            if (value.startsWith('saved_')) {
                const paymentLabel = document.querySelector(`label[for="payment_${value.replace('saved_', '')}"]`);
                if (paymentLabel) {
                    paymentHtml = paymentLabel.querySelector('.card-info').innerHTML;
                }
            } else {
                const paymentLabel = document.querySelector(`label[for="payment_${value}"]`);
                if (paymentLabel) {
                    const optionInfo = paymentLabel.querySelector('.option-info');
                    paymentHtml = `
                        <p><strong>${optionInfo.querySelector('strong').textContent}</strong></p>
                        <p>${optionInfo.querySelector('p').textContent}</p>
                    `;
                }
                
                // Si es tarjeta nueva, mostrar últimos dígitos
                if (value === 'card') {
                    const cardNumber = document.getElementById('card_number')?.value || '';
                    const cardName = document.getElementById('card_name')?.value || '';
                    const lastDigits = cardNumber.replace(/\s/g, '').slice(-4);
                    
                    if (lastDigits) {
                        paymentHtml += `<p>**** **** **** ${lastDigits}</p>`;
                    }
                    if (cardName) {
                        paymentHtml += `<p>${cardName}</p>`;
                    }
                }
            }
        }
        
        reviewEl.innerHTML = paymentHtml;
    }
    
    // === ENVÍO DEL FORMULARIO ===
    
    async handleFormSubmit(e) {
        e.preventDefault();
        
        // Validar términos y condiciones
        const acceptTerms = document.getElementById('accept_terms');
        if (!acceptTerms || !acceptTerms.checked) {
            this.showAlert('error', 'Debes aceptar los términos y condiciones para continuar');
            return;
        }
        
        // Deshabilitar botón y mostrar loading
        const submitBtn = document.getElementById('finalizeOrderBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
        
        // Mostrar overlay de carga
        this.showCheckoutLoading('Procesando tu pedido...');
        
        try {
            const formData = new FormData(document.getElementById('checkoutForm'));
            
            const response = await fetch('/checkout/process', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateCheckoutLoadingMessage('Pedido creado exitosamente. Redirigiendo...');
                
                // Limpiar datos guardados
                this.clearFormProgress();
                
                // Redirigir a confirmación
                setTimeout(() => {
                    window.location.href = `/checkout/success/${data.order_id}`;
                }, 2000);
            } else {
                throw new Error(data.message || 'Error al procesar el pedido');
            }
        } catch (error) {
            console.error('Error al enviar formulario:', error);
            this.showAlert('error', error.message || 'Error al procesar el pedido. Por favor intenta nuevamente.');
            
            // Restaurar botón
            submitBtn.disabled = false;
            btnText.style.display = 'inline-flex';
            btnLoading.style.display = 'none';
            
            this.hideCheckoutLoading();
        }
    }
    
    // === GESTIÓN DE PROGRESO ===
    
    saveFormProgress() {
        const formData = new FormData(document.getElementById('checkoutForm'));
        const progressData = {
            step: this.currentStep,
            data: Object.fromEntries(formData),
            timestamp: Date.now()
        };
        
        try {
            localStorage.setItem('checkout_progress', JSON.stringify(progressData));
        } catch (error) {
            console.error('Error al guardar progreso:', error);
        }
    }
    
    loadFormProgress() {
        try {
            const saved = localStorage.getItem('checkout_progress');
            if (saved) {
                const progressData = JSON.parse(saved);
                
                // Solo cargar si es reciente (menos de 1 hora)
                if (Date.now() - progressData.timestamp < 3600000) {
                    return progressData;
                }
            }
        } catch (error) {
            console.error('Error al cargar progreso:', error);
        }
        
        return null;
    }
    
    clearFormProgress() {
        try {
            localStorage.removeItem('checkout_progress');
        } catch (error) {
            console.error('Error al limpiar progreso:', error);
        }
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
    
    showStepError(message) {
        const currentStepEl = document.querySelector('.checkout-step.active');
        if (currentStepEl) {
            let errorEl = currentStepEl.querySelector('.step-error');
            
            if (!errorEl) {
                errorEl = document.createElement('div');
                errorEl.className = 'alert alert-danger step-error';
                currentStepEl.querySelector('.step-content').insertBefore(
                    errorEl, 
                    currentStepEl.querySelector('.step-actions')
                );
            }
            
            errorEl.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                ${message}
            `;
            
            errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    clearStepErrors() {
        document.querySelectorAll('.step-error').forEach(error => {
            error.remove();
        });
        
        document.querySelectorAll('.is-invalid').forEach(field => {
            this.clearFieldError(field);
        });
    }
    
    showAlert(type, message) {
        // Implementar sistema de alertas/toasts
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
        
        // Auto-remove después de 5 segundos
        setTimeout(() => {
            if (alertEl.parentElement) {
                alertEl.remove();
            }
        }, 5000);
    }
    
    showCheckoutLoading(message) {
        const loadingEl = document.getElementById('checkoutLoading');
        const messageEl = document.getElementById('checkoutLoadingMessage');
        
        if (loadingEl) {
            loadingEl.style.display = 'flex';
        }
        
        if (messageEl) {
            messageEl.textContent = message;
        }
    }
    
    updateCheckoutLoadingMessage(message) {
        const messageEl = document.getElementById('checkoutLoadingMessage');
        if (messageEl) {
            messageEl.textContent = message;
        }
    }
    
    hideCheckoutLoading() {
        const loadingEl = document.getElementById('checkoutLoading');
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
    }
    
    showLoadingOnShipping() {
        document.querySelectorAll('.method-price').forEach(price => {
            if (price.textContent !== 'GRATIS') {
                price.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            }
        });
    }
    
    hideLoadingOnShipping() {
        // Los precios se actualizarán con updateShippingPrices
    }
    
    closeAllModals() {
        // Para futuras implementaciones de modals
    }
}

// === FUNCIONES GLOBALES PARA LOS BOTONES ===

function nextStep(step) {
    if (window.checkoutManager) {
        window.checkoutManager.nextStep(step);
    }
}

function previousStep(step) {
    if (window.checkoutManager) {
        window.checkoutManager.previousStep(step);
    }
}

function selectAddress(type, id) {
    if (window.checkoutManager) {
        const value = type === 'saved' ? id : 'new';
        window.checkoutManager.handleAddressChange(value);
    }
}

function selectPaymentMethod(type, id) {
    if (window.checkoutManager) {
        const value = type === 'saved' ? `saved_${id}` : type;
        window.checkoutManager.handlePaymentMethodChange(value);
    }
}

function updateDeliveryMethod(method) {
    if (window.checkoutManager) {
        window.checkoutManager.calculateShippingCosts();
    }
}

// === INICIALIZACIÓN ===

document.addEventListener('DOMContentLoaded', function() {
    window.checkoutManager = new CheckoutManager();
    
    // Cargar progreso guardado si existe
    const savedProgress = window.checkoutManager.loadFormProgress();
    if (savedProgress && savedProgress.step > 1) {
        const shouldRestore = confirm(
            '¿Quieres continuar donde dejaste el proceso de checkout?'
        );
        
        if (shouldRestore) {
            // Restaurar datos del formulario
            Object.entries(savedProgress.data).forEach(([name, value]) => {
                const field = document.querySelector(`[name="${name}"]`);
                if (field) {
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        field.checked = value === field.value;
                    } else {
                        field.value = value;
                    }
                }
            });
            
            // Ir al paso guardado
            window.checkoutManager.nextStep(savedProgress.step);
        }
    }
});