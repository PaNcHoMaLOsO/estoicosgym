# Script de desarrollo con hot-reload automático
# Uso: .\dev-server.ps1

$projectPath = Get-Location
$port = 8000
$lastHash = $null
$serverProcess = $null

Write-Host "╔════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  EstóicosGym - Servidor de Desarrollo con Hot-Reload  ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""
Write-Host "🚀 Iniciando servidor en http://127.0.0.1:$port" -ForegroundColor Green
Write-Host "👁️  Monitoreando cambios en: $projectPath" -ForegroundColor Green
Write-Host "💡 El servidor se reiniciará automáticamente al detectar cambios" -ForegroundColor Yellow
Write-Host ""

function Start-DevServer {
    Write-Host "🔄 Iniciando servidor Laravel..." -ForegroundColor Blue
    $serverProcess = Start-Process -FilePath "php" -ArgumentList "artisan", "serve", "--port=$port" -PassThru -NoNewWindow
    Write-Host "✅ Servidor iniciado (PID: $($serverProcess.Id))" -ForegroundColor Green
    Start-Sleep -Seconds 2
}

function Stop-DevServer {
    if ($null -ne $serverProcess) {
        Write-Host "⛔ Deteniendo servidor..." -ForegroundColor Yellow
        Stop-Process -Id $serverProcess.Id -Force -ErrorAction SilentlyContinue
        Start-Sleep -Seconds 1
        Write-Host "✅ Servidor detenido" -ForegroundColor Green
    }
}

function Get-FilesHash {
    $files = @()
    $files += Get-ChildItem -Path "resources/views" -Recurse -Include "*.blade.php", "*.php" | Select-Object -ExpandProperty FullName
    $files += Get-ChildItem -Path "public/js", "public/css" -Recurse -Include "*.js", "*.css" 2>/dev/null | Select-Object -ExpandProperty FullName
    $files += Get-ChildItem -Path "app/Http/Controllers" -Recurse -Include "*.php" | Select-Object -ExpandProperty FullName
    
    $content = $files | Sort-Object | Get-Content | Measure-Object -Character | Select-Object -ExpandProperty Characters
    return $content
}

# Iniciar servidor
Start-DevServer

try {
    while ($true) {
        $currentHash = Get-FilesHash
        
        if ($lastHash -ne $null -and $currentHash -ne $lastHash) {
            Write-Host ""
            Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
            Write-Host "📝 Cambios detectados! Reiniciando servidor..." -ForegroundColor Yellow
            Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
            Stop-DevServer
            Start-Sleep -Seconds 1
            Start-DevServer
            Write-Host ""
        }
        
        $lastHash = $currentHash
        Start-Sleep -Seconds 2
    }
}
catch {
    Write-Host "❌ Error: $_" -ForegroundColor Red
}
finally {
    Stop-DevServer
}
