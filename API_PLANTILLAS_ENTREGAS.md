# API - Plantillas de Entregas Mensuales

Sistema para gestionar entregas recurrentes de productos a áreas específicas con cantidades predefinidas.

## 📋 Casos de uso

- **Entregas mensuales**: El mismo conjunto de productos se entrega cada mes a un área específica
- **Entregas programadas**: Productos que siempre van a las mismas áreas con las mismas cantidades
- **Ahorro de tiempo**: En lugar de hacer salidas individuales, se ejecuta una plantilla en un click

---

## 📦 Listar Plantillas

Obtiene todas las plantillas de entregas registradas.

### Endpoint
```
GET /api/plantillas-entregas
```

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Permisos requeridos
- `inventario.ver`

### Query Parameters (opcionales)
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `activa` | boolean | Filtrar por estado (true/false) |
| `area_id` | integer | Filtrar por área |

### Ejemplos de URL
```
GET /api/plantillas-entregas
GET /api/plantillas-entregas?activa=true
GET /api/plantillas-entregas?area_id=3
```

### Response Success (200)
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "nombre": "Entrega mensual área administrativa",
      "descripcion": "Artículos de oficina mensuales",
      "area_id": 3,
      "motivo": "Entrega mensual programada",
      "activa": true,
      "created_by": 1,
      "created_at": "2026-02-05T10:00:00.000000Z",
      "updated_at": "2026-02-05T10:00:00.000000Z",
      "area": {
        "id": 3,
        "nombre": "Área Administrativa",
        "codigo": "ADMIN"
      },
      "creador": {
        "id": 1,
        "nombre": "Juan Pérez"
      },
      "detalles": [
        {
          "id": 1,
          "plantilla_id": 1,
          "product_id": 1,
          "cantidad": 50,
          "observaciones": "Lapiceros azules",
          "producto": {
            "id": 1,
            "codigo": "ASSOF-0001",
            "nombre": "Lapicero azul"
          }
        },
        {
          "id": 2,
          "plantilla_id": 1,
          "product_id": 2,
          "cantidad": 30,
          "observaciones": "Cuadernos A4",
          "producto": {
            "id": 2,
            "codigo": "ASSOF-0002",
            "nombre": "Cuaderno A4"
          }
        }
      ]
    }
  ]
}
```

---

## ➕ Crear Plantilla

Crea una nueva plantilla de entrega con sus productos.

### Endpoint
```
POST /api/plantillas-entregas
```

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Permisos requeridos
- `inventario.crear`

### Request Body
```json
{
  "nombre": "Entrega mensual área administrativa",
  "descripcion": "Artículos de oficina mensuales",
  "area_id": 3,
  "motivo": "Entrega mensual programada",
  "productos": [
    {
      "product_id": 1,
      "cantidad": 50,
      "observaciones": "Lapiceros azules"
    },
    {
      "product_id": 2,
      "cantidad": 30,
      "observaciones": "Cuadernos A4"
    },
    {
      "product_id": 5,
      "cantidad": 10,
      "observaciones": "Alcohol en gel"
    }
  ]
}
```

### Validaciones
| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| `nombre` | string | ✅ | Máximo 255 caracteres |
| `descripcion` | string | ❌ | Opcional |
| `area_id` | integer | ✅ | Debe existir en la base de datos |
| `motivo` | string | ✅ | Máximo 255 caracteres |
| `productos` | array | ✅ | Mínimo 1 producto |
| `productos.*.product_id` | integer | ✅ | Debe existir en la base de datos |
| `productos.*.cantidad` | integer | ✅ | Mínimo 1 |
| `productos.*.observaciones` | string | ❌ | Opcional |

### Response Success (201)
```json
{
  "status": "success",
  "message": "Plantilla creada exitosamente",
  "data": {
    "id": 1,
    "nombre": "Entrega mensual área administrativa",
    "descripcion": "Artículos de oficina mensuales",
    "area_id": 3,
    "motivo": "Entrega mensual programada",
    "activa": true,
    "created_by": 1,
    "created_at": "2026-02-05T10:00:00.000000Z",
    "updated_at": "2026-02-05T10:00:00.000000Z",
    "area": {
      "id": 3,
      "nombre": "Área Administrativa",
      "codigo": "ADMIN",
      "descripcion": "Oficinas administrativas"
    },
    "detalles": [
      {
        "id": 1,
        "plantilla_id": 1,
        "product_id": 1,
        "cantidad": 50,
        "observaciones": "Lapiceros azules",
        "producto": {
          "id": 1,
          "codigo": "ASSOF-0001",
          "nombre": "Lapicero azul",
          "stock_actual": 200
        }
      },
      {
        "id": 2,
        "plantilla_id": 1,
        "product_id": 2,
        "cantidad": 30,
        "observaciones": "Cuadernos A4",
        "producto": {
          "id": 2,
          "codigo": "ASSOF-0002",
          "nombre": "Cuaderno A4",
          "stock_actual": 150
        }
      }
    ]
  }
}
```

### Response Error - Validación (422)
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "nombre": [
      "El campo nombre es obligatorio"
    ],
    "area_id": [
      "El área seleccionada no existe"
    ],
    "productos": [
      "Debe incluir al menos un producto"
    ]
  }
}
```

---

## 🔍 Ver Plantilla

Obtiene los detalles de una plantilla específica.

### Endpoint
```
GET /api/plantillas-entregas/{id}
```

### Headers
```
Authorization: Bearer {token}
```

### Permisos requeridos
- `inventario.ver`

### Response Success (200)
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "nombre": "Entrega mensual área administrativa",
    "descripcion": "Artículos de oficina mensuales",
    "area_id": 3,
    "motivo": "Entrega mensual programada",
    "activa": true,
    "created_by": 1,
    "created_at": "2026-02-05T10:00:00.000000Z",
    "updated_at": "2026-02-05T10:00:00.000000Z",
    "area": {
      "id": 3,
      "nombre": "Área Administrativa",
      "codigo": "ADMIN"
    },
    "creador": {
      "id": 1,
      "nombre": "Juan Pérez"
    },
    "detalles": [
      {
        "id": 1,
        "plantilla_id": 1,
        "product_id": 1,
        "cantidad": 50,
        "observaciones": "Lapiceros azules",
        "producto": {
          "id": 1,
          "codigo": "ASSOF-0001",
          "nombre": "Lapicero azul",
          "stock_actual": 200
        }
      }
    ]
  }
}
```

### Response Error (404)
```json
{
  "status": "error",
  "message": "Plantilla no encontrada"
}
```

---

## ✏️ Actualizar Plantilla

Actualiza los datos de una plantilla existente.

### Endpoint
```
PUT /api/plantillas-entregas/{id}
```

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Permisos requeridos
- `inventario.editar`

### Request Body
```json
{
  "nombre": "Entrega mensual administrativa actualizada",
  "descripcion": "Descripción actualizada",
  "area_id": 3,
  "motivo": "Entrega mensual",
  "activa": true,
  "productos": [
    {
      "product_id": 1,
      "cantidad": 60,
      "observaciones": "Cantidad actualizada"
    },
    {
      "product_id": 2,
      "cantidad": 40,
      "observaciones": null
    }
  ]
}
```

### Notas importantes
- Todos los campos son opcionales (puedes enviar solo los que quieres actualizar)
- Si envías `productos`, se reemplazan TODOS los productos de la plantilla
- Para desactivar una plantilla, envía `"activa": false`

### Response Success (200)
```json
{
  "status": "success",
  "message": "Plantilla actualizada exitosamente",
  "data": {
    "id": 1,
    "nombre": "Entrega mensual administrativa actualizada",
    "descripcion": "Descripción actualizada",
    "area_id": 3,
    "motivo": "Entrega mensual",
    "activa": true,
    "created_by": 1,
    "created_at": "2026-02-05T10:00:00.000000Z",
    "updated_at": "2026-02-05T11:30:00.000000Z",
    "area": {
      "id": 3,
      "nombre": "Área Administrativa",
      "codigo": "ADMIN"
    },
    "detalles": [
      {
        "id": 3,
        "plantilla_id": 1,
        "product_id": 1,
        "cantidad": 60,
        "observaciones": "Cantidad actualizada",
        "producto": {
          "id": 1,
          "codigo": "ASSOF-0001",
          "nombre": "Lapicero azul"
        }
      }
    ]
  }
}
```

---

## 🗑️ Eliminar Plantilla

Elimina una plantilla de entregas.

### Endpoint
```
DELETE /api/plantillas-entregas/{id}
```

### Headers
```
Authorization: Bearer {token}
```

### Permisos requeridos
- `inventario.eliminar`

### Response Success (200)
```json
{
  "status": "success",
  "message": "Plantilla eliminada exitosamente"
}
```

---

## ▶️ Ejecutar Plantilla (Lo más importante)

Ejecuta la plantilla y realiza todas las salidas de productos automáticamente.

### Endpoint
```
POST /api/plantillas-entregas/{id}/ejecutar
```

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Permisos requeridos
- `inventario.salida`

### Request Body
```json
{
  "fecha_movimiento": "2026-02-05",
  "observaciones_generales": "Entrega correspondiente a febrero 2026"
}
```

### Validaciones
| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| `fecha_movimiento` | date | ✅ | Formato: YYYY-MM-DD, no puede ser futura |
| `observaciones_generales` | string | ❌ | Se añade a las observaciones de cada producto |

### Response Success (200)
```json
{
  "status": "success",
  "message": "Plantilla ejecutada exitosamente",
  "plantilla": {
    "id": 1,
    "nombre": "Entrega mensual área administrativa",
    "area": "Área Administrativa"
  },
  "data": [
    {
      "product_id": 1,
      "codigo": "ASSOF-0001",
      "nombre": "Lapicero azul",
      "stock_anterior": 200,
      "cantidad_retirada": 50,
      "stock_actual": 150,
      "alerta_stock_bajo": false,
      "success": true
    },
    {
      "product_id": 2,
      "codigo": "ASSOF-0002",
      "nombre": "Cuaderno A4",
      "stock_anterior": 80,
      "cantidad_retirada": 30,
      "stock_actual": 50,
      "alerta_stock_bajo": true,
      "success": true
    }
  ],
  "total_procesados": 2,
  "alertas_stock_bajo": [
    {
      "product_id": 2,
      "codigo": "ASSOF-0002",
      "nombre": "Cuaderno A4",
      "stock_actual": 50,
      "stock_minimo": 60
    }
  ]
}
```

### Response Error - Plantilla Inactiva (400)
```json
{
  "status": "error",
  "message": "La plantilla está inactiva"
}
```

### Response Error - Stock Insuficiente (400)
```json
{
  "status": "error",
  "message": "Stock insuficiente en algunos productos",
  "errores": [
    {
      "product_id": 1,
      "codigo": "ASSOF-0001",
      "nombre": "Lapicero azul",
      "stock_actual": 10,
      "cantidad_solicitada": 50,
      "error": "Stock insuficiente"
    }
  ]
}
```

### Notas importantes sobre la ejecución
- ✅ **Transaccional**: Si falla algún producto, no se procesa nada
- ✅ **Validación previa**: Verifica stock de TODOS los productos antes de ejecutar
- ✅ **Historial completo**: Cada salida queda registrada en movimientos
- ✅ **Alertas automáticas**: Notifica productos que quedan con stock bajo
- ✅ **Trazabilidad**: El motivo incluye el nombre de la plantilla
- ✅ **Usuario registrado**: Queda el registro de quién ejecutó la plantilla

---

## ⚠️ Códigos de Error

| Código | Descripción |
|--------|-------------|
| 200 | Operación exitosa |
| 201 | Plantilla creada |
| 400 | Error de procesamiento (plantilla inactiva o stock insuficiente) |
| 401 | No autenticado |
| 403 | Sin permisos |
| 404 | Plantilla no encontrada |
| 422 | Error de validación |
| 500 | Error interno del servidor |

---

## 💡 Flujo de trabajo recomendado

### 1️⃣ Creación inicial (una sola vez)

El usuario crea la plantilla con:
- Nombre descriptivo (ej: "Entrega mensual enfermería")
- Área de destino
- Lista de productos y cantidades fijas

### 2️⃣ Ejecución mensual (cada mes)

Cada mes, el usuario solo:
1. Busca la plantilla
2. Click en "Ejecutar"
3. Ingresa la fecha
4. Confirma

### 3️⃣ Mantenimiento

Cuando cambien las cantidades o productos:
- Edita la plantilla
- Las próximas ejecuciones usarán los nuevos valores

---

## 📊 Ejemplo de integración con Axios

### Listar plantillas activas
```javascript
const obtenerPlantillas = async () => {
  try {
    const response = await axios.get('/api/plantillas-entregas', {
      params: { activa: true },
      headers: { 'Authorization': `Bearer ${token}` }
    });
    return response.data.data;
  } catch (error) {
    console.error('Error:', error.response?.data);
    throw error;
  }
};
```

### Crear plantilla
```javascript
const crearPlantilla = async (plantillaData) => {
  try {
    const response = await axios.post('/api/plantillas-entregas', {
      nombre: plantillaData.nombre,
      descripcion: plantillaData.descripcion,
      area_id: plantillaData.areaId,
      motivo: plantillaData.motivo,
      productos: plantillaData.productos.map(p => ({
        product_id: p.id,
        cantidad: p.cantidad,
        observaciones: p.observaciones || null
      }))
    }, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    
    console.log('Plantilla creada:', response.data);
    return response.data.data;
  } catch (error) {
    if (error.response?.status === 422) {
      console.error('Errores de validación:', error.response.data.errors);
    }
    throw error;
  }
};
```

### Ejecutar plantilla
```javascript
const ejecutarPlantilla = async (plantillaId, fecha, observaciones) => {
  try {
    const response = await axios.post(
      `/api/plantillas-entregas/${plantillaId}/ejecutar`,
      {
        fecha_movimiento: fecha,
        observaciones_generales: observaciones || null
      },
      {
        headers: { 'Authorization': `Bearer ${token}` }
      }
    );
    
    // Verificar alertas de stock bajo
    if (response.data.alertas_stock_bajo.length > 0) {
      console.warn('⚠️ Productos con stock bajo:', response.data.alertas_stock_bajo);
      // Mostrar alerta al usuario
    }
    
    console.log('✅ Plantilla ejecutada:', response.data);
    return response.data;
    
  } catch (error) {
    if (error.response?.status === 400) {
      if (error.response.data.message.includes('inactiva')) {
        console.error('❌ La plantilla está desactivada');
      } else if (error.response.data.errores) {
        console.error('❌ Stock insuficiente:', error.response.data.errores);
        // Mostrar qué productos no tienen stock
      }
    }
    throw error;
  }
};
```

### Actualizar plantilla
```javascript
const actualizarPlantilla = async (plantillaId, cambios) => {
  try {
    const response = await axios.put(
      `/api/plantillas-entregas/${plantillaId}`,
      cambios,
      {
        headers: { 'Authorization': `Bearer ${token}` }
      }
    );
    return response.data.data;
  } catch (error) {
    console.error('Error al actualizar:', error.response?.data);
    throw error;
  }
};
```

---

## 🎯 Componentes sugeridos para el Frontend

### 1. Lista de Plantillas
- Mostrar todas las plantillas
- Filtro por área
- Filtro activo/inactivo
- Botón "Ejecutar" en cada plantilla

### 2. Detalle de Plantilla
- Ver productos y cantidades
- Stock actual de cada producto
- Historial de ejecuciones (opcional)

### 3. Crear/Editar Plantilla
- Formulario con nombre, descripción, área
- Selector de productos con cantidades
- Validar que haya al menos un producto

### 4. Ejecutar Plantilla (Modal)
- Mostrar resumen de la plantilla
- Selector de fecha
- Campo de observaciones opcionales
- Validar stock antes de confirmar
- Mostrar alertas de stock bajo después

### 5. Confirmación de Ejecución
- Mostrar productos procesados
- Alertas de stock bajo
- Botón para ver movimientos generados

---

## 📝 Notas adicionales

### Diferencia con salidas manuales

| Salida Manual | Plantilla |
|---------------|-----------|
| Seleccionar productos uno por uno | Productos predefinidos |
| Ingresar cantidades cada vez | Cantidades fijas |
| Múltiples pasos | Un solo click |
| Propenso a errores | Consistente |

### Seguridad
- Solo usuarios con permiso `inventario.salida` pueden ejecutar plantillas
- Se registra quién y cuándo ejecutó cada plantilla
- Las plantillas inactivas no se pueden ejecutar

### Recomendaciones
- Usar nombres descriptivos para las plantillas
- Revisar stock antes de ejecutar (aunque el sistema valida)
- Actualizar plantillas cuando cambien necesidades
- Desactivar plantillas obsoletas en lugar de eliminarlas
