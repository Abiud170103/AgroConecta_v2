<?php
session_start();

echo "<h2>Debug de Variables de Sesión - AgroConecta</h2>";
echo "<h3>Variables $_SESSION disponibles:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<hr>";
echo "<h3>Verificaciones específicas:</h3>";
echo "user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO EXISTE') . "<br>";
echo "user_tipo: " . (isset($_SESSION['user_tipo']) ? $_SESSION['user_tipo'] : 'NO EXISTE') . "<br>";
echo "tipo: " . (isset($_SESSION['tipo']) ? $_SESSION['tipo'] : 'NO EXISTE') . "<br>";
echo "user_nombre: " . (isset($_SESSION['user_nombre']) ? $_SESSION['user_nombre'] : 'NO EXISTE') . "<br>";
echo "nombre: " . (isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'NO EXISTE') . "<br>";
echo "user_email: " . (isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'NO EXISTE') . "<br>";
echo "correo: " . (isset($_SESSION['correo']) ? $_SESSION['correo'] : 'NO EXISTE') . "<br>";

echo "<hr>";
echo "<h3>Estados de autenticación:</h3>";
echo "Está autenticado (user_id): " . (isset($_SESSION['user_id']) ? 'SÍ' : 'NO') . "<br>";
echo "Tiene tipo (user_tipo): " . (isset($_SESSION['user_tipo']) ? 'SÍ' : 'NO') . "<br>";
echo "Tiene tipo (tipo): " . (isset($_SESSION['tipo']) ? 'SÍ' : 'NO') . "<br>";

if (isset($_SESSION['user_id']) && (isset($_SESSION['user_tipo']) || isset($_SESSION['tipo']))) {
    echo "<br><strong style='color: green;'>✅ Dashboard debería funcionar</strong>";
    echo "<br><a href='dashboard.php'>Ir al Dashboard</a>";
} else {
    echo "<br><strong style='color: red;'>❌ Faltan variables de sesión</strong>";
    echo "<br><a href='login.php'>Ir al Login</a>";
}
?>