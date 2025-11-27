/**
 * Script de debug para verificar que todo carga correctamente
 */

console.log('✅ DEBUG: debug-pagos.js cargado');
console.log('✅ DEBUG: Verificando disponibilidad de jQuery:', typeof jQuery !== 'undefined');
console.log('✅ DEBUG: Verificando disponibilidad de $:', typeof $ !== 'undefined');

// Esperar a que el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkDOM);
} else {
    checkDOM();
}

function checkDOM() {
    console.log('✅ DEBUG: DOMContentLoaded disparado');
    console.log('✅ DEBUG: formPago existe:', !!document.getElementById('formPago'));
    console.log('✅ DEBUG: btnSubmit existe:', !!document.getElementById('btnSubmit'));
    console.log('✅ DEBUG: id_inscripcion existe:', !!document.getElementById('id_inscripcion'));
    console.log('✅ DEBUG: ValidadorPagos disponible:', typeof ValidadorPagos !== 'undefined');
    console.log('✅ DEBUG: GestorFormularioPagos disponible:', typeof GestorFormularioPagos !== 'undefined');
}

// Agregar listeners al botón directamente
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnSubmit');
    if (btn) {
        console.log('✅ DEBUG: Agregando event listener directo al botón');
        btn.addEventListener('click', function(e) {
            console.log('🖱️ DEBUG: Click en btnSubmit detectado!');
            console.log('  - Disabled:', this.disabled);
            console.log('  - Type:', this.type);
        });
    }
});
