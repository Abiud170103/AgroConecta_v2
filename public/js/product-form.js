class ProductForm {
    constructor() {
        this.form = document.getElementById('productForm');
        this.isEdit = document.querySelector('[name="product_id"]') !== null;
        this.maxImages = 8;
        this.maxFileSize = 5 * 1024 * 1024; // 5MB
        this.allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupValidation();
        this.initializeImageGallery();
    }

    setupEventListeners() {
        // Formulario principal
        this.form.addEventListener('submit', this.handleSubmit.bind(this));
        
        // Editor de descripción
        this.setupDescriptionEditor();
        
        // Subida de imágenes
        this.setupImageUploads();
        
        // Etiquetas
        this.setupTagsInput();
        
        // Contador de caracteres
        this.setupCharCounters();
        
        // Validación en tiempo real
        this.setupRealTimeValidation();
        
        // Botones especiales
        this.setupSpecialButtons();
    }

    initializeComponents() {
        // Auto-guardar borrador cada 2 minutos
        this.setupAutoSave();
        
        // Previsualización de precio
        this.setupPricePreview();
        
        // Sugerencias automáticas
        this.setupAutoSuggestions();
    }

    setupDescriptionEditor() {
        const editor = document.getElementById('descripcion_editor');
        const textarea = document.getElementById('descripcion');
        const toolbar = document.querySelector('.editor-toolbar');

        if (!editor || !textarea) return;

        // Sincronizar contenido
        editor.addEventListener('input', () => {
            textarea.value = editor.innerHTML;
            this.validateField(textarea);
        });

        // Botones de formato
        toolbar.addEventListener('click', (e) => {
            if (e.target.closest('[data-action]')) {
                e.preventDefault();
                const action = e.target.closest('[data-action]').dataset.action;
                this.executeEditorCommand(action);
            }
        });

        // Placeholder
        editor.addEventListener('focus', () => {
            if (editor.textContent.trim() === '') {
                editor.classList.add('focused');
            }
        });

        editor.addEventListener('blur', () => {
            if (editor.textContent.trim() === '') {
                editor.classList.remove('focused');
            }
        });
    }

    executeEditorCommand(command) {
        document.execCommand(command, false, null);
        document.getElementById('descripcion_editor').focus();
    }

    setupImageUploads() {
        // Imagen principal
        this.setupMainImageUpload();
        
        // Imágenes adicionales
        this.setupAdditionalImagesUpload();
    }

    setupMainImageUpload() {
        const uploadArea = document.getElementById('mainImageUpload');
        const input = document.getElementById('imagen_principal');

        if (!uploadArea || !input) return;

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleMainImageFile(files[0]);
            }
        });

        // Click to upload
        uploadArea.addEventListener('click', () => {
            input.click();
        });

        // File input change
        input.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.handleMainImageFile(e.target.files[0]);
            }
        });
    }

    async handleMainImageFile(file) {
        const validation = this.validateImageFile(file);
        if (!validation.valid) {
            this.showAlert(validation.message, 'error');
            return;
        }

        try {
            // Crear preview
            const preview = await this.createImagePreview(file);
            this.displayMainImagePreview(preview, file.name);
            
        } catch (error) {
            this.showAlert('Error al procesar la imagen: ' + error.message, 'error');
        }
    }

    displayMainImagePreview(previewUrl, fileName) {
        const uploadArea = document.getElementById('mainImageUpload');
        
        uploadArea.innerHTML = `
            <div class="uploaded-image">
                <img src="${previewUrl}" alt="${fileName}">
                <div class="image-overlay">
                    <button type="button" class="btn btn-sm btn-info" onclick="previewImage('${previewUrl}')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="productForm.removeMainImage()">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="image-info">
                    <small>${fileName}</small>
                </div>
            </div>
        `;
    }

    removeMainImage() {
        const uploadArea = document.getElementById('mainImageUpload');
        const input = document.getElementById('imagen_principal');
        
        uploadArea.innerHTML = `
            <div class="upload-placeholder">
                <i class="fas fa-cloud-upload-alt"></i>
                <h4>Imagen Principal</h4>
                <p>Arrastra una imagen o haz clic para seleccionar</p>
                <div class="upload-specs">
                    <small>JPG, PNG, WEBP • Máx. 5MB • Min. 400x400px</small>
                </div>
            </div>
        `;
        
        input.value = '';
    }

    setupAdditionalImagesUpload() {
        const container = document.getElementById('additionalImagesGrid');
        if (!container) return;

        // Event delegation para nuevos inputs
        container.addEventListener('change', (e) => {
            if (e.target.classList.contains('additional-image-input')) {
                this.handleAdditionalImages(e.target.files, e.target);
            }
        });

        // Drag and drop para slots existentes
        container.addEventListener('dragover', (e) => {
            e.preventDefault();
            const slot = e.target.closest('.image-upload-slot');
            if (slot) slot.classList.add('drag-over');
        });

        container.addEventListener('dragleave', (e) => {
            const slot = e.target.closest('.image-upload-slot');
            if (slot) slot.classList.remove('drag-over');
        });

        container.addEventListener('drop', (e) => {
            e.preventDefault();
            const slot = e.target.closest('.image-upload-slot');
            if (slot) {
                slot.classList.remove('drag-over');
                const input = slot.querySelector('input[type="file"]');
                const files = e.dataTransfer.files;
                
                if (input && files.length > 0) {
                    // Simular selección de archivos
                    const dt = new DataTransfer();
                    for (const file of files) {
                        dt.items.add(file);
                    }
                    input.files = dt.files;
                    this.handleAdditionalImages(files, input);
                }
            }
        });
    }

    async handleAdditionalImages(files, inputElement) {
        const container = document.getElementById('additionalImagesGrid');
        const currentImages = container.querySelectorAll('.image-item').length;
        
        if (currentImages + files.length > this.maxImages) {
            this.showAlert(`Máximo ${this.maxImages} imágenes permitidas`, 'warning');
            return;
        }

        for (const file of files) {
            const validation = this.validateImageFile(file);
            if (!validation.valid) {
                this.showAlert(validation.message, 'error');
                continue;
            }

            try {
                const preview = await this.createImagePreview(file);
                this.addAdditionalImagePreview(preview, file.name, file);
            } catch (error) {
                this.showAlert('Error al procesar imagen: ' + error.message, 'error');
            }
        }

        // Limpiar input y agregar nuevo slot si es necesario
        inputElement.value = '';
        this.ensureImageUploadSlot();
    }

    addAdditionalImagePreview(previewUrl, fileName, file) {
        const container = document.getElementById('additionalImagesGrid');
        const uploadSlot = container.querySelector('.image-upload-slot');
        
        const imageItem = document.createElement('div');
        imageItem.className = 'image-item';
        imageItem.dataset.fileName = fileName;
        
        imageItem.innerHTML = `
            <div class="image-preview">
                <img src="${previewUrl}" alt="${fileName}">
                <div class="image-actions">
                    <button type="button" class="btn btn-sm btn-primary" onclick="productForm.setAsMainImage('${previewUrl}', '${fileName}')">
                        <i class="fas fa-star"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-info" onclick="previewImage('${previewUrl}')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="productForm.removeAdditionalImage(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="image-sort-handle">
                    <i class="fas fa-grip-vertical"></i>
                </div>
            </div>
            <div class="image-info">
                <small>${fileName}</small>
            </div>
        `;

        // Crear input hidden con los datos del archivo
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'file';
        hiddenInput.name = 'new_images[]';
        hiddenInput.style.display = 'none';
        
        // Transferir el archivo al input hidden
        const dt = new DataTransfer();
        dt.items.add(file);
        hiddenInput.files = dt.files;
        
        imageItem.appendChild(hiddenInput);
        
        // Insertar antes del slot de subida
        container.insertBefore(imageItem, uploadSlot);
        
        // Inicializar drag and drop para reordenar
        this.initializeSortable();
    }

    removeAdditionalImage(button) {
        const imageItem = button.closest('.image-item');
        if (imageItem) {
            imageItem.remove();
            this.ensureImageUploadSlot();
        }
    }

    setAsMainImage(previewUrl, fileName) {
        // Implementar lógica para establecer como imagen principal
        this.displayMainImagePreview(previewUrl, fileName);
        this.showAlert('Imagen establecida como principal', 'success');
    }

    ensureImageUploadSlot() {
        const container = document.getElementById('additionalImagesGrid');
        const currentSlots = container.querySelectorAll('.image-upload-slot');
        const currentImages = container.querySelectorAll('.image-item').length;
        
        if (currentSlots.length === 0 && currentImages < this.maxImages) {
            const slot = document.createElement('div');
            slot.className = 'image-upload-slot';
            slot.innerHTML = `
                <div class="upload-placeholder small">
                    <i class="fas fa-plus"></i>
                    <p>Agregar imagen</p>
                </div>
                <input type="file" name="imagenes_adicionales[]" accept="image/*" class="file-input additional-image-input" multiple>
            `;
            container.appendChild(slot);
        }
    }

    initializeSortable() {
        // Implementar funcionalidad de reordenamiento drag and drop
        // Usar SortableJS o implementación custom
    }

    setupTagsInput() {
        const tagInput = document.getElementById('tagInput');
        const tagsDisplay = document.getElementById('tagsDisplay');
        const hiddenInput = document.getElementById('etiquetas');

        if (!tagInput || !tagsDisplay) return;

        tagInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const tag = tagInput.value.trim();
                if (tag && !this.hasTag(tag)) {
                    this.addTag(tag);
                    tagInput.value = '';
                }
            }
        });

        tagInput.addEventListener('blur', () => {
            const tag = tagInput.value.trim();
            if (tag && !this.hasTag(tag)) {
                this.addTag(tag);
                tagInput.value = '';
            }
        });
    }

    addTag(tagText) {
        const tagsDisplay = document.getElementById('tagsDisplay');
        const hiddenInput = document.getElementById('etiquetas');
        
        const tagElement = document.createElement('span');
        tagElement.className = 'tag-item';
        tagElement.innerHTML = `
            ${tagText}
            <button type="button" onclick="productForm.removeTag(this)">×</button>
        `;
        
        tagsDisplay.appendChild(tagElement);
        this.updateTagsInput();
    }

    removeTag(button) {
        button.parentElement.remove();
        this.updateTagsInput();
    }

    hasTag(tagText) {
        const existingTags = document.querySelectorAll('.tag-item');
        return Array.from(existingTags).some(tag => 
            tag.textContent.trim().replace('×', '') === tagText
        );
    }

    updateTagsInput() {
        const tags = Array.from(document.querySelectorAll('.tag-item'))
            .map(tag => tag.textContent.trim().replace('×', ''));
        
        document.getElementById('etiquetas').value = tags.join(',');
    }

    setupCharCounters() {
        const fields = [
            { id: 'meta_title', max: 60 },
            { id: 'meta_description', max: 160 },
            { id: 'nombre', max: 255 },
            { id: 'descripcion_corta', max: 500 }
        ];

        fields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                element.addEventListener('input', () => {
                    this.updateCharCount(element, field.max);
                });
                
                // Inicializar contador
                this.updateCharCount(element, field.max);
            }
        });
    }

    updateCharCount(element, max) {
        const count = element.value.length;
        const counter = element.parentElement.querySelector('.char-count');
        
        if (counter) {
            counter.textContent = count;
            counter.parentElement.classList.toggle('text-warning', count > max * 0.8);
            counter.parentElement.classList.toggle('text-danger', count > max);
        }
    }

    setupRealTimeValidation() {
        const form = this.form;
        const inputs = form.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid')) {
                    this.validateField(input);
                }
            });
        });
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let message = '';

        // Validaciones específicas por campo
        switch (field.name) {
            case 'nombre':
                if (!value) {
                    isValid = false;
                    message = 'El nombre del producto es requerido';
                } else if (value.length < 3) {
                    isValid = false;
                    message = 'El nombre debe tener al menos 3 caracteres';
                }
                break;
                
            case 'descripcion':
                if (!value) {
                    isValid = false;
                    message = 'La descripción es requerida';
                } else if (value.length < 10) {
                    isValid = false;
                    message = 'La descripción debe tener al menos 10 caracteres';
                }
                break;
                
            case 'categoria_id':
                if (!value) {
                    isValid = false;
                    message = 'Selecciona una categoría';
                }
                break;
                
            case 'precio':
                if (!value || parseFloat(value) <= 0) {
                    isValid = false;
                    message = 'Ingresa un precio válido mayor a 0';
                }
                break;
                
            case 'stock':
                if (!value || parseInt(value) < 0) {
                    isValid = false;
                    message = 'El stock debe ser un número mayor o igual a 0';
                }
                break;
        }

        // Actualizar UI
        this.updateFieldValidation(field, isValid, message);
        return isValid;
    }

    updateFieldValidation(field, isValid, message) {
        const feedback = field.parentElement.querySelector('.form-feedback') || 
                        field.closest('.form-group').querySelector('.form-feedback');

        field.classList.remove('is-valid', 'is-invalid');
        field.classList.add(isValid ? 'is-valid' : 'is-invalid');

        if (feedback) {
            feedback.textContent = message;
            feedback.className = 'form-feedback ' + (isValid ? 'valid-feedback' : 'invalid-feedback');
        }
    }

    setupSpecialButtons() {
        // Botón guardar borrador
        window.saveDraft = () => {
            this.submitForm(true);
        };

        // Preview de imagen
        window.previewImage = (src) => {
            document.getElementById('previewImage').src = src;
            $('#imagePreviewModal').modal('show');
        };

        // Eliminar imagen principal
        window.removeMainImage = () => {
            this.removeMainImage();
        };
    }

    setupAutoSave() {
        let autoSaveTimer;
        const form = this.form;
        
        form.addEventListener('input', () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                this.autoSave();
            }, 120000); // 2 minutos
        });
    }

    autoSave() {
        if (this.isEdit) return; // Solo para productos nuevos
        
        const formData = new FormData(this.form);
        formData.set('estado', 'borrador');
        
        // Guardar en localStorage como respaldo
        const formObject = {};
        for (let [key, value] of formData.entries()) {
            formObject[key] = value;
        }
        
        localStorage.setItem('product_draft', JSON.stringify(formObject));
        this.showAlert('Borrador guardado automáticamente', 'info', 3000);
    }

    setupPricePreview() {
        const precioActual = document.getElementById('precio');
        const precioAnterior = document.getElementById('precio_anterior');
        
        if (precioActual) {
            precioActual.addEventListener('input', this.updatePricePreview.bind(this));
        }
        if (precioAnterior) {
            precioAnterior.addEventListener('input', this.updatePricePreview.bind(this));
        }
    }

    updatePricePreview() {
        const actual = parseFloat(document.getElementById('precio').value) || 0;
        const anterior = parseFloat(document.getElementById('precio_anterior').value) || 0;
        
        // Calcular descuento
        if (anterior > actual && actual > 0) {
            const descuento = Math.round(((anterior - actual) / anterior) * 100);
            this.showPriceInfo(`Descuento: ${descuento}%`, 'success');
        } else if (anterior > 0 && anterior <= actual) {
            this.showPriceInfo('El precio anterior debe ser mayor al actual', 'warning');
        }
    }

    showPriceInfo(message, type) {
        // Mostrar información de precio temporal
        const precioGroup = document.getElementById('precio').closest('.form-group');
        let priceInfo = precioGroup.querySelector('.price-info');
        
        if (!priceInfo) {
            priceInfo = document.createElement('small');
            priceInfo.className = 'price-info form-text';
            precioGroup.appendChild(priceInfo);
        }
        
        priceInfo.className = `price-info form-text text-${type}`;
        priceInfo.textContent = message;
        
        setTimeout(() => {
            if (priceInfo) priceInfo.remove();
        }, 5000);
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        if (!this.validateForm()) {
            this.showAlert('Por favor corrige los errores en el formulario', 'error');
            return;
        }

        await this.submitForm(false);
    }

    validateForm() {
        const requiredFields = this.form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    async submitForm(isDraft = false) {
        const formData = new FormData(this.form);
        
        if (isDraft) {
            formData.set('estado', 'borrador');
        }

        // Procesar descripción del editor
        const descEditor = document.getElementById('descripcion_editor');
        if (descEditor) {
            formData.set('descripcion', descEditor.innerHTML);
        }

        try {
            this.showLoading(true);
            
            const response = await fetch(this.form.dataset.submitUrl, {
                method: this.form.dataset.method || 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.showAlert(result.message, 'success');
                
                if (result.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                } else if (!isDraft) {
                    setTimeout(() => {
                        window.location.href = '/vendor/products';
                    }, 1500);
                }
                
                // Limpiar autoguardado
                localStorage.removeItem('product_draft');
                
            } else {
                throw new Error(result.message || 'Error en el servidor');
            }

        } catch (error) {
            this.showAlert('Error al guardar el producto: ' + error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    // Utilidades
    validateImageFile(file) {
        if (!file) {
            return { valid: false, message: 'No se seleccionó archivo' };
        }

        if (!this.allowedTypes.includes(file.type)) {
            return { valid: false, message: 'Tipo de archivo no permitido. Use JPG, PNG, WEBP o GIF' };
        }

        if (file.size > this.maxFileSize) {
            return { valid: false, message: 'Archivo muy grande. Máximo 5MB' };
        }

        return { valid: true };
    }

    createImagePreview(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => resolve(e.target.result);
            reader.onerror = () => reject(new Error('Error al leer archivo'));
            reader.readAsDataURL(file);
        });
    }

    showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = show ? 'flex' : 'none';
        }
    }

    showAlert(message, type = 'info', duration = 5000) {
        const alertsContainer = document.getElementById('alertsContent');
        if (!alertsContainer) return;

        const alertId = 'alert_' + Date.now();
        const alertElement = document.createElement('div');
        alertElement.id = alertId;
        alertElement.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        alertElement.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;

        alertsContainer.appendChild(alertElement);

        if (duration > 0) {
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    alert.remove();
                }
            }, duration);
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.productForm = new ProductForm();
});