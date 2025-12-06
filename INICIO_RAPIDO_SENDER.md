# 🚀 INICIO RÁPIDO - Sender.net en 5 Minutos

## ⚡ Pasos Resumidos

### **1. Crear Cuenta (2 minutos)**
```
1. Ve a: https://www.sender.net/
2. Click "Sign Up"
3. Email: estoicosgymlosangeles@gmail.com
4. Verifica tu email
```

### **2. Obtener Credenciales SMTP (1 minuto)**
```
1. Login en: https://app.sender.net/
2. Ve a: Settings → SMTP Settings
3. Click "Enable SMTP"
4. Copia las credenciales que aparecen
```

### **3. Configurar Laravel (1 minuto)**

Abre tu archivo `.env` y actualiza:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sender.net
MAIL_PORT=587
MAIL_USERNAME=estoicosgymlosangeles@gmail.com
MAIL_PASSWORD=SND_abc123...tu_token_aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=estoicosgymlosangeles@gmail.com
MAIL_FROM_NAME="Estoicos Gym"
```

Limpia cache:
```bash
php artisan config:clear
```

### **4. Probar Conexión (30 segundos)**

```bash
php test_sender.php
```

Si ves ✅, revisa tu email. Deberías tener un mensaje de prueba.

### **5. Enviar Notificaciones Reales (30 segundos)**

```bash
# Generar notificaciones
php artisan notificaciones:generar --todo

# Enviar notificaciones
php artisan notificaciones:enviar --enviar
```

---

## ✅ Si Todo Funciona

Deberías ver:
```
✅ 3 notificaciones enviadas
✓ Juan Carlos - Membresía por vencer
✓ Ana María - Membresía por vencer  
✓ María José - Membresía vencida
```

Y en el panel de Sender.net (https://app.sender.net/campaigns) verás los emails enviados.

---

## ❌ Si Hay Errores

### **Error de Conexión**
```bash
# Prueba con SSL en lugar de TLS
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

### **Error de Autenticación**
```bash
# Verifica que copiaste bien el token
# Debe empezar con: SND_
# No debe tener espacios antes/después
```

### **No Llegan los Emails**
```bash
# Revisa spam/correo no deseado
# Espera 1-2 minutos
# Verifica en: https://app.sender.net/campaigns
```

---

## 📊 Monitoreo

**Panel de Sender.net:**
https://app.sender.net/

Verás:
- 📧 Emails enviados
- ✅ Entregados
- 📖 Abiertos
- 🖱️ Clicks

---

## 🆘 Soporte

- **Documentación completa:** `CONFIGURACION_SENDER_NET.md`
- **Panel Sender.net:** https://app.sender.net/
- **Configuración SMTP:** https://app.sender.net/settings/smtp

---

**🎯 Total: 5 minutos y listo para enviar emails sin restricciones!**
