# API - Movimientos Masivos de Stock

## 📦 Entrada Masiva

Registra entradas de stock para múltiples productos en una sola operación.

### Endpoint
```
POST /api/products/entrada-masiva
```

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Permisos requeridos
- `inventario.entrada`

### Request Body
```json
{
  "productos": [
    {
      "product_id": 1,
      "cantidad": 50,
      "motivo": "Compra a proveedor",
      "observaciones": "Lote 2026-A",
      "fecha_movimiento": "2026-02-04"
    },
    {
      "product_id": 2,
      "cantidad": 30,
      "motivo": "Compra a proveedor",
      "observaciones": null,
      "fecha_movimiento": "2026-02-04"
    },
    {
      "product_id": 5,
      "cantidad": 100,
      "motivo": "Donación",
      "observaciones": "Donación Universidad",
      "fecha_movimiento": "2026-02-04"
    }
  ]
}
```

### Validaciones por producto
| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| `product_id` | integer | ✅ | Debe existir en la base de datos |
| `cantidad` | integer | ✅ | Mínimo 1 |
| `motivo` | string | ✅ | Máximo 255 caracteres |
| `observaciones` | string | ❌ | Opcional |
| `fecha_movimiento` | date | ✅ | Formato: YYYY-MM-DD, no puede ser futura |

### Response Success (200)
```json
{
  "status": "success",
  "message": "Entradas masivas registradas exitosamente",
  "data": [
    {
      "product_id": 1,
      "codigo": "ASSOF-0001",
      "nombre": "Lapicero azul",
      "stock_anterior": 100,
      "cantidad_ingresada": 50,
      "stock_actual": 150,
      "success": true
    },
    {
      "product_id": 2,
      "codigo": "ASSOF-0002",
      "nombre": "Cuaderno A4",
      "stock_anterior": 80,
      "cantidad_ingresada": 30,
      "stock_actual": 110,
      "success": true
    },
    {
      "product_id": 5,
      "codigo": "ASSAL-0003",
      "nombre": "Alcohol en gel",
      "stock_anterior": 50,
      "cantidad_ingresada": 100,
      "stock_actual": 150,
      "success": true
    }
  ],
  "total_procesados": 3
}
```

### Response Error - Validación (422)
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "productos.0.cantidad": [
      "El campo cantidad es obligatorio"
    ],
    "productos.1.product_id": [
      "El producto seleccionado no existe"
    ]
  }
}
```

### Response Error - Procesamiento (400)
```json
{
  "status": "error",
  "message": "Error al procesar algunos productos",
  "errores": [
    {
      "index": 0,
      "product_id": 999,
      "error": "Producto no encontrado"
    }
  ],
  "procesados": 2,
  "fallidos": 1
}
```

### Notas importantes
- ✅ **Transaccional**: Si falla algún producto, se revierten todos los cambios
- ✅ Se validan todos los productos antes de procesar
- ✅ Cada movimiento se registra en el historial
- ✅ El usuario autenticado queda registrado en cada movimiento

---

## 📤 Salida Masiva

Registra salidas de stock para múltiples productos en una sola operación.

### Endpoint
```
POST /api/products/salida-masiva
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
  "productos": [
    {
      "product_id": 1,
      "cantidad": 20,
      "area_id": 3,
      "motivo": "Entrega a área administrativa",
      "observaciones": "Pedido #2026-001",
      "fecha_movimiento": "2026-02-04"
    },
    {
      "product_id": 2,
      "cantidad": 15,
      "area_id": 3,
      "motivo": "Entrega a área administrativa",
      "observaciones": null,
      "fecha_movimiento": "2026-02-04"
    },
    {
      "product_id": 5,
      "cantidad": 10,
      "area_id": 5,
      "motivo": "Entrega a enfermería",
      "observaciones": "Urgente",
      "fecha_movimiento": "2026-02-04"
    }
  ]
}
```

### Validaciones por producto
| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| `product_id` | integer | ✅ | Debe existir en la base de datos |
| `cantidad` | integer | ✅ | Mínimo 1 |
| `area_id` | integer | ✅ | Debe existir en la base de datos |
| `motivo` | string | ✅ | Máximo 255 caracteres |
| `observaciones` | string | ❌ | Opcional |
| `fecha_movimiento` | date | ✅ | Formato: YYYY-MM-DD, no puede ser futura |

### Response Success (200)
```json
{
  "status": "success",
  "message": "Salidas masivas registradas exitosamente",
  "data": [
    {
      "product_id": 1,
      "codigo": "ASSOF-0001",
      "nombre": "Lapicero azul",
      "stock_anterior": 150,
      "cantidad_retirada": 20,
      "stock_actual": 130,
      "alerta_stock_bajo": false,
      "success": true
    },
    {
      "product_id": 2,
      "codigo": "ASSOF-0002",
      "nombre": "Cuaderno A4",
      "stock_anterior": 110,
      "cantidad_retirada": 15,
      "stock_actual": 95,
      "alerta_stock_bajo": false,
      "success": true
    },
    {
      "product_id": 5,
      "codigo": "ASSAL-0003",
      "nombre": "Alcohol en gel",
      "stock_anterior": 150,
      "cantidad_retirada": 10,
      "stock_actual": 140,
      "alerta_stock_bajo": false,
      "success": true
    }
  ],
  "total_procesados": 3,
  "alertas_stock_bajo": []
}
```

### Response Success con alertas de stock bajo
```json
{
  "status": "success",
  "message": "Salidas masivas registradas exitosamente",
  "data": [
    {
      "product_id": 1,
      "codigo": "ASSOF-0001",
      "nombre": "Lapicero azul",
      "stock_anterior": 60,
      "cantidad_retirada": 50,
      "stock_actual": 10,
      "alerta_stock_bajo": true,
      "success": true
    }
  ],
  "total_procesados": 1,
  "alertas_stock_bajo": [
    {
      "product_id": 1,
      "codigo": "ASSOF-0001",
      "nombre": "Lapicero azul",
      "stock_actual": 10,
      "stock_minimo": 50
    }
  ]
}
```

### Response Error - Stock Insuficiente (400)
```json
{
  "status": "error",
  "message": "Stock insuficiente en algunos productos",
  "errores": [
    {
      "index": 0,
      "product_id": 1,
      "codigo": "ASSOF-0001",
      "nombre": "Lapicero azul",
      "stock_actual": 10,
      "cantidad_solicitada": 50,
      "error": "Stock insuficiente"
    },
    {
      "index": 2,
      "product_id": 5,
      "codigo": "ASSAL-0003",
      "nombre": "Alcohol en gel",
      "stock_actual": 5,
      "cantidad_solicitada": 20,
      "error": "Stock insuficiente"
    }
  ]
}
```

### Response Error - Validación (422)
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "productos.0.area_id": [
      "El campo área es obligatorio"
    ],
    "productos.1.cantidad": [
      "La cantidad debe ser al menos 1"
    ]
  }
}
```

### Notas importantes
- ✅ **Transaccional**: Si falla algún producto, se revierten todos los cambios
- ✅ **Validación previa de stock**: Verifica que hay stock suficiente en TODOS los productos antes de procesar
- ✅ **Alertas automáticas**: Notifica cuando un producto queda con stock bajo después de la salida
- ✅ Cada movimiento se registra en el historial con el área de destino
- ✅ El usuario autenticado queda registrado en cada movimiento

---

## ⚠️ Códigos de Error

| Código | Descripción |
|--------|-------------|
| 200 | Operación exitosa |
| 400 | Error de procesamiento (stock insuficiente o error en algún producto) |
| 401 | No autenticado |
| 403 | Sin permisos |
| 422 | Error de validación |
| 500 | Error interno del servidor |

---

## 💡 Ejemplos de uso con Axios

### Entrada Masiva
```javascript
const registrarEntradaMasiva = async (productos) => {
  try {
    const response = await axios.post('/api/products/entrada-masiva', {
      productos: productos.map(p => ({
        product_id: p.id,
        cantidad: p.cantidad,
        motivo: p.motivo,
        observaciones: p.observaciones || null,
        fecha_movimiento: p.fecha || new Date().toISOString().split('T')[0]
      }))
    }, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    console.log('Entradas registradas:', response.data);
    return response.data;
  } catch (error) {
    console.error('Error:', error.response?.data);
    throw error;
  }
};
```

### Salida Masiva
```javascript
const registrarSalidaMasiva = async (productos, areaId) => {
  try {
    const response = await axios.post('/api/products/salida-masiva', {
      productos: productos.map(p => ({
        product_id: p.id,
        cantidad: p.cantidad,
        area_id: areaId,
        motivo: p.motivo,
        observaciones: p.observaciones || null,
        fecha_movimiento: p.fecha || new Date().toISOString().split('T')[0]
      }))
    }, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    // Verificar alertas de stock bajo
    if (response.data.alertas_stock_bajo.length > 0) {
      console.warn('Productos con stock bajo:', response.data.alertas_stock_bajo);
    }
    
    return response.data;
  } catch (error) {
    if (error.response?.status === 400) {
      console.error('Stock insuficiente:', error.response.data.errores);
    }
    throw error;
  }
};
```

---

## 📋 Flujo recomendado en el Frontend

### Para Entradas Masivas:
1. Usuario selecciona múltiples productos
2. Completa cantidad, motivo y observaciones para cada uno
3. Valida que todos los campos requeridos estén completos
4. Envía la petición
5. Muestra resumen de productos procesados
6. Actualiza el inventario local

### Para Salidas Masivas:
1. Usuario selecciona múltiples productos
2. Selecciona área de destino (puede ser la misma para todos)
3. Completa cantidad, motivo y observaciones para cada uno
4. **Valida disponibilidad de stock antes de enviar** (opcional, para mejor UX)
5. Envía la petición
6. Si hay errores de stock, muestra qué productos no tienen suficiente
7. Si hay alertas de stock bajo, notifica al usuario
8. Muestra resumen de productos procesados
9. Actualiza el inventario local
