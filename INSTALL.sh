#!/bin/bash
# 🚀 Script de Instalación Rápida - EstóicosGym

echo "================================================"
echo "  💪 EstóicosGym - Instalación Rápida"
echo "================================================"
echo ""

# Verificar requisitos
echo "✓ Verificando requisitos..."
php -v > /dev/null 2>&1 || { echo "❌ PHP no instalado"; exit 1; }
composer -V > /dev/null 2>&1 || { echo "❌ Composer no instalado"; exit 1; }
git --version > /dev/null 2>&1 || { echo "❌ Git no instalado"; exit 1; }

echo "✓ Todos los requisitos detectados"
echo ""

# Paso 1: Clonar
echo "📥 Paso 1: Clonando repositorio..."
git clone https://github.com/PaNcHoMaLOsO/estoicosgym.git
cd estoicosgym

# Paso 2: Instalar Composer
echo ""
echo "📦 Paso 2: Instalando dependencias..."
composer install

# Paso 3: Configurar .env
echo ""
echo "⚙️  Paso 3: Configurando .env..."
cp .env.example .env

# Paso 4: Generar clave
echo ""
echo "🔐 Paso 4: Generando clave de aplicación..."
php artisan key:generate

# Paso 5: Información de BD
echo ""
echo "📊 Paso 5: Base de datos"
echo "   Antes de continuar, crea la base de datos:"
echo ""
echo "   mysql -u root -p"
echo "   CREATE DATABASE estoicosgym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "   EXIT;"
echo ""
read -p "   ¿Base de datos creada? (s/n): " respuesta

if [ "$respuesta" != "s" ]; then
  echo "❌ Instalación cancelada"
  exit 1
fi

# Paso 6: Migraciones
echo ""
echo "🗄️  Paso 6: Ejecutando migraciones..."
php artisan migrate

# Paso 7: Seeders
echo ""
echo "🌱 Paso 7: Cargando datos de prueba..."
php artisan db:seed

# Paso 8: Servidor
echo ""
echo "================================================"
echo "  ✅ ¡Instalación completada!"
echo "================================================"
echo ""
echo "🚀 Iniciando servidor..."
echo "   Accede en: http://localhost:8000/dashboard"
echo ""
echo "Para detener el servidor: Ctrl + C"
echo ""

php artisan serve
