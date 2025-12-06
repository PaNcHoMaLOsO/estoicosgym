# 📧 Sistema de Emails PROGYM - Configuración y Pruebas

## ✅ Estado: LISTO PARA PRODUCCIÓN

### 📬 Configuración de Email

**Servicio:** Resend API  
**Email verificado:** estoicosgymlosangeles@gmail.com  
**API Key:** Configurada en `.env`

```env
MAIL_MAILER=resend
RESEND_API_KEY=re_aj8Luxpg_GYFuWYicXrWGB4QEi1qaU3o4
MAIL_FROM_ADDRESS="onboarding@resend.dev"
MAIL_FROM_NAME="PROGYM Los Ángeles"
```

### 🎨 Plantillas Disponibles

Todas las plantillas están pre-cargadas en la base de datos y listas para usar:

1. **Bienvenida** (`bienvenida`)
   - Confirmación de inscripción
   - Color: Verde #2EB872
   - Variables: nombre, membresía, fecha_inicio, fecha_vencimiento, precio

2. **Membresía por Vencer** (`membresia_por_vencer`)
   - Recordatorio días antes del vencimiento
   - Color: Amarillo #FFC107
   - Variables: nombre, membresía, dias_restantes, fecha_vencimiento

3. **Membresía Vencida** (`membresia_vencida`)
   - Alerta de membresía expirada
   - Color: Rojo #E0001A (alerta completa)
   - Variables: nombre, membresía, fecha_vencimiento

4. **Pago Pendiente** (`pago_pendiente`)
   - Recordatorio de pago parcial/pendiente
   - Color: Rojo #E0001A (borde)
   - Variables: nombre, membresía, monto_pendiente, monto_total, fecha_vencimiento

### 🧪 Comando de Prueba

Para probar el envío de emails usa el comando:

```bash
php artisan test:email [email] [tipo_plantilla]
```

**Ejemplos:**
```bash
# Enviar email de bienvenida
php artisan test:email estoicosgymlosangeles@gmail.com bienvenida

# Enviar recordatorio de membresía por vencer
php artisan test:email estoicosgymlosangeles@gmail.com membresia_por_vencer

# Enviar alerta de membresía vencida
php artisan test:email estoicosgymlosangeles@gmail.com membresia_vencida

# Enviar recordatorio de pago pendiente
php artisan test:email estoicosgymlosangeles@gmail.com pago_pendiente
```

### 🖼️ Logo PROGYM

**Ubicación:** `public/images/progym_logo.svg`  
**URL en emails:** `https://raw.githubusercontent.com/PaNcHoMaLOsO/estoicosgym/main/public/images/progym_logo.svg`

**⚠️ Nota sobre el fondo del logo:**
Si el logo SVG aparece con fondo blanco en lugar de transparente, es porque el archivo SVG contiene un rectángulo de fondo blanco. Para solucionarlo:

1. Abrir el archivo `progym_logo.svg` en un editor de texto
2. Buscar elementos `<rect fill="#FFFFFF">` o similar
3. Eliminar el rectángulo de fondo o cambiar `fill` a `none`
4. Guardar y actualizar en GitHub

Alternativamente, puedes:
- Reemplazar el SVG con una versión sin fondo
- Usar PNG con fondo transparente
- Editar en software de diseño (Inkscape, Illustrator) y exportar sin fondo

### 🎨 Paleta de Colores PROGYM

| Elemento | Color | Hex | Uso |
|----------|-------|-----|-----|
| Header/Footer | Negro carbón | #101010 | Identidad fuerte |
| Botones CTA | Rojo energía | #E0001A | Llamadas a la acción |
| Éxito | Verde | #2EB872 | Confirmaciones |
| Recordatorio | Amarillo | #FFC107 | Alertas suaves |
| Texto principal | Negro/Gris | #101010 / #505050 | Lectura |
| Bordes | Gris acero | #C7C7C7 | Separadores |

### 📞 Datos de Contacto

```
Email: progymlosangeles@gmail.com
Teléfono: +56 9 5096 3143
WhatsApp: https://wa.me/56950963143
Instagram: @progym_losangeles
Google Maps: https://www.google.com/maps/place/Gimnasio+ProGym
```

### 🚀 Para Migración a Producción

1. **Verificar dominio en Resend:**
   - Ir a resend.com/domains
   - Agregar tu dominio personalizado (ej: progym.cl)
   - Configurar registros DNS (SPF, DKIM)
   - Esperar verificación

2. **Actualizar .env en producción:**
   ```env
   MAIL_FROM_ADDRESS="contacto@tudominio.cl"
   MAIL_FROM_NAME="PROGYM Los Ángeles"
   ```

3. **Ejecutar migraciones:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=NotificacionesSeeder --force
   ```

4. **Probar en producción:**
   ```bash
   php artisan test:email tu@email.com bienvenida
   ```

### ✅ Estado de Pruebas

- [x] Plantilla Bienvenida - ✅ Enviada exitosamente
- [x] Plantilla Membresía por Vencer - ✅ Enviada exitosamente  
- [x] Plantilla Membresía Vencida - ✅ Enviada exitosamente
- [x] Plantilla Pago Pendiente - ✅ Enviada exitosamente
- [x] Integración con Resend - ✅ Funcionando
- [x] Variables dinámicas - ✅ Reemplazo correcto
- [x] Logo en emails - ✅ URL funcional
- [x] Datos de contacto - ✅ Actualizados

### 📝 Notas Adicionales

- Las plantillas están en la tabla `tipo_notificaciones`
- El servicio `NotificacionService` se encarga del envío automático
- Los logs de email se guardan en `storage/logs/laravel.log` (modo local)
- Resend tiene límite de 3000 emails/mes en plan gratuito
- Para producción considera plan de pago si necesitas más volumen

---

**Última actualización:** 5 de diciembre de 2025  
**Version:** 1.0.0  
**Estado:** ✅ PRODUCCIÓN READY
