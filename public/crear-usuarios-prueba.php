<?php
/**
 * Script para crear usuarios de prueba verificados
 * AgroConecta - Datos de desarrollo
 */

// Cargar configuración y clases principales
require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../app/models/Model.php';
require_once '../app/models/Usuario.php';

try {
    $usuario = new Usuario();
    $usuariosCreados = [];

    // Datos de usuarios de prueba (solo campos básicos)
    $usuariosPrueba = [
        [
            'nombre' => 'Juan Carlos',
            'apellido' => 'Pérez García',
            'correo' => 'vendedor@test.com',
            'contraseña' => 'prueba123',
            'telefono' => '5512345678',
            'tipo_usuario' => 'vendedor',
            'activo' => 1,
            'verificado' => 1
        ],
        [
            'nombre' => 'María Elena',
            'apellido' => 'Rodríguez López',
            'correo' => 'cliente@test.com',
            'contraseña' => 'prueba123',
            'telefono' => '5587654321',
            'tipo_usuario' => 'cliente',
            'activo' => 1,
            'verificado' => 1
        ],
        [
            'nombre' => 'Carlos',
            'apellido' => 'Administrador',
            'correo' => 'admin@test.com',
            'contraseña' => 'prueba123',
            'telefono' => '5555555555',
            'tipo_usuario' => 'admin',
            'activo' => 1,
            'verificado' => 1
        ],
        [
            'nombre' => 'Ana Patricia',
            'apellido' => 'Hernández Silva',
            'correo' => 'vendedor2@test.com',
            'contraseña' => 'prueba123',
            'telefono' => '5511223344',
            'tipo_usuario' => 'vendedor',
            'activo' => 1,
            'verificado' => 1
        ],
        [
            'nombre' => 'Roberto',
            'apellido' => 'Martínez Ruiz',
            'correo' => 'cliente2@test.com',
            'contraseña' => 'prueba123',
            'telefono' => '5544556677',
            'tipo_usuario' => 'cliente',
            'activo' => 1,
            'verificado' => 1
        ]
    ];

    echo "<h1>🌱 AgroConecta - Creador de Usuarios de Prueba</h1>";
    echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; background: #f8f9fa; border-radius: 10px;'>";

    foreach ($usuariosPrueba as $datosUsuario) {
        try {
            // Verificar si el usuario ya existe
            $usuarioExistente = $usuario->findByEmail($datosUsuario['correo']);
            
            if ($usuarioExistente) {
                echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                echo "<strong>⚠️ Usuario ya existe:</strong> {$datosUsuario['correo']}";
                echo "</div>";
                continue;
            }

            // Crear el usuario usando el método del modelo
            $resultado = $usuario->createUser($datosUsuario);
            
            if ($resultado) {
                $usuariosCreados[] = $datosUsuario;
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                echo "<strong>✅ Usuario creado exitosamente:</strong><br>";
                echo "<strong>Nombre:</strong> {$datosUsuario['nombre']} {$datosUsuario['apellido']}<br>";
                echo "<strong>Email:</strong> {$datosUsuario['correo']}<br>";
                echo "<strong>Tipo:</strong> " . ucfirst($datosUsuario['tipo_usuario']) . "<br>";
                echo "<strong>Teléfono:</strong> {$datosUsuario['telefono']}";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                echo "<strong>❌ Error al crear usuario:</strong> {$datosUsuario['correo']}";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "<strong>❌ Error:</strong> " . $e->getMessage();
            echo "</div>";
        }
    }

    // Crear algunos productos de prueba para los vendedores
    if (!empty($usuariosCreados)) {
        echo "<hr style='margin: 30px 0;'>";
        echo "<h2>🌾 Productos de Prueba</h2>";
        
        // Obtener IDs de vendedores creados
        $vendedoresIds = [];
        foreach ($usuariosCreados as $usr) {
            if ($usr['tipo_usuario'] === 'vendedor') {
                $vendedorData = $usuario->findByEmail($usr['correo']);
                if ($vendedorData) {
                    $vendedoresIds[] = $vendedorData['id_usuario'];
                }
            }
        }

        if (!empty($vendedoresIds)) {
            $productosPrueba = [
                [
                    'nombre' => 'Tomates Cherry Orgánicos',
                    'descripcion' => 'Tomates cherry cultivados de manera orgánica, perfectos para ensaladas',
                    'categoria' => 'Verduras',
                    'precio' => 45.00,
                    'unidad_medida' => 'kg',
                    'stock_disponible' => 25,
                    'imagen_url' => '',
                    'activo' => 1
                ],
                [
                    'nombre' => 'Manzanas Red Delicious',
                    'descripcion' => 'Manzanas rojas dulces y crujientes, ideales para comer directamente',
                    'categoria' => 'Frutas',
                    'precio' => 38.50,
                    'unidad_medida' => 'kg',
                    'stock_disponible' => 50,
                    'imagen_url' => '',
                    'activo' => 1
                ],
                [
                    'nombre' => 'Lechuga Romana',
                    'descripcion' => 'Lechugas frescas y crujientes, perfectas para ensaladas César',
                    'categoria' => 'Hortalizas',
                    'precio' => 25.00,
                    'unidad_medida' => 'pieza',
                    'stock_disponible' => 30,
                    'imagen_url' => '',
                    'activo' => 1
                ],
                [
                    'nombre' => 'Frijoles Negro',
                    'descripcion' => 'Frijoles negros de alta calidad, ricos en proteína y fibra',
                    'categoria' => 'Legumbres',
                    'precio' => 35.00,
                    'unidad_medida' => 'kg',
                    'stock_disponible' => 40,
                    'imagen_url' => '',
                    'activo' => 1
                ],
                [
                    'nombre' => 'Maíz Amarillo',
                    'descripcion' => 'Maíz amarillo fresco, ideal para elotes y tortillas',
                    'categoria' => 'Cereales',
                    'precio' => 20.00,
                    'unidad_medida' => 'kg',
                    'stock_disponible' => 60,
                    'imagen_url' => '',
                    'activo' => 1
                ]
            ];

            $pdo = Database::getInstance()->getConnection();
            
            foreach ($productosPrueba as $index => $prod) {
                $vendedorId = $vendedoresIds[$index % count($vendedoresIds)];
                
                $stmt = $pdo->prepare("
                    INSERT INTO Producto (id_vendedor, nombre, descripcion, categoria, precio, unidad_medida, stock_disponible, imagen_url, activo, fecha_creacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $resultado = $stmt->execute([
                    $vendedorId,
                    $prod['nombre'],
                    $prod['descripcion'],
                    $prod['categoria'],
                    $prod['precio'],
                    $prod['unidad_medida'],
                    $prod['stock_disponible'],
                    $prod['imagen_url'],
                    $prod['activo']
                ]);
                
                if ($resultado) {
                    echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 8px; margin: 5px 0; border-radius: 5px; font-size: 0.9em;'>";
                    echo "<strong>🌱 Producto creado:</strong> {$prod['nombre']} - \${$prod['precio']} por {$prod['unidad_medida']}";
                    echo "</div>";
                }
            }
        }
    }

    echo "<hr style='margin: 30px 0;'>";
    echo "<h2>🔑 Credenciales de Acceso</h2>";
    echo "<div style='background: #e7f3ff; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>Todas las cuentas usan la misma contraseña:</strong> <code style='background: #f8f9fa; padding: 2px 6px; border-radius: 3px; color: #e83e8c;'>prueba123</code></p>";
    echo "<ul>";
    echo "<li><strong>Vendedor:</strong> vendedor@test.com</li>";
    echo "<li><strong>Cliente:</strong> cliente@test.com</li>";
    echo "<li><strong>Admin:</strong> admin@test.com</li>";
    echo "<li><strong>Vendedor 2:</strong> vendedor2@test.com</li>";
    echo "<li><strong>Cliente 2:</strong> cliente2@test.com</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>⚠️ Importante:</strong> Estos usuarios son solo para desarrollo y pruebas. ";
    echo "Elimina este archivo antes de llevar el sistema a producción.";
    echo "</div>";

    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔐 Ir al Login</a>";
    echo "<a href='dashboard.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Ver Dashboard</a>";
    echo "<a href='catalogo.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🛒 Ver Catálogo</a>";
    echo "</div>";

    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; margin: 20px; border-radius: 5px;'>";
    echo "<h2>❌ Error Fatal</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios de Prueba - AgroConecta</title>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }
    </style>
</head>
<body>
    <script>
        // Auto-scroll hacia abajo para ver los resultados
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.scrollTo({
                    top: document.body.scrollHeight,
                    behavior: 'smooth'
                });
            }, 500);
        });
    </script>
</body>
</html>