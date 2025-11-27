# Script simple para iniciar servidor con opciones útiles
# Uso: .\start-dev.ps1

Clear-Host

Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║          EstóicosGym - Servidor de Desarrollo        ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

$port = 8000
$host = "127.0.0.1"

Write-Host "✅ Iniciando servidor en puerto $port..." -ForegroundColor Green
Write-Host ""
Write-Host "📍 URLs:" -ForegroundColor Yellow
Write-Host "   • Principal:     http://$host:$port" -ForegroundColor Cyan
Write-Host "   • Admin:         http://$host:$port/admin" -ForegroundColor Cyan
Write-Host "   • Inscripciones: http://$host:$port/admin/inscripciones/create" -ForegroundColor Cyan
Write-Host ""
Write-Host "💡 Tips:" -ForegroundColor Green
Write-Host "   • Cambios .blade.php → Solo presiona F5" -ForegroundColor Gray
Write-Host "   • Cambios .css/.js  → Presiona Ctrl+Shift+R (hard refresh)" -ForegroundColor Gray
Write-Host "   • Presiona Ctrl+C para detener el servidor" -ForegroundColor Gray
Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host ""

& php artisan serve --host=$host --port=$port
