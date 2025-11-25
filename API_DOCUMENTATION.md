# EstóicosGym - API Documentation

## Base URL
`http://localhost:8000/api`

---

## Dashboard APIs

### Obtener Estadísticas Principales
**GET** `/dashboard/stats`
```json
{
  "clientes": {
    "total": 55,
    "activos": 55,
    "inactivos": 0
  },
  "inscripciones": {
    "total": 134,
    "activas": 98,
    "vencidas": 12,
    "pausadas": 3
  },
  "pagos": {
    "mes_actual": 5000,
    "total": 25000,
    "vencidos": 2,
    "cantidad_pagos": 146,
    "promedio_pago": 171.23
  }
}
```

### Ingresos por Mes (últimos 12 meses)
**GET** `/dashboard/ingresos-mes`
```json
{
  "labels": ["Ene 2025", "Feb 2025", ...],
  "data": [5000, 6200, 4500, ...]
}
```

### Inscripciones por Estado
**GET** `/dashboard/inscripciones-estado`
```json
{
  "labels": ["Activa", "Vencida", "Pausada"],
  "data": [98, 12, 3],
  "colors": ["#28a745", "#dc3545", "#ffc107"]
}
```

### Membresías Populares
**GET** `/dashboard/membresias-populares`
```json
[
  {"nombre": "Plan Mensual", "inscripciones": 45, "duracion_dias": 30},
  {"nombre": "Plan Trimestral", "inscripciones": 32, "duracion_dias": 90}
]
```

### Métodos de Pago Populares
**GET** `/dashboard/metodos-pago`
```json
[
  {"metodo": "Transferencia", "cantidad": 80, "monto_total": 15000},
  {"metodo": "Efectivo", "cantidad": 50, "monto_total": 8000}
]
```

### Últimos Pagos
**GET** `/dashboard/ultimos-pagos`
```json
[
  {
    "id": 146,
    "cliente": "Juan Pérez",
    "monto": 500,
    "fecha": "25/11/2025 14:30",
    "metodo": "Transferencia",
    "estado": "Pagado",
    "estado_color": "success"
  }
]
```

### Inscripciones Próximas a Vencer
**GET** `/dashboard/proximas-vencer`
```json
[
  {
    "id": 10,
    "cliente": "María González",
    "membresia": "Plan Mensual",
    "vencimiento": "30/11/2025",
    "dias_restantes": 5,
    "urgencia": "alta"
  }
]
```

### Resumen de Clientes
**GET** `/dashboard/resumen-clientes`
```json
{
  "total": 55,
  "activos": 55,
  "inactivos": 0,
  "con_inscripcion": 50,
  "sin_inscripcion": 5,
  "por_convenio": 12
}
```

---

## Clientes APIs

### Listar Clientes Activos
**GET** `/clientes`
```json
[
  {
    "id": 1,
    "nombre_completo": "Juan Pérez",
    "run": "12.345.678-9",
    "email": "juan@email.com",
    "celular": "+569 1234 5678",
    "inscripciones_activas": 2
  }
]
```

### Obtener Cliente Específico
**GET** `/clientes/{id}`
```json
{
  "id": 1,
  "nombres": "Juan",
  "apellido_paterno": "Pérez",
  "apellido_materno": "García",
  "run": "12.345.678-9",
  "email": "juan@email.com",
  "celular": "+569 1234 5678",
  "direccion": "Calle 123, Departamento 4",
  "fecha_nacimiento": "1990-05-15",
  "activo": true,
  "convenio": "Convenio Empresarial A",
  "inscripciones": [...]
}
```

### Buscar Clientes
**GET** `/clientes/search?q=juan`
```json
[
  {
    "id": 1,
    "text": "Juan Pérez (12.345.678-9)",
    "email": "juan@email.com",
    "celular": "+569 1234 5678"
  }
]
```

### Estadísticas del Cliente
**GET** `/clientes/{id}/stats`
```json
{
  "total_inscripciones": 3,
  "inscripciones_activas": 2,
  "total_pagado": 5000,
  "cantidad_pagos": 8,
  "monto_promedio_pago": 625
}
```

---

## Membresias APIs

### Listar Membresías Activas
**GET** `/membresias`
```json
[
  {
    "id": 1,
    "nombre": "Plan Mensual",
    "duracion_dias": 30,
    "duracion_meses": 1,
    "descripcion": "Acceso ilimitado por 30 días",
    "precio_normal": 500,
    "precio_convenio": 400
  }
]
```

### Buscar Membresías
**GET** `/membresias/search?q=mensual`
```json
[
  {
    "id": 1,
    "text": "Plan Mensual (30 días)",
    "nombre": "Plan Mensual",
    "duracion_dias": 30,
    "precio_normal": 500,
    "precio_convenio": 400
  }
]
```

### Obtener Membresía Específica
**GET** `/membresias/{id}`
```json
{
  "id": 1,
  "nombre": "Plan Mensual",
  "duracion_dias": 30,
  "duracion_meses": 1,
  "descripcion": "Acceso ilimitado por 30 días",
  "precio_normal": 500,
  "precio_convenio": 400,
  "activo": true,
  "inscripciones_count": 45
}
```

---

## Inscripciones APIs

### Calcular Precio Final y Fecha Vencimiento
**POST** `/inscripciones/calcular`
```json
{
  "id_membresia": 1,
  "id_convenio": null,
  "fecha_inicio": "2025-11-25",
  "precio_base": 500
}
```

**Response:**
```json
{
  "fecha_vencimiento": "2025-12-25",
  "descuento_aplicado": 0,
  "precio_final": 500
}
```

### Obtener Descuento por Convenio
**GET** `/convenios/{id}/descuento`
```json
{
  "id": 1,
  "nombre": "Convenio Empresarial A",
  "descuento_porcentaje": 10,
  "descuento_monto": 50
}
```

---

## Búsqueda General

### Buscar Clientes
**GET** `/clientes/search?q=juan`

### Buscar Inscripciones
**GET** `/inscripciones/search?q=plan`

---

## Notas Importantes

1. **Autenticación**: Actualmente sin requerimientos (agregar en futuro)
2. **Paginación**: APIs retornan 10-50 resultados por defecto
3. **Formato de Fechas**: YYYY-MM-DD para solicitudes, d/m/Y para respuestas
4. **Moneda**: Todos los montos en CLP (pesos chilenos)
5. **Colores**: Bootstrap colors (success, danger, warning, info, primary, secondary)

---

## Ejemplos de Uso

### JavaScript/Fetch
```javascript
// Obtener estadísticas
fetch('/api/dashboard/stats')
  .then(r => r.json())
  .then(data => console.log(data));

// Buscar cliente
fetch('/api/clientes/search?q=juan')
  .then(r => r.json())
  .then(data => console.log(data));
```

### cURL
```bash
# Obtener ingresos por mes
curl http://localhost:8000/api/dashboard/ingresos-mes

# Listar clientes
curl http://localhost:8000/api/clientes

# Obtener cliente específico
curl http://localhost:8000/api/clientes/1
```
