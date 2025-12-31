@echo off
echo =============================================
echo     INSTALADOR RAPIDO AGROCONECTA
echo =============================================
echo.

REM Verificar si XAMPP está instalado
if exist "C:\xampp\mysql\bin\mysql.exe" (
    echo [OK] XAMPP encontrado
    set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe
    set PHP_PATH=C:\xampp\php\php.exe
) else if exist "C:\laragon\bin\mysql\mysql-*\bin\mysql.exe" (
    echo [OK] Laragon encontrado
    for /d %%i in (C:\laragon\bin\mysql\mysql-*) do set MYSQL_PATH=%%i\bin\mysql.exe
    for /d %%i in (C:\laragon\bin\php\php-*) do set PHP_PATH=%%i\php.exe
) else (
    echo [ERROR] No se encontró XAMPP o Laragon
    echo Por favor instala uno de estos programas primero:
    echo - XAMPP: https://www.apachefriends.org/
    echo - Laragon: https://laragon.org/
    pause
    exit /b 1
)

echo.
echo Creando base de datos...

REM Crear la base de datos
"%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS agroconecta_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if %errorlevel% == 0 (
    echo [OK] Base de datos creada
) else (
    echo [ERROR] No se pudo crear la base de datos
    pause
    exit /b 1
)

echo.
echo Instalando tablas...
"%PHP_PATH%" install_database.php

echo.
echo =============================================
echo     INSTALACION COMPLETADA
echo =============================================
echo.
echo Tu proyecto estará disponible en:
echo http://localhost/AgroConecta
echo.
echo Para PHPMyAdmin:
echo http://localhost/phpmyadmin
echo.
pause