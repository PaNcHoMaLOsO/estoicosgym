/**
 * Sistema Simplificado de Gestión de Pagos
 * Versión limpia y funcional
 */

console.log('✅ validacion-pagos.js cargado');

// Esperar a que el DOM esté completamente listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeForm);
} else {
    initializeForm();
}

function initializeForm() {
    console.log('🚀 Inicializando formulario de pagos...');
    
    const formPago = document.getElementById('formPago');
    const btnSubmit = document.getElementById('btnSubmit');
    
    if (!formPago || !btnSubmit) {
        console.error('❌ Formulario no encontrado');
        return;
    }

    console.log('✓ Formulario encontrado');
    console.log('✓ Botón encontrado:', btnSubmit.id);

    // EVENTO: Click en botón
    btnSubmit.addEventListener('click', function(e) {
        console.log('🖱️ *** CLICK EN BOTÓN DETECTADO ***');
        console.log('  - Botón deshabilitado:', this.disabled);
        console.log('  - Tipo:', this.type);
        e.preventDefault();
        handleFormSubmit(e);
    });

    // EVENTO: Submit del formulario
    formPago.addEventListener('submit', function(e) {
        console.log('📤 *** SUBMIT DEL FORMULARIO DETECTADO ***');
        e.preventDefault();
        handleFormSubmit(e);
    });

    // Select2 initialization
    console.log('⚙️ Inicializando Select2...');
    $('#id_inscripcion').select2({
        width: '100%',
        language: 'es',
        placeholder: '🔍 Buscar por nombre, RUT o email',
        allowClear: true
    });

    // Select2 para método de pago en pago mixto
    $('#metodo_pago_1').select2({
        width: '100%',
        language: 'es',
        placeholder: '-- Seleccionar --',
        allowClear: false
    });

    // Cambio de cliente
    $('#id_inscripcion').on('select2:select', function(e) {
        console.log('👤 Cliente seleccionado:', e.params.data.id);
        handleClientChange();
    });

    $('#id_inscripcion').on('select2:clear', function() {
        console.log('❌ Cliente limpiado');
        document.getElementById('clienteInfoSection').classList.add('d-none');
        document.getElementById('tipoPagoSection').classList.add('d-none');
        document.getElementById('datosPagoSection').classList.add('d-none');
    });

    // Cambio de tipo de pago
    document.querySelectorAll('input[name="tipo_pago"]').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('🔄 Tipo de pago cambiado a:', this.value);
            handleTipoPagoChange(this.value);
        });
    });

    // Si hay un cliente pre-seleccionado (desde ruta), simular el cambio
    const selectInscripcion = document.getElementById('id_inscripcion');
    if (selectInscripcion.value) {
        console.log('👤 Cliente pre-seleccionado detectado, cargando datos...');
        setTimeout(() => {
            // Trigger change event en Select2
            $('#id_inscripcion').trigger('select2:select');
            handleClientChange();
        }, 100);
    }

    console.log('🎉 Formulario inicializado correctamente');
}

function handleClientChange() {
    console.log('📋 handleClientChange() ejecutándose...');
    
    const selectInscripcion = document.getElementById('id_inscripcion');
    const value = selectInscripcion.value;
    
    if (!value) {
        console.log('  Sin cliente seleccionado');
        return;
    }

    const option = document.querySelector(`option[value="${value}"]`);
    if (!option) {
        console.error('  Option no encontrado');
        return;
    }

    try {
        const precio = parseFloat(option.getAttribute('data-precio')) || 0;
        const pagos = parseFloat(option.getAttribute('data-pagos')) || 0;
        const pendiente = parseFloat(option.getAttribute('data-pendiente')) || (precio - pagos);
        const cliente = option.getAttribute('data-cliente') || '';
        const membresia = option.getAttribute('data-membresia') || '';
        const vencimiento = option.getAttribute('data-vencimiento') || '';

        console.log(`  Datos: cliente=${cliente}, precio=${precio}, pagos=${pagos}, pendiente=${pendiente}`);

        // Calcular días restantes
        const fechaVencimiento = new Date(vencimiento);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        const diferencia = fechaVencimiento - hoy;
        const diasRestantes = Math.ceil(diferencia / (1000 * 60 * 60 * 24));

        // Llenar información
        document.getElementById('clienteNombre').textContent = cliente;
        document.getElementById('membresiaNombre').textContent = membresia;
        document.getElementById('montoTotal').textContent = precio.toLocaleString('es-CO');
        document.getElementById('montoAbonado').textContent = pagos.toLocaleString('es-CO');
        document.getElementById('montoPendiente').textContent = pendiente.toLocaleString('es-CO');
        document.getElementById('fechaVencimiento').textContent = vencimiento;
        
        // Mostrar días restantes con color
        const diasElement = document.getElementById('diasRestantes');
        if (diasRestantes > 0) {
            diasElement.innerHTML = `<span style="color: #10b981;">${diasRestantes} días</span>`;
        } else if (diasRestantes === 0) {
            diasElement.innerHTML = `<span style="color: #fbbf24;">Hoy vence</span>`;
        } else {
            diasElement.innerHTML = `<span style="color: #ef4444;">Vencido</span>`;
        }

        // Actualizar campos de pago
        document.getElementById('monto_completo').value = '$' + pendiente.toLocaleString('es-CO');
        document.getElementById('monto_abonado_completo').value = pendiente;
        document.getElementById('target-mixto').textContent = pendiente.toLocaleString('es-CO');

        // Actualizar máximo permitido en abono
        document.getElementById('monto_abonado_abono').max = pendiente;
        document.getElementById('max-abono').textContent = `Máximo permitido: $${pendiente.toLocaleString('es-CO')}`;

        // Mostrar secciones
        document.getElementById('clienteInfoSection').classList.remove('d-none');
        document.getElementById('tipoPagoSection').classList.remove('d-none');
        document.getElementById('datosPagoSection').classList.remove('d-none');

        // Resetear formulario
        document.getElementById('monto_abonado_abono').value = '';
        document.getElementById('monto_metodo1').value = '';
        document.getElementById('monto_metodo2').value = '';
        document.getElementById('id_metodo_pago_abono').value = '';
        document.getElementById('id_metodo_pago_completo').value = '';
        document.getElementById('metodo_pago_1').value = '';
        document.getElementById('fecha_pago').value = new Date().toISOString().split('T')[0];
        document.getElementById('referencia_pago').value = '';
        document.getElementById('observaciones').value = '';
        document.getElementById('cantidad_cuotas').value = '1';

        // Tipo de pago por defecto
        document.querySelector('input[name="tipo_pago"][value="abono"]').checked = true;
        handleTipoPagoChange('abono');

        console.log('  ✓ Cliente cargado exitosamente');
    } catch(error) {
        console.error('  ❌ Error:', error);
    }
}

function handleTipoPagoChange(tipoPago) {
    console.log(`🔄 Cambiando a tipo: ${tipoPago}`);
    
    // Ocultar todas las secciones
    document.querySelectorAll('.pago-section').forEach(s => s.classList.add('d-none'));
    document.querySelectorAll('.tipo-pago-card').forEach(c => c.classList.remove('active'));
    
    // Mostrar la correcta
    if (tipoPago === 'abono') {
        document.getElementById('seccion-abono').classList.remove('d-none');
        document.getElementById('card-abono').classList.add('active');
    } else if (tipoPago === 'completo') {
        document.getElementById('seccion-completo').classList.remove('d-none');
        document.getElementById('card-completo').classList.add('active');
    } else if (tipoPago === 'mixto') {
        document.getElementById('seccion-mixto').classList.remove('d-none');
        document.getElementById('card-mixto').classList.add('active');
    }
}

function handleFormSubmit(e) {
    console.log('\n📤 *** PROCESANDO ENVÍO DE FORMULARIO ***\n');
    
    // Validaciones básicas
    const selectInscripcion = document.getElementById('id_inscripcion');
    if (!selectInscripcion.value) {
        console.error('❌ VALIDACIÓN FALLA: Cliente no seleccionado');
        alert('⚠️ Debes seleccionar un cliente');
        return;
    }

    const tipoPago = document.querySelector('input[name="tipo_pago"]:checked');
    if (!tipoPago) {
        console.error('❌ VALIDACIÓN FALLA: Tipo de pago no seleccionado');
        alert('⚠️ Debes seleccionar un tipo de pago');
        return;
    }

    console.log(`✓ VALIDACIÓN: Cliente seleccionado`);
    console.log(`✓ VALIDACIÓN: Tipo de pago = ${tipoPago.value}`);

    // Validar según tipo
    if (tipoPago.value === 'abono') {
        const monto = parseFloat(document.getElementById('monto_abonado_abono').value) || 0;
        const metodo = document.getElementById('id_metodo_pago_abono').value;
        
        if (monto <= 0) {
            console.error('❌ VALIDACIÓN FALLA: Monto inválido');
            alert('⚠️ Ingresa un monto válido');
            return;
        }
        if (!metodo) {
            console.error('❌ VALIDACIÓN FALLA: Método de pago no seleccionado');
            alert('⚠️ Selecciona un método de pago');
            return;
        }
        console.log(`✓ VALIDACIÓN: Abono válido (${monto})`);
    } else if (tipoPago.value === 'completo') {
        const metodo = document.getElementById('id_metodo_pago_completo').value;
        if (!metodo) {
            console.error('❌ VALIDACIÓN FALLA: Método de pago no seleccionado');
            alert('⚠️ Selecciona un método de pago');
            return;
        }
        console.log(`✓ VALIDACIÓN: Pago completo válido`);
    } else if (tipoPago.value === 'mixto') {
        const monto1 = parseFloat(document.getElementById('monto_metodo1').value) || 0;
        const monto2 = parseFloat(document.getElementById('monto_metodo2').value) || 0;
        const total = monto1 + monto2;
        const target = parseFloat(document.getElementById('target-mixto').textContent.replace(/\./g, '').replace(/,/g, '')) || 0;
        const metodo = document.getElementById('metodo_pago_1').value;
        
        if (total !== target) {
            console.error(`❌ VALIDACIÓN FALLA: Suma incorrecta (${total} vs ${target})`);
            alert(`⚠️ La suma debe ser exactamente $${target}`);
            return;
        }
        if (!metodo) {
            console.error('❌ VALIDACIÓN FALLA: Método de pago no seleccionado');
            alert('⚠️ Selecciona un método de pago');
            return;
        }
        console.log(`✓ VALIDACIÓN: Pago mixto válido (${total})`);
    }

    console.log('\n✅ *** TODAS LAS VALIDACIONES PASARON ***\n');
    console.log('📤 Enviando formulario...');
    
    // Poblar campos ocultos
    const monto_abonado = document.getElementById('monto_abonado');
    const id_metodo_pago_principal = document.getElementById('id_metodo_pago_principal');

    if (tipoPago.value === 'abono') {
        monto_abonado.value = document.getElementById('monto_abonado_abono').value;
        id_metodo_pago_principal.value = document.getElementById('id_metodo_pago_abono').value;
    } else if (tipoPago.value === 'completo') {
        monto_abonado.value = document.getElementById('monto_abonado_completo').value;
        id_metodo_pago_principal.value = document.getElementById('id_metodo_pago_completo').value;
    } else if (tipoPago.value === 'mixto') {
        const m1 = parseFloat(document.getElementById('monto_metodo1').value) || 0;
        const m2 = parseFloat(document.getElementById('monto_metodo2').value) || 0;
        monto_abonado.value = m1 + m2;
        id_metodo_pago_principal.value = document.getElementById('metodo_pago_1').value;
    }

    console.log('  monto_abonado =', monto_abonado.value);
    console.log('  id_metodo_pago_principal =', id_metodo_pago_principal.value);

    console.log('\n✅ ENVIANDO FORMULARIO AHORA...\n');
    
    // Enviar
    document.getElementById('formPago').submit();
}

console.log('✅ Script cargado y listo');
