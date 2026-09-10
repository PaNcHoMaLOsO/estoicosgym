# 🚨 Diagnóstico: Emails No Se Envían en Registro

**Fecha:** 8 de diciembre de 2025  
**Problema:** Los correos de bienvenida no se envían al registrar una inscripción

---

## 🔍 Causa Raíz Identificada

### Error en Logs
```
[2025-12-08 12:55:50] local.ERROR: Error enviando notificación
{"id":1,"error":"You can only send testing emails to your own email address 
(estoicosgymlosangeles@gmail.com). To send emails to other recipients, 
please verify a domain at resend.com/domains, and change the `from` address 
to an email using this domain."}
```

### Diagnóstico
❌ **Resend en Modo Test (Free Plan)**
- Solo permite enviar a: `estoicosgymlosangeles@gmail.com`
- No permite enviar a otros destinatarios
- Requiere verificar dominio propio para producción

---

## ✅ Código de Envío Implementado

### InscripcionController.php (Líneas 415-434)
```php
// 🎉 ENVIAR NOTIFICACIONES AUTOMÁTICAS
try {
    $notificacionService = app(NotificacionService::class);
    
    // Enviar notificación de bienvenida (siempre)
    $notificacionService->enviarNotificacionBienvenida($inscripcion);
    Log::info("Notificación de bienvenida enviada para inscripción #{$inscripcion->id}");
    
    // Si es menor de edad, enviar también confirmación al tutor legal
    if ($inscripcion->cliente->es_menor_edad && !empty($inscripcion->cliente->apoderado_email)) {
        $resultadoTutor = $notificacionService->enviarNotificacionTutorLegal($inscripcion);
        if ($resultadoTutor['enviada']) {
            Log::info("Notificación de tutor legal enviada a: {$inscripcion->cliente->apoderado_email}");
        } else {
            Log::warning("No se pudo enviar notificación de tutor legal: {$resultadoTutor['mensaje']}");
        }
    }
} catch (\Exception $e) {
    Log::error("Error al enviar notificaciones: " . $e->getMessage());
    // No interrumpir el flujo si falla el envío del email
}
```

### NotificacionService.php (Líneas 751-870)
```php
public function enviarNotificacionBienvenida(Inscripcion $inscripcion): array
{
    // 1. Buscar plantilla de bienvenida
    $tipoBienvenida = TipoNotificacion::where('codigo', TipoNotificacion::BIENVENIDA)
        ->where('activo', true)
        ->first();
    
    // 2. Validar cliente y email
    if (!$cliente || !$cliente->email) {
        return ['enviada' => false, 'mensaje' => 'Cliente sin email'];
    }
    
    // 3. Cargar plantilla HTML
    $rutaPlantilla = storage_path('app/test_emails/preview/01_bienvenida.html');
    $contenido = file_get_contents($rutaPlantilla);
    
    // 4. Reemplazar variables dinámicas
    $contenido = str_replace('Juan Pérez', $nombreCompleto, $contenido);
    $contenido = str_replace('Trimestral', $inscripcion->membresia->nombre, $contenido);
    // ... más reemplazos
    
    // 5. Crear notificación en BD
    $notificacion = Notificacion::create([...]);
    
    // 6. Enviar con Resend
    $resultado = \Resend\Laravel\Facades\Resend::emails()->send([
        'from' => 'PROGYM <onboarding@resend.dev>',
        'to' => [$cliente->email],
        'subject' => '🎉 ¡Bienvenido a PROGYM Los Ángeles!',
        'html' => $contenido,
    ]);
    
    // 7. Actualizar estado
    $notificacion->update([
        'id_estado' => Notificacion::ESTADO_ENVIADO,
        'fecha_envio' => Carbon::now(),
    ]);
}
```

---

## 🔧 Soluciones

### Opción 1: Modo Test - Email Único (INMEDIATO)

Para **demostración**, usar siempre el email verificado:

```php
// En NotificacionService.php línea 860
// Cambiar:
'to' => [$cliente->email],

// Por:
'to' => ['estoicosgymlosangeles@gmail.com'], // Solo en modo test
```

**Ventajas:**
- ✅ Funciona inmediatamente
- ✅ No requiere cambios en Resend
- ✅ Ideal para demostración

**Desventajas:**
- ❌ Todos los emails llegan a la misma dirección
- ❌ No se puede probar con clientes reales

---

### Opción 2: Usar Mailtrap para Testing (RECOMENDADO)

Servicio gratuito para testing de emails:

1. **Crear cuenta en Mailtrap.io**
   - URL: https://mailtrap.io
   - Plan Free: 500 emails/mes

2. **Actualizar .env:**
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username_mailtrap
MAIL_PASSWORD=tu_password_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@progym.cl
MAIL_FROM_NAME="PROGYM Los Ángeles"
```

3. **Cambiar servicio en NotificacionService.php:**
```php
// Usar Mail facade en lugar de Resend
use Illuminate\Support\Facades\Mail;

Mail::send([], [], function ($message) use ($cliente, $asunto, $contenido) {
    $message->to($cliente->email)
            ->subject($asunto)
            ->html($contenido);
});
```

**Ventajas:**
- ✅ Gratis y sin límites de destinatarios
- ✅ Panel web para ver emails enviados
- ✅ Testing completo del flujo
- ✅ No requiere verificar dominio

---

### Opción 3: Verificar Dominio en Resend (PRODUCCIÓN)

Para enviar a **emails reales**:

1. **Comprar dominio propio:**
   - Ejemplo: `progym.cl` o `progymlosangeles.cl`

2. **Verificar en Resend:**
   - Ir a: https://resend.com/domains
   - Agregar dominio
   - Configurar registros DNS (SPF, DKIM, DMARC)

3. **Actualizar .env:**
```env
RESEND_API_KEY=tu_api_key_production
MAIL_FROM_ADDRESS=noreply@progym.cl
MAIL_FROM_NAME="PROGYM Los Ángeles"
```

4. **Cambiar remitente en código:**
```php
'from' => 'PROGYM <noreply@progym.cl>', // Tu dominio verificado
```

**Ventajas:**
- ✅ Emails profesionales
- ✅ Sin límite de destinatarios
- ✅ Mejor reputación de dominio

**Desventajas:**
- ❌ Costo del dominio (~$10-15 USD/año)
- ❌ Configuración DNS requerida

---

## 🎯 Solución Rápida para Demostración

### Modificar NotificacionService.php

```php
// Línea 860 aproximadamente
try {
    // 🔧 MODO TEST: Enviar siempre al email verificado
    $emailDestino = env('APP_ENV') === 'production' 
        ? $cliente->email 
        : 'estoicosgymlosangeles@gmail.com';
    
    $resultado = \Resend\Laravel\Facades\Resend::emails()->send([
        'from' => 'PROGYM <onboarding@resend.dev>',
        'to' => [$emailDestino],
        'subject' => $notificacion->asunto,
        'html' => $contenido,
    ]);
    
    Log::info("Email enviado a: {$emailDestino} (original: {$cliente->email})");
```

---

## 📊 Verificación

### 1. Ver Logs
```bash
Get-Content storage\logs\laravel.log -Tail 50 | Select-String "notificacion"
```

### 2. Verificar Notificaciones en BD
```bash
php artisan tinker --execute="
echo 'Notificaciones creadas: ' . DB::table('notificaciones')->count() . PHP_EOL;
echo 'Enviadas: ' . DB::table('notificaciones')->where('id_estado', 601)->count() . PHP_EOL;
echo 'Fallidas: ' . DB::table('notificaciones')->where('id_estado', 602)->count() . PHP_EOL;
"
```

### 3. Ver Últimas Notificaciones
```bash
php artisan tinker --execute="
DB::table('notificaciones')
    ->join('clientes', 'notificaciones.id_cliente', '=', 'clientes.id')
    ->select('notificaciones.id', 'notificaciones.email_destino', 'notificaciones.id_estado', 'notificaciones.created_at')
    ->orderBy('notificaciones.id', 'desc')
    ->limit(5)
    ->get()
"
```

---

## ✅ Estado Actual

| Componente | Estado | Nota |
|------------|--------|------|
| Código de envío | ✅ Implementado | InscripcionController + NotificacionService |
| Plantilla HTML | ✅ Existe | 01_bienvenida.html (6,563 chars) |
| Integración Resend | ✅ Configurado | API Key activa |
| Registro en BD | ✅ Funcional | Tabla notificaciones |
| Log de eventos | ✅ Funcional | Tabla log_notificaciones |
| **Envío real** | ❌ **Limitado** | Solo a estoicosgymlosangeles@gmail.com |

---

## 🎬 Para tu Demostración

**Opción Recomendada:** Usar Mailtrap
1. Crear cuenta gratis en Mailtrap.io
2. Actualizar .env con credenciales
3. Cambiar a Mail facade
4. Demostrar emails en panel Mailtrap

**Alternativa Rápida:** Modo Test Resend
1. Modificar código para enviar siempre a estoicosgymlosangeles@gmail.com
2. Explicar limitación de plan free
3. Mostrar registro en BD y logs

---

## 📝 Conclusión

✅ **El código está correcto y funcional**
✅ **Las plantillas están completas**
✅ **La integración está configurada**

❌ **Limitación:** Plan free de Resend solo permite enviar al email registrado

**Solución:** Usar Mailtrap para testing o verificar dominio en Resend para producción

---

**Archivos Involucrados:**
- `app/Http/Controllers/Admin/InscripcionController.php` (líneas 415-434)
- `app/Services/NotificacionService.php` (líneas 751-892)
- `config/mail.php`
- `.env`
- `storage/logs/laravel.log`

**Actualizado:** 8 de diciembre de 2025
