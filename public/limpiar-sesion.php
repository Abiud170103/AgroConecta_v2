<?php
/**
 * Limpia sesión completamente
 */
session_start();
session_destroy();
session_start();

echo "<h1>✅ Sesión Limpiada</h1>";
echo "<p>La sesión ha sido completamente limpiada.</p>";
echo "<p><a href='login.php' style='background:#007bff;color:white;padding:10px;text-decoration:none;border-radius:5px;'>Ir a Login Principal</a></p>";
echo "<p><a href='login-simple.php' style='background:#28a745;color:white;padding:10px;text-decoration:none;border-radius:5px;margin-left:10px;'>Ir a Login Simple</a></p>";
?>