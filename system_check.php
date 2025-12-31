<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroConecta - Verificación del Sistema</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin: 0;
        }
        .header p {
            color: #7f8c8d;
            font-size: 1.2rem;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            border-left: 4px solid transparent;
        }
        .check-item.success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .check-item.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .check-item.error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .icon {
            font-size: 1.5rem;
            margin-right: 15px;
            min-width: 30px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-top: 3px solid #007bff;
        }
        .info-card h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌱 AgroConecta</h1>
            <p>Verificación del Sistema</p>
        </div>

        <?php
        $checks = [
            'PHP Version' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'MySQL Extension' => extension_loaded('pdo_mysql'),
            'GD Extension' => extension_loaded('gd'),
            'FileInfo Extension' => extension_loaded('fileinfo'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'Session Support' => function_exists('session_start'),
            'JSON Support' => function_exists('json_encode'),
            'cURL Extension' => extension_loaded('curl')
        ];

        $database_connection = false;
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=agroconecta_db;charset=utf8mb4", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database_connection = true;
        } catch (PDOException $e) {
            $database_error = $e->getMessage();
        }

        foreach ($checks as $check => $result) {
            $icon = $result ? '✅' : '❌';
            $class = $result ? 'success' : 'error';
            echo "<div class='check-item {$class}'>";
            echo "<span class='icon'>{$icon}</span>";
            echo "<span><strong>{$check}:</strong> " . ($result ? 'OK' : 'FALTA') . "</span>";
            echo "</div>";
        }

        // Verificación de base de datos
        $icon = $database_connection ? '✅' : '❌';
        $class = $database_connection ? 'success' : 'error';
        echo "<div class='check-item {$class}'>";
        echo "<span class='icon'>{$icon}</span>";
        echo "<span><strong>Conexión Base de Datos:</strong> " . ($database_connection ? 'OK' : 'ERROR') . "</span>";
        if (!$database_connection) {
            echo "<br><small>Error: " . ($database_error ?? 'Desconocido') . "</small>";
        }
        echo "</div>";

        // Verificación de archivos
        $required_files = [
            '.env' => file_exists('.env'),
            'config/database.php' => file_exists('config/database.php'),
            'database/schema.sql' => file_exists('database/schema.sql'),
            'app/core/Router.php' => file_exists('app/core/Router.php')
        ];

        foreach ($required_files as $file => $exists) {
            $icon = $exists ? '✅' : '❌';
            $class = $exists ? 'success' : 'error';
            echo "<div class='check-item {$class}'>";
            echo "<span class='icon'>{$icon}</span>";
            echo "<span><strong>Archivo {$file}:</strong> " . ($exists ? 'EXISTE' : 'FALTA') . "</span>";
            echo "</div>";
        }
        ?>

        <div class="info-grid">
            <div class="info-card">
                <h3>📊 Información del Sistema</h3>
                <p><strong>PHP:</strong> <?= PHP_VERSION ?></p>
                <p><strong>OS:</strong> <?= PHP_OS ?></p>
                <p><strong>Servidor:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido' ?></p>
                <p><strong>Memoria:</strong> <?= ini_get('memory_limit') ?></p>
            </div>

            <div class="info-card">
                <h3>🌐 URLs del Proyecto</h3>
                <p><strong>Home:</strong> <a href="index.php">index.php</a></p>
                <p><strong>Test:</strong> <a href="quick_test.php">quick_test.php</a></p>
                <p><strong>DB Install:</strong> <a href="install_database.php">install_database.php</a></p>
                <p><strong>PHPMyAdmin:</strong> <a href="/phpmyadmin" target="_blank">/phpmyadmin</a></p>
            </div>

            <div class="info-card">
                <h3>🔧 Acciones Rápidas</h3>
                <p><a href="?action=phpinfo">Ver PHP Info</a></p>
                <p><a href="install_database.php">Instalar Base de Datos</a></p>
                <p><a href="test_connection.php">Test Conexión</a></p>
                <p><a href="quick_test.php">Test Rápido</a></p>
            </div>
        </div>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'phpinfo'): ?>
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                <h3>PHP Configuration</h3>
                <?php phpinfo(); ?>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>AgroConecta - Sistema de apoyo a agricultores locales</p>
            <p>Equipo 6CV1 - <?= date('Y') ?></p>
        </div>
    </div>
</body>
</html>