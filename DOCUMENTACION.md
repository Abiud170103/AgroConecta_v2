# 🌱 AgroConecta - Documentación Completa

## Índice de Documentación

### 📋 Documentación General
- [**Manual de Instalación**](#manual-de-instalación) - Guía paso a paso para instalar el sistema
- [**Manual de Usuario**](#manual-de-usuario) - Guía completa para usar la plataforma
- [**Arquitectura del Sistema**](#arquitectura-del-sistema) - Documentación técnica
- [**API y Funcionalidades**](#api-y-funcionalidades) - Referencia técnica

### 👥 Documentación por Rol
- [**Guía para Clientes**](docs/MANUAL_CLIENTES.md) - Cómo comprar productos
- [**Guía para Vendedores**](docs/MANUAL_VENDEDORES.md) - Cómo vender productos
- [**Guía para Administradores**](docs/MANUAL_ADMINISTRADORES.md) - Gestión del sistema

### 🔧 Documentación Técnica
- [**Guía de Desarrollo**](docs/DESARROLLO.md) - Para desarrolladores
- [**Mantenimiento del Sistema**](docs/MANTENIMIENTO.md) - Administración técnica
- [**FAQ y Troubleshooting**](docs/FAQ.md) - Preguntas frecuentes

---

## Manual de Instalación

### Requisitos del Sistema

**Mínimos:**
- **Servidor Web:** Apache 2.4+ o Nginx 1.18+
- **PHP:** 7.4+ (Recomendado: PHP 8.1+)
- **Base de Datos:** MySQL 5.7+ o MariaDB 10.3+
- **RAM:** 512MB mínimo (2GB recomendado)
- **Espacio en Disco:** 500MB mínimo

**Recomendados:**
- **Sistema Operativo:** Ubuntu 20.04+ / CentOS 8+ / Windows 10+
- **PHP:** 8.1+ con extensiones: `mysqli`, `gd`, `curl`, `zip`, `json`
- **Base de Datos:** MySQL 8.0+ o MariaDB 10.5+
- **RAM:** 4GB+
- **SSD:** Para mejor rendimiento

### Instalación Paso a Paso

#### 1. Preparar el Entorno

**Para Windows con XAMPP:**
```bash
1. Descargar XAMPP desde https://www.apachefriends.org/
2. Instalar XAMPP en C:\xampp\
3. Iniciar Apache y MySQL desde el panel de control
```

**Para Linux (Ubuntu/Debian):**
```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-gd php-curl php-zip
```

#### 2. Descargar AgroConecta

```bash
# Clonar el repositorio
git clone [URL-del-repositorio] /path/to/webserver/AgroConecta_v2

# O descargar y extraer ZIP
cd /path/to/webserver/
unzip AgroConecta_v2.zip
```

#### 3. Configurar Base de Datos

```sql
-- Crear base de datos
CREATE DATABASE agroconecta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario
CREATE USER 'agroconecta_user'@'localhost' IDENTIFIED BY 'password_seguro';
GRANT ALL PRIVILEGES ON agroconecta.* TO 'agroconecta_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 4. Configurar el Sistema

1. **Copiar archivo de configuración:**
   ```bash
   cp .env.example .env
   ```

2. **Editar configuración en .env:**
   ```env
   DB_HOST=localhost
   DB_DATABASE=agroconecta
   DB_USERNAME=agroconecta_user
   DB_PASSWORD=password_seguro
   
   SITE_URL=http://localhost/AgroConecta_v2
   SITE_NAME="AgroConecta"
   
   # Configuración de Email
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=tu-email@gmail.com
   MAIL_PASSWORD=tu-password
   ```

#### 5. Instalar Base de Datos

```bash
# Ejecutar script de instalación
php install_database.php

# O importar manualmente
mysql -u root -p agroconecta < database/schema.sql
mysql -u root -p agroconecta < database/data.sql
```

#### 6. Configurar Permisos

**Linux:**
```bash
sudo chown -R www-data:www-data /path/to/AgroConecta_v2/
sudo chmod -R 755 /path/to/AgroConecta_v2/
sudo chmod -R 777 /path/to/AgroConecta_v2/public/uploads/
sudo chmod -R 777 /path/to/AgroConecta_v2/logs/
```

**Windows:**
```
- Dar permisos de escritura a las carpetas uploads/ y logs/
- Verificar que el usuario del servidor web tenga acceso
```

### Verificación de Instalación

1. **Acceder al sistema:** `http://localhost/AgroConecta_v2/`
2. **Ejecutar diagnóstico:** `http://localhost/AgroConecta_v2/diagnosis.php`
3. **Crear usuarios de prueba:** `http://localhost/AgroConecta_v2/crear-usuarios-prueba.php`

---

## Manual de Usuario

### Primeros Pasos

#### Registro y Verificación
1. **Acceder a la página principal**
2. **Hacer clic en "Registrarse"**
3. **Completar el formulario con:**
   - Nombre completo
   - Correo electrónico
   - Contraseña segura
   - Tipo de cuenta (Cliente/Vendedor)
4. **Verificar email recibido**
5. **Hacer clic en el enlace de verificación**

#### Inicio de Sesión
1. **Ir a "Iniciar Sesión"**
2. **Introducir credenciales**
3. **Acceder al dashboard personalizado**

### Funcionalidades por Tipo de Usuario

#### 👤 Clientes
- **Catálogo de Productos:** Explorar productos disponibles
- **Carrito de Compras:** Agregar y gestionar productos
- **Mis Pedidos:** Seguimiento de compras
- **Lista de Favoritos:** Guardar productos preferidos
- **Perfil Personal:** Gestionar información y preferencias

#### 🌾 Vendedores
- **Gestión de Productos:** Crear, editar y eliminar productos
- **Gestión de Pedidos:** Procesar ventas y entregas
- **Inventario:** Control de stock y disponibilidad
- **Estadísticas de Ventas:** Reportes y análisis
- **Perfil de Negocio:** Información empresarial y certificaciones

#### 👨‍💼 Administradores
- **Gestión de Usuarios:** Aprobar, suspender y gestionar usuarios
- **Reportes del Sistema:** Análisis completo de la plataforma
- **Configuración:** Ajustes globales del sistema
- **Moderación:** Control de contenido y actividad

---

## Arquitectura del Sistema

### Estructura de Directorios

```
AgroConecta_v2/
├── app/                      # Aplicación principal
│   ├── controllers/          # Controladores MVC
│   ├── models/              # Modelos de datos
│   ├── views/               # Vistas y templates
│   └── config/              # Configuraciones de aplicación
├── config/                  # Configuración global
├── core/                    # Núcleo del framework
├── database/                # Scripts y migraciones de BD
├── public/                  # Archivos públicos accesibles
│   ├── css/                 # Hojas de estilo
│   ├── js/                  # JavaScript
│   ├── images/              # Imágenes del sistema
│   ├── uploads/             # Archivos subidos por usuarios
│   └── *.php                # Páginas públicas
├── logs/                    # Archivos de log
└── docs/                    # Documentación adicional
```

### Tecnologías Utilizadas

- **Backend:** PHP 7.4+ con patrón MVC personalizado
- **Frontend:** HTML5, CSS3, JavaScript ES6+
- **UI Framework:** Bootstrap 5.3
- **Iconografía:** Font Awesome 6.4
- **Base de Datos:** MySQL 8.0+ / MariaDB 10.5+
- **Autenticación:** Sistema de sesiones PHP nativo
- **Email:** PHPMailer para notificaciones

### Características de Seguridad

- ✅ **Validación de Entrada:** Sanitización de todos los inputs
- ✅ **Protección CSRF:** Tokens de seguridad en formularios
- ✅ **Encriptación de Contraseñas:** Hashing bcrypt
- ✅ **Verificación de Email:** Proceso de activación de cuenta
- ✅ **Sesiones Seguras:** Configuración robusta de sesiones
- ✅ **Protección de Archivos:** Validación de uploads

---

## Características Principales

### 🎯 Funcionalidades Implementadas

#### Sistema de Usuarios
- ✅ Registro con verificación de email
- ✅ Inicio de sesión seguro
- ✅ Recuperación de contraseña
- ✅ Gestión de perfiles adaptativa (Cliente/Vendedor)
- ✅ Sistema de roles y permisos

#### E-commerce
- ✅ Catálogo de productos con filtros avanzados
- ✅ Carrito de compras persistente
- ✅ Sistema de favoritos
- ✅ Seguimiento de pedidos
- ✅ Gestión de inventario

#### Administración
- ✅ Panel de administración completo
- ✅ Gestión de usuarios y aprobaciones
- ✅ Reportes y analytics con gráficos
- ✅ Sistema de moderación

#### UX/UI
- ✅ Diseño responsive y moderno
- ✅ Interfaz adaptativa por rol
- ✅ Navegación intuitiva
- ✅ Notificaciones en tiempo real
- ✅ Modo oscuro/claro

### 📊 Métricas del Sistema

- **Líneas de Código:** ~15,000+
- **Archivos PHP:** 50+
- **Componentes UI:** 100+
- **Funcionalidades:** 30+
- **Tipos de Usuario:** 3 (Cliente, Vendedor, Admin)

---

## Mantenimiento y Soporte

### Tareas de Mantenimiento Regular

#### Diario
- Revisar logs de error
- Verificar backups automáticos
- Monitorear uso de recursos

#### Semanal
- Actualizar dependencias
- Revisar usuarios registrados
- Analizar estadísticas de uso

#### Mensual
- Backup completo del sistema
- Optimización de base de datos
- Revisión de seguridad

### Resolución de Problemas Comunes

#### Error de Base de Datos
```bash
# Verificar conexión
php test_connection.php

# Reparar tablas
mysql -u root -p -e "REPAIR TABLE tabla_name;"
```

#### Problemas de Permisos
```bash
# Linux
sudo chown -R www-data:www-data /path/to/project/
sudo chmod -R 755 /path/to/project/

# Verificar permisos de escritura
ls -la /path/to/project/uploads/
```

#### Cache y Rendimiento
```bash
# Limpiar cache del sistema
php -r "opcache_reset();"

# Optimizar base de datos
mysql -u root -p -e "OPTIMIZE TABLE tabla_name;"
```

---

## Desarrollo Futuro

### Características Planificadas

#### Próxima Versión (v3.0)
- 🔄 API REST completa
- 🔄 App móvil nativa
- 🔄 Sistema de pagos integrado
- 🔄 Chat en tiempo real
- 🔄 Geolocalización avanzada

#### Mejoras a Largo Plazo
- 🔄 Inteligencia artificial para recomendaciones
- 🔄 Blockchain para trazabilidad
- 🔄 IoT para sensores de campo
- 🔄 Marketplace internacional

---

## Contacto y Soporte

**Desarrollo:** GitHub Copilot  
**Documentación:** Actualizada el 4 de enero de 2026  
**Versión del Sistema:** AgroConecta v2.0  

**Para Soporte Técnico:**
- 📧 Email: soporte@agroconecta.com
- 🐛 Issues: [GitHub Issues](link-to-issues)
- 📖 Wiki: [Documentación Online](link-to-wiki)

---

**© 2026 AgroConecta - Plataforma de Conexión Agrícola**