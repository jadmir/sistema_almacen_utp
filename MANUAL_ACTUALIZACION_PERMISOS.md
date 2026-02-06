# 🔄 SISTEMA DE ACTUALIZACIÓN AUTOMÁTICA DE PERMISOS

## 📝 Descripción

Sistema que mantiene los permisos del usuario sincronizados en tiempo real sin necesidad de cerrar sesión cuando un administrador modifica los permisos.

---

## 🎯 Problema Resuelto

**Antes**: Si el admin quitaba permisos a un usuario, este seguía viéndolos hasta cerrar sesión.

**Ahora**: Los permisos se actualizan automáticamente cada 30 segundos o cuando el usuario navega.

---

## 🔧 API Endpoint

### Refrescar Permisos

**Endpoint**: `GET /api/auth/refresh-permissions`

**Headers**:
```
Authorization: Bearer {token}
```

**Response Exitoso (200)**:
```json
{
    "message": "Permisos actualizados",
    "user": {
        "id": 5,
        "nombre": "María García López",
        "email": "maria@utp.edu.pe",
        "estado": true,
        "rol": "Asistente",
        "permissions": [],
        "role": {
            "id": 2,
            "nombre": "Asistente",
            "descripcion": "Rol de asistente de almacén",
            "permissions": [
                {
                    "id": 1,
                    "nombre": "Ver Inventario",
                    "slug": "inventario.ver"
                }
            ]
        }
    }
}
```

**Response Usuario Desactivado (403)**:
```json
{
    "message": "Usuario desactivado",
    "logout_required": true
}
```

---

## 🖥️ Implementación Frontend

### 1. Sistema de Polling (Actualización Periódica)

```javascript
// auth-service.js

class AuthService {
    constructor() {
        this.pollingInterval = null;
        this.REFRESH_INTERVAL = 30000; // 30 segundos
    }

    /**
     * Iniciar polling de permisos
     */
    startPermissionPolling() {
        // Limpiar polling anterior si existe
        this.stopPermissionPolling();
        
        // Ejecutar inmediatamente
        this.refreshPermissions();
        
        // Configurar intervalo
        this.pollingInterval = setInterval(() => {
            this.refreshPermissions();
        }, this.REFRESH_INTERVAL);
        
        console.log('✅ Polling de permisos iniciado (cada 30s)');
    }

    /**
     * Detener polling de permisos
     */
    stopPermissionPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
            console.log('🛑 Polling de permisos detenido');
        }
    }

    /**
     * Refrescar permisos del usuario
     */
    async refreshPermissions() {
        const token = localStorage.getItem('token');
        
        if (!token) {
            this.stopPermissionPolling();
            return;
        }

        try {
            const response = await fetch('/api/auth/refresh-permissions', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Actualizar datos del usuario en localStorage
                localStorage.setItem('user', JSON.stringify(data.user));
                
                // Emitir evento personalizado para que componentes se actualicen
                window.dispatchEvent(new CustomEvent('permissions-updated', {
                    detail: data.user
                }));
                
                console.log('✅ Permisos actualizados:', data.user.permissions.length + data.user.role.permissions.length);
                
            } else if (response.status === 403 && data.logout_required) {
                // Usuario desactivado, forzar logout
                console.warn('⚠️ Usuario desactivado, cerrando sesión...');
                this.forceLogout('Tu cuenta ha sido desactivada');
            } else if (response.status === 401) {
                // Token expirado
                console.warn('⚠️ Token expirado, cerrando sesión...');
                this.forceLogout('Tu sesión ha expirado');
            }

        } catch (error) {
            console.error('❌ Error al refrescar permisos:', error);
        }
    }

    /**
     * Forzar logout cuando usuario es desactivado o token expira
     */
    forceLogout(message) {
        this.stopPermissionPolling();
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        
        alert(message);
        window.location.href = '/login.html';
    }

    /**
     * Login del usuario (llamar después de login exitoso)
     */
    async login(email, password) {
        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (response.ok) {
                // Guardar token y usuario
                localStorage.setItem('token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));
                
                // Iniciar polling de permisos
                this.startPermissionPolling();
                
                return { success: true, data };
            } else {
                return { success: false, message: data.message };
            }

        } catch (error) {
            console.error('Error en login:', error);
            return { success: false, message: 'Error de conexión' };
        }
    }

    /**
     * Logout del usuario
     */
    async logout() {
        try {
            const token = localStorage.getItem('token');
            
            await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

        } catch (error) {
            console.error('Error en logout:', error);
        } finally {
            this.stopPermissionPolling();
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login.html';
        }
    }

    /**
     * Obtener usuario actual del localStorage
     */
    getCurrentUser() {
        const userString = localStorage.getItem('user');
        return userString ? JSON.parse(userString) : null;
    }

    /**
     * Verificar si usuario tiene un permiso específico
     */
    hasPermission(permissionSlug) {
        const user = this.getCurrentUser();
        if (!user) return false;

        // Combinar permisos del rol + permisos individuales
        const rolePermissions = user.role?.permissions || [];
        const individualPermissions = user.permissions || [];
        
        const allPermissions = [...rolePermissions, ...individualPermissions];
        
        return allPermissions.some(p => p.slug === permissionSlug);
    }
}

// Crear instancia global
const authService = new AuthService();
```

---

### 2. Inicializar en el HTML Principal

```html
<!-- dashboard.html o tu archivo principal -->
<!DOCTYPE html>
<html>
<head>
    <title>Sistema UTP - Dashboard</title>
</head>
<body>
    <div id="app">
        <!-- Tu contenido aquí -->
    </div>

    <script src="auth-service.js"></script>
    <script>
        // Iniciar cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('token');
            
            if (!token) {
                window.location.href = '/login.html';
                return;
            }

            // Iniciar polling de permisos
            authService.startPermissionPolling();

            // Escuchar actualizaciones de permisos
            window.addEventListener('permissions-updated', function(event) {
                const updatedUser = event.detail;
                
                console.log('📢 Permisos actualizados en tiempo real');
                
                // Recargar menú o componentes que dependen de permisos
                actualizarMenuNavegacion(updatedUser);
                verificarAccesoActual(updatedUser);
            });

            // Detener polling cuando se cierra la pestaña
            window.addEventListener('beforeunload', function() {
                authService.stopPermissionPolling();
            });
        });

        /**
         * Actualizar menú según permisos actuales
         */
        function actualizarMenuNavegacion(user) {
            // Obtener todos los permisos (rol + individuales)
            const rolePermissions = user.role?.permissions || [];
            const individualPermissions = user.permissions || [];
            const todosPermisos = [...rolePermissions, ...individualPermissions];

            // Ocultar/mostrar elementos del menú
            const menuItems = document.querySelectorAll('[data-permission]');
            
            menuItems.forEach(item => {
                const requiredPermission = item.getAttribute('data-permission');
                const hasAccess = todosPermisos.some(p => p.slug === requiredPermission);
                
                if (hasAccess) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        /**
         * Verificar si usuario aún tiene acceso a la página actual
         */
        function verificarAccesoActual(user) {
            const paginaActual = document.body.getAttribute('data-required-permission');
            
            if (!paginaActual) return; // Página sin restricción
            
            const rolePermissions = user.role?.permissions || [];
            const individualPermissions = user.permissions || [];
            const todosPermisos = [...rolePermissions, ...individualPermissions];
            
            const tieneAcceso = todosPermisos.some(p => p.slug === paginaActual);
            
            if (!tieneAcceso) {
                alert('Ya no tienes acceso a esta sección. Serás redirigido al dashboard.');
                window.location.href = '/dashboard.html';
            }
        }
    </script>
</body>
</html>
```

---

### 3. Login con Polling Automático

```html
<!-- login.html -->
<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistema UTP</title>
</head>
<body>
    <form id="loginForm">
        <input type="email" id="email" placeholder="Email" required>
        <input type="password" id="password" placeholder="Contraseña" required>
        <button type="submit">Iniciar Sesión</button>
    </form>

    <script src="auth-service.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            const result = await authService.login(email, password);

            if (result.success) {
                // El polling ya se inició automáticamente en authService.login()
                
                // Verificar si debe cambiar contraseña
                if (result.data.debe_cambiar_password) {
                    window.location.href = '/cambiar-password.html';
                } else {
                    window.location.href = '/dashboard.html';
                }
            } else {
                alert(result.message);
            }
        });
    </script>
</body>
</html>
```

---

### 4. Mostrar Permisos en Interfaz

```html
<!-- Menú de navegación con permisos dinámicos -->
<nav>
    <ul>
        <!-- Solo visible si tiene el permiso -->
        <li data-permission="inventario.ver">
            <a href="/inventario.html">📦 Inventario</a>
        </li>
        
        <li data-permission="reportes.generar">
            <a href="/reportes.html">📊 Reportes</a>
        </li>
        
        <li data-permission="usuarios.gestionar">
            <a href="/usuarios.html">👥 Usuarios</a>
        </li>
    </ul>
</nav>

<!-- Sección que requiere permiso específico -->
<div id="seccionProductos" data-required-permission="productos.gestionar">
    <h2>Gestión de Productos</h2>
    <!-- Contenido -->
</div>

<script>
    // Aplicar permisos cuando carga la página
    const user = authService.getCurrentUser();
    actualizarMenuNavegacion(user);
</script>
```

---

### 5. Verificación Manual de Permisos

```javascript
// Verificar permiso antes de una acción
function eliminarProducto(productId) {
    // Verificar permiso actual
    if (!authService.hasPermission('productos.eliminar')) {
        alert('No tienes permiso para eliminar productos');
        return;
    }

    // Proceder con la eliminación
    fetch(`/api/productos/${productId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Producto eliminado');
    });
}

// Mostrar/ocultar botones según permisos
function actualizarBotonesAccion() {
    const user = authService.getCurrentUser();
    
    // Botón de eliminar
    const btnEliminar = document.getElementById('btnEliminar');
    if (authService.hasPermission('productos.eliminar')) {
        btnEliminar.style.display = 'block';
    } else {
        btnEliminar.style.display = 'none';
    }

    // Botón de crear
    const btnCrear = document.getElementById('btnCrear');
    if (authService.hasPermission('productos.crear')) {
        btnCrear.style.display = 'block';
    } else {
        btnCrear.style.display = 'none';
    }
}

// Actualizar botones cuando cambian permisos
window.addEventListener('permissions-updated', actualizarBotonesAccion);
```

---

## ⏱️ Configuración del Intervalo

Para cambiar la frecuencia de actualización:

```javascript
// auth-service.js
class AuthService {
    constructor() {
        // Cambiar tiempo de actualización
        this.REFRESH_INTERVAL = 15000; // 15 segundos (más frecuente)
        // this.REFRESH_INTERVAL = 60000; // 1 minuto (menos frecuente)
        // this.REFRESH_INTERVAL = 30000; // 30 segundos (recomendado)
    }
}
```

---

## 🚀 Flujo Completo

```
1. USUARIO HACE LOGIN
   ↓
   authService.login() guarda token
   ↓
   Se inicia polling automático (cada 30s)
   ↓
   Usuario navega normalmente

2. ADMIN QUITA PERMISOS AL USUARIO
   ↓
   Admin llama POST /api/usuarios/{id}/permisos
   {
       "remove_all": true,
       "permission_ids": []
   }
   ↓
   Permisos eliminados en base de datos

3. POLLING DETECTA CAMBIO (máximo 30s después)
   ↓
   GET /api/auth/refresh-permissions obtiene permisos actuales
   ↓
   localStorage.setItem('user', JSON.stringify(datosNuevos))
   ↓
   Se emite evento 'permissions-updated'
   ↓
   Frontend actualiza menús, botones, secciones automáticamente
   ↓
   Usuario ve cambios SIN cerrar sesión
```

---

## 🎯 Ventajas del Sistema

✅ **Actualización automática**: Sin recargar página ni cerrar sesión
✅ **Detección de desactivación**: Si admin desactiva usuario, se cierra sesión automáticamente
✅ **Eficiente**: Solo 1 request cada 30 segundos
✅ **Tiempo real**: Máximo 30 segundos de retraso
✅ **Sincronización**: Todos los componentes se actualizan a la vez
✅ **Seguro**: Siempre verifica contra base de datos, no confía en token antiguo

---

## 🛠️ Troubleshooting

### Problema: Los permisos no se actualizan
**Causa**: Polling no iniciado
**Solución**: Verificar que `authService.startPermissionPolling()` se llama después del login

### Problema: Muchas peticiones al servidor
**Causa**: Intervalo muy corto
**Solución**: Aumentar `REFRESH_INTERVAL` a 60000 (1 minuto)

### Problema: Usuario sigue viendo permisos antiguos
**Causa**: Frontend no escucha evento `permissions-updated`
**Solución**: Agregar listener:
```javascript
window.addEventListener('permissions-updated', function(event) {
    actualizarInterfaz(event.detail);
});
```

---

## 📞 Testing

Prueba el sistema con estos pasos:

```javascript
// 1. En consola del navegador después de login
authService.getCurrentUser(); // Ver permisos actuales

// 2. Admin quita permisos desde otro navegador/pestaña

// 3. Esperar máximo 30 segundos

// 4. Verificar en consola
authService.getCurrentUser(); // Debe mostrar permisos actualizados

// 5. Verificar que menú/botones se ocultan automáticamente
```

---

**Última actualización**: 27 de enero de 2026
**Versión**: 1.0.0
