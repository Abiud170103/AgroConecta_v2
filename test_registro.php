<?php
/**
 * Script de prueba para el registro de usuarios
 */

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Configurar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicializar sesión
session_start();

// Definir constantes
define('BASE_PATH', realpath(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('BASE_URL', 'http://localhost/AgroConecta_v2/public');

// Incluir configuración de base de datos y clases necesarias
require_once BASE_PATH . '/config/database.php';
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/models/Usuario.php';

echo "<h3>Probando Registro de Usuario</h3>\n";

// Datos de prueba para cliente
$clientData = [
    'nombre' => 'Juan',
    'apellido' => 'Pérez',
    'correo' => 'cliente@agroconecta.com',
    'contraseña' => '123456789',
    'telefono' => '5551234567',
    'tipo_usuario' => 'cliente',
    'activo' => 1,
    'verificado' => 0
];

// Datos de prueba para vendedor  
$vendedorData = [
    'nombre' => 'María',
    'apellido' => 'González',
    'correo' => 'vendedor@agroconecta.com',
    'contraseña' => '123456789',
    'telefono' => '5557654321',
    'tipo_usuario' => 'vendedor',
    'activo' => 1,
    'verificado' => 0
];

$userModel = new Usuario();

try {
    // Probar registro de cliente
    echo "<p><strong>Creando usuario cliente...</strong></p>\n";
    
    if (!$userModel->emailExists($clientData['correo'])) {
        $clientId = $userModel->createUser($clientData);
        if ($clientId) {
            echo "<p>✅ Cliente creado exitosamente - ID: {$clientId}</p>\n";
            echo "<p>Email: {$clientData['correo']}, Password: {$clientData['contraseña']}</p>\n";
        } else {
            echo "<p>❌ Error al crear cliente</p>\n";
        }
    } else {
        echo "<p>⚠️ Cliente ya existe - Email: {$clientData['correo']}, Password: {$clientData['contraseña']}</p>\n";
    }
    
    // Probar registro de vendedor
    echo "<p><strong>Creando usuario vendedor...</strong></p>\n";
    
    if (!$userModel->emailExists($vendedorData['correo'])) {
        $vendedorId = $userModel->createUser($vendedorData);
        if ($vendedorId) {
            echo "<p>✅ Vendedor creado exitosamente - ID: {$vendedorId}</p>\n";
            echo "<p>Email: {$vendedorData['correo']}, Password: {$vendedorData['contraseña']}</p>\n";
        } else {
            echo "<p>❌ Error al crear vendedor</p>\n";
        }
    } else {
        echo "<p>⚠️ Vendedor ya existe - Email: {$vendedorData['correo']}, Password: {$vendedorData['contraseña']}</p>\n";
    }
    
    echo "<h4>Usuarios creados para pruebas:</h4>\n";
    echo "<ul>\n";
    echo "<li><strong>Cliente:</strong> {$clientData['correo']} / {$clientData['contraseña']}</li>\n";
    echo "<li><strong>Vendedor:</strong> {$vendedorData['correo']} / {$vendedorData['contraseña']}</li>\n";
    echo "<li><strong>Usuario Test:</strong> test@agroconecta.com / 123456789</li>\n";
    echo "</ul>\n";
    
    echo "<p><strong>✅ Script completado exitosamente</strong></p>\n";
    echo "<p>Puedes probar el registro en: <a href='registro'>http://localhost/AgroConecta_v2/public/registro</a></p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>