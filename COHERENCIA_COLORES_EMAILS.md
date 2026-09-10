# 🎨 Sistema de Coherencia de Colores - Plantillas de Email PROGYM

> **Actualizado.** El rojo y el negro pasaron a ser los del logotipo
> (`#d81f26` y `#0a0a0b`), los mismos que usa el panel. Antes las plantillas
> llevaban `#E0001A` y `#101010`: eran dos rojos distintos en correos del mismo
> gimnasio. Los colores que SIGNIFICAN algo —el verde de «pagado», el ambar de
> «por vencer»— no se tocaron: se eligieron para leerse en un cliente de correo.


## 📋 Paleta de Colores por Estado

### 🟢 Verde (#2EB872) - Estados Positivos/Completados
**Uso:** Confirmaciones exitosas, pagos completados, reactivaciones

**Plantillas que lo usan:**
- ✅ **pago_completado**: Saldo en verde cuando está en $0
- ▶️ **activacion_inscripcion**: Box de estado "Activa" y botón CTA

**Ejemplos visuales:**
```html
<!-- Box de estado activo -->
<div style="background: #f0fdf4; border-left: 4px solid #2EB872;">
    <h3 style="color: #2EB872;">▶️ Activa</h3>
</div>

<!-- Botón CTA positivo -->
<a href="tel:..." style="background: #2EB872; color: white;">
    📞 Llámanos: +56 9 5096 3143
</a>
```

---

### 🟡 Amarillo (#FFC107) - Advertencias/Atención
**Uso:** Estados temporales, pausas, alertas que requieren atención pero no son urgentes

**Plantillas que lo usan:**
- ⏰ **membresia_por_vencer**: Box de advertencia "Próximo a vencer"
- ⏸️ **pausa_inscripcion**: Box de estado "Pausada" y botón CTA

**Ejemplos visuales:**
```html
<!-- Box de pausa -->
<div style="background: #fffbf0; border-left: 4px solid #FFC107;">
    <h3 style="color: #FFC107;">⏸️ Pausada</h3>
</div>

<!-- Botón CTA atención -->
<a href="tel:..." style="background: #FFC107; color: #0a0a0b;">
    📞 Llámanos: +56 9 5096 3143
</a>
```

---

### 🔴 Rojo (#d81f26) - Urgente/Acción Requerida
**Uso:** Vencimientos, deudas, estados que requieren acción inmediata

**Plantillas que lo usan:**
- ⚠️ **membresia_vencida**: Box de alerta "Membresía Vencida"
- 💳 **pago_pendiente**: Saldo pendiente y llamado a acción

**Ejemplos visuales:**
```html
<!-- Box de urgencia -->
<div style="background: #fff5f5; border-left: 4px solid #d81f26;">
    <h3 style="color: #d81f26;">⚠️ Membresía Vencida</h3>
</div>

<!-- Saldo pendiente -->
<p style="color: #d81f26; font-size: 26px; font-weight: bold;">
    $25.000
</p>

<!-- Botón CTA urgente -->
<a href="tel:..." style="background: #d81f26; color: #FFFFFF;">
    📞 Llámanos: +56 9 5096 3143
</a>
```

---

### ⚫ Negro (#0a0a0b) - Principal/Header
**Uso:** Header principal, textos principales, contraste fuerte

**Aplicación:**
- Header con logo PROGYM en todas las plantillas
- Títulos principales y textos de énfasis
- Footer en todas las plantillas

**Ejemplo:**
```html
<!-- Header universal -->
<div style="background: #0a0a0b; color: white;">
    <h1 style="font-family: Arial Black;">
        <span style="color: #FFFFFF;">PRO</span>
        <span style="color: #d81f26;">GYM</span>
    </h1>
</div>
```

---

### ⚪ Gris (#F5F5F5 / #C7C7C7) - Fondos Suaves/Secundarios
**Uso:** Backgrounds de información secundaria, textos de menor jerarquía

**Aplicación:**
- Boxes de horarios
- Información complementaria
- Textos secundarios en footer

---

## 📊 Resumen de Optimizaciones Aplicadas

### ✅ Cambios Implementados

#### 1. **Reducción de Espacios**
- Header: 50px → 30px (padding vertical)
- Content: 40px → 25px (padding)
- Boxes informativos: 25px → 18px (padding)
- Margins entre secciones: 30px → 20px

#### 2. **Eliminación de Contenido Redundante**
- ❌ Removed: Lista extensa de tips (✅ Llega 10-15 min antes, etc.)
- ✅ Kept: Solo horarios compactos en box pequeño con borde izquierdo

#### 3. **Coherencia Visual**
- Todos los boxes usan `border-left: 4px solid [color]` consistentemente
- Títulos de boxes alineados con el color del estado
- Botones CTA mantienen coherencia: Verde=Positivo, Amarillo=Pausa, Rojo=Urgente

#### 4. **Mejora en Datos Financieros (pago_completado)**
```html
<!-- ANTES (Confuso) -->
Monto abonado: $10.000  <!-- Solo último pago -->

<!-- DESPUÉS (Claro) -->
Pago de hoy: $10.000      <!-- Último pago específico -->
Total pagado: $25.000     <!-- Total acumulado -->
```

---

## 🎯 Plantillas por Tipo de Notificación

| Plantilla | Color Principal | Estado | Optimizada |
|-----------|----------------|---------|------------|
| **bienvenida** | Negro/Verde | Nuevo ingreso | ✅ COMPLETA |
| **membresia_por_vencer** | Amarillo | Advertencia | ✅ COMPLETA |
| **membresia_vencida** | Rojo | Urgente | ✅ COMPLETA |
| **pago_pendiente** | Rojo | Deuda | ✅ COMPLETA |
| **pausa_inscripcion** | Amarillo | Temporal | ✅ COMPLETA |
| **activacion_inscripcion** | Verde | Reactivado | ✅ COMPLETA |
| **pago_completado** | Verde | Completado | ✅ COMPLETA |

---

## 📏 Estándares de Diseño

### Padding Universal
```css
/* Header */
padding: 30px 20px;

/* Content principal */
padding: 25px 20px;

/* Boxes informativos */
padding: 18px;
margin: 20px 0;
border-radius: 6px;
```

### Tipografía
```css
/* Títulos H2 */
font-size: 22px;
margin: 0 0 15px 0;

/* Títulos H3 (boxes) */
font-size: 18px;
margin: 0 0 10px 0;

/* Texto normal */
font-size: 15px;
line-height: 1.6;

/* Texto secundario */
font-size: 13-14px;
```

### Botones CTA
```css
padding: 14px 35px;
border-radius: 6px;
font-size: 15px;
font-weight: bold;
margin: 20px 0 15px 0;
```

---

## 🧪 Testing y Verificación

### Archivos HTML Generados
Se encuentran en: `storage/app/test_emails/`

**Archivos actuales:**
1. `01_bienvenida.html` - Pago completo ($25.000 pagado, $0 pendiente)
2. `02_bienvenida.html` - Pago parcial ($40.000 pagado, $25.000 pendiente)
3. `03_bienvenida.html` - Sin pago ($0 pagado, $120.000 pendiente)
4. `04_bienvenida.html` - Pago mixto ($100.000 pagado, $100.000 pendiente)
5. `05_pago_completado.html` - Completó hoy ($25.000)

### Checklist de Verificación Visual
- [ ] Variables cargadas correctamente (nombres, montos, fechas)
- [ ] Colores coherentes según estado (Verde/Amarillo/Rojo)
- [ ] Espaciado reducido, menos scroll
- [ ] Sin contenido redundante
- [ ] Horarios compactos visibles
- [ ] Botones CTA funcionales (tel: links)
- [ ] Footer consistente con info de contacto

---

## 📞 Información de Contacto en Todas las Plantillas

**Teléfono:** +56 9 5096 3143  
**Email:** progymlosangeles@gmail.com  
**Instagram:** @progym_losangeles  
**Ubicación:** [Google Maps](https://www.google.com/maps/place/Gimnasio+ProGym)

---

## 🚀 Próximos Pasos

1. ✅ ~~Optimizar las 7 plantillas con coherencia de colores~~
2. ⚠️ Completar `ClientesTestSeeder` con clientes 6-10:
   - `test.porvencer@progym.test` → membresia_por_vencer
   - `test.vencido@progym.test` → membresia_vencida
   - `test.deuda@progym.test` → pago_pendiente
   - `test.pausado@progym.test` → pausa_inscripcion
   - `test.reactivado@progym.test` → activacion_inscripcion
3. ⚠️ Generar HTMLs de los 5 escenarios faltantes
4. ⚠️ Enviar emails de prueba con `simular:notificaciones`

---

## 📝 Notas Técnicas

### Comandos Útiles
```bash
# Regenerar plantillas
php artisan db:seed --class=NotificacionesSeeder

# Limpiar clientes de test
php artisan limpiar:clientes-test --force

# Generar clientes de test
php artisan db:seed --class=ClientesTestSeeder

# Generar HTMLs para verificación visual
php artisan test:email-visual --html

# Ver qué notificaciones recibiría cada cliente (SIN enviar)
php artisan verificar:notificaciones --limit=20 --solo-test

# Enviar emails de prueba reales
php artisan simular:notificaciones test.nuevo@progym.test
```

### Ubicación de Archivos
- **Seeder plantillas:** `database/seeders/NotificacionesSeeder.php` (572 líneas)
- **Comando visualización:** `app/Console/Commands/TestEmailVisualizacionCommand.php`
- **Comando verificación:** `app/Console/Commands/VerificarNotificacionesCommand.php`
- **Comando simulación:** `app/Console/Commands/SimularNotificacionesCommand.php`
- **Seeder clientes:** `database/seeders/ClientesTestSeeder.php` (334 líneas)
- **HTMLs generados:** `storage/app/test_emails/`

---

**Última actualización:** Optimización completa con sistema de coherencia de colores  
**Estado:** 7 plantillas optimizadas ✅ | 5 clientes test activos | Pendiente clientes 6-10
