<?php
/**
 * Controller - Controlador base para AgroConecta
 * Clase base de la cual heredan todos los controladores del sistema
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

class Controller {
    protected $db;
    protected $viewData = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Establece datos para las vistas
     */
    protected function setViewData($key, $value) {
        $this->viewData[$key] = $value;
    }
    
    /**
     * Obtiene datos de las vistas
     */
    protected function getViewData($key = null, $default = null) {
        if ($key === null) {
            return $this->viewData;
        }
        
        return isset($this->viewData[$key]) ? $this->viewData[$key] : $default;
    }
    
    /**
     * Carga una vista con datos opcionales
     */
    protected function view($viewName, $data = []) {
        // Combinar datos de la vista con datos globales del controlador
        $allData = array_merge($this->viewData, $data);
        
        // Extraer variables del array de datos
        extract($allData);
        
        // Construir la ruta de la vista
        $viewPath = APP_PATH . '/views/' . $viewName . '.php';
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            throw new Exception("Vista no encontrada: {$viewName}");
        }
    }
    
    /**
     * Carga una vista con layout compartido
     */
    protected function viewWithLayout($viewName, $data = [], $layout = 'main') {
        // Combinar datos del controlador con los datos pasados
        $viewData = array_merge($this->viewData, $data);
        
        // Renderizar la vista del contenido primero
        ob_start();
        $this->view($viewName, $viewData);
        $content = ob_get_clean();
        
        // Pasar el contenido renderizado al layout
        $viewData['content'] = $content;
        $viewData['content_view'] = $viewName;
        
        // Cargar el layout con el contenido
        $this->view("shared/layouts/{$layout}", $viewData);
    }
    
    /**
     * Método render() para compatibilidad - usa viewWithLayout
     */
    protected function render($viewName, $data = [], $layout = 'main') {
        $this->viewWithLayout($viewName, $data, $layout);
    }
    
    /**
     * Carga un modelo
     */
    protected function model($modelName) {
        $modelPath = APP_PATH . '/models/' . $modelName . '.php';
        
        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $modelName();
        } else {
            throw new Exception("Modelo no encontrado: {$modelName}");
        }
    }
    
    /**
     * Redirecciona a una URL
     */
    protected function redirect($url = '') {
        if (strpos($url, 'http') === false) {
            $url = BASE_URL . '/' . ltrim($url, '/');
        }
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Respuesta JSON
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Obtiene datos POST de forma segura
     */
    protected function getPost($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        
        return isset($_POST[$key]) ? $this->sanitize($_POST[$key]) : $default;
    }
    
    /**
     * Obtiene datos GET de forma segura
     */
    protected function getGet($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        
        return isset($_GET[$key]) ? $this->sanitize($_GET[$key]) : $default;
    }
    
    /**
     * Sanitiza entrada de datos
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Valida token CSRF
     */
    protected function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Genera token CSRF
     */
    protected function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verifica si el usuario está autenticado
     */
    protected function requireAuth() {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            $this->redirect('login');
        }
    }
    
    /**
     * Verifica el rol del usuario
     */
    protected function requireRole($role) {
        $this->requireAuth();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
            $this->redirect('error?code=403');
        }
    }
    
    /**
     * Obtiene información del usuario actual
     */
    protected function getCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            return [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'] ?? '',
                'role' => $_SESSION['user_role'] ?? '',
                'name' => $_SESSION['user_name'] ?? ''
            ];
        }
        
        return null;
    }
    
    /**
     * Establece mensaje flash
     */
    protected function setFlash($type, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        
        $_SESSION['flash'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Obtiene mensajes flash
     */
    protected function getFlash() {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
    
    /**
     * Valida formato de email
     */
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Hashea una contraseña
     */
    protected function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
    /**
     * Verifica una contraseña
     */
    protected function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Maneja subida de archivos
     */
    protected function handleFileUpload($inputName, $allowedTypes = [], $maxSize = null) {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $file = $_FILES[$inputName];
        $maxSize = $maxSize ?? MAX_FILE_SIZE;
        
        // Verificar tamaño
        if ($file['size'] > $maxSize) {
            throw new Exception('El archivo es demasiado grande');
        }
        
        // Verificar tipo si se especifica
        if (!empty($allowedTypes)) {
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Tipo de archivo no permitido');
            }
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $extension;
        $uploadPath = UPLOAD_PATH . $fileName;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return $fileName;
        }
        
        throw new Exception('Error al subir el archivo');
    }
}
?>