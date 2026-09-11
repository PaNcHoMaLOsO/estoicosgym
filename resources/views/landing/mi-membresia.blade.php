@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
        <!-- ===== CONSULTA MEMBRESÍA SECTION ===== -->
        <section id="consulta" class="pt-36 pb-24 bg-pg-carbon relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-px bg-linear-to-r from-transparent via-pg-rojo/30 to-transparent"></div>
            
            <!-- Glow decorativo -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-pg-rojo/10 rounded-full blur-3xl"></div>
            
            <div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-12 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">¿Ya eres miembro?</span>
                    <h1 class="font-display text-4xl md:text-5xl mt-4 text-pg-tiza">CONSULTA TU MEMBRESÍA</h1>
                    <p class="text-pg-tiza/60 mt-4 max-w-2xl mx-auto font-modern">
                        Ingresa tu RUT y los últimos 4 dígitos de tu celular para ver cómo está tu membresía.
                    </p>
                </div>
                
                {{-- El formulario con un acompañante al lado: solo, en una pantalla
                     ancha quedaba un recuadro angosto en medio de todo negro. --}}
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:items-start">
                <div class="max-w-md w-full mx-auto animate-on-scroll">
                    <div class="bg-pg-negro/50 border border-pg-tiza/10 rounded-2xl p-8">
                        <!-- Tabs de consulta -->
                        <div class="flex mb-6 bg-pg-carbon/50 rounded-lg p-1">
                            <button type="button" id="tab-rut" class="flex-1 py-2 px-4 rounded-md text-sm font-modern transition-all bg-pg-rojo text-white">
                                <i class="fas fa-id-card mr-1"></i> Con RUT
                            </button>
                            <button type="button" id="tab-celular" class="flex-1 py-2 px-4 rounded-md text-sm font-modern transition-all text-pg-tiza/60 hover:text-pg-tiza">
                                <i class="fas fa-mobile-alt mr-1"></i> Con Celular
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Honeypot anti-bot (oculto) -->
                            <div class="hp-field" aria-hidden="true">
                                <input type="text" name="website" id="consulta-website" tabindex="-1" autocomplete="off">
                            </div>
                            
                            <!-- Formulario RUT -->
                            <div id="form-rut" class="space-y-4">
                                <div>
                                    <label for="rut-consulta" class="block text-sm font-medium mb-2 text-pg-tiza font-modern">
                                        <i class="fas fa-id-card mr-2 text-pg-rojo-claro"></i>RUT
                                    </label>
                                    <input 
                                        type="text" 
                                        id="rut-consulta" 
                                        placeholder="12.345.678-9"
                                        maxlength="12"
                                        class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder:text-pg-tiza/30 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-center text-lg tracking-wider"
                                    >
                                </div>
                                <div>
                                    <label for="digitos-consulta" class="block text-sm font-medium mb-2 text-pg-tiza font-modern">
                                        <i class="fas fa-mobile-alt mr-2 text-pg-rojo-claro"></i>Últimos 4 dígitos de tu celular
                                    </label>
                                    <input
                                        type="text"
                                        id="digitos-consulta"
                                        inputmode="numeric"
                                        autocomplete="off"
                                        placeholder="••••"
                                        maxlength="4"
                                        class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder:text-pg-tiza/30 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-center text-lg tracking-[0.5em]"
                                    >
                                    <p class="text-pg-tiza/40 text-xs mt-1 font-modern text-center">El que registraste en el gimnasio: así nadie más puede ver tu membresía.</p>
                                </div>
                            </div>
                            
                            <!-- Formulario Celular (oculto inicialmente) -->
                            <div id="form-celular" class="space-y-4 hidden">
                                <div>
                                    <label for="celular-consulta" class="block text-sm font-medium mb-2 text-pg-tiza font-modern">
                                        <i class="fas fa-mobile-alt mr-2 text-pg-rojo-claro"></i>Celular
                                    </label>
                                    <input 
                                        type="tel" 
                                        id="celular-consulta" 
                                        placeholder="9 1234 5678"
                                        maxlength="12"
                                        class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder:text-pg-tiza/30 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-center text-lg tracking-wider"
                                    >
                                </div>
                                <div>
                                    <label for="nombre-consulta" class="block text-sm font-medium mb-2 text-pg-tiza font-modern">
                                        <i class="fas fa-user mr-2 text-pg-rojo-claro"></i>Primer Nombre
                                    </label>
                                    <input 
                                        type="text" 
                                        id="nombre-consulta" 
                                        placeholder="Tu nombre"
                                        maxlength="50"
                                        class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder:text-pg-tiza/30 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-center"
                                    >
                                    <p class="text-pg-tiza/40 text-xs mt-1 font-modern text-center">Para verificar tu identidad</p>
                                </div>
                            </div>
                            
                            <button 
                                type="button"
                                id="btn-consultar"
                                class="w-full bg-linear-to-r from-pg-rojo to-pg-rojo-oscuro hover:from-pg-rojo-oscuro hover:to-pg-rojo text-white font-bold py-4 rounded-lg transition-all btn-glow flex items-center justify-center font-modern"
                            >
                                <i class="fas fa-search mr-2"></i>
                                Consultar
                            </button>
                        </div>
                        
                        <p class="text-pg-tiza/40 text-xs text-center mt-4 font-modern">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Consulta protegida • Máximo 3 consultas cada 5 min
                        </p>
                    </div>
                </div>
                
                <!-- Resultado de la consulta (oculto inicialmente) -->
                <div id="resultado-consulta" class="hidden mt-8 max-w-2xl mx-auto animate-on-scroll">
                    <div class="bg-pg-negro/50 border border-pg-rojo/30 rounded-2xl p-8">
                        <!-- Header con nombre -->
                        <div class="text-center mb-6 pb-6 border-b border-pg-tiza/10">
                            <div class="w-16 h-16 bg-linear-to-br from-pg-rojo/30 to-pg-rojo-oscuro/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-user text-pg-rojo-claro text-2xl"></i>
                            </div>
                            <h3 id="resultado-nombre" class="font-display text-2xl text-pg-tiza">-</h3>
                        </div>
                        
                        <!-- Estado de membresía -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-pg-carbon/50 rounded-xl p-4">
                                <div class="text-pg-tiza/50 text-sm font-modern mb-1">Membresía</div>
                                <div id="resultado-membresia" class="text-pg-tiza font-semibold">-</div>
                            </div>
                            <div class="bg-pg-carbon/50 rounded-xl p-4">
                                <div class="text-pg-tiza/50 text-sm font-modern mb-1">Estado</div>
                                <div id="resultado-estado" class="font-semibold">-</div>
                            </div>
                        </div>
                        
                        <!-- Fechas y días restantes -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-pg-carbon/50 rounded-xl p-4 text-center">
                                <div class="text-pg-tiza/50 text-xs font-modern mb-1">Inicio</div>
                                <div id="resultado-inicio" class="text-pg-tiza font-modern">-</div>
                            </div>
                            <div class="bg-pg-carbon/50 rounded-xl p-4 text-center">
                                <div class="text-pg-tiza/50 text-xs font-modern mb-1">Vencimiento</div>
                                <div id="resultado-fin" class="text-pg-tiza font-modern">-</div>
                            </div>
                            <div class="bg-linear-to-br from-pg-rojo/20 to-pg-rojo-oscuro/20 border border-pg-rojo/30 rounded-xl p-4 text-center">
                                <div class="text-pg-rojo-claro text-xs font-modern mb-1">Días Restantes</div>
                                <div id="resultado-dias" class="text-3xl font-bold text-pg-rojo-claro">-</div>
                            </div>
                        </div>
                        
                        <!-- Estado de pago: si queda algo por pagar. El historial se ve en el meson. -->
                        <div class="border-t border-pg-tiza/10 pt-6">
                            <h4 class="text-pg-tiza font-semibold mb-2 font-modern">
                                <i class="fas fa-receipt mr-2 text-pg-rojo-claro"></i>Estado de pago
                            </h4>
                            <p id="resultado-saldo" class="font-modern text-sm text-pg-tiza/70">-</p>
                        </div>

                        <!-- Botón cerrar -->
                        <button 
                            type="button"
                            id="btn-cerrar-resultado"
                            class="w-full mt-6 border border-pg-tiza/20 hover:border-pg-rojo text-pg-tiza/60 hover:text-pg-tiza py-3 rounded-lg transition-all font-modern text-sm"
                        >
                            <i class="fas fa-times mr-2"></i>Cerrar consulta
                        </button>
                    </div>
                </div>
                
                <!-- Error message -->
                <div id="error-consulta" class="hidden mt-6 max-w-md mx-auto">
                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
                        <i class="fas fa-exclamation-circle text-red-400 mr-2"></i>
                        <span id="error-mensaje" class="text-red-400 font-modern"></span>
                    </div>
                </div>

                    {{-- Lo que sirve mientras se consulta: cuándo está abierto hoy,
                         por dónde escribir y qué hacer si todavía no es socio. --}}
                    <aside class="animate-on-scroll rounded-2xl border border-pg-tiza/10 bg-pg-negro/50 p-8">
                        <h2 class="font-display text-2xl uppercase text-pg-tiza">¿Dudas con tu membresía?</h2>
                        <p class="mt-3 text-pg-tiza/60 font-modern text-sm leading-relaxed">
                            Si el RUT no aparece o los datos no calzan, escríbenos y lo revisamos en el momento.
                            También puedes pasar por el mesón.
                        </p>

                        @if($horario['configurado'])
                            @php($hoy = collect($horario['dias'])->firstWhere('clave', $horario['hoy']))
                            @if($hoy)
                                <div class="mt-6 flex items-center gap-3 rounded-xl border border-pg-tiza/10 bg-pg-carbon/60 px-4 py-3">
                                    <i class="fas fa-clock text-pg-rojo-claro" aria-hidden="true"></i>
                                    <p class="font-modern text-sm text-pg-tiza/80">
                                        Hoy {{ mb_strtolower($hoy['nombre']) }}:
                                        <span class="text-pg-tiza tabular-nums">{{ $hoy['tramos'] ? implode(' · ', array_map(fn ($t) => $t[0] . ' – ' . $t[1], $hoy['tramos'])) : 'cerrado' }}</span>
                                    </p>
                                </div>
                            @endif
                        @endif

                        <div class="mt-6 flex flex-wrap gap-3">
                            @if($whatsapp)
                                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" data-evento="whatsapp_gimnasio"
                                   class="inline-flex items-center gap-2 px-5 py-3 rounded-lg bg-[#25D366] hover:brightness-110 text-white font-modern text-sm font-semibold transition-all">
                                    <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i> Escríbenos por WhatsApp
                                </a>
                            @endif
                            <a href="{{ route('landing.contacto') }}"
                               class="inline-flex items-center gap-2 px-5 py-3 rounded-lg border border-pg-tiza/20 hover:border-pg-rojo/50 text-pg-tiza font-modern text-sm font-semibold transition-colors">
                                <i class="fas fa-envelope" aria-hidden="true"></i> Contacto
                            </a>
                        </div>

                        <p class="mt-6 border-t border-pg-tiza/10 pt-5 font-modern text-sm text-pg-tiza/60">
                            ¿Todavía no eres socio?
                            <a href="{{ route('landing.planes') }}" class="text-pg-rojo-claro hover:underline">Mira los planes</a>.
                        </p>
                    </aside>
                </div>
            </div>
        </section>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos
    const btnConsultar = document.getElementById('btn-consultar');
    const btnCerrar = document.getElementById('btn-cerrar-resultado');
    const inputRut = document.getElementById('rut-consulta');
    const inputDigitos = document.getElementById('digitos-consulta');
    const inputCelular = document.getElementById('celular-consulta');
    const inputNombre = document.getElementById('nombre-consulta');
    const resultadoDiv = document.getElementById('resultado-consulta');
    const errorDiv = document.getElementById('error-consulta');
    
    // Tabs
    const tabRut = document.getElementById('tab-rut');
    const tabCelular = document.getElementById('tab-celular');
    const formRut = document.getElementById('form-rut');
    const formCelular = document.getElementById('form-celular');
    
    let modoConsulta = 'rut'; // 'rut' o 'celular'
    
    // Cambiar tabs
    tabRut.addEventListener('click', function() {
        modoConsulta = 'rut';
        tabRut.classList.add('bg-pg-rojo', 'text-white');
        tabRut.classList.remove('text-pg-tiza/60');
        tabCelular.classList.remove('bg-pg-rojo', 'text-white');
        tabCelular.classList.add('text-pg-tiza/60');
        formRut.classList.remove('hidden');
        formCelular.classList.add('hidden');
        ocultarError();
    });
    
    tabCelular.addEventListener('click', function() {
        modoConsulta = 'celular';
        tabCelular.classList.add('bg-pg-rojo', 'text-white');
        tabCelular.classList.remove('text-pg-tiza/60');
        tabRut.classList.remove('bg-pg-rojo', 'text-white');
        tabRut.classList.add('text-pg-tiza/60');
        formCelular.classList.remove('hidden');
        formRut.classList.add('hidden');
        ocultarError();
    });
    
    // Formatear RUT mientras escribe
    inputRut.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9kK]/g, '');
        if (value.length > 1) {
            let body = value.slice(0, -1);
            let dv = value.slice(-1).toUpperCase();
            body = body.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = body + '-' + dv;
        }
    });
    
    // Formatear celular mientras escribe
    inputCelular.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.length > 9) value = value.slice(0, 9);
        if (value.length > 1) {
            e.target.value = value.slice(0,1) + ' ' + value.slice(1,5) + ' ' + value.slice(5);
        } else {
            e.target.value = value;
        }
    });
    
    // Consultar al hacer click
    btnConsultar.addEventListener('click', consultarMembresia);
    
    // Consultar con Enter
    inputRut.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') consultarMembresia();
    });
    inputCelular.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') consultarMembresia();
    });
    inputNombre.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') consultarMembresia();
    });
    
    // Los 4 digitos: solo numeros, y con Enter se consulta igual que en el RUT.
    inputDigitos.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 4);
    });
    inputDigitos.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') consultarMembresia();
    });

    // Cerrar resultado
    btnCerrar.addEventListener('click', function() {
        resultadoDiv.classList.add('hidden');
        inputRut.value = '';
        inputCelular.value = '';
        inputNombre.value = '';
        inputDigitos.value = '';
        if (modoConsulta === 'rut') {
            inputRut.focus();
        } else {
            inputCelular.focus();
        }
    });
    
    async function consultarMembresia() {
        let payload = {};
        
        if (modoConsulta === 'rut') {
            const rut = inputRut.value.trim();
            if (!rut || rut.length < 7) {
                mostrarError('Ingresa un RUT válido');
                return;
            }
            const digitos = inputDigitos.value.trim();
            if (!/^[0-9]{4}$/.test(digitos)) {
                mostrarError('Ingresa los últimos 4 dígitos de tu celular');
                return;
            }
            payload = { tipo: 'rut', rut: rut, digitos: digitos };
        } else {
            const celular = inputCelular.value.replace(/\s/g, '').trim();
            const nombre = inputNombre.value.trim();
            
            if (!celular || celular.length < 8) {
                mostrarError('Ingresa un celular válido');
                return;
            }
            if (!nombre || nombre.length < 2) {
                mostrarError('Ingresa tu nombre para verificar');
                return;
            }
            payload = { tipo: 'celular', celular: celular, nombre: nombre };
        }
        
        // Mostrar loading
        btnConsultar.disabled = true;
        btnConsultar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Consultando...';
        ocultarError();
        resultadoDiv.classList.add('hidden');
        
        try {
            // Incluir honeypot en la consulta
            const honeypot = document.getElementById('consulta-website')?.value || '';
            payload.website = honeypot;
            
            const response = await fetch('{{ route("landing.consultar-membresia") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            
            if (data.success) {
                mostrarResultado(data.data);
            } else {
                // Mostrar mensaje especial si está bloqueado
                if (data.blocked) {
                    mostrarError(data.message, true);
                } else {
                    mostrarError(data.message || 'No se encontró tu membresía');
                }
            }
        } catch (error) {
            mostrarError('Error de conexión. Intenta nuevamente.');
            console.error('Error:', error);
        } finally {
            btnConsultar.disabled = false;
            btnConsultar.innerHTML = '<i class="fas fa-search mr-2"></i>Consultar';
        }
    }
    
    function mostrarResultado(data) {
        document.getElementById('resultado-nombre').textContent = data.nombre || 'Cliente';
        document.getElementById('resultado-membresia').textContent = data.membresia || 'Sin membresía';
        
        // Estado con color
        const estadoEl = document.getElementById('resultado-estado');
        estadoEl.textContent = data.estado || 'Sin estado';
        estadoEl.className = 'font-semibold ' + getColorEstado(data.estado);
        
        document.getElementById('resultado-inicio').textContent = data.fecha_inicio || '-';
        document.getElementById('resultado-fin').textContent = data.fecha_fin || '-';
        document.getElementById('resultado-dias').textContent = data.dias_restantes !== null ? data.dias_restantes : '-';
        
        // Estado de pago: si queda algo por pagar, cuanto. Nada de historial.
        const saldoEl = document.getElementById('resultado-saldo');
        if (data.saldo && data.saldo > 0) {
            saldoEl.textContent = 'Tienes un saldo pendiente de $' + Number(data.saldo).toLocaleString('es-CL') + '. Puedes pagarlo en el mesón.';
            saldoEl.className = 'font-modern text-sm text-yellow-400';
        } else if (data.membresia) {
            saldoEl.textContent = 'Estás al día.';
            saldoEl.className = 'font-modern text-sm text-green-400';
        } else {
            saldoEl.textContent = 'No tienes una membresía activa en este momento.';
            saldoEl.className = 'font-modern text-sm text-pg-tiza/60';
        }

        if (window.pgEvento) window.pgEvento('consulta_membresia');

        resultadoDiv.classList.remove('hidden');
        resultadoDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    function getColorEstado(estado) {
        if (!estado) return 'text-pg-tiza';
        const lower = estado.toLowerCase();
        if (lower.includes('activa')) return 'text-green-400';
        if (lower.includes('vence hoy')) return 'text-yellow-400';
        if (lower.includes('vencida')) return 'text-red-400';
        return 'text-pg-tiza';
    }
    
    function mostrarError(mensaje, bloqueado = false) {
        const errorMensaje = document.getElementById('error-mensaje');
        const errorContainer = errorDiv.querySelector('div');
        
        errorMensaje.textContent = mensaje;
        
        if (bloqueado) {
            errorContainer.className = 'bg-orange-500/10 border border-orange-500/30 rounded-lg p-4 text-center';
            errorMensaje.className = 'text-orange-400 font-modern';
            // Deshabilitar botón temporalmente
            btnConsultar.disabled = true;
            btnConsultar.innerHTML = '<i class="fas fa-ban mr-2"></i>Bloqueado temporalmente';
            btnConsultar.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            errorContainer.className = 'bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center';
            errorMensaje.className = 'text-red-400 font-modern';
        }
        
        errorDiv.classList.remove('hidden');
    }
    
    function ocultarError() {
        errorDiv.classList.add('hidden');
        // Restaurar botón
        btnConsultar.classList.remove('opacity-50', 'cursor-not-allowed');
    }
});
</script>
@endsection
