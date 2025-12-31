// =========================================
// PROFILE.JS - Funcionalidad del perfil de usuario
// =========================================

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // === ELEMENTOS DEL DOM ===
    const profileForm = document.getElementById('profileForm');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const editBtn = document.querySelector('[onclick="toggleEditMode()"]');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const bioTextarea = document.getElementById('bio');
    const bioCharCount = document.getElementById('bioCharCount');
    
    // Password fields
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordStrengthBar = document.getElementById('passwordStrengthBar');
    const passwordStrengthText = document.getElementById('passwordStrengthText');
    const passwordMatch = document.getElementById('passwordMatch');
    
    let isEditMode = false;
    let originalFormData = {};
    
    // === GESTIÓN DE PESTAÑAS ===
    const TabManager = {
        init: function() {
            tabButtons.forEach(button => {
                button.addEventListener('click', this.handleTabClick.bind(this));
            });
            
            // Activar pestaña desde URL hash
            const hash = window.location.hash.substring(1);
            if (hash && document.querySelector(`[data-tab="${hash}"]`)) {
                this.activateTab(hash);
            }
        },
        
        handleTabClick: function(e) {
            e.preventDefault();
            const tabName = e.currentTarget.dataset.tab;
            this.activateTab(tabName);
            
            // Update URL hash
            history.replaceState(null, null, '#' + tabName);
        },
        
        activateTab: function(tabName) {
            // Update buttons
            tabButtons.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.tab === tabName);
            });
            
            // Update content
            tabContents.forEach(content => {
                content.classList.toggle('active', content.dataset.tab === tabName);
            });
            
            // Scroll to top of form
            document.querySelector('.profile-form').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }
    };
    
    // === MODO DE EDICIÓN ===
    const EditMode = {
        toggle: function() {
            isEditMode = !isEditMode;
            
            if (isEditMode) {
                this.enableEditMode();
            } else {
                this.disableEditMode();
            }
        },
        
        enableEditMode: function() {
            // Store original data
            originalFormData = this.getFormData();
            
            // Enable form fields
            const inputs = profileForm.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (!input.hasAttribute('data-never-edit')) {
                    input.disabled = false;
                }
            });
            
            // Update UI
            editBtn.innerHTML = '<i class="fas fa-times"></i> <span id="editBtnText">Cancelar</span>';
            editBtn.className = 'btn btn-outline-secondary';
            
            saveBtn.style.display = 'inline-flex';
            cancelBtn.style.display = 'inline-flex';
            
            // Show edit hints
            this.showEditHints();
            
            if (window.AgroConectaUtils) {
                window.AgroConectaUtils.showToast('Modo edición activado', 'info');
            }
        },
        
        disableEditMode: function() {
            // Disable form fields
            const inputs = profileForm.querySelectorAll('input, select, textarea');
            inputs.forEach(input => input.disabled = true);
            
            // Update UI
            editBtn.innerHTML = '<i class="fas fa-edit"></i> <span id="editBtnText">Editar Perfil</span>';
            editBtn.className = 'btn btn-outline-secondary';
            
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            
            // Hide edit hints
            this.hideEditHints();
        },
        
        cancel: function() {
            if (originalFormData) {
                this.restoreFormData(originalFormData);
            }
            
            this.disableEditMode();
            isEditMode = false;
            
            if (window.AgroConectaUtils) {
                window.AgroConectaUtils.showToast('Cambios cancelados', 'info');
            }
        },
        
        getFormData: function() {
            const formData = new FormData(profileForm);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                if (data[key]) {
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value;
                }
            }
            
            return data;
        },
        
        restoreFormData: function(data) {
            Object.keys(data).forEach(key => {
                const field = profileForm.querySelector(`[name="${key}"]`);
                if (field) {
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        const value = Array.isArray(data[key]) ? data[key] : [data[key]];
                        const fields = profileForm.querySelectorAll(`[name="${key}"]`);
                        
                        fields.forEach(f => {
                            f.checked = value.includes(f.value);
                        });
                    } else {
                        field.value = Array.isArray(data[key]) ? data[key][0] : data[key];
                    }
                }
            });
            
            // Update character counters
            if (bioTextarea) {
                this.updateCharCount();
            }
        },
        
        showEditHints: function() {
            const hints = document.querySelectorAll('.edit-hint');
            hints.forEach(hint => hint.style.display = 'block');
        },
        
        hideEditHints: function() {
            const hints = document.querySelectorAll('.edit-hint');
            hints.forEach(hint => hint.style.display = 'none');
        },
        
        updateCharCount: function() {
            if (bioTextarea && bioCharCount) {
                const count = bioTextarea.value.length;
                bioCharCount.textContent = count;
                
                if (count > 500) {
                    bioCharCount.style.color = '#dc3545';
                    bioTextarea.classList.add('is-invalid');
                } else {
                    bioCharCount.style.color = '#6c757d';
                    bioTextarea.classList.remove('is-invalid');
                }
            }
        }
    };
    
    // === VALIDACIÓN DE CONTRASEÑA ===
    const PasswordValidation = {
        init: function() {
            if (newPasswordInput) {
                newPasswordInput.addEventListener('input', this.checkPasswordStrength.bind(this));
            }
            
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', this.checkPasswordMatch.bind(this));
            }
        },
        
        checkPasswordStrength: function() {
            const password = newPasswordInput.value;
            const strength = this.calculateStrength(password);
            
            this.updateStrengthMeter(strength);
            
            // Also check match if confirmation is filled
            if (confirmPasswordInput.value) {
                this.checkPasswordMatch();
            }
        },
        
        calculateStrength: function(password) {
            let score = 0;
            let feedback = [];
            
            if (password.length === 0) {
                return { score: 0, level: 'empty', text: 'Ingresa tu nueva contraseña', feedback: [] };
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
            if (!passwordStrengthBar || !passwordStrengthText) return;
            
            // Update bar width and color
            const percentage = Math.min((strength.score / 6) * 100, 100);
            passwordStrengthBar.style.width = percentage + '%';
            
            // Reset classes
            passwordStrengthBar.className = 'strength-bar';
            passwordStrengthText.className = 'strength-text';
            
            // Add level class
            if (strength.level !== 'empty') {
                passwordStrengthBar.classList.add(strength.level);
                passwordStrengthText.classList.add(strength.level);
            }
            
            // Update text
            if (strength.feedback.length > 0) {
                passwordStrengthText.textContent = strength.text + ': ' + strength.feedback.join(', ');
            } else if (strength.level === 'strong') {
                passwordStrengthText.textContent = '¡Excelente contraseña!';
            } else {
                passwordStrengthText.textContent = 'Contraseña ' + strength.text.toLowerCase();
            }
        },
        
        checkPasswordMatch: function() {
            if (!passwordMatch) return;
            
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
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
                confirmPasswordInput.setCustomValidity('');
            } else {
                checkIcon.style.display = 'none';
                timesIcon.style.display = 'inline';
                matchText.textContent = 'Las contraseñas no coinciden';
                matchText.className = 'match-text text-danger';
                confirmPasswordInput.setCustomValidity('Las contraseñas no coinciden');
            }
        }
    };
    
    // === GESTIÓN DE AVATAR ===
    const AvatarManager = {
        currentFile: null,
        
        init: function() {
            const avatarFileInput = document.getElementById('avatarFileInput');
            if (avatarFileInput) {
                avatarFileInput.addEventListener('change', this.handleFileSelect.bind(this));
            }
        },
        
        handleFileSelect: function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('Solo se permiten archivos de imagen', 'error');
                }
                return;
            }
            
            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('El archivo es demasiado grande. Máximo 5MB', 'error');
                }
                return;
            }
            
            this.currentFile = file;
            this.showPreview(file);
        },
        
        showPreview: function(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const previewSection = document.getElementById('avatarPreview');
                const previewImage = document.getElementById('previewImage');
                const uploadBtn = document.getElementById('uploadAvatarBtn');
                
                previewImage.src = e.target.result;
                previewSection.style.display = 'block';
                uploadBtn.disabled = false;
            };
            reader.readAsDataURL(file);
        },
        
        upload: async function() {
            if (!this.currentFile) return;
            
            const uploadBtn = document.getElementById('uploadAvatarBtn');
            const originalText = uploadBtn.innerHTML;
            
            try {
                uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
                uploadBtn.disabled = true;
                
                const formData = new FormData();
                formData.append('avatar', this.currentFile);
                formData.append('_token', document.querySelector('[name="_token"]').value);
                
                const response = await fetch('/user/avatar/upload', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update avatar in UI
                    const currentAvatar = document.getElementById('currentAvatar');
                    if (currentAvatar.tagName === 'IMG') {
                        currentAvatar.src = result.avatar_url + '?t=' + Date.now();
                    } else {
                        const img = document.createElement('img');
                        img.id = 'currentAvatar';
                        img.src = result.avatar_url;
                        img.alt = 'Avatar';
                        currentAvatar.parentNode.replaceChild(img, currentAvatar);
                    }
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('avatarModal'));
                    modal.hide();
                    
                    if (window.AgroConectaUtils) {
                        window.AgroConectaUtils.showToast('Foto de perfil actualizada correctamente', 'success');
                    }
                } else {
                    throw new Error(result.message || 'Error al subir la imagen');
                }
                
            } catch (error) {
                console.error('Avatar upload error:', error);
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('Error al subir la imagen: ' + error.message, 'error');
                }
            } finally {
                uploadBtn.innerHTML = originalText;
                uploadBtn.disabled = false;
            }
        }
    };
    
    // === VALIDACIÓN DEL FORMULARIO ===
    const FormValidation = {
        init: function() {
            if (profileForm) {
                profileForm.addEventListener('submit', this.handleSubmit.bind(this));
                
                // Real-time validation
                const inputs = profileForm.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.addEventListener('blur', () => this.validateField(input));
                    input.addEventListener('input', () => this.clearFieldError(input));
                });
            }
        },
        
        handleSubmit: async function(e) {
            e.preventDefault();
            
            if (!isEditMode) return false;
            
            const isValid = this.validateForm();
            
            if (!isValid) {
                return false;
            }
            
            await this.submitForm();
        },
        
        validateForm: function() {
            let isValid = true;
            
            // Basic field validation
            const requiredFields = profileForm.querySelectorAll('[required]:not([disabled])');
            requiredFields.forEach(field => {
                if (!this.validateField(field)) {
                    isValid = false;
                }
            });
            
            // Password validation if changing password
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (currentPassword || newPassword || confirmPassword) {
                if (!currentPassword) {
                    this.showFieldError(document.getElementById('current_password'), 'Ingresa tu contraseña actual');
                    isValid = false;
                }
                
                if (!newPassword) {
                    this.showFieldError(newPasswordInput, 'Ingresa tu nueva contraseña');
                    isValid = false;
                } else {
                    const strength = PasswordValidation.calculateStrength(newPassword);
                    if (strength.score < 3) {
                        this.showFieldError(newPasswordInput, 'La contraseña es muy débil');
                        isValid = false;
                    }
                }
                
                if (newPassword !== confirmPassword) {
                    this.showFieldError(confirmPasswordInput, 'Las contraseñas no coinciden');
                    isValid = false;
                }
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
        
        submitForm: async function() {
            const saveBtn = document.getElementById('saveBtn');
            const btnText = saveBtn.querySelector('.btn-text');
            const btnLoading = saveBtn.querySelector('.btn-loading');
            
            try {
                // Show loading state
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';
                saveBtn.disabled = true;
                
                const formData = new FormData(profileForm);
                
                const response = await fetch(profileForm.action, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update UI with new data
                    this.updateProfileDisplay(result.user);
                    
                    // Exit edit mode
                    EditMode.disableEditMode();
                    isEditMode = false;
                    
                    if (window.AgroConectaUtils) {
                        window.AgroConectaUtils.showToast('Perfil actualizado correctamente', 'success');
                    }
                } else {
                    throw new Error(result.message || 'Error al actualizar el perfil');
                }
                
            } catch (error) {
                console.error('Profile update error:', error);
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('Error al actualizar el perfil: ' + error.message, 'error');
                }
            } finally {
                // Reset button state
                btnText.style.display = 'inline-flex';
                btnLoading.style.display = 'none';
                saveBtn.disabled = false;
            }
        },
        
        updateProfileDisplay: function(userData) {
            // Update header information
            const profileSummary = document.querySelector('.profile-summary h1');
            if (profileSummary) {
                profileSummary.textContent = userData.nombre + ' ' + userData.apellido;
            }
            
            // Update other display elements as needed
            // This would depend on what fields were updated
        }
    };
    
    // === FUNCIONES GLOBALES EXPUESTAS ===
    window.toggleEditMode = function() {
        EditMode.toggle();
    };
    
    window.cancelEdit = function() {
        EditMode.cancel();
    };
    
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
    
    window.openAvatarModal = function() {
        const modal = new bootstrap.Modal(document.getElementById('avatarModal'));
        modal.show();
    };
    
    window.uploadAvatar = function() {
        AvatarManager.upload();
    };
    
    window.sendVerificationEmail = async function() {
        try {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            button.disabled = true;
            
            const response = await fetch('/user/email/verification-notification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    _token: document.querySelector('[name="_token"]').value
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('Correo de verificación enviado. Revisa tu bandeja de entrada.', 'success');
                }
            } else {
                throw new Error(result.message || 'Error al enviar el correo de verificación');
            }
        } catch (error) {
            if (window.AgroConectaUtils) {
                window.AgroConectaUtils.showToast('Error: ' + error.message, 'error');
            }
        } finally {
            const button = event.target;
            button.innerHTML = '<i class="fas fa-envelope"></i> Verificar Email';
            button.disabled = false;
        }
    };
    
    window.showDeleteAccountModal = function() {
        const modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
        modal.show();
    };
    
    window.confirmDeleteAccount = async function() {
        const password = document.getElementById('deleteConfirmPassword').value;
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        if (!password) {
            if (window.AgroConectaUtils) {
                window.AgroConectaUtils.showToast('Ingresa tu contraseña para confirmar', 'error');
            }
            return;
        }
        
        try {
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
            confirmBtn.disabled = true;
            
            const response = await fetch('/user/delete', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    password: password,
                    _token: document.querySelector('[name="_token"]').value
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                if (window.AgroConectaUtils) {
                    window.AgroConectaUtils.showToast('Cuenta eliminada correctamente. Adiós.', 'success');
                }
                
                setTimeout(() => {
                    window.location.href = '/';
                }, 2000);
            } else {
                throw new Error(result.message || 'Error al eliminar la cuenta');
            }
        } catch (error) {
            if (window.AgroConectaUtils) {
                window.AgroConectaUtils.showToast('Error: ' + error.message, 'error');
            }
            confirmBtn.innerHTML = '<i class="fas fa-trash"></i> Eliminar Cuenta Definitivamente';
            confirmBtn.disabled = false;
        }
    };
    
    // === UTILIDADES ADICIONALES ===
    const Utils = {
        // Bio character counter
        setupCharacterCounter: function() {
            if (bioTextarea) {
                bioTextarea.addEventListener('input', EditMode.updateCharCount.bind(EditMode));
                EditMode.updateCharCount(); // Initial count
            }
        },
        
        // Phone formatting
        setupPhoneFormatting: function() {
            const phoneInputs = document.querySelectorAll('input[type="tel"]');
            phoneInputs.forEach(input => {
                input.addEventListener('input', function() {
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
            });
        },
        
        // Auto-save draft (optional)
        setupAutoSave: function() {
            if (profileForm) {
                const draftKey = 'agroconecta_profile_draft_' + (window.userId || 'anonymous');
                let autoSaveTimeout;
                
                const inputs = profileForm.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.addEventListener('input', function() {
                        if (!isEditMode) return;
                        
                        clearTimeout(autoSaveTimeout);
                        autoSaveTimeout = setTimeout(() => {
                            const formData = EditMode.getFormData();
                            localStorage.setItem(draftKey, JSON.stringify({
                                timestamp: Date.now(),
                                data: formData
                            }));
                        }, 1000);
                    });
                });
                
                // Load draft on edit mode
                const loadDraft = () => {
                    const draft = localStorage.getItem(draftKey);
                    if (draft && isEditMode) {
                        try {
                            const parsed = JSON.parse(draft);
                            // Only load if draft is less than 1 hour old
                            if (Date.now() - parsed.timestamp < 3600000) {
                                if (confirm('¿Quieres cargar el borrador guardado?')) {
                                    EditMode.restoreFormData(parsed.data);
                                }
                            }
                        } catch (e) {
                            console.warn('Invalid draft data:', e);
                        }
                    }
                };
                
                // Clear draft on successful save
                profileForm.addEventListener('submit', () => {
                    localStorage.removeItem(draftKey);
                });
            }
        }
    };
    
    // === INICIALIZACIÓN ===
    try {
        TabManager.init();
        PasswordValidation.init();
        AvatarManager.init();
        FormValidation.init();
        Utils.setupCharacterCounter();
        Utils.setupPhoneFormatting();
        // Utils.setupAutoSave(); // Uncomment if needed
        
        // Enable delete password confirmation
        const deletePasswordInput = document.getElementById('deleteConfirmPassword');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        
        if (deletePasswordInput && confirmDeleteBtn) {
            deletePasswordInput.addEventListener('input', function() {
                confirmDeleteBtn.disabled = this.value.length === 0;
            });
        }
        
        console.log('Profile page initialized successfully');
    } catch (error) {
        console.error('Profile page initialization error:', error);
    }
});

// === CSS ADICIONAL PARA COMPONENTES ===
const additionalStyles = `
.user-profile {
    min-height: calc(100vh - 200px);
}

.profile-header {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
    padding: 2rem 0;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.profile-avatar-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex: 1;
}

.avatar-container {
    position: relative;
    width: 120px;
    height: 120px;
}

.avatar-container img,
.avatar-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255, 255, 255, 0.2);
}

.avatar-placeholder {
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: rgba(255, 255, 255, 0.8);
}

.avatar-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.avatar-container:hover .avatar-overlay {
    opacity: 1;
}

.btn-change-avatar {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: background-color 0.3s ease;
}

.btn-change-avatar:hover {
    background: rgba(255, 255, 255, 0.2);
}

.user-status {
    position: absolute;
    bottom: -5px;
    right: 10px;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.status-indicator.online {
    background-color: #2ecc71;
}

.profile-summary h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 600;
}

.user-type {
    margin-bottom: 1rem;
    opacity: 0.9;
}

.profile-stats {
    display: flex;
    gap: 2rem;
}

.stat-item {
    text-align: center;
}

.stat-item strong {
    display: block;
    font-size: 1.2rem;
}

.stat-item small {
    opacity: 0.8;
    font-size: 0.875rem;
}

.profile-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.form-tabs {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-top: 2rem;
}

.tab-navigation {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    overflow-x: auto;
}

.tab-btn {
    padding: 1rem 1.5rem;
    border: none;
    background: none;
    color: #6c757d;
    font-weight: 500;
    transition: all 0.3s ease;
    white-space: nowrap;
    position: relative;
}

.tab-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.tab-btn.active {
    color: #27ae60;
    background: white;
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: #27ae60;
}

.tab-btn i {
    margin-right: 0.5rem;
}

.tab-content {
    display: none;
    padding: 2rem;
}

.tab-content.active {
    display: block;
}

.form-section {
    margin-bottom: 2rem;
}

.section-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.section-header h2 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-header p {
    margin: 0;
    color: #6c757d;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.verified-badge {
    color: #28a745;
    font-size: 0.875rem;
    margin-left: 0.5rem;
}

.unverified-badge {
    color: #ffc107;
    font-size: 0.875rem;
    margin-left: 0.5rem;
}

.char-counter {
    text-align: right;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #6c757d;
}

.checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.5rem;
}

.password-input-group {
    position: relative;
}

.btn-toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.25rem;
}

.password-strength {
    margin-top: 0.5rem;
}

.strength-meter {
    height: 4px;
    background-color: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 0.25rem;
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
    font-size: 0.875rem;
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
    margin-top: 0.5rem;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.security-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.security-section h3 {
    margin: 0 0 1rem 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.two-factor-status {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: 8px;
}

.status-active {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-inactive {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.sessions-list {
    margin-top: 1rem;
}

.session-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 0.5rem;
}

.session-item.current {
    background: #d4edda;
    border-color: #c3e6cb;
}

.session-icon {
    font-size: 1.2rem;
    color: #6c757d;
}

.session-info {
    flex: 1;
}

.session-info strong {
    display: block;
    margin-bottom: 0.25rem;
}

.current-session {
    background: #28a745;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
}

.preference-section {
    margin-bottom: 2rem;
}

.preference-section h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
    font-size: 1.2rem;
}

.notification-preferences,
.privacy-preferences {
    display: grid;
    gap: 1rem;
}

.form-check-label {
    cursor: pointer;
}

.form-check-label strong {
    display: block;
    margin-bottom: 0.25rem;
}

.form-check-label small {
    color: #6c757d;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}

.actions-right {
    display: flex;
    gap: 1rem;
}

.avatar-preview {
    text-align: center;
    margin-top: 1rem;
}

.avatar-preview img {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
}

.preview-actions {
    margin-top: 0.5rem;
}

.delete-consequences {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.delete-consequences li {
    margin-bottom: 0.5rem;
    color: #dc3545;
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-stats {
        justify-content: center;
    }
    
    .tab-navigation {
        flex-direction: column;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 1rem;
    }
    
    .actions-right {
        width: 100%;
        justify-content: center;
    }
}
`;

// Inject additional styles
if (document.head) {
    const styleSheet = document.createElement('style');
    styleSheet.textContent = additionalStyles;
    document.head.appendChild(styleSheet);
}