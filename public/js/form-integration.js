// ===============================================
// Product Form Integration
// ===============================================

/**
 * Integración entre el formulario de productos y la galería de imágenes
 * Este archivo conecta ambos sistemas para trabajar de manera coordinada
 */

document.addEventListener('DOMContentLoaded', () => {
    // Esperar a que ambos sistemas estén inicializados
    const initializeIntegration = () => {
        if (typeof window.productForm === 'undefined' || typeof window.imageGallery === 'undefined') {
            setTimeout(initializeIntegration, 100);
            return;
        }

        setupFormGalleryIntegration();
    };

    initializeIntegration();
});

function setupFormGalleryIntegration() {
    // Conectar eventos entre formulario y galería
    setupImageValidation();
    setupFormSubmissionIntegration();
    setupAutoSaveIntegration();
    setupDraftRecovery();
}

function setupImageValidation() {
    // Validación de imágenes en tiempo real
    const originalValidateForm = window.productForm.validateForm;
    
    window.productForm.validateForm = function() {
        let isValid = originalValidateForm.call(this);
        
        // Validar que haya al menos una imagen
        const images = window.imageGallery.getImages();
        const imageValidation = document.getElementById('imageValidation') || createImageValidationMessage();
        
        if (images.length === 0) {
            imageValidation.textContent = 'Agrega al menos una imagen del producto';
            imageValidation.className = 'form-feedback invalid-feedback';
            imageValidation.style.display = 'block';
            isValid = false;
        } else {
            // Validar que haya una imagen principal
            const primaryImage = images.find(img => img.isPrimary);
            if (!primaryImage) {
                imageValidation.textContent = 'Selecciona una imagen como principal';
                imageValidation.className = 'form-feedback invalid-feedback';
                imageValidation.style.display = 'block';
                isValid = false;
            } else {
                imageValidation.style.display = 'none';
            }
        }
        
        return isValid;
    };
}

function createImageValidationMessage() {
    const validation = document.createElement('div');
    validation.id = 'imageValidation';
    validation.className = 'form-feedback';
    validation.style.display = 'none';
    
    const imageSection = document.getElementById('imagesGalleryContainer') || 
                        document.querySelector('.images-gallery-container');
    
    if (imageSection) {
        imageSection.appendChild(validation);
    }
    
    return validation;
}

function setupFormSubmissionIntegration() {
    // Integrar datos de imágenes en el envío del formulario
    const originalSubmitForm = window.productForm.submitForm;
    
    window.productForm.submitForm = async function(isDraft = false) {
        // Preparar datos de imágenes
        await prepareImageData();
        
        // Continuar con el envío original
        return originalSubmitForm.call(this, isDraft);
    };
}

async function prepareImageData() {
    const images = window.imageGallery.getImages();
    const form = document.getElementById('productForm');
    
    // Limpiar inputs de imágenes existentes
    form.querySelectorAll('input[name^="gallery_images"]').forEach(input => input.remove());
    form.querySelectorAll('input[name^="image_files"]').forEach(input => input.remove());
    
    // Crear inputs con datos de imágenes
    for (let i = 0; i < images.length; i++) {
        const image = images[i];
        
        // Datos de la imagen
        const imageDataInput = document.createElement('input');
        imageDataInput.type = 'hidden';
        imageDataInput.name = `gallery_images[${i}]`;
        imageDataInput.value = JSON.stringify({
            id: image.id,
            filename: image.filename,
            alt: image.alt || '',
            isPrimary: image.isPrimary,
            isExisting: image.isExisting,
            order: image.order
        });
        form.appendChild(imageDataInput);
        
        // Archivo de imagen (si es nueva)
        if (!image.isExisting && image.file) {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = `image_files[${i}]`;
            fileInput.style.display = 'none';
            
            // Transferir archivo
            const dt = new DataTransfer();
            dt.items.add(image.file);
            fileInput.files = dt.files;
            
            form.appendChild(fileInput);
        }
    }
    
    // Imagen principal
    const primaryImage = images.find(img => img.isPrimary);
    let primaryInput = form.querySelector('input[name="imagen_principal_id"]');
    
    if (!primaryInput) {
        primaryInput = document.createElement('input');
        primaryInput.type = 'hidden';
        primaryInput.name = 'imagen_principal_id';
        form.appendChild(primaryInput);
    }
    
    primaryInput.value = primaryImage ? primaryImage.id : '';
}

function setupAutoSaveIntegration() {
    // Incluir estado de la galería en el autoguardado
    const originalAutoSave = window.productForm.autoSave;
    
    window.productForm.autoSave = function() {
        // Guardar datos del formulario
        originalAutoSave.call(this);
        
        // Guardar estado de la galería
        const galleryState = {
            images: window.imageGallery.getImages().map(img => ({
                id: img.id,
                filename: img.filename,
                alt: img.alt,
                isPrimary: img.isPrimary,
                order: img.order,
                url: img.url,
                thumbnailUrl: img.thumbnailUrl
            })),
            timestamp: Date.now()
        };
        
        localStorage.setItem('product_gallery_draft', JSON.stringify(galleryState));
    };
}

function setupDraftRecovery() {
    // Recuperar estado de la galería si existe
    const savedGalleryState = localStorage.getItem('product_gallery_draft');
    
    if (savedGalleryState) {
        try {
            const galleryData = JSON.parse(savedGalleryState);
            
            // Verificar que no sea muy antiguo (más de 24 horas)
            const maxAge = 24 * 60 * 60 * 1000; // 24 horas
            const age = Date.now() - galleryData.timestamp;
            
            if (age < maxAge && galleryData.images.length > 0) {
                showDraftRecoveryModal(galleryData);
            }
        } catch (error) {
            console.warn('Error al recuperar borrador de galería:', error);
            localStorage.removeItem('product_gallery_draft');
        }
    }
}

function showDraftRecoveryModal(galleryData) {
    const modal = document.createElement('div');
    modal.className = 'draft-recovery-modal';
    modal.innerHTML = `
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h5><i class="fas fa-clock"></i> Recuperar Borrador</h5>
            </div>
            <div class="modal-body">
                <p>Se encontró un borrador guardado con <strong>${galleryData.images.length} imagen(es)</strong>.</p>
                <p><small class="text-muted">Guardado: ${new Date(galleryData.timestamp).toLocaleString()}</small></p>
                <p>¿Deseas recuperar las imágenes del borrador?</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-primary" onclick="recoverDraft(${JSON.stringify(galleryData).replace(/"/g, '&quot;')})">
                    <i class="fas fa-undo"></i> Recuperar
                </button>
                <button type="button" class="btn btn-secondary" onclick="discardDraft()">
                    Descartar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function recoverDraft(galleryData) {
    // Recuperar imágenes en la galería
    galleryData.images.forEach(imageData => {
        // Solo recuperar datos de imagen, no archivos
        if (imageData.url && imageData.url.startsWith('data:')) {
            window.imageGallery.images.push({
                ...imageData,
                isExisting: false,
                uploadStatus: 'recovered'
            });
        }
    });
    
    // Renderizar galería
    window.imageGallery.renderGallery();
    window.imageGallery.updateFormInputs();
    
    // Cerrar modal
    document.querySelector('.draft-recovery-modal').remove();
    
    // Mostrar notificación
    window.productForm.showAlert('Borrador recuperado exitosamente', 'success');
}

function discardDraft() {
    localStorage.removeItem('product_gallery_draft');
    document.querySelector('.draft-recovery-modal').remove();
}

// Utilidades de integración
const IntegrationUtils = {
    // Sincronizar validación entre sistemas
    syncValidation() {
        const formValid = window.productForm.validateForm();
        const imagesValid = window.imageGallery.getImages().length > 0;
        
        return formValid && imagesValid;
    },
    
    // Obtener datos completos del producto
    getCompleteProductData() {
        const formData = new FormData(document.getElementById('productForm'));
        const images = window.imageGallery.getImages();
        
        return {
            formData: Object.fromEntries(formData),
            images: images,
            stats: window.imageGallery.getGalleryStats()
        };
    },
    
    // Limpiar todos los borradores
    clearAllDrafts() {
        localStorage.removeItem('product_draft');
        localStorage.removeItem('product_gallery_draft');
        window.productForm.showAlert('Borradores eliminados', 'info');
    },
    
    // Validar formulario completo
    validateComplete() {
        const formValid = window.productForm.validateForm();
        const images = window.imageGallery.getImages();
        const imagesValid = images.length > 0;
        const primaryValid = images.some(img => img.isPrimary);
        
        if (!formValid) {
            window.productForm.showAlert('Completa todos los campos requeridos', 'error');
            return false;
        }
        
        if (!imagesValid) {
            window.productForm.showAlert('Agrega al menos una imagen del producto', 'error');
            return false;
        }
        
        if (!primaryValid) {
            window.productForm.showAlert('Selecciona una imagen como principal', 'error');
            return false;
        }
        
        return true;
    }
};

// Exportar utilidades globalmente
window.IntegrationUtils = IntegrationUtils;

// Agregar CSS para el modal de recuperación
const draftModalCSS = `
    <style>
        .draft-recovery-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10002;
            animation: fadeIn 0.3s ease;
        }
        
        .draft-recovery-modal .modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .draft-recovery-modal .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: slideUp 0.3s ease;
        }
        
        .draft-recovery-modal .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 20px 25px;
            border-radius: 12px 12px 0 0;
        }
        
        .draft-recovery-modal .modal-header h5 {
            margin: 0;
            color: #495057;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .draft-recovery-modal .modal-header i {
            color: #17a2b8;
        }
        
        .draft-recovery-modal .modal-body {
            padding: 25px;
        }
        
        .draft-recovery-modal .modal-body p {
            margin-bottom: 15px;
            color: #495057;
        }
        
        .draft-recovery-modal .modal-actions {
            display: flex;
            gap: 10px;
            padding: 20px 25px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 12px 12px;
            justify-content: flex-end;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
`;

document.head.insertAdjacentHTML('beforeend', draftModalCSS);