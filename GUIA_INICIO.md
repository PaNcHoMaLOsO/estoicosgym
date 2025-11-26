# 🚀 GUÍA DE INICIO DEL PROYECTO

**EstóicosGym** - Sistema de Gestión de Membresías para Gimnasios

---

## ✅ Estado Actual

El proyecto está **100% listo** y optimizado:
- ✓ Código limpio (23 archivos innecesarios eliminados)
- ✓ Dependencias instaladas
- ✓ Clave de aplicación generada
- ✓ Configuración `.env` establecida
- ✓ Documentación actualizada

---

## 🔧 Pasos para Iniciar

### 1️⃣ Crear Base de Datos (Primera vez)

Abre **MySQL Command Line** o **phpMyAdmin** y ejecuta:

```sql
CREATE DATABASE dbestoicos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**O si usas línea de comandos:**

```bash
mysql -u root -p
```

Ingresa tu contraseña (si la tienes) y luego:

```sql
CREATE DATABASE dbestoicos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

---

### 2️⃣ Ejecutar Migraciones

En **PowerShell** o **CMD**, en la carpeta del proyecto:

```bash
php artisan migrate
```

**Resultado esperado:** "Migration table created successfully" y luego migraciones ejecutadas

---

### 3️⃣ Cargar Datos de Prueba

```bash
php artisan db:seed
```

**Datos que se crearán:**
- 5 Estados (Activa, Vencida, Pausada, Cancelada, Pendiente)
- 5 Métodos de Pago
- 5 Motivos de Descuento
- 6 Membresías disponibles
- 50 Clientes de prueba
- 100+ Inscripciones
- 300+ Pagos

---

### 4️⃣ Iniciar Servidor

```bash
php artisan serve
```

**Resultado:**

```
INFO  Server running on [http://127.0.0.1:8000]
Press Ctrl+C to stop the server
```

---

## 🌐 Acceder al Sistema

### URL Principal
```
http://localhost:8000
```

### Dashboard
```
http://localhost:8000/dashboard
```

### Módulos
- **Clientes:** `http://localhost:8000/admin/clientes`
- **Inscripciones:** `http://localhost:8000/admin/inscripciones`
- **Pagos:** `http://localhost:8000/admin/pagos`
- **Membresías:** `http://localhost:8000/admin/membresias`

---

## 📊 Variables de Entorno (.env)

Si necesitas cambiar configuración:

```env
APP_NAME="Estoicos Gym"
APP_ENV=local              # Cambiar a 'production' en producción
APP_DEBUG=true             # Cambiar a 'false' en producción
APP_URL=http://localhost:8000

DB_DATABASE=dbestoicos
DB_USERNAME=root
DB_PASSWORD=               # Ingresa tu contraseña si la tienes
```

---

## 🐛 Troubleshooting

### Error: "SQLSTATE[HY000] [2002]"
**Problema:** MySQL no está corriendo

**Solución:**
- **Windows (XAMPP):** Abre XAMPP y haz clic en "Start" en Apache y MySQL
- **Linux:** `sudo systemctl start mysql`

### Error: "No such file or directory"
**Problema:** Archivo `.env` no existe

**Solución:**
```bash
copy .env.example .env
php artisan key:generate
```

### Error: "Base de datos no encontrada"
**Problema:** No creaste la base de datos

**Solución:** Ejecuta el comando SQL del paso 1

### Error en migraciones
**Solución completa:**
```bash
php artisan migrate:reset
php artisan migrate
php artisan db:seed
```

---

## 🛠️ Comandos Útiles

```bash
# Servidor
php artisan serve                      # Iniciar servidor
php artisan serve --port=8001          # Puerto personalizado

# Base de datos
php artisan migrate                    # Ejecutar migraciones
php artisan migrate:reset              # Resetear todo (cuidado!)
php artisan db:seed                    # Cargar datos de prueba

# Caché
php artisan cache:clear                # Limpiar caché
php artisan config:clear               # Limpiar configuración
php artisan view:clear                 # Limpiar vistas

# Debugging
php artisan tinker                     # Consola interactiva
php artisan route:list                 # Ver todas las rutas

# Logs
tail -f storage/logs/laravel.log       # Ver logs en tiempo real (Linux/Mac)
Get-Content storage/logs/laravel.log -Tail 50 -Wait  # PowerShell
```

---

## 📝 Usuarios de Prueba

Los seeders crean automáticamente:

### Usuario Admin
- **Email:** admin@estoicos.gym
- **Nombre:** Administrador
- **Rol:** Admin

### Usuario Recepcionista
- **Email:** recepcionista@estoicos.gym
- **Nombre:** Recepcionista
- **Rol:** Recepcionista

---

## 📁 Estructura del Proyecto

```
estoicosgym/
├── app/
│   ├── Http/Controllers/    (Controladores)
│   ├── Models/              (Modelos Eloquent)
│   ├── Traits/              (Traits reutilizables)
│   └── Helpers/             (Funciones helper)
├── database/
│   ├── migrations/          (Migraciones BD)
│   └── seeders/             (Datos de prueba)
├── resources/views/
│   └── admin/               (Vistas AdminLTE)
├── routes/
│   └── web.php              (Rutas)
├── config/                  (Configuración)
├── .env                     (Variables de entorno)
├── composer.json            (Dependencias)
└── README.md                (Documentación)
```

---

## 🎯 Próximos Pasos Después de Iniciar

1. **Explorar el Dashboard** - Ver estadísticas en tiempo real
2. **Crear clientes** - Módulo de gestión de clientes
3. **Crear inscripciones** - Asignar membresías a clientes
4. **Registrar pagos** - Seguimiento de pagos
5. **Usar pausa de membresía** - Sistema de pausa por 7, 14 o 30 días
6. **Ver reportes** - Dashboard con gráficos y estadísticas

---

## 📞 Ayuda

Si encuentras problemas:

1. **Revisar logs:** `storage/logs/laravel.log`
2. **Consola del navegador:** Presiona `F12`
3. **Terminal:** El servidor muestra errores en tiempo real
4. **Documentación:**
   - `README.md` - Guía completa
   - `API_DOCUMENTATION.md` - Endpoints API
   - `DATABASE_SCHEMA.md` - Estructura de BD

---

**¡Proyecto listo para usar!** 🎉

Para cualquier duda, revisa la documentación completa en `README.md`.
