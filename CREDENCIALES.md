# 🔐 Credenciales de Acceso - Sistema PROGYM

## Usuarios del Sistema

### 👨‍💼 Administrador
- **Email:** `admin@progym.cl`
- **Contraseña:** `password`
- **Rol:** Administrador (id_rol: 1)
- **Permisos:** Acceso completo al sistema

### 👤 Recepcionista
- **Email:** `recepcion@progym.cl`
- **Contraseña:** `password`
- **Rol:** Recepcionista (id_rol: 2)
- **Permisos:** Gestión operativa

---

## 🌐 Acceso al Sistema

**URL Local:**
```
http://localhost:8000/admin
```

**Comando para iniciar servidor:**
```bash
php artisan serve
```

---

## ℹ️ Información Técnica

### Contraseña Hasheada
Las contraseñas se almacenan con **bcrypt** (Laravel default):
```php
Hash::make('password')
```

### Factory
Las credenciales se configuran en:
```
database/factories/UserFactory.php
```

Línea 30:
```php
'password' => static::$password ??= Hash::make('password'),
```

### Seeder
Los usuarios se crean en:
```
database/seeders/DatabaseSeeder.php
```

Líneas 43-53:
```php
User::factory()->create([
    'name' => 'Administrador',
    'email' => 'admin@progym.cl',
    'id_rol' => 1,
]);

User::factory()->create([
    'name' => 'Recepcionista',
    'email' => 'recepcion@progym.cl',
    'id_rol' => 2,
]);
```

---

## 🔄 Restablecer Contraseñas

Si necesitas cambiar las contraseñas, ejecuta:

```bash
php artisan tinker
```

Luego:
```php
$user = User::where('email', 'admin@progym.cl')->first();
$user->password = Hash::make('nueva_password');
$user->save();
```

---

## ✅ Verificación

Para verificar que los usuarios existen en la base de datos:

```bash
php artisan tinker --execute="
\$users = DB::table('users')->select('name', 'email')->get();
foreach (\$users as \$u) {
    echo \$u->name . ' (' . \$u->email . ')' . PHP_EOL;
}
"
```

**Resultado esperado:**
```
Administrador (admin@progym.cl)
Recepcionista (recepcion@progym.cl)
```

---

## 📝 Notas

- ⚠️ **Producción:** Cambiar contraseñas por defecto
- 🔒 **Seguridad:** Las contraseñas están hasheadas con bcrypt
- 📧 **Email:** No es necesario verificación de email en desarrollo
- 🔑 **Recuperación:** Sistema de recuperación de contraseña pendiente (RF-01)

---

**Actualizado:** 8 de diciembre de 2025
