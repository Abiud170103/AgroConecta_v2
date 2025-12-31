<?php
/**
 * Configuración de base de datos para AgroConecta
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

// Configuración de la base de datos
define('DB_HOST', '127.0.0.1');  // Cambiado de localhost
define('DB_NAME', 'agroconecta_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// Configuración del sistema
define('ENVIRONMENT', 'development'); // development, testing, production
define('DEBUG_MODE', true);

// URLs y rutas
define('SITE_URL', 'http://localhost/AgroConecta');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configuración de seguridad
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos
define('CSRF_TOKEN_EXPIRE', 1800); // 30 minutos

// Configuración de correo (PHPMailer)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'agroconecta@gmail.com'); // Cambiar por email real
define('MAIL_PASSWORD', ''); // Configurar password de app
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_EMAIL', 'agroconecta@gmail.com');
define('MAIL_FROM_NAME', 'AgroConecta');

// Configuración de Mercado Pago
define('MP_ACCESS_TOKEN', ''); // Token de acceso de MercadoPago
define('MP_PUBLIC_KEY', ''); // Clave pública de MercadoPago
define('MP_SANDBOX_MODE', true); // true para pruebas, false para producción

// Configuraciones adicionales
define('DEFAULT_TIMEZONE', 'America/Mexico_City');
define('DEFAULT_LANGUAGE', 'es');
define('ITEMS_PER_PAGE', 12);

// Establecer zona horaria
date_default_timezone_set(DEFAULT_TIMEZONE);

// Configurar locale para México
setlocale(LC_ALL, 'es_MX.UTF-8');
?>