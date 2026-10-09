# Arranca PRO GYM en este equipo: Docker (con la base), el servidor y el panel.
#
# DOCKER DESKTOP SE TRABA AL ARRANCAR: queda un archivo de la sesión anterior
# en %LOCALAPPDATA%\Docker\run que no se deja borrar, y el motor nunca sube
# (ver docs/INSTALACION.md). Si Docker no responde, se cierra, se renombra esa
# carpeta y se vuelve a abrir. Se usa con doble clic en «Arrancar PRO GYM.bat».

$ErrorActionPreference = 'Continue'
$proyecto = Split-Path -Parent $PSScriptRoot
$php = 'C:\php84\php.exe'
$dockerApp = 'C:\Program Files\Docker\Docker\Docker Desktop.exe'

function Docker-Responde {
    docker info *> $null
    return $LASTEXITCODE -eq 0
}

function Esperar-Docker([int]$segundos) {
    for ($i = 0; $i -lt $segundos; $i += 3) {
        if (Docker-Responde) { return $true }
        Start-Sleep -Seconds 3
    }
    return $false
}

Write-Host '== PRO GYM ==' -ForegroundColor Red

# 1. Docker
if (Docker-Responde) {
    Write-Host 'Docker ya está andando.'
} else {
    Write-Host 'Abriendo Docker Desktop...'
    Start-Process $dockerApp
    if (-not (Esperar-Docker 45)) {
        Write-Host 'Docker se trabó: se destraba y se vuelve a abrir...' -ForegroundColor Yellow
        Get-Process | Where-Object { $_.Name -like '*docker*' } | Stop-Process -Force -ErrorAction SilentlyContinue
        Start-Sleep -Seconds 3
        $run = Join-Path $env:LOCALAPPDATA 'Docker\run'
        if (Test-Path $run) {
            Rename-Item $run ('run-viejo-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
            New-Item -ItemType Directory $run | Out-Null
        }
        Start-Process $dockerApp
        if (-not (Esperar-Docker 120)) {
            Write-Host 'Docker no arrancó. Ábrelo a mano y vuelve a correr esto.' -ForegroundColor Red
            Read-Host 'Enter para cerrar'
            exit 1
        }
    }
    Write-Host 'Docker listo.' -ForegroundColor Green
}

# 2. La base
docker start progym-pg *> $null
Write-Host 'Base de datos lista.' -ForegroundColor Green

# 3. El servidor, si no está ya
$puerto = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
if ($puerto) {
    Write-Host 'El servidor ya está andando.'
} else {
    Start-Process -FilePath $php -ArgumentList 'artisan', 'serve', '--host=127.0.0.1', '--port=8000' -WorkingDirectory $proyecto -WindowStyle Minimized
    Start-Sleep -Seconds 3
    Write-Host 'Servidor listo (ventana minimizada: no la cierres).' -ForegroundColor Green
}

# 4. El panel
Start-Process 'http://127.0.0.1:8000/panel'
Write-Host 'Listo: el panel se abrió en el navegador.' -ForegroundColor Green
Start-Sleep -Seconds 3
