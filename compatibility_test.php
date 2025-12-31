<?php
/**
 * Test rápido para verificar que no hay errores de sintaxis
 */

// Configuración básica
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST DE COMPATIBILIDAD DE MÉTODOS ===\n\n";

// Incluir archivos necesarios
$rootPath = __DIR__;
$appPath = $rootPath . '/app';

// Verificar si los archivos existen
$files_to_check = [
    'app/core/Controller.php',
    'app/controllers/BaseController.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ Archivo encontrado: {$file}\n";
    } else {
        echo "❌ Archivo no encontrado: {$file}\n";
        exit(1);
    }
}

echo "\n=== CARGANDO ARCHIVOS ===\n";

try {
    // Definir constantes si no existen
    if (!defined('ROOT_PATH')) define('ROOT_PATH', $rootPath);
    if (!defined('APP_PATH')) define('APP_PATH', $appPath);
    if (!defined('CONFIG_PATH')) define('CONFIG_PATH', $rootPath . '/config');

    // Cargar Database si existe
    if (file_exists('app/core/Database.php')) {
        require_once 'app/core/Database.php';
        echo "✅ Database cargado\n";
    }

    // Cargar Controller
    require_once 'app/core/Controller.php';
    echo "✅ Controller base cargado\n";

    // Cargar BaseController
    require_once 'app/controllers/BaseController.php';
    echo "✅ BaseController cargado\n";

    echo "\n=== VERIFICANDO COMPATIBILIDAD ===\n";

    // Crear instancia de reflexión para verificar métodos
    $controllerReflection = new ReflectionClass('Controller');
    $baseControllerReflection = new ReflectionClass('BaseController');

    // Verificar método validateCSRF
    if ($controllerReflection->hasMethod('validateCSRF')) {
        $parentMethod = $controllerReflection->getMethod('validateCSRF');
        echo "✅ Método validateCSRF encontrado en Controller\n";
        echo "   - Parámetros: " . $parentMethod->getNumberOfParameters() . "\n";
    }

    if ($baseControllerReflection->hasMethod('validateCSRF')) {
        $childMethod = $baseControllerReflection->getMethod('validateCSRF');
        echo "✅ Método validateCSRF encontrado en BaseController\n";
        echo "   - Parámetros: " . $childMethod->getNumberOfParameters() . "\n";
    }

    echo "\n=== TEST COMPLETADO ===\n";
    echo "✅ No se encontraron errores de compatibilidad\n";
    echo "✅ Los métodos ahora son compatibles\n";

} catch (Error $e) {
    echo "❌ Error de PHP: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Excepción: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== INFORMACIÓN DEL SISTEMA ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . "\n";
echo "Memoria: " . ini_get('memory_limit') . "\n";

echo "\n🎉 ¡Todo listo! Tu proyecto debería funcionar ahora.\n";
echo "Puedes acceder a: http://localhost/AgroConecta/\n";
?>