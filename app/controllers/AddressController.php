<?php

/**
 * Controlador para gestión de direcciones de usuario
 */
class AddressController extends BaseController {
    
    private $addressModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->addressModel = new Address();
        $this->userModel = new User();
        
        // Verificar que el usuario esté autenticado para todas las acciones
        $this->requireAuth();
    }
    
    /**
     * Mostrar lista de direcciones del usuario
     */
    public function index() {
        $data = [
            'title' => 'Mis Direcciones - AgroConecta',
            'page' => 'addresses'
        ];
        
        $this->render('user/addresses', $data);
    }
    
    /**
     * Crear nueva dirección
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }
        
        try {
            $this->validateCsrfToken();
            
            // Validar datos de entrada
            $validation = $this->validateAddressData($_POST);
            if (!$validation['valid']) {
                $this->jsonResponse([
                    'success' => false, 
                    'message' => 'Datos inválidos',
                    'errors' => $validation['errors']
                ], 400);
                return;
            }
            
            // Preparar datos para inserción
            $addressData = [
                'usuario_id' => $_SESSION['user_id'],
                'alias' => trim($_POST['alias'] ?? ''),
                'calle' => trim($_POST['calle']),
                'numero_interior' => trim($_POST['numero_interior'] ?? ''),
                'colonia' => trim($_POST['colonia']),
                'ciudad' => trim($_POST['ciudad']),
                'estado' => $_POST['estado'],
                'codigo_postal' => $_POST['codigo_postal'],
                'referencia' => trim($_POST['referencia'] ?? ''),
                'telefono' => $this->formatPhone($_POST['telefono'] ?? ''),
                'principal' => isset($_POST['principal']) ? 1 : 0,
                'activa' => isset($_POST['activa']) ? 1 : 0,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ];
            
            // Si se marca como principal, desmarcar otras direcciones
            if ($addressData['principal']) {
                $this->addressModel->unsetPrincipalAddresses($_SESSION['user_id']);
            }
            
            // Crear dirección
            $addressId = $this->addressModel->create($addressData);
            
            if ($addressId) {
                // Obtener la dirección recién creada para devolverla
                $newAddress = $this->addressModel->findById($addressId);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Dirección creada exitosamente',
                    'address' => $newAddress
                ]);
            } else {
                throw new Exception('Error al crear la dirección en la base de datos');
            }
            
        } catch (Exception $e) {
            $this->logError('Error creating address', [
                'user_id' => $_SESSION['user_id'] ?? null,
                'error' => $e->getMessage(),
                'data' => $_POST
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor intenta nuevamente.'
            ], 500);
        }
    }
    
    /**
     * Obtener datos de dirección para edición
     */
    public function edit($id = null) {
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de dirección requerido'], 400);
            return;
        }
        
        try {
            $address = $this->addressModel->findById($id);
            
            if (!$address) {
                $this->jsonResponse(['success' => false, 'message' => 'Dirección no encontrada'], 404);
                return;
            }
            
            // Verificar que la dirección pertenece al usuario actual
            if ($address['usuario_id'] != $_SESSION['user_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'No tienes permisos para acceder a esta dirección'], 403);
                return;
            }
            
            $this->jsonResponse([
                'success' => true,
                'address' => $address
            ]);
            
        } catch (Exception $e) {
            $this->logError('Error fetching address for edit', [
                'address_id' => $id,
                'user_id' => $_SESSION['user_id'],
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener la información de la dirección'
            ], 500);
        }
    }
    
    /**
     * Actualizar dirección existente
     */
    public function update($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }
        
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de dirección requerido'], 400);
            return;
        }
        
        try {
            $this->validateCsrfToken();
            
            // Verificar que la dirección existe y pertenece al usuario
            $existingAddress = $this->addressModel->findById($id);
            
            if (!$existingAddress) {
                $this->jsonResponse(['success' => false, 'message' => 'Dirección no encontrada'], 404);
                return;
            }
            
            if ($existingAddress['usuario_id'] != $_SESSION['user_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'No tienes permisos para modificar esta dirección'], 403);
                return;
            }
            
            // Validar datos de entrada
            $validation = $this->validateAddressData($_POST);
            if (!$validation['valid']) {
                $this->jsonResponse([
                    'success' => false, 
                    'message' => 'Datos inválidos',
                    'errors' => $validation['errors']
                ], 400);
                return;
            }
            
            // Preparar datos para actualización
            $addressData = [
                'alias' => trim($_POST['alias'] ?? ''),
                'calle' => trim($_POST['calle']),
                'numero_interior' => trim($_POST['numero_interior'] ?? ''),
                'colonia' => trim($_POST['colonia']),
                'ciudad' => trim($_POST['ciudad']),
                'estado' => $_POST['estado'],
                'codigo_postal' => $_POST['codigo_postal'],
                'referencia' => trim($_POST['referencia'] ?? ''),
                'telefono' => $this->formatPhone($_POST['telefono'] ?? ''),
                'principal' => isset($_POST['principal']) ? 1 : 0,
                'activa' => isset($_POST['activa']) ? 1 : 0,
                'fecha_actualizacion' => date('Y-m-d H:i:s')
            ];
            
            // Si se marca como principal, desmarcar otras direcciones
            if ($addressData['principal'] && !$existingAddress['principal']) {
                $this->addressModel->unsetPrincipalAddresses($_SESSION['user_id']);
            }
            
            // Actualizar dirección
            $updated = $this->addressModel->update($id, $addressData);
            
            if ($updated) {
                // Obtener la dirección actualizada para devolverla
                $updatedAddress = $this->addressModel->findById($id);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Dirección actualizada exitosamente',
                    'address' => $updatedAddress
                ]);
            } else {
                throw new Exception('Error al actualizar la dirección en la base de datos');
            }
            
        } catch (Exception $e) {
            $this->logError('Error updating address', [
                'address_id' => $id,
                'user_id' => $_SESSION['user_id'],
                'error' => $e->getMessage(),
                'data' => $_POST
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor intenta nuevamente.'
            ], 500);
        }
    }
    
    /**
     * Eliminar dirección
     */
    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }
        
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de dirección requerido'], 400);
            return;
        }
        
        try {
            $this->validateCsrfToken();
            
            // Verificar que la dirección existe y pertenece al usuario
            $address = $this->addressModel->findById($id);
            
            if (!$address) {
                $this->jsonResponse(['success' => false, 'message' => 'Dirección no encontrada'], 404);
                return;
            }
            
            if ($address['usuario_id'] != $_SESSION['user_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'No tienes permisos para eliminar esta dirección'], 403);
                return;
            }
            
            // Verificar si la dirección está siendo utilizada en pedidos pendientes
            $pendingOrders = $this->addressModel->getOrdersUsingAddress($id);
            
            if (!empty($pendingOrders)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se puede eliminar esta dirección porque tiene pedidos asociados. Puedes desactivarla en su lugar.'
                ], 400);
                return;
            }
            
            // Eliminar dirección
            $deleted = $this->addressModel->delete($id);
            
            if ($deleted) {
                // Si era la dirección principal, asignar otra como principal
                if ($address['principal']) {
                    $this->addressModel->assignNewPrincipal($_SESSION['user_id']);
                }
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Dirección eliminada exitosamente'
                ]);
            } else {
                throw new Exception('Error al eliminar la dirección de la base de datos');
            }
            
        } catch (Exception $e) {
            $this->logError('Error deleting address', [
                'address_id' => $id,
                'user_id' => $_SESSION['user_id'],
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor intenta nuevamente.'
            ], 500);
        }
    }
    
    /**
     * Establecer dirección como principal
     */
    public function setPrincipal($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }
        
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de dirección requerido'], 400);
            return;
        }
        
        try {
            $this->validateCsrfToken();
            
            // Verificar que la dirección existe y pertenece al usuario
            $address = $this->addressModel->findById($id);
            
            if (!$address) {
                $this->jsonResponse(['success' => false, 'message' => 'Dirección no encontrada'], 404);
                return;
            }
            
            if ($address['usuario_id'] != $_SESSION['user_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'No tienes permisos para modificar esta dirección'], 403);
                return;
            }
            
            // Verificar que la dirección está activa
            if (!$address['activa']) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se puede establecer como principal una dirección desactivada'
                ], 400);
                return;
            }
            
            // Desmarcar otras direcciones como principales
            $this->addressModel->unsetPrincipalAddresses($_SESSION['user_id']);
            
            // Marcar esta dirección como principal
            $updated = $this->addressModel->update($id, ['principal' => 1]);
            
            if ($updated) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Dirección establecida como principal'
                ]);
            } else {
                throw new Exception('Error al establecer la dirección como principal');
            }
            
        } catch (Exception $e) {
            $this->logError('Error setting principal address', [
                'address_id' => $id,
                'user_id' => $_SESSION['user_id'],
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor intenta nuevamente.'
            ], 500);
        }
    }
    
    /**
     * Activar dirección
     */
    public function activate($id = null) {
        $this->toggleAddressStatus($id, true);
    }
    
    /**
     * Desactivar dirección
     */
    public function deactivate($id = null) {
        $this->toggleAddressStatus($id, false);
    }
    
    /**
     * Alternar estado de activación de dirección
     */
    private function toggleAddressStatus($id, $activate) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }
        
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de dirección requerido'], 400);
            return;
        }
        
        try {
            $this->validateCsrfToken();
            
            // Verificar que la dirección existe y pertenece al usuario
            $address = $this->addressModel->findById($id);
            
            if (!$address) {
                $this->jsonResponse(['success' => false, 'message' => 'Dirección no encontrada'], 404);
                return;
            }
            
            if ($address['usuario_id'] != $_SESSION['user_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'No tienes permisos para modificar esta dirección'], 403);
                return;
            }
            
            // No se puede desactivar la dirección principal si es la única activa
            if (!$activate && $address['principal']) {
                $activeAddresses = $this->addressModel->getActiveAddresses($_SESSION['user_id']);
                if (count($activeAddresses) <= 1) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'No puedes desactivar tu única dirección activa. Agrega otra dirección primero.'
                    ], 400);
                    return;
                }
            }
            
            $updateData = ['activa' => $activate ? 1 : 0];
            
            // Si se desactiva la dirección principal, asignar otra como principal
            if (!$activate && $address['principal']) {
                $updateData['principal'] = 0;
            }
            
            $updated = $this->addressModel->update($id, $updateData);
            
            if ($updated) {
                // Si se desactivó la dirección principal, asignar otra como principal
                if (!$activate && $address['principal']) {
                    $this->addressModel->assignNewPrincipal($_SESSION['user_id']);
                }
                
                $action = $activate ? 'activada' : 'desactivada';
                $this->jsonResponse([
                    'success' => true,
                    'message' => "Dirección {$action} exitosamente"
                ]);
            } else {
                throw new Exception('Error al cambiar el estado de la dirección');
            }
            
        } catch (Exception $e) {
            $this->logError('Error toggling address status', [
                'address_id' => $id,
                'user_id' => $_SESSION['user_id'],
                'activate' => $activate,
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor intenta nuevamente.'
            ], 500);
        }
    }
    
    /**
     * Validar datos de dirección
     */
    private function validateAddressData($data) {
        $errors = [];
        
        // Campos requeridos
        $required = ['calle', 'colonia', 'ciudad', 'estado', 'codigo_postal'];
        
        foreach ($required as $field) {
            if (empty(trim($data[$field] ?? ''))) {
                $errors[$field] = 'Este campo es requerido';
            }
        }
        
        // Validación de código postal
        if (!empty($data['codigo_postal'])) {
            if (!preg_match('/^\d{5}$/', $data['codigo_postal'])) {
                $errors['codigo_postal'] = 'El código postal debe tener 5 dígitos';
            }
        }
        
        // Validación de estado
        if (!empty($data['estado'])) {
            $validStates = [
                'aguascalientes', 'baja_california', 'baja_california_sur', 'campeche', 
                'chiapas', 'chihuahua', 'coahuila', 'colima', 'cdmx', 'durango',
                'guanajuato', 'guerrero', 'hidalgo', 'jalisco', 'mexico', 'michoacan',
                'morelos', 'nayarit', 'nuevo_leon', 'oaxaca', 'puebla', 'queretaro',
                'quintana_roo', 'san_luis_potosi', 'sinaloa', 'sonora', 'tabasco',
                'tamaulipas', 'tlaxcala', 'veracruz', 'yucatan', 'zacatecas'
            ];
            
            if (!in_array($data['estado'], $validStates)) {
                $errors['estado'] = 'Estado inválido';
            }
        }
        
        // Validación de teléfono (opcional)
        if (!empty($data['telefono'])) {
            $cleanPhone = preg_replace('/\D/', '', $data['telefono']);
            if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 12) {
                $errors['telefono'] = 'Formato de teléfono inválido';
            }
        }
        
        // Validación de longitud de campos
        $maxLengths = [
            'alias' => 50,
            'calle' => 255,
            'numero_interior' => 20,
            'colonia' => 100,
            'ciudad' => 100,
            'referencia' => 500
        ];
        
        foreach ($maxLengths as $field => $maxLength) {
            if (!empty($data[$field]) && strlen($data[$field]) > $maxLength) {
                $errors[$field] = "Este campo no puede exceder {$maxLength} caracteres";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Formatear número de teléfono
     */
    private function formatPhone($phone) {
        if (empty($phone)) {
            return '';
        }
        
        // Limpiar solo dígitos
        $cleaned = preg_replace('/\D/', '', $phone);
        
        // Formatear según la longitud
        if (strlen($cleaned) == 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '$1 $2 $3', $cleaned);
        } elseif (strlen($cleaned) == 12) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{3})/', '$1 $2 $3 $4', $cleaned);
        }
        
        return $cleaned;
    }
    
    /**
     * Validar token CSRF
     */
    private function validateCsrfToken() {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception('Token de seguridad inválido');
        }
    }
    
    /**
     * Verificar que el usuario esté autenticado
     */
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Sesión expirada'], 401);
            } else {
                redirect('/auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            }
            exit;
        }
    }
    
    /**
     * Verificar si es una petición AJAX
     */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Enviar respuesta JSON
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Registrar errores
     */
    private function logError($message, $context = []) {
        // En una implementación real, esto escribiría a logs
        error_log($message . ' - Context: ' . json_encode($context));
    }
}