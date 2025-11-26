#!/usr/bin/env pwsh
<#
    .SYNOPSIS
    Script de setup automático para EstóicosGym

    .DESCRIPTION
    Automatiza los pasos de instalación y configuración del proyecto

    .EXAMPLE
    .\setup.ps1
#>

param(
    [switch]$SkipComposer,
    [switch]$SkipMigrations,
    [switch]$NoSeed
)

$ErrorActionPreference = "Continue"

function Write-Title {
    param([string]$Text)
    Write-Host "`n" -NoNewline
    Write-Host ("=" * 70) -ForegroundColor Cyan
    Write-Host " $Text" -ForegroundColor Green
    Write-Host ("=" * 70) -ForegroundColor Cyan
}

function Write-Step {
    param([string]$Text)
    Write-Host "`n✓ $Text" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Text)
    Write-Host "✗ ERROR: $Text" -ForegroundColor Red
}

function Write-Success {
    param([string]$Text)
    Write-Host "✓ $Text" -ForegroundColor Green
}

# ============================================================
# INICIO
# ============================================================

Write-Title "SETUP ESTOICOS GYM"

Write-Host "`nEste script configurará automáticamente el proyecto."
Write-Host "Asegúrate de tener MySQL ejecutándose."

# ============================================================
# 1. COMPOSER INSTALL
# ============================================================

if (-not $SkipComposer) {
    Write-Step "Instalando dependencias con Composer..."
    try {
        composer install --no-interaction --quiet
        Write-Success "Composer install completado"
    } catch {
        Write-Error "No se pudo ejecutar Composer"
    }
}

# ============================================================
# 2. GENERAR CLAVE
# ============================================================

Write-Step "Generando clave de aplicación..."
try {
    php artisan key:generate --force 2>&1 | Out-Null
    Write-Success "Clave generada"
} catch {
    Write-Error "No se pudo generar clave"
}

# ============================================================
# 3. LIMPIAR CACHÉ
# ============================================================

Write-Step "Limpiando caché..."
try {
    php artisan cache:clear --quiet 2>&1 | Out-Null
    php artisan config:clear --quiet 2>&1 | Out-Null
    Write-Success "Caché limpiado"
} catch {
    Write-Error "No se pudo limpiar caché"
}

# ============================================================
# 4. CREAR BASE DE DATOS (opcional)
# ============================================================

Write-Host "`n📌 BASE DE DATOS"
Write-Host "¿Ya creaste la BD 'dbestoicos' en MySQL? (s/n)" -ForegroundColor Cyan
$response = Read-Host

if ($response -eq "n") {
    Write-Host "`nEjecuta esto en MySQL Command Line:" -ForegroundColor Yellow
    Write-Host "mysql -u root -p" -ForegroundColor Gray
    Write-Host "CREATE DATABASE dbestoicos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" -ForegroundColor Gray
    Write-Host "EXIT;" -ForegroundColor Gray
    Read-Host "`nPresiona Enter cuando hayas creado la BD"
}

# ============================================================
# 5. MIGRACIONES
# ============================================================

if (-not $SkipMigrations) {
    Write-Step "Ejecutando migraciones..."
    try {
        php artisan migrate --force 2>&1 | Out-Null
        Write-Success "Migraciones completadas"
    } catch {
        Write-Error "Error en migraciones. Verifica la BD."
    }
}

# ============================================================
# 6. SEEDERS
# ============================================================

if (-not $NoSeed) {
    Write-Step "Cargando datos de prueba..."
    try {
        php artisan db:seed --force 2>&1 | Out-Null
        Write-Success "Datos de prueba cargados"
    } catch {
        Write-Error "Error al cargar datos"
    }
}

# ============================================================
# RESUMEN
# ============================================================

Write-Title "CONFIGURACIÓN COMPLETADA"

Write-Host "`n✓ Proyecto listo para iniciar"
Write-Host "`n📌 Para iniciar el servidor:"
Write-Host "   php artisan serve" -ForegroundColor Cyan

Write-Host "`n📌 URL de acceso:"
Write-Host "   http://localhost:8000/dashboard" -ForegroundColor Cyan

Write-Host "`n📌 Módulos disponibles:"
Write-Host "   • Clientes: /admin/clientes" -ForegroundColor Gray
Write-Host "   • Inscripciones: /admin/inscripciones" -ForegroundColor Gray
Write-Host "   • Pagos: /admin/pagos" -ForegroundColor Gray
Write-Host "   • Membresías: /admin/membresias" -ForegroundColor Gray

Write-Host "`n" -NoNewline
