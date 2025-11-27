# 🚀 Desarrollo Frontend - Guía Rápida

## Opción 1: Servidor Simple (Recomendado)

La forma más fácil es usar el script que mantiene el servidor corriendo:

```powershell
.\start-dev.ps1
```

Luego:
1. Abre http://127.0.0.1:8000 en el navegador
2. Edita archivos `.blade.php`, `.css`, `.js`
3. **Actualiza el navegador (F5)** para ver los cambios
4. Presiona Ctrl+C para detener

## Opción 2: Servidor con Auto-Reload (Experimental)

Si quieres que se reinicie automáticamente cuando hagas cambios:

```powershell
.\dev-server.ps1
```

Este script:
- Monitorea cambios en `resources/views`, `public/js`, `public/css`, `app/Http/Controllers`
- Detecta automáticamente cambios
- Reinicia el servidor cuando encuentra cambios
- ⚠️ Puede ser lento con muchos archivos

## Opción 3: Con Valet (Si lo tienes instalado)

```powershell
valet start
# El proyecto está en: http://estoicosgym.test
```

## Flujo de Trabajo Recomendado

### Para cambios en Blade (HTML/Views)
```
1. ✏️  Edita .blade.php
2. 🔄 Presiona F5 en el navegador
3. ✅ Ves los cambios inmediatamente
```

### Para cambios en CSS/JavaScript
```
1. ✏️  Edita .css o .js
2. 🔄 Actualiza navegador con Ctrl+Shift+R (hard refresh)
3. ✅ Ves los cambios
```

### Para cambios en Controllers/Rutas
```
1. ✏️  Edita archivos en app/
2. ⛔ Presiona Ctrl+C en terminal para detener servidor
3. 🚀 Ejecuta .\start-dev.ps1 nuevamente
4. ✅ Los cambios están aplicados
```

## URLs Útiles

- **Admin**: http://127.0.0.1:8000/admin
- **Inscripciones**: http://127.0.0.1:8000/admin/inscripciones
- **Crear Inscripción**: http://127.0.0.1:8000/admin/inscripciones/create
- **Pagos**: http://127.0.0.1:8000/admin/pagos
- **Clientes**: http://127.0.0.1:8000/admin/clientes

## Atajos del Navegador

- **F5**: Actualizar página
- **Ctrl+Shift+R**: Forzar actualización (limpia caché)
- **F12**: Abrir DevTools
- **Ctrl+Shift+M**: Responsive/Mobile mode

## Troubleshooting

### El servidor no arranca
```powershell
# Verifica que PHP esté instalado
php -v

# Mata procesos anteriores
Get-Process php | Stop-Process -Force

# Intenta nuevamente
.\start-dev.ps1
```

### Los cambios no se ven
- Si editaste `.blade.php`: Solo necesitas F5
- Si editaste `.css` o `.js`: Usa Ctrl+Shift+R
- Si aún no ves cambios: Borra caché del navegador

### Puerto 8000 ya está en uso
```powershell
# Edita start-dev.ps1 y cambia:
# $port = 8000  →  $port = 8001

.\start-dev.ps1
```

## Performance Tips

✅ **Haz**:
- Edita directamente el `.blade.php`
- Usa DevTools (F12) para ver errores JavaScript
- Mantén el servidor corriendo en una terminal separada

❌ **Evita**:
- No detengas el servidor cada vez que edites
- No confíes en el caché del navegador (usa Ctrl+Shift+R)
- No edites archivos compilados, siempre edita las fuentes

---

**Próximo paso**: Abre terminal y ejecuta `.\start-dev.ps1` 🚀
