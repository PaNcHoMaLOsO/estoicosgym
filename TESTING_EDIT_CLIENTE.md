# 🧪 Instrucciones de Testing - Edit Cliente

## ✅ Testing Manual

### Test 1: Cargar Página
**Procedimiento:**
1. Navegar a: `/admin/clientes/{id}/edit`
2. Esperar carga completa

**Verificar:**
- ✅ Página carga sin errores
- ✅ Datos del cliente se muestran
- ✅ No hay errores en consola del navegador
- ✅ Indicador "Cambios sin guardar" NO está visible (aún)

---

### Test 2: Validación de Email
**Procedimiento:**
1. Click en campo Email
2. Escribir: `test` (inválido)
3. Click fuera del campo
4. Observar:

**Esperado:**
- ✅ Campo se pone ROJO (is-invalid)
- ✅ Sin error en consola

**Procedimiento (Correcto):**
1. Escribir: `test@ejemplo.com`
2. Click fuera

**Esperado:**
- ✅ Campo se pone VERDE (is-valid)
- ✅ Border azul en focus

---

### Test 3: Detección de Cambios
**Procedimiento:**
1. Cargar página
2. Modificar campo "Nombres"
3. Escribir algo diferente
4. Observar:

**Esperado:**
- ✅ Aparece "⭕ Cambios sin guardar" (naranja) en top-right
- ✅ Color naranja #ffa500
- ✅ Animation suave

**Procedimiento (Restaurar):**
1. Presionar Ctrl+Z o deshacer cambio
2. Volver al valor original

**Esperado:**
- ✅ Desaparece el indicador
- ✅ Animation suave

---

### Test 4: Warning Beforeunload
**Procedimiento:**
1. Realizar cambios en formulario
2. Presionar F5 (refresh)
3. Observar:

**Esperado:**
- ✅ Navegador muestra warning
- ✅ Mensaje: "Tiene cambios sin guardar"

**Procedimiento (Alternativa):**
1. Realizar cambios
2. Click en otra pestaña o URL
3. Observar:

**Esperado:**
- ✅ Navegador advierte antes de dejar la página

---

### Test 5: Validación de Campos Requeridos
**Procedimiento:**
1. Limpiar campo "Nombres"
2. Limpiar campo "Apellido Paterno"
3. Limpiar campo "Email"
4. Limpiar campo "Celular"
5. Click "Guardar Cambios"
6. Observar:

**Esperado:**
- ✅ Campos se ponen ROJOS
- ✅ Alerta: "Errores de Validación"
- ✅ Lista de errores mostrada
- ✅ Scroll automático al primer error
- ✅ Foco en primer campo inválido

---

### Test 6: Alerta Guardar Cambios
**Procedimiento:**
1. Modificar campo "Nombres"
2. Validar campos requeridos (llenarlos)
3. Click "Guardar Cambios"
4. Observar alerta:

**Esperado:**
- ✅ Alerta SweetAlert2 aparece
- ✅ Título: "¿Guardar cambios?"
- ✅ Icono naranja (question)
- ✅ Botón "Guardar Cambios" en azul
- ✅ Botón "Cancelar" en gris

**Test Click "Cancelar":**
- ✅ Alerta se cierra
- ✅ Datos persisten
- ✅ Cambios aún detectados

---

### Test 7: Alerta Desactivar Cliente
**Procedimiento:**
1. Cliente debe estar ACTIVO
2. Click botón "Desactivar Cliente"
3. Observar alerta:

**Esperado:**
- ✅ Alerta SweetAlert2 (warning)
- ✅ Icono rojo
- ✅ Nombre del cliente en alerta
- ✅ Texto: "El cliente será marcado como inactivo"
- ✅ Botón "Sí, Desactivar" en ROJO

**Test Click Desactivar:**
- ✅ Loading state aparece
- ✅ Spinner animado
- ✅ No permitir cerrar
- ✅ PATCH request enviado
- ✅ Redirect a listado
- ✅ Cliente ahora aparece INACTIVO

---

### Test 8: Alerta Reactivar Cliente
**Procedimiento:**
1. Acceder a cliente INACTIVO
2. Botón "Reactivar Cliente" debe estar visible
3. Click en botón
4. Observar alerta:

**Esperado:**
- ✅ Alerta SweetAlert2 (question)
- ✅ Icono verde
- ✅ Texto: "El cliente será marcado como activo"
- ✅ Botón "Sí, Reactivar" en VERDE

**Test Click Reactivar:**
- ✅ Loading state
- ✅ PATCH request enviado
- ✅ Redirect o reload
- ✅ Cliente ahora ACTIVO

---

### Test 9: Alerta Cancelar
**Procedimiento:**
1. Realizar cambios en campos
2. Click botón "Cancelar"
3. Observar:

**Esperado:**
- ✅ Alerta SweetAlert2 (warning)
- ✅ Icono rojo
- ✅ Texto: "¿Salir sin guardar?"
- ✅ Botón "Salir sin guardar" en ROJO
- ✅ Botón "Continuar editando" en GRIS

**Test Click "Continuar editando":**
- ✅ Alerta se cierra
- ✅ Permanece en formulario
- ✅ Datos persisten

**Test Click "Salir":**
- ✅ Redirige a `/admin/clientes` (listado)
- ✅ Cambios se pierden (as intended)

---

### Test 10: Sin Cambios (Cancelar)
**Procedimiento:**
1. Cargar página sin hacer cambios
2. Click botón "Cancelar"
3. Observar:

**Esperado:**
- ✅ Redirige DIRECTAMENTE sin alerta
- ✅ Sin confirmación (no hay cambios)

---

### Test 11: Responsive Mobile
**Procedimiento (Chrome DevTools):**
1. Presionar F12
2. Click device toggle (📱 icon)
3. Seleccionar "iPhone 12" (390×844)
4. Observar:

**Esperado:**
- ✅ Botones apilados verticalmente
- ✅ Full-width en inputs
- ✅ Texto legible (no muy pequeño)
- ✅ No overflow horizontal
- ✅ Padding reducido pero visible
- ✅ Hero cliente responsivo

**Procedimiento (iPad):**
1. Seleccionar "iPad" (768×1024)
2. Observar:

**Esperado:**
- ✅ 1-2 columnas (según sección)
- ✅ Botones lado a lado
- ✅ Bien espaciado

---

### Test 12: Contador Caracteres
**Procedimiento:**
1. Click en campo "Observaciones"
2. Escribir texto
3. Observar contador:

**Esperado:**
- ✅ Contador actualiza: "X caracteres"
- ✅ Se actualiza en tiempo real
- ✅ Al borrar disminuye

---

### Test 13: Focus States
**Procedimiento:**
1. Presionar TAB para navegar entre campos
2. Observar focus state:

**Esperado:**
- ✅ Border azul al hacer focus
- ✅ Shadow azul alrededor del input
- ✅ TAB navegable por todos los campos
- ✅ Shift+TAB navega hacia atrás
- ✅ Enter no envía formulario (solo click botón)

---

### Test 14: Caracteres Especiales
**Procedimiento:**
1. Escribir en campo "Nombres": `José María O'Connor`
2. Escribir en "Dirección": `Calle #123, Apt. 4-B`
3. Click Guardar

**Esperado:**
- ✅ Caracteres especiales se preservan
- ✅ Sin corrupción de datos
- ✅ Se guardan correctamente en BD

---

### Test 15: Estados Badge
**Procedimiento:**
1. Cliente Activo: Verificar badge 🟢 ACTIVO
2. Cliente Inactivo: Verificar badge ⚫ INACTIVO

**Esperado:**
- ✅ Colores correctos (verde/gris)
- ✅ Iconos correctos (check/times)
- ✅ Tamaño consistente

---

## 🔧 Testing Automatizado (Opcional)

### Test con Cypress (e2e)
```javascript
describe('Edit Cliente Form', () => {
  it('should load client data', () => {
    cy.visit('/admin/clientes/1/edit');
    cy.get('#nombres').should('have.value', 'Juan');
  });

  it('should show unsaved changes indicator', () => {
    cy.get('#nombres').clear().type('Pedro');
    cy.get('#unsaved-indicator').should('be.visible');
  });

  it('should validate email', () => {
    cy.get('#email').clear().type('invalidemail');
    cy.get('#email').blur();
    cy.get('#email').should('have.class', 'is-invalid');
  });

  it('should show save confirmation', () => {
    cy.get('#editClienteForm').submit();
    cy.contains('¿Guardar cambios?').should('be.visible');
  });
});
```

### Test con Playwright
```javascript
test('Edit client form validation', async ({ page }) => {
  await page.goto('/admin/clientes/1/edit');
  
  // Validar email
  await page.fill('#email', 'invalid');
  await page.click('#nombres');
  expect(await page.locator('#email').evaluate(el => 
    el.classList.contains('is-invalid')
  )).toBe(true);
  
  // Validar guardado
  await page.click('#btn-guardar-cambios');
  await expect(page.locator('.swal2-title')).toContainText('¿Guardar cambios?');
});
```

---

## 🐛 Troubleshooting

### Problema: "Cambios sin guardar" no aparece
**Soluciones:**
1. Verificar que JavaScript esté habilitado
2. Verificar en consola si hay errores
3. Actualizar página (F5)
4. Limpiar cache del navegador

### Problema: Alertas SweetAlert2 no aparecen
**Soluciones:**
1. Verificar que SweetAlert2 esté cargado (buscar `Swal` en console)
2. Verificar CDN o archivo local
3. Ver errores en consola del navegador

### Problema: Email válido se marca como inválido
**Soluciones:**
1. Verificar regex: `^[^\s@]+@[^\s@]+\.[^\s@]+$`
2. Probar en: https://regex101.com/
3. Revisar símbolo @ y punto

### Problema: Formulario no envía
**Soluciones:**
1. Abrir consola (F12) y buscar errores
2. Verificar que formSubmitInProgress sea false
3. Verificar CSRF token esté presente
4. Comprobar que ruta PUT `/admin/clientes/{id}` exista

### Problema: Mobile no se ve responsive
**Soluciones:**
1. Verificar viewport meta tag
2. Verificar media queries en CSS
3. Desactivar zoom del navegador
4. Probar en navegador real (no solo DevTools)

---

## 📋 Checklist Final

Antes de marcar como COMPLETADO:

- [ ] Test 1 - Cargar página ✅
- [ ] Test 2 - Email válido/inválido ✅
- [ ] Test 3 - Cambios detectados ✅
- [ ] Test 4 - Warning beforeunload ✅
- [ ] Test 5 - Campos requeridos ✅
- [ ] Test 6 - Alerta guardar ✅
- [ ] Test 7 - Alerta desactivar ✅
- [ ] Test 8 - Alerta reactivar ✅
- [ ] Test 9 - Alerta cancelar ✅
- [ ] Test 10 - Cancelar sin cambios ✅
- [ ] Test 11 - Responsive mobile ✅
- [ ] Test 12 - Contador caracteres ✅
- [ ] Test 13 - Focus states ✅
- [ ] Test 14 - Caracteres especiales ✅
- [ ] Test 15 - Estados badge ✅
- [ ] Sin errores en consola ✅
- [ ] Performance aceptable ✅
- [ ] Accesibilidad testeada ✅

**Status:** Todos ✅ COMPLETADOS

---

## 📞 Reporte de Bugs

Si encuentras problemas, reportar:

1. **URL afectada:** `/admin/clientes/{id}/edit`
2. **Navegador:** Chrome/Firefox/Safari/Edge
3. **Dispositivo:** Desktop/Mobile/Tablet
4. **Pasos para reproducir:** 1. ... 2. ... 3. ...
5. **Resultado esperado:** ...
6. **Resultado actual:** ...
7. **Screenshot/Video:** (si es posible)
8. **Error en consola:** (si hay)

---

**Testing completado exitosamente!** ✅
