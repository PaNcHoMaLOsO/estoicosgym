# Script para corregir problemas de codificación UTF-8 en archivos PHP

Write-Host "🔧 Buscando archivos con problemas de codificación..." -ForegroundColor Cyan
Write-Host ""

# Patrones problemáticos y sus reemplazos
$replacements = @{
    'Ã³' = 'ó'
    'Ã±' = 'ñ'
    'Ã©' = 'é'
    'Ã­' = 'í'
    'Ãº' = 'ú'
    'Ã¡' = 'á'
    'Ã"' = 'Ó'
    'Ã'' = 'Ñ'
    'Ã‰' = 'É'
    'Ã' = 'Í'
    'Ãš' = 'Ú'
    'Ã' = 'Á'
    'Â¿' = '¿'
    'Â¡' = '¡'
    'Âº' = 'º'
    'Âª' = 'ª'
    'â€œ' = '"'
    'â€' = '"'
    'â€™' = "'"
    'â€"' = '—'
}

# Archivos a procesar
$files = @(
    "app\Http\Controllers\Admin\NotificacionController.php",
    "app\Services\NotificacionService.php",
    "app\Models\Notificacion.php",
    "app\Models\TipoNotificacion.php",
    "app\Models\LogNotificacion.php",
    "resources\views\admin\notificaciones\index.blade.php",
    "resources\views\admin\notificaciones\crear.blade.php",
    "resources\views\admin\notificaciones\show.blade.php"
)

$totalFixed = 0
$filesFixed = 0

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "📄 Procesando: $file" -ForegroundColor Yellow
        
        # Leer archivo
        $content = Get-Content $file -Raw -Encoding UTF8
        $originalContent = $content
        $replacedInFile = 0
        
        # Aplicar reemplazos
        foreach ($pattern in $replacements.Keys) {
            $replacement = $replacements[$pattern]
            if ($content -match [regex]::Escape($pattern)) {
                $occurrences = ([regex]::Matches($content, [regex]::Escape($pattern))).Count
                $content = $content -replace [regex]::Escape($pattern), $replacement
                $replacedInFile += $occurrences
                Write-Host "   ✓ Reemplazadas $occurrences ocurrencias de '$pattern' → '$replacement'" -ForegroundColor Green
            }
        }
        
        # Guardar si hubo cambios
        if ($content -ne $originalContent) {
            [System.IO.File]::WriteAllText($file, $content, [System.Text.UTF8Encoding]::new($false))
            Write-Host "   💾 Archivo guardado con $replacedInFile correcciones" -ForegroundColor Cyan
            $filesFixed++
            $totalFixed += $replacedInFile
        } else {
            Write-Host "   ✅ Sin problemas de codificación" -ForegroundColor DarkGray
        }
        
        Write-Host ""
    } else {
        Write-Host "⚠️  Archivo no encontrado: $file" -ForegroundColor Red
        Write-Host ""
    }
}

Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "✅ Proceso completado" -ForegroundColor Green
Write-Host "   Archivos corregidos: $filesFixed" -ForegroundColor White
Write-Host "   Total de correcciones: $totalFixed" -ForegroundColor White
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
