# Manual de Recuperación de Contraseña por Correo

## 📧 Resumen

Se ha implementado un sistema completo de recuperación de contraseña por correo electrónico. Los usuarios pueden solicitar un enlace de recuperación que será enviado a su correo registrado.

---

## 🔄 Endpoints de la API

### **1. Solicitar Recuperación de Contraseña**

```
POST /api/password/solicitar-recuperacion
```

**Cuerpo de la solicitud:**
```json
{
  "email": "usuario@example.com"
}
```

**Respuesta exitosa (200):**
```json
{
  "message": "Se ha enviado un enlace de recuperación a tu correo electrónico. Revisa tu bandeja de entrada."
}
```

**Errores de validación (422):**
```json
{
  "message": "Error de validación",
  "errors": {
    "email": ["El correo electrónico es obligatorio"]
  }
}
```

**Nota de seguridad:** Por razones de seguridad, siempre se responde con el mismo mensaje aunque el correo no exista en el sistema. Esto previene que atacantes descubran qué correos están registrados.

---

### **2. Restablecer Contraseña**

```
POST /api/password/restablecer
```

**Cuerpo de la solicitud:**
```json
{
  "email": "usuario@example.com",
  "token": "el_token_del_correo",
  "password": "NuevaPassword123",
  "password_confirmation": "NuevaPassword123"
}
```

**Respuesta exitosa (200):**
```json
{
  "message": "Contraseña restablecida exitosamente. Ya puedes iniciar sesión con tu nueva contraseña."
}
```

**Token inválido o expirado (400):**
```json
{
  "message": "Token inválido o expirado"
}
```

```json
{
  "message": "El token ha expirado. Por favor, solicita uno nuevo."
}
```

**Errores de validación (422):**
```json
{
  "message": "Error de validación",
  "errors": {
    "password": ["La contraseña debe tener al menos 8 caracteres"],
    "password_confirmation": ["Las contraseñas no coinciden"]
  }
}
```

---

## 📋 Validaciones de Contraseña

La nueva contraseña debe cumplir con:
- ✅ Mínimo 8 caracteres
- ✅ Máximo 50 caracteres
- ✅ Al menos una letra mayúscula
- ✅ Al menos una letra minúscula
- ✅ Al menos un número

---

## ⏰ Tiempo de Expiración

- Los tokens de recuperación **expiran en 60 minutos** (1 hora)
- Después de ese tiempo, el usuario debe solicitar un nuevo enlace
- Los tokens expirados se eliminan automáticamente al intentar usarlos

---

## 🔐 Seguridad Implementada

1. **Tokens únicos:** Cada solicitud genera un token aleatorio de 64 caracteres
2. **Hash del token:** Los tokens se guardan hasheados en la base de datos
3. **Un token por usuario:** Solicitar un nuevo token invalida el anterior
4. **Expiración automática:** Los tokens solo son válidos por 60 minutos
5. **Eliminación después de uso:** El token se elimina al cambiar la contraseña
6. **Respuestas genéricas:** No se revela si un correo existe o no en el sistema
7. **Verificación de estado:** Solo usuarios activos pueden recuperar su contraseña

---

## 📧 Correo de Recuperación

El usuario recibirá un correo con:

- ✉️ **Asunto:** "Recuperación de Contraseña - Sistema Almacén UTP"
- 👋 **Saludo personalizado** con el nombre del usuario
- 🔗 **Botón de acción** para restablecer contraseña
- 📋 **Enlace alternativo** si el botón no funciona
- ⏰ **Aviso de expiración** (60 minutos)
- 🛡️ **Nota de seguridad** indicando qué hacer si no solicitó el cambio

---

## 💻 Implementación en el Frontend

### **Flujo Completo**

```
1. Usuario hace clic en "¿Olvidaste tu contraseña?"
   ↓
2. Ingresa su correo electrónico
   ↓
3. Frontend llama a POST /api/password/solicitar-recuperacion
   ↓
4. Usuario recibe correo con enlace
   ↓
5. Usuario hace clic en el enlace del correo
   ↓
6. Se abre formulario con token y email en la URL
   ↓
7. Usuario ingresa nueva contraseña
   ↓
8. Frontend llama a POST /api/password/restablecer
   ↓
9. Contraseña actualizada, redirigir al login
```

---

### **Página 1: Solicitar Recuperación**

```html
<!-- /forgot-password -->
<form id="formSolicitarRecuperacion">
  <h2>¿Olvidaste tu contraseña?</h2>
  <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
  
  <input 
    type="email" 
    name="email" 
    placeholder="Correo electrónico"
    required
  />
  
  <button type="submit">Enviar enlace de recuperación</button>
  
  <a href="/login">Volver al inicio de sesión</a>
</form>

<script>
document.getElementById('formSolicitarRecuperacion').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const email = e.target.email.value;
  
  try {
    const response = await fetch('http://127.0.0.1:8000/api/password/solicitar-recuperacion', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email })
    });
    
    const result = await response.json();
    
    if (response.ok) {
      mostrarMensaje(result.message, 'success');
      // Opcional: Mostrar página de confirmación
    } else {
      mostrarMensaje(result.message || 'Error al enviar el correo', 'error');
    }
  } catch (error) {
    mostrarMensaje('Error de conexión', 'error');
  }
});
</script>
```

---

### **Página 2: Restablecer Contraseña**

```html
<!-- /reset-password?token=xxx&email=xxx -->
<form id="formRestablecerPassword">
  <h2>Restablecer Contraseña</h2>
  <p>Ingresa tu nueva contraseña.</p>
  
  <input 
    type="password" 
    name="password" 
    placeholder="Nueva contraseña"
    minlength="8"
    required
  />
  
  <input 
    type="password" 
    name="password_confirmation" 
    placeholder="Confirmar contraseña"
    minlength="8"
    required
  />
  
  <div class="password-requirements">
    <p>La contraseña debe contener:</p>
    <ul>
      <li>Mínimo 8 caracteres</li>
      <li>Al menos una letra mayúscula</li>
      <li>Al menos una letra minúscula</li>
      <li>Al menos un número</li>
    </ul>
  </div>
  
  <button type="submit">Restablecer contraseña</button>
</form>

<script>
// Obtener parámetros de la URL
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');
const email = urlParams.get('email');

// Verificar que existan los parámetros
if (!token || !email) {
  mostrarMensaje('Enlace inválido', 'error');
  window.location.href = '/login';
}

document.getElementById('formRestablecerPassword').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const password = e.target.password.value;
  const password_confirmation = e.target.password_confirmation.value;
  
  // Validación básica en frontend
  if (password !== password_confirmation) {
    mostrarMensaje('Las contraseñas no coinciden', 'error');
    return;
  }
  
  try {
    const response = await fetch('http://127.0.0.1:8000/api/password/restablecer', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email,
        token,
        password,
        password_confirmation
      })
    });
    
    const result = await response.json();
    
    if (response.ok) {
      mostrarMensaje(result.message, 'success');
      
      // Redirigir al login después de 2 segundos
      setTimeout(() => {
        window.location.href = '/login';
      }, 2000);
    } else {
      mostrarMensaje(result.message || 'Error al restablecer la contraseña', 'error');
      
      if (result.errors) {
        // Mostrar errores específicos de validación
        Object.values(result.errors).forEach(errorArray => {
          errorArray.forEach(error => mostrarMensaje(error, 'error'));
        });
      }
    }
  } catch (error) {
    mostrarMensaje('Error de conexión', 'error');
  }
});
</script>
```

---

### **Ejemplo con React**

```jsx
// Página: SolicitarRecuperacion.jsx
import { useState } from 'react';

function SolicitarRecuperacion() {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setMessage(null);

    try {
      const response = await fetch('/api/password/solicitar-recuperacion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
      });

      const result = await response.json();
      setMessage({ type: 'success', text: result.message });
      setEmail('');
    } catch (error) {
      setMessage({ type: 'error', text: 'Error al enviar el correo' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="recuperacion-container">
      <h2>¿Olvidaste tu contraseña?</h2>
      
      <form onSubmit={handleSubmit}>
        <input
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          placeholder="Correo electrónico"
          required
        />
        
        <button type="submit" disabled={loading}>
          {loading ? 'Enviando...' : 'Enviar enlace'}
        </button>
      </form>

      {message && (
        <div className={`alert alert-${message.type}`}>
          {message.text}
        </div>
      )}

      <a href="/login">Volver al inicio de sesión</a>
    </div>
  );
}
```

```jsx
// Página: RestablecerPassword.jsx
import { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';

function RestablecerPassword() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  
  const [formData, setFormData] = useState({
    password: '',
    password_confirmation: ''
  });
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const token = searchParams.get('token');
  const email = searchParams.get('email');

  useEffect(() => {
    if (!token || !email) {
      navigate('/login');
    }
  }, [token, email, navigate]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      const response = await fetch('/api/password/restablecer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email,
          token,
          ...formData
        })
      });

      const result = await response.json();

      if (response.ok) {
        toast.success(result.message);
        setTimeout(() => navigate('/login'), 2000);
      } else {
        if (result.errors) {
          setErrors(result.errors);
        } else {
          toast.error(result.message);
        }
      }
    } catch (error) {
      toast.error('Error de conexión');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="restablecer-container">
      <h2>Restablecer Contraseña</h2>
      
      <form onSubmit={handleSubmit}>
        <div>
          <input
            type="password"
            placeholder="Nueva contraseña"
            value={formData.password}
            onChange={(e) => setFormData({...formData, password: e.target.value})}
            required
          />
          {errors.password && <span className="error">{errors.password[0]}</span>}
        </div>

        <div>
          <input
            type="password"
            placeholder="Confirmar contraseña"
            value={formData.password_confirmation}
            onChange={(e) => setFormData({...formData, password_confirmation: e.target.value})}
            required
          />
          {errors.password_confirmation && <span className="error">{errors.password_confirmation[0]}</span>}
        </div>

        <div className="requirements">
          <p>La contraseña debe contener:</p>
          <ul>
            <li>Mínimo 8 caracteres</li>
            <li>Al menos una mayúscula</li>
            <li>Al menos una minúscula</li>
            <li>Al menos un número</li>
          </ul>
        </div>

        <button type="submit" disabled={loading}>
          {loading ? 'Restableciendo...' : 'Restablecer contraseña'}
        </button>
      </form>
    </div>
  );
}
```

---

## 🗄️ Base de Datos

### **Tabla: password_resets**

| Campo      | Tipo      | Descripción                           |
|------------|-----------|---------------------------------------|
| email      | string    | Correo del usuario (indexado)        |
| token      | string    | Token hasheado de recuperación       |
| created_at | timestamp | Fecha de creación del token          |

**Índices:**
- `email` (individual)
- `email` + `token` (compuesto)

---

## 🧪 Pruebas

### **Probar flujo completo:**

1. **Crear usuario de prueba** con tu correo real
2. **Ir a login** → "¿Olvidaste tu contraseña?"
3. **Ingresar tu correo**
4. **Revisar correo** (puede tardar 1-2 minutos)
5. **Hacer clic en el enlace** del correo
6. **Ingresar nueva contraseña**
7. **Iniciar sesión** con la nueva contraseña

### **Casos de prueba:**

- ✅ Solicitar con correo válido
- ✅ Solicitar con correo inexistente (debe responder igual)
- ✅ Token expirado (después de 60 minutos)
- ✅ Token inválido
- ✅ Token ya usado
- ✅ Contraseña que no cumple requisitos
- ✅ Contraseñas que no coinciden

---

## ⚠️ Consideraciones

1. **URL del Frontend:** Actualizar en `.env` la variable `APP_URL` con la URL real del frontend en producción
   ```env
   APP_URL=https://tu-dominio.com
   ```

2. **Límite de correos:** Gmail permite ~500 correos/día. Si usas Outlook, el límite es similar.

3. **Bandeja de spam:** Los primeros correos pueden caer en spam. Usuarios deben marcar como "No es spam".

4. **Tiempo de expiración:** Configurable en el código (actualmente 60 minutos).

5. **Limpieza de tokens:** Considera agregar un comando artisan para eliminar tokens expirados periódicamente.

---

## 📝 Archivos Creados/Modificados

### **Nuevos archivos:**
- ✅ `database/migrations/2026_02_23_141323_create_password_resets_table.php`
- ✅ `app/Mail/RecuperarPassword.php`
- ✅ `resources/views/emails/recuperar-password.blade.php`

### **Archivos modificados:**
- ✅ `app/Http/Controllers/AuthController.php` (agregados métodos `solicitarRecuperacion()` y `restablecerPassword()`)
- ✅ `routes/api.php` (agregadas 2 rutas públicas)

---

## 🔧 Configuración Backend

**Estado actual:**
- ✅ Migración ejecutada
- ✅ Tabla `password_resets` creada
- ✅ Controlador implementado
- ✅ Rutas públicas configuradas
- ✅ Plantilla de correo diseñada
- ✅ Validaciones implementadas
- ✅ Seguridad configurada

**Listo para usar en producción** ✅

---

**Fecha de implementación:** 23 de febrero de 2026  
**Versión:** 1.0
