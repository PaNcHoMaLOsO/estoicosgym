@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
        <!-- ===== CONSULTA MEMBRESÍA SECTION ===== -->
        <section id="consulta" class="pt-20 lg:pt-28 pb-9 lg:pb-16 bg-pg-carbon relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-px bg-linear-to-r from-transparent via-pg-rojo/30 to-transparent"></div>
            
            <!-- Glow decorativo -->
            
            <div class="max-w-[1100px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20 relative z-10">
                <div class="text-center mb-5 lg:mb-8 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">¿Ya eres miembro?</span>
                    <h1 class="font-display text-3xl md:text-4xl mt-4 text-pg-tiza">CONSULTA TU MEMBRESÍA</h1>
                    <p class="text-pg-tiza/60 mt-4 max-w-2xl mx-auto font-modern">
                        Ingresa tu RUT para ver cómo está tu membresía.
                    </p>
                </div>
                
                {{-- El formulario con un acompañante al lado: solo, en una pantalla
                     ancha quedaba un recuadro angosto en medio de todo negro. --}}
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:items-start">
                <div class="max-w-md w-full mx-auto animate-on-scroll lg:order-1">
                    <div class="bg-pg-negro/50 border border-pg-tiza/10 rounded-2xl p-6">
                        <div class="space-y-4">
                            <!-- Honeypot anti-bot (oculto) -->
                            <div class="hp-field" aria-hidden="true">
                                <input type="text" name="website" id="consulta-website" tabindex="-1" autocomplete="off">
                            </div>
                            
                            <!-- Formulario RUT -->
                            <div id="form-rut" class="space-y-4">
                                <div>
                                    <label for="rut-consulta" class="block text-sm font-medium mb-2 text-pg-tiza font-modern">
                                        <x-icono nombre="id-card" class="mr-2 text-pg-rojo-claro" />RUT
                                    </label>
                                    <input 
                                        type="text" 
                                        id="rut-consulta" 
                                        placeholder="12.345.678-9"
                                        maxlength="12"
                                        class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder:text-pg-tiza/30 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern text-center text-base tracking-wider"
                                    >
                                </div>
                            </div>

                            <button 
                                type="button"
                                id="btn-consultar"
                                class="w-full bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-bold py-3 rounded-lg transition-colors flex items-center justify-center font-modern"
                            >
                                <x-icono nombre="search" class="mr-2" />
                                Consultar
                            </button>
                        </div>
                    </div>
                </div>
                
                {{-- EL RESULTADO. En el celular, justo debajo del formulario; en
                     pantalla ancha, debajo de las dos columnas y a lo ancho
                     (`lg:order-3 lg:col-span-2`). Antes, al aparecer, se metía
                     en el lugar del recuadro de dudas y lo empujaba abajo. Compacto: el
                     estado, cuántos días le quedan con una barra, si debe algo y,
                     si se le acaba o ya no tiene plan, renovar por WhatsApp. --}}
                <div id="resultado-consulta" class="hidden w-full mx-auto max-w-2xl lg:order-3 lg:col-span-2" aria-live="polite">
                    <div class="rounded-2xl border border-pg-tiza/10 bg-pg-negro/50 p-5 sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
                            <h2 class="font-display text-2xl uppercase text-pg-tiza">Hola, <span id="resultado-nombre">-</span></h2>
                            <span id="resultado-estado" class="rounded-full px-3 py-1 font-modern text-xs font-semibold">-</span>
                        </div>
                        <p id="resultado-membresia" class="mt-1 font-modern text-sm text-pg-tiza/60">-</p>

                        <div id="resultado-plazo" class="mt-5">
                            <div class="flex flex-wrap items-end justify-between gap-x-4 gap-y-1 font-modern">
                                <p class="text-pg-tiza/60 text-sm">
                                    <span id="resultado-dias" class="font-display text-4xl text-pg-tiza tabular-nums">-</span>
                                    <span id="resultado-dias-texto">días</span>
                                </p>
                                <p class="text-right text-sm text-pg-tiza/60">Vence el <span id="resultado-fin" class="text-pg-tiza tabular-nums">-</span></p>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-pg-tiza/10">
                                <div id="resultado-barra" class="h-full w-0 rounded-full bg-pg-rojo transition-[width] duration-700"></div>
                            </div>
                            <p class="mt-2 font-modern text-xs text-pg-tiza/40">Desde el <span id="resultado-inicio" class="tabular-nums">-</span></p>
                        </div>

                        <p id="resultado-saldo" class="mt-5 border-t border-pg-tiza/10 pt-4 font-modern text-sm">-</p>

                        <div class="mt-5 flex flex-wrap items-center gap-3">
                            @if($whatsapp)
                                <a id="resultado-renovar" hidden href="{{ $whatsapp }}" target="_blank" rel="noopener" data-evento="whatsapp_gimnasio"
                                   class="inline-flex items-center gap-2 rounded-lg bg-[#25D366] px-5 py-3 font-modern text-sm font-semibold text-white transition-all hover:brightness-110">
                                    <x-icono nombre="whatsapp" class="text-base" /> Renovar por WhatsApp
                                </a>
                            @endif
                            <button type="button" id="btn-cerrar-resultado"
                                    class="inline-flex items-center gap-2 rounded-lg border border-pg-tiza/20 px-5 py-3 font-modern text-sm text-pg-tiza/70 transition-colors hover:border-pg-rojo/50 hover:text-pg-tiza">
                                <x-icono nombre="search" /> Consultar otra
                            </button>
                        </div>

                        {{-- Quien acaba de ver que su plan está al día es el mejor
                             momento para pedirle la reseña: solo sale con la consulta. --}}
                        @if($web['resenas'])
                            <p class="mt-5 font-modern text-sm text-pg-tiza/70">
                                ¿Te gusta entrenar aquí?
                                <a href="{{ $web['resenas'] }}" target="_blank" rel="noopener" data-evento="resena_google" class="inline-flex items-center gap-1.5 font-semibold text-pg-tiza underline decoration-yellow-400/60 underline-offset-4 hover:decoration-yellow-400">
                                    <x-icono nombre="star" class="text-yellow-400" /> Déjanos tu reseña en Google
                                </a>
                            </p>
                        @endif
                    </div>
                </div>

                <div id="error-consulta" class="hidden w-full mx-auto max-w-md lg:order-3 lg:col-span-2" aria-live="polite">
                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
                        <x-icono nombre="exclamation-circle" class="text-red-400 mr-2" />
                        <span id="error-mensaje" class="text-red-400 font-modern"></span>
                    </div>
                </div>

                    {{-- Lo que sirve mientras se consulta: cuándo está abierto hoy,
                         por dónde escribir y qué hacer si todavía no es socio. --}}
                    <aside class="animate-on-scroll lg:order-2 rounded-2xl border border-pg-tiza/10 bg-pg-negro/50 p-6">
                        <h2 class="font-display text-xl uppercase text-pg-tiza">¿Dudas con tu membresía?</h2>
                        <p class="mt-3 text-pg-tiza/60 font-modern text-sm leading-relaxed">
                            Escríbenos o pasa por el mesón.
                        </p>

                        @if($horario['configurado'])
                            @php($hoy = collect($horario['dias'])->firstWhere('clave', $horario['hoy']))
                            @if($hoy)
                                <div class="mt-6 flex items-center gap-3 rounded-xl border border-pg-tiza/10 bg-pg-carbon/60 px-4 py-3">
                                    <x-icono nombre="clock" class="text-pg-rojo-claro" />
                                    <p class="font-modern text-sm text-pg-tiza/80">
                                        Hoy {{ mb_strtolower($hoy['nombre']) }}:
                                        <span class="text-pg-tiza tabular-nums">{{ $hoy['tramos'] ? implode(' · ', array_map(fn ($t) => $t[0] . ' a ' . $t[1], $hoy['tramos'])) : 'cerrado' }}</span>
                                    </p>
                                </div>
                            @endif
                        @endif

                        <div class="mt-6 flex flex-wrap gap-3">
                            @if($whatsapp)
                                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" data-evento="whatsapp_gimnasio"
                                   class="inline-flex items-center gap-2 px-5 py-3 rounded-lg bg-[#25D366] hover:brightness-110 text-white font-modern text-sm font-semibold transition-all">
                                    <x-icono nombre="whatsapp" class="text-base" /> Escríbenos por WhatsApp
                                </a>
                            @endif
                            <a href="{{ route('landing.contacto') }}"
                               class="inline-flex items-center gap-2 px-5 py-3 rounded-lg border border-pg-tiza/20 hover:border-pg-rojo/50 text-pg-tiza font-modern text-sm font-semibold transition-colors">
                                <x-icono nombre="envelope" /> Contacto
                            </a>
                            {{-- Las redes, igual que en Contacto: salen de Configuración. --}}
                            @foreach($redes ?? [] as $red)
                                <a href="{{ $red['url'] }}" target="_blank" rel="noopener" aria-label="{{ $red['nombre'] }}"
                                   class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-pg-tiza/20 hover:border-pg-rojo/50 text-pg-tiza hover:text-pg-rojo-claro transition-colors">
                                    <x-icono :nombre="$red['icono']" class="text-lg" />
                                </a>
                            @endforeach
                        </div>

                        @if($navegacion['planes'])
                            <p class="mt-6 border-t border-pg-tiza/10 pt-5 font-modern text-sm text-pg-tiza/60">
                                ¿Todavía no eres socio?
                                <a href="{{ route('landing.planes') }}" class="text-pg-rojo-claro hover:underline">Mira los planes</a>.
                            </p>
                        @endif
                    </aside>
                </div>

                {{-- Los iconos del botón mientras trabaja. Escritos dentro de una
                     cadena del script, el icono traía un salto de línea al final y
                     rompía TODO el script: «Consultar» no hacía nada. --}}
                <template id="icono-buscando"><x-icono nombre="spinner" class="animate-spin mr-2" /></template>
                <template id="icono-buscar"><x-icono nombre="search" class="mr-2" /></template>
                <template id="icono-bloqueado"><x-icono nombre="ban" class="mr-2" /></template>
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
    const resultadoDiv = document.getElementById('resultado-consulta');
    const errorDiv = document.getElementById('error-consulta');
    const icono = (id) => document.getElementById(id)?.innerHTML.trim() ?? '';

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

    btnConsultar.addEventListener('click', consultarMembresia);
    inputRut.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') consultarMembresia();
    });

    // Consultar otra
    btnCerrar.addEventListener('click', function() {
        resultadoDiv.classList.add('hidden');
        inputRut.value = '';
        inputRut.focus();
    });

    async function consultarMembresia() {
        const rut = inputRut.value.trim();
        if (!rut || rut.length < 7) {
            mostrarError('Ingresa un RUT válido');
            return;
        }
        const payload = { rut: rut };

        // Mostrar loading
        btnConsultar.disabled = true;
        btnConsultar.innerHTML = icono('icono-buscando') + 'Consultando…';
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
            btnConsultar.innerHTML = icono('icono-buscar') + 'Consultar';
        }
    }
    
    function mostrarResultado(data) {
        document.getElementById('resultado-nombre').textContent = data.nombre || 'Socio';
        document.getElementById('resultado-membresia').textContent = data.membresia ? 'Plan ' + data.membresia : 'Sin plan vigente';

        // El estado como etiqueta de color.
        const estadoEl = document.getElementById('resultado-estado');
        estadoEl.textContent = data.estado || 'Sin estado';
        estadoEl.className = 'rounded-full px-3 py-1 font-modern text-xs font-semibold ' + getColorEstado(data.estado);

        // Los días, con una barra de lo que le queda del plan.
        const plazo = document.getElementById('resultado-plazo');
        const dias = data.dias_restantes;
        plazo.hidden = dias === null || dias === undefined;
        if (!plazo.hidden) {
            document.getElementById('resultado-dias').textContent = dias;
            document.getElementById('resultado-dias-texto').textContent = dias === 0 ? 'días: hoy es el último' : (dias === 1 ? 'día le queda a tu plan' : 'días le quedan a tu plan');
            document.getElementById('resultado-inicio').textContent = data.fecha_inicio || '-';
            document.getElementById('resultado-fin').textContent = data.fecha_fin || '-';

            const fecha = (t) => { const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(t || ''); return m ? new Date(+m[3], +m[2] - 1, +m[1]) : null; };
            const desde = fecha(data.fecha_inicio), hasta = fecha(data.fecha_fin);
            const total = desde && hasta ? Math.max(1, Math.round((hasta - desde) / 86400000)) : null;
            const barra = document.getElementById('resultado-barra');
            barra.style.width = '0';
            barra.className = 'h-full rounded-full transition-[width] duration-700 ' + (dias <= 7 ? 'bg-yellow-400' : 'bg-pg-rojo');
            requestAnimationFrame(() => { barra.style.width = total ? Math.min(100, Math.round(dias / total * 100)) + '%' : '0'; });
        }

        // Si debe algo, sin el monto: eso se dice en el mesón.
        const saldoEl = document.getElementById('resultado-saldo');
        const linea = 'mt-5 border-t border-pg-tiza/10 pt-4 font-modern text-sm ';
        if (data.debe) {
            saldoEl.textContent = 'Tienes un pago pendiente. Te decimos cuánto en el mesón o por WhatsApp.';
            saldoEl.className = linea + 'text-yellow-400';
        } else if (data.membresia) {
            saldoEl.textContent = 'Estás al día con los pagos.';
            saldoEl.className = linea + 'text-green-400';
        } else {
            saldoEl.textContent = 'No tienes un plan vigente en este momento.';
            saldoEl.className = linea + 'text-pg-tiza/60';
        }

        // Renovar por WhatsApp: cuando le queda una semana o menos, o ya no
        // tiene plan. El mensaje va escrito, con su nombre y su plan.
        const renovar = document.getElementById('resultado-renovar');
        if (renovar) {
            const toca = !data.membresia || (dias !== null && dias !== undefined && dias <= 7);
            renovar.hidden = !toca;
            if (toca) {
                try {
                    const url = new URL(renovar.getAttribute('href'));
                    url.searchParams.set('text', data.membresia
                        ? 'Hola, soy ' + (data.nombre || '') + '. Quiero renovar mi plan ' + data.membresia + '.'
                        : 'Hola, soy ' + (data.nombre || '') + '. Quiero volver a entrenar.');
                    renovar.href = url.toString();
                } catch (e) { /* se queda el enlace tal cual */ }
            }
        }

        if (window.pgEvento) window.pgEvento('consulta_membresia');

        resultadoDiv.classList.remove('hidden');
        resultadoDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function getColorEstado(estado) {
        const lower = (estado || '').toLowerCase();
        if (lower.includes('pausada') || lower.includes('vence hoy')) return 'bg-yellow-400/15 text-yellow-400';
        if (lower.includes('activa') && !lower.includes('sin')) return 'bg-green-500/15 text-green-400';
        if (lower.includes('vencida')) return 'bg-red-500/15 text-red-400';
        return 'bg-pg-tiza/10 text-pg-tiza/70';
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
            btnConsultar.innerHTML = icono('icono-bloqueado') + 'Bloqueado temporalmente';
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
