# 👥 Guía de Colaboración - AgroConecta

## 🚀 Configuración Inicial para el Equipo

### 1. Crear Repositorio Remoto

#### Opción A: GitHub (Recomendado)
1. Ve a [GitHub.com](https://github.com) y crea una cuenta si no tienes
2. Crear nuevo repositorio:
   - **Nombre:** `AgroConecta-6CV1`
   - **Descripción:** `Sistema de apoyo a agricultores locales - ESCOM 6CV1`
   - **Visibilidad:** Privado (para el equipo) o Público
   - **NO** inicializar con README (ya tenemos uno)

#### Opción B: GitLab
Similar proceso en [GitLab.com](https://gitlab.com)

### 2. Conectar Repositorio Local con Remoto

**El líder del equipo (quien tiene este proyecto) debe ejecutar:**

```bash
# Navegar al proyecto
cd "ruta/al/proyecto/AgroConecta"

# Agregar el repositorio remoto (reemplazar URL)
git remote add origin https://github.com/TU_USUARIO/AgroConecta-6CV1.git

# Subir el código inicial
git branch -M main
git push -u origin main
```

### 3. Invitar al Equipo

#### En GitHub:
1. Ve a tu repositorio → **Settings** → **Manage access**
2. Click **Invite a collaborator**
3. Agregar a cada miembro del equipo:
   - Bonilla Landeros Alberto
   - Flores Sosa Yunis Alberto  
   - Hernández Juárez Jesús Asaf
   - Mejía Franco Esteban Saúl
   - Pérez Rodríguez Alexis Gael
   - Trejo Jiménez Abiud

## 📥 Configuración para Miembros del Equipo

### 1. Clonar el Repositorio
```bash
# Clonar en tu computadora
git clone https://github.com/TU_USUARIO/AgroConecta-6CV1.git
cd AgroConecta-6CV1
```

### 2. Configurar Git (Primera vez)
```bash
# Configurar nombre y email
git config --global user.name "Tu Nombre Completo"
git config --global user.email "tu.email@alumno.ipn.mx"
```

### 3. Crear Archivo de Configuración Local
```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Editar .env con tus configuraciones locales
# (Base de datos, credenciales de email, etc.)
```

## 🔄 Flujo de Trabajo del Equipo

### Estrategia: Git Flow Simplificado

#### 1. Ramas Principales
- **`main`** - Código estable y funcional
- **`develop`** - Rama de desarrollo integrada
- **`feature/*`** - Ramas para nuevas características

#### 2. Antes de Empezar a Trabajar
```bash
# Actualizar el repositorio
git checkout main
git pull origin main

# Crear rama para tu característica
git checkout -b feature/nombre-caracteristica
```

#### 3. Durante el Desarrollo
```bash
# Ver estado de archivos
git status

# Agregar archivos modificados
git add .

# Hacer commit descriptivo
git commit -m "feat: descripción de lo que hiciste"

# Subir cambios a tu rama
git push origin feature/nombre-caracteristica
```

#### 4. Integrar Cambios (Pull Request)
1. Ve a GitHub/GitLab
2. Crear **Pull Request** desde tu `feature/*` hacia `main`
3. Asignar a otro compañero para revisión
4. Después de aprobación, hacer merge

## 📋 Asignación de Responsabilidades

### División Sugerida por Módulos:

#### 🔐 **Autenticación** 
- **Responsable:** [Asignar]
- **Archivos:** `AuthController.php`, vistas de login/registro
- **Rama:** `feature/autenticacion`

#### 🏠 **Página Principal**
- **Responsable:** [Asignar] 
- **Archivos:** `HomeController.php`, vistas principales
- **Rama:** `feature/homepage`

#### 👤 **Módulo Cliente**
- **Responsable:** [Asignar]
- **Archivos:** `ClienteController.php`, vistas cliente
- **Rama:** `feature/cliente-dashboard`

#### 👨‍🌾 **Módulo Vendedor**
- **Responsable:** [Asignar]
- **Archivos:** `VendedorController.php`, vistas vendedor  
- **Rama:** `feature/vendedor-dashboard`

#### 🛒 **Carrito y Pagos**
- **Responsable:** [Asignar]
- **Archivos:** `CarritoController.php`, `PagoController.php`
- **Rama:** `feature/carrito-pagos`

#### 🗄️ **Base de Datos**
- **Responsable:** [Asignar]
- **Archivos:** Scripts SQL, modelos
- **Rama:** `feature/database-schema`

## 📝 Convenciones del Equipo

### Mensajes de Commit
```bash
# Tipos de commits
feat: nueva funcionalidad
fix: corrección de bug
docs: documentación
style: formato de código
refactor: refactorización
test: pruebas
chore: tareas de mantenimiento

# Ejemplos
git commit -m "feat: agregar login de usuario"
git commit -m "fix: corregir validación de email"  
git commit -m "docs: actualizar README con API"
```

### Nomenclatura de Archivos
- **Controladores:** `PascalCase` (ej: `ClienteController.php`)
- **Modelos:** `PascalCase` (ej: `Usuario.php`)
- **Vistas:** `snake_case` (ej: `login_form.php`)
- **CSS/JS:** `kebab-case` (ej: `main-style.css`)

### Estructura de Ramas
```
main
├── feature/autenticacion
├── feature/homepage  
├── feature/cliente-dashboard
├── feature/vendedor-dashboard
├── feature/carrito-pagos
└── feature/database-schema
```

## 🚨 Reglas Importantes

### ❌ NO Hacer:
- **NUNCA** hacer `git push --force` en `main`
- **NO** hacer commit de archivos `.env` con datos reales
- **NO** subir la carpeta `vendor/` (usar .gitignore)
- **NO** hacer merge directo a `main` sin Pull Request

### ✅ SÍ Hacer:
- **SIEMPRE** actualizar antes de empezar: `git pull`
- **SIEMPRE** trabajar en ramas `feature/*`
- **SIEMPRE** hacer commits pequeños y descriptivos  
- **SIEMPRE** revisar el código antes del merge

## 🔧 Comandos Útiles

```bash
# Ver ramas
git branch -a

# Cambiar de rama
git checkout nombre-rama

# Ver diferencias
git diff

# Ver historial
git log --oneline

# Deshacer cambios locales
git checkout -- archivo.php

# Actualizar rama actual
git pull origin main

# Ver status detallado
git status
```

## 📞 Comunicación del Equipo

### Canales Recomendados:
- **WhatsApp/Telegram:** Coordinación diaria
- **GitHub Issues:** Reportar bugs y tareas
- **Pull Request Comments:** Revisiones de código
- **Reuniones:** Sincronización semanal

### Horarios de Trabajo:
- **Lunes a Viernes:** [Definir horarios]
- **Reunión semanal:** [Definir día y hora]
- **Deadline sprints:** [Definir fechas]

## 🆘 Resolución de Conflictos

### Si hay conflictos al hacer merge:
```bash
# Actualizar main
git checkout main
git pull origin main

# Volver a tu rama
git checkout feature/tu-rama

# Hacer rebase
git rebase main

# Resolver conflictos manualmente en los archivos
# Luego continuar
git add .
git rebase --continue

# Subir cambios
git push origin feature/tu-rama --force-with-lease
```

---

## 📋 Checklist de Configuración

### Para el Líder del Equipo:
- [ ] Crear repositorio en GitHub/GitLab
- [ ] Ejecutar comandos de conexión remota
- [ ] Invitar a todos los miembros
- [ ] Crear Issues para las tareas principales
- [ ] Configurar protección de rama `main`

### Para cada Miembro:
- [ ] Aceptar invitación al repositorio
- [ ] Clonar el proyecto localmente
- [ ] Configurar Git con nombre y email
- [ ] Crear archivo `.env` personal
- [ ] Probar que el proyecto funciona localmente
- [ ] Elegir módulo de responsabilidad
- [ ] Crear primera rama `feature/*`

---

**¡Importante!** Antes de empezar a codificar, asegúrense de que todos tengan:
1. ✅ XAMPP/WAMP instalado y funcionando
2. ✅ PHP 8+ configurado  
3. ✅ MySQL creado con la base `agroconecta_db`
4. ✅ Git configurado correctamente
5. ✅ Acceso al repositorio remoto

**¡A desarrollar AgroConecta! 🌱**