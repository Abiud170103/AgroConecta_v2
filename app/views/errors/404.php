<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada | AgroConecta</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            max-width: 500px;
            width: 90%;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #27ae60;
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
        .home-link {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
            margin-top: 1rem;
        }
        .home-link:hover {
            background: #219a52;
        }
        .search-box {
            margin: 2rem 0;
        }
        .search-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 1rem;
            box-sizing: border-box;
        }
        .search-btn {
            background: #3498db;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
        }
        .search-btn:hover {
            background: #2980b9;
        }
        .suggestions {
            margin-top: 1.5rem;
            text-align: left;
        }
        .suggestions h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        .suggestions a {
            display: block;
            color: #3498db;
            text-decoration: none;
            margin: 0.5rem 0;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ecf0f1;
        }
        .suggestions a:hover {
            color: #2980b9;
        }
        .agro-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="agro-icon">🌱</div>
        <h1 class="error-code">404</h1>
        <h2 class="error-title">¡Oops! Página no encontrada</h2>
        <p class="error-message">
            Lo sentimos, la página que buscas parece haberse perdido en nuestros campos. 
            Pero no te preocupes, tenemos muchas otras cosas frescas para ofrecerte.
        </p>
        
        <div class="search-box">
            <form action="/productos/buscar" method="GET">
                <input type="text" name="q" class="search-input" placeholder="¿Qué producto buscas?" autofocus>
                <button type="submit" class="search-btn">🔍 Buscar Productos</button>
            </form>
        </div>
        
        <div class="suggestions">
            <h3>¿Tal vez buscabas algo de esto?</h3>
            <a href="/">🏠 Página Principal</a>
            <a href="/productos">🛒 Ver Todos los Productos</a>
            <a href="/productos/categoria/vegetales">🥕 Vegetales Frescos</a>
            <a href="/productos/categoria/frutas">🍎 Frutas de Temporada</a>
            <a href="/contacto">📧 Contactar Soporte</a>
        </div>
        
        <a href="/" class="home-link">🌾 Volver al Inicio</a>
    </div>

    <script>
        // Auto-focus en el campo de búsqueda
        document.querySelector('.search-input').focus();
        
        // Enviar búsqueda al presionar Enter
        document.querySelector('.search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    </script>
</body>
</html>