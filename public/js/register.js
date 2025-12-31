// =========================================
// REGISTER.JS - Funcionalidad del registro
// =========================================

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // === ELEMENTOS DEL DOM ===
    const typeTabs = document.querySelectorAll('.type-tab');
    const tipoUsuarioInput = document.getElementById('tipoUsuario');
    const vendorSection = document.getElementById('vendorSection');
    const vendorTerms = document.getElementById('vendorTerms');
    const registerForm = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const passwordMatch = document.getElementById('passwordMatch');
    
    // === CAMBIO DE TIPO DE CUENTA ===
    const AccountType = {
        init: function() {
            typeTabs.forEach(tab => {
                tab.addEventListener('click', this.handleTabClick.bind(this));
            });
        },
        
        handleTabClick: function(e) {
            const tab = e.currentTarget;
            const accountType = tab.dataset.type;
            
            // Update active tab
            typeTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Update hidden input
            if (tipoUsuarioInput) {
                tipoUsuarioInput.value = accountType;
            }
            
            // Show/hide vendor section
            if (vendorSection) {
                if (accountType === 'vendedor') {
                    vendorSection.style.display = 'block';
                    vendorTerms.style.display = 'block';
                    this.makeVendorFieldsRequired(true);
                } else {
                    vendorSection.style.display = 'none';
                    vendorTerms.style.display = 'none';
                    this.makeVendorFieldsRequired(false);
                }
            }
            
            // Update form title and description
            this.updateFormContent(accountType);
        },
        
        makeVendorFieldsRequired: function(required) {
            const vendorFields = vendorSection.querySelectorAll('input[name="productos_cultiva[]"]');
            const termsVendor = document.querySelector('input[name="acepto_terminos_vendedor"]');
            
            if (required) {
                // At least one product type should be selected
                vendorFields.forEach(field => {
                    field.setAttribute('data-vendor-required', 'true');
                });
                if (termsVendor) {
                    termsVendor.setAttribute('required', 'true');
                }
            } else {
                vendorFields.forEach(field => {
                    field.removeAttribute('data-vendor-required');
                });
                if (termsVendor) {
                    termsVendor.removeAttribute('required');
                }
            }
        },
        
        updateFormContent: function(accountType) {
            const subtitle = document.querySelector('.auth-subtitle');
            const registerBtn = document.getElementById('registerBtn');
            
            if (accountType === 'vendedor') {
                subtitle.textContent = 'Únete como productor y comienza a vender tus productos frescos';
                registerBtn.querySelector('.btn-text').innerHTML = '<i class="fas fa-store"></i> Crear Cuenta de Productor';
            } else {
                subtitle.textContent = 'Crea tu cuenta gratuita y comienza a disfrutar de productos frescos del campo';
                registerBtn.querySelector('.btn-text').innerHTML = '<i class="fas fa-user-plus"></i> Crear Mi Cuenta';
            }
        }
    };
    
    // === VALIDACIÓN DE CONTRASEÑA ===
    const PasswordValidation = {
        init: function() {
            if (passwordInput) {
                passwordInput.addEventListener('input', this.checkPasswordStrength.bind(this));
            }
            
            if (passwordConfirmInput) {
                passwordConfirmInput.addEventListener('input', this.checkPasswordMatch.bind(this));
            }
        },
        
        checkPasswordStrength: function() {
            const password = passwordInput.value;
            const strength = this.calculateStrength(password);
            
            this.updateStrengthMeter(strength);
            
            // Also check match if confirmation is filled
            if (passwordConfirmInput.value) {
                this.checkPasswordMatch();
            }
        },
        
        calculateStrength: function(password) {
            let score = 0;
            let feedback = [];
            
            if (password.length === 0) {
                return { score: 0, level: 'empty', text: 'Ingresa tu contraseña', feedback: [] };
            }
            
            // Length check
            if (password.length >= 8) {
                score += 2;
            } else {
                feedback.push('Mínimo 8 caracteres');
            }
            
            // Lowercase check
            if (/[a-z]/.test(password)) {
                score += 1;
            } else {
                feedback.push('Incluye minúsculas');
            }
            
            // Uppercase check
            if (/[A-Z]/.test(password)) {
                score += 1;
            } else {
                feedback.push('Incluye mayúsculas');
            }
            
            // Number check
            if (/\d/.test(password)) {
                score += 1;
            } else {
                feedback.push('Incluye números');
            }
            
            // Special character check
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
                score += 1;
            }
            
            // Length bonus
            if (password.length >= 12) {
                score += 1;
            }
            
            // Determine level
            let level, text;
            if (score < 3) {
                level = 'weak';
                text = 'Débil';
            } else if (score < 5) {
                level = 'medium';
                text = 'Medio';
            } else {
                level = 'strong';
                text = 'Fuerte';
            }
            
            return { score, level, text, feedback };
        },
        
        updateStrengthMeter: function(strength) {
            if (!strengthBar || !strengthText) return;
            
            // Update bar width and color
            const percentage = Math.min((strength.score / 6) * 100, 100);
            strengthBar.style.width = percentage + '%';
            
            // Reset classes
            strengthBar.className = 'strength-bar';
            strengthText.className = 'strength-text';
            
            // Add level class
            if (strength.level !== 'empty') {
                strengthBar.classList.add(strength.level);
                strengthText.classList.add(strength.level);
            }
            
            // Update text
            if (strength.feedback.length > 0) {
                strengthText.textContent = strength.text + ': ' + strength.feedback.join(', ');
            } else if (strength.level === 'strong') {
                strengthText.textContent = '¡Excelente contraseña!';
            } else {
                strengthText.textContent = 'Contraseña ' + strength.text.toLowerCase();
            }
        },
        
        checkPasswordMatch: function() {
            if (!passwordMatch) return;
            
            const password = passwordInput.value;
            const confirmPassword = passwordConfirmInput.value;
            
            const checkIcon = passwordMatch.querySelector('.fa-check-circle');
            const timesIcon = passwordMatch.querySelector('.fa-times-circle');
            const matchText = passwordMatch.querySelector('.match-text');
            
            if (confirmPassword === '') {
                checkIcon.style.display = 'none';
                timesIcon.style.display = 'none';
                matchText.textContent = '';
                return;
            }
            
            if (password === confirmPassword) {
                checkIcon.style.display = 'inline';
                timesIcon.style.display = 'none';
                matchText.textContent = 'Las contraseñas coinciden';
                matchText.className = 'match-text text-success';
                passwordConfirmInput.setCustomValidity('');
            } else {
                checkIcon.style.display = 'none';
                timesIcon.style.display = 'inline';
                matchText.textContent = 'Las contraseñas no coinciden';
                matchText.className = 'match-text text-danger';
                passwordConfirmInput.setCustomValidity('Las contraseñas no coinciden');
            }
        }
    };
    
    // === VALIDACIÓN DEL FORMULARIO ===
    const FormValidation = {
        init: function() {
            if (registerForm) {
                registerForm.addEventListener('submit', this.handleSubmit.bind(this));
                
                // Real-time validation
                const inputs = registerForm.querySelectorAll('input, select');
                inputs.forEach(input => {
                    input.addEventListener('blur', () => this.validateField(input));
                    input.addEventListener('input', () => this.clearFieldError(input));
                });
            }
        },
        
        handleSubmit: function(e) {
            const isValid = this.validateForm();
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            this.showLoadingState();
        },
        
        validateForm: function() {
            let isValid = true;
            const errors = [];
            
            // Basic field validation
            const requiredFields = registerForm.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!this.validateField(field)) {
                    isValid = false;
                }
            });
            
            // Vendor-specific validation
            if (tipoUsuarioInput.value === 'vendedor') {
                const productTypes = registerForm.querySelectorAll('input[name="productos_cultiva[]"]:checked');
                if (productTypes.length === 0) {
                    isValid = false;
                    errors.push('Selecciona al menos un tipo de producto que cultivas');
                }
            }
            
            // Password validation
            const password = passwordInput.value;
            const confirmPassword = passwordConfirmInput.value;
            
            if (password !== confirmPassword) {
                isValid = false;
                errors.push('Las contraseñas no coinciden');
            }
            
            const strength = PasswordValidation.calculateStrength(password);
            if (strength.score < 3) {
                isValid = false;
                errors.push('La contraseña es muy débil');
            }
            
            // Terms validation
            const termsCheckbox = registerForm.querySelector('input[name="acepto_terminos"]');
            if (termsCheckbox && !termsCheckbox.checked) {
                isValid = false;
                errors.push('Debes aceptar los términos y condiciones');
            }
            
            // Vendor terms validation
            if (tipoUsuarioInput.value === 'vendedor') {
                const vendorTermsCheckbox = registerForm.querySelector('input[name="acepto_terminos_vendedor"]');
                if (vendorTermsCheckbox && !vendorTermsCheckbox.checked) {
                    isValid = false;
                    errors.push('Debes aceptar los términos adicionales para vendedores');
                }
            }
            
            // Show errors
            if (errors.length > 0 && window.AgroConectaUtils) {
                errors.forEach(error => {
                    window.AgroConectaUtils.showToast(error, 'error');
                });
            }
            
            return isValid;
        },
        
        validateField: function(field) {
            this.clearFieldError(field);
            
            let isValid = true;
            let errorMessage = '';
            
            // Required field validation
            if (field.hasAttribute('required') && !field.value.trim()) {
                isValid = false;
                errorMessage = 'Este campo es obligatorio';
            }
            
            // Email validation
            if (field.type === 'email' && field.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    isValid = false;
                    errorMessage = 'Ingresa un correo electrónico válido';
                }
            }
            
            // Phone validation
            if (field.name === 'telefono' && field.value) {
                const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
                if (!phoneRegex.test(field.value)) {
                    isValid = false;
                    errorMessage = 'Ingresa un número de teléfono válido';
                }
            }
            
            // Show error if not valid
            if (!isValid) {
                this.showFieldError(field, errorMessage);
            }
            
            return isValid;
        },
        
        showFieldError: function(field, message) {
            field.classList.add('is-invalid');
            
            // Create or update error message
            let errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                field.parentNode.appendChild(errorDiv);
            }
            
            errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        },
        
        clearFieldError: function(field) {
            field.classList.remove('is-invalid');
            const errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
        },
        
        showLoadingState: function() {
            const registerBtn = document.getElementById('registerBtn');
            if (registerBtn) {
                const btnText = registerBtn.querySelector('.btn-text');
                const btnLoading = registerBtn.querySelector('.btn-loading');
                
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';
                registerBtn.disabled = true;
            }
            
            // Show loading overlay
            const authLoading = document.getElementById('authLoading');
            if (authLoading) {
                authLoading.style.display = 'flex';
            }
        }
    };
    
    // === UTILIDADES ===
    const Utils = {
        // Auto-fill city based on state
        setupLocationAutofill: function() {
            const estadoSelect = document.getElementById('estado');
            const ciudadInput = document.getElementById('ciudad');
            
            if (!estadoSelect || !ciudadInput) return;
            
            const ciudadesPorEstado = {
                'cdmx': ['Ciudad de México', 'Álvaro Obregón', 'Azcapotzalco', 'Benito Juárez', 'Coyoacán'],
                'mexico': ['Toluca', 'Ecatepec', 'Nezahualcóyotl', 'Naucalpan', 'Tlalnepantla'],
                'jalisco': ['Guadalajara', 'Zapopan', 'Tlaquepaque', 'Tonalá', 'Puerto Vallarta'],
                'nuevo_leon': ['Monterrey', 'Guadalupe', 'San Nicolás', 'Apodaca', 'General Escobedo'],
                'puebla': ['Puebla', 'Tehuacán', 'San Martín Texmelucan', 'Atlixco', 'Cholula']
            };
            
            estadoSelect.addEventListener('change', function() {
                const estado = this.value;
                const ciudades = ciudadesPorEstado[estado];
                
                if (ciudades) {
                    // Convert input to select temporarily or show suggestions
                    ciudadInput.setAttribute('placeholder', `Ej: ${ciudades[0]}`);
                    ciudadInput.setAttribute('list', 'ciudades-' + estado);
                    
                    // Create datalist if it doesn't exist
                    let datalist = document.getElementById('ciudades-' + estado);
                    if (!datalist) {
                        datalist = document.createElement('datalist');
                        datalist.id = 'ciudades-' + estado;
                        ciudades.forEach(ciudad => {
                            const option = document.createElement('option');
                            option.value = ciudad;
                            datalist.appendChild(option);
                        });
                        document.body.appendChild(datalist);
                    }
                }
            });
        },
        
        // Format phone number as user types
        setupPhoneFormatting: function() {
            const phoneInput = document.getElementById('telefono');
            if (!phoneInput) return;
            
            phoneInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                
                if (value.length <= 10) {
                    // Format as: 55 1234 5678
                    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '$1 $2 $3');
                } else {
                    // Format as: +52 55 1234 5678
                    value = value.replace(/(\d{2})(\d{2})(\d{4})(\d{4})/, '+$1 $2 $3 $4');
                }
                
                this.value = value;
            });
        }
    };
    
    // === FUNCIONES GLOBALES ===
    window.togglePassword = function(inputId) {
        const input = document.getElementById(inputId);
        const toggle = input.nextElementSibling;
        const icon = toggle.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'far fa-eye';
        }
    };
    
    window.registerWithGoogle = function() {
        console.log('Register with Google');
        if (window.AgroConectaUtils) {
            window.AgroConectaUtils.showToast('Función de Google OAuth próximamente disponible', 'info');
        }
    };
    
    window.registerWithFacebook = function() {
        console.log('Register with Facebook');
        if (window.AgroConectaUtils) {
            window.AgroConectaUtils.showToast('Función de Facebook Login próximamente disponible', 'info');
        }
    };
    
    // === INICIALIZACIÓN ===
    try {
        AccountType.init();
        PasswordValidation.init();
        FormValidation.init();
        Utils.setupLocationAutofill();
        Utils.setupPhoneFormatting();
        
        console.log('Register form initialized successfully');
    } catch (error) {
        console.error('Register form initialization error:', error);
    }
    
    // === EVENTOS PERSONALIZADOS ===
    // Listen for form reset
    if (registerForm) {
        registerForm.addEventListener('reset', function() {
            // Reset password strength meter
            if (strengthBar) {
                strengthBar.style.width = '0%';
                strengthBar.className = 'strength-bar';
            }
            
            if (strengthText) {
                strengthText.textContent = 'Ingresa tu contraseña';
                strengthText.className = 'strength-text';
            }
            
            // Reset password match indicator
            if (passwordMatch) {
                passwordMatch.querySelectorAll('i').forEach(i => i.style.display = 'none');
                passwordMatch.querySelector('.match-text').textContent = '';
            }
            
            // Clear all field errors
            registerForm.querySelectorAll('.is-invalid').forEach(field => {
                field.classList.remove('is-invalid');
            });
            
            registerForm.querySelectorAll('.invalid-feedback').forEach(feedback => {
                feedback.remove();
            });
        });
    }
});

// === CSS ADICIONAL PARA COMPONENTES ===
const additionalStyles = `
.password-strength {
    margin-top: 5px;
}

.strength-meter {
    height: 4px;
    background-color: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 3px;
}

.strength-bar {
    height: 100%;
    transition: width 0.3s ease, background-color 0.3s ease;
    border-radius: 2px;
}

.strength-bar.weak {
    background-color: #dc3545;
}

.strength-bar.medium {
    background-color: #ffc107;
}

.strength-bar.strong {
    background-color: #28a745;
}

.strength-text {
    font-size: 12px;
    transition: color 0.3s ease;
}

.strength-text.weak {
    color: #dc3545;
}

.strength-text.medium {
    color: #856404;
}

.strength-text.strong {
    color: #155724;
}

.password-match {
    margin-top: 5px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.match-text {
    transition: color 0.3s ease;
}

.account-type-selection {
    margin-bottom: 2rem;
}

.type-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.type-tab {
    flex: 1;
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.type-tab:hover {
    border-color: #27ae60;
    transform: translateY(-2px);
}

.type-tab.active {
    border-color: #27ae60;
    background-color: #f8fff9;
}

.type-tab i {
    font-size: 1.5rem;
    color: #27ae60;
    margin-bottom: 0.5rem;
    display: block;
}

.type-tab span {
    display: block;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.type-tab small {
    color: #6c757d;
    font-size: 0.875rem;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.form-section:last-child {
    border-bottom: none;
}

.section-title {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: #27ae60;
}

.checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.5rem;
}

.vendor-section {
    background-color: #f8fff9;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #d4edda;
}

.terms-section {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.terms-check {
    margin-bottom: 1rem;
}

.terms-check .form-check-label {
    line-height: 1.5;
}

@media (max-width: 768px) {
    .type-tabs {
        flex-direction: column;
    }
    
    .checkbox-group {
        grid-template-columns: 1fr;
    }
}
`;

// Inject additional styles
if (document.head) {
    const styleSheet = document.createElement('style');
    styleSheet.textContent = additionalStyles;
    document.head.appendChild(styleSheet);
}