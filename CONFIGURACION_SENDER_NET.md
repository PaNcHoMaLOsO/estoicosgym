# 📧 Configuración de Sender.net para Laravel

## 🎯 ¿Por Qué Sender.net?

### **Ventajas sobre Resend:**
- ✅ **2,500 emails gratis/mes** (vs 100 de Resend)
- ✅ **Sin restricciones en modo testing** - Puedes enviar a cualquier email desde el primer día
- ✅ **Verificación de dominio opcional** para comenzar
- ✅ **Panel visual** con estadísticas de apertura y clicks
- ✅ **Plantillas drag & drop** incluidas
- ✅ **Sin tarjeta de crédito** para plan gratuito

---

## 📝 PASO 1: Crear Cuenta en Sender.net

### **1.1. Registro**
1. Ve a https://www.sender.net/
2. Click en **"Start Free Trial"** o **"Sign Up"**
3. Completa el formulario:
   - Email: `estoicosgymlosangeles@gmail.com` (o el que prefieras)
   - Password: (tu contraseña segura)
   - Company Name: `Estoicos Gym`

### **1.2. Verificar Email**
1. Revisa tu bandeja de entrada
2. Click en el link de verificación
3. Completa el perfil básico

---

## 🔑 PASO 2: Obtener Credenciales SMTP

### **2.1. Acceder a Configuración SMTP**
1. Login en https://app.sender.net/
2. Ve a **Settings** → **SMTP Settings**
3. O directo a: https://app.sender.net/settings/smtp

### **2.2. Activar SMTP**
1. Click en **"Enable SMTP"**
2. Verás las credenciales:

```
SMTP Server: smtp.sender.net
Port: 587 (TLS) o 465 (SSL)
Username: (tu email de registro)
Password: (se genera automáticamente)
```

### **2.3. Copiar Credenciales**
**IMPORTANTE:** Copia y guarda estas credenciales, las necesitarás en el siguiente paso.

Ejemplo:
```
Host: smtp.sender.net
Port: 587
Username: estoicosgymlosangeles@gmail.com
Password: SND_abc123xyz456...  (token generado)
```

---

## ⚙️ PASO 3: Configurar Laravel

### **3.1. Actualizar Archivo .env**

Abre tu archivo `.env` y actualiza la sección de MAIL:

```env
# ════════════════════════════════════════════════════════
# CONFIGURACIÓN DE EMAIL - SENDER.NET
# ════════════════════════════════════════════════════════

MAIL_MAILER=smtp
MAIL_HOST=smtp.sender.net
MAIL_PORT=587
MAIL_USERNAME=estoicosgymlosangeles@gmail.com
MAIL_PASSWORD=SND_tu_token_aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=estoicosgymlosangeles@gmail.com
MAIL_FROM_NAME="Estoicos Gym"

# Nota: Cambia MAIL_USERNAME, MAIL_PASSWORD y MAIL_FROM_ADDRESS
# con tus credenciales reales de Sender.net
```

### **3.2. Limpiar Cache de Configuración**

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🧪 PASO 4: Probar Envío

### **4.1. Prueba Rápida con Tinker**

```bash
php artisan tinker
```

```php
// Enviar email de prueba
Mail::raw('Este es un email de prueba desde Sender.net', function ($message) {
    $message->to('estoicosgymlosangeles@gmail.com')
            ->subject('Prueba de Sender.net');
});

// Si no hay errores, salir
exit
```

### **4.2. Probar con Notificaciones Reales**

```bash
# 1. Generar notificaciones
php artisan notificaciones:generar --todo

# 2. Enviar notificaciones
php artisan notificaciones:enviar --enviar
```

### **4.3. Verificar en Panel de Sender.net**

1. Ve a https://app.sender.net/campaigns
2. Deberías ver los emails enviados
3. Puedes ver:
   - ✅ Emails entregados
   - 📊 Tasa de apertura
   - 🖱️ Clicks en enlaces
   - ❌ Rebotes

---

## 🎨 PASO 5: Configuración Avanzada (Opcional)

### **5.1. Verificar Dominio (Recomendado para Producción)**

Si tienes un dominio propio (ej: `estoicosgym.cl`):

1. Ve a **Settings** → **Domains**
2. Click **"Add Domain"**
3. Ingresa tu dominio: `estoicosgym.cl`
4. Agrega los registros DNS que te muestren:

```dns
Tipo    Nombre              Valor
TXT     @                   sender-verification=xxxxx
CNAME   sender._domainkey   sender._domainkey.sender.net
```

5. Espera verificación (5-30 minutos)
6. Actualiza `.env`:

```env
MAIL_FROM_ADDRESS=noreply@estoicosgym.cl
MAIL_FROM_NAME="Estoicos Gym"
```

### **5.2. Configurar Webhooks (Opcional)**

Para recibir eventos de bounces, aperturas, clicks:

1. Ve a **Settings** → **Webhooks**
2. Agrega URL: `https://tudominio.com/webhooks/sender`
3. Selecciona eventos: `delivered`, `opened`, `clicked`, `bounced`

---

## 📊 PASO 6: Monitoreo y Estadísticas

### **6.1. Dashboard de Sender.net**

Accede a https://app.sender.net/ para ver:

- **📧 Emails Enviados:** Total de envíos del mes
- **✅ Tasa de Entrega:** % de emails que llegaron
- **📖 Tasa de Apertura:** % de emails abiertos
- **🖱️ Clicks:** % de clicks en enlaces
- **❌ Rebotes:** Emails que rebotaron
- **📉 Cancelaciones:** Unsubscribes

### **6.2. Ver Logs en Laravel**

```bash
# Ver últimas notificaciones
php artisan tinker
```

```php
// Ver notificaciones enviadas hoy
Notificacion::where('id_estado', 601)
    ->whereDate('fecha_envio', today())
    ->with('cliente')
    ->get()
    ->map(fn($n) => [
        'cliente' => $n->cliente->nombres,
        'email' => $n->email_destino,
        'asunto' => $n->asunto,
        'fecha' => $n->fecha_envio
    ]);
```

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### **Error: "Connection could not be established"**

```env
# Prueba cambiar el puerto
MAIL_PORT=465
MAIL_ENCRYPTION=ssl

# O prueba con TLS
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

### **Error: "Authentication failed"**

1. Verifica que el **Username** sea tu email completo
2. Verifica que el **Password** sea el token de SMTP (empieza con `SND_`)
3. Regenera el token en Sender.net si es necesario

### **Error: "Domain not verified"**

- En plan gratuito puedes usar tu email de registro
- Para dominios personalizados, debes verificarlos primero

### **Emails van a Spam**

1. **Verifica tu dominio** en Sender.net
2. **Agrega SPF y DKIM** (registros DNS)
3. **Evita palabras spam** en asuntos: "GRATIS", "URGENTE", etc.
4. **Incluye link de unsubscribe** en tus emails

---

## 📋 COMPARACIÓN: Resend vs Sender.net

| Característica | Resend | Sender.net |
|----------------|--------|------------|
| **Emails gratis/mes** | 100 | 2,500 |
| **Restricción testing** | ✅ Solo a email verificado | ❌ A cualquier email |
| **Verificación dominio** | Obligatoria | Opcional |
| **Estadísticas** | Básicas | Completas (opens, clicks) |
| **Plantillas visuales** | ❌ No | ✅ Sí (drag & drop) |
| **Webhooks** | ✅ Sí | ✅ Sí |
| **Soporte** | Email | Email + Chat |
| **Panel de control** | Minimalista | Visual completo |

---

## ✅ CHECKLIST DE CONFIGURACIÓN

- [ ] Cuenta creada en Sender.net
- [ ] Email verificado
- [ ] SMTP activado en panel
- [ ] Credenciales copiadas
- [ ] `.env` actualizado con:
  - [ ] `MAIL_HOST=smtp.sender.net`
  - [ ] `MAIL_PORT=587`
  - [ ] `MAIL_USERNAME=(tu email)`
  - [ ] `MAIL_PASSWORD=(token SND_...)`
  - [ ] `MAIL_FROM_ADDRESS=(tu email)`
- [ ] Cache limpiada (`php artisan config:clear`)
- [ ] Prueba con tinker exitosa
- [ ] Notificaciones enviadas correctamente
- [ ] Emails recibidos en bandeja de entrada

---

## 🎯 PRÓXIMOS PASOS

1. **Inmediato:**
   - Configurar Sender.net siguiendo esta guía
   - Probar envío de notificaciones
   - Verificar recepción de emails

2. **Corto Plazo:**
   - Personalizar plantillas de email
   - Agregar logo de Estoicos Gym
   - Mejorar diseño HTML de notificaciones

3. **Producción:**
   - Verificar dominio propio
   - Configurar webhooks
   - Monitorear estadísticas de apertura

---

## 📚 RECURSOS ÚTILES

- **Panel de Sender.net:** https://app.sender.net/
- **Documentación SMTP:** https://help.sender.net/en/articles/smtp-setup
- **Verificación de Dominio:** https://help.sender.net/en/articles/domain-verification
- **Laravel Mail Docs:** https://laravel.com/docs/10.x/mail

---

## 💡 TIPS PRO

### **1. Plantillas HTML Bonitas**
Sender.net tiene un editor drag & drop. Puedes:
1. Crear plantilla en Sender.net
2. Exportar HTML
3. Usar ese HTML en tus notificaciones de Laravel

### **2. Testing Local**
Para desarrollo local sin enviar emails reales:
```env
MAIL_MAILER=log  # Los emails se guardan en storage/logs/laravel.log
```

### **3. Segmentación**
Aprovecha las listas de Sender.net para organizar tus contactos:
- Lista "Membresías Activas"
- Lista "Pagos Pendientes"
- Lista "Clientes VIP"

### **4. Automatizaciones**
Sender.net permite crear automatizaciones visuales:
- Bienvenida → Esperar 3 días → Recordatorio
- Pago pendiente → Esperar 7 días → Recordatorio urgente

---

**🚀 ¡Listo! Con Sender.net tendrás emails profesionales sin restricciones desde el día 1.**
