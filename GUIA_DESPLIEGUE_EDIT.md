# 🚀 Guía de Despliegue - Edit Cliente Refactorizado

## 📋 Pre-Despliegue

### Verificaciones Previas
- [x] Código testeado localmente
- [x] Sin errores en consola
- [x] Validaciones funcionan
- [x] SweetAlert2 instalado
- [x] Rutas backend disponibles
- [x] Documentación completa
- [x] Backup de código anterior

---

## 🔧 Paso 1: Backup de Archivos Originales

### Linux/Mac
```bash
# Crear directorio backup
mkdir -p ~/backups/estiocosgym/$(date +%Y%m%d)

# Copiar archivo original
cp resources/views/admin/clientes/edit.blade.php \
   ~/backups/estiocosgym/$(date +%Y%m%d)/edit.blade.php.bak
```

### Windows (PowerShell)
```powershell
# Crear directorio backup
$backupDir = "C:\Backups\estoicosgym\$(Get-Date -f 'yyyyMMdd')"
New-Item -ItemType Directory -Path $backupDir -Force

# Copiar archivo
Copy-Item `
  "resources\views\admin\clientes\edit.blade.php" `
  "$backupDir\edit.blade.php.bak"
```

---

## 📁 Paso 2: Copiar Archivos Refactorizados

### Archivo Principal
```bash
# El archivo ya está en:
resources/views/admin/clientes/edit.blade.php

# Verificar que existe:
ls -l resources/views/admin/clientes/edit.blade.php
```

### Documentación (Opcional pero Recomendado)
```bash
# Copiar documentos de referencia:
cp REFACTORING_EDIT_CLIENTE.md ./docs/
cp VERIFICACION_EDIT_CLIENTE.md ./docs/
cp VISUAL_GUIDE_EDIT_CLIENTE.md ./docs/
cp TESTING_EDIT_CLIENTE.md ./docs/
```

---

## 🔐 Paso 3: Verificar Rutas Backend

### Laravel Routes Requeridas

Verificar que estas rutas existan en `routes/web.php`:

```php
Route::middleware('auth')->group(function () {
    Route::resource('admin.clientes', ClienteController::class);
    
    // Rutas específicas para desactivar/reactivar
    Route::patch('admin/clientes/{id}/desactivate', [ClienteController::class, 'desactivate'])->name('admin.clientes.desactivate');
    Route::patch('admin/clientes/{id}/reactivate', [ClienteController::class, 'reactivate'])->name('admin.clientes.reactivate');
});
```

### Si las rutas no existen

Agregarlas a `routes/web.php`:

```php
// En el grupo de rutas autenticadas
Route::group(['middleware' => ['auth']], function () {
    // ... otras rutas ...
    
    // Editar cliente
    Route::resource('admin.clientes', ClienteController::class);
    
    // Nuevas rutas para desactivación/reactivación
    Route::patch('admin/clientes/{cliente}/desactivate', 'Admin\ClienteController@desactivate')
        ->name('admin.clientes.desactivate');
    Route::patch('admin/clientes/{cliente}/reactivate', 'Admin\ClienteController@reactivate')
        ->name('admin.clientes.reactivate');
});
```

---

## 🎯 Paso 4: Verificar Controlador ClienteController

### Métodos Requeridos en `app/Http/Controllers/Admin/ClienteController.php`

```php
class ClienteController extends Controller
{
    // ... otros métodos ...
    
    /**
     * Actualizar cliente (PUT)
     */
    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email' => 'required|email|max:100',
            'celular' => 'required|string|max:20',
            'run_pasaporte' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'contacto_emergencia' => 'nullable|string|max:100',
            'telefono_emergencia' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
            'id_convenio' => 'nullable|exists:convenios,id',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $cliente->update($validated);

        return redirect()
            ->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente actualizado correctamente');
    }

    /**
     * Desactivar cliente (PATCH)
     */
    public function desactivate(Cliente $cliente)
    {
        $cliente->update(['activo' => false]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente desactivado correctamente');
    }

    /**
     * Reactivar cliente (PATCH)
     */
    public function reactivate(Cliente $cliente)
    {
        $cliente->update(['activo' => true]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente reactivado correctamente');
    }
}
```

---

## ⚙️ Paso 5: Verificar SweetAlert2

### CDN en `resources/views/layouts/app.blade.php`

```html
<!-- En la sección HEAD o BODY -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

### O instalado via NPM

```bash
# Si lo deseas instalar localmente
npm install sweetalert2

# Luego importar en tu app.js
import Swal from 'sweetalert2'
window.Swal = Swal
```

---

## 🧪 Paso 6: Testing Pre-Despliegue

### Test Local en Staging

```bash
# 1. Iniciar servidor Laravel
php artisan serve

# 2. Navegar a
# http://localhost:8000/admin/clientes/{id}/edit

# 3. Verificar:
# - Página carga sin errores ✓
# - Datos del cliente se muestran ✓
# - Campos se validan ✓
# - Alertas SweetAlert2 funcionan ✓
# - Cambios sin guardar detectados ✓
```

---

## 📤 Paso 7: Despliegue a Producción

### Opción A: Manual (FTP/SSH)

```bash
# Conectar por SSH
ssh usuario@servidor.com

# Navegar a directorio del proyecto
cd /var/www/estoicosgym

# Hacer backup
cp resources/views/admin/clientes/edit.blade.php \
   resources/views/admin/clientes/edit.blade.php.bak.$(date +%Y%m%d_%H%M%S)

# Copiar archivo refactorizado
# (Usar FTP o git para copiar el archivo)
git pull origin main
# O via SCP:
# scp resources/views/admin/clientes/edit.blade.php usuario@servidor:/var/www/estoicosgym/resources/views/admin/clientes/
```

### Opción B: Git (Recomendado)

```bash
# En tu máquina local
git add resources/views/admin/clientes/edit.blade.php
git commit -m "refactor: refactorización profesional de edit.blade.php

- Eliminar formulario anidado (HTML inválido)
- Implementar 5 alertas SweetAlert2
- Agregar validaciones robustas
- Detección de cambios sin guardar
- Diseño responsive profesional
- 320 líneas CSS con variables
- 10 secciones de formulario bien organizadas
"
git push origin main

# En el servidor (en rama main)
cd /var/www/estoicosgym
git pull origin main
```

### Opción C: Docker (Si aplica)

```dockerfile
# En tu Dockerfile
COPY resources/views/admin/clientes/edit.blade.php \
     /app/resources/views/admin/clientes/

# Rebuild y redeploy
docker-compose build
docker-compose up -d
```

---

## ✅ Paso 8: Post-Despliegue

### Verificaciones

```bash
# 1. Verificar archivo existe
ls -la resources/views/admin/clientes/edit.blade.php

# 2. Limpiar cache Laravel
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# 3. Verificar permisos
chmod 644 resources/views/admin/clientes/edit.blade.php

# 4. Compilar assets (si tienes)
npm run build
# O
php artisan mix
```

### Testing Post-Despliegue

1. **Abrir navegador**
   - Ir a `https://tudominio.com/admin/clientes/{id}/edit`

2. **Verificar carga**
   - Página carga sin errores
   - Estilos CSS se aplican
   - JavaScript funciona

3. **Verificar funcionalidad**
   - Editar datos
   - Validaciones trabajan
   - SweetAlert2 aparece
   - Desactivar/Reactivar funciona

4. **Verificar responsive**
   - Abrir DevTools (F12)
   - Cambiar a device mobile
   - Verificar que se ve bien

---

## 🔄 Paso 9: Rollback (Si Es Necesario)

### Si hay problemas

```bash
# Restaurar backup
cp resources/views/admin/clientes/edit.blade.php.bak \
   resources/views/admin/clientes/edit.blade.php

# Limpiar cache
php artisan cache:clear
php artisan view:clear

# O si usas Git
git revert HEAD --no-edit
git push origin main
```

---

## 📊 Monitoreo Post-Despliegue

### Verificar Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx logs (si aplica)
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# O si usas Apache
tail -f /var/log/apache2/error.log
```

### Monitorear en Navegador

1. Abrir DevTools (F12)
2. Ir a pestaña Console
3. Buscar errores (rojo)
4. Revisar Network (red lenta?)
5. Verificar Performance

---

## 🎯 Checklist de Despliegue

- [ ] Backup del archivo original creado
- [ ] Rutas backend verificadas y disponibles
- [ ] Controlador tiene métodos desactivate/reactivate
- [ ] SweetAlert2 está incluido
- [ ] Testing local completado exitosamente
- [ ] Archivo copiado a servidor
- [ ] Cache de Laravel limpiado
- [ ] Permisos de archivo correctos
- [ ] Página carga en producción
- [ ] Validaciones funcionan
- [ ] SweetAlert2 aparece
- [ ] Alertas responden correctamente
- [ ] Mobile responsive verificado
- [ ] Logs revisados (sin errores)
- [ ] Usuarios notificados de cambios

---

## 📞 Soporte Post-Despliegue

### Si encuentras problemas

**1. Página no carga**
- Revisar logs: `storage/logs/laravel.log`
- Verificar permisos: `chmod 644 edit.blade.php`
- Limpiar cache: `php artisan view:clear`

**2. Estilos no se aplican**
- Limpiar cache CSS: `php artisan view:clear`
- Verificar DevTools si hay CORS errors
- Revisar `public/css/` si hay assets compilados

**3. JavaScript no funciona**
- Abrir console (F12) y buscar errores
- Verificar que SweetAlert2 esté cargado (buscar `window.Swal`)
- Revisar rutas en formulario

**4. SweetAlert2 no aparece**
- Verificar CDN está accesible: https://cdn.jsdelivr.net/npm/sweetalert2@11
- O que está instalado si es local
- Revisar console por errores

**5. Validaciones no funcionan**
- Abrir console y buscar errores JavaScript
- Verificar que campos tengan `id` correcto
- Revisar que `editClienteForm` existe

---

## 🎊 ¡Despliegue Completado!

Una vez verificado:

```
✅ Archivo en producción
✅ Funcionalidad verificada
✅ Responsive testeado
✅ Logs limpios
✅ Usuarios notificados

STATUS: 🟢 EN PRODUCCIÓN
```

---

## 📚 Referencia Rápida

### Rutas Usadas
```
GET    /admin/clientes/{id}/edit       → Mostrar formulario
PUT    /admin/clientes/{id}            → Actualizar datos
PATCH  /admin/clientes/{id}/desactivate → Desactivar
PATCH  /admin/clientes/{id}/reactivate → Reactivar
```

### Validaciones
```
Email:    ^[^\s@]+@[^\s@]+\.[^\s@]+$
RUT:      ^(\d{1,2}\.)?\d{3}\.\d{3}-[0-9kK]$|^\d+$
Requeridos: nombres, apellido_paterno, email, celular
```

### Funciones JavaScript Principales
```javascript
handleEditFormSubmit(event)
confirmarGuardiarCambios(event)
confirmarDesactivacion(clienteId, clienteNombre)
confirmarReactivacion(event)
confirmarCancelar(event)
validarEmail(input)
validarRutAjax(input)
```

---

**Despliegue completado exitosamente!** 🎉
