@echo off
REM =====================================================
REM AgroConecta - Script de Instalación de Base de Datos (Windows)
REM Sistema de apoyo a agricultores locales
REM Equipo: 6CV1 - ESCOM IPN
REM =====================================================

echo 🌱 Instalando Base de Datos de AgroConecta...
echo.

REM Configuración (modificar según tu entorno)
set DB_HOST=localhost
set DB_USER=root
set DB_PASS=
set DB_NAME=agroconecta_db

echo 📡 Verificando conexión a MySQL...

REM Verificar si MySQL está disponible
mysql -h %DB_HOST% -u %DB_USER% -e "SELECT 1" >nul 2>&1
if errorlevel 1 (
    echo ❌ Error: No se puede conectar a MySQL
    echo    Verifica que MySQL esté ejecutándose y las credenciales sean correctas
    echo    Si usas XAMPP: Asegurate de iniciar MySQL desde el panel de control
    pause
    exit /b 1
)

echo ✅ Conexión a MySQL exitosa
echo.

REM Ejecutar schema
echo 🗄️  Creando esquema de base de datos...
mysql -h %DB_HOST% -u %DB_USER% < schema.sql
if errorlevel 1 (
    echo ❌ Error al crear el esquema
    pause
    exit /b 1
)

echo ✅ Esquema creado exitosamente

REM Ejecutar seeders
echo 🌱 Insertando datos de prueba...
mysql -h %DB_HOST% -u %DB_USER% < seeders.sql
if errorlevel 1 (
    echo ❌ Error al insertar datos de prueba
    pause
    exit /b 1
)

echo ✅ Datos de prueba insertados exitosamente
echo.

echo 🎉 ¡Instalación completada!
echo.
echo 📋 Información de la base de datos:
echo    • Base de datos: %DB_NAME%
echo    • Tablas creadas: 9
echo    • Usuarios de prueba: 11
echo    • Productos de prueba: 16
echo    • Pedidos de prueba: 3
echo.
echo 🔐 Cuentas de prueba:
echo    • Admin: admin@agroconecta.com / password123
echo    • Vendedor: juan.mendoza@gmail.com / password123
echo    • Cliente: carlos.lopez@cliente.com / password123
echo.
echo 🚀 ¡Ya puedes comenzar a desarrollar AgroConecta!
echo.
pause