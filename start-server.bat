@echo off
REM Script para iniciar servidor de desarrollo
REM Uso: start-server.bat

cls
echo.
echo ╔═══════════════════════════════════════════════════════╗
echo ║          EstóicosGym - Servidor de Desarrollo        ║
echo ╚═══════════════════════════════════════════════════════╝
echo.
echo ✅ Iniciando servidor en puerto 8000...
echo.
echo 📍 URLs:
echo    • Principal:     http://127.0.0.1:8000
echo    • Admin:         http://127.0.0.1:8000/admin
echo    • Inscripciones: http://127.0.0.1:8000/admin/inscripciones/create
echo.
echo 💡 Tips:
echo    • Cambios .blade.php ^-^> Solo presiona F5
echo    • Cambios .css/.js  ^-^> Presiona Ctrl+Shift+R ^(hard refresh^)
echo    • Presiona Ctrl+C para detener el servidor
echo.
echo ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
echo.

php artisan serve --host=127.0.0.1 --port=8000

pause
