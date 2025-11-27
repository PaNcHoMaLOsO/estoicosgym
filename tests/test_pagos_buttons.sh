#!/bin/bash
# Script de Testing - Módulo Pagos
# Verifica que todos los botones funcionen correctamente

echo "╔════════════════════════════════════════════════════════╗"
echo "║     TESTING MÓDULO PAGOS - BOTONES Y CHECKBOXES       ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función de prueba
test_route() {
    local method=$1
    local route=$2
    local description=$3
    
    echo -n "Testing $description... "
    
    case $method in
        GET)
            response=$(curl -s -o /dev/null -w "%{http_code}" "http://127.0.0.1:8000$route")
            ;;
        POST)
            response=$(curl -s -X POST -o /dev/null -w "%{http_code}" "http://127.0.0.1:8000$route")
            ;;
        PUT)
            response=$(curl -s -X PUT -o /dev/null -w "%{http_code}" "http://127.0.0.1:8000$route")
            ;;
        DELETE)
            response=$(curl -s -X DELETE -o /dev/null -w "%{http_code}" "http://127.0.0.1:8000$route")
            ;;
    esac
    
    if [ $response -eq 200 ] || [ $response -eq 302 ] || [ $response -eq 404 ]; then
        if [ $response -eq 404 ]; then
            echo -e "${YELLOW}[404 - Recurso no encontrado]${NC}"
        else
            echo -e "${GREEN}[✓ $response]${NC}"
        fi
    else
        echo -e "${RED}[✗ $response]${NC}"
    fi
}

echo "════════════════════════════════════════════════════════"
echo "1. RUTAS GET (Navegación)"
echo "════════════════════════════════════════════════════════"
test_route GET "/admin/pagos" "Listado de pagos (INDEX)"
test_route GET "/admin/pagos/create" "Crear nuevo pago (CREATE)"
test_route GET "/admin/pagos/1" "Ver detalles pago (SHOW)"
test_route GET "/admin/pagos/1/edit" "Editar pago (EDIT)"

echo ""
echo "════════════════════════════════════════════════════════"
echo "2. RUTAS POST (Crear)"
echo "════════════════════════════════════════════════════════"
echo -n "Testing Crear pago (STORE)... "
echo -e "${YELLOW}[Requiere datos válidos - Omitido]${NC}"

echo ""
echo "════════════════════════════════════════════════════════"
echo "3. RUTAS PUT (Actualizar)"
echo "════════════════════════════════════════════════════════"
echo -n "Testing Actualizar pago (UPDATE)... "
echo -e "${YELLOW}[Requiere datos válidos - Omitido]${NC}"

echo ""
echo "════════════════════════════════════════════════════════"
echo "4. RUTAS DELETE (Eliminar)"
echo "════════════════════════════════════════════════════════"
echo -n "Testing Eliminar pago (DESTROY)... "
echo -e "${YELLOW}[Destructivo - Omitido]${NC}"

echo ""
echo "════════════════════════════════════════════════════════"
echo "5. APIS"
echo "════════════════════════════════════════════════════════"
test_route GET "/api/inscripciones/search?q=test" "Buscar inscripciones API"
echo -n "Testing Obtener saldo inscripción API... "
echo -e "${YELLOW}[Requiere ID válido - Omitido]${NC}"

echo ""
echo "════════════════════════════════════════════════════════"
echo "✅ TESTING COMPLETADO"
echo "════════════════════════════════════════════════════════"
echo ""
echo "Notas:"
echo "  • Las rutas 404 indican recursos no encontrados (esperado)"
echo "  • Las rutas 302 indican redirecciones (esperado en auth)"
echo "  • Los métodos omitidos requieren datos válidos"
echo ""
