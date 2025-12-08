param()

Write-Host "Corrigiendo problemas de codificacion UTF-8..." -ForegroundColor Cyan

$files = @(
    "app\Http\Controllers\Admin\NotificacionController.php"
)

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "Procesando: $file" -ForegroundColor Yellow
        
        $content = [System.IO.File]::ReadAllText($file, [System.Text.Encoding]::UTF8)
        
        # Reemplazos
        $content = $content -replace 'Ã³', 'ó'
        $content = $content -replace 'Ã±', 'ñ'
        $content = $content -replace 'Ã©', 'é'
        $content = $content -replace 'Ã­', 'í'
        $content = $content -replace 'Ãº', 'ú'
        $content = $content -replace 'Ã¡', 'á'
        $content = $content -replace 'Ã"', 'Ó'
        $content = $content -replace 'Ã‰', 'É'
        $content = $content -replace 'Ã', 'Í'
        $content = $content -replace 'Ãš', 'Ú'
        $content = $content -replace 'Ã', 'Á'
        
        [System.IO.File]::WriteAllText($file, $content, [System.Text.UTF8Encoding]::new($false))
        Write-Host "  Completado!" -ForegroundColor Green
    }
}

Write-Host "Proceso completado" -ForegroundColor Green
