<?php
/**
 * AgroConecta - Sistema de apoyo a agricultores locales
 * Archivo principal de entrada del sistema
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

session_start();

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir constantes del sistema
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta');

// Cargar archivos de configuración
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/routes.php';

// Cargar funciones auxiliares
require_once APP_PATH . '/core/helpers.php';

// Cargar el sistema de enrutamiento
require_once APP_PATH . '/core/Router.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Database.php';

try {
    // Inicializar el router
    $router = new Router();
    
    // Procesar la solicitud
    $router->handleRequest();
    
} catch (Exception $e) {
    // Manejo de errores global
    error_log('Error en AgroConecta: ' . $e->getMessage());
    
    // En producción, mostrar página de error genérica
    if (defined('PRODUCTION') && PRODUCTION === true) {
        include APP_PATH . '/views/shared/error.php';
    } else {
        // En desarrollo, mostrar el error completo
        echo '<h1>Error en AgroConecta</h1>';
        echo '<p><strong>Mensaje:</strong> ' . $e->getMessage() . '</p>';
        echo '<p><strong>Archivo:</strong> ' . $e->getFile() . '</p>';
        echo '<p><strong>Línea:</strong> ' . $e->getLine() . '</p>';
    }
}
?>