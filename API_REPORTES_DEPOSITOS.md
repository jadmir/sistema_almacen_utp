# Reportes de Inventario por Depósito - Documentación

## 📋 Descripción General

Los reportes de inventario ahora incluyen **filtrado por depósitos**, permitiendo descargar información específica de cada depósito sin tener que descargar toda la información del almacén.

---

## 🎯 Depósitos Disponibles

Para listar todos los depósitos disponibles:

**Endpoint:** `GET /api/depositos`

**Respuesta:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "nombre": "DEPOSITO - OPE - AZOTEA",
      "activo": true,
      "productos_count": 0
    },
    {
      "id": 4,
      "nombre": "DEPOSITO 1 - VU - PRIMER PISO",
      "activo": true,
      "productos_count": 15
    }
  ]
}
```

---

## 📊 Reportes Disponibles con Filtro de Depósito

### 1️⃣ Reporte General de Productos

**Endpoint:** `GET /api/reportes/productos`

**Parámetros:**
- `section_id` (opcional) - ID de la sección
- `stock_type_id` (opcional) - ID del tipo de stock
- `deposito_id` (opcional) - **NUEVO** - ID del depósito
- `codigo` (opcional) - Código del producto
- `nombre` (opcional) - Nombre del producto

**Ejemplos:**

```bash
# Todos los productos del Depósito 4
GET /api/reportes/productos?deposito_id=4

# Productos del Depósito 1 de la sección 3
GET /api/reportes/productos?deposito_id=1&section_id=3

# Productos del Depósito 2 con stock bajo
GET /api/reportes/productos?deposito_id=2&stock_type_id=1
```

---

### 2️⃣ Reporte de Stock Bajo

**Endpoint:** `GET /api/reportes/stock-bajo`

**Parámetros:**
- `section_id` (opcional) - ID de la sección
- `stock_type_id` (opcional) - ID del tipo de stock
- `deposito_id` (opcional) - **NUEVO** - ID del depósito

**Ejemplos:**

```bash
# Stock bajo del Depósito 4
GET /api/reportes/stock-bajo?deposito_id=4

# Stock bajo de todos los depósitos (sin filtro)
GET /api/reportes/stock-bajo
```

---

### 3️⃣ Reporte de Productos Próximos a Vencer

**Endpoint:** `GET /api/reportes/proximos-vencer`

**Parámetros:**
- `dias` (opcional, default: 30) - Días hacia adelante
- `section_id` (opcional) - ID de la sección
- `deposito_id` (opcional) - **NUEVO** - ID del depósito

**Ejemplos:**

```bash
# Productos que vencen en 15 días en el Depósito 5
GET /api/reportes/proximos-vencer?dias=15&deposito_id=5

# Productos que vencen en 7 días (todos los depósitos)
GET /api/reportes/proximos-vencer?dias=7
```

---

### 4️⃣ Reporte de Productos Vencidos

**Endpoint:** `GET /api/reportes/vencidos`

**Parámetros:**
- `section_id` (opcional) - ID de la sección
- `deposito_id` (opcional) - **NUEVO** - ID del depósito

**Ejemplos:**

```bash
# Productos vencidos del Depósito 3
GET /api/reportes/vencidos?deposito_id=3

# Todos los productos vencidos
GET /api/reportes/vencidos
```

---

## 💻 Integración Frontend

### Listar Depósitos

```javascript
// Función para obtener lista de depósitos
async function obtenerDepositos(token) {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/depositos', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    const result = await response.json();
    
    if (result.status === 'success') {
      return {
        success: true,
        depositos: result.data
      };
    }
    
    return { success: false };
  } catch (error) {
    console.error('Error al obtener depósitos:', error);
    return { success: false };
  }
}
```

---

### Descargar Reporte por Depósito

```javascript
// Función para descargar reporte filtrado por depósito
async function descargarReportePorDeposito(tipoReporte, filtros, token) {
  try {
    const params = new URLSearchParams();
    
    // Agregar filtros según el tipo de reporte
    if (filtros.depositoId) params.append('deposito_id', filtros.depositoId);
    if (filtros.sectionId) params.append('section_id', filtros.sectionId);
    if (filtros.stockTypeId) params.append('stock_type_id', filtros.stockTypeId);
    if (filtros.dias) params.append('dias', filtros.dias);
    
    const endpoints = {
      'productos': '/api/reportes/productos',
      'stockBajo': '/api/reportes/stock-bajo',
      'proximosVencer': '/api/reportes/proximos-vencer',
      'vencidos': '/api/reportes/vencidos'
    };
    
    const url = `http://127.0.0.1:8000${endpoints[tipoReporte]}?${params.toString()}`;
    
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    if (response.ok) {
      const blob = await response.blob();
      const urlBlob = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = urlBlob;
      link.download = `reporte_${tipoReporte}_${new Date().getTime()}.xlsx`;
      link.click();
      window.URL.revokeObjectURL(urlBlob);
      
      return { success: true };
    } else {
      return { success: false, message: 'Error al descargar reporte' };
    }
  } catch (error) {
    console.error('Error:', error);
    return { success: false, message: 'Error de conexión' };
  }
}

// Uso:
// Descargar productos del depósito 4
descargarReportePorDeposito('productos', { depositoId: 4 }, token);

// Descargar stock bajo del depósito 2 y sección 3
descargarReportePorDeposito('stockBajo', { depositoId: 2, sectionId: 3 }, token);
```

---

## ⚛️ Ejemplo en React

```jsx
import React, { useState, useEffect } from 'react';

function ReportesDepositos({ token }) {
  const [depositos, setDepositos] = useState([]);
  const [filtros, setFiltros] = useState({
    tipoReporte: 'productos',
    depositoId: ''
  });
  const [descargando, setDescargando] = useState(false);

  useEffect(() => {
    cargarDepositos();
  }, []);

  const cargarDepositos = async () => {
    const response = await fetch('http://127.0.0.1:8000/api/depositos', {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const data = await response.json();
    if (data.status === 'success') {
      setDepositos(data.data);
    }
  };

  const descargarReporte = async () => {
    setDescargando(true);
    
    const params = new URLSearchParams();
    if (filtros.depositoId) params.append('deposito_id', filtros.depositoId);
    
    const endpoints = {
      'productos': '/api/reportes/productos',
      'stockBajo': '/api/reportes/stock-bajo',
      'proximosVencer': '/api/reportes/proximos-vencer',
      'vencidos': '/api/reportes/vencidos'
    };
    
    try {
      const response = await fetch(
        `http://127.0.0.1:8000${endpoints[filtros.tipoReporte]}?${params.toString()}`,
        {
          headers: { 'Authorization': `Bearer ${token}` }
        }
      );
      
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `reporte_${filtros.tipoReporte}_${Date.now()}.xlsx`;
      link.click();
      window.URL.revokeObjectURL(url);
    } catch (error) {
      alert('Error al descargar reporte');
    } finally {
      setDescargando(false);
    }
  };

  return (
    <div className="reportes-container">
      <h2>Reportes por Depósito</h2>
      
      <div className="filtros">
        <div className="campo">
          <label>Tipo de Reporte:</label>
          <select 
            value={filtros.tipoReporte}
            onChange={(e) => setFiltros({...filtros, tipoReporte: e.target.value})}
          >
            <option value="productos">Inventario General</option>
            <option value="stockBajo">Stock Bajo</option>
            <option value="proximosVencer">Próximos a Vencer</option>
            <option value="vencidos">Productos Vencidos</option>
          </select>
        </div>

        <div className="campo">
          <label>Depósito:</label>
          <select 
            value={filtros.depositoId}
            onChange={(e) => setFiltros({...filtros, depositoId: e.target.value})}
          >
            <option value="">Todos los depósitos</option>
            {depositos.map(dep => (
              <option key={dep.id} value={dep.id}>
                {dep.nombre} ({dep.productos_count} productos)
              </option>
            ))}
          </select>
        </div>

        <button 
          onClick={descargarReporte}
          disabled={descargando}
          className="btn-descargar"
        >
          {descargando ? 'Descargando...' : 'Descargar Reporte Excel'}
        </button>
      </div>

      <div className="info">
        {filtros.depositoId ? (
          <p>📦 Se descargará el reporte del depósito seleccionado</p>
        ) : (
          <p>📦 Se descargará el reporte de TODOS los depósitos</p>
        )}
      </div>
    </div>
  );
}

export default ReportesDepositos;
```

---

## 🎨 Ejemplo en Vue.js

```vue
<template>
  <div class="reportes-depositos">
    <h2>Reportes por Depósito</h2>
    
    <div class="filtros-card">
      <div class="campo">
        <label>Tipo de Reporte</label>
        <select v-model="filtros.tipoReporte">
          <option value="productos">Inventario General</option>
          <option value="stockBajo">Stock Bajo</option>
          <option value="proximosVencer">Próximos a Vencer</option>
          <option value="vencidos">Productos Vencidos</option>
        </select>
      </div>

      <div class="campo">
        <label>Depósito (Opcional)</label>
        <select v-model="filtros.depositoId">
          <option value="">Todos los depósitos</option>
          <option 
            v-for="dep in depositos" 
            :key="dep.id" 
            :value="dep.id"
          >
            {{ dep.nombre }} ({{ dep.productos_count }} productos)
          </option>
        </select>
      </div>

      <button 
        @click="descargarReporte" 
        :disabled="descargando"
        class="btn-primary"
      >
        <i class="icon-download"></i>
        {{ descargando ? 'Descargando...' : 'Descargar Excel' }}
      </button>
    </div>

    <div class="alert-info" v-if="filtros.depositoId">
      <p>📦 Se generará el reporte solo del depósito seleccionado</p>
    </div>
    <div class="alert-warning" v-else>
      <p>⚠️ Se generará el reporte de TODOS los depósitos</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReportesDepositos',
  
  data() {
    return {
      depositos: [],
      filtros: {
        tipoReporte: 'productos',
        depositoId: ''
      },
      descargando: false
    }
  },
  
  mounted() {
    this.cargarDepositos();
  },
  
  methods: {
    async cargarDepositos() {
      try {
        const response = await fetch(
          `${process.env.VUE_APP_API_URL}/depositos`,
          {
            headers: {
              'Authorization': `Bearer ${this.$store.state.token}`
            }
          }
        );
        
        const data = await response.json();
        if (data.status === 'success') {
          this.depositos = data.data;
        }
      } catch (error) {
        console.error('Error al cargar depósitos:', error);
      }
    },
    
    async descargarReporte() {
      this.descargando = true;
      
      try {
        const params = new URLSearchParams();
        if (this.filtros.depositoId) {
          params.append('deposito_id', this.filtros.depositoId);
        }
        
        const endpoints = {
          'productos': '/reportes/productos',
          'stockBajo': '/reportes/stock-bajo',
          'proximosVencer': '/reportes/proximos-vencer',
          'vencidos': '/reportes/vencidos'
        };
        
        const url = `${process.env.VUE_APP_API_URL}${endpoints[this.filtros.tipoReporte]}?${params.toString()}`;
        
        const response = await fetch(url, {
          headers: {
            'Authorization': `Bearer ${this.$store.state.token}`
          }
        });
        
        if (response.ok) {
          const blob = await response.blob();
          const urlBlob = window.URL.createObjectURL(blob);
          const link = document.createElement('a');
          link.href = urlBlob;
          link.download = `reporte_${this.filtros.tipoReporte}_${Date.now()}.xlsx`;
          link.click();
          window.URL.revokeObjectURL(urlBlob);
          
          this.$toast.success('Reporte descargado exitosamente');
        } else {
          this.$toast.error('Error al descargar reporte');
        }
      } catch (error) {
        console.error('Error:', error);
        this.$toast.error('Error de conexión');
      } finally {
        this.descargando = false;
      }
    }
  }
}
</script>

<style scoped>
.reportes-depositos {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.filtros-card {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.campo {
  margin-bottom: 15px;
}

.campo label {
  display: block;
  font-weight: 600;
  margin-bottom: 5px;
}

.campo select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.btn-primary {
  width: 100%;
  padding: 12px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
}

.btn-primary:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.alert-info {
  background: #d1ecf1;
  padding: 10px;
  border-radius: 4px;
  border-left: 4px solid #0c5460;
}

.alert-warning {
  background: #fff3cd;
  padding: 10px;
  border-radius: 4px;
  border-left: 4px solid #856404;
}
</style>
```

---

## 📝 Casos de Uso

### 1. Descargar inventario de un depósito específico
```bash
GET /api/reportes/productos?deposito_id=4
```
**Uso:** El encargado del Depósito 4 necesita su inventario completo

### 2. Ver productos con stock bajo en un depósito
```bash
GET /api/reportes/stock-bajo?deposito_id=2
```
**Uso:** Revisar qué productos necesitan reposición en el Depósito 2

### 3. Productos próximos a vencer en depósitos de alimentos
```bash
GET /api/reportes/proximos-vencer?deposito_id=9&dias=7
```
**Uso:** Control de vencimientos en depósito de comedor (depósito 9)

### 4. Comparar depósitos
```bash
# Descargar reporte del Depósito 1
GET /api/reportes/productos?deposito_id=1

# Descargar reporte del Depósito 2
GET /api/reportes/productos?deposito_id=2
```
**Uso:** Comparar inventarios entre diferentes depósitos

---

## ✅ Beneficios

✅ **Descarga Selectiva** - Solo los datos del depósito que necesitas  
✅ **Menor Tamaño de Archivo** - Archivos Excel más ligeros y rápidos  
✅ **Organización** - Un reporte por cada depósito  
✅ **Performance** - Consultas más rápidas al filtrar datos  
✅ **Gestión Independiente** - Cada depósito puede gestionar su inventario  
✅ **Reportes Específicos** - Información precisa por ubicación física

---

## 🔐 Permisos Requeridos

- `inventario.ver` → Para listar depósitos
- `reportes.ver` → Para ver reportes
- `reportes.generar` → Para descargar reportes

---

**Fecha de implementación:** 11 de Febrero de 2026
