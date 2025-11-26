# 📊 Esquema de Base de Datos - EstóicosGym

## 📋 Tablas Principales

### 1. **USUARIOS Y ROLES**

#### `users`
```
id (PK)
run_pasaporte (unique)
nombre
email (unique)
password
id_rol (FK → roles)
activo
created_at
updated_at
```

#### `roles`
```
id (PK)
nombre (unique)
descripcion
created_at
updated_at
```

**Relación**: `users` ↔ `roles` (Many-to-One)

---

### 2. **CLIENTE Y CONVENIOS**

#### `clientes`
```
id (PK)
run_pasaporte (unique)
nombres
apellido_paterno
apellido_materno
celular
email
direccion
fecha_nacimiento
contacto_emergencia
telefono_emergencia
id_convenio (FK → convenios)
observaciones
activo
created_at
updated_at
```

#### `convenios`
```
id (PK)
nombre
descripcion
descuento_porcentaje
descuento_fijo
meses_gratis
activo
created_at
updated_at
```

**Relación**: `clientes` → `convenios` (Many-to-One)

---

### 3. **MEMBRESÍAS Y PRECIOS**

#### `membresias`
```
id (PK)
nombre (unique)
descripcion
duracion_dias
limite_visitantes
activo
created_at
updated_at
```

#### `precios_membresias`
```
id (PK)
id_membresia (FK → membresias)
precio
fecha_inicio
fecha_fin
activo
created_at
updated_at
```

#### `historial_precios`
```
id (PK)
id_precio_membresia (FK → precios_membresias)
precio_anterior
precio_nuevo
fecha_cambio
motivo
created_at
updated_at
```

**Relaciones**:
- `precios_membresias` → `membresias` (Many-to-One)
- `historial_precios` → `precios_membresias` (Many-to-One)

---

### 4. **INSCRIPCIONES (CORE)**

#### `inscripciones`
```
id (PK)
id_cliente (FK → clientes)
id_membresia (FK → membresias)
id_convenio (FK → convenios)
id_precio_acordado (FK → precios_membresias)
fecha_inscripcion
fecha_inicio
fecha_vencimiento
dia_pago (1-31)
precio_base (decimal)
descuento_aplicado (decimal)
precio_final (decimal)
id_motivo_descuento (FK → motivos_descuento)
id_estado (FK → estados) [1-9: membresía]
observaciones
pausada (boolean)
dias_pausa (7, 14, 30)
fecha_pausa_inicio
fecha_pausa_fin
razon_pausa
pausas_realizadas
max_pausas_permitidas
created_at
updated_at
```

#### `estados`
```
id (PK)
nombre
descripcion
tipo (membresía|pago)
color (hex)
created_at
updated_at
```

**Estados Membresía** (ID: 1-9):
- 1: Activa
- 2: Pausada - 7d
- 3: Pausada - 14d
- 4: Pausada - 30d
- 5: Vencida
- 6: Cancelada
- 7-9: Otros

#### `motivos_descuento`
```
id (PK)
nombre
descripcion
activo
created_at
updated_at
```

**Relaciones**:
- `inscripciones` → `clientes` (Many-to-One)
- `inscripciones` → `membresias` (Many-to-One)
- `inscripciones` → `convenios` (Many-to-One)
- `inscripciones` → `precios_membresias` (Many-to-One)
- `inscripciones` → `estados` (Many-to-One) [1-9]
- `inscripciones` → `motivos_descuento` (Many-to-One)

---

### 5. **PAGOS**

#### `pagos`
```
id (PK)
id_inscripcion (FK → inscripciones)
id_cliente (FK → clientes)
monto_total (decimal)
monto_abonado (decimal)
fecha_pago
comprobante (file path)
observaciones
id_metodo_pago (FK → metodos_pago)
id_estado (FK → estados) [101-108: pago]
created_at
updated_at
```

#### `metodos_pago`
```
id (PK)
nombre
descripcion
activo
created_at
updated_at
```

**Estados Pago** (ID: 101-108):
- 101: Pendiente
- 102: Pagado
- 103: Parcial
- 104: Vencido
- 105-108: Otros

**Relaciones**:
- `pagos` → `inscripciones` (Many-to-One)
- `pagos` → `clientes` (Many-to-One)
- `pagos` → `metodos_pago` (Many-to-One)
- `pagos` → `estados` (Many-to-One) [101-108]

---

### 6. **AUDITORÍA Y NOTIFICACIONES**

#### `auditoria`
```
id (PK)
id_usuario (FK → users)
tabla
accion (CREATE, UPDATE, DELETE)
datos_anteriores (JSON)
datos_nuevos (JSON)
ip_address
user_agent
created_at
```

#### `notificaciones`
```
id (PK)
id_cliente (FK → clientes)
id_inscripcion (FK → inscripciones)
tipo (vencimiento, pausa, pago, etc)
titulo
contenido
leido (boolean)
created_at
updated_at
```

**Relaciones**:
- `auditoria` → `users` (Many-to-One)
- `notificaciones` → `clientes` (Many-to-One)
- `notificaciones` → `inscripciones` (Many-to-One)

---

## 🔗 Diagrama ER Simplificado

```
┌─────────────────────────────────────────────────────────────┐
│                        USUARIOS                              │
├──────────────────────────────────────────────────────────────┤
│ users                                                         │
│ ├─ id (PK)                                                   │
│ ├─ id_rol (FK) ──────┐                                      │
│ └─ ...               │                                      │
│                      │                                      │
└──────────────────────┼──────────────────────────────────────┘
                       │
                       ▼
            ┌─────────────────┐
            │     roles       │
            │ ├─ id (PK)      │
            │ └─ nombre       │
            └─────────────────┘


┌──────────────────────────────────────────────────────────────┐
│                      CLIENTES                                 │
├──────────────────────────────────────────────────────────────┤
│ clientes                                                      │
│ ├─ id (PK)                                                   │
│ ├─ id_convenio (FK) ──────┐                                 │
│ └─ ...                    │                                 │
│                           │                                 │
└───────────────────────────┼──────────────────────────────────┘
                            │
                            ▼
                  ┌─────────────────┐
                  │  convenios      │
                  │ ├─ id (PK)      │
                  │ └─ nombre       │
                  └─────────────────┘


┌──────────────────────────────────────────────────────────────┐
│                    MEMBRESÍAS                                 │
├──────────────────────────────────────────────────────────────┤
│ membresias                    precios_membresias             │
│ ├─ id (PK)                   ├─ id (PK)                     │
│ ├─ nombre ◄────────────────── id_membresia (FK)             │
│ └─ ...                       ├─ precio                      │
│                              └─ ...                         │
│                                  │                          │
│                                  ▼                          │
│                      ┌───────────────────────┐               │
│                      │ historial_precios     │               │
│                      │ ├─ id (PK)            │               │
│                      │ └─ id_precio (FK) ────┘               │
│                      └───────────────────────┘               │
└──────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│                      INSCRIPCIONES (CORE)                            │
├─────────────────────────────────────────────────────────────────────┤
│ inscripciones                                                        │
│ ├─ id (PK)                                                          │
│ ├─ id_cliente (FK) ────────────► clientes                          │
│ ├─ id_membresia (FK) ─────────► membresias                         │
│ ├─ id_convenio (FK) ──────────► convenios                          │
│ ├─ id_precio_acordado (FK) ───► precios_membresias                 │
│ ├─ id_estado (FK: 1-9) ───────► estados [membresía]                │
│ ├─ id_motivo_descuento (FK) ──► motivos_descuento                  │
│ ├─ pausada (boolean)                                                 │
│ ├─ fecha_pausa_fin                                                   │
│ └─ ...                                                               │
└─────────────────────────────────────────────────────────────────────┘
                            │
                            ▼
            ┌───────────────────────────┐
            │       pagos               │
            │ ├─ id (PK)                │
            │ ├─ id_inscripcion (FK) ──┘
            │ ├─ id_cliente (FK) ───────► clientes
            │ ├─ id_metodo_pago (FK) ──► metodos_pago
            │ ├─ id_estado (FK: 101-108)─► estados [pago]
            │ └─ ...                    │
            └───────────────────────────┘


┌────────────────────────────────────────────────────────────┐
│              AUDITORÍA Y NOTIFICACIONES                    │
├────────────────────────────────────────────────────────────┤
│ auditoria                    notificaciones                │
│ ├─ id (PK)                  ├─ id (PK)                   │
│ ├─ id_usuario (FK) ──────► users │  ├─ id_cliente (FK)     │
│ ├─ tabla                      │  ├─ id_inscripcion (FK) ──┘
│ └─ ...                        │  └─ ...                   │
│                               ▼                            │
│                         clientes & inscripciones           │
└────────────────────────────────────────────────────────────┘
```

---

## 🔑 Resumen de Relaciones

| Tabla | Relación | Tabla Destino | Tipo |
|-------|----------|---------------|------|
| users | id_rol | roles | M:1 |
| clientes | id_convenio | convenios | M:1 |
| precios_membresias | id_membresia | membresias | M:1 |
| historial_precios | id_precio_membresia | precios_membresias | M:1 |
| inscripciones | id_cliente | clientes | M:1 |
| inscripciones | id_membresia | membresias | M:1 |
| inscripciones | id_convenio | convenios | M:1 |
| inscripciones | id_precio_acordado | precios_membresias | M:1 |
| inscripciones | id_estado | estados (1-9) | M:1 |
| inscripciones | id_motivo_descuento | motivos_descuento | M:1 |
| pagos | id_inscripcion | inscripciones | M:1 |
| pagos | id_cliente | clientes | M:1 |
| pagos | id_metodo_pago | metodos_pago | M:1 |
| pagos | id_estado | estados (101-108) | M:1 |
| notificaciones | id_cliente | clientes | M:1 |
| notificaciones | id_inscripcion | inscripciones | M:1 |
| auditoria | id_usuario | users | M:1 |

---

## 📌 Notas Importantes

1. **Estados**: Tabla única con `tipo` para diferenciar:
   - Membresía: IDs 1-9
   - Pago: IDs 101-108

2. **Inscripciones**: Tabla central con todas las relaciones críticas

3. **Pagos**: Relacionados directamente con Inscripciones y Clientes

4. **Pausa**: Campos en inscripciones para control de pausas

5. **Auditoría**: Registra todos los cambios por usuario

6. **Notificaciones**: Alertas automáticas para clientes e inscripciones

---

**Total: 16 tablas | 20 migraciones | 114 commits**
