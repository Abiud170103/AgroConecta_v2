class ImageGallery {
    constructor(options = {}) {
        this.maxImages = options.maxImages || 8;
        this.maxFileSize = options.maxFileSize || 5 * 1024 * 1024; // 5MB
        this.allowedTypes = options.allowedTypes || ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        this.compressionQuality = options.compressionQuality || 0.8;
        this.maxDimensions = options.maxDimensions || { width: 1200, height: 1200 };
        this.thumbnailSize = options.thumbnailSize || { width: 300, height: 300 };
        
        this.images = [];
        this.draggedElement = null;
        this.dragCounter = 0;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadExistingImages();
        this.initializeSortable();
    }

    setupEventListeners() {
        // Global drag and drop
        document.addEventListener('dragenter', this.handleDragEnter.bind(this));
        document.addEventListener('dragleave', this.handleDragLeave.bind(this));
        document.addEventListener('dragover', this.handleDragOver.bind(this));
        document.addEventListener('drop', this.handleGlobalDrop.bind(this));

        // Paste para pegar imágenes
        document.addEventListener('paste', this.handlePaste.bind(this));

        // Teclas de acceso directo
        document.addEventListener('keydown', this.handleKeydown.bind(this));
    }

    loadExistingImages() {
        // Cargar imágenes existentes si estamos editando
        const existingImages = document.querySelectorAll('[data-existing-image]');
        
        existingImages.forEach((imgElement, index) => {
            const imageData = {
                id: imgElement.dataset.imageId,
                url: imgElement.src,
                filename: imgElement.dataset.filename || `image_${index + 1}`,
                isExisting: true,
                isPrimary: imgElement.dataset.isPrimary === 'true',
                order: parseInt(imgElement.dataset.order) || index
            };

            this.images.push(imageData);
        });

        this.renderGallery();
    }

    async handleFileSelection(files, targetContainer = null) {
        const fileArray = Array.from(files);
        
        for (const file of fileArray) {
            if (this.images.length >= this.maxImages) {
                this.showNotification(`Máximo ${this.maxImages} imágenes permitidas`, 'warning');
                break;
            }

            const validation = this.validateFile(file);
            if (!validation.isValid) {
                this.showNotification(validation.message, 'error');
                continue;
            }

            try {
                await this.processAndAddImage(file);
            } catch (error) {
                this.showNotification(`Error al procesar ${file.name}: ${error.message}`, 'error');
            }
        }

        this.renderGallery();
        this.updateFormInputs();
    }

    validateFile(file) {
        if (!this.allowedTypes.includes(file.type)) {
            return {
                isValid: false,
                message: `Tipo de archivo no soportado: ${file.name}. Use JPG, PNG, WEBP o GIF`
            };
        }

        if (file.size > this.maxFileSize) {
            const sizeMB = (this.maxFileSize / (1024 * 1024)).toFixed(1);
            return {
                isValid: false,
                message: `Archivo muy grande: ${file.name}. Máximo ${sizeMB}MB`
            };
        }

        // Verificar duplicados por nombre y tamaño
        const isDuplicate = this.images.some(img => 
            img.filename === file.name && img.size === file.size
        );

        if (isDuplicate) {
            return {
                isValid: false,
                message: `Imagen duplicada: ${file.name}`
            };
        }

        return { isValid: true };
    }

    async processAndAddImage(file) {
        // Mostrar indicador de carga
        const loadingId = this.showLoadingIndicator(`Procesando ${file.name}...`);

        try {
            // Leer archivo como data URL
            const originalDataUrl = await this.readFileAsDataURL(file);
            
            // Crear imagen para obtener dimensiones
            const img = await this.createImageElement(originalDataUrl);
            
            // Redimensionar si es necesario
            const { canvas, compressedBlob } = await this.resizeAndCompressImage(img, file);
            
            // Crear thumbnail
            const thumbnailDataUrl = await this.createThumbnail(img);
            
            // Crear objeto de imagen
            const imageData = {
                id: this.generateImageId(),
                file: compressedBlob || file,
                originalFile: file,
                url: canvas ? canvas.toDataURL(file.type, this.compressionQuality) : originalDataUrl,
                thumbnailUrl: thumbnailDataUrl,
                filename: file.name,
                size: compressedBlob ? compressedBlob.size : file.size,
                dimensions: {
                    width: img.naturalWidth,
                    height: img.naturalHeight
                },
                isExisting: false,
                isPrimary: this.images.length === 0, // Primera imagen como principal
                order: this.images.length,
                uploadStatus: 'ready'
            };

            this.images.push(imageData);
            
        } finally {
            this.hideLoadingIndicator(loadingId);
        }
    }

    async resizeAndCompressImage(img, file) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        const { width: originalWidth, height: originalHeight } = img;
        const { width: maxWidth, height: maxHeight } = this.maxDimensions;
        
        // Calcular nuevas dimensiones manteniendo aspecto
        let newWidth = originalWidth;
        let newHeight = originalHeight;
        
        if (originalWidth > maxWidth || originalHeight > maxHeight) {
            const ratio = Math.min(maxWidth / originalWidth, maxHeight / originalHeight);
            newWidth = Math.round(originalWidth * ratio);
            newHeight = Math.round(originalHeight * ratio);
        }
        
        // Solo redimensionar si las dimensiones cambiaron
        if (newWidth !== originalWidth || newHeight !== originalHeight) {
            canvas.width = newWidth;
            canvas.height = newHeight;
            
            // Configurar canvas para mejor calidad
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            
            // Dibujar imagen redimensionada
            ctx.drawImage(img, 0, 0, newWidth, newHeight);
            
            // Convertir a blob comprimido
            const compressedBlob = await this.canvasToBlob(canvas, file.type, this.compressionQuality);
            
            return { canvas, compressedBlob };
        }
        
        return { canvas: null, compressedBlob: null };
    }

    async createThumbnail(img) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        const { width: thumbWidth, height: thumbHeight } = this.thumbnailSize;
        canvas.width = thumbWidth;
        canvas.height = thumbHeight;
        
        // Calcular crop para mantener aspecto
        const imgRatio = img.naturalWidth / img.naturalHeight;
        const thumbRatio = thumbWidth / thumbHeight;
        
        let sourceX = 0, sourceY = 0, sourceWidth = img.naturalWidth, sourceHeight = img.naturalHeight;
        
        if (imgRatio > thumbRatio) {
            // Imagen más ancha, recortar horizontalmente
            sourceWidth = img.naturalHeight * thumbRatio;
            sourceX = (img.naturalWidth - sourceWidth) / 2;
        } else {
            // Imagen más alta, recortar verticalmente
            sourceHeight = img.naturalWidth / thumbRatio;
            sourceY = (img.naturalHeight - sourceHeight) / 2;
        }
        
        ctx.drawImage(img, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, thumbWidth, thumbHeight);
        
        return canvas.toDataURL('image/jpeg', 0.7);
    }

    renderGallery() {
        const container = document.getElementById('imagesGalleryContainer') || this.createGalleryContainer();
        
        // Ordenar imágenes
        const sortedImages = [...this.images].sort((a, b) => a.order - b.order);
        
        container.innerHTML = `
            <div class="gallery-header">
                <h4><i class="fas fa-images"></i> Galería de Imágenes (${this.images.length}/${this.maxImages})</h4>
                <div class="gallery-actions">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="imageGallery.selectFiles()">
                        <i class="fas fa-plus"></i> Agregar Imágenes
                    </button>
                    ${this.images.length > 0 ? `
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imageGallery.selectAll()">
                            <i class="fas fa-check-square"></i> Seleccionar Todo
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="imageGallery.removeSelected()">
                            <i class="fas fa-trash"></i> Eliminar Seleccionadas
                        </button>
                    ` : ''}
                </div>
            </div>
            
            <div class="gallery-grid">
                ${sortedImages.map(img => this.renderImageCard(img)).join('')}
                
                ${this.images.length < this.maxImages ? `
                    <div class="add-image-card" onclick="imageGallery.selectFiles()">
                        <div class="add-image-content">
                            <i class="fas fa-plus-circle"></i>
                            <span>Agregar Imagen</span>
                        </div>
                    </div>
                ` : ''}
            </div>
            
            ${this.images.length > 0 ? `
                <div class="gallery-footer">
                    <div class="gallery-info">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Arrastra las imágenes para reordenar. Clic en la estrella para marcar como principal.
                        </small>
                    </div>
                </div>
            ` : ''}
            
            <input type="file" id="galleryFileInput" multiple accept="image/*" style="display: none;">
        `;

        // Event listeners para las nuevas cartas
        this.attachCardEventListeners();
        
        // Reinicializar sortable
        this.initializeSortable();
    }

    renderImageCard(imageData) {
        const { id, url, thumbnailUrl, filename, size, dimensions, isPrimary, isExisting, uploadStatus } = imageData;
        const sizeKB = Math.round(size / 1024);
        
        return `
            <div class="image-card" data-image-id="${id}" data-is-existing="${isExisting}">
                <div class="image-card-content">
                    <div class="image-preview" onclick="imageGallery.previewImage('${id}')">
                        <img src="${thumbnailUrl || url}" alt="${filename}" loading="lazy">
                        <div class="image-overlay">
                            <button type="button" class="overlay-btn preview-btn" title="Previsualizar">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="overlay-btn download-btn" onclick="event.stopPropagation(); imageGallery.downloadImage('${id}')" title="Descargar">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                        ${uploadStatus === 'uploading' ? '<div class="upload-progress"><div class="progress-bar"></div></div>' : ''}
                    </div>
                    
                    <div class="image-info">
                        <div class="image-title" title="${filename}">${filename}</div>
                        <div class="image-meta">
                            <span>${dimensions.width}x${dimensions.height}</span>
                            <span>${sizeKB}KB</span>
                        </div>
                    </div>
                    
                    <div class="image-actions">
                        <label class="image-checkbox">
                            <input type="checkbox" onchange="imageGallery.toggleSelection('${id}')">
                            <span class="checkmark"></span>
                        </label>
                        
                        <button type="button" class="action-btn ${isPrimary ? 'primary' : ''}" 
                                onclick="imageGallery.setPrimary('${id}')" 
                                title="${isPrimary ? 'Imagen principal' : 'Marcar como principal'}">
                            <i class="fas fa-star"></i>
                        </button>
                        
                        <button type="button" class="action-btn" onclick="imageGallery.editImageData('${id}')" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <button type="button" class="action-btn danger" onclick="imageGallery.removeImage('${id}')" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                        
                        <div class="drag-handle" title="Arrastar para reordenar">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                    </div>
                </div>
                
                ${isPrimary ? '<div class="primary-badge"><i class="fas fa-star"></i> Principal</div>' : ''}
            </div>
        `;
    }

    attachCardEventListeners() {
        const fileInput = document.getElementById('galleryFileInput');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    this.handleFileSelection(e.target.files);
                    e.target.value = ''; // Reset input
                }
            });
        }
    }

    createGalleryContainer() {
        const container = document.createElement('div');
        container.id = 'imagesGalleryContainer';
        container.className = 'images-gallery-container';
        
        // Buscar un lugar apropiado para insertar la galería
        const targetElement = document.getElementById('imageUploadSection') || 
                             document.querySelector('.form-section.images') ||
                             document.querySelector('.section-content');
        
        if (targetElement) {
            targetElement.appendChild(container);
        } else {
            document.body.appendChild(container);
        }
        
        return container;
    }

    initializeSortable() {
        const galleryGrid = document.querySelector('.gallery-grid');
        if (!galleryGrid) return;

        // Implementar sortable simple sin dependencias externas
        let draggedElement = null;
        
        const imageCards = galleryGrid.querySelectorAll('.image-card');
        
        imageCards.forEach(card => {
            const dragHandle = card.querySelector('.drag-handle');
            
            if (dragHandle) {
                dragHandle.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    card.draggable = true;
                    draggedElement = card;
                });
                
                card.addEventListener('dragstart', (e) => {
                    if (draggedElement === card) {
                        card.classList.add('dragging');
                        e.dataTransfer.effectAllowed = 'move';
                    } else {
                        e.preventDefault();
                    }
                });
                
                card.addEventListener('dragend', () => {
                    card.draggable = false;
                    card.classList.remove('dragging');
                    draggedElement = null;
                });
                
                card.addEventListener('dragover', (e) => {
                    if (draggedElement && draggedElement !== card) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        
                        const rect = card.getBoundingClientRect();
                        const midpoint = rect.left + rect.width / 2;
                        
                        if (e.clientX < midpoint) {
                            galleryGrid.insertBefore(draggedElement, card);
                        } else {
                            galleryGrid.insertBefore(draggedElement, card.nextSibling);
                        }
                    }
                });
                
                card.addEventListener('drop', (e) => {
                    e.preventDefault();
                    if (draggedElement) {
                        this.updateImageOrder();
                    }
                });
            }
        });
    }

    updateImageOrder() {
        const cards = document.querySelectorAll('.image-card');
        
        cards.forEach((card, index) => {
            const imageId = card.dataset.imageId;
            const image = this.images.find(img => img.id === imageId);
            if (image) {
                image.order = index;
            }
        });
        
        this.updateFormInputs();
    }

    // Métodos de interacción
    selectFiles() {
        const fileInput = document.getElementById('galleryFileInput');
        if (fileInput) {
            fileInput.click();
        }
    }

    previewImage(imageId) {
        const image = this.images.find(img => img.id === imageId);
        if (!image) return;

        // Crear modal de previsualización
        const modal = document.createElement('div');
        modal.className = 'image-preview-modal';
        modal.innerHTML = `
            <div class="modal-backdrop" onclick="this.parentElement.remove()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h5>${image.filename}</h5>
                    <button type="button" class="close-btn" onclick="this.closest('.image-preview-modal').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <img src="${image.url}" alt="${image.filename}">
                    <div class="image-details">
                        <div class="detail-item">
                            <strong>Dimensiones:</strong> ${image.dimensions.width} x ${image.dimensions.height}px
                        </div>
                        <div class="detail-item">
                            <strong>Tamaño:</strong> ${Math.round(image.size / 1024)}KB
                        </div>
                        <div class="detail-item">
                            <strong>Tipo:</strong> ${image.filename.split('.').pop().toUpperCase()}
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-primary" onclick="imageGallery.setPrimary('${imageId}'); this.closest('.image-preview-modal').remove();">
                        <i class="fas fa-star"></i> Marcar como Principal
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="imageGallery.downloadImage('${imageId}')">
                        <i class="fas fa-download"></i> Descargar
                    </button>
                    <button type="button" class="btn btn-danger" onclick="imageGallery.removeImage('${imageId}'); this.closest('.image-preview-modal').remove();">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        
        // Cerrar con ESC
        const escListener = (e) => {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', escListener);
            }
        };
        document.addEventListener('keydown', escListener);
    }

    setPrimary(imageId) {
        // Remover primary de todas las imágenes
        this.images.forEach(img => img.isPrimary = false);
        
        // Marcar la seleccionada como primary
        const image = this.images.find(img => img.id === imageId);
        if (image) {
            image.isPrimary = true;
            this.renderGallery();
            this.updateFormInputs();
            this.showNotification('Imagen principal actualizada', 'success');
        }
    }

    removeImage(imageId) {
        const imageIndex = this.images.findIndex(img => img.id === imageId);
        if (imageIndex === -1) return;

        const image = this.images[imageIndex];
        
        // Confirmar eliminación
        if (!confirm(`¿Eliminar la imagen "${image.filename}"?`)) return;

        // Si era la principal, marcar la siguiente como principal
        if (image.isPrimary && this.images.length > 1) {
            const nextImage = this.images.find(img => img.id !== imageId);
            if (nextImage) nextImage.isPrimary = true;
        }

        this.images.splice(imageIndex, 1);
        this.renderGallery();
        this.updateFormInputs();
        this.showNotification('Imagen eliminada', 'info');
    }

    toggleSelection(imageId) {
        // Implementar lógica de selección múltiple
        const checkbox = document.querySelector(`[data-image-id="${imageId}"] input[type="checkbox"]`);
        const card = document.querySelector(`[data-image-id="${imageId}"]`);
        
        if (checkbox && card) {
            card.classList.toggle('selected', checkbox.checked);
        }
    }

    selectAll() {
        const checkboxes = document.querySelectorAll('.image-card input[type="checkbox"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
            cb.dispatchEvent(new Event('change'));
        });
    }

    removeSelected() {
        const selectedCards = document.querySelectorAll('.image-card.selected');
        if (selectedCards.length === 0) {
            this.showNotification('No hay imágenes seleccionadas', 'warning');
            return;
        }

        if (!confirm(`¿Eliminar ${selectedCards.length} imagen(es) seleccionada(s)?`)) return;

        selectedCards.forEach(card => {
            const imageId = card.dataset.imageId;
            this.removeImage(imageId);
        });
    }

    downloadImage(imageId) {
        const image = this.images.find(img => img.id === imageId);
        if (!image) return;

        const link = document.createElement('a');
        link.href = image.url;
        link.download = image.filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    editImageData(imageId) {
        const image = this.images.find(img => img.id === imageId);
        if (!image) return;

        // Crear modal de edición
        const modal = document.createElement('div');
        modal.className = 'image-edit-modal';
        modal.innerHTML = `
            <div class="modal-backdrop" onclick="this.parentElement.remove()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Editar Imagen</h5>
                    <button type="button" class="close-btn" onclick="this.closest('.image-edit-modal').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre del archivo:</label>
                        <input type="text" class="form-control" id="editImageName" value="${image.filename}">
                    </div>
                    <div class="form-group">
                        <label>Descripción (alt text):</label>
                        <input type="text" class="form-control" id="editImageAlt" value="${image.alt || ''}">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-primary" onclick="imageGallery.saveImageData('${imageId}')">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="this.closest('.image-edit-modal').remove()">
                        Cancelar
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        document.getElementById('editImageName').focus();
    }

    saveImageData(imageId) {
        const image = this.images.find(img => img.id === imageId);
        if (!image) return;

        const newName = document.getElementById('editImageName').value.trim();
        const newAlt = document.getElementById('editImageAlt').value.trim();

        if (newName) {
            image.filename = newName;
            image.alt = newAlt;
            
            this.renderGallery();
            this.updateFormInputs();
            
            document.querySelector('.image-edit-modal').remove();
            this.showNotification('Datos de imagen actualizados', 'success');
        }
    }

    updateFormInputs() {
        // Actualizar inputs ocultos del formulario
        const form = document.getElementById('productForm');
        if (!form) return;

        // Limpiar inputs existentes
        form.querySelectorAll('input[name^="gallery_images"]').forEach(input => input.remove());
        form.querySelectorAll('input[name^="image_order"]').forEach(input => input.remove());

        // Crear nuevos inputs
        this.images.forEach((image, index) => {
            // Orden
            const orderInput = document.createElement('input');
            orderInput.type = 'hidden';
            orderInput.name = `image_order[${image.id}]`;
            orderInput.value = image.order;
            form.appendChild(orderInput);

            // Datos de imagen
            const dataInput = document.createElement('input');
            dataInput.type = 'hidden';
            dataInput.name = `gallery_images[${image.id}]`;
            dataInput.value = JSON.stringify({
                id: image.id,
                filename: image.filename,
                alt: image.alt || '',
                isPrimary: image.isPrimary,
                isExisting: image.isExisting,
                order: image.order
            });
            form.appendChild(dataInput);
        });

        // Imagen principal
        const primaryImage = this.images.find(img => img.isPrimary);
        const primaryInput = document.getElementById('imagen_principal_id') || document.createElement('input');
        primaryInput.type = 'hidden';
        primaryInput.name = 'imagen_principal_id';
        primaryInput.id = 'imagen_principal_id';
        primaryInput.value = primaryImage ? primaryImage.id : '';
        
        if (!primaryInput.parentNode) {
            form.appendChild(primaryInput);
        }
    }

    // Event handlers
    handleDragEnter(e) {
        e.preventDefault();
        this.dragCounter++;
        if (e.dataTransfer.types.includes('Files')) {
            document.body.classList.add('drag-active');
        }
    }

    handleDragLeave(e) {
        e.preventDefault();
        this.dragCounter--;
        if (this.dragCounter <= 0) {
            document.body.classList.remove('drag-active');
        }
    }

    handleDragOver(e) {
        e.preventDefault();
        if (e.dataTransfer.types.includes('Files')) {
            e.dataTransfer.dropEffect = 'copy';
        }
    }

    handleGlobalDrop(e) {
        e.preventDefault();
        this.dragCounter = 0;
        document.body.classList.remove('drag-active');
        
        if (e.dataTransfer.files.length > 0) {
            this.handleFileSelection(e.dataTransfer.files);
        }
    }

    handlePaste(e) {
        const items = e.clipboardData?.items;
        if (!items) return;

        const files = [];
        for (const item of items) {
            if (item.type.startsWith('image/')) {
                const file = item.getAsFile();
                if (file) files.push(file);
            }
        }

        if (files.length > 0) {
            e.preventDefault();
            this.handleFileSelection(files);
            this.showNotification(`${files.length} imagen(es) pegada(s)`, 'info');
        }
    }

    handleKeydown(e) {
        // Ctrl/Cmd + V para pegar
        if ((e.ctrlKey || e.metaKey) && e.key === 'v') {
            // El evento paste se encargará
            return;
        }

        // Supr para eliminar seleccionadas
        if (e.key === 'Delete' && !e.target.closest('input, textarea')) {
            e.preventDefault();
            this.removeSelected();
        }

        // Ctrl/Cmd + A para seleccionar todas
        if ((e.ctrlKey || e.metaKey) && e.key === 'a' && !e.target.closest('input, textarea')) {
            e.preventDefault();
            this.selectAll();
        }
    }

    // Utilidades
    generateImageId() {
        return 'img_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    readFileAsDataURL(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = e => resolve(e.target.result);
            reader.onerror = () => reject(new Error('Error al leer archivo'));
            reader.readAsDataURL(file);
        });
    }

    createImageElement(src) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = () => reject(new Error('Error al cargar imagen'));
            img.src = src;
        });
    }

    canvasToBlob(canvas, type, quality) {
        return new Promise(resolve => {
            canvas.toBlob(resolve, type, quality);
        });
    }

    showLoadingIndicator(message) {
        const id = 'loading_' + Date.now();
        const indicator = document.createElement('div');
        indicator.id = id;
        indicator.className = 'loading-indicator';
        indicator.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(indicator);
        return id;
    }

    hideLoadingIndicator(id) {
        const indicator = document.getElementById(id);
        if (indicator) {
            indicator.remove();
        }
    }

    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `gallery-notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
                <button type="button" class="close-notification" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        const container = document.getElementById('galleryNotifications') || this.createNotificationContainer();
        container.appendChild(notification);

        if (duration > 0) {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, duration);
        }
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'galleryNotifications';
        container.className = 'gallery-notifications';
        document.body.appendChild(container);
        return container;
    }

    // API pública
    getImages() {
        return [...this.images];
    }

    getPrimaryImage() {
        return this.images.find(img => img.isPrimary) || null;
    }

    addImages(files) {
        return this.handleFileSelection(files);
    }

    clearAll() {
        if (this.images.length === 0) return;
        
        if (confirm('¿Eliminar todas las imágenes?')) {
            this.images = [];
            this.renderGallery();
            this.updateFormInputs();
            this.showNotification('Todas las imágenes eliminadas', 'info');
        }
    }

    getGalleryStats() {
        const totalSize = this.images.reduce((sum, img) => sum + img.size, 0);
        return {
            count: this.images.length,
            maxCount: this.maxImages,
            totalSize,
            totalSizeMB: Math.round(totalSize / (1024 * 1024) * 100) / 100,
            hasPrimary: this.images.some(img => img.isPrimary)
        };
    }
}

// Inicializar la galería cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.imageGallery = new ImageGallery({
        maxImages: 8,
        maxFileSize: 5 * 1024 * 1024, // 5MB
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'],
        compressionQuality: 0.8,
        maxDimensions: { width: 1200, height: 1200 },
        thumbnailSize: { width: 300, height: 300 }
    });
});