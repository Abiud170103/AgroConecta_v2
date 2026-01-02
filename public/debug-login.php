<?php
// Página de debug para probar router
echo "<h1>Debug POST to /login</h1>";
echo "<p>Método: " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p>URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Script: " . $_SERVER['SCRIPT_NAME'] . "</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Datos POST recibidos:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
}

echo "<form method='POST' action='login'>";
echo "<input type='email' name='email' value='test@agroconecta.com' required><br>";
echo "<input type='password' name='password' value='123456789' required><br>";
echo "<button type='submit'>Test Login</button>";
echo "</form>";
?>