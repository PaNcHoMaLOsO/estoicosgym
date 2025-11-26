# 📊 Estructura de Migraciones - Base de Datos Limpia

## Estado Actual

✅ **BD Vacía y Lista**
✅ **14 Migraciones Consolidadas**
✅ **Numeración Secuencial (0001-0014)**
✅ **Todas las Modificaciones Integradas**

---

## Orden de Migraciones

### Core Laravel (2)
1. **0001_create_users_table** - Tabla de usuarios
2. **0002_create_cache_table** - Cache de Laravel

### Catálogos Base (5)
3. **0003_create_estados_table** - Catálogo de estados (con color integrado)
4. **0004_create_metodos_pago_table** - Métodos de pago disponibles
5. **0005_create_motivos_descuento_table** - Razones de descuento
6. **0006_create_membresias_table** - Planes de membresía (con UUID)
7. **0007_create_precios_membresias_table** - Precios por membresía

### Historial (1)
8. **0008_create_historial_precios_table** - Registro de cambios de precios

### Seguridad (2)
9. **0009_create_roles_table** - Roles de usuarios
10. **0010_add_role_to_users_table** - Relación usuarios-roles

### Entidades Principales (4)
11. **0011_create_convenios_table** - Convenios (con descuentos integrados, UUID)
12. **0012_create_clientes_table** - Clientes (con UUID)
13. **0013_create_inscripciones_table** - Inscripciones (con pausas, convenio, UUID)
14. **0014_create_pagos_table** - Pagos (con cuotas, UUID)

---

## Cambios Consolidados

### ✅ UUIDs Integrados
- **inscripciones** - uuid único
- **pagos** - uuid único
- **membresias** - uuid único
- **clientes** - uuid único
- **convenios** - uuid único

### ✅ Estados
- Color integrado directamente en tabla
- Eliminada migración 0018

### ✅ Inscripciones
- **id_convenio** integrado (anteriormente migración 0015)
- **Campos de pausa** integrados (anteriormente migración 0019):
  - pausada, dias_pausa, fecha_pausa_inicio, fecha_pausa_fin, razon_pausa, pausas_realizadas, max_pausas_permitidas

### ✅ Pagos
- **Campos de cuotas** integrados (anteriormente migración 2025_11_26_054343):
  - cantidad_cuotas, numero_cuota, monto_cuota, fecha_vencimiento_cuota

### ✅ Convenios
- **Descuentos integrados** (anteriormente migración 0016):
  - descuento_porcentaje, descuento_monto

### ✅ Historial de Precios
- Estructura actualizada con campos finales (anteriormente migración 0017):
  - precio_anterior, precio_nuevo, razon_cambio, usuario_cambio

---

## Migraciones Eliminadas

❌ 0015_add_id_convenio_to_inscripciones_table.php
❌ 0016_add_descuentos_to_convenios_table.php
❌ 0017_update_historial_precios_table.php
❌ 0018_add_color_to_estados_table.php
❌ 0019_add_pausa_fields_to_inscripciones_table.php
❌ 2025_11_26_000001_add_uuid_to_inscripciones.php
❌ 2025_11_26_000002_add_uuid_to_pagos.php
❌ 2025_11_26_000003_add_uuid_to_membresias.php
❌ 2025_11_26_000004_add_uuid_to_clientes.php
❌ 2025_11_26_000005_add_uuid_to_convenios.php
❌ 2025_11_26_054343_add_cuotas_to_pagos_table.php

**Total eliminadas:** 11 migraciones redundantes

---

## Estructura Final por Tabla

### estados
- id (PK)
- codigo (UNIQUE)
- nombre
- descripcion
- categoria (enum)
- activo
- **color** ✅ (integrado)
- timestamps

### membresias
- id (PK)
- **uuid** ✅ (integrado)
- nombre (UNIQUE)
- duracion_meses
- duracion_dias
- descripcion
- activo
- timestamps

### inscripciones
- id (PK)
- **uuid** ✅ (integrado)
- id_cliente (FK)
- id_membresia (FK)
- **id_convenio** ✅ (integrado)
- id_precio_acordado (FK)
- fecha_inscripcion
- fecha_inicio
- fecha_vencimiento
- dia_pago
- precio_base, descuento_aplicado, precio_final
- id_motivo_descuento
- id_estado (FK)
- **pausada, dias_pausa, fecha_pausa_inicio, fecha_pausa_fin, razon_pausa, pausas_realizadas, max_pausas_permitidas** ✅ (integrados)
- observaciones
- timestamps
- **Indices:** id_cliente, id_estado, fecha_inicio/vencimiento, pausada, fecha_pausa_fin

### pagos
- id (PK)
- **uuid** ✅ (integrado)
- id_inscripcion (FK)
- id_cliente (FK - denormalizado)
- monto_total, monto_abonado, monto_pendiente
- descuento_aplicado
- id_motivo_descuento (FK)
- fecha_pago
- periodo_inicio, periodo_fin
- id_metodo_pago (FK)
- referencia_pago
- id_estado (FK)
- **cantidad_cuotas, numero_cuota, monto_cuota, fecha_vencimiento_cuota** ✅ (integrados)
- observaciones
- timestamps
- **Indices:** id_cliente, id_inscripcion, fecha_pago, id_estado

### convenios
- id (PK)
- **uuid** ✅ (integrado)
- nombre (UNIQUE)
- tipo (enum)
- **descuento_porcentaje, descuento_monto** ✅ (integrados)
- descripcion
- contacto_nombre, contacto_telefono, contacto_email
- activo
- timestamps

### clientes
- id (PK)
- **uuid** ✅ (integrado)
- run_pasaporte (UNIQUE, nullable)
- nombres
- apellido_paterno
- apellido_materno
- celular
- email
- direccion
- fecha_nacimiento
- contacto_emergencia, telefono_emergencia
- id_convenio (FK, nullable)
- observaciones
- activo
- timestamps

### historial_precios
- id (PK)
- id_precio_membresia (FK)
- **precio_anterior, precio_nuevo** ✅ (estructura actualizada)
- **razon_cambio, usuario_cambio** ✅ (integrados)
- timestamps

---

## Próximos Pasos

### 1. Crear Seeders de Datos Iniciales
```bash
php artisan make:seeder EstadoSeeder
php artisan make:seeder MetodoPagoSeeder
php artisan make:seeder MotivoDescuentoSeeder
php artisan make:seeder RolSeeder
```

### 2. Módulo por Módulo
- **Gestión de Estados** - Crear catálogo inicial
- **Gestión de Membresias** - CRUD completo
- **Gestión de Clientes** - CRUD con validaciones
- **Sistema de Inscripciones** - Con UUIDs y relaciones
- **Sistema de Pagos** - Con cuotas y estados
- **Sistema de Pausas** - Lógica completa

---

## Beneficios de Esta Estructura

✅ **Limpio** - Sin migraciones redundantes
✅ **Organizado** - Numeración secuencial clara
✅ **Escalable** - Fácil de extender
✅ **Coherente** - UUID, colores, pausas, etc. desde el inicio
✅ **Documentado** - Cada migración es completa

---

**Estado:** Base de datos lista para iniciar desarrollo modular
**Commit:** 9cde988
**Fecha:** 2025-11-26
