# 📊 Diagrama de Relaciones - Sistema Estoicos Gym

## Diagrama ER (Entity-Relationship)

```
┌─────────────────────────────────────────────────────────────────┐
│                    SISTEMA ESTOICOS GYM                         │
└─────────────────────────────────────────────────────────────────┘

                        ┌──────────────┐
                        │    ROLES     │
                        ├──────────────┤
                        │ id (PK)      │
                        │ nombre       │
                        │ permisos     │
                        └──────┬───────┘
                               │ 1:N
                               │
                        ┌──────▼───────┐
                        │    USERS     │
                        ├──────────────┤
                        │ id (PK)      │
                        │ name         │
                        │ email        │
                        │ id_rol (FK)  │
                        └──────────────┘


        ┌───────────────┐      1:N    ┌──────────────────┐
        │  CONVENIOS    │◄────────────│    CLIENTES      │
        ├───────────────┤             ├──────────────────┤
        │ id (PK)       │             │ id (PK)          │
        │ nombre        │             │ run_pasaporte    │
        │ tipo          │             │ nombres          │
        │               │             │ apellido_pat     │
        └───────────────┘             │ apellido_mat     │
                                      │ celular          │
                                      │ email            │
                                      │ id_convenio (FK) │
                                      │ activo           │
                                      └────────┬─────────┘
                                               │
                                ┌──────────────┼──────────────┐
                                │              │              │
                         1:N     │       1:N    │        1:N   │
                                 │              │              │
                    ┌────────────▼────┐ ┌──────▼───────┐ ┌───▼─────────┐
                    │ INSCRIPCIONES   │ │    PAGOS     │ │NOTIFICACIONES
                    ├─────────────────┤ ├──────────────┤ ├─────────────┤
                    │ id (PK)         │ │ id (PK)      │ │ id (PK)     │
                    │ fecha_inscr     │ │ monto_total  │ │ tipo        │
                    │ fecha_inicio    │ │ monto_abono  │ │ canal       │
                    │ fecha_vencim    │ │ fecha_pago   │ │ estado      │
                    │ precio_final    │ │ id_inscr(FK) │ │ mensaje     │
                    │ id_cliente(FK)  │ │ id_cliente   │ │ fecha_envio │
                    │ id_membresia(FK)│ │ (FK)         │ └─────────────┘
                    │ id_estado(FK)   │ │ id_metodo    │
                    │ id_precio(FK)   │ │ (FK)         │
                    │ id_motivo(FK)   │ │ id_estado(FK)│
                    └────┬─────────────┘ │ id_motivo(FK)│
                         │               └──────┬───────┘
                         │                      │
        ┌────────────────┼──────────────────────┼──────┐
        │                │                      │      │
        │                │                      │      │
        │         ┌──────▼────────┐    ┌────────▼──┐  │
        │         │   MEMBRESIAS  │    │  METODOS  │  │
        │         ├───────────────┤    ├───────────┤  │
        │         │ id (PK)       │    │ id (PK)   │  │
        │         │ nombre        │    │ nombre    │  │
        │         │ duracion_meses│    └───────────┘  │
        │         │ duracion_dias │                    │
        │         └────────┬──────┘     ┌──────────────┤
        │                  │ 1:N        │
        │    ┌─────────────▼──────────┐ │
        │    │PRECIOS_MEMBRESIAS      │ │
        │    ├────────────────────────┤ │
        │    │ id (PK)                │ │
        │    │ id_membresia (FK)      │ │
        │    │ precio_normal          │ │
        │    │ precio_convenio        │ │
        │    │ fecha_vigencia_desde   │ │
        │    │ fecha_vigencia_hasta   │ │
        │    └────────────────────────┘ │
        │                                │
        │         ┌──────────────────────┤
        │         │                      │
        │    ┌────▼────────────────┐ ┌──▼──────────────┐
        │    │HISTORIAL_PRECIOS    │ │    ESTADOS      │
        │    ├─────────────────────┤ ├─────────────────┤
        │    │ id (PK)             │ │ id (PK)         │
        │    │ id_precio (FK)      │ │ codigo          │
        │    │ precio_anterior     │ │ nombre          │
        │    │ precio_nuevo        │ │ categoria       │
        │    │ fecha_cambio        │ │ 201: Activa     │
        │    │ motivo_cambio       │ │ 202: Vencida    │
        │    └─────────────────────┘ │ 203: Pausada    │
        │                            │ 204: Cancelada  │
        │                            │ 205: Pendiente  │
        └────────────────────────────┼─────────────────┤
                                     │ 301: Pendiente  │
                                     │ 302: Pagado     │
                                     │ 303: Parcial    │
                                     │ 304: Vencido    │
                                     └─────────────────┘


                    ┌──────────────────────┐
                    │MOTIVOS_DESCUENTO     │
                    ├──────────────────────┤
                    │ id (PK)              │
                    │ nombre               │
                    │ - Convenio Estudiante│
                    │ - Promoción Mensual  │
                    │ - Cliente Frecuente  │
                    │ - Acuerdo Especial   │
                    │ - Otro               │
                    └──────────────────────┘


┌──────────────────────────┐
│      AUDITORÍA           │
├──────────────────────────┤
│ id (PK)                  │
│ tabla_afectada           │
│ id_registro_afectado     │
│ accion (INSERT/UPDATE)   │
│ datos_anteriores (JSON)  │
│ datos_nuevos (JSON)      │
│ usuario_id               │
│ fecha_hora               │
└──────────────────────────┘
```

---

## 🔗 Relaciones Detalladas

### 1. Usuario ↔ Rol (N:1)
```
Usuario.id_rol → Rol.id

Un rol puede tener muchos usuarios
Un usuario tiene un rol
```

### 2. Cliente ↔ Convenio (N:1)
```
Cliente.id_convenio → Convenio.id

Un convenio puede tener muchos clientes
Un cliente puede estar asociado a un convenio (nullable)
```

### 3. Cliente ↔ Inscripción (1:N)
```
Cliente.id ← Inscripcion.id_cliente

Un cliente puede tener muchas inscripciones
Una inscripción pertenece a un cliente
```

### 4. Cliente ↔ Pago (1:N)
```
Cliente.id ← Pago.id_cliente

Un cliente puede tener muchos pagos
Un pago es de un cliente
```

### 5. Cliente ↔ Notificación (1:N)
```
Cliente.id ← Notificacion.id_cliente

Un cliente puede recibir muchas notificaciones
Una notificación es para un cliente
```

### 6. Inscripción ↔ Membresia (N:1)
```
Inscripcion.id_membresia → Membresia.id

Una membresía puede ser inscrita por muchos clientes
Una inscripción es de una membresía
```

### 7. Inscripción ↔ PrecioMembresia (N:1)
```
Inscripcion.id_precio_acordado → PrecioMembresia.id

Un precio puede ser usado en muchas inscripciones
Una inscripción usa un precio específico
```

### 8. Inscripción ↔ Estado (N:1)
```
Inscripcion.id_estado → Estado.id

Un estado puede tener muchas inscripciones
Una inscripción tiene un estado (201-205)
```

### 9. Inscripción ↔ MotivoDescuento (N:1)
```
Inscripcion.id_motivo_descuento → MotivoDescuento.id

Un motivo de descuento puede estar en muchas inscripciones
Una inscripción puede tener un motivo de descuento (nullable)
```

### 10. Inscripción ↔ Pago (1:N)
```
Inscripcion.id ← Pago.id_inscripcion

Una inscripción puede tener muchos pagos (parciales)
Un pago es por una inscripción
```

### 11. Inscripción ↔ Notificación (1:N)
```
Inscripcion.id ← Notificacion.id_inscripcion

Una inscripción puede tener muchas notificaciones
Una notificación puede ser para una inscripción (nullable)
```

### 12. Membresia ↔ PrecioMembresia (1:N)
```
Membresia.id ← PrecioMembresia.id_membresia

Una membresía puede tener muchos precios (histórico)
Un precio es para una membresía
```

### 13. PrecioMembresia ↔ HistorialPrecio (1:N)
```
PrecioMembresia.id ← HistorialPrecio.id_precio_membresia

Un precio puede tener muchos cambios en el historial
Un cambio de precio es de un precio
```

### 14. Pago ↔ MetodoPago (N:1)
```
Pago.id_metodo_pago → MetodoPago.id

Un método de pago puede ser usado en muchos pagos
Un pago usa un método de pago
```

### 15. Pago ↔ Estado (N:1)
```
Pago.id_estado → Estado.id

Un estado puede tener muchos pagos
Un pago tiene un estado (301-304)
```

### 16. Pago ↔ MotivoDescuento (N:1)
```
Pago.id_motivo_descuento → MotivoDescuento.id

Un motivo de descuento puede estar en muchos pagos
Un pago puede tener un motivo de descuento (nullable)
```

---

## 📊 Datos por Tipo

### Estados (código 201-205: Inscripciones)
| Código | Nombre | Descripción |
|--------|--------|-------------|
| 201 | Activa | Membresía vigente |
| 202 | Vencida | Membresía expirada |
| 203 | Pausada | Suspendida temporalmente |
| 204 | Cancelada | Cancelada por cliente |
| 205 | Pendiente | Espera inicio futuro |

### Estados (código 301-304: Pagos)
| Código | Nombre | Descripción |
|--------|--------|-------------|
| 301 | Pendiente | No pagado aún |
| 302 | Pagado | Completado |
| 303 | Parcial | Abono realizado |
| 304 | Vencido | Fecha límite pasada |

---

## 🎯 Flujo de Transacciones

### Crear Cliente y Membresía

```
1. Cliente (nuevo)
   ├─ Conveni (opcional)
   └─ Datos personales
       ↓
2. Inscripción (nueva)
   ├─ Membresia (seleccionar)
   ├─ PrecioMembresia (vigente)
   ├─ Estado (201 = Activa)
   ├─ Fecha inicio/vencimiento
   └─ Descuento (opcional)
       ↓
3. Pago (nueva)
   ├─ Monto
   ├─ MetodoPago
   ├─ Estado (301, 302, o 303)
   └─ Referencia/Comprobante
       ↓
4. Auditoría (automática)
   └─ Registra todos los cambios
```

---

## 🔄 Consultas Comunes

### Obtener cliente con membresía activa y pagos
```php
$cliente = Cliente::with([
    'convenio',
    'inscripciones' => function($q) {
        $q->where('id_estado', 201);
    },
    'pagos' => function($q) {
        $q->orderBy('fecha_pago', 'desc');
    }
])->find($id);
```

### Inscripciones por vencer
```php
$venciendo = Inscripcion::where('id_estado', 201)
    ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
    ->with(['cliente', 'membresia', 'pagos'])
    ->get();
```

### Pagos pendientes
```php
$pendientes = Pago::whereIn('id_estado', [301, 303])
    ->with(['cliente', 'inscripcion.membresia', 'metodoPago'])
    ->get();
```

---

## 🛡️ Integridad Referencial

- **ON DELETE RESTRICT**: Evita eliminar registros padre con referencias
- **ON DELETE CASCADE**: Elimina registros relacionados automáticamente
- **ON DELETE SET NULL**: Pone NULL si se elimina el padre (para relaciones opcionales)

```
Estados → Inscripciones/Pagos (RESTRICT)
Clientes → Inscripciones/Pagos (RESTRICT)
Membresias → Precios (RESTRICT)
Convenios → Clientes (SET NULL)
Inscripciones → Pagos (RESTRICT)
Precios → HistorialPrecios (RESTRICT)
```

---

## 📈 Escalabilidad

La estructura permite agregar fácilmente:
- ✅ Tipos de membresía adicionales
- ✅ Nuevos métodos de pago
- ✅ Diferentes tipos de descuentos
- ✅ Nuevas categorías de estados
- ✅ Múltiples convenios
- ✅ Auditoría completa

---

**Documentado**:

