# ❓ FAQ y Troubleshooting - AgroConecta

## Índice
1. [Preguntas Frecuentes - Usuarios](#preguntas-frecuentes---usuarios)
2. [Preguntas Frecuentes - Técnicas](#preguntas-frecuentes---técnicas)
3. [Troubleshooting](#troubleshooting)
4. [Errores Comunes](#errores-comunes)
5. [Optimización y Performance](#optimización-y-performance)
6. [Contacto y Soporte](#contacto-y-soporte)

---

## Preguntas Frecuentes - Usuarios

### 👤 Cuenta y Registro

**❓ ¿Por qué no recibo el email de verificación?**
- Revisa tu carpeta de spam/correo no deseado
- Verifica que escribiste correctamente tu email
- Algunos proveedores (Hotmail, Yahoo) pueden tardar hasta 30 minutos
- Si el problema persiste, contacta soporte

**❓ ¿Puedo cambiar mi tipo de cuenta después del registro?**
- No es posible cambiar de cliente a vendedor automáticamente
- Los vendedores pasan por un proceso de verificación
- Contacta soporte para cambios de tipo de cuenta

**❓ ¿Qué hago si olvido mi contraseña?**
1. Ve a la página de inicio de sesión
2. Haz clic en "¿Olvidaste tu contraseña?"
3. Introduce tu email registrado
4. Revisa tu correo para el enlace de recuperación
5. Sigue las instrucciones para crear nueva contraseña

### 🛍️ Compras y Productos

**❓ ¿Cómo sé si un producto es realmente orgánico?**
- Los productos orgánicos muestran una etiqueta verde "Orgánico"
- Revisa el perfil del vendedor para ver certificaciones
- Los vendedores deben subir documentos de certificación
- En caso de duda, contacta directamente al vendedor

**❓ ¿Puedo cancelar un pedido después de realizarlo?**
- Sí, puedes cancelar pedidos en estado "Pendiente"
- Una vez que el vendedor confirma, debes contactarlo directamente
- Los pedidos "En Camino" generalmente no se pueden cancelar
- Revisa la política de cancelación de cada vendedor

**❓ ¿Cómo funciona el sistema de calificaciones?**
- Solo puedes calificar después de recibir tu pedido
- Las calificaciones van de 1 a 5 estrellas
- Puedes dejar comentarios detallados
- Las calificaciones son públicas y permanentes

### 🚚 Envío y Entrega

**❓ ¿Los precios incluyen el costo de envío?**
- No, el envío se calcula según la distancia
- Se muestra el costo antes de confirmar la compra
- Algunos vendedores ofrecen envío gratuito en compras grandes
- Los métodos de entrega varían por vendedor

**❓ ¿Cuánto tiempo tardan las entregas?**
- **Envío estándar:** 3-5 días laborables
- **Envío express:** 1-2 días laborables
- **Recolección:** Según disponibilidad del vendedor
- Los tiempos pueden variar por ubicación y producto

---

## Preguntas Frecuentes - Técnicas

### 💻 Compatibilidad y Acceso

**❓ ¿Qué navegadores son compatibles?**
- **Recomendados:** Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Funcionales:** Versiones anteriores con funcionalidad limitada
- **No soportados:** Internet Explorer
- **Móvil:** iOS Safari 14+, Chrome Mobile 90+

**❓ ¿Por qué la página se ve mal en mi teléfono?**
- Asegúrate de usar un navegador actualizado
- Limpia la cache del navegador
- Verifica tu conexión a internet
- Algunos navegadores antiguos no soportan todas las características

### 🔐 Seguridad y Privacidad

**❓ ¿Es seguro hacer compras en AgroConecta?**
- Sí, utilizamos encriptación SSL para proteger tus datos
- Las contraseñas se almacenan con encriptación avanzada
- No compartimos información personal con terceros
- Los pagos se procesan a través de gateways seguros

**❓ ¿Qué datos personales recolectan?**
- **Registro:** Nombre, email, tipo de cuenta
- **Perfil:** Dirección, teléfono, preferencias
- **Compras:** Historial de pedidos, productos favoritos
- **No recolectamos:** Información financiera directamente

### 📱 Funcionalidades

**❓ ¿Hay una app móvil de AgroConecta?**
- Actualmente solo existe la versión web responsive
- La web se adapta perfectamente a móviles
- Una app nativa está en desarrollo futuro
- Puedes agregar un acceso directo a tu pantalla de inicio

**❓ ¿Por qué no funciona el chat con vendedores?**
- La función de chat está en desarrollo
- Actualmente puedes contactar vendedores por email
- Se notificará cuando el chat esté disponible
- Mientras tanto, usa los formularios de contacto

---

## Troubleshooting

### 🚨 Problemas de Inicio de Sesión

#### "Credenciales incorrectas"
```
Diagnóstico:
1. ✅ Verifica que tu email esté bien escrito
2. ✅ Asegúrate de que tu cuenta esté verificada
3. ✅ Intenta recuperar contraseña si no recuerdas
4. ✅ Contacta soporte si el problema persiste
```

#### "Sesión expirada"
```
Causa: La sesión ha estado inactiva por mucho tiempo
Solución:
1. Cierra todas las pestañas de AgroConecta
2. Limpia cookies del navegador
3. Inicia sesión nuevamente
4. Si persiste, reinicia el navegador
```

#### Página de login en bucle infinito
```
Diagnóstico paso a paso:
1. Limpia cache y cookies completamente
2. Desactiva extensiones del navegador temporalmente
3. Prueba en modo incógnito/privado
4. Intenta con otro navegador
5. Verifica tu conexión a internet
```

### 🛒 Problemas con el Carrito

#### No se agregan productos al carrito
```javascript
// Revisa la consola del navegador (F12)
// Busca errores de JavaScript
// Mensaje típico: "Network error" o "Failed to fetch"

Soluciones:
1. Refresca la página
2. Limpia cache del navegador
3. Verifica tu conexión a internet
4. Intenta con otro navegador
```

#### Carrito se vacía al cerrar sesión
```
Comportamiento normal:
- El carrito se guarda en la sesión
- Al cerrar sesión se limpia automáticamente
- Los favoritos sí se mantienen permanentemente
```

### 📧 Problemas de Email

#### No llegan emails de verificación
```
Checklist de diagnóstico:
1. ✅ Revisar carpeta de spam
2. ✅ Verificar email escrito correctamente
3. ✅ Esperar hasta 30 minutos
4. ✅ Intentar reenviar verificación
5. ✅ Contactar soporte con screenshot
```

#### Links de recuperación no funcionan
```
Posibles causas:
- Link expirado (válido solo 1 hora)
- Link ya usado anteriormente
- Problema con formato del email

Solución:
1. Solicitar nuevo link de recuperación
2. Copiar y pegar URL manualmente
3. Contactar soporte técnico
```

---

## Errores Comunes

### ⚠️ Errores del Sistema

#### Error 500 - Internal Server Error
```
Para usuarios:
- Refresca la página después de unos minutos
- Si persiste, reporta el error con detalles

Para desarrolladores:
- Revisar logs/error.log
- Verificar permisos de archivos
- Comprobar configuración de BD
- Validar sintaxis PHP
```

#### Error 404 - Página no encontrada
```
Causas comunes:
- URL escrita incorrectamente
- Página movida o eliminada
- Problema con .htaccess
- Error en enlaces internos

Solución:
- Verificar URL manualmente
- Usar navegación del sitio
- Reportar enlaces rotos
```

#### Error de Base de Datos
```
Mensaje típico: "Database connection failed"

Para administradores:
1. Verificar credenciales en .env
2. Comprobar que MySQL esté activo
3. Verificar permisos de usuario BD
4. Revisar logs de MySQL
```

### 🐛 Errores de JavaScript

#### "TypeError: Cannot read property"
```javascript
// Error común en funciones JavaScript
// Usualmente relacionado con elementos DOM

Debugging:
1. Abrir consola del navegador (F12)
2. Buscar línea específica del error
3. Verificar que elementos existan en HTML
4. Comprobar timing de ejecución de scripts
```

#### Formularios no se envían
```javascript
// Revisar en consola:
// - Errores de validación
// - Problemas de red
// - Respuestas del servidor

Soluciones rápidas:
1. Recargar página
2. Llenar todos los campos obligatorios
3. Verificar conexión a internet
4. Intentar envío manual
```

---

## Optimización y Performance

### 🚀 Mejoras de Velocidad

#### Página carga lentamente
```
Para usuarios:
1. ✅ Verificar velocidad de internet
2. ✅ Cerrar otras pestañas/aplicaciones
3. ✅ Limpiar cache del navegador
4. ✅ Usar conexión estable (WiFi vs móvil)

Para administradores:
1. ✅ Optimizar imágenes (compresión)
2. ✅ Habilitar cache del servidor
3. ✅ Minificar CSS/JavaScript
4. ✅ Usar CDN para recursos estáticos
```

#### Imágenes tardan en cargar
```
Optimizaciones:
- Redimensionar imágenes antes de subir
- Usar formatos eficientes (WebP, JPEG optimizado)
- Implementar lazy loading
- Configurar cache de navegador
```

### 💾 Gestión de Memoria

#### "Out of memory" errors
```php
// Para desarrolladores
ini_set('memory_limit', '256M');
// Optimizar queries de BD
// Evitar cargar datasets grandes completos
// Implementar paginación en todas las listas
```

### 📊 Monitoreo

#### Métricas importantes
```
- Tiempo de carga < 3 segundos
- Tasa de error < 1%
- Uptime > 99.5%
- Velocidad de BD < 100ms promedio
```

---

## Contacto y Soporte

### 📞 Canales de Soporte

#### Para Usuarios Finales
- **📧 Email:** soporte@agroconecta.com
- **📱 WhatsApp:** +52 55 1234 5678
- **🕐 Horario:** Lunes a Viernes 9:00 AM - 6:00 PM CST
- **⏱️ Tiempo de respuesta:** 24 horas máximo

#### Para Desarrolladores/Administradores
- **📧 Email técnico:** tech@agroconecta.com
- **🐛 Issues:** GitHub Issues (si aplica)
- **📱 Emergencias:** +52 55 9999 0000 (24/7)
- **💬 Slack:** Canal #agroconecta-dev

### 📋 Información Necesaria para Soporte

#### Para Reportes de Bugs
```
Incluir siempre:
✅ URL donde ocurre el problema
✅ Navegador y versión
✅ Sistema operativo
✅ Pasos para reproducir el error
✅ Screenshot del error
✅ Mensaje de error exacto
✅ Hora aproximada del incidente
```

#### Para Problemas de Cuenta
```
Proporcionar:
✅ Email registrado
✅ Tipo de cuenta (Cliente/Vendedor)
✅ Descripción detallada del problema
✅ Screenshots relevantes
✅ Acciones intentadas previamente
```

### 🎯 Tipos de Soporte

#### Soporte Básico (Gratuito)
- Problemas generales de uso
- Preguntas sobre funcionalidades
- Guías de uso básico
- Reportes de bugs

#### Soporte Premium (Vendedores)
- Asistencia prioritaria
- Configuración de tienda
- Optimización de productos
- Análisis de ventas

#### Soporte Técnico (Administradores)
- Configuración de servidor
- Optimización de BD
- Implementación de nuevas funcionalidades
- Mantenimiento preventivo

---

## 🔧 Herramientas de Diagnóstico

### 🌐 Para Usuarios

#### Diagnóstico de Conexión
```
1. Visita: https://www.speedtest.net/
2. Ejecuta test de velocidad
3. Resultados mínimos recomendados:
   - Download: 5 Mbps
   - Upload: 1 Mbps
   - Latencia: < 100ms
```

#### Diagnóstico de Navegador
```
1. Abre DevTools (F12)
2. Ve a la pestaña "Network"
3. Recarga la página
4. Busca requests fallidos (rojos)
5. Toma screenshot para soporte
```

### ⚙️ Para Administradores

#### Health Check
```php
// Crear archivo: public/health-check.php
<?php
$checks = [
    'database' => check_database_connection(),
    'uploads' => is_writable('uploads/'),
    'logs' => is_writable('logs/'),
    'memory' => memory_get_usage() < (50 * 1024 * 1024)
];

http_response_code(all($checks) ? 200 : 503);
echo json_encode($checks);
?>
```

#### System Info
```php
// Información del sistema para debugging
phpinfo();
// O crear script personalizado con info relevante
```

---

## 📚 Recursos Adicionales

### 🎓 Tutoriales
- **Video: Registro y primer pedido** - 5 minutos
- **Guía: Configurar perfil de vendedor** - 10 pasos
- **Tutorial: Optimizar fotos de productos** - Técnicas básicas

### 📖 Documentación
- [Manual Completo de Usuario](MANUAL_CLIENTES.md)
- [Guía de Vendedores](MANUAL_VENDEDORES.md)
- [Panel de Administración](MANUAL_ADMINISTRADORES.md)
- [Documentación Técnica](DESARROLLO.md)

### 🤝 Comunidad
- **Foro de usuarios:** comunidad.agroconecta.com
- **Facebook:** AgroConecta Oficial
- **YouTube:** Tutoriales y novedades
- **Newsletter:** Suscríbete para actualizaciones

---

**💡 ¿No encuentras respuesta a tu pregunta? ¡Contacta nuestro equipo de soporte!**

**Última actualización:** 4 de enero de 2026  
**Versión del sistema:** AgroConecta v2.0