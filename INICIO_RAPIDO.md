# 🚀 INICIO RÁPIDO - EstóicosGym

## ⏱️ 5 Minutos para Comenzar

### 1️⃣ Clonar Proyecto
```bash
git clone https://github.com/PaNcHoMaLOsO/estoicosgym.git
cd estoicosgym
```

### 2️⃣ Instalar Dependencias
```bash
composer install
```

### 3️⃣ Configurar .env
```bash
cp .env.example .env
```

**Editar `.env` con tu configuración de BD:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=estoicosgym
DB_USERNAME=root
DB_PASSWORD=
```

### 4️⃣ Generar Clave
```bash
php artisan key:generate
```

### 5️⃣ Crear Base de Datos
```bash
mysql -u root -p
CREATE DATABASE estoicosgym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 6️⃣ Migraciones
```bash
php artisan migrate
php artisan db:seed
```

### 7️⃣ Iniciar Servidor
```bash
php artisan serve
```

### 8️⃣ ¡Acceder!
Abrir navegador: **`http://localhost:8000/dashboard`** ✨

---

## 📥 Qué Necesitas Descargar

### Requisitos Obligatorios:
1. **PHP 8.2+** 
   - Windows: [php.net/downloads](https://www.php.net/downloads)
   - Linux: `apt-get install php8.2 php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl`

2. **Composer 2.x**
   - [getcomposer.org/download](https://getcomposer.org/download/)

3. **MySQL 8.0+**
   - Windows: [mysql.com/downloads](https://www.mysql.com/downloads/)
   - Linux: `apt-get install mysql-server`
   - Mac: `brew install mysql`

4. **Git**
   - [git-scm.com/download](https://git-scm.com/download/)

---

## 🤖 Scripts de Instalación Automática

### Windows
```bash
INSTALL.bat
```

### Linux/Mac
```bash
bash INSTALL.sh
```

---

## ✅ Verificar Instalación

```bash
# PHP
php --version

# Composer
composer --version

# MySQL
mysql --version

# Git
git --version
```

---

## 🔗 Enlaces Útiles

- **Dashboard:** `http://localhost:8000/dashboard`
- **Clientes:** `http://localhost:8000/admin/clientes`
- **Inscripciones:** `http://localhost:8000/admin/inscripciones`
- **Pagos:** `http://localhost:8000/admin/pagos`

---

## 📞 ¿Problemas?

Ver sección **"Problemas Comunes"** en `README.md`

---

**¡Listo! Sistema en 5 minutos** ⚡
