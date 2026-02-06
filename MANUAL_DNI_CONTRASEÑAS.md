# 📋 SISTEMA DE DNI Y CONTRASEÑAS AUTOMÁTICAS

## 📝 Descripción General

Sistema implementado para gestionar usuarios mediante DNI (8 dígitos) con generación automática de contraseñas y cambio obligatorio en el primer inicio de sesión.

## 🎯 Características Principales

### ✅ Creación de Usuarios
- **DNI obligatorio**: 8 dígitos únicos
- **Generación automática de contraseña**: DNI + dos primeras letras del nombre en MAYÚSCULA
- **Contraseña temporal**: El administrador recibe la contraseña para comunicarla al usuario
- **Cambio obligatorio**: Flag `debe_cambiar_password = true` por defecto

### 🔐 Seguridad
- Contraseñas estandarizadas en creación
- Cambio obligatorio en primer login
- Validación de contraseña nueva (mínimo 8 caracteres, mayúscula, minúscula, número)
- Verificación de contraseña actual antes de cambiar

---

## 🗄️ Base de Datos

### Campos Agregados a `usuarios`

```sql
dni VARCHAR(8) UNIQUE NULL
debe_cambiar_password BOOLEAN DEFAULT TRUE
```

**Migración**: `2026_01_27_201548_add_dni_and_debe_cambiar_password_to_users_table.php`

---

## 🔧 API Endpoints

### 1. 👤 Crear Usuario (Solo Admin)

**Endpoint**: `POST /api/usuarios`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
    "nombre": "María García López",
    "email": "maria@utp.edu.pe",
    "dni": "12345678",
    "rol_id": 2,
    "estado": true
}
```

**Response Exitoso (201)**:
```json
{
    "message": "Usuario creado exitosamente",
    "data": {
        "id": 5,
        "nombre": "María García López",
        "email": "maria@utp.edu.pe",
        "dni": "12345678",
        "rol_id": 2,
        "debe_cambiar_password": true,
        "estado": true,
        "created_at": "2026-01-27T15:30:00.000000Z"
    },
    "password_temporal": "12345678MA"
}
```

**Validaciones**:
- `nombre`: requerido, string, max 255
- `email`: requerido, email, único
- `dni`: requerido, string, 8 caracteres exactos, único, solo dígitos
- `rol_id`: requerido, existe en tabla roles
- `estado`: opcional, boolean

**Fórmula de Contraseña**:
```
DNI + Primera letra del nombre + Segunda letra del nombre (MAYÚSCULAS)

Ejemplos:
- DNI: 12345678, Nombre: "María García" → Password: "12345678MA"
- DNI: 87654321, Nombre: "Juan Pérez" → Password: "87654321JU"
- DNI: 11223344, Nombre: "Ana Torres" → Password: "11223344AN"
```

---

### 2. 🔓 Login con Detección de Cambio Obligatorio

**Endpoint**: `POST /api/login`

**Request Body**:
```json
{
    "email": "maria@utp.edu.pe",
    "password": "12345678MA"
}
```

**Response - PRIMER LOGIN (debe cambiar contraseña)**:
```json
{
    "message": "Debe cambiar su contraseña",
    "debe_cambiar_password": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "user": {
        "id": 5,
        "nombre": "María García López",
        "email": "maria@utp.edu.pe"
    }
}
```

**Response - LOGIN NORMAL (contraseña ya cambiada)**:
```json
{
    "message": "Login exitoso",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": {
        "id": 5,
        "nombre": "María García López",
        "email": "maria@utp.edu.pe",
        "rol": {
            "id": 2,
            "nombre": "Asistente"
        },
        "permisos": [...]
    }
}
```

---

### 3. 🔑 Cambiar Contraseña

**Endpoint**: `POST /api/auth/cambiar-password`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
    "password_actual": "12345678MA",
    "password_nueva": "MiNuevaPassword123!",
    "password_confirmacion": "MiNuevaPassword123!"
}
```

**Response Exitoso (200)**:
```json
{
    "message": "Contraseña cambiada exitosamente. Por favor, inicie sesión nuevamente."
}
```

**Errores Posibles**:

❌ **Contraseña actual incorrecta (422)**:
```json
{
    "message": "La contraseña actual es incorrecta",
    "errors": {
        "password_actual": ["La contraseña actual es incorrecta"]
    }
}
```

❌ **Contraseña nueva no cumple requisitos (422)**:
```json
{
    "message": "The password nueva field format is invalid.",
    "errors": {
        "password_nueva": [
            "La contraseña debe contener al menos una letra mayúscula, una minúscula y un número"
        ]
    }
}
```

❌ **Confirmación no coincide (422)**:
```json
{
    "message": "The password confirmacion field must match password nueva.",
    "errors": {
        "password_confirmacion": [
            "The password confirmacion field must match password nueva."
        ]
    }
}
```

**Validaciones de Contraseña Nueva**:
- Mínimo 8 caracteres
- Máximo 50 caracteres
- Al menos una letra MAYÚSCULA
- Al menos una letra minúscula
- Al menos un número
- Regex: `/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/`

---

## 🌊 Flujo Completo

### Flujo de Usuario Nuevo

```
1. ADMIN CREA USUARIO
   ↓
   POST /api/usuarios
   {
       "nombre": "Juan Pérez",
       "email": "juan@utp.edu.pe",
       "dni": "87654321",
       "rol_id": 2
   }
   ↓
   Response: { password_temporal: "87654321JU" }
   ↓
   Admin comunica credenciales al usuario

2. USUARIO INICIA SESIÓN POR PRIMERA VEZ
   ↓
   POST /api/login
   {
       "email": "juan@utp.edu.pe",
       "password": "87654321JU"
   }
   ↓
   Response: { debe_cambiar_password: true, token: "..." }
   ↓
   Frontend detecta flag y redirige a pantalla de cambio

3. USUARIO CAMBIA CONTRASEÑA
   ↓
   POST /api/auth/cambiar-password
   {
       "password_actual": "87654321JU",
       "password_nueva": "MiPassword2026!",
       "password_confirmacion": "MiPassword2026!"
   }
   ↓
   Response: { message: "Contraseña cambiada..." }
   ↓
   debe_cambiar_password = FALSE

4. USUARIO SE VUELVE A LOGGEAR
   ↓
   POST /api/login
   {
       "email": "juan@utp.edu.pe",
       "password": "MiPassword2026!"
   }
   ↓
   Response: { message: "Login exitoso", user: {...}, permisos: [...] }
   ↓
   Acceso completo al sistema
```

---

## 🖥️ Implementación Frontend

### 1. Formulario de Creación de Usuario

```html
<!-- Agregar campo DNI -->
<div class="form-group">
    <label for="dni">DNI *</label>
    <input 
        type="text" 
        id="dni" 
        name="dni" 
        maxlength="8" 
        pattern="[0-9]{8}"
        required
        placeholder="12345678"
    >
    <small>8 dígitos numéricos</small>
</div>

<!-- NOTA: Eliminar campo de contraseña manual -->
<!-- La contraseña se genera automáticamente -->
```

**Manejo de Response**:
```javascript
async function crearUsuario(data) {
    try {
        const response = await fetch('/api/usuarios', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // Mostrar contraseña temporal al admin
            alert(`Usuario creado. Contraseña temporal: ${result.password_temporal}`);
            
            // O mejor, mostrar en modal para copiar
            mostrarModalContraseña(result.password_temporal);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function mostrarModalContraseña(password) {
    // Modal con botón de copiar
    const modal = document.getElementById('modalPasswordTemporal');
    document.getElementById('passwordTemporal').textContent = password;
    modal.style.display = 'block';
}
```

---

### 2. Pantalla de Login con Detección

```javascript
async function login(email, password) {
    try {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Guardar token
            localStorage.setItem('token', data.token);
            
            // DETECTAR SI DEBE CAMBIAR CONTRASEÑA
            if (data.debe_cambiar_password === true) {
                // Redirigir a pantalla de cambio de contraseña
                window.location.href = '/cambiar-password.html';
            } else {
                // Login normal, ir al dashboard
                window.location.href = '/dashboard.html';
            }
        } else {
            mostrarError(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
```

---

### 3. Pantalla de Cambio de Contraseña

**HTML**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>Cambiar Contraseña - Sistema UTP</title>
</head>
<body>
    <div class="container">
        <h1>Cambio de Contraseña Obligatorio</h1>
        <p>Por seguridad, debe cambiar su contraseña temporal.</p>
        
        <form id="formCambiarPassword">
            <div class="form-group">
                <label>Contraseña Actual *</label>
                <input 
                    type="password" 
                    id="password_actual" 
                    required
                    placeholder="Contraseña temporal recibida"
                >
            </div>
            
            <div class="form-group">
                <label>Nueva Contraseña *</label>
                <input 
                    type="password" 
                    id="password_nueva" 
                    required
                    minlength="8"
                    placeholder="Mínimo 8 caracteres"
                >
                <small>
                    Debe contener:
                    • Al menos 8 caracteres
                    • Una letra mayúscula
                    • Una letra minúscula
                    • Un número
                </small>
            </div>
            
            <div class="form-group">
                <label>Confirmar Nueva Contraseña *</label>
                <input 
                    type="password" 
                    id="password_confirmacion" 
                    required
                    minlength="8"
                    placeholder="Repita la nueva contraseña"
                >
            </div>
            
            <button type="submit">Cambiar Contraseña</button>
        </form>
        
        <div id="mensaje"></div>
    </div>
    
    <script src="cambiar-password.js"></script>
</body>
</html>
```

**JavaScript**:
```javascript
// cambiar-password.js

document.getElementById('formCambiarPassword').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = '/login.html';
        return;
    }
    
    const passwordActual = document.getElementById('password_actual').value;
    const passwordNueva = document.getElementById('password_nueva').value;
    const passwordConfirmacion = document.getElementById('password_confirmacion').value;
    
    // Validación de coincidencia
    if (passwordNueva !== passwordConfirmacion) {
        mostrarError('Las contraseñas no coinciden');
        return;
    }
    
    // Validación de formato
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
    if (!regex.test(passwordNueva)) {
        mostrarError('La contraseña debe contener al menos una mayúscula, una minúscula y un número');
        return;
    }
    
    try {
        const response = await fetch('/api/auth/cambiar-password', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                password_actual: passwordActual,
                password_nueva: passwordNueva,
                password_confirmacion: passwordConfirmacion
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            mostrarExito(data.message);
            
            // Limpiar token y redirigir al login después de 2 segundos
            setTimeout(() => {
                localStorage.removeItem('token');
                window.location.href = '/login.html';
            }, 2000);
        } else {
            // Mostrar errores de validación
            if (data.errors) {
                let mensajeError = '';
                Object.values(data.errors).forEach(errors => {
                    mensajeError += errors.join('\n') + '\n';
                });
                mostrarError(mensajeError);
            } else {
                mostrarError(data.message);
            }
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('Error al cambiar la contraseña');
    }
});

function mostrarError(mensaje) {
    const div = document.getElementById('mensaje');
    div.className = 'alert alert-danger';
    div.textContent = mensaje;
}

function mostrarExito(mensaje) {
    const div = document.getElementById('mensaje');
    div.className = 'alert alert-success';
    div.textContent = mensaje;
}
```

---

### 4. Validación de Contraseña en Tiempo Real

```javascript
// Agregar validación visual en tiempo real
document.getElementById('password_nueva').addEventListener('input', function(e) {
    const password = e.target.value;
    const feedback = document.getElementById('password-feedback');
    
    const requisitos = {
        longitud: password.length >= 8,
        mayuscula: /[A-Z]/.test(password),
        minuscula: /[a-z]/.test(password),
        numero: /\d/.test(password)
    };
    
    let html = '<ul>';
    html += `<li class="${requisitos.longitud ? 'valid' : 'invalid'}">Mínimo 8 caracteres</li>`;
    html += `<li class="${requisitos.mayuscula ? 'valid' : 'invalid'}">Una letra mayúscula</li>`;
    html += `<li class="${requisitos.minuscula ? 'valid' : 'invalid'}">Una letra minúscula</li>`;
    html += `<li class="${requisitos.numero ? 'valid' : 'invalid'}">Un número</li>`;
    html += '</ul>';
    
    feedback.innerHTML = html;
});
```

**CSS**:
```css
.valid {
    color: green;
}

.invalid {
    color: red;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
}
```

---

## 🧪 Ejemplos de Prueba

### Caso 1: Crear Usuario con DNI
```bash
POST /api/usuarios
{
    "nombre": "Carlos Mendoza Silva",
    "email": "carlos@utp.edu.pe",
    "dni": "55667788",
    "rol_id": 2,
    "estado": true
}

# Resultado esperado:
# password_temporal: "55667788CA"
```

### Caso 2: Primer Login
```bash
POST /api/login
{
    "email": "carlos@utp.edu.pe",
    "password": "55667788CA"
}

# Resultado esperado:
# debe_cambiar_password: true
```

### Caso 3: Cambiar Contraseña
```bash
POST /api/auth/cambiar-password
Headers: Authorization: Bearer {token}
{
    "password_actual": "55667788CA",
    "password_nueva": "Carlos2026!",
    "password_confirmacion": "Carlos2026!"
}

# Resultado esperado:
# message: "Contraseña cambiada exitosamente..."
```

### Caso 4: Segundo Login (Normal)
```bash
POST /api/login
{
    "email": "carlos@utp.edu.pe",
    "password": "Carlos2026!"
}

# Resultado esperado:
# Login normal sin flag debe_cambiar_password
```

---

## ❌ Manejo de Errores Comunes

### Error: DNI duplicado
```json
{
    "message": "The dni has already been taken.",
    "errors": {
        "dni": ["The dni has already been taken."]
    }
}
```
**Solución**: Verificar que el DNI no esté registrado.

### Error: DNI con formato incorrecto
```json
{
    "message": "The dni field format is invalid.",
    "errors": {
        "dni": ["The dni field format is invalid."]
    }
}
```
**Solución**: Asegurar que el DNI tenga exactamente 8 dígitos numéricos.

### Error: Contraseña actual incorrecta
```json
{
    "message": "La contraseña actual es incorrecta"
}
```
**Solución**: El usuario debe ingresar correctamente su contraseña temporal o actual.

---

## 🔒 Consideraciones de Seguridad

1. **Comunicación de Contraseña Temporal**:
   - El admin debe comunicar la contraseña por un canal seguro
   - Nunca enviar por email sin cifrar
   - Idealmente, entregar en persona o mediante sistema interno

2. **Token JWT**:
   - Guardar en localStorage o sessionStorage
   - Incluir en header `Authorization: Bearer {token}` en todas las peticiones protegidas
   - Limpiar al hacer logout

3. **Validación Frontend + Backend**:
   - SIEMPRE validar en backend (no confiar solo en frontend)
   - Frontend valida para mejor UX
   - Backend valida para seguridad

4. **HTTPS**:
   - En producción, usar HTTPS para todas las comunicaciones
   - Nunca transmitir contraseñas por HTTP

---

## 📊 Estados del Usuario

| Estado | debe_cambiar_password | Acción en Login |
|--------|----------------------|-----------------|
| Usuario nuevo | `true` | Redirigir a cambio de contraseña |
| Contraseña cambiada | `false` | Login normal |

---

## 🛠️ Troubleshooting

### Problema: No se genera la contraseña temporal
**Causa**: Campo `nombre` vacío o error en lógica
**Solución**: Verificar que el nombre tenga al menos 2 caracteres

### Problema: El flag debe_cambiar_password no se detecta
**Causa**: Frontend no está revisando el campo en el response
**Solución**: Agregar validación `if (data.debe_cambiar_password === true)`

### Problema: Usuario no puede cambiar contraseña
**Causa**: Token no válido o expirado
**Solución**: Verificar que el token esté en el header Authorization

---

## 📞 Soporte

Para dudas o problemas con el sistema de DNI y contraseñas:
1. Revisar este manual
2. Verificar logs de Laravel: `storage/logs/laravel.log`
3. Probar endpoints con Postman/Insomnia
4. Contactar al desarrollador

---

**Última actualización**: 27 de enero de 2026
**Versión**: 1.0.0
