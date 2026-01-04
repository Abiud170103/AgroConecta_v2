# 🚀 Guía de Instalación Rápida - AgroConecta

## ⚡ Instalación en 5 Pasos

### Paso 1: Requisitos del Sistema
- **PHP:** 7.4+ (Recomendado: 8.1+)
- **MySQL:** 8.0+ o MariaDB 10.5+
- **Servidor Web:** Apache 2.4+ o Nginx
- **Extensiones PHP:** `mysqli`, `gd`, `curl`, `zip`, `json`

### Paso 2: Descargar y Descomprimir
```bash
# Descargar proyecto
git clone [repo-url] AgroConecta_v2
# O descomprimir ZIP en carpeta del servidor web
```

### Paso 3: Configurar Base de Datos
```sql
-- Crear base de datos
CREATE DATABASE agroconecta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario
CREATE USER 'agroconecta_user'@'localhost' IDENTIFIED BY 'tu_password_seguro';
GRANT ALL PRIVILEGES ON agroconecta.* TO 'agroconecta_user'@'localhost';
FLUSH PRIVILEGES;
```

### Paso 4: Configurar Aplicación
```bash
# Copiar archivo de configuración
cp .env.example .env

# Editar .env con tus datos:
# DB_HOST=localhost
# DB_DATABASE=agroconecta  
# DB_USERNAME=agroconecta_user
# DB_PASSWORD=tu_password_seguro
# SITE_URL=http://localhost/AgroConecta_v2
```

### Paso 5: Configurar Permisos
```bash
# Linux/Mac
chmod -R 755 AgroConecta_v2/
chmod -R 777 AgroConecta_v2/public/uploads/
chmod -R 777 AgroConecta_v2/logs/

# Windows: Dar permisos de escritura a carpetas uploads/ y logs/
```

## ✅ Verificar Instalación

1. **Acceder:** `http://localhost/AgroConecta_v2/`
2. **Crear cuenta de prueba** o usar:
   - Email: `admin@test.com` / Password: `admin123`
3. **Ejecutar diagnóstico:** `http://localhost/AgroConecta_v2/diagnosis.php`

## 📞 Soporte
- **Documentación completa:** [DOCUMENTACION.md](DOCUMENTACION.md)
- **Email:** soporte@agroconecta.com

---
**🌱 ¡Listo para usar AgroConecta!**