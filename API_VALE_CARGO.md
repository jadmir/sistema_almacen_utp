# Sistema de Vale de Cargo - Documentación

## 📋 Descripción General

El sistema de **Vale de Cargo** permite generar comprobantes de entrega físicos cuando se realiza una salida de productos del almacén hacia un área específica. Este vale debe ser firmado por quien entrega y quien recibe, sirviendo como respaldo documental.

---

## 🚀 Funcionamiento

### 1. Registro de Salida con Vale de Cargo

Al realizar una **salida de productos**, ahora se deben incluir los datos de quien recibe:

**Endpoint:** `POST /api/products/{id}/salida`

**Nuevos campos requeridos:**
```json
{
  "cantidad": 50,
  "motivo": "Distribución mensual",
  "area_id": 1,
  "fecha_movimiento": "2026-02-11",
  "observaciones": "Entrega de material de oficina",
  
  // DATOS DEL RECEPTOR (Nuevos campos requeridos)
  "recibido_por": "María García López",
  "dni_receptor": "12345678",
  "cargo_receptor": "Coordinadora de Área",
  "observaciones_receptor": "Material en buen estado"
}
```

**Respuesta exitosa:**
```json
{
  "status": "success",
  "message": "Salida de stock registrada exitosamente",
  "data": {
    "movimiento_id": 123,
    "numero_vale": "VC-2026-0001",
    "producto": {...},
    "stock_anterior": 100,
    "stock_actual": 50,
    "cantidad_retirada": 50,
    "alerta_stock_bajo": false
  }
}
```

---

### 2. Generación del PDF del Vale de Cargo

**Endpoint:** `GET /api/reportes/pdf/vale-cargo/{movementId}`

**Ejemplo:**
```bash
GET /api/reportes/pdf/vale-cargo/123
Authorization: Bearer {token}
```

**Descarga:**
- Archivo: `vale_cargo_VC-2026-0001.pdf`
- Formato: A4 vertical
- Listo para imprimir y firmar
- **✨ Se guarda automáticamente en el servidor como evidencia**

---

### 3. Listar Vales de Cargo (Búsqueda de Evidencias)

**Endpoint:** `GET /api/vales-cargo`

**Parámetros opcionales:**
- `fecha_desde` - Filtrar desde fecha (YYYY-MM-DD)
- `fecha_hasta` - Filtrar hasta fecha (YYYY-MM-DD)
- `numero_vale` - Buscar por número de vale
- `recibido_por` - Buscar por nombre del receptor
- `area_id` - Filtrar por área
- `per_page` - Resultados por página (default: 15)

**Ejemplo:**
```bash
GET /api/vales-cargo?fecha_desde=2026-02-01&recibido_por=Juan
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 123,
        "numero_vale": "VC-2026-0001",
        "cantidad": 50,
        "fecha_movimiento": "2026-02-11T00:00:00.000000Z",
        "recibido_por": "Juan Pérez García",
        "dni_receptor": "12345678",
        "cargo_receptor": "Jefe de Área",
        "pdf_path": "vales_cargo/2026/02/vale_cargo_VC-2026-0001.pdf",
        "product": {
          "id": 15,
          "codigo": "ASSOF-0001",
          "nombre": "Lapicero azul"
        },
        "user": {
          "id": 1,
          "nombre": "Administrador"
        },
        "area": {
          "id": 1,
          "nombre": "Administración",
          "codigo": "ADM"
        }
      }
    ],
    "total": 25,
    "per_page": 15,
    "current_page": 1,
    "last_page": 2
  }
}
```

---

### 4. Descargar Vale Previamente Generado

**Endpoint:** `GET /api/vales-cargo/{movementId}/descargar`

**Ejemplo:**
```bash
GET /api/vales-cargo/123/descargar
Authorization: Bearer {token}
```

**Descarga:**
- Descarga el PDF guardado previamente en el servidor
- Útil cuando se olvidó imprimir o se necesita una copia

---

## 📄 Contenido del Vale de Cargo

El PDF generado incluye:

### Sección 1: Información General
- Número de vale único (VC-YYYY-NNNN)
- Fecha y hora de la salida
- Nombre del asistente/administrador que entrega
- Área de destino

### Sección 2: Productos Entregados
- Código del producto
- Descripción completa
- Cantidad entregada
- Unidad de medida
- Sección de origen
- Motivo de la salida
- Observaciones (opcional)

### Sección 3: Datos del Receptor
- Nombre completo de quien recibe
- DNI
- Cargo o puesto
- Observaciones del receptor (opcional)

### Sección 4: Firmas
- Espacio para firma del que entrega (Asistente/Administrador)
- Espacio para firma de quien recibe

---

## 🔢 Sistema de Numeración

Los vales tienen un número único y correlativo:

**Formato:** `VC-YYYY-NNNN`

**Ejemplos:**
- `VC-2026-0001` → Primer vale del año 2026
- `VC-2026-0125` → Vale número 125 del año 2026
- `VC-2027-0001` → Primer vale del año 2027 (reinicia)

**Características:**
- ✅ Único e irrepetible
- ✅ Reinicia cada año
- ✅ Formato con ceros a la izquierda (4 dígitos)
- ✅ Indexado en base de datos

---

## 📊 Campos en Base de Datos

Se agregaron los siguientes campos a la tabla `movements`:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `numero_vale` | string(20) | Número único del vale |
| `recibido_por` | string(255) | Nombre completo del receptor |
| `dni_receptor` | string(20) | DNI del receptor |
| `cargo_receptor` | string(100) | Cargo o puesto del receptor |
| `observaciones_receptor` | text | Observaciones adicionales |
| `pdf_path` | string(500) | Ruta del PDF guardado en servidor |

---

## 🔍 Validaciones

### Al Registrar Salida
✅ Todos los campos del receptor son **obligatorios**
✅ Debe existir el área destino
✅ Debe haber stock suficiente
✅ Fecha no puede ser futura

### Al Generar PDF
✅ Solo se generan vales para movimientos de **SALIDA**
✅ El movimiento debe tener datos del receptor registrados
✅ El movimiento debe existir en la base de datos

---

## 💡 Flujo de Trabajo Recomendado

1. **Solicitud de Material**
   - El área solicita productos al almacén

2. **Registro en el Sistema**
   - Asistente/Administrador registra la salida
   - Incluye datos de quien recibirá el material
   - Sistema genera número de vale automáticamente
   - **✨ PDF se guarda automáticamente en el servidor**

3. **Generación del Vale**
   - Se genera el PDF del vale de cargo
   - Se descarga para imprimir

4. **Entrega Física**
   - Asistente/Administrador entrega los productos
   - Ambas partes firman el vale impreso

5. **Archivo**
   - El vale firmado se archiva físicamente
   - **✨ Siempre disponible en el sistema para descarga**
   - Opcionalmente se puede escanear y guardar digitalmente

6. **Búsqueda y Recuperación** (NUEVO)
   - Si se olvida la entrega, puede buscar en el sistema
   - Filtrar por fecha, número de vale o receptor
   - Descargar nuevamente el PDF como evidencia

---

## 📱 Ejemplo de Uso Completo

### Paso 1: Registrar Salida
```bash
POST /api/products/15/salida
Content-Type: application/json
Authorization: Bearer {token}

{
  "cantidad": 20,
  "motivo": "Material de oficina mensual",
  "area_id": 2,
  "fecha_movimiento": "2026-02-11",
  "observaciones": "Entrega programada",
  "recibido_por": "Carlos Méndez Ruiz",
  "dni_receptor": "87654321",
  "cargo_receptor": "Jefe de Área",
  "observaciones_receptor": "Material completo y en buen estado"
}
```

### Paso 2: Obtener Número de Vale
```json
{
  "status": "success",
  "data": {
    "movimiento_id": 456,
    "numero_vale": "VC-2026-0089",
    ...
  }
}
```

### Paso 3: Generar PDF
```bash
GET /api/reportes/pdf/vale-cargo/456
Authorization: Bearer {token}
```

### Paso 4: Imprimir y Firmar
- Se descarga: `vale_cargo_VC-2026-0089.pdf`
- Se imprime
- Se firma por ambas partes
- Se archiva

---

## 🎯 Beneficios

✅ **Trazabilidad:** Cada salida tiene un comprobante físico  
✅ **Responsabilidad:** Firmas de entrega y recepción  
✅ **Control:** Número único para cada vale  
✅ **Auditoría:** Historial completo de entregas  
✅ **Legal:** Documento válido como respaldo  
✅ **✨ Evidencia Digital:** PDFs guardados automáticamente en el servidor  
✅ **✨ Búsqueda Rápida:** Encuentra vales por fecha, número o receptor  
✅ **✨ Recuperación:** Re-descarga vales aunque se hayan olvidado

---

## 🔐 Permisos Requeridos

- `inventario.salida` → Para registrar salidas
- `inventario.ver` → Para generar el PDF del vale

---

## 📌 Notas Importantes

1. Los campos del receptor son **obligatorios** para todas las salidas
2. El número de vale se genera **automáticamente**
3. Solo se generan vales para movimientos de **SALIDA**
4. El PDF está optimizado para impresión en **papel A4**
5. Los vales se numeran **por año calendario**

---

## 🆕 Cambios en el Sistema

### Archivos Modificados:
- ✅ `database/migrations/...add_vale_cargo_fields_to_movements_table.php`
- ✅ `app/Models/Movement.php`
- ✅ `app/Http/Controllers/ProductController.php` (método `registrarSalida`)
- ✅ `app/Http/Controllers/ReportController.php` (nuevo método `valeCargoPdf`)
- ✅ `resources/views/reports/vale-cargo.blade.php` (nueva vista)
- ✅ `routes/api.php` (nueva ruta)

### Tablas Modificadas:
- ✅ `movements` → Agregados 5 campos nuevos

---

**Fecha de implementación:** 11 de Febrero de 2026

---

## 🎨 GUÍA DE INTEGRACIÓN PARA FRONTEND

### 📍 URL Base
```javascript
const API_URL = 'http://127.0.0.1:8000/api';
```

### 🔑 Configuración de Headers
```javascript
const getHeaders = (token) => ({
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${token}`
});
```

---

## 🚀 Implementación en React/Vue/Angular

### 1️⃣ Registrar Salida con Vale de Cargo

```javascript
// Función para registrar salida
async function registrarSalidaConVale(productoId, datosFormulario, token) {
  try {
    const response = await fetch(`${API_URL}/products/${productoId}/salida`, {
      method: 'POST',
      headers: getHeaders(token),
      body: JSON.stringify({
        // Datos normales de salida
        cantidad: datosFormulario.cantidad,
        motivo: datosFormulario.motivo,
        area_id: datosFormulario.areaId,
        fecha_movimiento: datosFormulario.fecha, // Formato: YYYY-MM-DD
        observaciones: datosFormulario.observaciones || '',
        
        // DATOS DEL RECEPTOR (obligatorios)
        recibido_por: datosFormulario.recibidoPor,
        dni_receptor: datosFormulario.dniReceptor,
        cargo_receptor: datosFormulario.cargoReceptor,
        observaciones_receptor: datosFormulario.observacionesReceptor || ''
      })
    });

    const result = await response.json();
    
    if (result.status === 'success') {
      // Guardar el ID del movimiento y número de vale
      const movimientoId = result.data.movimiento_id;
      const numeroVale = result.data.numero_vale;
      
      console.log('Vale generado:', numeroVale);
      
      // Retornar los datos
      return {
        success: true,
        movimientoId,
        numeroVale,
        data: result.data
      };
    } else {
      // Manejar errores de validación
      return {
        success: false,
        errors: result.errors || {},
        message: result.message
      };
    }
    
  } catch (error) {
    console.error('Error al registrar salida:', error);
    return {
      success: false,
      message: 'Error de conexión con el servidor'
    };
  }
}
```

---

### 2️⃣ Descargar PDF del Vale de Cargo

```javascript
// Función para descargar el PDF
async function descargarValePDF(movimientoId, token) {
  try {
    const response = await fetch(
      `${API_URL}/reportes/pdf/vale-cargo/${movimientoId}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    );

    if (response.ok) {
      // Obtener el blob del PDF
      const blob = await response.blob();
      
      // Crear URL temporal
      const url = window.URL.createObjectURL(blob);
      
      // Crear link de descarga
      const link = document.createElement('a');
      link.href = url;
      link.download = `vale_cargo_${movimientoId}.pdf`;
      
      // Simular click para descargar
      document.body.appendChild(link);
      link.click();
      
      // Limpiar
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
      
      return { success: true };
    } else {
      const error = await response.json();
      return {
        success: false,
        message: error.message || 'Error al generar PDF'
      };
    }
    
  } catch (error) {
    console.error('Error al descargar PDF:', error);
    return {
      success: false,
      message: 'Error de conexión'
    };
  }
}
```

---

### 3️⃣ Ver PDF en nueva ventana (alternativa)

```javascript
// Función para abrir PDF en nueva pestaña
function abrirValePDF(movimientoId, token) {
  const url = `${API_URL}/reportes/pdf/vale-cargo/${movimientoId}`;
  
  // Abrir en nueva pestaña con autenticación
  const newWindow = window.open('', '_blank');
  
  fetch(url, {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`
    }
  })
  .then(response => response.blob())
  .then(blob => {
    const blobUrl = window.URL.createObjectURL(blob);
    newWindow.location.href = blobUrl;
  })
  .catch(error => {
    console.error('Error:', error);
    newWindow.close();
    alert('Error al abrir el PDF');
  });
}
```

---

### 4️⃣ Listar Vales de Cargo (Búsqueda)

```javascript
// Función para listar y buscar vales
async function listarVales(filtros, token) {
  try {
    const params = new URLSearchParams();
    
    if (filtros.fechaDesde) params.append('fecha_desde', filtros.fechaDesde);
    if (filtros.fechaHasta) params.append('fecha_hasta', filtros.fechaHasta);
    if (filtros.numeroVale) params.append('numero_vale', filtros.numeroVale);
    if (filtros.recibidoPor) params.append('recibido_por', filtros.recibidoPor);
    if (filtros.areaId) params.append('area_id', filtros.areaId);
    if (filtros.perPage) params.append('per_page', filtros.perPage);
    
    const response = await fetch(
      `${API_URL}/vales-cargo?${params.toString()}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    );

    if (response.ok) {
      const result = await response.json();
      return {
        success: true,
        vales: result.data.data,
        pagination: {
          currentPage: result.data.current_page,
          lastPage: result.data.last_page,
          total: result.data.total,
          perPage: result.data.per_page
        }
      };
    } else {
      return {
        success: false,
        message: 'Error al listar vales'
      };
    }
    
  } catch (error) {
    console.error('Error al listar vales:', error);
    return {
      success: false,
      message: 'Error de conexión'
    };
  }
}
```

---

### 5️⃣ Descargar Vale Previamente Guardado

```javascript
// Función para re-descargar un vale guardado
async function descargarValeGuardado(movimientoId, numeroVale, token) {
  try {
    const response = await fetch(
      `${API_URL}/vales-cargo/${movimientoId}/descargar`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    );

    if (response.ok) {
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `vale_cargo_${numeroVale}.pdf`;
      link.click();
      window.URL.revokeObjectURL(url);
      
      return { success: true };
    } else {
      const error = await response.json();
      return {
        success: false,
        message: error.message || 'PDF no disponible'
      };
    }
    
  } catch (error) {
    console.error('Error:', error);
    return {
      success: false,
      message: 'Error de conexión'
    };
  }
}
```

---

## 📝 Ejemplo de Formulario React

```jsx
import React, { useState } from 'react';

function FormularioSalidaConVale({ productoId, token, onSuccess }) {
  const [formData, setFormData] = useState({
    cantidad: '',
    motivo: '',
    areaId: '',
    fecha: new Date().toISOString().split('T')[0],
    observaciones: '',
    recibidoPor: '',
    dniReceptor: '',
    cargoReceptor: '',
    observacionesReceptor: ''
  });
  
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    // Registrar salida
    const result = await registrarSalidaConVale(productoId, formData, token);
    
    if (result.success) {
      // Mostrar mensaje de éxito
      alert(`Vale generado: ${result.numeroVale}`);
      
      // Descargar automáticamente el PDF
      await descargarValePDF(result.movimientoId, token);
      
      // Callback de éxito
      onSuccess(result);
    } else {
      // Mostrar errores
      setErrors(result.errors || {});
      alert(result.message);
    }
    
    setLoading(false);
  };

  return (
    <form onSubmit={handleSubmit} className="vale-form">
      <h2>Registrar Salida con Vale de Cargo</h2>
      
      {/* Datos de la Salida */}
      <fieldset>
        <legend>Datos de la Salida</legend>
        
        <div className="form-group">
          <label>Cantidad *</label>
          <input
            type="number"
            min="1"
            value={formData.cantidad}
            onChange={(e) => setFormData({...formData, cantidad: e.target.value})}
            required
          />
          {errors.cantidad && <span className="error">{errors.cantidad[0]}</span>}
        </div>

        <div className="form-group">
          <label>Motivo *</label>
          <input
            type="text"
            value={formData.motivo}
            onChange={(e) => setFormData({...formData, motivo: e.target.value})}
            required
          />
          {errors.motivo && <span className="error">{errors.motivo[0]}</span>}
        </div>

        <div className="form-group">
          <label>Área Destino *</label>
          <select
            value={formData.areaId}
            onChange={(e) => setFormData({...formData, areaId: e.target.value})}
            required
          >
            <option value="">Seleccione...</option>
            {/* Cargar áreas desde API */}
          </select>
          {errors.area_id && <span className="error">{errors.area_id[0]}</span>}
        </div>

        <div className="form-group">
          <label>Fecha *</label>
          <input
            type="date"
            value={formData.fecha}
            onChange={(e) => setFormData({...formData, fecha: e.target.value})}
            max={new Date().toISOString().split('T')[0]}
            required
          />
          {errors.fecha_movimiento && <span className="error">{errors.fecha_movimiento[0]}</span>}
        </div>

        <div className="form-group">
          <label>Observaciones</label>
          <textarea
            value={formData.observaciones}
            onChange={(e) => setFormData({...formData, observaciones: e.target.value})}
            rows="3"
          />
        </div>
      </fieldset>

      {/* Datos del Receptor */}
      <fieldset>
        <legend>Datos de Quien Recibe</legend>
        
        <div className="form-group">
          <label>Nombre Completo *</label>
          <input
            type="text"
            value={formData.recibidoPor}
            onChange={(e) => setFormData({...formData, recibidoPor: e.target.value})}
            placeholder="Ej: Juan Pérez García"
            required
          />
          {errors.recibido_por && <span className="error">{errors.recibido_por[0]}</span>}
        </div>

        <div className="form-group">
          <label>DNI *</label>
          <input
            type="text"
            value={formData.dniReceptor}
            onChange={(e) => setFormData({...formData, dniReceptor: e.target.value})}
            placeholder="12345678"
            maxLength="8"
            required
          />
          {errors.dni_receptor && <span className="error">{errors.dni_receptor[0]}</span>}
        </div>

        <div className="form-group">
          <label>Cargo *</label>
          <input
            type="text"
            value={formData.cargoReceptor}
            onChange={(e) => setFormData({...formData, cargoReceptor: e.target.value})}
            placeholder="Ej: Jefe de Área"
            required
          />
          {errors.cargo_receptor && <span className="error">{errors.cargo_receptor[0]}</span>}
        </div>

        <div className="form-group">
          <label>Observaciones del Receptor</label>
          <textarea
            value={formData.observacionesReceptor}
            onChange={(e) => setFormData({...formData, observacionesReceptor: e.target.value})}
            placeholder="Ej: Material recibido en buen estado"
            rows="2"
          />
        </div>
      </fieldset>

      <button type="submit" disabled={loading}>
        {loading ? 'Procesando...' : 'Registrar Salida y Generar Vale'}
      </button>
    </form>
  );
}

export default FormularioSalidaConVale;
```

---

## 🎨 Ejemplo de Vista en Vue.js

```vue
<template>
  <div class="salida-vale-container">
    <h2>Registrar Salida con Vale de Cargo</h2>
    
    <form @submit.prevent="registrarSalida">
      <!-- Datos de Salida -->
      <div class="seccion">
        <h3>Datos de la Salida</h3>
        
        <div class="campo">
          <label>Cantidad *</label>
          <input 
            v-model.number="form.cantidad" 
            type="number" 
            min="1" 
            required 
          />
        </div>

        <div class="campo">
          <label>Motivo *</label>
          <input 
            v-model="form.motivo" 
            type="text" 
            required 
          />
        </div>

        <div class="campo">
          <label>Área Destino *</label>
          <select v-model="form.areaId" required>
            <option value="">Seleccione...</option>
            <option 
              v-for="area in areas" 
              :key="area.id" 
              :value="area.id"
            >
              {{ area.nombre }}
            </option>
          </select>
        </div>

        <div class="campo">
          <label>Fecha *</label>
          <input 
            v-model="form.fecha" 
            type="date" 
            :max="hoy" 
            required 
          />
        </div>

        <div class="campo">
          <label>Observaciones</label>
          <textarea v-model="form.observaciones" rows="3"></textarea>
        </div>
      </div>

      <!-- Datos del Receptor -->
      <div class="seccion">
        <h3>Datos de Quien Recibe</h3>
        
        <div class="campo">
          <label>Nombre Completo *</label>
          <input 
            v-model="form.recibidoPor" 
            type="text" 
            required 
          />
        </div>

        <div class="campo">
          <label>DNI *</label>
          <input 
            v-model="form.dniReceptor" 
            type="text" 
            maxlength="8" 
            required 
          />
        </div>

        <div class="campo">
          <label>Cargo *</label>
          <input 
            v-model="form.cargoReceptor" 
            type="text" 
            required 
          />
        </div>

        <div class="campo">
          <label>Observaciones del Receptor</label>
          <textarea 
            v-model="form.observacionesReceptor" 
            rows="2"
          ></textarea>
        </div>
      </div>

      <button type="submit" :disabled="cargando">
        {{ cargando ? 'Procesando...' : 'Registrar y Generar Vale' }}
      </button>
    </form>

    <!-- Resultado -->
    <div v-if="valeGenerado" class="resultado-exitoso">
      <h3>✅ Vale Generado Exitosamente</h3>
      <p><strong>Número:</strong> {{ numeroVale }}</p>
      <button @click="descargarPDF">Descargar PDF</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormularioSalidaVale',
  
  data() {
    return {
      form: {
        cantidad: 1,
        motivo: '',
        areaId: '',
        fecha: new Date().toISOString().split('T')[0],
        observaciones: '',
        recibidoPor: '',
        dniReceptor: '',
        cargoReceptor: '',
        observacionesReceptor: ''
      },
      areas: [],
      cargando: false,
      valeGenerado: false,
      numeroVale: '',
      movimientoId: null
    }
  },
  
  computed: {
    hoy() {
      return new Date().toISOString().split('T')[0];
    }
  },
  
  mounted() {
    this.cargarAreas();
  },
  
  methods: {
    async cargarAreas() {
      try {
        const response = await fetch(
          `${process.env.VUE_APP_API_URL}/areas`,
          {
            headers: {
              'Authorization': `Bearer ${this.$store.state.token}`
            }
          }
        );
        const data = await response.json();
        this.areas = data.data || [];
      } catch (error) {
        console.error('Error al cargar áreas:', error);
      }
    },
    
    async registrarSalida() {
      this.cargando = true;
      
      try {
        const response = await fetch(
          `${process.env.VUE_APP_API_URL}/products/${this.$route.params.id}/salida`,
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${this.$store.state.token}`
            },
            body: JSON.stringify({
              cantidad: this.form.cantidad,
              motivo: this.form.motivo,
              area_id: this.form.areaId,
              fecha_movimiento: this.form.fecha,
              observaciones: this.form.observaciones,
              recibido_por: this.form.recibidoPor,
              dni_receptor: this.form.dniReceptor,
              cargo_receptor: this.form.cargoReceptor,
              observaciones_receptor: this.form.observacionesReceptor
            })
          }
        );
        
        const result = await response.json();
        
        if (result.status === 'success') {
          this.valeGenerado = true;
          this.numeroVale = result.data.numero_vale;
          this.movimientoId = result.data.movimiento_id;
          
          this.$emit('salida-registrada', result.data);
          
          // Auto-descargar PDF
          await this.descargarPDF();
        } else {
          alert(result.message);
        }
        
      } catch (error) {
        console.error('Error:', error);
        alert('Error al registrar la salida');
      } finally {
        this.cargando = false;
      }
    },
    
    async descargarPDF() {
      try {
        const response = await fetch(
          `${process.env.VUE_APP_API_URL}/reportes/pdf/vale-cargo/${this.movimientoId}`,
          {
            headers: {
              'Authorization': `Bearer ${this.$store.state.token}`
            }
          }
        );
        
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `vale_cargo_${this.numeroVale}.pdf`;
        link.click();
        window.URL.revokeObjectURL(url);
        
      } catch (error) {
        console.error('Error al descargar PDF:', error);
        alert('Error al descargar el PDF');
      }
    }
  }
}
</script>

<style scoped>
.salida-vale-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.seccion {
  background: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.campo {
  margin-bottom: 15px;
}

.campo label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
}

.campo input,
.campo select,
.campo textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

button[type="submit"] {
  background: #4CAF50;
  color: white;
  padding: 12px 24px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  width: 100%;
}

button[type="submit"]:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.resultado-exitoso {
  background: #d4edda;
  border: 1px solid #c3e6cb;
  padding: 20px;
  border-radius: 8px;
  margin-top: 20px;
  text-align: center;
}

.resultado-exitoso button {
  background: #007bff;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 10px;
}
</style>
```

---

## 🔄 Flujo Completo de Usuario

```
1. Usuario abre formulario de salida
   ↓
2. Completa datos de salida (cantidad, motivo, área, fecha)
   ↓
3. Completa datos del receptor (nombre, DNI, cargo)
   ↓
4. Click en "Registrar Salida"
   ↓
5. Frontend envía POST /api/products/{id}/salida
   ↓
6. Backend valida y crea movimiento
   ↓
7. Backend genera número de vale automáticamente
   ↓
8. Backend responde con movimiento_id y numero_vale
   ↓
9. Frontend recibe respuesta exitosa
   ↓
10. Frontend automáticamente llama GET /api/reportes/pdf/vale-cargo/{id}
    ↓
11. PDF se descarga automáticamente
    ↓
12. Usuario imprime PDF
    ↓
13. Vale se firma físicamente
    ↓
14. Se archiva como respaldo documental
```

---

## ⚠️ Manejo de Errores

### Errores Comunes y Cómo Manejarlos

```javascript
// Ejemplo de manejo de errores
async function manejarRegistroSalida(productoId, datos, token) {
  try {
    const result = await registrarSalidaConVale(productoId, datos, token);
    
    if (!result.success) {
      // Errores de validación
      if (result.errors) {
        // Mostrar errores campo por campo
        Object.keys(result.errors).forEach(campo => {
          console.error(`${campo}: ${result.errors[campo][0]}`);
        });
        
        // Ejemplos de errores:
        // cantidad: ["La cantidad no puede ser mayor al stock disponible"]
        // recibido_por: ["El campo recibido por es obligatorio"]
        // dni_receptor: ["El campo DNI debe tener 8 dígitos"]
      }
      
      return false;
    }
    
    return true;
    
  } catch (error) {
    // Error de red o servidor
    console.error('Error de conexión:', error);
    alert('No se pudo conectar con el servidor. Verifique su conexión.');
    return false;
  }
}
```

### Validaciones en el Frontend (Recomendadas)

```javascript
function validarFormulario(form) {
  const errores = {};
  
  // Validar cantidad
  if (!form.cantidad || form.cantidad <= 0) {
    errores.cantidad = 'La cantidad debe ser mayor a 0';
  }
  
  // Validar motivo
  if (!form.motivo || form.motivo.trim() === '') {
    errores.motivo = 'El motivo es obligatorio';
  }
  
  // Validar área
  if (!form.areaId) {
    errores.areaId = 'Debe seleccionar un área';
  }
  
  // Validar fecha
  const hoy = new Date().toISOString().split('T')[0];
  if (!form.fecha || form.fecha > hoy) {
    errores.fecha = 'La fecha no puede ser futura';
  }
  
  // Validar nombre del receptor
  if (!form.recibidoPor || form.recibidoPor.trim().length < 3) {
    errores.recibidoPor = 'El nombre completo es obligatorio (mínimo 3 caracteres)';
  }
  
  // Validar DNI
  if (!form.dniReceptor || !/^\d{8}$/.test(form.dniReceptor)) {
    errores.dniReceptor = 'El DNI debe tener 8 dígitos';
  }
  
  // Validar cargo
  if (!form.cargoReceptor || form.cargoReceptor.trim() === '') {
    errores.cargoReceptor = 'El cargo es obligatorio';
  }
  
  return {
    valido: Object.keys(errores).length === 0,
    errores
  };
}
```

---

## 📊 Estados de la Interfaz

```javascript
// Estados recomendados para el componente
const [estado, setEstado] = useState({
  cargando: false,
  valeGenerado: false,
  descargandoPDF: false,
  error: null
});

// Actualizar estados durante el flujo
setEstado({ cargando: true });
// ... proceso ...
setEstado({ 
  cargando: false, 
  valeGenerado: true,
  numeroVale: 'VC-2026-0001'
});
```

---

## 💾 Variables de Entorno Recomendadas

```env
# .env
REACT_APP_API_URL=http://127.0.0.1:8000/api
VUE_APP_API_URL=http://127.0.0.1:8000/api
VITE_API_URL=http://127.0.0.1:8000/api
```

---

## ✅ Checklist de Implementación

- [ ] Crear formulario con todos los campos requeridos
- [ ] Validar campos en el frontend antes de enviar
- [ ] Implementar función de registro de salida
- [ ] Implementar función de descarga de PDF
- [ ] Manejar estados de carga (loading)
- [ ] Manejar errores de validación del backend
- [ ] Mostrar mensaje de éxito con número de vale
- [ ] Auto-descargar PDF tras registro exitoso
- [ ] Agregar botón para re-descargar PDF
- [ ] Agregar opción para ver PDF en nueva pestaña
- [ ] Implementar notificaciones/toast de éxito
- [ ] Agregar loader/spinner durante procesamiento

---

**✨ Listo para integrar en tu frontend!**
