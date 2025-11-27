# Script simple para iniciar servidor con opciones útiles
# Uso: .\start-dev.ps1

Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║          EstóicosGym - Servidor de Desarrollo        ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

$port = 8000
$host = "127.0.0.1"

Write-Host "✅ Iniciando servidor..." -ForegroundColor Green
Write-Host ""
Write-Host "📍 Dirección: http://$host:$port" -ForegroundColor Yellow
Write-Host "📍 Admin:     http://$host:$port/admin" -ForegroundColor Yellow
Write-Host "📍 Inscripciones: http://$host:$port/admin/inscripciones" -ForegroundColor Yellow
Write-Host ""
Write-Host "💡 Tips:" -ForegroundColor Cyan
Write-Host "   - Los cambios en .blade.php se ven automáticamente al actualizar el navegador" -ForegroundColor Gray
Write-Host "   - Los cambios en JavaScript/CSS requieren limpiar caché del navegador (Ctrl+Shift+R)" -ForegroundColor Gray
Write-Host "   - Presiona Ctrl+C para detener el servidor" -ForegroundColor Gray
Write-Host ""

php artisan serve --host=$host --port=$port
