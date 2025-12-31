/**
 * AgroConecta - Auth JavaScript
 * Validaciones y funcionalidad del frontend para autenticación
 */

// Toggle password visibility
function togglePassword(inputId) {
    const input = inputId ? document.getElementById(inputId) : document.getElementById('password');
    const icon = input.parentElement.querySelector('.toggle-password');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validación de email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validación de contraseña
function validatePassword(password) {
    // Mínimo 8 caracteres, al menos una mayúscula, una minúscula, un número y un carácter especial
    const minLength = password.length >= 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[*?¿¡!#$&\/\[\]_{}]/.test(password);
    
    return {
        valid: minLength && hasUpper && hasLower && hasNumber && hasSpecial,
        minLength,
        hasUpper,
        hasLower,
        hasNumber,
        hasSpecial
    };
}

// Validación de teléfono
function validatePhone(phone) {
    // Acepta formatos: 5512345678, 55 1234 5678, (55) 1234-5678
    const re = /^[\d\s\(\)\-]{10,15}$/;
    return re.test(phone);
}

// Mostrar mensaje de error en campo
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const feedback = field.parentElement.parentElement.querySelector('.invalid-feedback');
    
    field.classList.add('is-invalid');
    feedback.textContent = message;
    feedback.style.display = 'block';
}

// Limpiar mensaje de error en campo
function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const feedback = field.parentElement.parentElement.querySelector('.invalid-feedback');
    
    field.classList.remove('is-invalid');
    feedback.textContent = '';
    feedback.style.display = 'none';
}

// Validación del formulario de login
if (document.getElementById('loginForm')) {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    // Validación en tiempo real
    emailInput.addEventListener('blur', function() {
        if (!this.value) {
            showFieldError('email', 'El correo es requerido');
        } else if (!validateEmail(this.value)) {
            showFieldError('email', 'Ingresa un correo válido');
        } else {
            clearFieldError('email');
        }
    });
    
    emailInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            clearFieldError('email');
        }
    });
    
    passwordInput.addEventListener('blur', function() {
        if (!this.value) {
            showFieldError('password', 'La contraseña es requerida');
        } else {
            clearFieldError('password');
        }
    });
    
    passwordInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            clearFieldError('password');
        }
    });
    
    // Validación al enviar
    loginForm.addEventListener('submit', function(e) {
        let valid = true;
        
        // Validar email
        if (!emailInput.value) {
            showFieldError('email', 'El correo es requerido');
            valid = false;
        } else if (!validateEmail(emailInput.value)) {
            showFieldError('email', 'Ingresa un correo válido');
            valid = false;
        }
        
        // Validar password
        if (!passwordInput.value) {
            showFieldError('password', 'La contraseña es requerida');
            valid = false;
        }
        
        if (!valid) {
            e.preventDefault();
        } else {
            // Mostrar loading en botón
            const btn = document.getElementById('loginBtn');
            btn.classList.add('btn-loading');
            btn.disabled = true;
        }
    });
}

// Validación del formulario de registro
if (document.getElementById('registerForm')) {
    const registerForm = document.getElementById('registerForm');
    const nombreInput = document.getElementById('nombre');
    const apellidoInput = document.getElementById('apellido');
    const emailInput = document.getElementById('email');
    const telefonoInput = document.getElementById('telefono');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    const terminosInput = document.getElementById('terminos');
    
    // Validación de nombre
    nombreInput.addEventListener('blur', function() {
        if (!this.value.trim()) {
            showFieldError('nombre', 'El nombre es requerido');
        } else if (this.value.trim().length < 2) {
            showFieldError('nombre', 'El nombre debe tener al menos 2 caracteres');
        } else {
            clearFieldError('nombre');
        }
    });
    
    // Validación de apellido
    apellidoInput.addEventListener('blur', function() {
        if (!this.value.trim()) {
            showFieldError('apellido', 'El apellido es requerido');
        } else if (this.value.trim().length < 2) {
            showFieldError('apellido', 'El apellido debe tener al menos 2 caracteres');
        } else {
            clearFieldError('apellido');
        }
    });
    
    // Validación de email
    emailInput.addEventListener('blur', function() {
        if (!this.value) {
            showFieldError('email', 'El correo es requerido');
        } else if (!validateEmail(this.value)) {
            showFieldError('email', 'Ingresa un correo válido');
        } else {
            clearFieldError('email');
        }
    });
    
    // Validación de teléfono
    telefonoInput.addEventListener('blur', function() {
        if (!this.value) {
            showFieldError('telefono', 'El teléfono es requerido');
        } else if (!validatePhone(this.value)) {
            showFieldError('telefono', 'Ingresa un teléfono válido (10 dígitos)');
        } else {
            clearFieldError('telefono');
        }
    });
    
    // Validación de contraseña
    passwordInput.addEventListener('blur', function() {
        if (!this.value) {
            showFieldError('password', 'La contraseña es requerida');
        } else {
            const validation = validatePassword(this.value);
            if (!validation.valid) {
                let errors = [];
                if (!validation.minLength) errors.push('mínimo 8 caracteres');
                if (!validation.hasUpper) errors.push('una mayúscula');
                if (!validation.hasLower) errors.push('una minúscula');
                if (!validation.hasNumber) errors.push('un número');
                if (!validation.hasSpecial) errors.push('un carácter especial');
                
                showFieldError('password', 'Debe contener: ' + errors.join(', '));
            } else {
                clearFieldError('password');
            }
        }
    });
    
    // Validación de confirmación de contraseña
    passwordConfirmInput.addEventListener('blur', function() {
        if (!this.value) {
            showFieldError('password_confirm', 'Debes confirmar tu contraseña');
        } else if (this.value !== passwordInput.value) {
            showFieldError('password_confirm', 'Las contraseñas no coinciden');
        } else {
            clearFieldError('password_confirm');
        }
    });
    
    // Limpiar errores al escribir
    [nombreInput, apellidoInput, emailInput, telefonoInput, passwordInput, passwordConfirmInput].forEach(input => {
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                clearFieldError(this.id);
            }
        });
    });
    
    // Validación al enviar
    registerForm.addEventListener('submit', function(e) {
        let valid = true;
        
        // Validar nombre
        if (!nombreInput.value.trim() || nombreInput.value.trim().length < 2) {
            showFieldError('nombre', 'El nombre es requerido (mínimo 2 caracteres)');
            valid = false;
        }
        
        // Validar apellido
        if (!apellidoInput.value.trim() || apellidoInput.value.trim().length < 2) {
            showFieldError('apellido', 'El apellido es requerido (mínimo 2 caracteres)');
            valid = false;
        }
        
        // Validar email
        if (!emailInput.value || !validateEmail(emailInput.value)) {
            showFieldError('email', 'Ingresa un correo válido');
            valid = false;
        }
        
        // Validar teléfono
        if (!telefonoInput.value || !validatePhone(telefonoInput.value)) {
            showFieldError('telefono', 'Ingresa un teléfono válido');
            valid = false;
        }
        
        // Validar password
        const passwordValidation = validatePassword(passwordInput.value);
        if (!passwordValidation.valid) {
            showFieldError('password', 'La contraseña no cumple los requisitos de seguridad');
            valid = false;
        }
        
        // Validar confirmación
        if (passwordInput.value !== passwordConfirmInput.value) {
            showFieldError('password_confirm', 'Las contraseñas no coinciden');
            valid = false;
        }
        
        // Validar términos
        if (!terminosInput.checked) {
            alert('Debes aceptar los términos y condiciones');
            valid = false;
        }
        
        // Validar campos de vendedor si aplica
        const tipoUsuario = document.querySelector('input[name="tipo_usuario"]:checked').value;
        if (tipoUsuario === 'vendedor') {
            const nombreNegocio = document.getElementById('nombre_negocio');
            const ciudad = document.getElementById('ciudad');
            const estado = document.getElementById('estado');
            
            if (!nombreNegocio.value.trim()) {
                showFieldError('nombre_negocio', 'El nombre del negocio es requerido');
                valid = false;
            }
            
            if (!ciudad.value.trim()) {
                showFieldError('ciudad', 'La ciudad es requerida');
                valid = false;
            }
            
            if (!estado.value) {
                showFieldError('estado', 'El estado es requerido');
                valid = false;
            }
        }
        
        if (!valid) {
            e.preventDefault();
            // Scroll al primer error
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            // Mostrar loading en botón
            const btn = document.getElementById('registerBtn');
            btn.classList.add('btn-loading');
            btn.disabled = true;
        }
    });
}

// Validación del formulario de recuperación de contraseña
if (document.getElementById('forgotPasswordForm')) {
    const forgotForm = document.getElementById('forgotPasswordForm');
    const emailInput = document.getElementById('email');
    
    emailInput.addEventListener('blur', function() {
        if (!this.value) {
            showFieldError('email', 'El correo es requerido');
        } else if (!validateEmail(this.value)) {
            showFieldError('email', 'Ingresa un correo válido');
        } else {
            clearFieldError('email');
        }
    });
    
    emailInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            clearFieldError('email');
        }
    });
    
    forgotForm.addEventListener('submit', function(e) {
        if (!emailInput.value || !validateEmail(emailInput.value)) {
            showFieldError('email', 'Ingresa un correo válido');
            e.preventDefault();
        } else {
            // Mostrar loading en botón
            const btn = document.getElementById('resetBtn');
            btn.classList.add('btn-loading');
            btn.disabled = true;
        }
    });
}

// Auto-hide alerts después de 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
