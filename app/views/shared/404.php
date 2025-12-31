<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada | AgroConecta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
        }
        
        .error-icon {
            font-size: 8rem;
            color: #ff6b6b;
            margin-bottom: 30px;
            animation: bounce 2s infinite;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .error-message {
            font-size: 1.5rem;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        .error-description {
            color: #95a5a6;
            margin-bottom: 40px;
            font-size: 1.1rem;
        }
        
        .btn-home {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: transform 0.3s ease;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
        }
        
        .suggestions {
            text-align: left;
            margin-top: 40px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 15px;
        }
        
        .suggestions h5 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .suggestions ul {
            color: #6c757d;
        }
        
        .suggestions li {
            margin-bottom: 10px;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }
        
        @media (max-width: 768px) {
            .error-container {
                padding: 30px 20px;
                margin: 20px;
            }
            
            .error-icon {
                font-size: 5rem;
            }
            
            .error-code {
                font-size: 4rem;
            }
            
            .error-message {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="error-container mx-auto">
                    <div class="error-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    
                    <div class="error-code">404</div>
                    
                    <div class="error-message">
                        ¡Oops! Página no encontrada
                    </div>
                    
                    <div class="error-description">
                        La página que buscas parece haberse perdido en el campo. 
                        No te preocupes, te ayudamos a encontrar el camino de regreso.
                    </div>
                    
                    <a href="/" class="btn btn-primary btn-home">
                        <i class="fas fa-home"></i>
                        Volver al Inicio
                    </a>
                    
                    <div class="suggestions">
                        <h5><i class="fas fa-lightbulb"></i> ¿Qué puedes hacer?</h5>
                        <ul>
                            <li>Verifica que la URL esté escrita correctamente</li>
                            <li>Usa el menú de navegación para encontrar lo que buscas</li>
                            <li>Explora nuestros productos agrícolas</li>
                            <li>Contacta con nuestro equipo de soporte</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="/" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                        <a href="/productos" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-shopping-basket"></i> Productos
                        </a>
                        <a href="/contacto" class="btn btn-outline-secondary">
                            <i class="fas fa-envelope"></i> Contacto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>