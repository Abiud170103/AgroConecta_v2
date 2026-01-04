<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba API - Carrito y Favoritos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h2>🧪 Prueba de APIs - Carrito y Favoritos</h2>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-shopping-cart"></i> API Carrito</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="productoIdCarrito" class="form-label">ID del Producto:</label>
                            <input type="number" class="form-control" id="productoIdCarrito" value="1">
                        </div>
                        <div class="mb-3">
                            <label for="cantidadCarrito" class="form-label">Cantidad:</label>
                            <input type="number" class="form-control" id="cantidadCarrito" value="1" min="1">
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" onclick="agregarAlCarrito()">
                                Agregar al Carrito
                            </button>
                            <button class="btn btn-info" onclick="obtenerCarrito()">
                                Ver Carrito
                            </button>
                            <button class="btn btn-warning" onclick="limpiarCarrito()">
                                Limpiar Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-heart"></i> API Favoritos</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="productoIdFavorito" class="form-label">ID del Producto:</label>
                            <input type="number" class="form-control" id="productoIdFavorito" value="1">
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-danger" onclick="toggleFavorito()">
                                Toggle Favorito
                            </button>
                            <button class="btn btn-info" onclick="obtenerFavoritos()">
                                Ver Favoritos
                            </button>
                            <button class="btn btn-secondary" onclick="verificarFavorito()">
                                Verificar Estado
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5><i class="fas fa-terminal"></i> Resultado de Pruebas</h5>
            </div>
            <div class="card-body">
                <div id="resultado" class="border rounded p-3 bg-light" style="height: 300px; overflow-y: auto;">
                    <p class="text-muted">Los resultados de las pruebas aparecerán aquí...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarResultado(titulo, data, tipo = 'info') {
            const resultado = document.getElementById('resultado');
            const timestamp = new Date().toLocaleTimeString();
            
            const color = {
                'success': 'text-success',
                'error': 'text-danger',
                'info': 'text-primary',
                'warning': 'text-warning'
            };
            
            resultado.innerHTML += `
                <div class="mb-2">
                    <strong class="${color[tipo] || 'text-primary'}">[${timestamp}] ${titulo}:</strong>
                    <pre class="mt-1 mb-0"><code>${JSON.stringify(data, null, 2)}</code></pre>
                </div>
                <hr>
            `;
            
            // Scroll al final
            resultado.scrollTop = resultado.scrollHeight;
        }
        
        function limpiarResultados() {
            document.getElementById('resultado').innerHTML = '<p class="text-muted">Los resultados de las pruebas aparecerán aquí...</p>';
        }
        
        // Funciones para Carrito
        function agregarAlCarrito() {
            const productoId = document.getElementById('productoIdCarrito').value;
            const cantidad = document.getElementById('cantidadCarrito').value;
            
            fetch('api/carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'agregar',
                    id: parseInt(productoId),
                    cantidad: parseInt(cantidad)
                })
            })
            .then(response => response.json())
            .then(data => {
                mostrarResultado('Agregar al Carrito', data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                mostrarResultado('Error Carrito', error, 'error');
            });
        }
        
        function obtenerCarrito() {
            fetch('api/carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'obtener'
                })
            })
            .then(response => response.json())
            .then(data => {
                mostrarResultado('Obtener Carrito', data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                mostrarResultado('Error Carrito', error, 'error');
            });
        }
        
        function limpiarCarrito() {
            if (confirm('¿Estás seguro de que deseas limpiar el carrito?')) {
                fetch('api/carrito.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        action: 'limpiar'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    mostrarResultado('Limpiar Carrito', data, data.success ? 'success' : 'error');
                })
                .catch(error => {
                    mostrarResultado('Error Carrito', error, 'error');
                });
            }
        }
        
        // Funciones para Favoritos
        function toggleFavorito() {
            const productoId = document.getElementById('productoIdFavorito').value;
            
            fetch('api/favoritos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'toggle',
                    id: parseInt(productoId)
                })
            })
            .then(response => response.json())
            .then(data => {
                mostrarResultado('Toggle Favorito', data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                mostrarResultado('Error Favoritos', error, 'error');
            });
        }
        
        function obtenerFavoritos() {
            fetch('api/favoritos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'obtener'
                })
            })
            .then(response => response.json())
            .then(data => {
                mostrarResultado('Obtener Favoritos', data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                mostrarResultado('Error Favoritos', error, 'error');
            });
        }
        
        function verificarFavorito() {
            const productoId = document.getElementById('productoIdFavorito').value;
            
            fetch('api/favoritos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'verificar',
                    id: parseInt(productoId)
                })
            })
            .then(response => response.json())
            .then(data => {
                mostrarResultado('Verificar Favorito', data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                mostrarResultado('Error Favoritos', error, 'error');
            });
        }
        
        // Limpiar resultados al inicio
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar botón para limpiar resultados
            const card = document.querySelector('.card:last-child .card-header');
            card.innerHTML += ` <button class="btn btn-sm btn-outline-light float-end" onclick="limpiarResultados()">Limpiar</button>`;
        });
    </script>
</body>
</html>