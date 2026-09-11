<!DOCTYPE html>
<html lang="es">
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
        
    </style>
    
    @yield('styles')
</head>
<body class="bg-pg-negro text-pg-tiza font-body antialiased">
    <!-- Skip to content (Accessibility) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-pg-rojo text-white px-4 py-2 rounded z-50">
        Saltar al contenido principal
    </a>

    @yield('content')

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
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
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
    
    @yield('scripts')
</body>
</html>
