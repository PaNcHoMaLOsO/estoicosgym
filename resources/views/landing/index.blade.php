@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    <!-- ===== NAVBAR ===== -->
    <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="#inicio" class="flex items-center" aria-label="{{ $gimnasio['nombre'] }}, ir al inicio">
                    <picture>
                        <source srcset="{{ asset('images/progym-logo.webp') }}" type="image/webp">
                        <img src="{{ asset('images/progym-logo.png') }}" alt="{{ $gimnasio['nombre'] }}" width="1096" height="495" class="h-11 w-auto">
                    </picture>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#inicio" class="text-pg-tiza/80 hover:text-pg-rojo-claro transition-colors font-modern text-sm">Inicio</a>
                    <a href="#servicios" class="text-pg-tiza/80 hover:text-pg-rojo-claro transition-colors font-modern text-sm">Servicios</a>
                    <a href="#planes" class="text-pg-tiza/80 hover:text-pg-rojo-claro transition-colors font-modern text-sm">Planes</a>
                    <a href="#consulta" class="text-pg-tiza/80 hover:text-pg-rojo-claro transition-colors font-modern text-sm">Mi Membresía</a>
                    <a href="#contacto" class="text-pg-tiza/80 hover:text-pg-rojo-claro transition-colors font-modern text-sm">Contacto</a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-pg-tiza p-2">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4 bg-pg-negro/95 backdrop-blur-md rounded-b-xl">
                <div class="flex flex-col space-y-4 px-4">
                    <a href="#inicio" class="text-pg-tiza/80 hover:text-pg-rojo-claro transition-colors py-2">Inicio</a>
                    <a href="#servicios" class="text-pg-tiza/80 hover:text-pg-rojo-claro transition-colors py-2">Servicios</a>
                    <a href="#planes" class="text-pg-tiza/80 hover:text-pg-rojo-claro transition-colors py-2">Planes</a>
                    <a href="#consulta" class="text-pg-tiza/80 hover:text-pg-rojo-claro transition-colors py-2">Mi Membresía</a>
                    <a href="#contacto" class="text-pg-tiza/80 hover:text-pg-rojo-claro transition-colors py-2">Contacto</a>
                </div>
            </div>
        </div>
    </nav>

    <main id="main-content">
        <!-- ===== HERO SECTION ===== -->
        <section id="inicio" class="relative min-h-screen flex items-center justify-center overflow-hidden">
            <!-- Background con gradiente navy -->
            <div class="absolute inset-0 bg-gradient-to-br from-pg-negro via-pg-carbon to-pg-grafito">
                <!-- Pattern decorativo -->
                <div class="absolute inset-0 opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M54.627 0l.83.828-1.415 1.415L51.8 0h2.827zM5.373 0l-.83.828L5.96 2.243 8.2 0H5.374zM48.97 0l3.657 3.657-1.414 1.414L46.143 0h2.828zM11.03 0L7.372 3.657 8.787 5.07 13.857 0H11.03zm32.284 0L49.8 6.485 48.384 7.9l-7.9-7.9h2.83zM16.686 0L10.2 6.485 11.616 7.9l7.9-7.9h-2.83zM22.344 0L13.858 8.485 15.272 9.9l7.9-7.9h-.828zm5.656 0L19.515 8.485 20.929 9.9 28.828 2l-.828-.828zM33.656 0L25.172 8.485 26.586 9.9l8.485-8.485L34.243 0h-.587zM39.314 0L30.828 8.485 32.243 9.9l8.485-8.485-.828-.829h-.586zm5.657 0l-8.485 8.485 1.414 1.414 8.485-8.485-.828-.829h-.586z\" fill=\"%23dd2a32\" fill-opacity=\"0.4\" fill-rule=\"evenodd\"/%3E%3C/svg%3E');"></div>
            </div>
            
            <!-- Decorative glows -->
            <div class="absolute top-1/4 left-10 w-72 h-72 bg-pg-rojo/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 right-10 w-96 h-96 bg-pg-plata/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-pg-rojo/5 rounded-full blur-3xl"></div>
            
            <!-- Content -->
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-20">
                <div class="fade-in">
                    <span class="inline-block px-4 py-2 bg-pg-rojo/20 border border-pg-rojo/40 rounded-full text-pg-rojo-claro text-sm font-modern mb-6">
                        <i class="fas fa-map-marker-alt mr-2" aria-hidden="true"></i>
                        {{ $web['ciudad'] ? 'Gimnasio en ' . $web['ciudad'] : 'Profesionales del deporte' }}
                    </span>
                    
                    <h1 class="font-display text-5xl sm:text-6xl md:text-7xl lg:text-8xl tracking-wider mb-6">
                        <span class="block text-pg-tiza">TRANSFORMA</span>
                        <span class="gradient-text">TU CUERPO</span>
                    </h1>
                    
                    <p class="text-lg sm:text-xl text-pg-tiza/70 max-w-3xl mx-auto mb-10 font-modern">
                        Musculación, cardio y un equipo que te orienta desde el primer día. Elige tu plan y empieza hoy.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="#planes" class="w-full sm:w-auto bg-gradient-to-r from-pg-rojo to-pg-rojo-oscuro hover:from-pg-rojo-oscuro hover:to-pg-rojo text-white font-bold px-10 py-4 rounded-lg text-lg transition-all btn-glow font-modern">
                            <i class="fas fa-rocket mr-2"></i>
                            Comenzar Ahora
                        </a>
                        <a href="#servicios" class="w-full sm:w-auto border-2 border-pg-tiza/30 hover:border-pg-rojo text-pg-tiza hover:text-pg-rojo-claro px-10 py-4 rounded-lg text-lg transition-all font-modern">
                            <i class="fas fa-play-circle mr-2"></i>
                            Conocer Más
                        </a>
                    </div>
                </div>
                
            </div>

            <!-- Scroll indicator -->
            <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
                <a href="#servicios" class="text-pg-rojo/50 hover:text-pg-rojo-claro transition-colors">
                    <i class="fas fa-chevron-down text-2xl"></i>
                </a>
            </div>
        </section>

        <!-- ===== SERVICIOS SECTION ===== -->
        <section id="servicios" class="py-24 bg-pg-carbon relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-pg-rojo/30 to-transparent"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-16 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Lo que ofrecemos</span>
                    <h2 class="font-display text-4xl md:text-5xl mt-4 text-pg-tiza">NUESTROS SERVICIOS</h2>
                    <p class="text-pg-tiza/60 mt-4 max-w-2xl mx-auto font-modern">
                        {{ $gimnasio['nombre'] }} es un gimnasio{{ $web['ciudad'] ? ' en ' . $web['ciudad'] : '' }}{{ $web['region'] ? ', región del ' . $web['region'] : '' }}. Esto es lo que encuentras al entrar.
                    </p>
                </div>
                
                <!-- Services Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($servicios as $index => $servicio)
                        <div class="animate-on-scroll card-hover bg-pg-negro/50 border border-pg-tiza/10 rounded-2xl p-8 hover:border-pg-rojo/30" style="animation-delay: {{ $index * 0.1 }}s">
                            <div class="w-16 h-16 bg-gradient-to-br from-pg-rojo/20 to-pg-rojo-oscuro/20 rounded-xl flex items-center justify-center mb-6">
                                <i class="fas fa-{{ $servicio['icono'] }} text-pg-rojo-claro text-2xl"></i>
                            </div>
                            <h3 class="font-display text-xl mb-3 text-pg-tiza">{{ $servicio['titulo'] }}</h3>
                            <p class="text-pg-tiza/60 font-modern text-sm">{{ $servicio['descripcion'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ===== PLANES SECTION ===== -->
        <section id="planes" class="py-24 bg-pg-negro relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-16 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Inversión en ti</span>
                    <h2 class="font-display text-4xl md:text-5xl mt-4 text-pg-tiza">ELIGE TU PLAN</h2>
                    <p class="text-pg-tiza/60 mt-4 max-w-2xl mx-auto font-modern">
                        Planes diseñados para cada objetivo. Elige el que calce con tu ritmo.
                    </p>
                </div>
                
                <!-- Pricing Cards -->
                {{-- Los planes y los precios son los del catálogo: los mismos que se cobran en el mesón. --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                    @forelse($planes as $index => $plan)
                        <div class="animate-on-scroll" style="animation-delay: {{ $index * 0.1 }}s">
                            <div class="h-full flex flex-col bg-pg-carbon/70 border {{ $plan['destacado'] ? 'border-pg-rojo ring-2 ring-pg-rojo/30' : 'border-pg-tiza/10' }} rounded-2xl p-6 relative card-hover">
                                @if($plan['destacado'])
                                    {{-- Se cuenta, no se decide: es el plan con más membresías activas. --}}
                                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap bg-pg-rojo text-white text-xs font-bold px-3 py-1 rounded-full font-modern tracking-wide">
                                        EL MÁS ELEGIDO
                                    </span>
                                @endif

                                <h3 class="font-display text-2xl uppercase tracking-wide text-pg-tiza">{{ $plan['nombre'] }}</h3>
                                @if($plan['duracion'])
                                    <p class="text-pg-tiza/50 font-modern text-sm">{{ $plan['duracion'] }}</p>
                                @endif

                                <p class="mt-5 font-display text-4xl text-pg-tiza">${{ number_format($plan['precio'], 0, ',', '.') }}</p>

                                @if($plan['precio_convenio'])
                                    <p class="mt-1 text-pg-rojo-claro font-modern text-sm">Con convenio: ${{ number_format($plan['precio_convenio'], 0, ',', '.') }}</p>
                                @endif

                                @if($plan['descripcion'])
                                    <p class="mt-4 text-pg-tiza/60 font-modern text-sm">{{ $plan['descripcion'] }}</p>
                                @endif

                                <div class="mt-auto pt-6">
                                    <a href="#contacto" data-evento="elegir_plan" data-plan="{{ $plan['nombre'] }}" class="block w-full text-center py-3 rounded-lg font-semibold transition-all font-modern text-sm {{ $plan['destacado'] ? 'bg-pg-rojo hover:bg-pg-rojo-oscuro text-white btn-glow' : 'border border-pg-tiza/20 text-pg-tiza hover:border-pg-rojo hover:text-pg-rojo-claro' }}">
                                        Lo quiero
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="col-span-full text-center text-pg-tiza/60 font-modern">Pregunta por los planes en el mesón.</p>
                    @endforelse
                </div>

                @if(collect($planes)->contains(fn ($p) => $p['precio_convenio']))
                    <p class="mt-10 text-center text-pg-tiza/50 font-modern text-sm">
                        ¿Tu empresa o institución tiene convenio con nosotros? Pregunta por el precio de convenio.
                    </p>
                @endif
            </div>
        </section>

        <!-- ===== CTA SECTION ===== -->
        <section class="py-24 bg-gradient-to-r from-pg-rojo-oscuro via-pg-rojo to-pg-rojo-oscuro relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"40\" height=\"40\" viewBox=\"0 0 40 40\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M20 20.5V18H0v-2h20v-2H0v-2h20v-2H0V8h20V6H0V4h20V2H0V0h22v20.5h-2zM0 20h2v20H0V20z\" fill=\"%23000\" fill-opacity=\"0.2\" fill-rule=\"evenodd\"/%3E%3C/svg%3E');"></div>
            </div>
            
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                <h2 class="font-display text-4xl md:text-5xl text-white mb-6">
                    ¿LISTO PARA EL CAMBIO?
                </h2>
                <p class="text-white/80 text-lg mb-10 max-w-2xl mx-auto font-modern">
                    El mejor momento para empezar fue ayer. El segundo mejor momento es ahora.
                    Tu transformación comienza con un solo paso.
                </p>
                <a href="#contacto" class="inline-block bg-pg-negro hover:bg-pg-carbon text-pg-tiza font-bold px-12 py-5 rounded-lg text-lg transition-all hover:scale-105 font-modern">
                    <i class="fas fa-bolt mr-2"></i>
                    Empezar Hoy
                </a>
            </div>
        </section>

        <!-- ===== CONTACTO SECTION ===== -->
        <section id="contacto" class="py-24 bg-pg-negro relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                    <!-- Contact Info -->
                    <div class="animate-on-scroll">
                        <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Contáctanos</span>
                        <h2 class="font-display text-4xl md:text-5xl mt-4 mb-6 text-pg-tiza">HABLEMOS</h2>
                        <p class="text-pg-tiza/60 font-modern mb-10">
                            ¿Tienes dudas? ¿Quieres conocer nuestras instalaciones? 
                            Contáctanos y te ayudaremos a dar el primer paso.
                        </p>
                        
                        {{-- Salen de Configuración → El gimnasio. Lo vacío no se enseña: mejor nada que un teléfono de ejemplo. --}}
                        <div class="space-y-6">
                            @if($gimnasio['direccion'])
                                <div class="flex items-start">
                                    <div class="w-14 h-14 bg-pg-rojo/10 border border-pg-rojo/20 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                        <i class="fas fa-map-marker-alt text-pg-rojo-claro text-xl" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold mb-1 text-pg-tiza">Dirección</h4>
                                        <p class="text-pg-tiza/60 font-modern text-sm">{{ $gimnasio['direccion'] }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($gimnasio['telefono'])
                                <div class="flex items-start">
                                    <div class="w-14 h-14 bg-pg-rojo/10 border border-pg-rojo/20 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                        <i class="fas fa-phone-alt text-pg-rojo-claro text-xl" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold mb-1 text-pg-tiza">Teléfono</h4>
                                        <p class="text-pg-tiza/60 font-modern text-sm"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $gimnasio['telefono']) }}" class="hover:text-pg-tiza transition-colors" data-evento="contacto_directo">{{ $gimnasio['telefono'] }}</a></p>
                                    </div>
                                </div>
                            @endif
                            @if($gimnasio['email'])
                                <div class="flex items-start">
                                    <div class="w-14 h-14 bg-pg-rojo/10 border border-pg-rojo/20 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                        <i class="fas fa-envelope text-pg-rojo-claro text-xl" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold mb-1 text-pg-tiza">Correo</h4>
                                        <p class="text-pg-tiza/60 font-modern text-sm"><a href="mailto:{{ $gimnasio['email'] }}" class="hover:text-pg-tiza transition-colors" data-evento="contacto_directo">{{ $gimnasio['email'] }}</a></p>
                                    </div>
                                </div>
                            @endif
                            @if($gimnasio['horario'])
                                <div class="flex items-start">
                                    <div class="w-14 h-14 bg-pg-rojo/10 border border-pg-rojo/20 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                        <i class="fas fa-clock text-pg-rojo-claro text-xl" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold mb-1 text-pg-tiza">Horario</h4>
                                        <p class="text-pg-tiza/60 font-modern text-sm">{{ $gimnasio['horario'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($web['google_maps'] || $web['instagram'] || $web['facebook'])
                            <div class="mt-10 flex flex-wrap items-center gap-3">
                                @if($web['google_maps'])
                                    <a href="{{ $web['google_maps'] }}" target="_blank" rel="noopener" data-evento="como_llegar"
                                       class="inline-flex items-center gap-2 px-5 py-3 rounded-lg bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-modern text-sm font-semibold transition-colors">
                                        <i class="fas fa-route" aria-hidden="true"></i> Cómo llegar
                                    </a>
                                @endif
                                @if($web['instagram'])
                                    <a href="{{ $web['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"
                                       class="w-12 h-12 bg-pg-carbon border border-pg-tiza/10 hover:border-pg-rojo/40 rounded-xl flex items-center justify-center transition-all text-pg-tiza hover:text-pg-rojo-claro">
                                        <i class="fab fa-instagram text-xl" aria-hidden="true"></i>
                                    </a>
                                @endif
                                @if($web['facebook'])
                                    <a href="{{ $web['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"
                                       class="w-12 h-12 bg-pg-carbon border border-pg-tiza/10 hover:border-pg-rojo/40 rounded-xl flex items-center justify-center transition-all text-pg-tiza hover:text-pg-rojo-claro">
                                        <i class="fab fa-facebook-f text-xl" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Contact Form -->
                    <div class="animate-on-scroll">
                        <div class="bg-pg-carbon/50 border border-pg-tiza/10 rounded-2xl p-8 md:p-10">
                            <h3 class="font-display text-2xl mb-6 text-pg-tiza">ENVÍANOS UN MENSAJE</h3>
                            
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
                            
                            <form action="{{ route('landing.contacto') }}" method="POST" class="space-y-6">
                                @csrf
                                
                                <!-- Honeypot (anti-spam) -->
                                <div class="hp-field" aria-hidden="true">
                                    <label for="website">Website</label>
                                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="nombre" class="block text-sm font-medium mb-2 text-pg-tiza font-modern">Nombre *</label>
                                        <input 
                                            type="text" 
                                            name="nombre" 
                                            id="nombre" 
                                            value="{{ old('nombre') }}"
                                            required
                                            minlength="2"
                                            maxlength="100"
                                            class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder-pg-tiza/30 transition-colors focus:outline-none focus:ring-2 focus:ring-pg-rojo/20 font-modern"
                                            placeholder="Tu nombre"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label for="email" class="block text-sm font-medium mb-2 text-pg-tiza font-modern">Email *</label>
                                        <input 
                                            type="email" 
                                            name="email" 
                                            id="email" 
                                            value="{{ old('email') }}"
                                            required
                                            maxlength="255"
                                            class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder-pg-tiza/30 transition-colors focus:outline-none focus:ring-2 focus:ring-pg-rojo/20 font-modern"
                                            placeholder="tu@email.com"
                                        >
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="telefono" class="block text-sm font-medium mb-2 text-pg-tiza font-modern">Teléfono</label>
                                        <input 
                                            type="tel" 
                                            name="telefono" 
                                            id="telefono" 
                                            value="{{ old('telefono') }}"
                                            maxlength="20"
                                            class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder-pg-tiza/30 transition-colors focus:outline-none focus:ring-2 focus:ring-pg-rojo/20 font-modern"
                                            placeholder="+56 9 1234 5678"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label for="servicio" class="block text-sm font-medium mb-2 text-pg-tiza font-modern">Interés</label>
                                        <select 
                                            name="servicio" 
                                            id="servicio"
                                            class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza transition-colors focus:outline-none focus:ring-2 focus:ring-pg-rojo/20 font-modern"
                                        >
                                            <option value="informacion">Información general</option>
                                            <option value="inscripcion">Quiero inscribirme</option>
                                            <option value="convenio">Convenio de empresa</option>
                                            <option value="otro">Otro</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="mensaje" class="block text-sm font-medium mb-2 text-pg-tiza font-modern">Mensaje *</label>
                                    <textarea 
                                        name="mensaje" 
                                        id="mensaje" 
                                        rows="5"
                                        required
                                        minlength="10"
                                        maxlength="1000"
                                        class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder-pg-tiza/30 transition-colors focus:outline-none focus:ring-2 focus:ring-pg-rojo/20 resize-none font-modern"
                                        placeholder="¿En qué podemos ayudarte?"
                                    >{{ old('mensaje') }}</textarea>
                                </div>
                                
                                <button 
                                    type="submit" 
                                    class="w-full bg-gradient-to-r from-pg-rojo to-pg-rojo-oscuro hover:from-pg-rojo-oscuro hover:to-pg-rojo text-white font-bold py-4 rounded-lg transition-all btn-glow flex items-center justify-center font-modern"
                                >
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Enviar Mensaje
                                </button>
                                
                                <p class="text-pg-tiza/40 text-sm text-center font-modern">
                                    <i class="fas fa-lock mr-1"></i>
                                    Tu información está segura y no será compartida.
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== CONSULTA MEMBRESÍA SECTION ===== -->
        <section id="consulta" class="py-24 bg-pg-carbon relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-pg-rojo/30 to-transparent"></div>
            
            <!-- Glow decorativo -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-pg-rojo/10 rounded-full blur-3xl"></div>
            
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-12 animate-on-scroll">
                    <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">¿Ya eres miembro?</span>
                    <h2 class="font-display text-4xl md:text-5xl mt-4 text-pg-tiza">CONSULTA TU MEMBRESÍA</h2>
                    <p class="text-pg-tiza/60 mt-4 max-w-2xl mx-auto font-modern">
                        Ingresa tu RUT y los últimos 4 dígitos de tu celular para ver cómo está tu membresía.
                    </p>
                </div>
                
                <!-- Formulario de consulta -->
                <div class="max-w-md mx-auto animate-on-scroll">
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
                                        class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder-pg-tiza/30 transition-colors focus:outline-none focus:ring-2 focus:ring-pg-rojo/20 font-modern text-center text-lg tracking-wider"
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
                                        class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder-pg-tiza/30 transition-colors focus:outline-none focus:ring-2 focus:ring-pg-rojo/20 font-modern text-center text-lg tracking-[0.5em]"
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
                                        class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder-pg-tiza/30 transition-colors focus:outline-none focus:ring-2 focus:ring-pg-rojo/20 font-modern text-center text-lg tracking-wider"
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
                                        class="w-full bg-pg-negro border border-pg-tiza/20 focus:border-pg-rojo rounded-lg px-4 py-3 text-pg-tiza placeholder-pg-tiza/30 transition-colors focus:outline-none focus:ring-2 focus:ring-pg-rojo/20 font-modern text-center"
                                    >
                                    <p class="text-pg-tiza/40 text-xs mt-1 font-modern text-center">Para verificar tu identidad</p>
                                </div>
                            </div>
                            
                            <button 
                                type="button"
                                id="btn-consultar"
                                class="w-full bg-gradient-to-r from-pg-rojo to-pg-rojo-oscuro hover:from-pg-rojo-oscuro hover:to-pg-rojo text-white font-bold py-4 rounded-lg transition-all btn-glow flex items-center justify-center font-modern"
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
                            <div class="w-16 h-16 bg-gradient-to-br from-pg-rojo/30 to-pg-rojo-oscuro/30 rounded-full flex items-center justify-center mx-auto mb-4">
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
                            <div class="bg-gradient-to-br from-pg-rojo/20 to-pg-rojo-oscuro/20 border border-pg-rojo/30 rounded-xl p-4 text-center">
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
            </div>
        </section>

        <!-- ===== FOOTER ===== -->
        <footer class="bg-pg-carbon border-t border-pg-tiza/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <!-- Brand -->
                    <div class="md:col-span-2">
                        <picture>
                        <source srcset="{{ asset('images/progym-logo.webp') }}" type="image/webp">
                        <img src="{{ asset('images/progym-logo.png') }}" alt="{{ $gimnasio['nombre'] }}" width="1096" height="495" class="h-16 w-auto" loading="lazy">
                    </picture>
                        @if($gimnasio['direccion'] || $gimnasio['horario'])
                            <p class="text-pg-tiza/50 max-w-md font-modern text-sm mt-4">
                                {{ $gimnasio['direccion'] }}
                                @if($gimnasio['direccion'] && $gimnasio['horario'])<br>@endif
                                {{ $gimnasio['horario'] }}
                            </p>
                        @endif
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h4 class="font-semibold mb-4 text-pg-tiza">Enlaces Rápidos</h4>
                        <ul class="space-y-2 font-modern text-sm">
                            <li><a href="#inicio" class="text-pg-tiza/50 hover:text-pg-rojo-claro transition-colors">Inicio</a></li>
                            <li><a href="#servicios" class="text-pg-tiza/50 hover:text-pg-rojo-claro transition-colors">Servicios</a></li>
                            <li><a href="#planes" class="text-pg-tiza/50 hover:text-pg-rojo-claro transition-colors">Planes</a></li>
                            <li><a href="#contacto" class="text-pg-tiza/50 hover:text-pg-rojo-claro transition-colors">Contacto</a></li>
                        </ul>
                    </div>
                    
                </div>

                <div class="border-t border-pg-tiza/5 mt-12 pt-8 flex flex-col md:flex-row items-center justify-between">
                    <p class="text-pg-tiza/40 text-sm font-modern">
                        &copy; {{ date('Y') }} {{ $gimnasio['nombre'] }}. Todos los derechos reservados.
                    </p>
                    <p class="text-pg-tiza/40 text-sm mt-2 md:mt-0 font-modern">
                        Hecho con <i class="fas fa-heart text-pg-rojo"></i> en Chile
                    </p>
                </div>
            </div>
        </footer>
    </main>
@endsection

@section('scripts')
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.pgEvento) window.pgEvento('generate_lead');
    });
</script>
@endif
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
