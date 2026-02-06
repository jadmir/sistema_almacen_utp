# 🔐 SISTEMA DE ASIGNACIÓN DE PERMISOS A USUARIOS

## 📝 Descripción

Sistema mejorado para gestionar permisos de usuarios que muestra **todos los permisos disponibles** y marca automáticamente los que el usuario ya tiene (tanto del rol como individuales).

---

## 🎯 Problema Resuelto

**Antes**: 
- Checkboxes desmarcados aunque el usuario tuviera permisos
- No se podían quitar permisos del rol

**Ahora**: 
- ✅ Los checkboxes se marcan automáticamente
- ✅ Permisos del **rol** se pueden **revocar** para usuarios específicos
- ✅ Permisos **individuales/personalizados** se pueden agregar y quitar

---

## 🔧 API Endpoints

### 1. Obtener Permisos de un Usuario

**Endpoint**: `GET /api/usuarios/{id}/permisos`

**Headers**:
```
Authorization: Bearer {token}
```

**Response (200)**:
```json
{
    "data": {
        "usuario": {
            "id": 5,
            "nombre": "María García López",
            "email": "maria@utp.edu.pe"
        },
        "rol": {
            "id": 2,
            "nombre": "Asistente"
        },
        "permisos_disponibles": [
            {
                "id": 1,
                "nombre": "Ver Inventario",
                "slug": "inventario.ver",
                "descripcion": "Ver productos del inventario",
                "tiene_individual": false,
                "tiene_por_rol": true,
                "esta_revocado": false,
                "tiene_permiso": true
            },
            {
                "id": 2,
                "nombre": "Crear Productos",
                "slug": "inventario.crear",
                "descripcion": "Crear productos, secciones y tipos",
                "tiene_individual": true,
                "tiene_por_rol": false,
                "esta_revocado": false,
                "tiene_permiso": true
            },
            {
                "id": 3,
                "nombre": "Editar Productos",
                "slug": "inventario.editar",
                "descripcion": "Editar información de productos",
                "tiene_individual": false,
                "tiene_por_rol": true,
                "esta_revocado": true,
                "tiene_permiso": false
            }
        ],
        "permisos_del_rol": [...],
        "permisos_individuales": [...],
        "permisos_revocados": [3],
        "permisos_totales": [...]
    }
}
```

**Campos importantes en `permisos_disponibles`**:
- `tiene_individual`: **true** si el permiso está asignado directamente al usuario
- `tiene_por_rol`: **true** si el permiso viene del rol del usuario
- `esta_revocado`: **true** si el permiso del rol fue **revocado** para este usuario
- `tiene_permiso`: **true** si tiene el permiso (considerando revocaciones)

---

### 2. Asignar Permisos Individuales y Revocar del Rol

**Endpoint**: `POST /api/usuarios/{id}/permisos`

**Request Body - Asignar permisos adicionales y revocar del rol**:
```json
{
    "permission_ids": [2, 3],
    "revoked_permission_ids": [1, 5]
}
```

**Explicación**:
- `permission_ids`: Permisos **adicionales** (además de los del rol)
- `revoked_permission_ids`: Permisos del rol que se **revocan** para este usuario

**Request Body - Solo revocar permisos del rol**:
```json
{
    "permission_ids": [],
    "revoked_permission_ids": [1]
}
```

**Request Body - Remover todo (permisos adicionales y revocaciones)**:
```json
{
    "remove_all": true
}
```

**Response (200)**:
```json
{
    "message": "Permisos asignados exitosamente",
    "data": {
        "usuario": {...},
        "permisos_totales": [...]
    }
}
```

---

## 🖥️ Implementación Frontend

### 1. HTML - Pantalla de Permisos

```html
<!DOCTYPE html>
<html>
<head>
    <title>Gestionar Permisos - Usuario</title>
    <style>
        .permiso-item {
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        
        .permiso-checkbox {
            margin-right: 10px;
        }
        
        .permiso-del-rol {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
        }
        
        .permiso-individual {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
        }
        
        .permiso-sin-asignar {
            background-color: #f5f5f5;
        }
        
        .permiso-revocado {
            background-color: #ffebee;
            border-left: 4px solid #f44336;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .badge-rol {
            background-color: #2196F3;
            color: white;
        }
        
        .badge-individual {
            background-color: #ff9800;
            color: white;
        }
        
        .badge-revocado {
            background-color: #f44336;
            color: white;
        }
        
        .permiso-descripcion {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .permiso-disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestionar Permisos</h1>
        
        <div id="infoUsuario">
            <h3 id="nombreUsuario"></h3>
            <p>Rol: <span id="rolUsuario"></span></p>
        </div>
        
        <div id="seccionPermisos">
            <h3>Permisos Disponibles</h3>
            <button id="btnMarcarTodos">Marcar Todos</button>
            <button id="btnDesmarcarTodos">Desmarcar Todos</button>
            <button id="btnGuardar">Guardar Cambios</button>
            
            <div id="listaPermisos">
                <!-- Los permisos se cargarán aquí dinámicamente -->
            </div>
        </div>
    </div>

    <script src="permisos-usuario.js"></script>
</body>
</html>
```

---

### 2. JavaScript - Cargar y Mostrar Permisos

```javascript
// permisos-usuario.js

const PermisosUsuario = {
    usuarioId: null,
    permisosOriginales: [],
    
    /**
     * Inicializar la página
     */
    async init(userId) {
        this.usuarioId = userId;
        await this.cargarPermisos();
        this.configurarEventos();
    },
    
    /**
     * Cargar permisos del usuario
     */
    async cargarPermisos() {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`/api/usuarios/${this.usuarioId}/permisos`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            
            const data = await response.json();
            
            if (response.ok) {
                this.renderizarInformacion(data.data);
                this.renderizarPermisos(data.data.permisos_disponibles);
                this.permisosOriginales = data.data.permisos_individuales.map(p => p.id);
            } else {
                alert('Error al cargar permisos');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de conexión');
        }
    },
    
    /**
     * Mostrar información del usuario
     */
    renderizarInformacion(data) {
        document.getElementById('nombreUsuario').textContent = data.usuario.nombre;
        document.getElementById('rolUsuario').textContent = data.rol.nombre;
    },
    
    /**
     * Renderizar lista de permisos con checkboxes
     */
    renderizarPermisos(permisos) {
        const container = document.getElementById('listaPermisos');
        container.innerHTML = '';
        
        permisos.forEach(permiso => {
            const permisoDiv = document.createElement('div');
            
            // Determinar clase CSS según tipo de permiso
            let clasePermiso = 'permiso-item permiso-sin-asignar';
            if (permiso.esta_revocado) {
                clasePermiso = 'permiso-item permiso-revocado';
            } else if (permiso.tiene_por_rol && permiso.tiene_permiso) {
                clasePermiso = 'permiso-item permiso-del-rol';
            } else if (permiso.tiene_individual) {
                clasePermiso = 'permiso-item permiso-individual';
            }
            
            permisoDiv.className = clasePermiso;
            
            // Crear checkbox
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'permiso-checkbox';
            checkbox.value = permiso.id;
            checkbox.id = `permiso-${permiso.id}`;
            checkbox.dataset.tieneDelRol = permiso.tiene_por_rol;
            checkbox.dataset.estaRevocado = permiso.esta_revocado;
            
            // Marcar si tiene el permiso (considerando revocaciones)
            checkbox.checked = permiso.tiene_permiso;
            
            // Crear label
            const label = document.createElement('label');
            label.htmlFor = `permiso-${permiso.id}`;
            label.innerHTML = `
                <strong>${permiso.nombre}</strong>
                ${permiso.tiene_por_rol ? '<span class="badge badge-rol">Del Rol</span>' : ''}
                ${permiso.tiene_individual ? '<span class="badge badge-individual">Personalizado</span>' : ''}
                ${permiso.esta_revocado ? '<span class="badge badge-revocado">Revocado</span>' : ''}
            `;
            
            // Crear descripción
            const descripcion = document.createElement('div');
            descripcion.className = 'permiso-descripcion';
            descripcion.textContent = permiso.descripcion;
            
            // Agregar elementos al div
            permisoDiv.appendChild(checkbox);
            permisoDiv.appendChild(label);
            permisoDiv.appendChild(descripcion);
            
            container.appendChild(permisoDiv);
        });
    },
    
    /**
     * Configurar eventos de botones
     */
    configurarEventos() {
        // Botón Guardar
        document.getElementById('btnGuardar').addEventListener('click', () => {
            this.guardarPermisos();
        });
        
        // Botón Marcar Todos
        document.getElementById('btnMarcarTodos').addEventListener('click', () => {
            this.marcarTodos(true);
        });
        
        // Botón Desmarcar Todos
        document.getElementById('btnDesmarcarTodos').addEventListener('click', () => {
            this.marcarTodos(false);
        });
    },
    
    /**
     * Marcar o desmarcar todos los permisos editables
     */
    marcarTodos(marcar) {
        const checkboxes = document.querySelectorAll('.permiso-checkbox:not([disabled])');
        checkboxes.forEach(checkbox => {
            checkbox.checked = marcar;
        });
    },
    
    /**
     * Guardar permisos individuales del usuario
     */
    async guardarPermisos() {
        try {
            const checkboxes = document.querySelectorAll('.permiso-checkbox');
            const permisosAdicionales = [];
            const permisosRevocados = [];
            
            checkboxes.forEach(checkbox => {
                const permisoId = parseInt(checkbox.value);
                const tieneDelRol = checkbox.dataset.tieneDelRol === 'true';
                const estaChecked = checkbox.checked;
                
                if (tieneDelRol) {
                    // Si viene del rol y NO está marcado, está REVOCADO
                    if (!estaChecked) {
                        permisosRevocados.push(permisoId);
                    }
                } else {
                    // Si NO viene del rol y SÍ está marcado, es ADICIONAL
                    if (estaChecked) {
                        permisosAdicionales.push(permisoId);
                    }
                }
            });
            
            const token = localStorage.getItem('token');
            
            // Enviar ambos arrays
            const body = {
                permission_ids: permisosAdicionales,
                revoked_permission_ids: permisosRevocados
            };
            
            const response = await fetch(`/api/usuarios/${this.usuarioId}/permisos`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(body)
            });
            
            const data = await response.json();
            
            if (response.ok) {
                alert('Permisos actualizados exitosamente');
                // Recargar permisos para actualizar vista
                await this.cargarPermisos();
            } else {
                alert(data.message || 'Error al guardar permisos');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al guardar permisos');
        }
    }
};

// Inicializar cuando carga la página
document.addEventListener('DOMContentLoaded', function() {
    // Obtener ID del usuario desde URL o variable
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');
    
    if (userId) {
        PermisosUsuario.init(userId);
    } else {
        alert('ID de usuario no especificado');
    }
});
```

---

### 3. Versión Simplificada (Solo Checkboxes)

```html
<!-- Versión más simple sin estilos complejos -->
<div id="permisosContainer"></div>
<button id="btnGuardar">Guardar Permisos</button>

<script>
async function cargarPermisos(userId) {
    const response = await fetch(`/api/usuarios/${userId}/permisos`, {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
    });
    
    const { data } = await response.json();
    const container = document.getElementById('permisosContainer');
    
    data.permisos_disponibles.forEach(permiso => {
        const label = document.createElement('label');
        label.innerHTML = `
            <input 
                type="checkbox" 
                name="permisos" 
                value="${permiso.id}"
                ${permiso.tiene_permiso ? 'checked' : ''}
                ${permiso.tiene_por_rol ? 'disabled' : ''}
            >
            ${permiso.nombre}
            ${permiso.tiene_por_rol ? '(Del Rol)' : ''}
        `;
        container.appendChild(label);
        container.appendChild(document.createElement('br'));
    });
}

async function guardarPermisos(userId) {
    const checkboxes = document.querySelectorAll('input[name="permisos"]:not([disabled]):checked');
    const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    await fetch(`/api/usuarios/${userId}/permisos`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ permission_ids: ids })
    });
    
    alert('Permisos guardados');
}

// Uso
cargarPermisos(5); // ID del usuario
document.getElementById('btnGuardar').onclick = () => guardarPermisos(5);
</script>
```

---

## 🎨 Interpretación de Colores

En la versión completa:

| Color | Significa |
|-------|-----------|
| 🔵 Azul | Permiso del **rol** (activo) |
| 🟠 Naranja | Permiso **personalizado/adicional** |
| 🔴 Rojo | Permiso del rol **revocado** para este usuario |
| ⚪ Gris | Permiso **no asignado** |

---

## 🔄 Flujo Completo

```
1. ADMIN ABRE PANTALLA DE PERMISOS
   ↓
   GET /api/usuarios/{id}/permisos
   ↓
   Backend devuelve:
   - Todos los permisos disponibles
   - Flags: tiene_individual, tiene_por_rol, esta_revocado, tiene_permiso
   ↓
   Frontend renderiza checkboxes:
   - Marcados: tiene_permiso = true
   - Desmarcados: tiene_permiso = false

2. ADMIN MARCA/DESMARCA PERMISOS
   ↓
   Puede cambiar TODOS los permisos:
   - Desmarcar permisos del rol → Se revocan
   - Marcar permisos que no tiene → Se agregan como adicionales
   ↓
   Los checkboxes ya no están deshabilitados

3. ADMIN GUARDA CAMBIOS
   ↓
   POST /api/usuarios/{id}/permisos
   {
       permission_ids: [2, 3],          // Permisos adicionales
       revoked_permission_ids: [1]      // Permisos del rol revocados
   }
   ↓
   Backend actualiza:
   - Permisos adicionales en tabla pivot user_permission
   - Permisos revocados en columna revoked_permissions
   ↓
   Frontend recarga y muestra cambios
```

## 📊 Ejemplo Práctico

**Usuario con rol "Asistente"**

**Permisos del rol Asistente:**
- Ver Inventario (ID: 1)
- Generar Reportes (ID: 5)

**Estado inicial (sin modificaciones):**
- ✅ Ver Inventario (del rol)
- ✅ Generar Reportes (del rol)

**Admin desmarca "Ver Inventario" y marca "Eliminar Productos":**

**Request enviado:**
```json
{
    "permission_ids": [6],           // ID de "Eliminar Productos"
    "revoked_permission_ids": [1]    // ID de "Ver Inventario"
}
```

**Estado final:**
- ❌ Ver Inventario (revocado)
- ✅ Generar Reportes (del rol)
- ✅ Eliminar Productos (adicional)

---

## ✅ Ventajas del Nuevo Sistema

✅ **Visual**: Checkboxes marcados desde el inicio  
✅ **Flexible**: Permite revocar permisos del rol para usuarios específicos  
✅ **Claro**: Distingue entre permisos del rol, adicionales y revocados  
✅ **Granular**: Control total sobre permisos individuales  
✅ **Completo**: Muestra TODOS los permisos disponibles  
✅ **Intuitivo**: Colores y badges indican el origen y estado del permiso

---

## 🧪 Testing

```javascript
// 1. Cargar permisos
await fetch('/api/usuarios/5/permisos', {
    headers: { 'Authorization': 'Bearer TOKEN' }
});

// Verificar en respuesta:
// - permisos_disponibles: array con TODOS los permisos
// - Cada permiso tiene: tiene_individual, tiene_por_rol, tiene_permiso

// 2. Asignar permiso nuevo
await fetch('/api/usuarios/5/permisos', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer TOKEN',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({ permission_ids: [2, 3] })
});

// 3. Recargar y verificar que checkboxes están marcados
```

---

## 🛠️ Troubleshooting

### Problema: Checkboxes no se marcan
**Causa**: Frontend no usa el campo `tiene_permiso`
**Solución**: 
```javascript
checkbox.checked = permiso.tiene_permiso;
```

### Problema: Puedo desmarcar permisos del rol
**Causa**: No se desactiva el checkbox cuando `tiene_por_rol = true`
**Solución**:
```javascript
if (permiso.tiene_por_rol) {
    checkbox.disabled = true;
}
```

### Problema: No muestra todos los permisos
**Causa**: Solo muestra los que tiene el usuario
**Solución**: Usar `permisos_disponibles` que incluye TODOS los permisos del sistema

---

**Última actualización**: 27 de enero de 2026
**Versión**: 1.0.0
