<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO: el nombre sale de Configuracion -> El gimnasio -->
    @php($nombreGimnasio = $gimnasio['nombre'] ?? 'PRO GYM')
    <title>@yield('title', $nombreGimnasio . ' — Profesionales del deporte')</title>
    <meta name="description" content="@yield('description', 'Gimnasio ' . $nombreGimnasio . '. Revisa los planes y consulta tu membresía en línea.')">
    <meta name="keywords" content="gimnasio, fitness, musculación, cardio, entrenamiento">
    <meta name="author" content="{{ $nombreGimnasio }}">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', $nombreGimnasio)">
    <meta property="og:description" content="@yield('description', 'Gimnasio ' . $nombreGimnasio . '.')">
    <meta property="og:image" content="{{ asset('images/progym-logo.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', $nombreGimnasio)">
    <meta property="twitter:description" content="@yield('description', 'Gimnasio ' . $nombreGimnasio . '.')">

    <!-- Favicon: el isotipo del logotipo -->
    <link rel="icon" type="image/png" href="{{ asset('images/progym-isotipo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/progym-isotipo.png') }}">

    <!-- Una sola direccion para esta pagina: evita que Google la cuente dos veces -->
    <link rel="canonical" href="{{ $web['canonical'] ?? url('/') }}">
    @if(!empty($web['search_console']))
        <meta name="google-site-verification" content="{{ $web['search_console'] }}">
    @endif

    <!-- La ficha que lee Google: sale de los planes y de Configuracion. JSON_HEX_TAG impide cerrar el <script> desde un ajuste. -->
    @if(!empty($web['json_ld']))
        <script type="application/ld+json">{!! json_encode($web['json_ld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endif

    @if(!empty($web['google_analytics']))
        <!-- Google Analytics con el consentimiento DENEGADO por defecto: hasta que la persona acepta, no guarda cookies. -->
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('consent', 'default', {
                ad_storage: 'denied',
                ad_user_data: 'denied',
                ad_personalization: 'denied',
                analytics_storage: 'denied',
                wait_for_update: 500
            });
            try {
                if (localStorage.getItem('pg-analitica') === 'si') {
                    gtag('consent', 'update', { analytics_storage: 'granted' });
                }
            } catch (e) {}
            gtag('js', new Date());
            gtag('config', @json($web['google_analytics']));
        </script>
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $web['google_analytics'] }}"></script>
    @endif

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Preconnect para performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fuentes: las mismas de las pantallas de acceso -->
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Paleta PRO GYM: los mismos valores que el panel (tema oscuro)
                        'pg-rojo': '#dd2a32',
                        'pg-rojo-oscuro': '#b81e25',
                        'pg-rojo-claro': '#ef4a51',
                        'pg-tiza': '#f2f2f4',
                        'pg-tiza-clara': '#ffffff',
                        'pg-tiza-oscura': '#c9c9ce',
                        'pg-negro': '#0a0a0b',
                        'pg-carbon': '#121214',
                        'pg-grafito': '#1a1a1e',
                        'pg-gris': '#3d3d44',
                        'pg-plata': '#c7cad1',
                        'pg-acero': '#8b8f98',
                    },
                    fontFamily: {
                        'display': ['Oswald', 'sans-serif'],
                        'body': ['Poppins', 'sans-serif'],
                        'modern': ['Poppins', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <!-- Custom Styles -->
    <style>
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0a0a0b;
        }
        ::-webkit-scrollbar-thumb {
            background: #dd2a32;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #b81e25;
        }
        
        /* Animations */
        .fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .slide-in-left {
            animation: slideInLeft 0.8s ease-out forwards;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Texto cromado, como el «GYM» del logotipo */
        .gradient-text {
            background: linear-gradient(100deg, #ffffff 0%, #c7cad1 45%, #8b8f98 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Boton con brillo rojo */
        .btn-glow {
            box-shadow: 0 0 20px rgba(221, 42, 50, 0.3);
            transition: all 0.3s ease;
        }
        .btn-glow:hover {
            box-shadow: 0 0 40px rgba(221, 42, 50, 0.5);
            transform: translateY(-2px);
        }
        
        /* Card hover */
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        /* Parallax effect */
        .parallax {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        
        /* Hidden honeypot */
        .hp-field {
            position: absolute;
            left: -9999px;
            opacity: 0;
            pointer-events: none;
        }
        

        /* ===== Movimiento: le da vida a la pagina. Se apaga si el equipo pide reducirlo. ===== */

        /* Cinta de logos de convenios: pasa sola y se detiene con el mouse. */
        .cinta {
            overflow: hidden;
            -webkit-mask-image: linear-gradient(to right, transparent, #000 6%, #000 94%, transparent);
            mask-image: linear-gradient(to right, transparent, #000 6%, #000 94%, transparent);
        }
        .cinta-pista { display: flex; width: max-content; animation: cinta linear infinite; }
        .cinta:hover .cinta-pista { animation-play-state: paused; }
        .cinta-logo {
            flex: 0 0 auto;
            width: 15rem;
            height: 8.5rem;
            margin-right: 1.25rem;
            padding: 1.1rem 1.4rem;
            background: #fff;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 30px -12px rgba(0, 0, 0, 0.7);
            transition: transform 0.3s ease;
        }
        .cinta-logo:hover { transform: translateY(-4px) scale(1.04); }
        .cinta-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .cinta-logo span { color: #1f2937; font-weight: 600; font-family: 'Poppins', sans-serif; font-size: 1.05rem; text-align: center; }
        @keyframes cinta { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* Con uno o dos logos no hay cinta: quedan quietos y centrados. */
        .cinta-quieta { -webkit-mask-image: none; mask-image: none; }
        .cinta-quieta .cinta-pista { animation: none; flex-wrap: wrap; justify-content: center; width: auto; }
        .cinta-quieta .cinta-logo { margin: 0.625rem; }

        /* Las fichas de los convenios flotan, cada una a su tiempo. */
        .flotar { animation: flotar 6s ease-in-out infinite; }
        @keyframes flotar { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }

        /* El titulo plateado de la portada: un brillo que recorre las letras. */
        .brillo-texto {
            background-image: linear-gradient(100deg, #8b8f98 0%, #ffffff 25%, #c7cad1 50%, #ffffff 75%, #8b8f98 100%);
            background-size: 200% auto;
            animation: brillo-texto 6s linear infinite;
        }
        @keyframes brillo-texto { to { background-position: 200% center; } }

        /* Los resplandores de fondo respiran. */
        .brillo { animation: latido 8s ease-in-out infinite; }
        @keyframes latido { 0%, 100% { transform: scale(1); opacity: 0.7; } 50% { transform: scale(1.18); opacity: 1; } }

        /* La foto de portada se acerca muy despacio. */
        .portada-foto { animation: acercar 24s ease-in-out infinite alternate; transform-origin: center; }
        @keyframes acercar { from { transform: scale(1); } to { transform: scale(1.12); } }

        /* Franjas que avanzan en la banda roja. */
        .franjas {
            background-image: repeating-linear-gradient(45deg, rgba(0, 0, 0, 0.35) 0 2px, transparent 2px 24px);
            animation: franjas 20s linear infinite;
        }
        @keyframes franjas { to { background-position: 480px 0; } }

        /* El boton de WhatsApp late. */
        .pulso::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            background: #25D366;
            z-index: -1;
            animation: pulso 2.4s ease-out infinite;
        }
        @keyframes pulso { 0% { transform: scale(1); opacity: 0.55; } 100% { transform: scale(1.8); opacity: 0; } }

        @media (prefers-reduced-motion: reduce) {
            .cinta-pista, .brillo-texto, .brillo, .portada-foto, .franjas, .flotar, .pulso::before, .animate-bounce, .fade-in { animation: none !important; }
            .cinta { -webkit-mask-image: none; mask-image: none; }
            .cinta-pista { flex-wrap: wrap; justify-content: center; width: auto; }
            .cinta-logo { margin: 0.625rem; }
            .cinta-logo[data-repetido] { display: none; }
        }
    </style>
    
    @yield('styles')
</head>
<body class="bg-pg-negro text-pg-tiza font-body antialiased">
    <!-- Skip to content (Accessibility) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-pg-rojo text-white px-4 py-2 rounded z-50">
        Saltar al contenido principal
    </a>

    {{-- Menu, contenido, pie y el WhatsApp flotante: iguales en todas las paginas. --}}
    @include('landing.partes.menu')
    <main id="main-content">
        @yield('content')
    </main>
    @include('landing.partes.pie')
    @include('landing.partes.whatsapp')

    <!-- Scripts -->
    <script>
        // CSRF Token para AJAX
        window.csrfToken = '{{ csrf_token() }}';
        
        // Intersection Observer para animaciones on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-visible');
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        // Al terminar de entrar se sueltan los estilos en linea: si se
                        // quedan, pisan el efecto de pasar el mouse por las tarjetas.
                        const el = entry.target;
                        const soltar = (e) => {
                            if (e.target !== el) {
                                return;
                            }
                            el.removeEventListener('transitionend', soltar);
                            el.style.transition = '';
                            el.style.transform = '';
                            el.style.opacity = '';
                        };
                        el.addEventListener('transitionend', soltar);
                        observer.unobserve(el);
                    }
                });
            }, observerOptions);
            
            // Quien pidio menos movimiento ve todo de una vez.
            const reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                if (reducir) {
                    return;
                }
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                // El retraso escrito en cada tarjeta hace que entren de a una.
                const retraso = el.style.animationDelay || '0s';
                el.style.transition = `opacity 0.6s ease ${retraso}, transform 0.6s ease ${retraso}`;
                observer.observe(el);
            });
            
            // Navbar scroll effect
            const navbar = document.getElementById('navbar');
            if (navbar) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 50) {
                        navbar.classList.add('bg-pg-negro/95', 'backdrop-blur-md', 'shadow-lg');
                    } else {
                        navbar.classList.remove('bg-pg-negro/95', 'backdrop-blur-md', 'shadow-lg');
                    }
                });
            }
            
            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        // Close mobile menu if open
                        if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                            mobileMenu.classList.add('hidden');
                        }
                    }
                });
            });
        });
    </script>
    
    <!-- Eventos para Analytics. Sin ID configurado no hacen nada. -->
    <script>
        window.pgEvento = function (nombre, datos) {
            if (typeof window.gtag === 'function') {
                window.gtag('event', nombre, datos || {});
            }
        };
        document.addEventListener('click', function (e) {
            var el = e.target.closest ? e.target.closest('[data-evento]') : null;
            if (el) {
                var datos = {};
                if (el.getAttribute('data-plan')) { datos.plan = el.getAttribute('data-plan'); }
                if (el.getAttribute('data-detalle')) { datos.detalle = el.getAttribute('data-detalle'); }
                window.pgEvento(el.getAttribute('data-evento'), datos);
            }
        });
    </script>

    @if(!empty($web['google_analytics']))
        <!-- Aviso de cookies: aparece hasta que la persona elige, y su eleccion se respeta. -->
        <div id="aviso-cookies" class="hidden fixed bottom-0 inset-x-0 z-50 p-4">
            <div class="max-w-3xl mx-auto bg-pg-carbon border border-pg-tiza/10 rounded-xl shadow-2xl p-4 flex flex-col sm:flex-row items-center gap-4">
                <p class="text-pg-tiza/80 text-sm font-modern flex-1">
                    Usamos cookies de analítica para saber qué partes de la página sirven. No las usamos para publicidad. <a href="{{ route('landing.privacidad') }}" class="underline hover:text-pg-tiza">Más información</a>
                </p>
                <div class="flex gap-2 shrink-0">
                    <button type="button" data-cookies="no" class="px-4 py-2 rounded-lg border border-pg-tiza/20 text-pg-tiza/80 hover:text-pg-tiza text-sm font-modern">No, gracias</button>
                    <button type="button" data-cookies="si" class="px-4 py-2 rounded-lg bg-pg-rojo hover:bg-pg-rojo-oscuro text-white text-sm font-semibold font-modern">Aceptar</button>
                </div>
            </div>
        </div>
        <script>
            (function () {
                var aviso = document.getElementById('aviso-cookies');
                var elegido = null;
                try { elegido = localStorage.getItem('pg-analitica'); } catch (e) {}
                if (elegido !== 'si' && elegido !== 'no') {
                    aviso.classList.remove('hidden');
                }
                aviso.addEventListener('click', function (e) {
                    var opcion = e.target.getAttribute('data-cookies');
                    if (!opcion) {
                        return;
                    }
                    try { localStorage.setItem('pg-analitica', opcion); } catch (e2) {}
                    if (opcion === 'si' && typeof window.gtag === 'function') {
                        window.gtag('consent', 'update', { analytics_storage: 'granted' });
                    }
                    aviso.classList.add('hidden');
                });
            })();
        </script>
    @endif

    @yield('scripts')
</body>
</html>
