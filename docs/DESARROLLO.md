# 🔧 Guía de Desarrollo - AgroConecta

## Índice
1. [Arquitectura del Sistema](#arquitectura-del-sistema)
2. [Configuración del Entorno de Desarrollo](#configuración-del-entorno-de-desarrollo)
3. [Estructura del Proyecto](#estructura-del-proyecto)
4. [Patrones de Desarrollo](#patrones-de-desarrollo)
5. [API y Funcionalidades](#api-y-funcionalidades)
6. [Base de Datos](#base-de-datos)
7. [Testing y QA](#testing-y-qa)
8. [Deployment](#deployment)

---

## Arquitectura del Sistema

### 🏗️ Visión General

AgroConecta utiliza una **arquitectura híbrida** que combina patrones tradicionales con funcionalidades modernas:

- **Backend:** PHP 7.4+ con patrón MVC personalizado
- **Frontend:** HTML5 + CSS3 + JavaScript vanilla + Bootstrap 5
- **Base de Datos:** MySQL 8.0+ / MariaDB 10.5+
- **Sesiones:** PHP Sessions nativas con configuración segura
- **Archivos:** Sistema de upload con validación robusta

### 🔄 Flujo de Datos

```
Cliente (Browser)
    ↓
public/*.php (Entry Points)
    ↓
core/ (Routing & Authentication)
    ↓
app/controllers/ (Business Logic)
    ↓
app/models/ (Data Layer)
    ↓
database/ (MySQL)
```

### 🛡️ Capas de Seguridad

1. **Validación de Entrada:** Sanitización en todos los entry points
2. **Autenticación:** Sistema de sesiones con tokens CSRF
3. **Autorización:** Control de roles granular
4. **Encriptación:** Passwords con bcrypt, datos sensibles protegidos

---

## Configuración del Entorno de Desarrollo

### 💻 Requisitos

```bash
# Software requerido
PHP 7.4+ (Recomendado: 8.1+)
MySQL 8.0+ o MariaDB 10.5+
Apache 2.4+ o Nginx 1.18+
Composer (para dependencias futuras)
Git (control de versiones)

# Extensiones PHP requeridas
php-mysql
php-gd
php-curl
php-zip
php-json
php-mbstring
```

### 🚀 Setup Inicial

```bash
# Clonar repositorio
git clone [repo-url] agroconecta-dev
cd agroconecta-dev

# Configurar entorno
cp .env.example .env.dev
```

**Configuración .env.dev:**
```env
# Base de datos
DB_HOST=localhost
DB_DATABASE=agroconecta_dev
DB_USERNAME=dev_user
DB_PASSWORD=dev_password

# URLs
SITE_URL=http://localhost:8000/agroconecta-dev
API_BASE_URL=http://localhost:8000/agroconecta-dev/api

# Debug
DEBUG=true
LOG_LEVEL=debug
DISPLAY_ERRORS=true

# Email (usar Mailtrap para desarrollo)
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_mailtrap_user
MAIL_PASSWORD=your_mailtrap_pass
```

### 🗄️ Base de Datos de Desarrollo

```sql
-- Crear BD de desarrollo
CREATE DATABASE agroconecta_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usuario de desarrollo
CREATE USER 'dev_user'@'localhost' IDENTIFIED BY 'dev_password';
GRANT ALL PRIVILEGES ON agroconecta_dev.* TO 'dev_user'@'localhost';

-- Instalar esquema
SOURCE database/schema.sql;
SOURCE database/test_data.sql;
```

---

## Estructura del Proyecto

### 📁 Organización de Archivos

```
AgroConecta_v2/
├── 📱 app/                          # Aplicación principal
│   ├── 🎮 controllers/              # Controladores MVC
│   │   ├── AuthController.php       # Autenticación
│   │   ├── UserController.php       # Gestión usuarios
│   │   ├── ProductController.php    # Gestión productos
│   │   └── AdminController.php      # Panel administración
│   ├── 🗃️ models/                   # Modelos de datos
│   │   ├── User.php                 # Modelo usuario
│   │   ├── Product.php              # Modelo producto
│   │   ├── Order.php                # Modelo pedido
│   │   └── Database.php             # Conexión BD
│   ├── 👁️ views/                    # Vistas y templates
│   │   ├── auth/                    # Vistas autenticación
│   │   ├── dashboard/               # Vistas dashboard
│   │   ├── products/                # Vistas productos
│   │   └── shared/                  # Componentes compartidos
│   └── ⚙️ config/                   # Configuración app
├── 🧠 core/                         # Núcleo del framework
│   ├── Router.php                   # Sistema de rutas
│   ├── Session.php                  # Manejo de sesiones
│   ├── Validator.php                # Validaciones
│   └── Helper.php                   # Funciones auxiliares
├── 🌐 public/                       # Archivos web públicos
│   ├── 🎨 css/                      # Hojas de estilo
│   ├── ⚡ js/                       # JavaScript
│   ├── 🖼️ images/                   # Imágenes del sistema
│   ├── 📤 uploads/                  # Archivos usuario
│   ├── index.php                    # Punto entrada principal
│   ├── dashboard.php                # Dashboard principal
│   ├── catalogo.php                 # Catálogo productos
│   └── *.php                        # Páginas específicas
├── 🗄️ database/                     # Scripts BD
│   ├── schema.sql                   # Estructura tablas
│   ├── data.sql                     # Datos iniciales
│   └── migrations/                  # Migraciones
├── 📊 logs/                         # Archivos de log
├── ⚙️ config/                       # Configuración global
└── 📚 docs/                         # Documentación
```

### 🎯 Convenciones de Naming

```php
// Archivos
PascalCase.php          // Clases: UserController.php
kebab-case.php          // Páginas: forgot-password.php
snake_case.sql          // BD: user_products.sql

// Variables y funciones
$camelCase              // Variables: $userData
snake_case()            // Funciones: get_user_data()
PascalCase              // Clases: UserManager
UPPER_SNAKE_CASE        // Constantes: MAX_FILE_SIZE
```

---

## Patrones de Desarrollo

### 🏗️ Arquitectura MVC Híbrida

El sistema utiliza un **patrón MVC flexible** que permite tanto desarrollo tradicional como moderno:

#### 📄 Páginas Directas (Actual)
```php
<?php
// public/catalogo.php - Página independiente
session_start();
// Lógica de autenticación
// Procesamiento de datos
// HTML directo
?>
<!DOCTYPE html>
<html><!-- Vista integrada --></html>
```

#### 🎮 Controladores MVC (Futuro)
```php
<?php
// app/controllers/CatalogController.php
class CatalogController {
    public function index() {
        $products = Product::getAll();
        $this->view('catalog/index', compact('products'));
    }
}
```

### 🛡️ Patrón de Autenticación

```php
// Verificación estándar en todas las páginas
if (!isset($_SESSION['user_id']) || 
    (!isset($_SESSION['user_tipo']) && !isset($_SESSION['tipo']))) {
    header('Location: login.php');
    exit;
}

// Datos de usuario consistentes
$user = [
    'id' => $_SESSION['user_id'],
    'nombre' => $_SESSION['user_nombre'] ?? $_SESSION['nombre'] ?? 'Usuario',
    'correo' => $_SESSION['user_email'] ?? $_SESSION['correo'] ?? 'email@test.com',
    'tipo' => $_SESSION['user_tipo'] ?? $_SESSION['tipo'] ?? 'cliente'
];
```

### 📊 Patrón de Manejo de Datos

```php
// Datos simulados con estructura real
$productos = [
    [
        'id' => 1,
        'nombre' => 'Tomates Cherry Orgánicos',
        'precio' => 45.50,
        'categoria' => 'Verduras',
        'vendedor' => 'Granja Verde SA',
        'disponible' => true
    ]
    // ... más productos
];

// Filtros y procesamiento
$productosFiltrados = array_filter($productos, function($p) use ($filtros) {
    return stripos($p['nombre'], $filtros['busqueda']) !== false;
});
```

---

## API y Funcionalidades

### 🔌 Endpoints Principales

Aunque el sistema actual no tiene API REST formal, las funcionalidades AJAX siguen patrones consistentes:

#### Autenticación
```javascript
// POST /process-login.php
{
    "email": "user@example.com",
    "password": "password123"
}

// Response
{
    "success": true,
    "redirect": "dashboard.php",
    "user_type": "cliente"
}
```

#### Productos
```javascript
// POST /catalogo.php (AJAX)
{
    "action": "agregar_carrito",
    "id": 1,
    "cantidad": 2
}

// Response
{
    "success": true,
    "message": "Producto agregado al carrito"
}
```

### 📱 Funcionalidades Frontend

#### Sistema de Notificaciones
```javascript
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const toastBody = toast.querySelector('.toast-body');
    toastBody.textContent = message;
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}
```

#### Manejo de Formularios
```javascript
// Patrón estándar para formularios AJAX
async function handleFormSubmit(formId, endpoint) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error de conexión', 'error');
    }
}
```

---

## Base de Datos

### 🗄️ Esquema Principal

```sql
-- Usuarios principales
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    correo VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    tipo ENUM('cliente', 'vendedor', 'admin') NOT NULL,
    estado ENUM('activo', 'pendiente', 'suspendido') DEFAULT 'pendiente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_verificacion TIMESTAMP NULL,
    token_verificacion VARCHAR(255) NULL
);

-- Productos
CREATE TABLE productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vendedor_id INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    stock INT DEFAULT 0,
    imagen_url VARCHAR(500),
    estado ENUM('activo', 'inactivo', 'agotado') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendedor_id) REFERENCES usuarios(id)
);

-- Pedidos
CREATE TABLE pedidos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    vendedor_id INT NOT NULL,
    estado ENUM('pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    total DECIMAL(10,2) NOT NULL,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_entrega TIMESTAMP NULL,
    direccion_entrega TEXT NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
    FOREIGN KEY (vendedor_id) REFERENCES usuarios(id)
);
```

### 🔄 Migraciones

```sql
-- Migration: 2024_01_01_create_usuarios_table.sql
CREATE TABLE usuarios (
    -- estructura inicial
);

-- Migration: 2024_01_15_add_profile_fields.sql
ALTER TABLE usuarios 
ADD COLUMN telefono VARCHAR(20),
ADD COLUMN direccion TEXT;
```

### 📊 Datos de Prueba

```sql
-- Usuarios de prueba
INSERT INTO usuarios (nombre, correo, password_hash, tipo, estado) VALUES
('Admin Sistema', 'admin@agroconecta.com', '$2y$10$hash...', 'admin', 'activo'),
('Juan Vendedor', 'vendedor@test.com', '$2y$10$hash...', 'vendedor', 'activo'),
('María Cliente', 'cliente@test.com', '$2y$10$hash...', 'cliente', 'activo');

-- Productos de prueba
INSERT INTO productos (vendedor_id, nombre, descripcion, precio, categoria, stock) VALUES
(2, 'Tomates Cherry Orgánicos', 'Tomates frescos cultivados orgánicamente', 45.50, 'Verduras', 25),
(2, 'Lechugas Hidropónicas', 'Lechugas frescas cultivadas hidropónicamente', 35.00, 'Verduras', 18);
```

---

## Testing y QA

### 🧪 Estrategia de Testing

#### Testing Manual
- **Funcionalidad:** Flujos completos de usuario
- **Cross-browser:** Chrome, Firefox, Safari, Edge
- **Responsive:** Mobile, tablet, desktop
- **Performance:** Carga de páginas, imágenes

#### Testing Automatizado (Futuro)
```php
// PHPUnit para backend
class UserControllerTest extends PHPUnit\Framework\TestCase {
    public function testUserRegistration() {
        $result = UserController::register($validData);
        $this->assertTrue($result['success']);
    }
}
```

```javascript
// Jest para frontend
describe('Catalog Functions', () => {
    test('should add product to cart', () => {
        const result = addToCart(1, 2);
        expect(result.success).toBe(true);
    });
});
```

### 🔍 QA Checklist

#### Funcionalidades Críticas
- [ ] **Registro de usuarios** - Todos los tipos
- [ ] **Inicio de sesión** - Credenciales válidas/inválidas
- [ ] **Verificación de email** - Links y expiración
- [ ] **Catálogo de productos** - Filtros y búsqueda
- [ ] **Carrito de compras** - Agregar/quitar/modificar
- [ ] **Gestión de pedidos** - Estados y notificaciones
- [ ] **Panel de administración** - Todas las funciones

#### Seguridad
- [ ] **SQL Injection** - Todos los inputs
- [ ] **XSS** - Campos de texto y uploads
- [ ] **CSRF** - Formularios críticos
- [ ] **Authentication bypass** - Rutas protegidas
- [ ] **File uploads** - Tipos y tamaños permitidos

---

## Deployment

### 🚀 Proceso de Deploy

#### Pre-Deploy Checklist
- [ ] **Tests pasando** en todos los niveles
- [ ] **Variables de entorno** configuradas
- [ ] **Base de datos** migrada y con datos
- [ ] **Permisos de archivos** correctos
- [ ] **SSL certificado** instalado

#### Deploy a Producción

```bash
# 1. Backup de producción actual
mysqldump -u user -p agroconecta > backup_$(date +%Y%m%d).sql
tar -czf files_backup_$(date +%Y%m%d).tar.gz /path/to/current/

# 2. Subir nuevos archivos
rsync -avz --exclude='.git' --exclude='logs/*' local/ server:/path/to/app/

# 3. Configurar entorno de producción
cp .env.production .env
chmod 644 .env

# 4. Permisos
chown -R www-data:www-data /path/to/app/
chmod -R 755 /path/to/app/
chmod -R 777 /path/to/app/public/uploads/
chmod -R 777 /path/to/app/logs/

# 5. Cache y optimizaciones
php -r "opcache_reset();"
service apache2 reload
```

#### Configuración de Producción

```env
# .env.production
DB_HOST=production-db-host
DB_DATABASE=agroconecta_prod
DB_USERNAME=secure_user
DB_PASSWORD=very_secure_password

SITE_URL=https://www.agroconecta.com
DEBUG=false
LOG_LEVEL=error
DISPLAY_ERRORS=false

# SSL y seguridad
FORCE_HTTPS=true
SESSION_SECURE=true
CSRF_PROTECTION=true
```

### 📊 Monitoreo de Producción

#### Métricas Clave
- **Uptime** - Disponibilidad del sistema
- **Response Time** - Tiempo de carga páginas
- **Error Rate** - Porcentaje de errores 5xx
- **Database Performance** - Queries lentas
- **Disk Usage** - Espacio uploads y logs

#### Alertas
```bash
# Monitoreo de uptime (crontab)
*/5 * * * * curl -f https://www.agroconecta.com/health-check.php || echo "Site down" | mail -s "AgroConecta Down" admin@example.com

# Limpieza de logs
0 2 * * * find /path/to/logs/ -name "*.log" -mtime +30 -delete
```

---

## 🛠️ Herramientas de Desarrollo

### 🐛 Debug y Logging

```php
// Sistema de logging personalizado
function log_debug($message, $data = null) {
    if (DEBUG) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
            'data' => $data,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
        ];
        
        file_put_contents('logs/debug.log', 
            json_encode($logEntry) . PHP_EOL, 
            FILE_APPEND | LOCK_EX
        );
    }
}
```

### 📦 Build Tools (Futuro)

```json
// package.json
{
    "scripts": {
        "build": "webpack --mode production",
        "dev": "webpack --mode development --watch",
        "test": "jest",
        "lint": "eslint public/js/"
    },
    "devDependencies": {
        "webpack": "^5.0.0",
        "babel-loader": "^8.0.0",
        "sass-loader": "^10.0.0"
    }
}
```

---

## 📚 Recursos para Desarrolladores

### 🔗 Referencias Útiles
- **PHP Documentation:** https://www.php.net/docs.php
- **Bootstrap 5:** https://getbootstrap.com/docs/5.3/
- **MySQL Reference:** https://dev.mysql.com/doc/
- **FontAwesome Icons:** https://fontawesome.com/icons

### 📋 Code Standards
```php
<?php
/**
 * Ejemplo de documentación de funciones
 * 
 * @param array $data Datos del usuario
 * @param string $type Tipo de validación
 * @return array Resultado de validación
 * @throws InvalidArgumentException Si los datos son inválidos
 */
function validate_user_data(array $data, string $type): array {
    // Implementación
}
```

### 🤝 Contribución
1. **Fork** del repositorio
2. **Branch** para nueva feature: `git checkout -b feature/nueva-funcionalidad`
3. **Commit** cambios: `git commit -m 'Add nueva funcionalidad'`
4. **Push** branch: `git push origin feature/nueva-funcionalidad`
5. **Pull Request** con descripción detallada

---

**🚀 ¡Feliz desarrollo con AgroConecta!**