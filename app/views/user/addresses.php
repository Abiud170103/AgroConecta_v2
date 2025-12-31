<?php
// Obtener direcciones del usuario
$userAddresses = [];
$isGuest = !isset($_SESSION['user_id']);

if (!$isGuest) {
    $userAddresses = $this->addressModel->getUserAddresses($_SESSION['user_id']);
}

// Estados de México para el formulario
$estados = [
    'aguascalientes' => 'Aguascalientes',
    'baja_california' => 'Baja California',
    'baja_california_sur' => 'Baja California Sur',
    'campeche' => 'Campeche',
    'chiapas' => 'Chiapas',
    'chihuahua' => 'Chihuahua',
    'coahuila' => 'Coahuila',
    'colima' => 'Colima',
    'cdmx' => 'Ciudad de México',
    'durango' => 'Durango',
    'guanajuato' => 'Guanajuato',
    'guerrero' => 'Guerrero',
    'hidalgo' => 'Hidalgo',
    'jalisco' => 'Jalisco',
    'mexico' => 'Estado de México',
    'michoacan' => 'Michoacán',
    'morelos' => 'Morelos',
    'nayarit' => 'Nayarit',
    'nuevo_leon' => 'Nuevo León',
    'oaxaca' => 'Oaxaca',
    'puebla' => 'Puebla',
    'queretaro' => 'Querétaro',
    'quintana_roo' => 'Quintana Roo',
    'san_luis_potosi' => 'San Luis Potosí',
    'sinaloa' => 'Sinaloa',
    'sonora' => 'Sonora',
    'tabasco' => 'Tabasco',
    'tamaulipas' => 'Tamaulipas',
    'tlaxcala' => 'Tlaxcala',
    'veracruz' => 'Veracruz',
    'yucatan' => 'Yucatán',
    'zacatecas' => 'Zacatecas'
];
?>

<div class="addresses-page">
    <!-- === BREADCRUMB === -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/"><i class="fas fa-home"></i> Inicio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="/user/dashboard"><i class="fas fa-user"></i> Mi Cuenta</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-map-marker-alt"></i> Mis Direcciones
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- === HEADER === -->
    <div class="page-header">
        <div class="container">
            <div class="header-content">
                <div class="header-info">
                    <h1>
                        <i class="fas fa-map-marker-alt"></i>
                        Mis Direcciones
                    </h1>
                    <p>Gestiona las direcciones de entrega para tus pedidos</p>
                </div>
                
                <div class="header-actions">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addressModal">
                        <i class="fas fa-plus"></i>
                        Nueva Dirección
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- === CONTENIDO === -->
    <div class="page-content">
        <div class="container">
            
            <?php if (empty($userAddresses)): ?>
                <!-- Estado vacío -->
                <div class="empty-addresses">
                    <div class="empty-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    
                    <h3>No tienes direcciones guardadas</h3>
                    <p>Agrega una dirección para facilitar tus próximas compras</p>
                    
                    <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#addressModal">
                        <i class="fas fa-plus"></i>
                        Agregar Primera Dirección
                    </button>
                    
                    <div class="benefits-grid">
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div class="benefit-content">
                                <h5>Checkout Rápido</h5>
                                <p>Completa tus compras más rápido</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="benefit-content">
                                <h5>Fácil Edición</h5>
                                <p>Modifica o elimina cuando quieras</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="benefit-content">
                                <h5>Datos Seguros</h5>
                                <p>Tu información está protegida</p>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Lista de direcciones -->
                <div class="addresses-grid" id="addressesGrid">
                    <?php foreach ($userAddresses as $address): ?>
                        <div class="address-card" data-address-id="<?= $address['id'] ?>">
                            <div class="address-header">
                                <div class="address-title">
                                    <h4><?= h($address['alias'] ?? 'Mi Dirección') ?></h4>
                                    
                                    <?php if ($address['principal']): ?>
                                        <span class="badge badge-primary">
                                            <i class="fas fa-star"></i>
                                            Principal
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($address['activa']): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i>
                                            Activa
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="address-actions">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button class="dropdown-item" onclick="editAddress(<?= $address['id'] ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            
                                            <?php if (!$address['principal']): ?>
                                                <button class="dropdown-item" onclick="setPrincipalAddress(<?= $address['id'] ?>)">
                                                    <i class="fas fa-star"></i> Marcar como Principal
                                                </button>
                                            <?php endif; ?>
                                            
                                            <div class="dropdown-divider"></div>
                                            
                                            <?php if ($address['activa']): ?>
                                                <button class="dropdown-item text-warning" onclick="deactivateAddress(<?= $address['id'] ?>)">
                                                    <i class="fas fa-pause"></i> Desactivar
                                                </button>
                                            <?php else: ?>
                                                <button class="dropdown-item text-success" onclick="activateAddress(<?= $address['id'] ?>)">
                                                    <i class="fas fa-play"></i> Activar
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button class="dropdown-item text-danger" onclick="deleteAddress(<?= $address['id'] ?>, '<?= h($address['alias'] ?? 'esta dirección') ?>')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="address-body">
                                <div class="address-details">
                                    <p class="address-line">
                                        <i class="fas fa-road"></i>
                                        <strong><?= h($address['calle']) ?></strong>
                                    </p>
                                    
                                    <p class="address-line">
                                        <i class="fas fa-building"></i>
                                        <?= h($address['colonia']) ?>
                                    </p>
                                    
                                    <p class="address-line">
                                        <i class="fas fa-map"></i>
                                        <?= h($address['ciudad']) ?>, <?= h($estados[$address['estado']] ?? $address['estado']) ?>
                                    </p>
                                    
                                    <p class="address-line">
                                        <i class="fas fa-mail-bulk"></i>
                                        <strong>CP <?= h($address['codigo_postal']) ?></strong>
                                    </p>
                                    
                                    <?php if ($address['referencia']): ?>
                                        <p class="address-line reference">
                                            <i class="fas fa-info-circle"></i>
                                            <em><?= h($address['referencia']) ?></em>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($address['telefono']): ?>
                                        <p class="address-line">
                                            <i class="fas fa-phone"></i>
                                            <?= h($address['telefono']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="address-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Pedidos realizados:</span>
                                        <span class="stat-value"><?= $address['total_pedidos'] ?? 0 ?></span>
                                    </div>
                                    
                                    <div class="stat-item">
                                        <span class="stat-label">Última vez usado:</span>
                                        <span class="stat-value">
                                            <?php if ($address['ultimo_uso']): ?>
                                                <?= date('d/m/Y', strtotime($address['ultimo_uso'])) ?>
                                            <?php else: ?>
                                                Nunca
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="address-footer">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="editAddress(<?= $address['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                    Editar
                                </button>
                                
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="useInCheckout(<?= $address['id'] ?>)">
                                    <i class="fas fa-shopping-cart"></i>
                                    Usar en Checkout
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Botón para agregar nueva dirección -->
                    <div class="address-card add-new-card">
                        <button type="button" class="add-address-btn" data-toggle="modal" data-target="#addressModal">
                            <div class="add-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h5>Agregar Nueva Dirección</h5>
                            <p>Añade otra dirección de entrega</p>
                        </button>
                    </div>
                </div>
                
                <!-- Información adicional -->
                <div class="addresses-info">
                    <div class="info-cards">
                        <div class="info-card">
                            <div class="card-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="card-content">
                                <h5>Dirección Principal</h5>
                                <p>Se utilizará por defecto en el checkout. Puedes cambiarla cuando quieras.</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="card-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="card-content">
                                <h5>Cálculo de Envío</h5>
                                <p>Los costos de envío se calculan automáticamente según la ubicación.</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="card-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="card-content">
                                <h5>Tiempos de Entrega</h5>
                                <p>Varían según la distancia y el método de envío seleccionado.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para agregar/editar dirección -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addressModalLabel">
                    <i class="fas fa-map-marker-alt"></i>
                    <span id="modalTitle">Nueva Dirección</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form id="addressForm" method="POST">
                <?= csrf_token() ?>
                <input type="hidden" id="address_id" name="address_id" value="">
                <input type="hidden" id="form_action" name="action" value="create">
                
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label for="alias" class="form-label">
                                <i class="fas fa-tag"></i>
                                Nombre para esta dirección
                            </label>
                            <input type="text" 
                                   id="alias" 
                                   name="alias" 
                                   class="form-control" 
                                   placeholder="Ej: Casa, Oficina, Casa de mis padres"
                                   maxlength="50">
                            <small class="form-text text-muted">Te ayudará a identificar esta dirección fácilmente</small>
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label for="telefono" class="form-label">
                                <i class="fas fa-phone"></i>
                                Teléfono de contacto
                            </label>
                            <input type="tel" 
                                   id="telefono" 
                                   name="telefono" 
                                   class="form-control" 
                                   placeholder="55 1234 5678">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label for="calle" class="form-label">
                                <i class="fas fa-road"></i>
                                Calle y Número *
                            </label>
                            <input type="text" 
                                   id="calle" 
                                   name="calle" 
                                   class="form-control" 
                                   placeholder="Ej: Av. Insurgentes Sur 1234"
                                   required>
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label for="numero_interior" class="form-label">
                                <i class="fas fa-door-open"></i>
                                Núm. Interior
                            </label>
                            <input type="text" 
                                   id="numero_interior" 
                                   name="numero_interior" 
                                   class="form-control" 
                                   placeholder="Ej: Depto 4B">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="colonia" class="form-label">
                                <i class="fas fa-building"></i>
                                Colonia/Barrio *
                            </label>
                            <input type="text" 
                                   id="colonia" 
                                   name="colonia" 
                                   class="form-control" 
                                   placeholder="Ej: Roma Norte"
                                   required>
                        </div>
                        
                        <div class="form-group col-md-3">
                            <label for="codigo_postal" class="form-label">
                                <i class="fas fa-mail-bulk"></i>
                                Código Postal *
                            </label>
                            <input type="text" 
                                   id="codigo_postal" 
                                   name="codigo_postal" 
                                   class="form-control" 
                                   placeholder="06700"
                                   maxlength="5"
                                   pattern="[0-9]{5}"
                                   required>
                        </div>
                        
                        <div class="form-group col-md-3">
                            <button type="button" class="btn btn-outline-info" id="searchByCP" style="margin-top: 2rem;">
                                <i class="fas fa-search"></i>
                                Buscar
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="ciudad" class="form-label">
                                <i class="fas fa-city"></i>
                                Ciudad/Municipio *
                            </label>
                            <input type="text" 
                                   id="ciudad" 
                                   name="ciudad" 
                                   class="form-control" 
                                   placeholder="Ej: Ciudad de México"
                                   required>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label for="estado" class="form-label">
                                <i class="fas fa-map"></i>
                                Estado *
                            </label>
                            <select id="estado" name="estado" class="form-control" required>
                                <option value="">Selecciona un estado</option>
                                <?php foreach ($estados as $key => $nombre): ?>
                                    <option value="<?= $key ?>"><?= $nombre ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="referencia" class="form-label">
                            <i class="fas fa-info-circle"></i>
                            Referencias de ubicación
                        </label>
                        <textarea id="referencia" 
                                  name="referencia" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Ej: Casa azul con portón blanco, entre la farmacia y la panadería. Tocar el timbre dos veces."
                                  maxlength="500"></textarea>
                        <small class="form-text text-muted">
                            Ayuda al repartidor a encontrar tu dirección más fácilmente
                        </small>
                    </div>
                    
                    <div class="form-options">
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="principal" 
                                   name="principal" 
                                   class="form-check-input" 
                                   value="1">
                            <label for="principal" class="form-check-label">
                                <i class="fas fa-star text-warning"></i>
                                <strong>Establecer como dirección principal</strong>
                                <small class="text-muted d-block">Se utilizará por defecto en tus próximas compras</small>
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" 
                                   id="activa" 
                                   name="activa" 
                                   class="form-check-input" 
                                   value="1" 
                                   checked>
                            <label for="activa" class="form-check-label">
                                <i class="fas fa-check text-success"></i>
                                <strong>Dirección activa</strong>
                                <small class="text-muted d-block">Disponible para usar en el checkout</small>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Previsualización de la dirección -->
                    <div class="address-preview" id="addressPreview" style="display: none;">
                        <h6>
                            <i class="fas fa-eye"></i>
                            Vista previa de la dirección:
                        </h6>
                        <div class="preview-content" id="previewContent">
                            <!-- Se llenará dinámicamente -->
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    
                    <button type="submit" class="btn btn-success" id="saveAddressBtn">
                        <span class="btn-text">
                            <i class="fas fa-save"></i>
                            <span id="saveText">Guardar Dirección</span>
                        </span>
                        <span class="btn-loading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                            Guardando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar <strong id="deleteAddressName"></strong>?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i>
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/addresses.js') ?>"></script>