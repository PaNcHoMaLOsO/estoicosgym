<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title>@yield('title', 'Estoicos Gym - Transforma tu cuerpo, fortalece tu mente')</title>
    <meta name="description" content="@yield('description', 'Estoicos Gym es el gimnasio líder en entrenamiento de alto rendimiento. Equipos de última generación, entrenadores certificados y planes personalizados.')">
    <meta name="keywords" content="gimnasio, fitness, musculación, entrenamiento, CrossFit, personal trainer, nutrición deportiva">
    <meta name="author" content="Estoicos Gym">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Estoicos Gym - Transforma tu cuerpo')">
    <meta property="og:description" content="@yield('description', 'El gimnasio líder en entrenamiento de alto rendimiento.')">
    <meta property="og:image" content="{{ asset('images/landing/og-image.jpg') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', 'Estoicos Gym')">
    <meta property="twitter:description" content="@yield('description', 'El gimnasio líder en entrenamiento.')">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Preconnect para performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fonts (mismas que login) -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800;900&family=Philosopher:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Paleta Estoicos Gym (igual que login)
                        'gym-purple': '#c140d4',
                        'gym-purple-dark': '#9a32a8',
                        'gym-purple-light': '#d466e3',
                        'gym-cream': '#f1e6bf',
                        'gym-cream-light': '#f7f0d8',
                        'gym-cream-dark': '#d4c99a',
                        'gym-navy': '#253a5b',
                        'gym-navy-dark': '#1a2940',
                        'gym-navy-light': '#344d6e',
                        'gym-gray': '#434750',
                        'gym-gold': '#d4af37',
                        'gym-bronze': '#cd7f32',
                    },
                    fontFamily: {
                        'display': ['Cinzel', 'serif'],
                        'body': ['Philosopher', 'serif'],
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
            background: #1a2940;
        }
        ::-webkit-scrollbar-thumb {
            background: #c140d4;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9a32a8;
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
        
        /* Gradient text - Purple to Gold */
        .gradient-text {
            background: linear-gradient(135deg, #c140d4, #d4af37, #c140d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Glowing button - Purple */
        .btn-glow {
            box-shadow: 0 0 20px rgba(193, 64, 212, 0.3);
            transition: all 0.3s ease;
        }
        .btn-glow:hover {
            box-shadow: 0 0 40px rgba(193, 64, 212, 0.5);
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
        
        /* Star twinkle effect */
        @keyframes twinkle {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }
        
        .star {
            animation: twinkle 2s ease-in-out infinite;
        }
    </style>
    
    @yield('styles')
</head>
<body class="bg-gym-navy-dark text-gym-cream font-body antialiased">
    <!-- Skip to content (Accessibility) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-gym-purple text-white px-4 py-2 rounded z-50">
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
                        navbar.classList.add('bg-gym-darker/95', 'backdrop-blur-md', 'shadow-lg');
                    } else {
                        navbar.classList.remove('bg-gym-darker/95', 'backdrop-blur-md', 'shadow-lg');
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
