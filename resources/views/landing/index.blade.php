@extends('layouts.landing')

@section('title', 'Estoicos Gym - Transforma tu cuerpo, fortalece tu mente')
@section('description', 'Únete al gimnasio líder en entrenamiento de alto rendimiento. Equipos de última generación, entrenadores certificados y planes personalizados para alcanzar tus metas.')

@section('content')
    <!-- ===== NIGHT SKY STARS (igual que login) ===== -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        @for($i = 1; $i <= 30; $i++)
            <div class="star absolute w-1 h-1 bg-gym-cream rounded-full" style="top: {{ rand(5, 95) }}%; left: {{ rand(5, 95) }}%; animation-delay: {{ $i * 0.1 }}s;"></div>
        @endfor
    </div>

    <!-- ===== NAVBAR ===== -->
    <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="#" class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-gym-purple to-gym-purple-dark rounded-lg flex items-center justify-center shadow-lg shadow-gym-purple/30">
                        <i class="fas fa-dumbbell text-gym-cream text-xl"></i>
                    </div>
                    <span class="font-display text-2xl tracking-widest text-gym-cream">ESTOICOS</span>
                </a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#inicio" class="text-gym-cream/80 hover:text-gym-purple-light transition-colors font-modern text-sm">Inicio</a>
                    <a href="#servicios" class="text-gym-cream/80 hover:text-gym-purple-light transition-colors font-modern text-sm">Servicios</a>
                    <a href="#planes" class="text-gym-cream/80 hover:text-gym-purple-light transition-colors font-modern text-sm">Planes</a>
                    <a href="#testimonios" class="text-gym-cream/80 hover:text-gym-purple-light transition-colors font-modern text-sm">Testimonios</a>
                    <a href="#contacto" class="text-gym-cream/80 hover:text-gym-purple-light transition-colors font-modern text-sm">Contacto</a>
                    <a href="{{ route('login') }}" class="bg-gradient-to-r from-gym-purple to-gym-purple-dark hover:from-gym-purple-dark hover:to-gym-purple text-white font-semibold px-6 py-2.5 rounded-lg transition-all btn-glow font-modern text-sm">
                        Acceder
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-gym-cream p-2">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4 bg-gym-navy-dark/95 backdrop-blur-md rounded-b-xl">
                <div class="flex flex-col space-y-4 px-4">
                    <a href="#inicio" class="text-gym-cream/80 hover:text-gym-purple-light transition-colors py-2">Inicio</a>
                    <a href="#servicios" class="text-gym-cream/80 hover:text-gym-purple-light transition-colors py-2">Servicios</a>
                    <a href="#planes" class="text-gym-cream/80 hover:text-gym-purple-light transition-colors py-2">Planes</a>
                    <a href="#testimonios" class="text-gym-cream/80 hover:text-gym-purple-light transition-colors py-2">Testimonios</a>
                    <a href="#contacto" class="text-gym-cream/80 hover:text-gym-purple-light transition-colors py-2">Contacto</a>
                    <a href="{{ route('login') }}" class="bg-gradient-to-r from-gym-purple to-gym-purple-dark text-white font-semibold px-6 py-3 rounded-lg text-center transition-all">
                        Acceder
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main id="main-content">
        <!-- ===== HERO SECTION ===== -->
        <section id="inicio" class="relative min-h-screen flex items-center justify-center overflow-hidden">
            <!-- Background con gradiente navy -->
            <div class="absolute inset-0 bg-gradient-to-br from-gym-navy-dark via-gym-navy to-gym-navy-light">
                <!-- Pattern decorativo -->
                <div class="absolute inset-0 opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M54.627 0l.83.828-1.415 1.415L51.8 0h2.827zM5.373 0l-.83.828L5.96 2.243 8.2 0H5.374zM48.97 0l3.657 3.657-1.414 1.414L46.143 0h2.828zM11.03 0L7.372 3.657 8.787 5.07 13.857 0H11.03zm32.284 0L49.8 6.485 48.384 7.9l-7.9-7.9h2.83zM16.686 0L10.2 6.485 11.616 7.9l7.9-7.9h-2.83zM22.344 0L13.858 8.485 15.272 9.9l7.9-7.9h-.828zm5.656 0L19.515 8.485 20.929 9.9 28.828 2l-.828-.828zM33.656 0L25.172 8.485 26.586 9.9l8.485-8.485L34.243 0h-.587zM39.314 0L30.828 8.485 32.243 9.9l8.485-8.485-.828-.829h-.586zm5.657 0l-8.485 8.485 1.414 1.414 8.485-8.485-.828-.829h-.586z\" fill=\"%23c140d4\" fill-opacity=\"0.4\" fill-rule=\"evenodd\"/%3E%3C/svg%3E');"></div>
            </div>
            
            <!-- Decorative glows -->
            <div class="absolute top-1/4 left-10 w-72 h-72 bg-gym-purple/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 right-10 w-96 h-96 bg-gym-gold/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gym-purple/5 rounded-full blur-3xl"></div>
            
            <!-- Content -->
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-20">
                <div class="fade-in">
                    <span class="inline-block px-4 py-2 bg-gym-purple/20 border border-gym-purple/40 rounded-full text-gym-purple-light text-sm font-modern mb-6">
                        <i class="fas fa-fire mr-2"></i>
                        Nuevos planes disponibles
                    </span>
                    
                    <h1 class="font-display text-5xl sm:text-6xl md:text-7xl lg:text-8xl tracking-wider mb-6">
                        <span class="block text-gym-cream">TRANSFORMA</span>
                        <span class="gradient-text">TU CUERPO</span>
                    </h1>
                    
                    <p class="text-lg sm:text-xl text-gym-cream/70 max-w-3xl mx-auto mb-10 font-modern">
                        Únete a la comunidad de guerreros que eligen superarse cada día. 
                        Equipos de élite, entrenadores expertos y un ambiente que te impulsa.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="#planes" class="w-full sm:w-auto bg-gradient-to-r from-gym-purple to-gym-purple-dark hover:from-gym-purple-dark hover:to-gym-purple text-white font-bold px-10 py-4 rounded-lg text-lg transition-all btn-glow font-modern">
                            <i class="fas fa-rocket mr-2"></i>
                            Comenzar Ahora
                        </a>
                        <a href="#servicios" class="w-full sm:w-auto border-2 border-gym-cream/30 hover:border-gym-purple text-gym-cream hover:text-gym-purple-light px-10 py-4 rounded-lg text-lg transition-all font-modern">
                            <i class="fas fa-play-circle mr-2"></i>
                            Conocer Más
                        </a>
                    </div>
                </div>
                
                <!-- Stats -->
                <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                    <div class="animate-on-scroll">
                        <div class="font-display text-4xl md:text-5xl text-gym-purple-light">500+</div>
                        <div class="text-gym-cream/60 font-modern text-sm">Miembros Activos</div>
                    </div>
                    <div class="animate-on-scroll" style="animation-delay: 0.1s">
                        <div class="font-display text-4xl md:text-5xl text-gym-purple-light">15+</div>
                        <div class="text-gym-cream/60 font-modern text-sm">Entrenadores</div>
                    </div>
                    <div class="animate-on-scroll" style="animation-delay: 0.2s">
                        <div class="font-display text-4xl md:text-5xl text-gym-purple-light">24/7</div>
                        <div class="text-gym-cream/60 font-modern text-sm">Acceso Total</div>
                    </div>
                    <div class="animate-on-scroll" style="animation-delay: 0.3s">
                        <div class="font-display text-4xl md:text-5xl text-gym-purple-light">98%</div>
                        <div class="text-gym-cream/60 font-modern text-sm">Satisfacción</div>
                    </div>
                </div>
            </div>
            
            <!-- Scroll indicator -->
            <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
                <a href="#servicios" class="text-gym-purple/50 hover:text-gym-purple-light transition-colors">
                    <i class="fas fa-chevron-down text-2xl"></i>
                </a>
            </div>
        </section>

        <!-- ===== SERVICIOS SECTION ===== -->
        <section id="servicios" class="py-24 bg-gym-navy relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gym-purple/30 to-transparent"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-16 animate-on-scroll">
                    <span class="text-gym-purple-light font-modern tracking-widest uppercase text-sm">Lo que ofrecemos</span>
                    <h2 class="font-display text-4xl md:text-5xl mt-4 text-gym-cream">NUESTROS SERVICIOS</h2>
                    <p class="text-gym-cream/60 mt-4 max-w-2xl mx-auto font-modern">
                        Todo lo que necesitas para alcanzar tu mejor versión en un solo lugar.
                    </p>
                </div>
                
                <!-- Services Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($servicios as $index => $servicio)
                        <div class="animate-on-scroll card-hover bg-gym-navy-dark/50 border border-gym-cream/10 rounded-2xl p-8 hover:border-gym-purple/30" style="animation-delay: {{ $index * 0.1 }}s">
                            <div class="w-16 h-16 bg-gradient-to-br from-gym-purple/20 to-gym-purple-dark/20 rounded-xl flex items-center justify-center mb-6">
                                <i class="fas fa-{{ $servicio['icono'] }} text-gym-purple-light text-2xl"></i>
                            </div>
                            <h3 class="font-display text-xl mb-3 text-gym-cream">{{ $servicio['titulo'] }}</h3>
                            <p class="text-gym-cream/60 font-modern text-sm">{{ $servicio['descripcion'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ===== PLANES SECTION ===== -->
        <section id="planes" class="py-24 bg-gym-navy-dark relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-16 animate-on-scroll">
                    <span class="text-gym-purple-light font-modern tracking-widest uppercase text-sm">Inversión en ti</span>
                    <h2 class="font-display text-4xl md:text-5xl mt-4 text-gym-cream">ELIGE TU PLAN</h2>
                    <p class="text-gym-cream/60 mt-4 max-w-2xl mx-auto font-modern">
                        Planes diseñados para cada objetivo. Sin matrículas ocultas, sin compromisos de largo plazo.
                    </p>
                </div>
                
                <!-- Pricing Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    @foreach($planes as $index => $plan)
                        <div class="animate-on-scroll {{ $plan['destacado'] ? 'md:-mt-4 md:mb-4' : '' }}" style="animation-delay: {{ $index * 0.15 }}s">
                            <div class="h-full bg-gym-navy/50 border {{ $plan['destacado'] ? 'border-gym-purple' : 'border-gym-cream/10' }} rounded-2xl p-8 relative card-hover {{ $plan['destacado'] ? 'ring-2 ring-gym-purple/30' : '' }}">
                                @if($plan['destacado'])
                                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                        <span class="bg-gradient-to-r from-gym-purple to-gym-purple-dark text-white text-sm font-bold px-4 py-1 rounded-full font-modern">
                                            MÁS POPULAR
                                        </span>
                                    </div>
                                @endif
                                
                                <div class="text-center mb-8">
                                    <h3 class="font-display text-2xl mb-2 text-gym-cream">{{ $plan['nombre'] }}</h3>
                                    <div class="flex items-baseline justify-center">
                                        <span class="text-4xl font-bold text-gym-purple-light">${{ number_format($plan['precio'], 0, ',', '.') }}</span>
                                        <span class="text-gym-cream/60 ml-2 font-modern">/{{ $plan['periodo'] }}</span>
                                    </div>
                                </div>
                                
                                <ul class="space-y-4 mb-8">
                                    @foreach($plan['caracteristicas'] as $caracteristica)
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-gym-purple-light mt-1 mr-3"></i>
                                            <span class="text-gym-cream/80 font-modern text-sm">{{ $caracteristica }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                
                                <a href="#contacto" class="block w-full text-center py-4 rounded-lg font-semibold transition-all font-modern {{ $plan['destacado'] ? 'bg-gradient-to-r from-gym-purple to-gym-purple-dark hover:from-gym-purple-dark hover:to-gym-purple text-white btn-glow' : 'border border-gym-purple text-gym-purple-light hover:bg-gym-purple/10' }}">
                                    Elegir Plan
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Guarantee Badge -->
                <div class="text-center mt-12 animate-on-scroll">
                    <div class="inline-flex items-center bg-gym-purple/10 border border-gym-purple/30 rounded-full px-6 py-3">
                        <i class="fas fa-shield-alt text-gym-purple-light mr-3"></i>
                        <span class="text-gym-cream/80 font-modern text-sm">Garantía de satisfacción de 7 días o te devolvemos tu dinero</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== TESTIMONIOS SECTION ===== -->
        <section id="testimonios" class="py-24 bg-gym-navy relative overflow-hidden">
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-20 left-20 text-gym-purple text-9xl font-display">"</div>
                <div class="absolute bottom-20 right-20 text-gym-purple text-9xl font-display rotate-180">"</div>
            </div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-16 animate-on-scroll">
                    <span class="text-gym-purple-light font-modern tracking-widest uppercase text-sm">Historias de éxito</span>
                    <h2 class="font-display text-4xl md:text-5xl mt-4 text-gym-cream">LO QUE DICEN</h2>
                    <p class="text-gym-cream/60 mt-4 max-w-2xl mx-auto font-modern">
                        Miles de personas ya transformaron sus vidas. Esta es su experiencia.
                    </p>
                </div>
                
                <!-- Testimonials Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($testimonios as $index => $testimonio)
                        <div class="animate-on-scroll bg-gym-navy-dark/50 border border-gym-cream/10 rounded-2xl p-8 card-hover" style="animation-delay: {{ $index * 0.1 }}s">
                            <!-- Stars -->
                            <div class="flex mb-4">
                                @for($i = 0; $i < $testimonio['rating']; $i++)
                                    <i class="fas fa-star text-gym-gold"></i>
                                @endfor
                            </div>
                            
                            <p class="text-gym-cream/80 mb-6 italic font-modern">"{{ $testimonio['texto'] }}"</p>
                            
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-gym-purple/30 to-gym-purple-dark/30 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-user text-gym-purple-light"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gym-cream">{{ $testimonio['nombre'] }}</div>
                                    <div class="text-gym-cream/50 text-sm font-modern">Miembro verificado</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ===== CTA SECTION ===== -->
        <section class="py-24 bg-gradient-to-r from-gym-purple-dark via-gym-purple to-gym-purple-dark relative overflow-hidden">
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
                <a href="#contacto" class="inline-block bg-gym-navy-dark hover:bg-gym-navy text-gym-cream font-bold px-12 py-5 rounded-lg text-lg transition-all hover:scale-105 font-modern">
                    <i class="fas fa-bolt mr-2"></i>
                    Empezar Hoy
                </a>
            </div>
        </section>

        <!-- ===== CONTACTO SECTION ===== -->
        <section id="contacto" class="py-24 bg-gym-navy-dark relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                    <!-- Contact Info -->
                    <div class="animate-on-scroll">
                        <span class="text-gym-purple-light font-modern tracking-widest uppercase text-sm">Contáctanos</span>
                        <h2 class="font-display text-4xl md:text-5xl mt-4 mb-6 text-gym-cream">HABLEMOS</h2>
                        <p class="text-gym-cream/60 font-modern mb-10">
                            ¿Tienes dudas? ¿Quieres conocer nuestras instalaciones? 
                            Contáctanos y te ayudaremos a dar el primer paso.
                        </p>
                        
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="w-14 h-14 bg-gradient-to-br from-gym-purple/20 to-gym-purple-dark/20 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-gym-purple-light text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1 text-gym-cream">Dirección</h4>
                                    <p class="text-gym-cream/60 font-modern text-sm">Av. Principal #1234, Santiago, Chile</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-14 h-14 bg-gradient-to-br from-gym-purple/20 to-gym-purple-dark/20 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-phone-alt text-gym-purple-light text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1 text-gym-cream">Teléfono</h4>
                                    <p class="text-gym-cream/60 font-modern text-sm">+56 9 1234 5678</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-14 h-14 bg-gradient-to-br from-gym-purple/20 to-gym-purple-dark/20 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-envelope text-gym-purple-light text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1 text-gym-cream">Email</h4>
                                    <p class="text-gym-cream/60 font-modern text-sm">contacto@estoicosgym.cl</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-14 h-14 bg-gradient-to-br from-gym-purple/20 to-gym-purple-dark/20 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-clock text-gym-purple-light text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-1 text-gym-cream">Horario</h4>
                                    <p class="text-gym-cream/60 font-modern text-sm">Lun - Vie: 6:00 - 23:00</p>
                                    <p class="text-gym-cream/60 font-modern text-sm">Sáb - Dom: 8:00 - 20:00</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Social Links -->
                        <div class="mt-10">
                            <h4 class="font-semibold mb-4 text-gym-cream">Síguenos</h4>
                            <div class="flex space-x-4">
                                <a href="#" class="w-12 h-12 bg-gym-navy hover:bg-gym-purple/20 border border-gym-cream/10 hover:border-gym-purple/30 rounded-xl flex items-center justify-center transition-all text-gym-cream hover:text-gym-purple-light" aria-label="Instagram">
                                    <i class="fab fa-instagram text-xl"></i>
                                </a>
                                <a href="#" class="w-12 h-12 bg-gym-navy hover:bg-gym-purple/20 border border-gym-cream/10 hover:border-gym-purple/30 rounded-xl flex items-center justify-center transition-all text-gym-cream hover:text-gym-purple-light" aria-label="Facebook">
                                    <i class="fab fa-facebook-f text-xl"></i>
                                </a>
                                <a href="#" class="w-12 h-12 bg-gym-navy hover:bg-gym-purple/20 border border-gym-cream/10 hover:border-gym-purple/30 rounded-xl flex items-center justify-center transition-all text-gym-cream hover:text-gym-purple-light" aria-label="YouTube">
                                    <i class="fab fa-youtube text-xl"></i>
                                </a>
                                <a href="#" class="w-12 h-12 bg-gym-navy hover:bg-gym-purple/20 border border-gym-cream/10 hover:border-gym-purple/30 rounded-xl flex items-center justify-center transition-all text-gym-cream hover:text-gym-purple-light" aria-label="TikTok">
                                    <i class="fab fa-tiktok text-xl"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="animate-on-scroll">
                        <div class="bg-gym-navy/50 border border-gym-cream/10 rounded-2xl p-8 md:p-10">
                            <h3 class="font-display text-2xl mb-6 text-gym-cream">ENVÍANOS UN MENSAJE</h3>
                            
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
                                        <label for="nombre" class="block text-sm font-medium mb-2 text-gym-cream font-modern">Nombre *</label>
                                        <input 
                                            type="text" 
                                            name="nombre" 
                                            id="nombre" 
                                            value="{{ old('nombre') }}"
                                            required
                                            minlength="2"
                                            maxlength="100"
                                            class="w-full bg-gym-navy-dark border border-gym-cream/20 focus:border-gym-purple rounded-lg px-4 py-3 text-gym-cream placeholder-gym-cream/30 transition-colors focus:outline-none focus:ring-2 focus:ring-gym-purple/20 font-modern"
                                            placeholder="Tu nombre"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label for="email" class="block text-sm font-medium mb-2 text-gym-cream font-modern">Email *</label>
                                        <input 
                                            type="email" 
                                            name="email" 
                                            id="email" 
                                            value="{{ old('email') }}"
                                            required
                                            maxlength="255"
                                            class="w-full bg-gym-navy-dark border border-gym-cream/20 focus:border-gym-purple rounded-lg px-4 py-3 text-gym-cream placeholder-gym-cream/30 transition-colors focus:outline-none focus:ring-2 focus:ring-gym-purple/20 font-modern"
                                            placeholder="tu@email.com"
                                        >
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="telefono" class="block text-sm font-medium mb-2 text-gym-cream font-modern">Teléfono</label>
                                        <input 
                                            type="tel" 
                                            name="telefono" 
                                            id="telefono" 
                                            value="{{ old('telefono') }}"
                                            maxlength="20"
                                            class="w-full bg-gym-navy-dark border border-gym-cream/20 focus:border-gym-purple rounded-lg px-4 py-3 text-gym-cream placeholder-gym-cream/30 transition-colors focus:outline-none focus:ring-2 focus:ring-gym-purple/20 font-modern"
                                            placeholder="+56 9 1234 5678"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label for="servicio" class="block text-sm font-medium mb-2 text-gym-cream font-modern">Interés</label>
                                        <select 
                                            name="servicio" 
                                            id="servicio"
                                            class="w-full bg-gym-navy-dark border border-gym-cream/20 focus:border-gym-purple rounded-lg px-4 py-3 text-gym-cream transition-colors focus:outline-none focus:ring-2 focus:ring-gym-purple/20 font-modern"
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
                                    <label for="mensaje" class="block text-sm font-medium mb-2 text-gym-cream font-modern">Mensaje *</label>
                                    <textarea 
                                        name="mensaje" 
                                        id="mensaje" 
                                        rows="5"
                                        required
                                        minlength="10"
                                        maxlength="1000"
                                        class="w-full bg-gym-navy-dark border border-gym-cream/20 focus:border-gym-purple rounded-lg px-4 py-3 text-gym-cream placeholder-gym-cream/30 transition-colors focus:outline-none focus:ring-2 focus:ring-gym-purple/20 resize-none font-modern"
                                        placeholder="¿En qué podemos ayudarte?"
                                    >{{ old('mensaje') }}</textarea>
                                </div>
                                
                                <button 
                                    type="submit" 
                                    class="w-full bg-gradient-to-r from-gym-purple to-gym-purple-dark hover:from-gym-purple-dark hover:to-gym-purple text-white font-bold py-4 rounded-lg transition-all btn-glow flex items-center justify-center font-modern"
                                >
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Enviar Mensaje
                                </button>
                                
                                <p class="text-gym-cream/40 text-sm text-center font-modern">
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
        <footer class="bg-gym-navy border-t border-gym-cream/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                    <!-- Brand -->
                    <div class="md:col-span-2">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-gym-purple to-gym-purple-dark rounded-lg flex items-center justify-center">
                                <i class="fas fa-dumbbell text-gym-cream text-xl"></i>
                            </div>
                            <span class="font-display text-2xl tracking-widest text-gym-cream">ESTOICOS</span>
                        </div>
                        <p class="text-gym-cream/50 max-w-md font-modern text-sm">
                            Somos más que un gimnasio. Somos una comunidad comprometida con tu transformación física y mental. 
                            Únete a los estoicos y descubre tu verdadero potencial.
                        </p>
                    </div>
                    
                    <!-- Quick Links -->
                    <div>
                        <h4 class="font-semibold mb-4 text-gym-cream">Enlaces Rápidos</h4>
                        <ul class="space-y-2 font-modern text-sm">
                            <li><a href="#inicio" class="text-gym-cream/50 hover:text-gym-purple-light transition-colors">Inicio</a></li>
                            <li><a href="#servicios" class="text-gym-cream/50 hover:text-gym-purple-light transition-colors">Servicios</a></li>
                            <li><a href="#planes" class="text-gym-cream/50 hover:text-gym-purple-light transition-colors">Planes</a></li>
                            <li><a href="#contacto" class="text-gym-cream/50 hover:text-gym-purple-light transition-colors">Contacto</a></li>
                            <li><a href="{{ route('login') }}" class="text-gym-cream/50 hover:text-gym-purple-light transition-colors">Acceder</a></li>
                        </ul>
                    </div>
                    
                    <!-- Legal -->
                    <div>
                        <h4 class="font-semibold mb-4 text-gym-cream">Legal</h4>
                        <ul class="space-y-2 font-modern text-sm">
                            <li><a href="#" class="text-gym-cream/50 hover:text-gym-purple-light transition-colors">Términos de servicio</a></li>
                            <li><a href="#" class="text-gym-cream/50 hover:text-gym-purple-light transition-colors">Política de privacidad</a></li>
                            <li><a href="#" class="text-gym-cream/50 hover:text-gym-purple-light transition-colors">Política de cookies</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-gym-cream/5 mt-12 pt-8 flex flex-col md:flex-row items-center justify-between">
                    <p class="text-gym-cream/40 text-sm font-modern">
                        &copy; {{ date('Y') }} Estoicos Gym. Todos los derechos reservados.
                    </p>
                    <p class="text-gym-cream/40 text-sm mt-2 md:mt-0 font-modern">
                        Hecho con <i class="fas fa-heart text-gym-purple"></i> en Chile
                    </p>
                </div>
            </div>
        </footer>
    </main>
@endsection
