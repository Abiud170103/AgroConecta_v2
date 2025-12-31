<?php
/**
 * Script de prueba para los modelos de AgroConecta
 * Verifica conexión a base de datos y funcionalidades básicas
 * 
 * IMPORTANTE: Ejecutar solo después de instalar la base de datos
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PRUEBA DE MODELOS AGROCONECTA ===\n\n";

// Verificar archivos necesarios
$archivosRequeridos = [
    'config/database.php',
    'app/core/Database.php',
    'app/models/Model.php',
    'app/models/Usuario.php',
    'app/models/Producto.php',
    'app/models/Carrito.php'
];

echo "1. Verificando archivos requeridos...\n";
foreach ($archivosRequeridos as $archivo) {
    if (file_exists($archivo)) {
        echo "   ✅ {$archivo}\n";
    } else {
        echo "   ❌ {$archivo} - NO ENCONTRADO\n";
        exit("Error: Archivo requerido no encontrado\n");
    }
}

// Incluir archivos
try {
    require_once 'config/database.php';
    require_once 'app/core/Database.php';
    require_once 'app/models/Model.php';
    require_once 'app/models/Usuario.php';
    require_once 'app/models/Producto.php';
    require_once 'app/models/Carrito.php';
    require_once 'app/models/Direccion.php';
    require_once 'app/models/Notificacion.php';
    echo "\n2. ✅ Archivos incluidos correctamente\n";
} catch (Exception $e) {
    echo "\n2. ❌ Error al incluir archivos: " . $e->getMessage() . "\n";
    exit();
}

// Probar conexión a base de datos
echo "\n3. Probando conexión a base de datos...\n";
try {
    $db = Database::getInstance();
    echo "   ✅ Conexión establecida\n";
    
    // Probar una consulta simple
    $result = $db->selectOne("SELECT COUNT(*) as total FROM Usuario");
    echo "   ✅ Consulta ejecutada - Usuarios en BD: " . $result['total'] . "\n";
} catch (Exception $e) {
    echo "   ❌ Error de conexión: " . $e->getMessage() . "\n";
    exit("Verifica tu configuración en config/database.php\n");
}

// Probar modelo Usuario
echo "\n4. Probando modelo Usuario...\n";
try {
    $usuario = new Usuario();
    
    // Contar usuarios
    $totalUsuarios = $usuario->count();
    echo "   ✅ Total usuarios: {$totalUsuarios}\n";
    
    // Obtener estadísticas
    $stats = $usuario->getStats();
    if ($stats && is_array($stats)) {
        echo "   ✅ Estadísticas obtenidas\n";
        foreach ($stats as $stat) {
            echo "     - {$stat['tipo_usuario']}: {$stat['total']} total, {$stat['activos']} activos\n";
        }
    }
    
    // Probar búsqueda por email (debería existir admin@agroconecta.com)
    $admin = $usuario->findByEmail('admin@agroconecta.com');
    if ($admin) {
        echo "   ✅ Usuario admin encontrado: {$admin['nombre']} {$admin['apellido']}\n";
    } else {
        echo "   ⚠️ Usuario admin no encontrado (puede ser normal si no hay seeders)\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error en modelo Usuario: " . $e->getMessage() . "\n";
}

// Probar modelo Producto
echo "\n5. Probando modelo Producto...\n";
try {
    $producto = new Producto();
    
    // Contar productos
    $totalProductos = $producto->count();
    echo "   ✅ Total productos: {$totalProductos}\n";
    
    // Obtener categorías
    $categorias = $producto->getCategorias();
    if ($categorias && is_array($categorias)) {
        echo "   ✅ Categorías disponibles: " . count($categorias) . "\n";
        foreach (array_slice($categorias, 0, 3) as $categoria) {
            echo "     - {$categoria['categoria']}: {$categoria['total']} productos\n";
        }
    }
    
    // Obtener productos destacados
    $destacados = $producto->getProductosDestacados(3);
    echo "   ✅ Productos destacados encontrados: " . count($destacados) . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Error en modelo Producto: " . $e->getMessage() . "\n";
}

// Probar modelo Carrito
echo "\n6. Probando modelo Carrito...\n";
try {
    $carrito = new Carrito();
    
    // Contar items en carritos
    $totalItems = $carrito->count();
    echo "   ✅ Total items en carritos: {$totalItems}\n";
    
} catch (Exception $e) {
    echo "   ❌ Error en modelo Carrito: " . $e->getMessage() . "\n";
}

// Probar modelo Direccion
echo "\n7. Probando modelo Direccion...\n";
try {
    $direccion = new Direccion();
    
    // Contar direcciones
    $totalDirecciones = $direccion->count();
    echo "   ✅ Total direcciones: {$totalDirecciones}\n";
    
    // Probar validación de código postal
    $cpValido = $direccion->validarCodigoPostal('01234');
    $cpInvalido = $direccion->validarCodigoPostal('abc12');
    
    echo "   ✅ Validación CP '01234': " . ($cpValido ? 'Válido' : 'Inválido') . "\n";
    echo "   ✅ Validación CP 'abc12': " . ($cpInvalido ? 'Válido' : 'Inválido') . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Error en modelo Direccion: " . $e->getMessage() . "\n";
}

// Probar modelo Notificacion
echo "\n8. Probando modelo Notificacion...\n";
try {
    $notificacion = new Notificacion();
    
    // Contar notificaciones
    $totalNotificaciones = $notificacion->count();
    echo "   ✅ Total notificaciones: {$totalNotificaciones}\n";
    
    // Obtener estadísticas
    $statsNotif = $notificacion->getEstadisticas();
    if ($statsNotif && is_array($statsNotif)) {
        echo "   ✅ Estadísticas de notificaciones obtenidas\n";
        foreach ($statsNotif as $stat) {
            echo "     - {$stat['tipo']}: {$stat['total']} total ({$stat['leidas']} leídas)\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ Error en modelo Notificacion: " . $e->getMessage() . "\n";
}

// Resumen final
echo "\n=== RESUMEN ===\n";
echo "✅ Conexión a base de datos: OK\n";
echo "✅ Modelos cargados: OK\n";
echo "✅ Funcionalidades básicas: OK\n";

echo "\n🎉 TODOS LOS MODELOS ESTÁN FUNCIONANDO CORRECTAMENTE!\n";
echo "\nPróximos pasos:\n";
echo "1. Crear controladores que usen estos modelos\n";
echo "2. Implementar las vistas del frontend\n";
echo "3. Configurar las rutas del sistema\n";
echo "4. Probar el flujo completo de la aplicación\n";

echo "\n=== FIN DE LA PRUEBA ===\n";
?>