<?php
/**
 * HomeController - Controlador de la página principal
 * Maneja la página de inicio y funcionalidades públicas
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'BaseController.php';
require_once APP_PATH . '/models/Producto.php';
require_once APP_PATH . '/models/Usuario.php';

class HomeController extends BaseController {
    
    /**
     * Página principal
     */
    public function index() {
        try {
            $productoModel = new Producto();
            $usuarioModel = new Usuario();
            
            // Productos destacados
            $productosDestacados = $productoModel->getProductosDestacados(8);
            
            // Categorías populares
            $categorias = $productoModel->getCategorias();
            $categoriasPopulares = array_slice($categorias, 0, 6);
            
            // Vendedores destacados
            $vendedoresDestacados = $usuarioModel->getVendedores(4);
            
            // Estadísticas básicas para mostrar
            $stats = [
                'total_productos' => $productoModel->count(['activo' => 1]),
                'total_vendedores' => $usuarioModel->count(['tipo_usuario' => 'vendedor', 'activo' => 1]),
                'total_categorias' => count($categorias)
            ];
            
            $this->setViewData('pageTitle', 'Inicio - Productos Agrícolas Frescos');
            $this->setViewData('productosDestacados', $productosDestacados);
            $this->setViewData('categoriasPopulares', $categoriasPopulares);
            $this->setViewData('vendedoresDestacados', $vendedoresDestacados);
            $this->setViewData('stats', $stats);
            $this->setViewData('showHeroSection', true);
            
        } catch (Exception $e) {
            error_log("Error in HomeController::index: " . $e->getMessage());
            
            // Datos por defecto en caso de error
            $this->setViewData('pageTitle', 'AgroConecta - Productos Agrícolas');
            $this->setViewData('productosDestacados', []);
            $this->setViewData('categoriasPopulares', []);
            $this->setViewData('vendedoresDestacados', []);
            $this->setViewData('stats', ['total_productos' => 0, 'total_vendedores' => 0, 'total_categorias' => 0]);
            $this->setViewData('showHeroSection', true);
        }
        
        $this->render('home/index');
    }
    
    /**
     * Página Acerca de
     */
    public function about() {
        $this->setViewData('pageTitle', 'Acerca de AgroConecta');
        $this->setViewData('breadcrumb', [
            ['name' => 'Inicio', 'url' => '/'],
            ['name' => 'Acerca de', 'url' => '/about']
        ]);
        
        $this->render('home/about');
    }
    
    /**
     * Página de Contacto
     */
    public function contact() {
        $this->setViewData('pageTitle', 'Contacto');
        $this->setViewData('csrf_token', $this->generateCSRF());
        $this->setViewData('success', $this->getFlashMessage('success'));
        $this->setViewData('error', $this->getFlashMessage('error'));
        $this->setViewData('breadcrumb', [
            ['name' => 'Inicio', 'url' => '/'],
            ['name' => 'Contacto', 'url' => '/contact']
        ]);
        
        $this->render('home/contact');
    }
    
    /**
     * Procesa formulario de contacto
     */
    public function processContact() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/contact');
            return;
        }
        
        if (!$this->validateCSRF()) return;
        
        $data = $this->sanitizeInput([
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'message' => $_POST['message'] ?? ''
        ]);
        
        // Validaciones
        $errors = [];
        if (empty($data['name'])) $errors['name'] = 'El nombre es requerido';
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email válido es requerido';
        }
        if (empty($data['subject'])) $errors['subject'] = 'El asunto es requerido';
        if (empty($data['message']) || strlen($data['message']) < 10) {
            $errors['message'] = 'El mensaje debe tener al menos 10 caracteres';
        }
        
        if (!empty($errors)) {
            $_SESSION['contact_errors'] = $errors;
            $_SESSION['contact_data'] = $data;
            $this->setFlashMessage('error', 'Por favor corrige los errores en el formulario');
            $this->redirect('/contact');
            return;
        }
        
        // Procesar mensaje de contacto
        try {
            $this->sendContactEmail($data);
            $this->logActivity('contact_form_submitted', "From: {$data['email']} - Subject: {$data['subject']}");
            
            $this->setFlashMessage('success', 'Mensaje enviado correctamente. Te responderemos pronto.');
            $this->redirect('/contact');
            
        } catch (Exception $e) {
            error_log("Error sending contact email: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error al enviar el mensaje. Inténtalo de nuevo.');
            $this->redirect('/contact');
        }
    }
    
    /**
     * Página de términos y condiciones
     */
    public function terms() {
        $this->setViewData('pageTitle', 'Términos y Condiciones');
        $this->setViewData('breadcrumb', [
            ['name' => 'Inicio', 'url' => '/'],
            ['name' => 'Términos y Condiciones', 'url' => '/terms']
        ]);
        
        $this->render('home/terms');
    }
    
    /**
     * Página de política de privacidad
     */
    public function privacy() {
        $this->setViewData('pageTitle', 'Política de Privacidad');
        $this->setViewData('breadcrumb', [
            ['name' => 'Inicio', 'url' => '/'],
            ['name' => 'Política de Privacidad', 'url' => '/privacy']
        ]);
        
        $this->render('home/privacy');
    }
    
    /**
     * Página de preguntas frecuentes
     */
    public function faq() {
        $faqs = [
            [
                'question' => '¿Cómo puedo vender mis productos en AgroConecta?',
                'answer' => 'Regístrate como vendedor, completa tu perfil y comienza a agregar tus productos. Nuestro equipo revisará tu cuenta antes de aprobarla.'
            ],
            [
                'question' => '¿Qué métodos de pago aceptan?',
                'answer' => 'Aceptamos tarjetas de crédito y débito, PayPal, Mercado Pago y transferencias bancarias.'
            ],
            [
                'question' => '¿Realizan entregas a domicilio?',
                'answer' => 'Sí, trabajamos con diferentes vendedores que pueden ofrecer entrega a domicilio dependiendo de tu ubicación.'
            ],
            [
                'question' => '¿Cómo garantizan la frescura de los productos?',
                'answer' => 'Trabajamos directamente con productores locales para garantizar que los productos lleguen frescos. Cada vendedor está verificado por nuestro equipo.'
            ],
            [
                'question' => '¿Puedo cancelar mi pedido?',
                'answer' => 'Puedes cancelar tu pedido antes de que sea confirmado por el vendedor. Una vez confirmado, contacta directamente al vendedor.'
            ],
            [
                'question' => '¿Qué hago si tengo problemas con mi pedido?',
                'answer' => 'Contacta a nuestro equipo de soporte a través del formulario de contacto o directamente con el vendedor. Te ayudaremos a resolver cualquier inconveniente.'
            ]
        ];
        
        $this->setViewData('pageTitle', 'Preguntas Frecuentes');
        $this->setViewData('faqs', $faqs);
        $this->setViewData('breadcrumb', [
            ['name' => 'Inicio', 'url' => '/'],
            ['name' => 'FAQ', 'url' => '/faq']
        ]);
        
        $this->render('home/faq');
    }
    
    /**
     * Búsqueda rápida desde el home
     */
    public function search() {
        $query = $this->sanitizeInput($_GET['q'] ?? '');
        
        if (empty($query)) {
            $this->redirect('/productos');
            return;
        }
        
        // Redireccionar a la búsqueda de productos
        $this->redirect('/productos/buscar?q=' . urlencode($query));
    }
    
    /**
     * API para obtener sugerencias de búsqueda (AJAX)
     */
    public function searchSuggestions() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonError('Método no permitido', 405);
            return;
        }
        
        $query = $this->sanitizeInput($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            $this->jsonSuccess('OK', []);
            return;
        }
        
        try {
            $productoModel = new Producto();
            
            // Buscar productos que coincidan
            $productos = $productoModel->buscarProductos($query, null, 5);
            
            $suggestions = [];
            foreach ($productos as $producto) {
                $suggestions[] = [
                    'id' => $producto['id_producto'],
                    'name' => $producto['nombre'],
                    'category' => $producto['categoria'],
                    'price' => number_format($producto['precio'], 2),
                    'image' => $producto['imagen_url'] ?? '/images/default-product.jpg',
                    'url' => '/productos/ver/' . $producto['id_producto']
                ];
            }
            
            $this->jsonSuccess('OK', $suggestions);
            
        } catch (Exception $e) {
            error_log("Error in search suggestions: " . $e->getMessage());
            $this->jsonError('Error interno del servidor', 500);
        }
    }
    
    // MÉTODOS PRIVADOS
    
    /**
     * Envía email de contacto
     */
    private function sendContactEmail($data) {
        // TODO: Implementar envío de email
        // Por ahora, guardar en log
        $message = "Contact Form Submission\n";
        $message .= "Name: {$data['name']}\n";
        $message .= "Email: {$data['email']}\n";
        $message .= "Subject: {$data['subject']}\n";
        $message .= "Message: {$data['message']}\n";
        $message .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $message .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
        
        error_log("AgroConecta Contact Form: " . $message);
        
        // También se puede guardar en base de datos si se crea una tabla para ello
    }
}
?>