# AgroConecta 🌱

## Sistema de apoyo a agricultores locales

### Descripción
AgroConecta es una plataforma web desarrollada en PHP que conecta directamente agricultores con compradores, eliminando intermediarios y fomentando el comercio directo de productos agrícolas.

### Equipo de Desarrollo - 6CV1
- **Bonilla Landeros Alberto**
- **Flores Sosa Yunis Alberto** 
- **Hernández Juárez Jesús Asaf**
- **Mejía Franco Esteban Saúl**
- **Pérez Rodríguez Alexis Gael**
- **Trejo Jiménez Abiud**

### Tecnologías Utilizadas

#### Backend
- **PHP 8** - Lenguaje principal del servidor
- **MySQL** - Base de datos relacional
- **Apache** - Servidor web
- **PHPMailer** - Envío de correos electrónicos
- **Mercado Pago SDK** - Procesamiento de pagos

#### Frontend
- **HTML5** - Estructura de las páginas
- **CSS3** - Estilos y diseño
- **JavaScript** - Interactividad del cliente
- **Bootstrap 5** - Framework CSS responsivo

#### Arquitectura
- **MVC (Model-View-Controller)** - Patrón de diseño
- **PDO** - Capa de abstracción de base de datos
- **Router personalizado** - Sistema de enrutamiento

### Características Principales

#### Para Clientes 🛒
- Registro y autenticación
- Búsqueda y filtrado de productos
- Carrito de compras
- Checkout con Mercado Pago
- Seguimiento de pedidos
- Gestión de direcciones de entrega

#### Para Vendedores 👨‍🌾
- Panel de vendedor
- Gestión de productos (CRUD)
- Gestión de inventario
- Seguimiento de pedidos
- Actualización de estados de entrega

#### Funcionalidades Generales 🔧
- Sistema de notificaciones por email
- Diseño responsivo (móvil y escritorio)
- Seguridad con tokens CSRF
- Validación de datos
- Manejo de errores
- Logs del sistema

### Requisitos del Sistema

#### Servidor
- **PHP 8.0+**
- **MySQL 5.7+** o **MariaDB 10.2+**
- **Apache 2.4+** con mod_rewrite
- **Extensiones PHP:**
  - PDO
  - PDO_MySQL
  - GD (para manejo de imágenes)
  - cURL (para Mercado Pago)
  - OpenSSL (para envío de emails)
  - mbstring

#### Cliente
- Navegador web moderno (Chrome 70+, Firefox 65+, Safari 12+, Edge 79+)
- JavaScript habilitado

### Instalación

#### 1. Clonar/Descargar el proyecto
```bash
# Si usas Git
git clone [URL_DEL_REPOSITORIO] AgroConecta

# O descomprime el archivo ZIP en tu servidor web
```

#### 2. Configurar el servidor web
- Coloca el proyecto en la carpeta de tu servidor web (htdocs, www, etc.)
- Asegúrate de que Apache tenga mod_rewrite habilitado

#### 3. Crear la base de datos
```sql
CREATE DATABASE agroconecta_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 4. Ejecutar el script de la base de datos
```bash
# Importar el archivo SQL (cuando esté disponible)
mysql -u usuario -p agroconecta_db < database/agroconecta_schema.sql
```

#### 5. Configurar la aplicación
Edita el archivo `config/database.php`:

```php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'agroconecta_db');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');

// Configurar correo electrónico (PHPMailer)
define('MAIL_USERNAME', 'tu_email@gmail.com');
define('MAIL_PASSWORD', 'tu_app_password');

// Configurar Mercado Pago
define('MP_ACCESS_TOKEN', 'tu_access_token');
define('MP_PUBLIC_KEY', 'tu_public_key');
```

#### 6. Configurar permisos
```bash
# Dar permisos de escritura a las carpetas necesarias
chmod 755 public/uploads/
chmod 755 logs/
```

### Estructura del Proyecto

```
AgroConecta/
├── app/
│   ├── controllers/          # Controladores MVC
│   ├── models/              # Modelos de datos
│   ├── views/               # Vistas (HTML/PHP)
│   │   ├── auth/           # Vistas de autenticación
│   │   ├── cliente/        # Vistas del cliente
│   │   ├── vendedor/       # Vistas del vendedor
│   │   └── shared/         # Vistas compartidas
│   └── core/               # Clases principales del sistema
├── config/                 # Archivos de configuración
├── database/              # Scripts de base de datos
├── public/                # Archivos públicos
│   ├── css/              # Hojas de estilo
│   ├── js/               # JavaScript
│   ├── images/           # Imágenes del sitio
│   └── uploads/          # Archivos subidos por usuarios
├── vendor/               # Dependencias (PHPMailer, etc.)
├── logs/                 # Archivos de log
├── .htaccess            # Configuración de Apache
├── index.php           # Punto de entrada
└── README.md          # Esta documentación
```

### Uso

#### Acceso al Sistema
1. **Página principal:** `http://localhost/AgroConecta`
2. **Registro de cliente:** `http://localhost/AgroConecta/registro/cliente`
3. **Registro de vendedor:** `http://localhost/AgroConecta/registro/vendedor`
4. **Iniciar sesión:** `http://localhost/AgroConecta/login`

#### Cuentas de Prueba (cuando estén disponibles)
```
Cliente:
Email: cliente@test.com
Contraseña: cliente123

Vendedor:
Email: vendedor@test.com
Contraseña: vendedor123
```

### API Endpoints

#### Autenticación
- `POST /login` - Iniciar sesión
- `POST /logout` - Cerrar sesión
- `POST /registro/cliente` - Registrar cliente
- `POST /registro/vendedor` - Registrar vendedor

#### Productos
- `GET /productos` - Listar productos
- `GET /producto/{id}` - Detalle de producto
- `POST /vendedor/productos/agregar` - Agregar producto
- `PUT /vendedor/productos/editar/{id}` - Editar producto

#### Carrito y Pagos
- `POST /carrito/agregar` - Agregar al carrito
- `POST /pago/procesar` - Procesar pago
- `POST /pago/webhook` - Webhook de Mercado Pago

### Desarrollo

#### Metodología
- **Espiral** - Desarrollo iterativo con análisis de riesgos

#### Estándares de Codificación
- PSR-4 para autoloading
- PSR-12 para estilo de código
- Comentarios en español
- Nombres de variables y funciones en español/inglés

#### Control de Versiones
```bash
# Estructura de commits recomendada
git commit -m "feat: agregar funcionalidad de carrito"
git commit -m "fix: corregir validación de email"
git commit -m "docs: actualizar README"
```

### Testing

#### Pruebas Manuales
1. Probar registro de usuarios
2. Validar login/logout
3. Verificar CRUD de productos
4. Probar flujo de compra completo
5. Validar notificaciones por email

#### Pruebas de Seguridad
- Validación de entrada de datos
- Protección CSRF
- Sanitización de SQL
- Autenticación y autorización

### Troubleshooting

#### Problemas Comunes

**Error de conexión a la base de datos:**
- Verifica las credenciales en `config/database.php`
- Asegúrate de que MySQL esté ejecutándose
- Verifica que la base de datos exista

**Error 404 en rutas:**
- Verifica que mod_rewrite esté habilitado
- Revisa el archivo `.htaccess`
- Comprueba los permisos de archivos

**Errores de permisos:**
```bash
chmod -R 755 public/uploads/
chown -R www-data:www-data public/uploads/
```

**Problemas con emails:**
- Verifica la configuración SMTP
- Usa contraseñas de aplicación para Gmail
- Revisa los logs en `logs/`

### Contribuciones

#### Cómo Contribuir
1. Fork del repositorio
2. Crear rama de feature: `git checkout -b feature/nueva-funcionalidad`
3. Commit de cambios: `git commit -m 'feat: agregar nueva funcionalidad'`
4. Push a la rama: `git push origin feature/nueva-funcionalidad`
5. Crear Pull Request

### Licencia

Este proyecto es desarrollado como parte del curso de Ingeniería de Software en ESCOM-IPN.

### Contacto

**Institución:** Escuela Superior de Cómputo - Instituto Politécnico Nacional  
**Grupo:** 6CV1  
**Profesor:** Marko Alfonso González Ramírez  
**Fecha:** Diciembre 2024

---

*"Cultivando futuro, conectando cosechas."* 🌱