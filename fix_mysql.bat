@echo off
echo === CONFIGURADOR MYSQL XAMPP ===
echo.

echo 1. Deteniendo MySQL...
taskkill /F /IM mysqld.exe >nul 2>&1

echo 2. Esperando 3 segundos...
timeout /t 3 /nobreak >nul

echo 3. Iniciando MySQL desde XAMPP...
cd /d C:\xampp\mysql\bin
start "MySQL" mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini --standalone

echo 4. Esperando que MySQL inicie...
timeout /t 5 /nobreak >nul

echo 5. Probando conexión...
mysql.exe -u root -e "SHOW DATABASES;" 2>nul
if errorlevel 1 (
    echo ❌ Conexión falló
    echo Intentando con contraseña vacía desde PHP...
) else (
    echo ✅ MySQL funcionando correctamente
)

echo.
echo === TERMINADO ===
pause