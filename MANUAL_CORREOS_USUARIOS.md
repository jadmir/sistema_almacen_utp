# Manual de Cambios - Sistema de Correos para Creación de Usuarios

## 📧 Resumen del Cambio

Ahora cuando se crea un usuario, **automáticamente se envía un correo** con las credenciales. Ya no es necesario mostrar la contraseña en un modal, salvo que falle el envío del correo.

---

## 🔄 Cambios en la Respuesta de la API

### **ANTES (Comportamiento antiguo)**

```json
POST /api/usuarios

Response 201:
{
  "message": "Usuario creado exitosamente",
  "data": {
    "id": 1,
    "nombre": "Juan Pérez",
    "dni": "12345678",
    "email": "juan@example.com",
    "rol_id": 2,
    "role": {
      "id": 2,
      "nombre": "Almacenero"
    }
  },
  "password_temporal": "12345678JU"  // ❌ Siempre venía
}
```

### **AHORA (Nuevo comportamiento)**

#### **Caso 1: Correo enviado exitosamente** ✅
`1. **Crear un usuario con un correo real** (el tuyo o uno de prueba)
2. **Verificar que NO aparezca el modal** con la contraseña
3. **Revisar la bandeja de entrada** del correo proporcionado
4. **Confirmar** que llegó el correo con las credenciales

---

## ❓ Preguntas Frecuentes

**P: ¿Qué pasa si el usuario no recibe el correo?**  
R: El sistema detecta si el correo falla y devuelve `password_temporal` en la respuesta. En ese caso, el frontend debe mostrar el modal con las credenciales.

**P: ¿Debo eliminar completamente el modal de credenciales?**  
R: NO. Mantenlo como fallback para cuando falle el envío del correo. Solo evita mostrarlo cuando el correo se envíe exitosamente.

**P: ¿Cómo sé si el correo se envió?**  
R: Si la respuesta NO contiene `password_temporal`, significa que el correo se envió correctamente.

**P: ¿El usuario puede usar la misma contraseña?**  
R: No. El sistema obliga al usuario a cambiar su contraseña en el primer inicio de sesión.

---

## 🔧 Configuración del Backend (Ya implementado)

✅ Correo configurado: `almacenutp86@gmail.com`  
✅ SMTP: Gmail (smtp.gmail.com:587)  
✅ Plantilla de correo: `resources/views/emails/usuario-creado.blade.php`  
✅ Clase Mailable: `app/Mail/UsuarioCreado.php`  
✅ Controlador modificado: `app/Http/Controllers/UsuarioController.php`

---

**Fecha de implementación:** 19 de febrero de 2026  
**Versión:** 1.0
``json
Response 201:
{
  "message": "Usuario creado exitosamente. Se ha enviado un correo con las credenciales de acceso.",
  "data": {
    "id": 1,
    "nombre": "Juan Pérez",
    "dni": "12345678",
    "email": "juan@example.com",
    "rol_id": 2,
    "role": {
      "id": 2,
      "nombre": "Almacenero"
    }
  }
  // ❌ NO viene "password_temporal"
}
```

#### **Caso 2: Correo NO se pudo enviar** ⚠️
```json
Response 201:
{
  "message": "Usuario creado exitosamente, pero no se pudo enviar el correo.",
  "data": {
    "id": 1,
    "nombre": "Juan Pérez",
    "dni": "12345678",
    "email": "juan@example.com",
    "rol_id": 2
  },
  "password_temporal": "12345678JU",  // ✅ Solo viene si falló el correo
  "error_correo": "Por favor, proporcione las credenciales manualmente al usuario."
}
```

---

## 💻 Cambios Necesarios en el Frontend

### **1. Modificar la función de creación de usuario**

```javascript
async function crearUsuario(datosFormulario) {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/usuarios', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}` // Si usas JWT
      },
      body: JSON.stringify(datosFormulario)
    });

    const result = await response.json();

    if (response.ok) {
      // Verificar si el correo falló
      if (result.password_temporal && result.error_correo) {
        // ⚠️ FALLBACK: Mostrar modal con contraseña (solo si falló el correo)
        mostrarModalCredenciales({
          nombre: result.data.nombre,
          dni: result.data.dni,
          password: result.password_temporal
        });
        
        mostrarNotificacion(
          'Usuario creado, pero el correo no se envió. Por favor, proporciona estas credenciales al usuario.',
          'warning'
        );
      } else {
        // ✅ TODO OK: Correo enviado exitosamente
        mostrarNotificacion(
          `Usuario creado exitosamente. Se ha enviado un correo a ${result.data.email} con las credenciales.`,
          'success'
        );
      }

      // Cerrar formulario y recargar tabla
      cerrarFormularioUsuario();
      recargarTablaUsuarios();
    } else {
      // Error de validación o servidor
      mostrarNotificacion(result.message || 'Error al crear usuario', 'error');
    }

  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error de conexión al servidor', 'error');
  }
}
```

---

### **2. Ejemplo para React**

```jsx
import { useState } from 'react';

function CrearUsuarioForm() {
  const [showPasswordModal, setShowPasswordModal] = useState(false);
  const [credenciales, setCredenciales] = useState(null);

  const handleSubmit = async (datos) => {
    try {
      const response = await fetch('/api/usuarios', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
      });

      const result = await response.json();

      if (response.ok) {
        // Verificar si falló el envío del correo
        if (result.password_temporal) {
          // Mostrar modal de fallback
          setCredenciales({
            nombre: result.data.nombre,
            dni: result.data.dni,
            password: result.password_temporal
          });
          setShowPasswordModal(true);
          
          toast.warning('Usuario creado, pero no se envió el correo. Muestra estas credenciales al usuario.');
        } else {
          // Correo enviado exitosamente
          toast.success(`Usuario creado. Se envió un correo a ${result.data.email}`);
        }

        onClose(); // Cerrar formulario
        refreshTable(); // Recargar tabla
      }
    } catch (error) {
      toast.error('Error al crear usuario');
    }
  };

  return (
    <>
      {/* Tu formulario aquí */}
      
      {/* Modal de credenciales (solo se muestra si falla el correo) */}
      {showPasswordModal && (
        <PasswordModal 
          credenciales={credenciales}
          onClose={() => setShowPasswordModal(false)}
        />
      )}
    </>
  );
}
```

---

### **3. Ejemplo de Modal de Credenciales (Fallback)**

Este modal **solo debe mostrarse** cuando `password_temporal` venga en la respuesta (es decir, cuando falle el envío del correo).

```jsx
function PasswordModal({ credenciales, onClose }) {
  return (
    <div className="modal-overlay">
      <div className="modal-content">
        <div className="modal-header warning">
          <h3>⚠️ Correo no enviado - Credenciales Temporales</h3>
        </div>
        
        <div className="modal-body">
          <p><strong>Nombre:</strong> {credenciales.nombre}</p>
          <p><strong>Usuario (DNI):</strong> {credenciales.dni}</p>
          <p><strong>Contraseña Temporal:</strong> 
            <code>{credenciales.password}</code>
          </p>
          
          <div className="alert alert-warning">
            Por favor, proporciona estas credenciales al usuario manualmente.
            El sistema no pudo enviar el correo automáticamente.
          </div>
        </div>
        
        <div className="modal-footer">
          <button onClick={onClose}>Cerrar</button>
        </div>
      </div>
    </div>
  );
}
```

---

## 📋 Lista de Verificación

### Cambios a realizar en el Frontend:

- [ ] **Modificar** la función que crea usuarios para manejar la nueva respuesta
- [ ] **Verificar** si existe `password_temporal` en la respuesta
- [ ] **Mostrar notificación de éxito** cuando el correo se envía (sin modal)
- [ ] **Mantener modal de credenciales** solo como fallback si falla el correo
- [ ] **Actualizar mensajes** de confirmación para informar que se envió un correo
- [ ] **Probar** ambos escenarios:
  - ✅ Correo enviado correctamente (no mostrar modal)
  - ⚠️ Correo falló (mostrar modal con credenciales)

---

## 🎨 Mensajes Sugeridos para el Usuario

### **Éxito (correo enviado):**
```
✅ Usuario creado exitosamente
Se ha enviado un correo a juan@example.com con las credenciales de acceso.
```

### **Advertencia (correo falló):**
```
⚠️ Usuario creado, pero el correo no se envió
Por favor, proporciona las siguientes credenciales al usuario:
Usuario: juan@example.com
Contraseña: 12345678JU
```

---

## 📧 Contenido del Correo que Recibe el Usuario

El usuario recibirá un correo profesional con:

- ✉️ Asunto: "Bienvenido al Sistema de Almacén UTP"
- 🔑 Usuario (Correo Electrónico)
- 🔒 Contraseña temporal
- 📋 Instrucciones de primer acceso
- 🔗 Botón para acceder al sistema
- ⚠️ Advertencia de seguridad (cambio obligatorio de contraseña)

---

## 🧪 Cómo Probar

