# API de Depósitos

Sistema de gestión de depósitos (ubicaciones de almacenamiento) para productos.

---

## 📋 Tabla de Contenidos

- [Características](#características)
- [Endpoints Disponibles](#endpoints-disponibles)
- [Ejemplos de Uso](#ejemplos-de-uso)

---

## ✨ Características

- Gestión CRUD completa de depósitos
- Solo requiere campo **nombre** (simplificado)
- Estado activo/inactivo
- Validación de integridad (no se puede eliminar si tiene productos)
- Listado de depósitos activos para selects/dropdowns

---

## 🔌 Endpoints Disponibles

### 1. Listar Todos los Depósitos
```http
GET /api/depositos
```

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters (opcionales):**
- `activo` (boolean): Filtrar por estado activo/inactivo

**Respuesta Exitosa (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "nombre": "DEPOSITO - OPE - AZOTEA",
      "activo": true,
      "created_at": "2026-02-10T00:00:00.000000Z",
      "updated_at": "2026-02-10T00:00:00.000000Z",
      "productos_count": 15
    },
    {
      "id": 2,
      "nombre": "DEPOSITO - MNTO - AZOTEA",
      "activo": true,
      "created_at": "2026-02-10T00:00:00.000000Z",
      "updated_at": "2026-02-10T00:00:00.000000Z",
      "productos_count": 8
    }
  ]
}
```

---

### 2. Listar Depósitos Activos (Para Selects)
```http
GET /api/depositos/activos
```

**Headers:**
```
Authorization: Bearer {token}
```

**Uso:** Ideal para poblar dropdowns/selects de depósitos en formularios.

**Respuesta Exitosa (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "nombre": "DEPOSITO - OPE - AZOTEA"
    },
    {
      "id": 2,
      "nombre": "DEPOSITO - MNTO - AZOTEA"
    },
    {
      "id": 3,
      "nombre": "DEPOSITO 2 - OPE - PRIMER PISO"
    }
  ]
}
```

---

### 3. Crear Depósito
```http
POST /api/depositos
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
  "nombre": "DEPOSITO 5 - SSUU - SOTANO 1",
  "activo": true
}
```

**Validaciones:**
- `nombre`: **requerido**, string, máximo 255 caracteres
- `activo`: opcional, boolean (default: true)

**Respuesta Exitosa (201):**
```json
{
  "status": "success",
  "message": "Depósito creado exitosamente",
  "data": {
    "id": 11,
    "nombre": "DEPOSITO 5 - SSUU - SOTANO 1",
    "activo": true,
    "created_at": "2026-02-10T15:30:00.000000Z",
    "updated_at": "2026-02-10T15:30:00.000000Z"
  }
}
```

**Respuesta de Error (422):**
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "nombre": [
      "El campo nombre es obligatorio."
    ]
  }
}
```

---

### 4. Ver Detalle de un Depósito
```http
GET /api/depositos/{id}
```

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "nombre": "DEPOSITO - OPE - AZOTEA",
    "activo": true,
    "created_at": "2026-02-10T00:00:00.000000Z",
    "updated_at": "2026-02-10T00:00:00.000000Z",
    "productos_count": 15
  }
}
```

**Respuesta de Error (404):**
```json
{
  "status": "error",
  "message": "Depósito no encontrado"
}
```

---

### 5. Actualizar Depósito
```http
PUT /api/depositos/{id}
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
  "nombre": "DEPOSITO - OPE - AZOTEA (Actualizado)",
  "activo": false
}
```

**Validaciones:**
- `nombre`: opcional, string, máximo 255 caracteres
- `activo`: opcional, boolean

**Respuesta Exitosa (200):**
```json
{
  "status": "success",
  "message": "Depósito actualizado exitosamente",
  "data": {
    "id": 1,
    "nombre": "DEPOSITO - OPE - AZOTEA (Actualizado)",
    "activo": false,
    "created_at": "2026-02-10T00:00:00.000000Z",
    "updated_at": "2026-02-10T15:45:00.000000Z"
  }
}
```

---

### 6. Eliminar Depósito
```http
DELETE /api/depositos/{id}
```

**Headers:**
```
Authorization: Bearer {token}
```

**⚠️ Importante:** No se puede eliminar un depósito si tiene productos asignados.

**Respuesta Exitosa (200):**
```json
{
  "status": "success",
  "message": "Depósito eliminado exitosamente"
}
```

**Respuesta de Error (400) - Tiene productos:**
```json
{
  "status": "error",
  "message": "No se puede eliminar el depósito porque tiene 15 productos asignados"
}
```

---

## 💻 Ejemplos de Uso

### Ejemplo 1: Cargar Depósitos Activos en un Select

**JavaScript/React:**
```javascript
// Obtener depósitos activos
const fetchDepositos = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/depositos/activos', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    const data = await response.json();
    
    // data.data contendrá [{id: 1, nombre: "DEPOSITO - OPE - AZOTEA"}, ...]
    setDepositos(data.data);
  } catch (error) {
    console.error('Error al cargar depósitos:', error);
  }
};

// Renderizar en un <select>
<select name="deposito_id" className="form-control">
  <option value="">Seleccione un depósito</option>
  {depositos.map(deposito => (
    <option key={deposito.id} value={deposito.id}>
      {deposito.nombre}
    </option>
  ))}
</select>
```

---

### Ejemplo 2: Crear un Nuevo Depósito

**JavaScript/React:**
```javascript
const crearDeposito = async (formData) => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/depositos', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        nombre: formData.nombre,
        activo: true
      })
    });
    
    const data = await response.json();
    
    if (data.status === 'success') {
      alert('Depósito creado exitosamente');
      // Recargar lista de depósitos
      fetchDepositos();
    } else {
      alert('Error al crear depósito');
    }
  } catch (error) {
    console.error('Error:', error);
  }
};
```

---

### Ejemplo 3: Agregar Depósito en Formulario de Producto

**Modificar el formulario de creación/edición de productos:**

```jsx
// 1. Cargar depósitos al montar el componente
useEffect(() => {
  fetchDepositos();
}, []);

// 2. Agregar campo en el formulario
<div className="form-group">
  <label>Depósito</label>
  <select 
    name="deposito_id" 
    value={formData.deposito_id || ''} 
    onChange={handleChange}
    className="form-control"
  >
    <option value="">Sin depósito</option>
    {depositos.map(deposito => (
      <option key={deposito.id} value={deposito.id}>
        {deposito.nombre}
      </option>
    ))}
  </select>
</div>

// 3. Enviar con los datos del producto
const crearProducto = async (formData) => {
  await fetch('http://127.0.0.1:8000/api/products', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      ...formData,
      deposito_id: formData.deposito_id || null  // Nullable
    })
  });
};
```

---

## 🔐 Permisos Requeridos

Todos los endpoints requieren los siguientes permisos:

- **Ver depósitos**: `inventario.ver`
- **Crear depósito**: `inventario.crear`
- **Editar depósito**: `inventario.editar`
- **Eliminar depósito**: `inventario.eliminar`

---

## 📝 Notas Importantes

1. **Campo nullable**: El campo `deposito_id` en productos es **nullable**, por lo que es opcional asignar un depósito.

2. **Integridad referencial**: No se puede eliminar un depósito si tiene productos asociados. Primero debes reasignar o eliminar los productos.

3. **Estado activo**: Los depósitos inactivos no aparecen en el endpoint `/depositos/activos` pero sí en `/depositos`.

4. **Formato de nombre**: Se recomienda usar el formato: `DEPOSITO - [ÁREA] - [UBICACIÓN]`
   - Ejemplo: `DEPOSITO - OPE - AZOTEA`
   - Ejemplo: `DEPOSITO 2 - VU - PRIMER PISO`
   - Ejemplo: `CABINA DE CONTROL - OPE - PRIMER PISO`

---

## 🐛 Manejo de Errores Comunes

| Código | Error | Solución |
|--------|-------|----------|
| 401 | No autorizado | Verificar token JWT |
| 404 | Depósito no encontrado | Verificar que el ID existe |
| 422 | Error de validación | Revisar campos requeridos |
| 400 | No se puede eliminar | El depósito tiene productos asignados |
| 500 | Error del servidor | Revisar logs de Laravel |

---

## 📦 Integración con Productos

Cuando consultas productos, el depósito viene incluido automáticamente:

```json
{
  "id": 123,
  "nombre": "Papel Bond A4",
  "stock_actual": 500,
  "section": {
    "id": 5,
    "nombre": "ASSAL"
  },
  "deposito": {
    "id": 1,
    "nombre": "DEPOSITO - OPE - AZOTEA"
  }
}
```

Si el producto no tiene depósito asignado, el campo será `null`:
```json
{
  "id": 124,
  "nombre": "Tinta para impresora",
  "deposito": null
}
```
