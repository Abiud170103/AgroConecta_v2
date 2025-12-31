<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Error del Servidor | AgroConecta</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff7b7b 0%, #d63031 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .error-container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            width: 90%;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #e74c3c;
            margin: 0;
            line-height: 1;
        }
        .error-title {
            font-size: 2rem;
            margin: 1rem 0;
            color: #2c3e50;
        }
        .error-message {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin: 1.5rem 0;
            line-height: 1.6;
        }
        .technical-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin: 1.5rem 0;
            text-align: left;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            color: #495057;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }
        .home-link {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
            margin: 0.5rem;
        }
        .home-link:hover {
            background: #219a52;
        }
        .retry-link {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
            margin: 0.5rem;
        }
        .retry-link:hover {
            background: #2980b9;
        }
        .show-details {
            background: #f39c12;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            margin: 1rem 0;
        }
        .show-details:hover {
            background: #e67e22;
        }
        .support-info {
            background: #ecf0f1;
            padding: 1rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        .support-info h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .agro-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .error-id {
            color: #95a5a6;
            font-size: 0.9rem;
            margin-top: 1rem;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="agro-icon">🚨</div>
        <h1 class="error-code">500</h1>
        <h2 class="error-title">Error Interno del Servidor</h2>
        <p class="error-message">
            ¡Ups! Algo salió mal en nuestros servidores. Nuestro equipo técnico ha sido notificado 
            y está trabajando para solucionar el problema lo antes posible.
        </p>
        
        <?php if(isset($error) && !empty($error) && $_ENV['APP_DEBUG'] ?? false): ?>
        <button class="show-details" onclick="toggleDetails()">
            🔍 Mostrar Detalles Técnicos
        </button>
        <div id="technicalInfo" class="technical-info">
            <strong>Error:</strong> <?= htmlspecialchars($error) ?><br>
            <?php if(isset($file)): ?>
                <strong>Archivo:</strong> <?= htmlspecialchars($file) ?><br>
            <?php endif; ?>
            <?php if(isset($line)): ?>
                <strong>Línea:</strong> <?= $line ?><br>
            <?php endif; ?>
            <?php if(isset($trace)): ?>
                <strong>Stack Trace:</strong><br>
                <pre><?= htmlspecialchars($trace) ?></pre>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="support-info">
            <h3>🛠️ ¿Qué puedes hacer?</h3>
            <ul style="text-align: left; color: #7f8c8d;">
                <li>Esperar unos minutos e intentar de nuevo</li>
                <li>Verificar tu conexión a internet</li>
                <li>Contactar nuestro soporte si el problema persiste</li>
                <li>Reportar este error con el ID mostrado abajo</li>
            </ul>
        </div>
        
        <a href="javascript:window.location.reload()" class="retry-link">🔄 Intentar de Nuevo</a>
        <a href="/" class="home-link">🏠 Volver al Inicio</a>
        
        <div class="error-id">
            Error ID: <?= uniqid('AGR-', true) ?> | 
            Timestamp: <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>

    <script>
        function toggleDetails() {
            const details = document.getElementById('technicalInfo');
            const button = document.querySelector('.show-details');
            
            if (details.style.display === 'none' || details.style.display === '') {
                details.style.display = 'block';
                button.textContent = '🔒 Ocultar Detalles Técnicos';
            } else {
                details.style.display = 'none';
                button.textContent = '🔍 Mostrar Detalles Técnicos';
            }
        }
        
        // Auto-refresh después de 30 segundos si no es modo debug
        <?php if(!($_ENV['APP_DEBUG'] ?? false)): ?>
        setTimeout(() => {
            if(confirm('¿Quieres recargar la página para intentar de nuevo?')) {
                window.location.reload();
            }
        }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>