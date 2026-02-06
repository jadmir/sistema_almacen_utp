# 📅 Sistema de Dos Fechas en Movimientos

## Información General

El sistema ahora requiere registrar **dos fechas diferentes** para cada movimiento de inventario:

1. **`fecha_movimiento`** - Fecha real cuando ocurrió el movimiento físico (OBLIGATORIO)
2. **`created_at`** - Fecha/hora cuando se registró en el sistema (AUTOMÁTICO)

Esto permite registrar movimientos que ocurrieron días atrás pero que se olvidaron de registrar en su momento.

---

## 🎯 Propósito de Cada Fecha

### `fecha_movimiento` (Campo Requerido)
**¿Qué representa?**
- Fecha real del movimiento físico del producto
- Cuando realmente llegó la mercadería (ENTRADA)
- Cuando realmente se entregó a un área (SALIDA)
- Cuando realmente se hizo el inventario físico (AJUSTE)

**Características:**
- **Obligatorio** - Debe enviarse en todas las peticiones
- **Formato:** YYYY-MM-DD (ej: 2026-01-24)
- **Restricción:** No puede ser fecha futura (máximo HOY)
- **Puede ser pasada:** Sí, permite registrar movimientos atrasados

### `created_at` (Campo Automático)
**¿Qué representa?**
- Fecha y hora exacta cuando el usuario registró la información en el sistema
- NO se envía en el request
- Laravel lo llena automáticamente

**Características:**
- **Automático** - No requiere acción del frontend
- **Formato:** YYYY-MM-DD HH:MM:SS (ej: 2026-01-27 10:30:15)
- **Inmutable** - Se establece una sola vez al crear el registro

---

## 📡 Cambios en los Endpoints

### 1. Registrar Entrada
```
POST /api/products/{id}/entrada
```

**Request (ACTUALIZADO):**
```json
{
  "cantidad": 100,
  "motivo": "Compra semanal proveedor ABC",
  "observaciones": "Llegó el viernes pero registré el lunes",
  "fecha_movimiento": "2026-01-24"
}
```

**Validaciones:**
- `cantidad`: required, integer, min:1
- `motivo`: required, string, max:255
- `observaciones`: nullable, string
- `fecha_movimiento`: **required**, date, before_or_equal:today ⭐ NUEVO OBLIGATORIO

**Response (200):**
```json
{
  "status": "success",
  "message": "Entrada de stock registrada exitosamente",
  "data": {
    "producto": { ... },
    "stock_anterior": 50,
    "stock_actual": 150,
    "cantidad_ingresada": 100
  }
}
```

---

### 2. Registrar Salida
```
POST /api/products/{id}/salida
```

**Request (ACTUALIZADO):**
```json
{
  "cantidad": 20,
  "motivo": "Entrega mensual a enfermería",
  "area_id": 2,
  "observaciones": "Entregado el jueves",
  "fecha_movimiento": "2026-01-23"
}
```

**Validaciones:**
- `cantidad`: required, integer, min:1
- `motivo`: required, string, max:255
- `area_id`: required, exists:areas,id
- `observaciones`: nullable, string
- `fecha_movimiento`: **required**, date, before_or_equal:today ⭐ NUEVO OBLIGATORIO

**Response (200):**
```json
{
  "status": "success",
  "message": "Salida de stock registrada exitosamente",
  "data": {
    "producto": { ... },
    "stock_anterior": 100,
    "stock_actual": 80,
    "cantidad_retirada": 20,
    "alerta_stock_bajo": false
  }
}
```

---

### 3. Registrar Ajuste
```
POST /api/products/{id}/ajuste
```

**Request (ACTUALIZADO):**
```json
{
  "stock_nuevo": 50,
  "motivo": "Inventario físico mensual",
  "observaciones": "Encontradas diferencias en conteo",
  "fecha_movimiento": "2026-01-25"
}
```

**Validaciones:**
- `stock_nuevo`: required, integer, min:0
- `motivo`: required, string, max:255
- `observaciones`: nullable, string
- `fecha_movimiento`: **required**, date, before_or_equal:today ⭐ NUEVO OBLIGATORIO

**Response (200):**
```json
{
  "status": "success",
  "message": "Ajuste de stock realizado exitosamente",
  "data": {
    "producto": { ... },
    "stock_anterior": 48,
    "stock_actual": 50,
    "diferencia": 2
  }
}
```

---

## 💻 Implementación Frontend

### 1. Formulario con Input de Fecha

```jsx
import { useState } from 'react';

const FormularioEntrada = ({ productoId }) => {
  const [formData, setFormData] = useState({
    cantidad: '',
    motivo: '',
    observaciones: '',
    fecha_movimiento: new Date().toISOString().split('T')[0] // Fecha de hoy por defecto
  });

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const response = await axios.post(
        `/api/products/${productoId}/entrada`,
        {
          cantidad: parseInt(formData.cantidad),
          motivo: formData.motivo,
          observaciones: formData.observaciones,
          fecha_movimiento: formData.fecha_movimiento // REQUERIDO
        },
        {
          headers: { Authorization: `Bearer ${token}` }
        }
      );
      
      alert('Entrada registrada exitosamente');
    } catch (error) {
      if (error.response?.status === 422) {
        const errors = error.response.data.errors;
        if (errors.fecha_movimiento) {
          alert('Error: ' + errors.fecha_movimiento[0]);
        }
      }
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <div className="form-group">
        <label>Cantidad *</label>
        <input
          type="number"
          value={formData.cantidad}
          onChange={(e) => setFormData({...formData, cantidad: e.target.value})}
          min="1"
          required
        />
      </div>

      <div className="form-group">
        <label>Fecha del Movimiento * 📅</label>
        <input
          type="date"
          value={formData.fecha_movimiento}
          onChange={(e) => setFormData({...formData, fecha_movimiento: e.target.value})}
          max={new Date().toISOString().split('T')[0]} // No permite fechas futuras
          required
        />
        <small className="text-muted">
          ¿Cuándo realmente llegó el producto? (puede ser días atrás)
        </small>
      </div>

      <div className="form-group">
        <label>Motivo *</label>
        <input
          type="text"
          value={formData.motivo}
          onChange={(e) => setFormData({...formData, motivo: e.target.value})}
          maxLength="255"
          required
        />
      </div>

      <div className="form-group">
        <label>Observaciones</label>
        <textarea
          value={formData.observaciones}
          onChange={(e) => setFormData({...formData, observaciones: e.target.value})}
          rows="3"
        />
        <small className="text-muted">
          Opcional: agregar notas adicionales
        </small>
      </div>

      <button type="submit" className="btn btn-primary">
        Registrar Entrada
      </button>
    </form>
  );
};
```

---

### 2. Formulario de Salida (con área)

```jsx
const FormularioSalida = ({ productoId }) => {
  const [areas, setAreas] = useState([]);
  const [formData, setFormData] = useState({
    cantidad: '',
    motivo: '',
    area_id: '',
    observaciones: '',
    fecha_movimiento: new Date().toISOString().split('T')[0]
  });

  useEffect(() => {
    cargarAreas();
  }, []);

  const cargarAreas = async () => {
    const response = await axios.get('/api/areas/activas', {
      headers: { Authorization: `Bearer ${token}` }
    });
    setAreas(response.data);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      await axios.post(
        `/api/products/${productoId}/salida`,
        {
          cantidad: parseInt(formData.cantidad),
          motivo: formData.motivo,
          area_id: parseInt(formData.area_id),
          observaciones: formData.observaciones,
          fecha_movimiento: formData.fecha_movimiento // REQUERIDO
        },
        {
          headers: { Authorization: `Bearer ${token}` }
        }
      );
      
      alert('Salida registrada exitosamente');
    } catch (error) {
      console.error('Error:', error);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <div className="form-group">
        <label>Cantidad *</label>
        <input
          type="number"
          value={formData.cantidad}
          onChange={(e) => setFormData({...formData, cantidad: e.target.value})}
          min="1"
          required
        />
      </div>

      <div className="form-group">
        <label>Fecha de la Salida * 📅</label>
        <input
          type="date"
          value={formData.fecha_movimiento}
          onChange={(e) => setFormData({...formData, fecha_movimiento: e.target.value})}
          max={new Date().toISOString().split('T')[0]}
          required
        />
        <small className="text-muted">
          ¿Cuándo se entregó realmente a esta área?
        </small>
      </div>

      <div className="form-group">
        <label>Área Destino *</label>
        <select
          value={formData.area_id}
          onChange={(e) => setFormData({...formData, area_id: e.target.value})}
          required
        >
          <option value="">Seleccione un área...</option>
          {areas.map(area => (
            <option key={area.id} value={area.id}>
              {area.nombre} - {area.codigo}
            </option>
          ))}
        </select>
      </div>

      <div className="form-group">
        <label>Motivo *</label>
        <input
          type="text"
          value={formData.motivo}
          onChange={(e) => setFormData({...formData, motivo: e.target.value})}
          maxLength="255"
          required
        />
      </div>

      <div className="form-group">
        <label>Observaciones</label>
        <textarea
          value={formData.observaciones}
          onChange={(e) => setFormData({...formData, observaciones: e.target.value})}
          rows="3"
        />
      </div>

      <button type="submit" className="btn btn-danger">
        Registrar Salida
      </button>
    </form>
  );
};
```

---

### 3. Mostrar Ambas Fechas en Historial

```jsx
const MovementRow = ({ movement }) => (
  <tr>
    <td>
      <span className={`badge badge-${getBadgeColor(movement.tipo)}`}>
        {movement.tipo}
      </span>
    </td>
    <td>{movement.cantidad} {movement.product?.unidad_medida}</td>
    <td>{movement.motivo}</td>
    <td>
      <div>
        <strong>Movimiento:</strong> 
        <span className="text-primary">
          {formatDate(movement.fecha_movimiento)}
        </span>
      </div>
      <div>
        <small className="text-muted">
          Registrado: {formatDateTime(movement.created_at)}
        </small>
      </div>
    </td>
    <td>{movement.user?.nombre}</td>
  </tr>
);

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('es-PE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

const formatDateTime = (dateString) => {
  return new Date(dateString).toLocaleString('es-PE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
```

---

## 📋 Casos de Uso

### Caso 1: Registro el mismo día
**Situación:** El producto llegó hoy y se registra inmediatamente.

```javascript
{
  "cantidad": 50,
  "motivo": "Compra regular",
  "fecha_movimiento": "2026-01-27" // Hoy
}
```

**Resultado en BD:**
- `fecha_movimiento`: 2026-01-27
- `created_at`: 2026-01-27 10:30:15

✅ Ambas fechas coinciden (día), todo normal.

---

### Caso 2: Se olvidaron de registrar
**Situación:** El producto llegó el viernes 24 pero lo registran el lunes 27.

```javascript
{
  "cantidad": 100,
  "motivo": "Llegó viernes pero no registré",
  "fecha_movimiento": "2026-01-24" // Viernes
}
```

**Resultado en BD:**
- `fecha_movimiento`: 2026-01-24 (cuando llegó)
- `created_at`: 2026-01-27 10:30:15 (cuando lo registraron)

✅ Trazabilidad completa: saben cuándo llegó Y cuándo lo cargaron al sistema.

---

### Caso 3: Salida atrasada
**Situación:** Entregaron material a enfermería el jueves pero lo registran el lunes.

```javascript
{
  "cantidad": 30,
  "motivo": "Entrega semanal enfermería",
  "area_id": 2,
  "fecha_movimiento": "2026-01-23" // Jueves
}
```

**Resultado en BD:**
- `fecha_movimiento`: 2026-01-23 (cuando se entregó)
- `created_at`: 2026-01-27 09:00:00 (cuando lo registraron)

✅ El kardex mostrará la fecha correcta del movimiento real.

---

## ⚠️ Validaciones y Errores

### Error 422: fecha_movimiento faltante
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "fecha_movimiento": [
      "El campo fecha movimiento es obligatorio."
    ]
  }
}
```

### Error 422: fecha_movimiento futura
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "fecha_movimiento": [
      "El campo fecha movimiento debe ser una fecha anterior o igual a hoy."
    ]
  }
}
```

### Error 422: fecha_movimiento formato inválido
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "fecha_movimiento": [
      "El campo fecha movimiento no es una fecha válida."
    ]
  }
}
```

---

## 🎨 Recomendaciones de UX/UI

### 1. Input de Fecha con Valor por Defecto
```jsx
// Siempre iniciar con la fecha de HOY
fecha_movimiento: new Date().toISOString().split('T')[0]
```

### 2. Tooltip Explicativo
```jsx
<label>
  Fecha del Movimiento *
  <span 
    className="tooltip-icon" 
    title="¿Cuándo ocurrió realmente este movimiento? Puede ser días atrás si olvidaste registrarlo."
  >
    ℹ️
  </span>
</label>
```

### 3. Advertencia para Fechas Pasadas
```jsx
{formData.fecha_movimiento !== getTodayDate() && (
  <div className="alert alert-warning">
    ⚠️ Estás registrando un movimiento de una fecha pasada ({formData.fecha_movimiento}).
    Asegúrate de que esta sea la fecha correcta del movimiento físico.
  </div>
)}
```

### 4. Restricción en Input
```jsx
<input
  type="date"
  max={new Date().toISOString().split('T')[0]} // No permite futuro
  required
/>
```

---

## 📊 Visualización en Reportes

### En Tabla de Movimientos
```
| Tipo     | Cantidad | Fecha Movimiento | Fecha Registro      | Usuario |
|----------|----------|------------------|---------------------|---------|
| ENTRADA  | 100 UND  | 24/01/2026       | 27/01/2026 10:30    | Juan    |
| SALIDA   | 20 UND   | 23/01/2026       | 27/01/2026 09:00    | María   |
```

### En Kardex
```
Fecha del Movimiento: 24/01/2026
Registrado en sistema: 27/01/2026 10:30:15
Tipo: ENTRADA
Cantidad: 100
Usuario: Juan Pérez
```

---

## ✅ Checklist de Implementación

### Backend ✅
- [x] Validación `fecha_movimiento` obligatoria
- [x] Validación `before_or_equal:today`
- [x] Campo `fecha_movimiento` en Movement model fillable
- [x] Aplicado en ENTRADA, SALIDA y AJUSTE

### Frontend (Pendiente)
- [ ] Agregar input `type="date"` en formularios
- [ ] Establecer valor por defecto (fecha actual)
- [ ] Agregar restricción `max={today}`
- [ ] Mostrar ambas fechas en historial
- [ ] Actualizar reportes para incluir fecha_movimiento
- [ ] Agregar tooltips explicativos
- [ ] Validación en frontend antes de enviar

---

## 🔧 Testing

### Test Manual

**1. Registrar entrada con fecha de hoy:**
```bash
POST /api/products/1/entrada
{
  "cantidad": 50,
  "motivo": "Prueba",
  "fecha_movimiento": "2026-01-27"
}
```
✅ Debe funcionar correctamente.

**2. Registrar entrada con fecha pasada:**
```bash
POST /api/products/1/entrada
{
  "cantidad": 50,
  "motivo": "Prueba atrasada",
  "fecha_movimiento": "2026-01-20"
}
```
✅ Debe funcionar correctamente.

**3. Intentar fecha futura:**
```bash
POST /api/products/1/entrada
{
  "cantidad": 50,
  "motivo": "Prueba futura",
  "fecha_movimiento": "2026-01-30"
}
```
❌ Debe retornar error 422.

**4. Omitir fecha_movimiento:**
```bash
POST /api/products/1/entrada
{
  "cantidad": 50,
  "motivo": "Sin fecha"
}
```
❌ Debe retornar error 422.

---

**Fecha de actualización:** 27 de enero de 2026
