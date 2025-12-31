# Modelos de AgroConecta

Esta carpeta contiene todos los modelos del sistema AgroConecta, implementando el patrón Active Record con funcionalidades extendidas.

## 🏗️ Arquitectura

### Clase Base: `Model.php`
- **Patrón**: Active Record
- **Base de datos**: MySQL con PDO
- **Funcionalidades**:
  - CRUD básico (Create, Read, Update, Delete)
  - Filtrado de campos permitidos (`$fillable`)
  - Transacciones de base de datos
  - Paginación automática
  - Búsqueda con LIKE
  - Conteo de registros
  - Timestamps automáticos

## 📊 Modelos Disponibles

### 1. `Usuario.php` - Gestión de Usuarios
**Propósito**: Manejo de cuentas de usuarios (clientes, vendedores, admin)

**Funcionalidades principales**:
- ✅ Registro y autenticación
- ✅ Verificación por email
- ✅ Reset de contraseñas
- ✅ Gestión de roles (cliente/vendedor/admin)
- ✅ Soft delete (desactivación)

**Métodos destacados**:
```php
$usuario = new Usuario();
$user = $usuario->verifyLogin($email, $password);
$token = $usuario->generateResetToken($email);
$vendedores = $usuario->getVendedores(10);
```

### 2. `Producto.php` - Catálogo de Productos
**Propósito**: Gestión del inventario y catálogo de productos agrícolas

**Funcionalidades principales**:
- ✅ Catálogo con categorías
- ✅ Control de stock automático
- ✅ Búsqueda full-text
- ✅ Productos destacados
- ✅ Filtros por ubicación y temporada

**Métodos destacados**:
```php
$producto = new Producto();
$productos = $producto->buscarProductos('tomate', 'verduras');
$destacados = $producto->getProductosDestacados(8);
$stockBajo = $producto->getProductosStockBajo($vendedorId);
```

### 3. `Pedido.php` - Sistema de Órdenes
**Propósito**: Gestión completa del flujo de pedidos

**Funcionalidades principales**:
- ✅ Creación de pedidos con transacciones
- ✅ Estados del pedido (pendiente → entregado)
- ✅ Generación de números únicos
- ✅ Cancelación con restauración de stock
- ✅ Historial completo

**Estados válidos**:
- `pendiente` → `confirmado` → `preparando` → `enviado` → `entregado`
- `cancelado` (en cualquier momento)

**Métodos destacados**:
```php
$pedido = new Pedido();
$pedidoId = $pedido->crearPedido($usuario, $items, $total, $direccion, $telefono);
$pedido->actualizarEstado($pedidoId, 'enviado');
$pedidos = $pedido->getPedidosUsuario($userId);
```

### 4. `DetallePedido.php` - Items de Pedidos
**Propósito**: Gestión de items individuales y estadísticas

**Funcionalidades principales**:
- ✅ Detalles de productos por pedido
- ✅ Cálculo de subtotales
- ✅ Reportes de ventas
- ✅ Productos más vendidos
- ✅ Estadísticas por vendedor

### 5. `Carrito.php` - Carrito de Compras
**Propósito**: Carrito temporal antes de crear pedido

**Funcionalidades principales**:
- ✅ Gestión de items temporales
- ✅ Cálculo de totales en tiempo real
- ✅ Verificación de stock disponible
- ✅ Agrupación por vendedor
- ✅ Limpieza automática de items antiguos

**Métodos destacados**:
```php
$carrito = new Carrito();
$carrito->agregarProducto($userId, $productoId, 2);
$items = $carrito->getItemsCarrito($userId);
$total = $carrito->calcularTotal($userId);
```

### 6. `Pago.php` - Procesamiento de Pagos
**Propósito**: Gestión de transacciones y pagos

**Funcionalidades principales**:
- ✅ Múltiples métodos de pago
- ✅ Estados de transacción
- ✅ Integración con gateways
- ✅ Reembolsos automatizados
- ✅ Reportes financieros

**Estados de pago**:
- `pendiente` → `procesando` → `completado`
- `fallido` / `cancelado` / `reembolsado`

**Métodos destacados**:
```php
$pago = new Pago();
$pagoId = $pago->crearPago($pedidoId, $monto, 'mercado_pago');
$pago->confirmarPago($transaccionId, $referencia);
$estadisticas = $pago->getEstadisticasPorMetodo();
```

### 7. `Direccion.php` - Direcciones de Entrega
**Propósito**: Gestión de direcciones de usuarios

**Funcionalidades principales**:
- ✅ Múltiples direcciones por usuario
- ✅ Dirección principal automática
- ✅ Formateo de direcciones
- ✅ Cálculo de costos de envío
- ✅ Validación de códigos postales

**Métodos destacados**:
```php
$direccion = new Direccion();
$direccionId = $direccion->crearDireccion($data);
$principal = $direccion->getDireccionPrincipal($userId);
$direccion->establecerPrincipal($direccionId, $userId);
```

### 8. `Notificacion.php` - Sistema de Notificaciones
**Propósito**: Comunicación con usuarios del sistema

**Funcionalidades principales**:
- ✅ Notificaciones por tipo (pedido, pago, producto, etc.)
- ✅ Estado de lectura
- ✅ Notificaciones automáticas de eventos
- ✅ Notificaciones masivas
- ✅ Limpieza automática

**Tipos de notificación**:
- `pedido` - Estados de órdenes
- `pago` - Transacciones
- `producto` - Stock, ventas
- `cuenta` - Verificación, bienvenida
- `sistema` - Mantenimiento, actualizaciones
- `promocion` - Ofertas especiales

**Métodos destacados**:
```php
$notificacion = new Notificacion();
$notificacion->notificarNuevoPedido($userId, $numeroPedido, $total);
$notificacion->notificarCambioEstadoPedido($userId, $numero, 'enviado');
$noLeidas = $notificacion->contarNoLeidas($userId);
```

## 🔄 Relaciones entre Modelos

```
Usuario (1) ←→ (N) Producto
Usuario (1) ←→ (N) Pedido
Usuario (1) ←→ (N) Direccion
Usuario (1) ←→ (N) Carrito
Usuario (1) ←→ (N) Notificacion

Pedido (1) ←→ (N) DetallePedido
Pedido (1) ←→ (1) Pago

Producto (1) ←→ (N) DetallePedido
Producto (1) ←→ (N) Carrito
```

## 💾 Base de Datos

Todos los modelos están diseñados para trabajar con el esquema de base de datos en:
- `database/schema.sql` - Estructura de tablas
- `database/seeders.sql` - Datos de prueba

## 🚀 Uso Básico

### Configuración
Asegúrate de que tu clase `Database` esté configurada:

```php
// config/database.php
return [
    'host' => 'localhost',
    'database' => 'agroconecta',
    'username' => 'tu_usuario',
    'password' => 'tu_password'
];
```

### Ejemplo de Uso

```php
<?php
// Incluir autoloader o archivos necesarios
require_once 'app/core/Database.php';
require_once 'app/models/Usuario.php';

// Crear usuario
$usuario = new Usuario();
$nuevoUsuario = $usuario->createUser([
    'nombre' => 'Juan',
    'apellido' => 'Pérez',
    'correo' => 'juan@example.com',
    'contraseña' => '123456',
    'tipo_usuario' => 'cliente'
]);

// Buscar productos
$producto = new Producto();
$productos = $producto->buscarProductos('tomate');

// Crear pedido
$pedido = new Pedido();
$pedidoId = $pedido->crearPedido($datosUsuario, $items, $total, $direccion, $telefono);
?>
```

## 🔧 Mantenimiento

### Limpieza Automática
Algunos modelos incluyen métodos de limpieza:

```php
// Limpiar carritos antiguos (30+ días)
$carrito = new Carrito();
$carrito->limpiarCarritosAntiguos(30);

// Eliminar notificaciones leídas antiguas
$notificacion = new Notificacion();
$notificacion->eliminarAntiguas(30);

// Limpiar direcciones sin usar
$direccion = new Direccion();
$direccion->limpiarDireccionesAntiguas(365);
```

### Estadísticas
Cada modelo incluye métodos para generar reportes:

```php
// Estadísticas de usuarios
$usuario = new Usuario();
$stats = $usuario->getStats();

// Productos más vendidos
$detalle = new DetallePedido();
$topProductos = $detalle->getProductosMasVendidos(10);

// Estadísticas de pagos
$pago = new Pago();
$reportePagos = $pago->getEstadisticasPorMetodo();
```

## 🛡️ Seguridad

- **Prepared Statements**: Protección contra SQL injection
- **Password Hashing**: Contraseñas con `password_hash()`
- **Filtrado de campos**: Solo campos en `$fillable` son modificables
- **Transacciones**: Operaciones críticas con rollback automático
- **Validaciones**: Verificación de datos antes de inserción

## 📚 Próximos Pasos

1. **Controladores**: Crear controladores que usen estos modelos
2. **Validaciones**: Agregar validaciones más específicas
3. **Cache**: Implementar cache para consultas frecuentes
4. **APIs**: Crear endpoints RESTful
5. **Tests**: Agregar pruebas unitarias

---

**Equipo AgroConecta 6CV1** - Ingeniería de Software