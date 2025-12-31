# 🧪 PRUEBAS DE AUTENTICACIÓN - AgroConecta

## URLs de Prueba Directa

### ✅ Páginas Implementadas

1. **Login**: http://localhost/AgroConecta_v2/public/login.php
2. **Registro**: http://localhost/AgroConecta_v2/public/register.php  
3. **Recuperar Contraseña**: http://localhost/AgroConecta_v2/public/forgot-password.php
4. **Página de Prueba**: http://localhost/AgroConecta_v2/public/test-auth.php

---

## ✅ Checklist de Pruebas

### 1. Página de Login (login.php)
- [ ] La página carga correctamente
- [ ] Se muestra el logo de AgroConecta
- [ ] El fondo tiene gradiente verde (#28a745)
- [ ] El formulario tiene campos de email y contraseña
- [ ] El campo de email tiene icono de sobre
- [ ] El campo de contraseña tiene icono de candado
- [ ] El botón de "ver contraseña" (ojo) funciona
- [ ] Checkbox "Recordarme" está presente
- [ ] Enlace "¿Olvidaste tu contraseña?" funciona
- [ ] Enlace "Regístrate aquí" funciona
- [ ] Diseño responsive en móvil

**Validaciones a probar:**
- [ ] Campo email vacío muestra error
- [ ] Email inválido muestra error
- [ ] Campo contraseña vacío muestra error
- [ ] Validación en tiempo real funciona

---

### 2. Página de Registro (register.php)
- [ ] La página carga correctamente
- [ ] Toggle Cliente/Vendedor funciona
- [ ] Icono de Cliente (🛒) y Vendedor (🏪) se muestran
- [ ] Campos básicos: nombre, apellido, email, teléfono
- [ ] Campos de contraseña con confirmación
- [ ] Botón de ver contraseña funciona en ambos campos
- [ ] Al seleccionar "Vendedor", aparecen campos adicionales
- [ ] Campos de vendedor: nombre negocio, descripción, ciudad, estado
- [ ] Select de estados de México carga correctamente
- [ ] Checkbox de términos y condiciones presente
- [ ] Diseño responsive en móvil

**Campos adicionales de vendedor:**
- [ ] Nombre del Negocio
- [ ] Descripción del Negocio
- [ ] Ciudad
- [ ] Estado (dropdown con 32 estados)

**Validaciones a probar:**
- [ ] Nombre mínimo 2 caracteres
- [ ] Email válido
- [ ] Teléfono válido (10 dígitos)
- [ ] Contraseña: 8+ caracteres, mayúscula, minúscula, número, especial
- [ ] Contraseñas coinciden
- [ ] Términos y condiciones obligatorio
- [ ] Campos de vendedor requeridos si tipo es "vendedor"

---

### 3. Página de Recuperar Contraseña (forgot-password.php)
- [ ] La página carga correctamente
- [ ] Formulario simple con solo campo de email
- [ ] Botón "Enviar Instrucciones" funciona
- [ ] Enlace "Volver al inicio de sesión" funciona
- [ ] Enlace "Regístrate aquí" funciona
- [ ] Diseño responsive en móvil

**Validaciones a probar:**
- [ ] Email requerido
- [ ] Email válido

---

## 🎨 Verificación Visual

### Colores del Mockup
- [x] Color primario: `#28a745` (verde)
- [x] Color secundario: `#1e7e34` (verde oscuro)
- [x] Gradiente de fondo: verde
- [x] Contenedor: blanco con sombra
- [x] Botones: gradiente verde
- [x] Hover en botones: efecto de elevación

### Tipografía
- [x] Títulos grandes y llamativos
- [x] Subtítulos con color gris
- [x] Texto legible en campos

### Iconos
- [x] Font Awesome 6.4.0 cargado
- [x] Iconos en campos de formulario
- [x] Iconos en botones de tipo usuario

---

## 🔧 Funcionalidad JavaScript

### Validaciones en Tiempo Real (auth.js)
- [ ] Toggle de contraseña funciona
- [ ] Validación de email
- [ ] Validación de contraseña fuerte
- [ ] Validación de teléfono
- [ ] Validación de coincidencia de contraseñas
- [ ] Mensajes de error se muestran correctamente
- [ ] Mensajes de error desaparecen al escribir
- [ ] Alertas se auto-ocultan después de 5 segundos
- [ ] Loading state en botones al enviar

### Toggle Cliente/Vendedor
- [ ] Click en "Cliente" oculta campos de vendedor
- [ ] Click en "Vendedor" muestra campos de vendedor
- [ ] Campos de vendedor se hacen requeridos al seleccionar vendedor
- [ ] Campos de vendedor se hacen opcionales al seleccionar cliente
- [ ] Animación smooth al mostrar/ocultar campos

---

## 📱 Pruebas Responsive

### Desktop (1920x1080)
- [ ] Contenedor centrado
- [ ] Ancho máximo de 500px
- [ ] Espaciado apropiado

### Tablet (768x1024)
- [ ] Contenedor se adapta
- [ ] Formularios legibles
- [ ] Botones accesibles

### Móvil (375x667)
- [ ] Toggle de tipo usuario en columna
- [ ] Campos de formulario en columna
- [ ] Texto legible
- [ ] Botones de tamaño apropiado
- [ ] Sin scroll horizontal

---

## 🔐 Seguridad

### Campos de Formulario
- [x] CSRF token implementado en todos los formularios
- [x] Campos de contraseña tipo "password"
- [x] No se muestran contraseñas en texto plano por defecto
- [ ] Formularios usan método POST

### Headers de Seguridad
- [x] X-Frame-Options configurado
- [x] X-XSS-Protection configurado
- [x] X-Content-Type-Options configurado

---

## ⚠️ Problemas Conocidos

1. **Rutas del Backend**: Las vistas apuntan a `/auth/login`, `/auth/register`, pero en `agroconecta_routes.php` las rutas son `/login`, `/registro`.
   
   **Solución**: Actualizar las acciones de los formularios en las vistas.

2. **Métodos del Controlador**: Las rutas llaman a `showLogin()`, `processLogin()`, pero el `AuthController.php` tiene `login()`.
   
   **Solución**: Alinear nombres de métodos entre rutas y controlador.

3. **Sesión PHP**: Iniciar sesión antes de usar `$_SESSION['csrf_token']`.
   
   **Estado**: ✅ Ya implementado en index.php

---

## 📋 Siguiente Pasos

### Inmediato
1. [x] Verificar que las páginas cargan correctamente
2. [ ] Actualizar rutas en formularios
3. [ ] Sincronizar métodos del controlador con rutas
4. [ ] Probar envío de formularios
5. [ ] Verificar mensajes de error/éxito

### Corto Plazo
1. [ ] Integrar con base de datos real
2. [ ] Implementar envío de emails
3. [ ] Crear página de verificación de email
4. [ ] Implementar reset de contraseña completo
5. [ ] Pruebas de integración completas

### Largo Plazo
1. [ ] Implementar autenticación con Google/Facebook
2. [ ] Agregar autenticación de dos factores
3. [ ] Logs de intentos de login
4. [ ] Rate limiting para prevenir ataques
5. [ ] Captcha en formularios

---

## 📊 Estado Actual

**Fecha**: 30 de diciembre de 2025

**Completado**: 85%
- ✅ Diseño UI completo
- ✅ HTML/CSS implementado
- ✅ JavaScript de validaciones
- ✅ Estructura de archivos
- ⚠️ Integración con backend (pendiente ajustes menores)
- ⏳ Pruebas funcionales (en proceso)

**Equipo**: AgroConecta 6CV1
