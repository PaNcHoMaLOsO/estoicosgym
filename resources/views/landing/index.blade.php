@extends('layouts.landing')

@section('title', 'Estoicos Gym - Transforma tu cuerpo, fortalece tu mente')
@section('description', 'Únete al gimnasio líder en entrenamiento de alto rendimiento. Equipos de última generación, entrenadores certificados y planes personalizados para alcanzar tus metas.')

@section('content')
    <!-- ===== NAVBAR ===== -->
    <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="#" class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gym-gold rounded-lg flex items-center justify-center">
                        <i class="fas fa-dumbbell text-black text-xl"></i>
                    </div>
                    <span class="font-display text-3xl tracking-wider">ESTOICOS</span>
                </a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#inicio" class="text-white/80 hover:text-gym-gold transition-colors">Inicio</a>
                    <a href="#servicios" class="text-white/80 hover:text-gym-gold transition-colors">Servicios</a>
                    <a href="#planes" class="text-white/80 hover:text-gym-gold transition-colors">Planes</a>
                    <a href="#testimonios" class="text-white/80 hover:text-gym-gold transition-colors">Testimonios</a>
                    <a href="#contacto" class="text-white/80 hover:text-gym-gold transition-colors">Contacto</a>
                    <a href="{{ route('login') }}" class="bg-gym-gold hover:bg-gym-gold-dark text-black font-semibold px-6 py-2 rounded-lg transition-all btn-glow">
                        Acceder
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-white p-2">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-4">
                    <a href="#inicio" class="text-white/80 hover:text-gym-gold transition-colors py-2">Inicio</a>
                    <a href="#servicios" class="text-white/80 hover:text-gym-gold transition-colors py-2">Servicios</a>
                    <a href="#planes" class="text-white/80 hover:text-gym-gold transition-colors py-2">Planes</a>
                    <a href="#testimonios" class="text-white/80 hover:text-gym-gold transition-colors py-2">Testimonios</a>
                    <a href="#contacto" class="text-white/80 hover:text-gym-gold transition-colors py-2">Contacto</a>
                    <a href="{{ route('login') }}" class="bg-gym-gold hover:bg-gym-gold-dark text-black font-semibold px-6 py-3 rounded-lg text-center transition-all">
                        Acceder
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main id="main-content">
        <!-- ===== HERO SECTION ===== -->
        <section id="inicio" class="relative min-h-screen flex items-center justify-center overflow-hidden">
            <!-- Background con overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-gym-darker via-gym-dark to-gym-gray">
                <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23D4AF37\" fill-opacity=\"0.1\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            
            <!-- Decorative elements -->
            <div class="absolute top-1/4 left-10 w-72 h-72 bg-gym-gold/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 right-10 w-96 h-96 bg-gym-gold/5 rounded-full blur-3xl"></div>
            
            <!-- Content -->
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="fade-in">
                    <span class="inline-block px-4 py-2 bg-gym-gold/10 border border-gym-gold/30 rounded-full text-gym-gold text-sm font-medium mb-6">
                        <i class="fas fa-fire mr-2"></i>
                        Nuevos planes disponibles
                    </span>
                    
                    <h1 class="font-display text-6xl sm:text-7xl md:text-8xl lg:text-9xl tracking-tight mb-6">
                        <span class="block">TRANSFORMA</span>
                        <span class="gradient-text">TU CUERPO</span>
                    </h1>
                    
                    <p class="text-xl sm:text-2xl text-white/70 max-w-3xl mx-auto mb-10">
                        Únete a la comunidad de guerreros que eligen superarse cada día. 
                        Equipos de élite, entrenadores expertos y un ambiente que te impulsa.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="#planes" class="w-full sm:w-auto bg-gym-gold hover:bg-gym-gold-dark text-black font-bold px-10 py-4 rounded-lg text-lg transition-all btn-glow">
                            <i class="fas fa-rocket mr-2"></i>
                            Comenzar Ahora
                        </a>
                        <a href="#servicios" class="w-full sm:w-auto border-2 border-white/30 hover:border-gym-gold text-white hover:text-gym-gold px-10 py-4 rounded-lg text-lg transition-all">
                            <i class="fas fa-play-circle mr-2"></i>
                            Conocer Más
                        </a>
                    </div>
                </div>
                
                <!-- Stats -->
                <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                    <div class="animate-on-scroll">
                        <div class="font-display text-5xl text-gym-gold">500+</div>
                        <div class="text-white/60">Miembros Activos</div>
                    </div>
                    <div class="animate-on-scroll" style="animation-delay: 0.1s">
                        <div class="font-display text-5xl text-gym-gold">15+</div>
                        <div class="text-white/60">Entrenadores</div>
                    </div>
                    <div class="animate-on-scroll" style="animation-delay: 0.2s">
                        <div class="font-display text-5xl text-gym-gold">24/7</div>
                        <div class="text-white/60">Acceso Total</div>
                    </div>
                    <div class="animate-on-scroll" style="animation-delay: 0.3s">
                        <div class="font-display text-5xl text-gym-gold">98%</div>
                        <div class="text-white/60">Satisfacción</div>
                    </div>
                </div>
            </div>
            
            <!-- Scroll indicator -->
            <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
                <a href="#servicios" class="text-gym-gold/50 hover:text-gym-gold transition-colors">
                    <i class="fas fa-chevron-down text-2xl"></i>
                </a>
            </div>
        </section>

        <!-- ===== SERVICIOS SECTION ===== -->
        <section id="servicios" class="py-24 bg-gym-darker relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gym-gold/30 to-transparent"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-16 animate-on-scroll">
                    <span class="text-gym-gold font-medium tracking-widest uppercase text-sm">Lo que ofrecemos</span>
                    <h2 class="font-display text-5xl md:text-6xl mt-4">NUESTROS SERVICIOS</h2>
                    <p class="text-white/60 mt-4 max-w-2xl mx-auto text-lg">
                        Todo lo que necesitas para alcanzar tu mejor versión en un solo lugar.
                    </p>
                </div>
                
                <!-- Services Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($servicios as $index => $servicio)
                        <div class="animate-on-scroll card-hover bg-gym-gray/50 border border-white/5 rounded-2xl p-8 hover:border-gym-gold/30" style="animation-delay: {{ $index * 0.1 }}s">
                            <div class="w-16 h-16 bg-gym-gold/10 rounded-xl flex items-center justify-center mb-6">
                                <i class="fas fa-{{ $servicio['icono'] }} text-gym-gold text-2xl"></i>
                            </div>
                            <h3 class="font-display text-2xl mb-3">{{ $servicio['titulo'] }}</h3>
                            <p class="text-white/60">{{ $servicio['descripcion'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ===== PLANES SECTION ===== -->
        <section id="planes" class="py-24 bg-gym-dark relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-16 animate-on-scroll">
                    <span class="text-gym-gold font-medium tracking-widest uppercase text-sm">Inversión en ti</span>
                    <h2 class="font-display text-5xl md:text-6xl mt-4">ELIGE TU PLAN</h2>
                    <p class="text-white/60 mt-4 max-w-2xl mx-auto text-lg">
                        Planes diseñados para cada objetivo. Sin matrículas ocultas, sin compromisos de largo plazo.
                    </p>
                </div>
                
                <!-- Pricing Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    @foreach($planes as $index => $plan)
                        <div class="animate-on-scroll {{ $plan['destacado'] ? 'md:-mt-4 md:mb-4' : '' }}" style="animation-delay: {{ $index * 0.15 }}s">
                            <div class="h-full bg-gym-gray/30 border {{ $plan['destacado'] ? 'border-gym-gold' : 'border-white/10' }} rounded-2xl p-8 relative card-hover {{ $plan['destacado'] ? 'ring-2 ring-gym-gold/20' : '' }}">
                                @if($plan['destacado'])
                                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                        <span class="bg-gym-gold text-black text-sm font-bold px-4 py-1 rounded-full">
                                            MÁS POPULAR
                                        </span>
                                    </div>
                                @endif
                                
                                <div class="text-center mb-8">
                                    <h3 class="font-display text-3xl mb-2">{{ $plan['nombre'] }}</h3>
                                    <div class="flex items-baseline justify-center">
                                        <span class="text-4xl font-bold text-gym-gold">${{ number_format($plan['precio'], 0, ',', '.') }}</span>
                                        <span class="text-white/60 ml-2">/{{ $plan['periodo'] }}</span>
                                    </div>
                                </div>
                                
                                <ul class="space-y-4 mb-8">
                                    @foreach($plan['caracteristicas'] as $caracteristica)
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-gym-gold mt-1 mr-3"></i>
                                            <span class="text-white/80">{{ $caracteristica }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                
                                <a href="#contacto" class="block w-full text-center py-4 rounded-lg font-semibold transition-all {{ $plan['destacado'] ? 'bg-gym-gold hover:bg-gym-gold-dark text-black btn-glow' : 'border border-gym-gold text-gym-gold hover:bg-gym-gold/10' }}">
                                    Elegir Plan
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Guarantee Badge -->
                <div class="text-center mt-12 animate-on-scroll">
                    <div class="inline-flex items-center bg-gym-gold/10 border border-gym-gold/30 rounded-full px-6 py-3">
                        <i class="fas fa-shield-alt text-gym-gold mr-3"></i>
                        <span class="text-white/80">Garantía de satisfacción de 7 días o te devolvemos tu dinero</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== TESTIMONIOS SECTION ===== -->
        <section id="testimonios" class="py-24 bg-gym-darker relative overflow-hidden">
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-20 left-20 text-gym-gold text-9xl font-display">"</div>
                <div class="absolute bottom-20 right-20 text-gym-gold text-9xl font-display rotate-180">"</div>
            </div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-16 animate-on-scroll">
                    <span class="text-gym-gold font-medium tracking-widest uppercase text-sm">Historias de éxito</span>
                    <h2 class="font-display text-5xl md:text-6xl mt-4">LO QUE DICEN</h2>
                    <p class="text-white/60 mt-4 max-w-2xl mx-auto text-lg">
                        Miles de personas ya transformaron sus vidas. Esta es su experiencia.
                    </p>
                </div>
                
                <!-- Testimonials Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($testimonios as $index => $testimonio)
                        <div class="animate-on-scroll bg-gym-gray/30 border border-white/5 rounded-2xl p-8 card-hover" style="animation-delay: {{ $index * 0.1 }}s">
                            <!-- Stars -->
                            <div class="flex mb-4">
                                @for($i = 0; $i < $testimonio['rating']; $i++)
                                    <i class="fas fa-star text-gym-gold"></i>
                                @endfor
                            </div>
                            
                            <p class="text-white/80 mb-6 italic">"{{ $testimonio['texto'] }}"</p>
                            
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gym-gold/20 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-user text-gym-gold"></i>
                                </div>
                                <div>
                                    <div class="font-semibold">{{ $testimonio['nombre'] }}</div>
                                    <div class="text-white/50 text-sm">Miembro verificado</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ===== CTA SECTION ===== -->
        <section class="py-24 bg-gradient-to-r from-gym-gold-dark via-gym-gold to-gym-gold-dark relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"40\" height=\"40\" viewBox=\"0 0 40 40\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M20 20.5V18H0v-2h20v-2H0v-2h20v-2H0V8h20V6H0V4h20V2H0V0h22v20.5h-2zM0 20h2v20H0V20z\" fill=\"%23000\" fill-opacity=\"0.2\" fill-rule=\"evenodd\"/%3E%3C/svg%3E');"></div>
            </div>
            
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                <h2 class="font-display text-5xl md:text-6xl text-black mb-6">
                    ¿LISTO PARA EL CAMBIO?
                </h2>
                <p class="text-black/80 text-xl mb-10 max-w-2xl mx-auto">
                    El mejor momento para empezar fue ayer. El segundo mejor momento es ahora.
                    Tu transformación comienza con un solo paso.
                </p>
                <a href="#contacto" class="inline-block bg-black hover:bg-gym-darker text-white font-bold px-12 py-5 rounded-lg text-lg transition-all hover:scale-105">
                    <i class="fas fa-bolt mr-2"></i>
                    Empezar Hoy
                </a>
            </div>
        </section>

        <!-- ===== CONTACTO SECTION ===== -->
        <section id="contacto" class="py-24 bg-gym-dark relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                    <!-- Contact Info -->
                    <div class="animate-on-scroll">
                        <span class="text-gym-gold font-medium tracking-widest uppercase text-sm">Contáctanos</span>
                        <h2 class="font-display text-5xl md:text-6xl mt-4 mb-6">HABLEMOS</h2>
                        <p class="text-white/60 text-lg mb-10">
                            ¿Tienes dudas? ¿Quieres conocer nuestras instalaciones? 
                            Contáctanos y te ayudaremos a dar el primer paso.
                        </p>
                        
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="w-14 h-14 bg-gym-gold/10 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-gym-gold text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1">Dirección</h4>
                                    <p class="text-white/60">Av. Principal #1234, Santiago, Chile</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-14 h-14 bg-gym-gold/10 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-phone-alt text-gym-gold text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1">Teléfono</h4>
                                    <p class="text-white/60">+56 9 1234 5678</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-14 h-14 bg-gym-gold/10 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-envelope text-gym-gold text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1">Email</h4>
                                    <p class="text-white/60">contacto@estoicosgym.cl</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-14 h-14 bg-gym-gold/10 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-clock text-gym-gold text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1">Horario</h4>
                                    <p class="text-white/60">Lun - Vie: 6:00 - 23:00</p>
                                    <p class="text-white/60">Sáb - Dom: 8:00 - 20:00</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Social Links -->
                        <div class="mt-10">
                            <h4 class="font-semibold mb-4">Síguenos</h4>
                            <div class="flex space-x-4">
                                <a href="#" class="w-12 h-12 bg-gym-gray hover:bg-gym-gold/20 border border-white/10 hover:border-gym-gold/30 rounded-xl flex items-center justify-center transition-all" aria-label="Instagram">
                                    <i class="fab fa-instagram text-xl"></i>
                                </a>
                                <a href="#" class="w-12 h-12 bg-gym-gray hover:bg-gym-gold/20 border border-white/10 hover:border-gym-gold/30 rounded-xl flex items-center justify-center transition-all" aria-label="Facebook">
                                    <i class="fab fa-facebook-f text-xl"></i>
                                </a>
                                <a href="#" class="w-12 h-12 bg-gym-gray hover:bg-gym-gold/20 border border-white/10 hover:border-gym-gold/30 rounded-xl flex items-center justify-center transition-all" aria-label="YouTube">
                                    <i class="fab fa-youtube text-xl"></i>
                                </a>
                                <a href="#" class="w-12 h-12 bg-gym-gray hover:bg-gym-gold/20 border border-white/10 hover:border-gym-gold/30 rounded-xl flex items-center justify-center transition-all" aria-label="TikTok">
                                    <i class="fab fa-tiktok text-xl"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="animate-on-scroll">
                        <div class="bg-gym-gray/30 border border-white/10 rounded-2xl p-8 md:p-10">
                            <h3 class="font-display text-3xl mb-6">ENVÍANOS UN MENSAJE</h3>
                            
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
                                        <label for="nombre" class="block text-sm font-medium mb-2">Nombre *</label>
                                        <input 
                                            type="text" 
                                            name="nombre" 
                                            id="nombre" 
                                            value="{{ old('nombre') }}"
                                            required
                                            minlength="2"
                                            maxlength="100"
                                            class="w-full bg-gym-darker border border-white/10 focus:border-gym-gold rounded-lg px-4 py-3 text-white placeholder-white/30 transition-colors focus:outline-none focus:ring-2 focus:ring-gym-gold/20"
                                            placeholder="Tu nombre"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label for="email" class="block text-sm font-medium mb-2">Email *</label>
                                        <input 
                                            type="email" 
                                            name="email" 
                                            id="email" 
                                            value="{{ old('email') }}"
                                            required
                                            maxlength="255"
                                            class="w-full bg-gym-darker border border-white/10 focus:border-gym-gold rounded-lg px-4 py-3 text-white placeholder-white/30 transition-colors focus:outline-none focus:ring-2 focus:ring-gym-gold/20"
                                            placeholder="tu@email.com"
                                        >
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="telefono" class="block text-sm font-medium mb-2">Teléfono</label>
                                        <input 
                                            type="tel" 
                                            name="telefono" 
                                            id="telefono" 
                                            value="{{ old('telefono') }}"
                                            maxlength="20"
                                            class="w-full bg-gym-darker border border-white/10 focus:border-gym-gold rounded-lg px-4 py-3 text-white placeholder-white/30 transition-colors focus:outline-none focus:ring-2 focus:ring-gym-gold/20"
                                            placeholder="+56 9 1234 5678"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label for="servicio" class="block text-sm font-medium mb-2">Interés</label>
                                        <select 
                                            name="servicio" 
                                            id="servicio"
                                            class="w-full bg-gym-darker border border-white/10 focus:border-gym-gold rounded-lg px-4 py-3 text-white transition-colors focus:outline-none focus:ring-2 focus:ring-gym-gold/20"
                                        >
                                            <option value="informacion">Información general</option>
                                            <option value="inscripcion">Inscripción</option>
                                            <option value="clases">Clases grupales</option>
                                            <option value="personal">Personal training</option>
                                            <option value="otro">Otro</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="mensaje" class="block text-sm font-medium mb-2">Mensaje *</label>
                                    <textarea 
                                        name="mensaje" 
                                        id="mensaje" 
                                        rows="5"
                                        required
                                        minlength="10"
                                        maxlength="1000"
                                        class="w-full bg-gym-darker border border-white/10 focus:border-gym-gold rounded-lg px-4 py-3 text-white placeholder-white/30 transition-colors focus:outline-none focus:ring-2 focus:ring-gym-gold/20 resize-none"
                                        placeholder="¿En qué podemos ayudarte?"
                                    >{{ old('mensaje') }}</textarea>
                                </div>
                                
                                <button 
                                    type="submit" 
                                    class="w-full bg-gym-gold hover:bg-gym-gold-dark text-black font-bold py-4 rounded-lg transition-all btn-glow flex items-center justify-center"
                                >
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Enviar Mensaje
                                </button>
                                
                                <p class="text-white/40 text-sm text-center">
                                    <i class="fas fa-lock mr-1"></i>
                                    Tu información está segura y no será compartida.
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== FOOTER ===== -->
        <footer class="bg-gym-darker border-t border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                    <!-- Brand -->
                    <div class="md:col-span-2">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-gym-gold rounded-lg flex items-center justify-center">
                                <i class="fas fa-dumbbell text-black text-xl"></i>
                            </div>
                            <span class="font-display text-3xl tracking-wider">ESTOICOS</span>
                        </div>
                        <p class="text-white/50 max-w-md">
                            Somos más que un gimnasio. Somos una comunidad comprometida con tu transformación física y mental. 
                            Únete a los estoicos y descubre tu verdadero potencial.
                        </p>
                    </div>
                    
                    <!-- Quick Links -->
                    <div>
                        <h4 class="font-semibold mb-4">Enlaces Rápidos</h4>
                        <ul class="space-y-2">
                            <li><a href="#inicio" class="text-white/50 hover:text-gym-gold transition-colors">Inicio</a></li>
                            <li><a href="#servicios" class="text-white/50 hover:text-gym-gold transition-colors">Servicios</a></li>
                            <li><a href="#planes" class="text-white/50 hover:text-gym-gold transition-colors">Planes</a></li>
                            <li><a href="#contacto" class="text-white/50 hover:text-gym-gold transition-colors">Contacto</a></li>
                            <li><a href="{{ route('login') }}" class="text-white/50 hover:text-gym-gold transition-colors">Acceder</a></li>
                        </ul>
                    </div>
                    
                    <!-- Legal -->
                    <div>
                        <h4 class="font-semibold mb-4">Legal</h4>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-white/50 hover:text-gym-gold transition-colors">Términos de servicio</a></li>
                            <li><a href="#" class="text-white/50 hover:text-gym-gold transition-colors">Política de privacidad</a></li>
                            <li><a href="#" class="text-white/50 hover:text-gym-gold transition-colors">Política de cookies</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-white/5 mt-12 pt-8 flex flex-col md:flex-row items-center justify-between">
                    <p class="text-white/40 text-sm">
                        &copy; {{ date('Y') }} Estoicos Gym. Todos los derechos reservados.
                    </p>
                    <p class="text-white/40 text-sm mt-2 md:mt-0">
                        Hecho con <i class="fas fa-heart text-gym-gold"></i> en Chile
                    </p>
                </div>
            </div>
        </footer>
    </main>
@endsection
