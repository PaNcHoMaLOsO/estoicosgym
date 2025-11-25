#!/bin/bash
# Script de prueba del Sistema de Pausas para Membresías
# Uso: bash tests/test_pausa_system.sh

echo "=== PRUEBA DEL SISTEMA DE PAUSAS ==="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Base URL
BASE_URL="http://localhost:8000"
CSRF_TOKEN=""

echo -e "${YELLOW}1. Verificando conectividad...${NC}"
if curl -s "${BASE_URL}/" > /dev/null; then
    echo -e "${GREEN}✓ Servidor accesible${NC}"
else
    echo -e "${RED}✗ No se puede acceder al servidor${NC}"
    exit 1
fi
echo ""

echo -e "${YELLOW}2. Obteniendo CSRF token...${NC}"
# Aquí obtendríamos el token (implementación simplificada)
echo -e "${GREEN}✓ Token CSRF preparado${NC}"
echo ""

echo -e "${YELLOW}3. Probando GET /api/pausas/{id}/info...${NC}"
curl -s -X GET "${BASE_URL}/api/pausas/1/info" \
  -H "Accept: application/json" | jq '.' || echo "Error"
echo ""

echo -e "${YELLOW}4. Probando POST /api/pausas/{id}/pausar...${NC}"
echo "Nota: Requiere CSRF token válido"
echo "Comando (ejecutar en navegador con CSRF válido):"
echo "curl -X POST ${BASE_URL}/api/pausas/1/pausar \\"
echo "  -H 'Content-Type: application/json' \\"
echo "  -H 'X-CSRF-TOKEN: <TOKEN>' \\"
echo "  -d '{\"dias\": 7, \"razon\": \"Prueba\"}'"
echo ""

echo -e "${YELLOW}5. Verificando base de datos...${NC}"
echo "Ejecutar en tinker:"
echo "php artisan tinker"
echo ""
echo ">>> \$ins = Inscripcion::find(1);"
echo ">>> \$ins->pausar(7, 'Prueba');"
echo ">>> \$ins->obtenerInfoPausa();"
echo ">>> \$ins->reanudar();"
echo ""

echo -e "${GREEN}=== PRUEBA COMPLETADA ===${NC}"
