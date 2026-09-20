@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
        <!-- ===== CONTACTO SECTION ===== -->
        <section id="contacto" class="pt-24 lg:pt-40 pb-10 lg:pb-20 bg-pg-negro relative">
            {{-- Mas angosto que el resto de la web: son dos columnas cortas, y a todo
                 el ancho de un monitor quedaban pegadas a los bordes y con un vacio
                 enorme en medio. --}}
            <div class="max-w-6xl mx-auto px-5 sm:px-8 lg:px-12">
                {{-- El formulario se lleva más ancho que los datos: al medio y medio,
                     la columna de la izquierda quedaba con un vacío enorme debajo. --}}
                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] gap-8 lg:gap-16">
                    <!-- Contact Info -->
                    <div class="animate-on-scroll">
                        <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Contáctanos</span>
                        <h1 class="font-display text-3xl md:text-4xl mt-2 mb-3 lg:mt-4 lg:mb-6 text-pg-tiza">HABLEMOS</h1>
                        <p class="text-pg-tiza/60 font-modern mb-5 lg:mb-7">
                            ¿Tienes dudas? ¿Quieres conocer nuestras instalaciones? 
                            Contáctanos y te ayudaremos a dar el primer paso.
                        </p>
                        
                        {{-- Salen de Configuración → El gimnasio. Lo vacío no se enseña: mejor nada que un teléfono de ejemplo. --}}
                        <div class="space-y-4">
                            @if($gimnasio['direccion'])
                                <div class="flex items-start">
                                    {{-- El icono suelto, como en el resto de la web: el
                                         cuadradito de color es adorno de plantilla. --}}
                                    <i class="fas fa-map-marker-alt text-pg-rojo-claro text-base w-5 mr-4 mt-1 shrink-0 text-center" aria-hidden="true"></i>
                                    <div>
                                        <h4 class="font-semibold mb-1 text-pg-tiza">Dirección</h4>
                                        <p class="text-pg-tiza/60 font-modern text-sm">{{ $gimnasio['direccion'] }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($gimnasio['telefono'])
                                <div class="flex items-start">
                                    <i class="fas fa-phone-alt text-pg-rojo-claro text-base w-5 mr-4 mt-1 shrink-0 text-center" aria-hidden="true"></i>
                                    <div>
                                        <h4 class="font-semibold mb-1 text-pg-tiza">Teléfono</h4>
                                        <p class="text-pg-tiza/60 font-modern text-sm"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $gimnasio['telefono']) }}" class="hover:text-pg-tiza transition-colors" data-evento="contacto_directo">{{ $gimnasio['telefono'] }}</a></p>
                                    </div>
                                </div>
                            @endif
                            @if($gimnasio['email'])
                                <div class="flex items-start">
                                    <i class="fas fa-envelope text-pg-rojo-claro text-base w-5 mr-4 mt-1 shrink-0 text-center" aria-hidden="true"></i>
                                    <div>
                                        <h4 class="font-semibold mb-1 text-pg-tiza">Correo</h4>
                                        <p class="text-pg-tiza/60 font-modern text-sm"><a href="mailto:{{ $gimnasio['email'] }}" class="hover:text-pg-tiza transition-colors" data-evento="contacto_directo">{{ $gimnasio['email'] }}</a></p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($whatsapp || $web['google_maps'] || $web['resenas'] || $redes)
                            <div class="mt-6 lg:mt-8 flex flex-wrap items-center gap-3">
                                {{-- El WhatsApp va primero: es por donde escribe casi todo el mundo. --}}
                                @if($whatsapp)
                                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" data-evento="whatsapp_gimnasio"
                                       class="inline-flex items-center gap-2 px-5 py-3 rounded-lg bg-[#25D366] hover:brightness-110 text-white font-modern text-sm font-semibold transition-all">
                                        <i class="fab fa-whatsapp text-base" aria-hidden="true"></i> Escríbenos por WhatsApp
                                    </a>
                                @endif
                                @if($web['google_maps'])
                                    <a href="{{ $web['google_maps'] }}" target="_blank" rel="noopener" data-evento="como_llegar"
                                       class="inline-flex items-center gap-2 px-5 py-3 rounded-lg bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-modern text-sm font-semibold transition-colors">
                                        <i class="fas fa-route" aria-hidden="true"></i> Cómo llegar
                                    </a>
                                @endif
                                @if($web['resenas'])
                                    {{-- Se pide la reseña donde ya hay confianza: es lo que más ayuda a salir primero en el mapa. --}}
                                    <a href="{{ $web['resenas'] }}" target="_blank" rel="noopener" data-evento="resena_google"
                                       class="inline-flex items-center gap-2 px-5 py-3 rounded-lg border border-pg-tiza/20 hover:border-yellow-400/60 text-pg-tiza font-modern text-sm font-semibold transition-colors">
                                        <i class="fas fa-star text-yellow-400" aria-hidden="true"></i> Déjanos tu reseña
                                    </a>
                                @endif
                                @foreach($redes as $red)
                                    <a href="{{ $red['url'] }}" target="_blank" rel="noopener" aria-label="{{ $red['nombre'] }}"
                                       class="w-12 h-12 bg-pg-carbon border border-pg-tiza/10 hover:border-pg-rojo/40 rounded-xl flex items-center justify-center transition-all text-pg-tiza hover:text-pg-rojo-claro">
                                        <i class="{{ $red['icono'] }} text-xl" aria-hidden="true"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Contact Form -->
                    {{-- El formulario ya no va dentro de un panel con borde: lo
                         separa de los datos de contacto una línea, como en el resto
                         de la web. Los campos sí llevan borde, que eso no es adorno
                         sino la señal de dónde se escribe. --}}
                    <div class="animate-on-scroll lg:border-l lg:border-pg-tiza/10">
                        <div class="lg:pl-16">
                            <h3 class="font-display text-xl mb-4 lg:mb-6 text-pg-tiza">ENVÍANOS UN MENSAJE</h3>
                            
                            <!-- Alerts -->
                            @if(session('success'))
                                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    {{ session('success') }}
                                </div>
                            @endif
                            
                            @if(session('error'))
                                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    {{ session('error') }}
                                </div>
                            @endif
                            
                            @if($errors->any())
                                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                                    <ul class="text-red-400 text-sm space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li><i class="fas fa-times mr-2"></i>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <form action="{{ route('landing.contacto.enviar') }}" method="POST" class="space-y-4">
                                @csrf
                                
                                <!-- Honeypot (anti-spam) -->
                                <div class="hp-field" aria-hidden="true">
                                    <label for="website">Website</label>
                                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3 md:gap-4">
                                    <div>
                                        <label for="nombre" class="block text-sm font-medium mb-1.5 text-pg-tiza font-modern">Nombre *</label>
                                        <input 
                                            type="text" 
                                            name="nombre" 
                                            id="nombre" 
                                            value="{{ old('nombre') }}"
                                            required
                                            minlength="2"
                                            maxlength="100"
                                            class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-2.5 text-sm text-pg-tiza placeholder:text-pg-tiza/30 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern"
                                            placeholder="Tu nombre"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label for="email" class="block text-sm font-medium mb-1.5 text-pg-tiza font-modern">Email *</label>
                                        <input 
                                            type="email" 
                                            name="email" 
                                            id="email" 
                                            value="{{ old('email') }}"
                                            required
                                            maxlength="255"
                                            class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-2.5 text-sm text-pg-tiza placeholder:text-pg-tiza/30 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern"
                                            placeholder="tu@email.com"
                                        >
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3 md:gap-4">
                                    <div>
                                        <label for="telefono" class="block text-sm font-medium mb-1.5 text-pg-tiza font-modern">Teléfono</label>
                                        <input 
                                            type="tel" 
                                            name="telefono" 
                                            id="telefono" 
                                            value="{{ old('telefono') }}"
                                            maxlength="20"
                                            class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-2.5 text-sm text-pg-tiza placeholder:text-pg-tiza/30 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern"
                                            placeholder="+56 9 1234 5678"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label for="servicio" class="block text-sm font-medium mb-1.5 text-pg-tiza font-modern">Interés</label>
                                        <select 
                                            name="servicio" 
                                            id="servicio"
                                            class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-2.5 text-sm text-pg-tiza transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 font-modern"
                                        >
                                            <option value="informacion">Información general</option>
                                            <option value="inscripcion">Quiero inscribirme</option>
                                            <option value="convenio">Convenio de empresa</option>
                                            {{-- Quien llega desde «Arriendo para instituciones» ya lo trae elegido. --}}
                                            <option value="arriendo" @selected(old('servicio', request('interes')) === 'arriendo')>Arriendo para instituciones</option>
                                            <option value="otro">Otro</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="mensaje" class="block text-sm font-medium mb-1.5 text-pg-tiza font-modern">Mensaje *</label>
                                    <textarea 
                                        name="mensaje" 
                                        id="mensaje" 
                                        rows="3"
                                        required
                                        minlength="10"
                                        maxlength="1000"
                                        class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-2.5 text-sm text-pg-tiza placeholder:text-pg-tiza/30 transition-colors focus:outline-hidden focus:ring-2 focus:ring-pg-rojo/20 resize-none font-modern"
                                        placeholder="¿En qué podemos ayudarte?"
                                    >{{ old('mensaje') }}</textarea>
                                </div>
                                
                                <button 
                                    type="submit" 
                                    class="w-full bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-bold py-3 rounded-lg transition-colors flex items-center justify-center font-modern"
                                >
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Enviar Mensaje
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    @include('landing.partes.horario')
@endsection

@section('scripts')
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.pgEvento) window.pgEvento('generate_lead');
    });
</script>
@endif
@endsection
