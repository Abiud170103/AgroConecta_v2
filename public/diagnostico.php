<?php
echo "🔧 Diagnóstico de AgroConecta<br>";
echo "Fecha: " . date('Y-m-d H:i:s') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Directorio actual: " . __DIR__ . "<br>";
echo "Archivo actual: " . __FILE__ . "<br>";
echo "<br>";

echo "Verificando archivos importantes:<br>";
$archivos = [
    'index.php' => file_exists('index.php'),
    '../config/database.php' => file_exists('../config/database.php'),
    '../core/Router.php' => file_exists('../core/Router.php'),
    '../core/SessionManager.php' => file_exists('../core/SessionManager.php'),
    '../app/controllers/AuthController.php' => file_exists('../app/controllers/AuthController.php')
];

foreach ($archivos as $archivo => $existe) {
    echo "- $archivo: " . ($existe ? '✅ Existe' : '❌ No existe') . "<br>";
}

echo "<br>";
echo "Variables del servidor:<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'No definida') . "<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'No definida') . "<br>";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'No definida') . "<br>";

echo "<br>";
echo "<a href='index.php'>Ir a la aplicación principal</a><br>";
echo "<a href='test-login.php'>Test Login</a><br>";
echo "<a href='crear-usuarios-prueba.php'>Crear Usuarios</a><br>";
?>