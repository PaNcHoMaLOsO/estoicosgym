# Sistema de Gestión - Estoicos Gym

Sistema completo de gestión para gimnasio con control de clientes, membresías, inscripciones y pagos.

## 📋 Requisitos Previos

- PHP 8.1 o superior
- Composer
- MySQL 8.0+
- Node.js 16+ (para Vite)

## 🚀 Instalación y Configuración

### 1. Preparar la Base de Datos en XAMPP

```sql
-- En MySQL (phpMyAdmin de XAMPP)
-- Copiar y ejecutar el script SQL completo proporcionado
-- O ejecutar las migraciones de Laravel (ver paso 3)
```

### 2. Clonar/Descargar el Proyecto

```bash
cd tu-proyecto
```

### 3. Instalar Dependencias

```bash
# Instalar dependencias PHP
composer install

# Instalar dependencias Node.js
npm install
```

### 4. Configurar Archivo .env

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

**Editar .env con la configuración de tu base de datos:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dbestoicos
DB_USERNAME=root
DB_PASSWORD=  # Sin contraseña por defecto en XAMPP
```

### 5. Ejecutar Migraciones

```bash
# Crear las tablas
php artisan migrate

# Hacer seeders con datos iniciales (opcional)
php artisan db:seed
```

### 6. Iniciar el Servidor

```bash
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Compilar assets (Vite)
npm run dev
```

La aplicación estará disponible en: **http://localhost:8000**

## 📁 Estructura del Proyecto

```
app/
├── Models/              # Modelos Eloquent
│   ├── Cliente.php
│   ├── Inscripcion.php
│   ├── Pago.php
│   ├── Membresia.php
│   ├── PrecioMembresia.php
│   ├── MetodoPago.php
│   ├── Estado.php
│   ├── MotivoDescuento.php
│   ├── Convenio.php
│   ├── Notificacion.php
│   ├── Auditoria.php
│   ├── HistorialPrecio.php
│   └── Rol.php
│
├── Http/Controllers/    # Controladores
│   ├── DashboardController.php
│   ├── ClienteController.php
│   ├── InscripcionController.php
│   └── PagoController.php

database/
├── migrations/          # Migraciones de BD
└── seeders/            # Seeders de datos iniciales

resources/views/
├── dashboard/          # Vistas del dashboard
├── clientes/           # Vistas de clientes
├── inscripciones/      # Vistas de inscripciones
└── pagos/             # Vistas de pagos

routes/
└── web.php            # Rutas de la aplicación
```

## 🔧 Configuración de la Base de Datos

### Tablas Principales

1. **estados** - Estados del sistema (activa, vencida, etc.)
2. **metodos_pago** - Métodos de pago disponibles
3. **motivos_descuento** - Razones de descuentos
4. **membresias** - Tipos de membresía
5. **precios_membresias** - Precios vigentes
6. **historial_precios** - Historial de cambios de precio
7. **convenios** - Instituciones con convenio
8. **clientes** - Base de clientes
9. **inscripciones** - Registro de membresías de clientes
10. **pagos** - Registro de pagos
11. **notificaciones** - Notificaciones a clientes
12. **auditoria** - Registro de cambios en el sistema
13. **roles** - Roles de usuarios
14. **users** - Usuarios del sistema (compatible con Laravel Auth)

## 📊 Funcionalidades del Dashboard

### Estadísticas

- **Total de Clientes**: Clientes activos registrados
- **Clientes Activos**: Con membresía vigente
- **Ingresos del Mes**: Total de pagos completados
- **Pagos Pendientes**: Montos por cobrar

### Secciones

1. **Membresías por Vencer**: Próximos 7 días
2. **Ingresos por Método**: Gráfico de métodos de pago
3. **Últimos Pagos**: Últimas 10 transacciones
4. **Clientes Recientes**: Últimos 5 registros
5. **Membresías Más Vendidas**: Ranking del mes

## 🔐 Seguridad

- El proyecto usa roles y permisos para control de acceso
- Las contraseñas se guardan hasheadas
- Sistema de auditoría para rastrear cambios
- Validación en servidor y cliente

## 🗑️ Limpieza de Datos (Soft Delete)

Los modelos usan soft delete por defecto. Para eliminar un registro:

```php
// No elimina físicamente, solo marca como inactivo
$cliente->update(['activo' => false]);

// O usar método destroy
$cliente->destroy($id);
```

## 📱 Relaciones entre Tablas

```
Cliente
  ├── Convenio (belongsTo)
  ├── Inscripciones (hasMany)
  ├── Pagos (hasMany)
  └── Notificaciones (hasMany)

Inscripcion
  ├── Cliente (belongsTo)
  ├── Membresia (belongsTo)
  ├── PrecioMembresia (belongsTo)
  ├── Estado (belongsTo)
  ├── MotivoDescuento (belongsTo)
  ├── Pagos (hasMany)
  └── Notificaciones (hasMany)

Pago
  ├── Inscripcion (belongsTo)
  ├── Cliente (belongsTo)
  ├── MetodoPago (belongsTo)
  ├── Estado (belongsTo)
  └── MotivoDescuento (belongsTo)
```

## 💡 Próximas Funcionalidades

- [ ] Autenticación y login
- [ ] Envío de notificaciones por email/WhatsApp
- [ ] Reporte de vencimientos
- [ ] Sistema de cobros automáticos
- [ ] Integración de pasarelas de pago
- [ ] Estadísticas avanzadas
- [ ] Exportación de reportes (Excel/PDF)
- [ ] APP móvil

## 🐛 Solución de Problemas

### Error: "Base de datos no encontrada"

```bash
php artisan migrate
```

### Error: "Tabla no existe"

```bash
php artisan migrate:fresh --seed
```

### Limpiar caché

```bash
php artisan cache:clear
php artisan config:clear
```

## 📞 Soporte

Para consultas, contactar al administrador del sistema.

---

**Última actualización**: 25 de Noviembre de 2024  
**Versión**: 1.0.0
