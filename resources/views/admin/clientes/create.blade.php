@extends('adminlte::page')

@section('title', 'Crear Cliente - EstóicosGym')

@section('css')
    <style>
        .step-indicator { display: none; }
        .step-indicator.active { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        .steps-nav { 
            display: flex; 
            gap: 1rem; 
            margin-bottom: 2rem; 
            flex-wrap: wrap;
            padding: 1rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 0.75rem;
        }
        
        .step-btn {
            flex: 1;
            min-width: 120px;
            padding: 1rem;
            text-align: center;
            border-radius: 0.75rem;
            background: white;
            border: 2px solid #dee2e6;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .step-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .step-btn.completed {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }

        .step-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .form-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 1.5rem 0 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #667eea;
        }

        .precio-box {
            background: #f0f4ff;
            border: 2px solid #667eea;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }

        .buttons-container {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .buttons-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* ============= SweetAlert2 Custom Styles ============= */
        
        /* Error Alert Styles */
        .swal-error-popup {
            border-radius: 12px !important;
            box-shadow: 0 4px 20px rgba(220, 53, 69, 0.15) !important;
        }

        .swal-error-title {
            color: #dc3545 !important;
            font-size: 1.4em !important;
            font-weight: 700 !important;
            margin-bottom: 16px !important;
        }

        .swal-error-content {
            text-align: left !important;
            padding: 12px 0 !important;
        }

        /* Confirmation Alert Styles */
        .swal-confirm-popup {
            border-radius: 12px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12) !important;
            animation: slideInUp 0.3s ease-out !important;
        }

        .swal-confirm-title {
            color: #2c3e50 !important;
            font-size: 1.3em !important;
            font-weight: 700 !important;
            margin-bottom: 16px !important;
        }

        .swal-confirm-content {
            text-align: left !important;
            padding: 12px 0 !important;
            font-size: 0.95em !important;
        }

        /* Warning Alert Styles */
        .swal-warning-popup {
            border-radius: 12px !important;
            box-shadow: 0 4px 20px rgba(255, 107, 107, 0.15) !important;
        }

        .swal-warning-title {
            color: #ff6b6b !important;
            font-size: 1.3em !important;
            font-weight: 700 !important;
            margin-bottom: 16px !important;
        }

        .swal-warning-content {
            text-align: left !important;
            padding: 12px 0 !important;
        }

        /* Button Styles */
        .swal2-confirm {
            border-radius: 6px !important;
            padding: 10px 24px !important;
            font-weight: 600 !important;
            font-size: 0.95em !important;
            transition: all 0.3s ease !important;
        }

        .swal2-cancel {
            border-radius: 6px !important;
            padding: 10px 24px !important;
            font-weight: 600 !important;
            font-size: 0.95em !important;
            background-color: #6c757d !important;
            transition: all 0.3s ease !important;
        }

        .swal2-confirm:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        .swal2-cancel:hover {
            background-color: #5a6268 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        /* Loading Animation */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Spinner Style */
        .swal2-popup .fa-spinner {
            animation: spin 1s linear infinite !important;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script>
        let currentStep = 1;
        const totalSteps = 3;
        let hayDatosNoGuardados = false;

        /**
         * Validar un paso específico del formulario
         * Retorna {valido: bool, errores: array}
         */
        function validarPasoCompleto(paso) {
            const errores = [];

            if (paso === 1) {
                const nombres = document.getElementById('nombres')?.value?.trim();
                const apellido = document.getElementById('apellido_paterno')?.value?.trim();
                const email = document.getElementById('email')?.value?.trim();
                const celular = document.getElementById('celular')?.value?.trim();

                if (!nombres) errores.push('Nombres es requerido');
                if (!apellido) errores.push('Apellido Paterno es requerido');
                if (!email) errores.push('Email es requerido');
                else if (!email.includes('@')) errores.push('Email debe ser válido');
                if (!celular) errores.push('Celular es requerido');
            } else if (paso === 2) {
                const membresia = document.getElementById('id_membresia')?.value;
                const fechaInicio = document.getElementById('fecha_inicio')?.value;

                if (!membresia) errores.push('Membresía es requerida');
                if (!fechaInicio) errores.push('Fecha de Inicio es requerida');
            } else if (paso === 3) {
                const tipoPago = document.getElementById('tipo_pago')?.value;
                const fechaPago = document.getElementById('fecha_pago')?.value;
                const metodo = document.getElementById('id_metodo_pago')?.value;
                const montoAbonado = parseInt(document.getElementById('monto_abonado')?.value || '0');
                const precioFinalText = document.getElementById('resumen-precio-final')?.textContent || '$0';
                const precioFinal = parseInt(precioFinalText.replace('$', '').replace(/\./g, '')) || 0;

                if (!tipoPago) errores.push('Tipo de Pago es requerido');
                if (!fechaPago) errores.push('Fecha de Pago es requerida');

                if (fechaPago) {
                    const fechaPagoDate = new Date(fechaPago);
                    const hoy = new Date();
                    hoy.setHours(0, 0, 0, 0);
                    if (fechaPagoDate > hoy) {
                        errores.push('Fecha de Pago no puede ser futura');
                    }
                }

                if (tipoPago === 'completo' || tipoPago === 'parcial' || tipoPago === 'mixto') {
                    if (!metodo) errores.push('Método de Pago es requerido para este tipo de pago');
                }

                if (tipoPago === 'parcial') {
                    if (montoAbonado <= 0) errores.push('En pago parcial el monto debe ser mayor a $0');
                    if (montoAbonado >= precioFinal) errores.push('En pago parcial el monto debe ser menor al precio final');
                } else if (tipoPago === 'mixto') {
                    if (montoAbonado < 0) errores.push('El monto no puede ser negativo');
                    if (montoAbonado > precioFinal) errores.push('El monto no puede ser mayor al precio final');
                }
            }

            return { valido: errores.length === 0, errores };
        }

        /**
         * Mostrar alerta de error con SweetAlert2
         */
        function mostrarErrorValidacion(errores) {
            const listaErrores = errores.map(e => 
                `<div style="text-align: left; padding: 8px; margin: 4px 0; background-color: #fff5f5; border-left: 4px solid #dc3545; border-radius: 4px;">
                    <i class="fas fa-exclamation-circle" style="color: #dc3545; margin-right: 8px;"></i>${e}
                </div>`
            ).join('');
            Swal.fire({
                icon: 'error',
                title: '<i class="fas fa-triangle-exclamation" style="color: #dc3545;"></i> Campos incompletos',
                html: `<div style="text-align: left; margin-top: 16px;">${listaErrores}</div>`,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#dc3545',
                customClass: {
                    popup: 'swal-error-popup',
                    title: 'swal-error-title',
                    htmlContainer: 'swal-error-content'
                }
            });
        }

        /**
         * Ir a un paso específico con validación
         */
        function goToStep(step, skipValidation = false) {
            if (step < 1 || step > totalSteps) return;

            // Si intentamos avanzar (no retroceder), validar el paso actual
            if (!skipValidation && step > currentStep) {
                const validacion = validarPasoCompleto(currentStep);
                if (!validacion.valido) {
                    mostrarErrorValidacion(validacion.errores);
                    return;
                }
            }

            document.querySelectorAll('.step-indicator').forEach(el => {
                el.classList.remove('active');
            });

            const stepElement = document.getElementById(`step-${step}`);
            if (stepElement) {
                stepElement.classList.add('active');
                currentStep = step;
                updateButtons();
                updateStepButtons();

                // Actualizar datos según el paso
                if (step === 2) {
                    actualizarNombreCliente();
                } else if (step === 3) {
                    actualizarPrecio();
                    setTimeout(() => {
                        actualizarResumenPaso3();
                    }, 100);
                }

                // Scroll al inicio del paso
                stepElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function nextStep() {
            if (currentStep < totalSteps) {
                goToStep(currentStep + 1, false);
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                goToStep(currentStep - 1, true);
            }
        }

        function updateButtons() {
            const btnAnterior = document.getElementById('btn-anterior');
            const btnSiguiente = document.getElementById('btn-siguiente');
            const btnGuardarSoloCliente = document.getElementById('btn-guardar-solo-cliente');
            const btnGuardarConMembresia = document.getElementById('btn-guardar-con-membresia');
            const btnGuardarCompleto = document.getElementById('btn-guardar-completo');
            const flujoInput = document.getElementById('flujo_cliente');
            
            btnAnterior.style.display = 'none';
            btnSiguiente.style.display = 'none';
            btnGuardarSoloCliente.style.display = 'none';
            btnGuardarConMembresia.style.display = 'none';
            btnGuardarCompleto.style.display = 'none';
            
            if (currentStep === 1) {
                btnSiguiente.style.display = 'block';
                btnGuardarSoloCliente.style.display = 'block';
                flujoInput.value = 'solo_cliente';
            } else if (currentStep === 2) {
                btnAnterior.style.display = 'block';
                btnSiguiente.style.display = 'block';
                btnGuardarConMembresia.style.display = 'block';
                flujoInput.value = 'con_membresia';
            } else if (currentStep === 3) {
                btnAnterior.style.display = 'block';
                btnGuardarCompleto.style.display = 'block';
                flujoInput.value = 'completo';
            }
        }

        function updateStepButtons() {
            for (let i = 1; i <= totalSteps; i++) {
                const btn = document.getElementById(`step${i}-btn`);
                btn.classList.remove('active', 'completed');
                
                if (i < currentStep) {
                    btn.classList.add('completed');
                    btn.disabled = false;
                } else if (i === currentStep) {
                    btn.classList.add('active');
                    btn.disabled = false;
                } else {
                    btn.disabled = true;
                }
            }
        }

        function actualizarPrecio() {
            const membresiaSelect = document.getElementById('id_membresia');
            const convenioSelect = document.getElementById('id_convenio');
            const fechaInicio = document.getElementById('fecha_inicio');
            
            if (!membresiaSelect || !membresiaSelect.value) {
                const precioBox = document.getElementById('precioBox');
                if (precioBox) precioBox.style.display = 'none';
                return;
            }
            
            const membresia_id = membresiaSelect.value;
            const convenio_id = convenioSelect ? convenioSelect.value : '';
            
            console.log('🔍 Fetching precio para membresia:', membresia_id, 'convenio:', convenio_id);
            
            // Construir URL correctamente
            let url = `/api/precio-membresia/${membresia_id}`;
            if (convenio_id) {
                url += `?convenio=${convenio_id}`;
            }
            
            console.log('🔗 URL:', url);
            
            fetch(url)
                .then(response => {
                    console.log('📡 Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('✅ Respuesta API:', data);
                    
                    if (!data || data.error) {
                        console.error('❌ Error en API:', data?.error);
                        const precioBox = document.getElementById('precioBox');
                        if (precioBox) precioBox.style.display = 'none';
                        return;
                    }
                    
                    const precioBase = parseInt(data.precio_base) || 0;
                    const precioConConvenio = parseInt(data.precio_final) || precioBase;
                    const duracionDias = parseInt(data.duracion_dias) || 30;
                    
                    console.log('💰 Precios:', { precioBase, precioConConvenio, duracionDias });
                    
                    const precioBoxEl = document.getElementById('precioBox');
                    if (precioBoxEl) {
                        precioBoxEl.style.display = 'block';
                        console.log('📦 Mostrando precio-box');
                    } else {
                        console.error('❌ Elemento precioBox NO encontrado');
                    }
                    
                    const normalEl = document.getElementById('precio-normal');
                    const convenioEl = document.getElementById('precio-convenio');
                    
                    if (normalEl) {
                        normalEl.textContent = '$' + precioBase.toLocaleString('es-CL');
                        console.log('✅ Precio normal actualizado');
                    }
                    if (convenioEl) {
                        convenioEl.textContent = '$' + precioConConvenio.toLocaleString('es-CL');
                        console.log('✅ Precio convenio actualizado');
                    }
                    
                    if (fechaInicio && fechaInicio.value) {
                        const inicio = new Date(fechaInicio.value);
                        const termino = new Date(inicio);
                        termino.setDate(termino.getDate() + duracionDias);
                        
                        const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
                        const terminoFormato = termino.toLocaleDateString('es-CL', options);
                        
                        const fechaTerminoEl = document.getElementById('fecha-termino');
                        if (fechaTerminoEl) {
                            fechaTerminoEl.textContent = terminoFormato;
                            console.log('✅ Fecha término actualizada:', terminoFormato);
                        }
                    }
                    
                    actualizarPrecioFinal(precioConConvenio);
                })
                .catch(error => {
                    console.error('❌ Error en fetch:', error);
                    const precioBox = document.getElementById('precioBox');
                    if (precioBox) precioBox.style.display = 'none';
                });
        }

        function actualizarPrecioFinal(precioConConvenio = null) {
            const descuentoManualInput = document.getElementById('descuento_manual');
            const precioTotalEl = document.getElementById('precio-total');
            const descManualDisplay = document.getElementById('desc-manual-display');
            const precioFinalOculto = document.getElementById('precio-final-oculto');
            
            if (!precioTotalEl || !descuentoManualInput) {
                console.error('❌ Elementos precio-total o descuento_manual no encontrados');
                return;
            }
            
            if (precioConConvenio === null) {
                const convenioEl = document.getElementById('precio-convenio');
                if (convenioEl) {
                    const text = convenioEl.textContent.replace('$', '').replace(/\./g, '').trim();
                    precioConConvenio = parseInt(text) || 0;
                } else {
                    console.error('❌ Elemento precio-convenio no encontrado');
                    return;
                }
            }
            
            const descuentoManual = parseInt(descuentoManualInput.value) || 0;
            const precioTotal = Math.max(0, precioConConvenio - descuentoManual);
            
            console.log('💵 Calculando precio final:', { precioConConvenio, descuentoManual, precioTotal });
            
            if (descManualDisplay) {
                descManualDisplay.textContent = descuentoManual > 0 ? '-$' + descuentoManual.toLocaleString('es-CL') : '-$0';
            }
            
            precioTotalEl.textContent = '$' + precioTotal.toLocaleString('es-CL');
            
            // Guardar precio final en campo oculto para validaciones
            if (precioFinalOculto) {
                precioFinalOculto.value = precioTotal;
                console.log('✅ Precio final guardado en campo oculto:', precioTotal);
            }
        }

        function actualizarNombreCliente() {
            const nombres = document.getElementById('nombres')?.value || '';
            const apellido = document.getElementById('apellido_paterno')?.value || '';
            const clienteNombreEl = document.getElementById('cliente-nombre');
            
            if (clienteNombreEl) {
                const nombreCompleto = (nombres + ' ' + apellido).trim() || 'Ingrese datos en Paso 1';
                clienteNombreEl.textContent = nombreCompleto;
                console.log('✅ Nombre cliente actualizado:', nombreCompleto);
            }
            
            // Actualizar también en el resumen del PASO 3 (solo si estamos en PASO 3)
            if (currentStep === 3) {
                actualizarResumenPaso3();
            }
        }

        function actualizarResumenPaso3() {
            console.log('🔄 Actualizando resumen PASO 3...');
            
            // Datos PASO 1
            const nombres = document.getElementById('nombres')?.value || '';
            const apellido = document.getElementById('apellido_paterno')?.value || '';
            const nombreCompleto = (nombres + ' ' + apellido).trim() || '-';
            
            // Datos PASO 2
            const membresiaSelect = document.getElementById('id_membresia');
            const membresiaText = membresiaSelect?.options[membresiaSelect?.selectedIndex]?.text || '- Seleccionar -';
            
            const convenioSelect = document.getElementById('id_convenio');
            const convenioValue = convenioSelect?.value;
            const convenioText = convenioValue ? convenioSelect.options[convenioSelect.selectedIndex]?.text : 'No';
            
            const motivoSelect = document.getElementById('id_motivo_descuento');
            const motivoValue = motivoSelect?.value;
            const motivoText = motivoValue ? motivoSelect.options[motivoSelect.selectedIndex]?.text : '-';
            
            const descuentoManualInput = document.getElementById('descuento_manual');
            const descuentoManual = parseInt(descuentoManualInput?.value || '0') || 0;
            
            // Obtener precio final (leer del elemento visible o del campo oculto)
            const precioTotalEl = document.getElementById('precio-total');
            let precioFinal = '$0';
            
            if (precioTotalEl?.textContent) {
                precioFinal = precioTotalEl.textContent;
            } else {
                const precioFinalOculto = document.getElementById('precio-final-oculto');
                if (precioFinalOculto?.value) {
                    precioFinal = '$' + parseInt(precioFinalOculto.value).toLocaleString('es-CL');
                }
            }
            
            // Actualizar elementos del resumen en PASO 3
            const resumenCliente = document.getElementById('resumen-cliente');
            const resumenMembresia = document.getElementById('resumen-membresia');
            const resumenConvenio = document.getElementById('resumen-convenio');
            const resumenMotivo = document.getElementById('resumen-motivo');
            const resumenDescManual = document.getElementById('resumen-desc-manual');
            const resumenPrecioFinal = document.getElementById('resumen-precio-final');
            
            if (resumenCliente) {
                resumenCliente.textContent = nombreCompleto;
                console.log('✅ Cliente:', nombreCompleto);
            }
            if (resumenMembresia) {
                resumenMembresia.textContent = membresiaText;
                console.log('✅ Membresía:', membresiaText);
            }
            if (resumenConvenio) {
                resumenConvenio.textContent = convenioText;
                console.log('✅ Convenio:', convenioText);
            }
            if (resumenMotivo) {
                resumenMotivo.textContent = motivoText;
                console.log('✅ Motivo Descuento:', motivoText);
            }
            if (resumenDescManual) {
                const descManualFormato = descuentoManual > 0 ? '-$' + descuentoManual.toLocaleString('es-CL') : '-$0';
                resumenDescManual.textContent = descManualFormato;
                console.log('✅ Descuento Manual:', descManualFormato);
            }
            if (resumenPrecioFinal) {
                resumenPrecioFinal.textContent = precioFinal;
                console.log('✅ Precio Final:', precioFinal);
            }
            
            console.log('✅ Resumen PASO 3 actualizado');
        }

        function actualizarTipoPago() {
            const tipoPago = document.getElementById('tipo_pago').value;
            const seccionMonto = document.getElementById('seccion-monto');
            const infoAdicional = document.getElementById('info-adicional');
            const alertTipoPago = document.getElementById('alert-tipo-pago');
            const labelMonto = document.getElementById('label-monto');
            const hintMonto = document.getElementById('hint-monto');
            const montoAbonado = document.getElementById('monto_abonado');
            const precioFinalText = document.getElementById('resumen-precio-final')?.textContent || '$0';
            const precioFinal = parseInt(precioFinalText.replace('$', '').replace(/\./g, '')) || 0;
            
            seccionMonto.style.display = 'none';
            infoAdicional.style.display = 'none';
            montoAbonado.value = '';
            montoAbonado.required = false;
            
            if (tipoPago === 'completo') {
                // Pago completo: mostrar monto como lectura y establecer el precio final
                seccionMonto.style.display = 'block';
                montoAbonado.value = precioFinal;
                montoAbonado.readonly = true;
                montoAbonado.required = true;
                labelMonto.textContent = 'Monto Total (Pago Completo)';
                hintMonto.textContent = 'El monto se establece automáticamente al precio final';
                
                infoAdicional.style.display = 'block';
                alertTipoPago.className = 'alert alert-success';
                alertTipoPago.innerHTML = '<i class="fas fa-check-circle"></i> <strong>Pago Completo:</strong> Se pagará el monto total de $' + precioFinal.toLocaleString('es-CL') + ' de una sola vez.';
                
            } else if (tipoPago === 'parcial') {
                // Pago parcial: permitir edición de monto
                seccionMonto.style.display = 'block';
                montoAbonado.readonly = false;
                montoAbonado.required = true;
                montoAbonado.min = '1';
                labelMonto.textContent = 'Monto a Abonar';
                hintMonto.textContent = 'Ingrese el monto que desea abonar. Quedarán pendientes: $' + precioFinal.toLocaleString('es-CL');
                
                infoAdicional.style.display = 'block';
                alertTipoPago.className = 'alert alert-info';
                alertTipoPago.innerHTML = '<i class="fas fa-info-circle"></i> <strong>Pago Parcial:</strong> El cliente puede abonar una parte. El saldo restante quedará pendiente de pago.';
                
            } else if (tipoPago === 'pendiente') {
                // Pago pendiente: sin mostrar campos de monto
                infoAdicional.style.display = 'block';
                alertTipoPago.className = 'alert alert-warning';
                alertTipoPago.innerHTML = '<i class="fas fa-clock"></i> <strong>Pago Pendiente:</strong> No se registrará pago. La inscripción se crea sin abonar. Total a pagar: $' + precioFinal.toLocaleString('es-CL');
                
            } else if (tipoPago === 'mixto') {
                // Pago mixto: permitir edición de monto
                seccionMonto.style.display = 'block';
                montoAbonado.readonly = false;
                montoAbonado.required = true;
                montoAbonado.min = '0';
                labelMonto.textContent = 'Monto Abonado (Parte 1)';
                hintMonto.textContent = 'Ingrese el monto de la primera parte del pago. Puede usar múltiples métodos o cuotas.';
                
                infoAdicional.style.display = 'block';
                alertTipoPago.className = 'alert alert-warning';
                alertTipoPago.innerHTML = '<i class="fas fa-shuffle"></i> <strong>Pago Mixto:</strong> Se pueden combinar múltiples pagos o métodos. Total a cubrir: $' + precioFinal.toLocaleString('es-CL');
            }
        }

        function handleFormSubmit(event) {
            event.preventDefault();

            const form = document.getElementById('clienteForm');
            const flujoInput = document.getElementById('flujo_cliente');
            const flujo = flujoInput.value;

            // Validar el paso actual antes de guardar
            const validacion = validarPasoCompleto(currentStep);
            if (!validacion.valido) {
                mostrarErrorValidacion(validacion.errores);
                return false;
            }

            // Preparar mensaje de confirmación según flujo
            let titulo = '';
            let icono = 'info';
            let mensaje = '';
            let colorbtn = '#28a745';

            if (flujo === 'solo_cliente') {
                titulo = 'Guardar cliente';
                icono = 'info';
                const nombre = document.getElementById('nombres')?.value || '';
                const apellido = document.getElementById('apellido_paterno')?.value || '';
                const email = document.getElementById('email')?.value || '';
                mensaje = `<div style="text-align: left; line-height: 1.8;">
                          <p style="color: #495057; margin-bottom: 12px;"><i class="fas fa-user" style="color: #17a2b8; margin-right: 8px;"></i> <strong>Datos del cliente:</strong></p>
                          <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 6px 0; padding-left: 24px;"><strong>Nombre:</strong> ${nombre} ${apellido}</li>
                            <li style="padding: 6px 0; padding-left: 24px;"><strong>Email:</strong> ${email}</li>
                          </ul>
                          </div>`;
                colorbtn = '#17a2b8';
            } else if (flujo === 'con_membresia') {
                titulo = 'Guardar cliente con membresía';
                icono = 'info';
                const nombre = document.getElementById('nombres')?.value || '';
                const membresiaEl = document.getElementById('id_membresia');
                const membresia = membresiaEl?.options[membresiaEl?.selectedIndex]?.text || '';
                const fechaInicio = document.getElementById('fecha_inicio')?.value || '';
                mensaje = `<div style="text-align: left; line-height: 1.8;">
                          <p style="color: #495057; margin-bottom: 12px;"><i class="fas fa-id-card" style="color: #007bff; margin-right: 8px;"></i> <strong>Información del registro:</strong></p>
                          <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 6px 0; padding-left: 24px;"><strong>Cliente:</strong> ${nombre}</li>
                            <li style="padding: 6px 0; padding-left: 24px;"><strong>Membresía:</strong> ${membresia}</li>
                            <li style="padding: 6px 0; padding-left: 24px;"><strong>Fecha Inicio:</strong> ${fechaInicio}</li>
                          </ul>
                          </div>`;
                colorbtn = '#007bff';
            } else if (flujo === 'completo') {
                titulo = 'Confirmar registro completo';
                icono = 'question';
                const nombre = document.getElementById('nombres')?.value || '';
                const membresiaEl = document.getElementById('id_membresia');
                const membresia = membresiaEl?.options[membresiaEl?.selectedIndex]?.text || '';
                const tipoEl = document.getElementById('tipo_pago');
                const tipo = tipoEl?.options[tipoEl?.selectedIndex]?.text || '';
                const precio = document.getElementById('resumen-precio-final')?.textContent || '$0';
                const metodo = document.getElementById('id_metodo_pago')?.options[document.getElementById('id_metodo_pago')?.selectedIndex]?.text || '';
                mensaje = `<div style="text-align: left; line-height: 1.8;">
                          <p style="color: #495057; margin-bottom: 12px;"><i class="fas fa-check-circle" style="color: #28a745; margin-right: 8px;"></i> <strong>Se guardará el siguiente registro:</strong></p>
                          <div style="background-color: #f8f9fa; border-radius: 8px; padding: 12px; margin-top: 8px;">
                            <div style="padding: 6px 0;"><i class="fas fa-user" style="color: #6c757d; margin-right: 8px; width: 20px;"></i><strong>Cliente:</strong> ${nombre}</div>
                            <div style="padding: 6px 0;"><i class="fas fa-dumbbell" style="color: #6c757d; margin-right: 8px; width: 20px;"></i><strong>Membresía:</strong> ${membresia}</div>
                            <div style="padding: 6px 0;"><i class="fas fa-credit-card" style="color: #6c757d; margin-right: 8px; width: 20px;"></i><strong>Tipo Pago:</strong> ${tipo}</div>
                            <div style="padding: 6px 0;"><i class="fas fa-piggy-bank" style="color: #6c757d; margin-right: 8px; width: 20px;"></i><strong>Método:</strong> ${metodo}</div>
                            <div style="padding: 6px 0; border-top: 2px solid #dee2e6; margin-top: 8px; padding-top: 8px;"><i class="fas fa-money-bill" style="color: #28a745; margin-right: 8px; width: 20px;"></i><strong>Monto Total:</strong> <span style="color: #28a745; font-size: 1.1em;">${precio}</span></div>
                          </div>
                          </div>`;
                colorbtn = '#28a745';
            }

            // Mostrar confirmación
            Swal.fire({
                icon: icono,
                title: `<i class="fas fa-save" style="margin-right: 8px;"></i> ${titulo}`,
                html: mensaje,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check" style="margin-right: 8px;"></i> Sí, guardar',
                cancelButtonText: '<i class="fas fa-times" style="margin-right: 8px;"></i> Cancelar',
                confirmButtonColor: colorbtn,
                cancelButtonColor: '#6c757d',
                customClass: {
                    popup: 'swal-confirm-popup',
                    title: 'swal-confirm-title',
                    htmlContainer: 'swal-confirm-content'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: '<i class="fas fa-hourglass-start"></i> Guardando...',
                        html: '<i class="fas fa-spinner fa-spin" style="font-size: 2em; color: #28a745;"></i><br/><p style="margin-top: 12px; color: #495057;">Por favor espere mientras procesamos su solicitud</p>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Deshabilitar botones
                    document.querySelectorAll('button[type="submit"]').forEach(btn => {
                        btn.disabled = true;
                    });

                    // Enviar formulario
                    form.submit();
                }
            });

            return false;
        }

        document.addEventListener('DOMContentLoaded', function() {
            goToStep(1);
            
            const form = document.getElementById('clienteForm');
            const convenioSelect = document.getElementById('id_convenio');
            const membresiaSelect = document.getElementById('id_membresia');
            const fechaInicio = document.getElementById('fecha_inicio');
            const nombresInput = document.getElementById('nombres');
            const apellidoInput = document.getElementById('apellido_paterno');
            const descuentoManualInput = document.getElementById('descuento_manual');
            const motivoDescuentoSelect = document.getElementById('id_motivo_descuento');
            
            // Detectar cambios en cualquier input
            form.addEventListener('change', function() {
                hayDatosNoGuardados = true;
            });
            form.addEventListener('input', function() {
                hayDatosNoGuardados = true;
            });

            // Advertencia si intenta salir sin guardar
            window.addEventListener('beforeunload', function(e) {
                if (hayDatosNoGuardados) {
                    e.preventDefault();
                    e.returnValue = '';
                    return '';
                }
            });

            // Botón Cancelar
            const btnCancelar = document.querySelector('a[href*="clientes"]');
            if (btnCancelar) {
                btnCancelar.addEventListener('click', function(e) {
                    if (hayDatosNoGuardados) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: '<i class="fas fa-exclamation-triangle" style="color: #ff6b6b;"></i> ¿Salir sin guardar?',
                            html: `<div style="text-align: left; line-height: 1.8; margin-top: 12px;">
                                    <p style="color: #495057;">Tiene cambios sin guardar que se perderán:</p>
                                    <ul style="list-style: none; padding: 0; margin: 12px 0;">
                                        <li style="padding: 6px 0; padding-left: 24px;"><i class="fas fa-times-circle" style="color: #dc3545; margin-right: 8px;"></i>Los datos ingresados no se guardarán</li>
                                        <li style="padding: 6px 0; padding-left: 24px;"><i class="fas fa-info-circle" style="color: #17a2b8; margin-right: 8px;"></i>Puede volver al formulario y guardar</li>
                                    </ul>
                                  </div>`,
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Sí, salir',
                            cancelButtonText: '<i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Volver',
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            customClass: {
                                popup: 'swal-warning-popup',
                                title: 'swal-warning-title',
                                htmlContainer: 'swal-warning-content'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = e.target.href;
                            }
                        });
                    }
                });
            }
            
            if (convenioSelect) {
                convenioSelect.addEventListener('change', function() {
                    actualizarPrecio();
                    if (currentStep === 3) {
                        actualizarResumenPaso3();
                    }
                    hayDatosNoGuardados = true;
                });
            }
            if (membresiaSelect) {
                membresiaSelect.addEventListener('change', function() {
                    actualizarPrecio();
                    if (currentStep === 3) {
                        actualizarResumenPaso3();
                    }
                    hayDatosNoGuardados = true;
                });
            }
            if (fechaInicio) {
                fechaInicio.addEventListener('change', function() {
                    actualizarPrecio();
                    if (currentStep === 3) {
                        actualizarResumenPaso3();
                    }
                    hayDatosNoGuardados = true;
                });
            }
            
            if (nombresInput) {
                nombresInput.addEventListener('change', actualizarNombreCliente);
                nombresInput.addEventListener('input', actualizarNombreCliente);
            }
            if (apellidoInput) {
                apellidoInput.addEventListener('change', actualizarNombreCliente);
                apellidoInput.addEventListener('input', actualizarNombreCliente);
            }
            
            if (descuentoManualInput) {
                descuentoManualInput.addEventListener('change', function() {
                    actualizarPrecioFinal();
                    if (currentStep === 3) {
                        actualizarResumenPaso3();
                    }
                });
                descuentoManualInput.addEventListener('input', function() {
                    actualizarPrecioFinal();
                    if (currentStep === 3) {
                        actualizarResumenPaso3();
                    }
                });
            }

            if (motivoDescuentoSelect) {
                motivoDescuentoSelect.addEventListener('change', function() {
                    if (currentStep === 3) {
                        actualizarResumenPaso3();
                    }
                });
            }

            // Resetear flag cuando se guarda exitosamente
            form.addEventListener('submit', function() {
                hayDatosNoGuardados = false;
            });
        });
    </script>
@endsection

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-user-plus"></i> Crear Nuevo Cliente
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Errores en el Formulario</h4>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks"></i> Registro de Cliente - 3 Pasos
            </h3>
        </div>

        <div class="card-body">
            <div id="formAlerts"></div>
            
            <div class="steps-nav">
                <button type="button" class="step-btn active" onclick="goToStep(1)" id="step1-btn">
                    <i class="fas fa-user"></i> Paso 1: Datos
                </button>
                <button type="button" class="step-btn" onclick="goToStep(2)" id="step2-btn" disabled>
                    <i class="fas fa-dumbbell"></i> Paso 2: Membresía
                </button>
                <button type="button" class="step-btn" onclick="goToStep(3)" id="step3-btn" disabled>
                    <i class="fas fa-credit-card"></i> Paso 3: Pago
                </button>
            </div>

            <form action="{{ route('admin.clientes.store') }}" method="POST" id="clienteForm" onsubmit="return handleFormSubmit(event)">
                @csrf
                <input type="hidden" id="form_submit_token" name="form_submit_token" value="{{ uniqid() }}">
                <input type="hidden" id="flujo_cliente" name="flujo_cliente" value="solo_cliente">

                <!-- PASO 1: DATOS DEL CLIENTE -->
                <div class="step-indicator active" id="step-1">
                    
                    <div class="form-section-title">
                        <i class="fas fa-id-card"></i> Identificación
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="run_pasaporte" class="form-label">RUT/Pasaporte</label>
                            <input type="text" class="form-control @error('run_pasaporte') is-invalid @enderror" 
                                   id="run_pasaporte" name="run_pasaporte" placeholder="Ej: 7.882.382-4" 
                                   value="{{ old('run_pasaporte') }}">
                            @error('run_pasaporte')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-user"></i> Datos Personales
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                                   id="nombres" name="nombres" value="{{ old('nombres') }}" required>
                            @error('nombres')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror" 
                                   id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required>
                            @error('apellido_paterno')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="apellido_materno" class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control @error('apellido_materno') is-invalid @enderror" 
                                   id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}">
                            @error('apellido_materno')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                   id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}">
                            @error('fecha_nacimiento')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-phone"></i> Contacto
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="celular" class="form-label">Celular <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('celular') is-invalid @enderror" 
                                   id="celular" name="celular" value="{{ old('celular') }}" required>
                            @error('celular')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-heart-pulse"></i> Contacto de Emergencia
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contacto_emergencia" class="form-label">Nombre del Contacto</label>
                            <input type="text" class="form-control @error('contacto_emergencia') is-invalid @enderror" 
                                   id="contacto_emergencia" name="contacto_emergencia" 
                                   value="{{ old('contacto_emergencia') }}">
                            @error('contacto_emergencia')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono_emergencia" class="form-label">Teléfono del Contacto</label>
                            <input type="tel" class="form-control @error('telefono_emergencia') is-invalid @enderror" 
                                   id="telefono_emergencia" name="telefono_emergencia" 
                                   value="{{ old('telefono_emergencia') }}">
                            @error('telefono_emergencia')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-map-marker-alt"></i> Domicilio
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                   id="direccion" name="direccion" value="{{ old('direccion') }}">
                            @error('direccion')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-sticky-note"></i> Observaciones
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="observaciones" class="form-label">Notas Adicionales</label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                      id="observaciones" name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <!-- PASO 2: MEMBRESÍA -->
                <div class="step-indicator" id="step-2">
                    
                    <div class="alert alert-info">
                        <strong><i class="fas fa-user"></i> Cliente:</strong> 
                        <span id="cliente-nombre">Ingrese datos en Paso 1</span>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-dumbbell"></i> Seleccionar Membresía
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_membresia" class="form-label">Membresía <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_membresia') is-invalid @enderror" 
                                    id="id_membresia" name="id_membresia" onchange="actualizarPrecio()">
                                <option value="">-- Seleccionar Membresía --</option>
                                @foreach($membresias as $membresia)
                                    <option value="{{ $membresia->id }}" {{ old('id_membresia') == $membresia->id ? 'selected' : '' }}>
                                        {{ $membresia->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_membresia')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                   id="fecha_inicio" name="fecha_inicio" 
                                   value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}"
                                   onchange="actualizarPrecio()">
                            @error('fecha_inicio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-handshake"></i> Convenio / Descuento
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_convenio" class="form-label">¿Tiene Convenio?</label>
                            <select class="form-control @error('id_convenio') is-invalid @enderror" 
                                    id="id_convenio" name="id_convenio" onchange="actualizarPrecio()">
                                <option value="">-- Sin Convenio --</option>
                                @foreach($convenios as $convenio)
                                    <option value="{{ $convenio->id }}" {{ old('id_convenio') == $convenio->id ? 'selected' : '' }}>
                                        {{ $convenio->nombre }} ({{ $convenio->descuento_porcentaje }}%)
                                    </option>
                                @endforeach
                            </select>
                            @error('id_convenio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_motivo_descuento" class="form-label">Motivo del Descuento</label>
                            <select class="form-control @error('id_motivo_descuento') is-invalid @enderror" 
                                    id="id_motivo_descuento" name="id_motivo_descuento">
                                <option value="">-- Sin Motivo --</option>
                                @php
                                    $motivosDescuento = \App\Models\MotivoDescuento::where('activo', true)->get();
                                @endphp
                                @foreach($motivosDescuento as $motivo)
                                    <option value="{{ $motivo->id }}" {{ old('id_motivo_descuento') == $motivo->id ? 'selected' : '' }}>
                                        {{ $motivo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_motivo_descuento')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="descuento_manual" class="form-label">Descuento Manual ($)</label>
                            <input type="number" class="form-control @error('descuento_manual') is-invalid @enderror" 
                                   id="descuento_manual" name="descuento_manual" 
                                   min="0" step="1" value="{{ old('descuento_manual', 0) }}"
                                   placeholder="0"
                                   onchange="actualizarPrecioFinal()"
                                   oninput="actualizarPrecioFinal()">
                            @error('descuento_manual')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="observaciones_inscripcion" class="form-label">Observaciones</label>
                            <input type="text" class="form-control" 
                                   id="observaciones_inscripcion" name="observaciones_inscripcion" 
                                   placeholder="Notas sobre la inscripción"
                                   value="{{ old('observaciones_inscripcion', '') }}">
                        </div>
                    </div>

                    <div class="precio-box" id="precioBox" style="display:none;">
                        <!-- Campo oculto para guardar precio final -->
                        <input type="hidden" id="precio-final-oculto" value="0">
                        
                        <h5><i class="fas fa-tag"></i> Resumen de Precios</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div><strong>Precio Base:</strong> <span id="precio-normal" style="font-size: 1.1em; color: #667eea;">$0</span></div>
                                <div style="margin-top: 0.5rem;"><strong>Convenio:</strong> <span id="precio-convenio" style="color: #28a745;">$0</span></div>
                            </div>
                            <div class="col-md-6">
                                <div><strong>Descuento Manual:</strong> <span id="desc-manual-display" style="color: #dc3545;">-$0</span></div>
                            </div>
                        </div>
                        <hr>
                        <div style="font-size: 1.2em; text-align: center;">
                            <strong>Precio Final: <span id="precio-total" style="color: #667eea; font-size: 1.3em;">$0</span></strong>
                        </div>
                        <div style="margin-top: 1rem; text-align: center; color: #666; font-size: 0.9em;">
                            <strong>Fecha de Término:</strong> <span id="fecha-termino">-</span>
                        </div>
                    </div>

                </div>

                <!-- PASO 3: PAGO -->
                <div class="step-indicator" id="step-3">
                    
                    <!-- RESUMEN DE PASOS ANTERIORES -->
                    <div class="card card-info mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Resumen del Registro</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Cliente:</strong> <span id="resumen-cliente">-</span></p>
                                    <p><strong>Membresía:</strong> <span id="resumen-membresia">-</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Convenio:</strong> <span id="resumen-convenio">No</span></p>
                                    <p><strong>Descuento Motivo:</strong> <span id="resumen-motivo">-</span></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <p><strong>Descuento Manual:</strong> <span id="resumen-desc-manual">$0</span></p>
                                    <p style="font-size: 1.1em; color: #667eea;">
                                        <strong>Precio Final a Pagar:</strong> 
                                        <span id="resumen-precio-final" style="font-size: 1.2em;">$0</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INFORMACIÓN DE PAGO -->
                    <div class="form-section-title">
                        <i class="fas fa-credit-card"></i> Información de Pago
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_pago" class="form-label">Tipo de Pago <span class="text-danger">*</span></label>
                            <select class="form-control @error('tipo_pago') is-invalid @enderror" 
                                    id="tipo_pago" name="tipo_pago" onchange="actualizarTipoPago()">
                                <option value="">-- Seleccionar Tipo --</option>
                                <option value="completo">Pago Completo (Todo de una)</option>
                                <option value="parcial">Pago Parcial / Abono</option>
                                <option value="pendiente">Pago Pendiente (Sin pagar)</option>
                                <option value="mixto">Pago Mixto (Combinado)</option>
                            </select>
                            @error('tipo_pago')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_pago" class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                   id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}">
                            @error('fecha_pago')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- SECCIÓN DE MONTO (se muestra según tipo de pago) -->
                    <div id="seccion-monto" style="display:none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="monto_abonado" class="form-label" id="label-monto">Monto a Abonar <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('monto_abonado') is-invalid @enderror" 
                                       id="monto_abonado" name="monto_abonado" min="0" step="1" 
                                       value="{{ old('monto_abonado', '') }}" placeholder="Ingrese monto">
                                <small class="text-muted" id="hint-monto"></small>
                                @error('monto_abonado')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="id_metodo_pago" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                                <select class="form-control @error('id_metodo_pago') is-invalid @enderror" 
                                        id="id_metodo_pago" name="id_metodo_pago">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($metodos_pago as $metodo)
                                        <option value="{{ $metodo->id }}" {{ old('id_metodo_pago') == $metodo->id ? 'selected' : '' }}>
                                            {{ $metodo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_metodo_pago')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- INFORMACIÓN ADICIONAL SEGÚN TIPO DE PAGO -->
                    <div id="info-adicional" style="display:none;">
                        <div class="alert alert-warning" id="alert-tipo-pago"></div>
                    </div>

                </div>

                <hr class="my-4">

                <div class="buttons-container">
                    <div>
                        <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <div class="buttons-group">
                        <button type="button" id="btn-anterior" class="btn btn-outline-secondary" onclick="previousStep()" style="display:none;">
                            <i class="fas fa-arrow-left"></i> Anterior
                        </button>
                        <button type="button" id="btn-siguiente" class="btn btn-primary" onclick="nextStep()">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>

                        <button type="submit" id="btn-guardar-solo-cliente" class="btn btn-info" style="display:none;">
                            <i class="fas fa-user-check"></i> Guardar Cliente
                        </button>

                        <button type="submit" id="btn-guardar-con-membresia" class="btn btn-success" style="display:none;">
                            <i class="fas fa-layer-group"></i> Guardar con Membresía
                        </button>

                        <button type="submit" id="btn-guardar-completo" class="btn btn-success" style="display:none;">
                            <i class="fas fa-check-circle"></i> Guardar Todo
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Scripts adicionales aquí si se necesita
    </script>
@endpush
