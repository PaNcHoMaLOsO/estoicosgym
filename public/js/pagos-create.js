/**
 * Gestión de Pagos - Formulario de Creación
 * Interactividad dinámica para registro de pagos, abonos y planes de cuotas
 */

class PagosCreateManager {
    constructor() {
        this.inscripcionSeleccionada = null;
        this.tipoPago = 'simple';
        this.init();
    }

    init() {
        this.cacheElements();
        this.bindEvents();
        this.initializeSelect2();
        
        // Inicializar valores por defecto
        this.cantidadCuotas.value = this.cantidadCuotas.value || 1;
        this.tipoPagoSimple.checked = true;  // Por defecto es pago simple
        this.onTipoPagoChange('simple');
    }

    cacheElements() {
        // Inputs principales
        this.idInscripcion = document.getElementById('id_inscripcion');
        this.tipoPagoSimple = document.getElementById('tipoPagoSimple');
        this.tipoPagoCuotas = document.getElementById('tipoPagoCuotas');
        this.idMetodoPago = document.getElementById('id_metodo_pago_principal');
        this.fechaPago = document.getElementById('fecha_pago');
        this.montoAbonado = document.getElementById('monto_abonado');
        this.cantidadCuotas = document.getElementById('cantidad_cuotas');
        this.montoPorCuota = document.getElementById('monto_por_cuota');
        this.fechaVencimientoCuota = document.getElementById('fecha_vencimiento_cuota');
        this.referenciaPago = document.getElementById('referencia_pago');
        this.esPlanCuotas = document.getElementById('es_plan_cuotas');
        
        // Elementos de UI
        this.cardMetodo = document.getElementById('cardMetodo');
        this.cardDetalles = document.getElementById('cardDetalles');
        this.seccionCuotas = document.getElementById('seccionCuotas');
        this.saldoInfo = document.getElementById('saldoInfo');
        this.previewCuotas = document.getElementById('previewCuotas');
        this.btnSubmit = document.getElementById('btnSubmit');
        this.formPago = document.getElementById('formPago');
        
        // Labels de saldo
        this.totalAPagar = document.getElementById('totalAPagar');
        this.totalAbonado = document.getElementById('totalAbonado');
        this.saldoPendiente = document.getElementById('saldoPendiente');
        this.porcentajePagado = document.getElementById('porcentajePagado');
        
        // Detalles de inscripción
        this.membresiaNombre = document.getElementById('membresiaNombre');
        this.periodoInscripcion = document.getElementById('periodoInscripcion');
        this.clienteNombre = document.getElementById('clienteNombre');
        this.clienteEmail = document.getElementById('clienteEmail');
    }

    bindEvents() {
        // Cambio de inscripción - usando select2:select event que es más confiable
        $('#id_inscripcion').on('select2:select', (e) => {
            console.log('Select2 change event triggered');
            this.onInscripcionChange();
        });

        // Cambio de tipo de pago
        this.tipoPagoSimple.addEventListener('change', () => this.onTipoPagoChange('simple'));
        this.tipoPagoCuotas.addEventListener('change', () => this.onTipoPagoChange('cuotas'));

        // Cambios en montos
        this.montoAbonado.addEventListener('input', () => this.calcularPreviewCuotas());
        this.montoAbonado.addEventListener('change', () => this.validarFormulario());

        // Cambios en cuotas
        this.cantidadCuotas.addEventListener('change', () => this.calcularPreviewCuotas());
        this.cantidadCuotas.addEventListener('change', () => this.validarFormulario());

        // Validación del formulario
        this.formPago.addEventListener('submit', (e) => this.onSubmit(e));
    }

    initializeSelect2() {
        // Select2 para inscripción
        const $select = $('#id_inscripcion');
        
        // Si ya hay valor (pre-cargado desde inscripción.show), cargar los datos
        if (this.idInscripcion.value) {
            console.log('Inscripción pre-cargada:', this.idInscripcion.value);
            // Disparar el evento change para cargar información
            setTimeout(() => this.onInscripcionChange(), 500);
            return; // No inicializar select2 si ya tiene valor
        }

        // Inicializar Select2 con búsqueda AJAX
        $select.select2({
            theme: 'bootstrap-5',
            language: 'es',
            allowClear: true,
            placeholder: '📝 Escribe nombre o email (mín 2 caracteres)',
            ajax: {
                url: '/api/inscripciones/search',
                dataType: 'json',
                delay: 300,
                data: (params) => {
                    const searchTerm = params.term || '';
                    console.log('Buscando:', searchTerm);
                    return {
                        q: searchTerm,
                        activa: true
                    };
                },
                processResults: (data) => {
                    console.log('Resultados recibidos:', data.length, 'inscripciones');
                    return {
                        results: data
                    };
                },
                error: () => {
                    console.error('Error en la búsqueda AJAX');
                }
            },
            minimumInputLength: 2,
            templateResult: this.formatInscripcionResult.bind(this),
            templateSelection: this.formatInscripcionSelection.bind(this),
            escapeMarkup: (markup) => markup  // No escapar HTML en templates
        });

        // Debug: Log cuando se abre el dropdown
        $select.on('select2:opening', () => {
            console.log('Select2 opening');
        });

        // Debug: Log cuando se cierra
        $select.on('select2:closing', () => {
            console.log('Select2 closing');
        });
    }

    formatInscripcionResult(data) {
        if (data.loading) return data.text;
        
        // Mostrar información clara en el dropdown
        const saldoFormatted = this.formatMoney(data.saldo || 0);
        const totalFormatted = this.formatMoney(data.total_a_pagar || 0);
        
        return `<div style="padding: 8px 0;">
                    <div style="font-weight: bold; color: #333;">
                        #${data.id} - ${data.nombre}
                    </div>
                    <div style="font-size: 12px; color: #666; margin-top: 4px;">
                        Total: $${totalFormatted} | Saldo: <span style="color: #d9534f; font-weight: bold;">$${saldoFormatted}</span>
                    </div>
                </div>`;
    }

    formatInscripcionSelection(data) {
        // Mostrar en el campo seleccionado
        if (!data.id) return data.text;
        return `#${data.id} - ${data.nombre}`;
    }

    onInscripcionChange() {
        const idInscripcion = this.idInscripcion.value;
        
        console.log('onInscripcionChange disparado. ID:', idInscripcion);
        
        if (!idInscripcion) {
            console.warn('No hay inscripción seleccionada');
            this.cardMetodo.style.display = 'none';
            this.cardDetalles.style.display = 'none';
            this.saldoInfo.style.display = 'none';
            this.btnSubmit.disabled = true;
            return;
        }

        // Mostrar spinner de carga
        console.log('Fetching saldo para inscripción:', idInscripcion);
        
        // Fetch de información de saldo
        fetch(`/api/inscripciones/${idInscripcion}/saldo`)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos de saldo recibidos:', data);
                this.actualizarSaldoInfo(data);
                this.cardMetodo.style.display = 'block';
                this.cardDetalles.style.display = 'block';
                this.saldoInfo.style.display = 'block';
                this.validarFormulario();
            })
            .catch(error => {
                console.error('Error fetching saldo:', error);
                alert('❌ Error al cargar la información de saldo.\n\nDet: ' + error.message);
            });
    }

    actualizarSaldoInfo(data) {
        this.totalAPagar.textContent = `$ ${this.formatMoney(data.total_a_pagar || 0)}`;
        this.totalAbonado.textContent = `$ ${this.formatMoney(data.total_abonado || 0)}`;
        this.saldoPendiente.textContent = `$ ${this.formatMoney(data.saldo_pendiente || 0)}`;
        
        // Calcular porcentaje pagado
        const totalAPagar = parseFloat(data.total_a_pagar) || 0;
        const totalAbonado = parseFloat(data.total_abonado) || 0;
        const porcentaje = totalAPagar > 0 ? ((totalAbonado / totalAPagar) * 100).toFixed(0) : 0;
        this.porcentajePagado.textContent = `${porcentaje}%`;
        
        // Mostrar detalles de inscripción
        if (data.membresia_nombre) {
            this.membresiaNombre.textContent = data.membresia_nombre;
        }
        if (data.periodo) {
            this.periodoInscripcion.textContent = data.periodo;
        }
        if (data.cliente_nombre) {
            this.clienteNombre.textContent = data.cliente_nombre;
        }
        if (data.cliente_email) {
            this.clienteEmail.textContent = data.cliente_email;
        }
        
        this.inscripcionSeleccionada = data;
        
        // Establer máximo para monto abonado
        this.montoAbonado.max = (data.saldo_pendiente || 0).toString();
    }

    onTipoPagoChange(tipo) {
        this.tipoPago = tipo;
        this.esPlanCuotas.value = tipo === 'cuotas' ? '1' : '0';
        
        if (tipo === 'simple') {
            this.seccionCuotas.classList.add('hidden');
            this.cantidadCuotas.value = '1';  // Importante: siempre debe tener un valor
            this.montoPorCuota.value = '';
            this.cantidadCuotas.removeAttribute('required');
        } else {
            this.seccionCuotas.classList.remove('hidden');
            this.cantidadCuotas.value = '2';
            this.cantidadCuotas.setAttribute('required', 'required');
            this.calcularPreviewCuotas();
        }
        
        this.validarFormulario();
    }

    calcularPreviewCuotas() {
        if (this.tipoPago !== 'cuotas') return;

        const monto = parseFloat(this.montoAbonado.value) || 0;
        const cantidadCuotas = parseInt(this.cantidadCuotas.value) || 1;

        if (monto <= 0 || cantidadCuotas < 2) {
            this.previewCuotas.innerHTML = `
                <div class="text-muted text-center py-3">
                    <small>Ingresa el monto y cantidad de cuotas para ver la distribución</small>
                </div>
            `;
            return;
        }

        // Calcular monto por cuota
        const montoPorCuota = monto / cantidadCuotas;
        this.montoPorCuota.value = montoPorCuota.toFixed(2);

        // Generar preview de cuotas
        let fechaVencimiento = new Date(this.fechaVencimiento.value || new Date());
        let html = '';

        for (let i = 1; i <= cantidadCuotas; i++) {
            const numeroMes = i - 1;
            const fechaCuota = new Date(fechaVencimiento);
            fechaCuota.setMonth(fechaCuota.getMonth() + numeroMes);
            
            html += `
                <div class="cuota-row">
                    <strong>Cuota #${i}</strong>
                    <span class="flex-grow-1">$ ${this.formatMoney(montoPorCuota)}</span>
                    <span class="badge-custom estado-badge">
                        ${this.formatDate(fechaCuota)}
                    </span>
                </div>
            `;
        }

        this.previewCuotas.innerHTML = html;
    }

    validarFormulario() {
        const idInscripcion = this.idInscripcion.value;
        const montoAbonado = parseFloat(this.montoAbonado.value) || 0;
        const idMetodo = this.idMetodoPago.value;
        const cantidadCuotas = parseInt(this.cantidadCuotas.value) || 0;
        
        // Validación de tipo de pago
        const tipoPagoValido = this.tipoPago !== 'cuotas' || cantidadCuotas >= 2;

        const formValido = idInscripcion && montoAbonado > 0 && idMetodo && tipoPagoValido;
        
        console.log('Validación:', {
            idInscripcion: idInscripcion ? '✓' : '✗',
            montoAbonado: montoAbonado > 0 ? '✓' : '✗',
            idMetodo: idMetodo ? '✓' : '✗',
            tipoPago: this.tipoPago,
            cantidadCuotas: cantidadCuotas,
            formValido: formValido
        });
        
        // Validación de máximo
        if (this.inscripcionSeleccionada && montoAbonado > this.inscripcionSeleccionada.saldo_pendiente) {
            this.btnSubmit.disabled = true;
            this.montoAbonado.classList.add('is-invalid');
            console.warn('Monto excede saldo pendiente');
            return;
        } else {
            this.montoAbonado.classList.remove('is-invalid');
        }

        this.btnSubmit.disabled = !formValido;
        
        if (!formValido) {
            console.warn('Formulario NO válido. Falta completar:', {
                idInscripcion: !idInscripcion ? 'inscripción' : null,
                montoAbonado: montoAbonado <= 0 ? 'monto' : null,
                idMetodo: !idMetodo ? 'método' : null,
                tipoPago: !tipoPagoValido ? 'cuotas' : null
            });
        }
    }

    onSubmit(e) {
        if (!this.btnSubmit.disabled) {
            // El formulario se envía normalmente
            return true;
        }
        
        e.preventDefault();
        alert('Por favor completa todos los campos correctamente');
        return false;
    }

    // Utilidades
    formatMoney(value) {
        return parseFloat(value).toLocaleString('es-CL', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    formatDate(date) {
        return date.toLocaleDateString('es-CL', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new PagosCreateManager();
});
