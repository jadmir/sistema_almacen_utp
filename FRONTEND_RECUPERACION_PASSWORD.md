# Documentación Frontend - Recuperación de Contraseña

## 🔑 Endpoints de la API

### **1. Solicitar Recuperación**
```
POST http://127.0.0.1:8000/api/password/solicitar-recuperacion
```

**Body (JSON):**
```json
{
  "email": "usuario@example.com"
}
```

**Respuesta Exitosa (200):**
```json
{
  "message": "Se ha enviado un enlace de recuperación a tu correo electrónico. Revisa tu bandeja de entrada."
}
```

**Errores (422):**
```json
{
  "message": "Error de validación",
  "errors": {
    "email": ["El correo electrónico es obligatorio"]
  }
}
```

---

### **2. Restablecer Contraseña**
```
POST http://127.0.0.1:8000/api/password/restablecer
```

**Body (JSON):**
```json
{
  "email": "usuario@example.com",
  "token": "el_token_que_viene_en_la_url",
  "password": "NuevaPassword123",
  "password_confirmation": "NuevaPassword123"
}
```

**Respuesta Exitosa (200):**
```json
{
  "message": "Contraseña restablecida exitosamente. Ya puedes iniciar sesión con tu nueva contraseña."
}
```

**Errores (400):**
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

**Errores (422):**
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

- ✅ Mínimo 8 caracteres
- ✅ Al menos una letra mayúscula
- ✅ Al menos una letra minúscula
- ✅ Al menos un número

---

## 🔄 Flujo Completo

### **Paso 1: Login - Agregar enlace**
```html
<form>
  <input type="email" placeholder="Correo electrónico" />
  <input type="password" placeholder="Contraseña" />
  <button>Iniciar Sesión</button>
  
  <!-- 👇 Agregar este enlace -->
  <a href="/forgot-password">¿Olvidaste tu contraseña?</a>
</form>
```

---

### **Paso 2: Página `/forgot-password` (Solicitar Recuperación)**

```html
<!DOCTYPE html>
<html>
<head>
  <title>Recuperar Contraseña</title>
</head>
<body>
  <h2>¿Olvidaste tu contraseña?</h2>
  <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
  
  <form id="forgotPasswordForm">
    <input 
      type="email" 
      id="email"
      placeholder="Correo electrónico"
      required
    />
    <button type="submit">Enviar enlace</button>
  </form>
  
  <a href="/login">Volver al inicio de sesión</a>
  
  <div id="mensaje"></div>

  <script>
    document.getElementById('forgotPasswordForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const email = document.getElementById('email').value;
      const mensajeDiv = document.getElementById('mensaje');
      
      try {
        const response = await fetch('http://127.0.0.1:8000/api/password/solicitar-recuperacion', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ email })
        });
        
        const data = await response.json();
        
        if (response.ok) {
          mensajeDiv.innerHTML = `<p style="color: green;">${data.message}</p>`;
          document.getElementById('email').value = '';
        } else {
          mensajeDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
        }
      } catch (error) {
        mensajeDiv.innerHTML = '<p style="color: red;">Error de conexión</p>';
      }
    });
  </script>
</body>
</html>
```

---

### **Paso 3: Página `/reset-password` (Restablecer Contraseña)**

**IMPORTANTE:** Esta página recibe `token` y `email` en la URL:
```
/reset-password?token=xxx&email=usuario@example.com
```

```html
<!DOCTYPE html>
<html>
<head>
  <title>Restablecer Contraseña</title>
</head>
<body>
  <h2>Restablecer Contraseña</h2>
  
  <form id="resetPasswordForm">
    <input 
      type="password" 
      id="password"
      placeholder="Nueva contraseña"
      minlength="8"
      required
    />
    
    <input 
      type="password" 
      id="password_confirmation"
      placeholder="Confirmar contraseña"
      minlength="8"
      required
    />
    
    <div style="background: #f0f0f0; padding: 10px; margin: 10px 0;">
      <p><strong>La contraseña debe contener:</strong></p>
      <ul>
        <li>Mínimo 8 caracteres</li>
        <li>Al menos una letra mayúscula</li>
        <li>Al menos una letra minúscula</li>
        <li>Al menos un número</li>
      </ul>
    </div>
    
    <button type="submit">Restablecer contraseña</button>
  </form>
  
  <div id="mensaje"></div>

  <script>
    // Obtener parámetros de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    const email = urlParams.get('email');
    
    // Validar que existan los parámetros
    if (!token || !email) {
      document.body.innerHTML = '<h2>Enlace inválido</h2><a href="/login">Ir al login</a>';
    }
    
    document.getElementById('resetPasswordForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const password = document.getElementById('password').value;
      const password_confirmation = document.getElementById('password_confirmation').value;
      const mensajeDiv = document.getElementById('mensaje');
      
      // Validar que las contraseñas coincidan
      if (password !== password_confirmation) {
        mensajeDiv.innerHTML = '<p style="color: red;">Las contraseñas no coinciden</p>';
        return;
      }
      
      try {
        const response = await fetch('http://127.0.0.1:8000/api/password/restablecer', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            email,
            token,
            password,
            password_confirmation
          })
        });
        
        const data = await response.json();
        
        if (response.ok) {
          mensajeDiv.innerHTML = `<p style="color: green;">${data.message}</p>`;
          
          // Redirigir al login después de 2 segundos
          setTimeout(() => {
            window.location.href = '/login';
          }, 2000);
        } else {
          // Mostrar errores
          if (data.errors) {
            let errores = '<div style="color: red;">';
            Object.values(data.errors).forEach(errorArray => {
              errorArray.forEach(error => {
                errores += `<p>${error}</p>`;
              });
            });
            errores += '</div>';
            mensajeDiv.innerHTML = errores;
          } else {
            mensajeDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
          }
        }
      } catch (error) {
        mensajeDiv.innerHTML = '<p style="color: red;">Error de conexión</p>';
      }
    });
  </script>
</body>
</html>
```

---

## 📧 ¿Qué recibe el usuario en su correo?

El usuario recibirá un correo con:
- Un botón grande que dice "Restablecer Contraseña"
- Ese botón lo lleva a: `http://tu-frontend.com/reset-password?token=xxx&email=xxx`
- Un enlace alternativo por si el botón no funciona
- Aviso de que el enlace expira en 60 minutos

---

## ⚠️ Notas Importantes

1. **Ambas páginas son públicas** - No requieren que el usuario esté logueado
2. **El token expira en 60 minutos** - Después debe solicitar uno nuevo
3. **Cambiar URL en producción** - En el archivo `.env` cambiar `APP_URL` a la URL real del frontend
4. **Sin autenticación JWT** - Estos endpoints NO requieren token de autorización

---

## ✅ Checklist de Implementación

**Login:**
- [ ] Agregar enlace "¿Olvidaste tu contraseña?" que vaya a `/forgot-password`

**Página `/forgot-password`:**
- [ ] Crear página con formulario de email
- [ ] Hacer POST a `/api/password/solicitar-recuperacion`
- [ ] Mostrar mensaje de éxito
- [ ] Agregar enlace para volver al login

**Página `/reset-password`:**
- [ ] Crear página que reciba `token` y `email` de la URL
- [ ] Validar que existan los parámetros
- [ ] Formulario con 2 campos de contraseña
- [ ] Mostrar requisitos de contraseña
- [ ] Hacer POST a `/api/password/restablecer`
- [ ] Redirigir al login después de éxito

---

## 🧪 Pruebas

1. Crear un usuario con tu correo real
2. Ir a login → clic en "¿Olvidaste tu contraseña?"
3. Ingresar tu correo
4. Revisar tu bandeja de entrada (puede tardar 1-2 minutos)
5. Hacer clic en el botón del correo
6. Ingresar nueva contraseña
7. Iniciar sesión con la nueva contraseña

---

## 📞 Soporte

Si hay problemas:
- Verificar que el correo SMTP esté configurado en el backend
- Revisar la bandeja de spam
- Verificar que `APP_URL` en `.env` apunte a la URL correcta del frontend
