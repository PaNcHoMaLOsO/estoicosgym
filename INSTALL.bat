@echo off
REM ============================================
REM 🚀 Script de Instalación - EstóicosGym
REM ============================================
REM Para Windows (CMD)

setlocal enabledelayedexpansion

cls
echo ================================================
echo   Cerulimo EstoicosGym - Instalacion Rapida
echo ================================================
echo.

REM Verificar requisitos
echo Verificando requisitos...
php -v >nul 2>&1
if errorlevel 1 (
    echo Error: PHP no esta instalado
    pause
    exit /b 1
)

composer -V >nul 2>&1
if errorlevel 1 (
    echo Error: Composer no esta instalado
    pause
    exit /b 1
)

git --version >nul 2>&1
if errorlevel 1 (
    echo Error: Git no esta instalado
    pause
    exit /b 1
)

echo Requisitos OK
echo.

REM Paso 1
echo Paso 1: Clonando repositorio...
git clone https://github.com/PaNcHoMaLOsO/estoicosgym.git
cd estoicosgym

REM Paso 2
echo.
echo Paso 2: Instalando dependencias...
call composer install

REM Paso 3
echo.
echo Paso 3: Configurando .env...
copy .env.example .env

REM Paso 4
echo.
echo Paso 4: Generando clave...
php artisan key:generate

REM Paso 5
echo.
echo Paso 5: Base de datos
echo Antes de continuar, crea la base de datos:
echo.
echo   mysql -u root -p
echo   CREATE DATABASE estoicosgym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
echo   EXIT;
echo.
set /p confirm="Base de datos creada? (s/n): "
if /i not "%confirm%"=="s" (
    echo Instalacion cancelada
    pause
    exit /b 1
)

REM Paso 6
echo.
echo Paso 6: Ejecutando migraciones...
php artisan migrate

REM Paso 7
echo.
echo Paso 7: Cargando datos de prueba...
php artisan db:seed

REM Paso 8
cls
echo ================================================
echo   INSTALACION COMPLETADA!
echo ================================================
echo.
echo Iniciando servidor...
echo Accede en: http://localhost:8000/dashboard
echo.
echo Para detener: Ctrl + C
echo.
pause

php artisan serve

pause
