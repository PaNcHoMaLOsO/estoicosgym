<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO: el nombre sale de Configuracion -> El gimnasio -->
    @php($nombreGimnasio = $gimnasio['nombre'] ?? 'PRO GYM')
    <title>@yield('title', $nombreGimnasio . ' | Profesionales del deporte')</title>
    <meta name="description" content="@yield('description', 'Gimnasio ' . $nombreGimnasio . '. Revisa los planes y consulta tu membresía en línea.')">
    <meta name="keywords" content="gimnasio, fitness, musculación, cardio, entrenamiento">
    <meta name="author" content="{{ $nombreGimnasio }}">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="theme-color" content="#0a0a0b">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', $nombreGimnasio)">
    <meta property="og:description" content="@yield('description', 'Gimnasio ' . $nombreGimnasio . '.')">
    {{-- Al compartir la página sale la primera foto del gimnasio; sin fotos, el logo. --}}
    <meta property="og:image" content="{{ $web['imagen'] ?? asset('images/progym-logo.png') }}">
    <meta property="og:image:alt" content="{{ $nombreGimnasio }}{{ !empty($web['ciudad']) ? ', gimnasio en ' . $web['ciudad'] : '' }}">
    <meta property="og:site_name" content="{{ $nombreGimnasio }}">
    <meta property="og:locale" content="es_CL">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', $nombreGimnasio)">
    <meta property="twitter:description" content="@yield('description', 'Gimnasio ' . $nombreGimnasio . '.')">
    <meta property="twitter:image" content="{{ $web['imagen'] ?? asset('images/progym-logo.png') }}">

    <!-- Favicon: el isotipo del logotipo -->
    <link rel="icon" type="image/png" href="{{ asset('images/progym-isotipo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/progym-isotipo.png') }}">

    <!-- Una sola direccion para esta pagina: evita que Google la cuente dos veces -->
    <link rel="canonical" href="{{ $web['canonical'] ?? url('/') }}">
    @if(!empty($web['search_console']))
        <meta name="google-site-verification" content="{{ $web['search_console'] }}">
    @endif
    @if(!empty($web['bing']))
        <meta name="msvalidate.01" content="{{ $web['bing'] }}">
    @endif

    <!-- La ficha que lee Google: sale de los planes y de Configuracion. JSON_HEX_TAG impide cerrar el <script> desde un ajuste. -->
    @if(!empty($web['json_ld']))
        <script type="application/ld+json">{!! json_encode($web['json_ld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endif
    {{-- Las migas de cada página: «PRO GYM › Planes y precios». --}}
    @if(!empty($web['migas']))
        <script type="application/ld+json">{!! json_encode($web['migas'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
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
    
    <!-- Los estilos de la web, compilados: ver resources/css/landing.css -->
    @vite('resources/css/landing.css')
    
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
        
        
        /* Card hover */
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25);
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
        /* UNA BARRA BLANCA, NO UNA FILA DE TARJETAS. Cada logo iba en su propio
           recuadro blanco con sombra: seis cajas seguidas pesaban más que los
           logos que llevaban dentro. Ahora el blanco es el fondo de la franja y
           los logos van sueltos encima. */
        .cinta-barra { background: #fff; padding: 1.5rem 0; }
        .cinta-logo {
            flex: 0 0 auto;
            height: 3.25rem;
            min-width: 8rem;
            margin-right: 3.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cinta-logo img { max-width: 100%; max-height: 100%; width: auto; object-fit: contain; }
        .cinta-logo span { color: #1f2937; font-weight: 600; font-family: 'Poppins', sans-serif; font-size: 1.05rem; text-align: center; }
        @keyframes cinta { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* Con uno o dos logos no hay cinta: quedan quietos y centrados. */
        .cinta-quieta { -webkit-mask-image: none; mask-image: none; }
        .cinta-quieta .cinta-pista { animation: none; flex-wrap: wrap; justify-content: center; width: auto; }
        .cinta-quieta .cinta-logo { margin: 0 1.75rem; }

        /* Las fichas de los convenios flotan, cada una a su tiempo. */
        .flotar { animation: flotar 6s ease-in-out infinite; }
        @keyframes flotar { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }



        /* La foto de portada se acerca muy despacio. */
        /* La foto de portada se encuadra por encima del centro. Las fotos del
           gimnasio son verticales y la portada es ancha y baja, asi que el
           recorte centrado de `object-cover` cortaba a la persona por la cabeza. */
        .portada-foto { animation: acercar 24s ease-in-out infinite alternate; transform-origin: center; object-position: 50% 22%; }

        /* EN EL TELEFONO LA FOTO ENTRA ENTERA. Las fotos del gimnasio son
           verticales (1600 x 2000) y la pantalla tambien: van arriba, en un
           hueco con su proporcion, 4:5, y el texto debajo, montado sobre el
           fundido. En pantalla ancha la foto sigue de lado a lado, como fondo:
           se probo a ponerla a la derecha y entera, y la portada quedaba medio
           vacia. */
        @media (max-width: 1023px) {
            .portada-foto, .portada-capa.portada-foto {
                position: absolute; inset: auto; top: 4rem; left: 0; width: 100%; height: auto;
                aspect-ratio: 4 / 5; max-height: 70svh; object-position: 50% 30%;
                -webkit-mask-image: linear-gradient(to bottom, #000 55%, transparent 100%);
                mask-image: linear-gradient(to bottom, #000 55%, transparent 100%);
            }
            section:has(.portada-foto) > .entrada { padding-top: calc(min(125vw, 70svh) - 4rem); }
        }
        .entrada { text-shadow: 0 2px 18px rgba(0, 0, 0, 0.55); }
        .entrada a { text-shadow: none; }

        /* El video de portada NO se acerca: ya se mueve solo, y las dos cosas a
           la vez marean. Va encuadrado al centro, que es como esta grabado. */
        .portada-video { object-position: 50% 50%; }

        /* Las capas del fondo de la portada, una encima de otra. La que entra
           aparece por delante de la que sale, asi que en medio no se ve ni un
           hueco ni un parpadeo negro. Curva suave y sin prisa: un corte rapido
           en un fondo a pantalla completa se nota como un parpadeo. */
        .portada-capa {
            position: absolute;
            inset: 0;
            transition: opacity 1.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @keyframes acercar { from { transform: scale(1); } to { transform: scale(1.05); } }


        /* Los botones flotantes laten.

           EL HALO CRECE POR FUERA, con una sombra. Antes era un circulo hijo con
           z-index negativo, y eso NO se pinta detras del boton: se pinta encima
           de su fondo y debajo de su contenido. Con el de WhatsApp no se notaba
           (halo verde sobre fondo verde), pero el de la tienda es un circulo
           oscuro y el halo morado se lo comia entero, dejando el laurel del
           logotipo, que tambien es morado, sin contraste.

           El color se cambia desde el propio boton: verde el de WhatsApp,
           morado el de la tienda. Una sola animacion para los dos. */
        .pulso {
            animation: pulso 2.4s ease-out infinite;
        }
        /* La sombra de siempre va DENTRO de la animacion: `box-shadow` es una
           sola propiedad, y animarla a secas dejaba los botones planos. */
        @keyframes pulso {
            0% { box-shadow: 0 10px 22px rgba(0, 0, 0, 0.45), 0 0 0 0 var(--color-pulso, #25D366); }
            70% { box-shadow: 0 10px 22px rgba(0, 0, 0, 0.45), 0 0 0 16px transparent; }
            100% { box-shadow: 0 10px 22px rgba(0, 0, 0, 0.45), 0 0 0 0 transparent; }
        }

        /* ===== Entradas, scroll y paso entre paginas ===== */

        /* La portada entra por partes: el rotulo, el titular linea a linea, el
           texto y los botones, uno detras de otro. Todo con la misma curva, que
           frena al final: es lo que hace que se sienta asentado y no rebotado. */
        .entrada > * { opacity: 0; animation: subir 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .entrada > :nth-child(1) { animation-delay: 0.1s; }
        .entrada > :nth-child(2) { animation-delay: 0.2s; }
        .entrada > :nth-child(3) { animation-delay: 0.5s; }
        .entrada > :nth-child(4) { animation-delay: 0.65s; }
        .entrada > :nth-child(n+5) { animation-delay: 0.8s; }
        @keyframes subir { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

        /* El titular de dos lineas no sube entero: cada linea se destapa de
           abajo arriba. El recorte lleva margen de sobra para no cortar tildes. */
        .entrada > h1:has(> span) { opacity: 1; animation: none; }
        .entrada > h1 > span { animation: destapar 1s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: 0.2s; }
        .entrada > h1 > span + span { animation-delay: 0.34s; }
        @keyframes destapar {
            from { opacity: 0; transform: translateY(0.55em); clip-path: inset(-15% 0 100% 0); }
            to { opacity: 1; transform: translateY(0); clip-path: inset(-15% 0 -15% 0); }
        }

        /* El rotulo rojo de cada seccion llega con las letras abiertas y se cierra. */
        .animate-on-scroll.text-center > span.uppercase { display: inline-block; transition: letter-spacing 1.1s cubic-bezier(0.16, 1, 0.3, 1); }
        @media (prefers-reduced-motion: no-preference) {
            .animate-on-scroll.text-center:not(.animate-visible) > span.uppercase { letter-spacing: 0.45em; }
        }

        /* ===== Detalles ===== */

        /* Lo que se selecciona y lo que tiene el foco, en el rojo de la casa. */
        ::selection { background: #dd2a32; color: #fff; }
        :focus-visible { outline: 2px solid #dd2a32; outline-offset: 3px; border-radius: 4px; }

        /* Grano de pelicula sobre la portada: le quita lo plastico a la foto y
           al video. Va por encima del fondo y por debajo del texto. */
        section:has(> .entrada)::after {
            content: ''; position: absolute; inset: 0; z-index: 5; pointer-events: none; opacity: 0.07; mix-blend-mode: overlay;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='g'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23g)'/%3E%3C/svg%3E");
        }

        /* Una raya roja se dibuja delante del rotulo de la portada. */
        .entrada > p:first-child { display: flex; align-items: center; gap: 0.85rem; }
        .entrada > p:first-child::before {
            content: ''; width: 2.5rem; height: 2px; background: #dd2a32; flex: none;
            transform: scaleX(0); transform-origin: 0 50%;
            animation: trazar 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.45s forwards;
        }
        @keyframes trazar { to { transform: scaleX(1); } }

        /* Los iconos de accesos y servicios dan un paso al pasar por encima. */
        .animate-on-scroll h2 > i, .animate-on-scroll h3 > i { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .animate-on-scroll:hover h2 > i, .animate-on-scroll:hover h3 > i { transform: translateY(-3px) scale(1.15); }

        /* Las comillas de los testimonios se asientan al entrar. */
        figure.animate-on-scroll > .fa-quote-left { display: inline-block; transition: transform 1s cubic-bezier(0.16, 1, 0.3, 1) 0.2s; }
        figure.animate-on-scroll:not(.animate-visible) > .fa-quote-left { transform: translateY(-10px) rotate(-12deg); }

        /* Los enlaces del pie se corren un poco a la derecha. */
        footer li > a { display: inline-block; transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), color 0.15s ease; }
        footer li > a:hover { transform: translateX(4px); }

        /* Barra de avance: cuanto de la pagina se lleva leido. */
        .avance {
            position: fixed; top: 0; left: 0; right: 0; height: 2px; z-index: 60;
            background: #dd2a32; transform: scaleX(0); transform-origin: 0 50%; pointer-events: none;
        }

        /* El menu se aparta al bajar y vuelve en cuanto se sube. */
        #navbar { transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.3s ease, box-shadow 0.3s ease; }
        #navbar.recogido { transform: translateY(-100%); }

        /* El menu del telefono: boton con su marco, panel macizo que baja, los
           enlaces entran de a uno y lo de atras se oscurece. */
        #mobile-menu-btn {
            width: 2.75rem; height: 2.75rem; display: inline-flex; align-items: center; justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 0.7rem;
            transition: border-color 0.25s ease, background-color 0.25s ease;
        }
        @media (min-width: 1024px) { #mobile-menu-btn { display: none; } }
        #mobile-menu-btn i { transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1); }
        #navbar.abierto #mobile-menu-btn { border-color: rgba(221, 42, 50, 0.6); background: rgba(221, 42, 50, 0.12); }
        #navbar.abierto #mobile-menu-btn i { transform: rotate(90deg); }
        #navbar.abierto { background-color: #0a0a0b; box-shadow: 0 0 0 100vmax rgba(0, 0, 0, 0.65); }
        #mobile-menu:not(.hidden) { animation: bajar 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        #mobile-menu > div { gap: 0; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 0.5rem; }
        #mobile-menu a:not(.bg-pg-rojo) {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.95rem 0.25rem; border-radius: 0; background: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            font-family: 'Oswald', sans-serif; text-transform: uppercase; letter-spacing: 0.06em; font-size: 1.05rem;
        }
        #mobile-menu a:not(.bg-pg-rojo)::after {
            content: '\f061'; font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: 0.7rem;
            color: #dd2a32; opacity: 0.7;
        }
        #mobile-menu a[aria-current] { color: #fff; box-shadow: inset 3px 0 0 #dd2a32; padding-left: 0.85rem; }
        #mobile-menu a.bg-pg-rojo { margin-top: 1.1rem; }
        #mobile-menu:not(.hidden) a { opacity: 0; animation: subir 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        #mobile-menu a:nth-child(1) { animation-delay: 0.05s; }
        #mobile-menu a:nth-child(2) { animation-delay: 0.1s; }
        #mobile-menu a:nth-child(3) { animation-delay: 0.15s; }
        #mobile-menu a:nth-child(4) { animation-delay: 0.2s; }
        #mobile-menu a:nth-child(5) { animation-delay: 0.25s; }
        #mobile-menu a:nth-child(n+6) { animation-delay: 0.3s; }
        @keyframes bajar { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* La raya roja del menu crece desde la izquierda. */
        #navbar nav a.relative:not([aria-current])::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: -2px; height: 2px; border-radius: 2px;
            background: #dd2a32; transform: scaleX(0); transform-origin: 0 50%;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #navbar nav a.relative:not([aria-current]):hover::after { transform: scaleX(1); }

        /* Los accesos de la portada: una raya roja se dibuja arriba al pasar. */
        #accesos a { position: relative; }
        #accesos a::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: #dd2a32;
            transform: scaleX(0); transform-origin: 0 50%; transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #accesos a:hover::before { transform: scaleX(1); }

        /* Un brillo cruza los botones rojos, y todos ceden un pelo al pulsarlos. */
        a.bg-pg-rojo:not(.sr-only), button.bg-pg-rojo { position: relative; overflow: hidden; }
        a.bg-pg-rojo:not(.sr-only)::after, button.bg-pg-rojo::after {
            content: ''; position: absolute; top: 0; bottom: 0; left: -60%; width: 40%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.28), transparent);
            transform: skewX(-20deg); pointer-events: none;
        }
        a.bg-pg-rojo:not(.sr-only):hover::after, button.bg-pg-rojo:hover::after { left: 130%; transition: left 0.7s ease; }
        main a[class*="rounded-lg"]:active, main button:active { transform: scale(0.97); transition: transform 0.1s ease; }

        /* De una pagina a otra se pasa con un fundido, y el menu no parpadea. */
        @media (prefers-reduced-motion: no-preference) {
            @view-transition { navigation: auto; }
            #navbar { view-transition-name: menu; }
            ::view-transition-old(root), ::view-transition-new(root) { animation-duration: 0.35s; }
        }

        @media (prefers-reduced-motion: reduce) {
            .entrada > *, .entrada > h1 > span { animation: none !important; opacity: 1 !important; }
            .avance { display: none; }
            #mobile-menu, #mobile-menu a { animation: none !important; opacity: 1 !important; }
            .entrada > p:first-child::before { animation: none; transform: none; }
            figure.animate-on-scroll > .fa-quote-left { transform: none !important; }
            #navbar.recogido { transform: none; }
            a.bg-pg-rojo::after, button.bg-pg-rojo::after { display: none; }
            .cinta-pista, .portada-foto, .flotar, .pulso, .animate-bounce, .fade-in { animation: none !important; }
            .cinta { -webkit-mask-image: none; mask-image: none; }
            .cinta-pista { flex-wrap: wrap; justify-content: center; width: auto; }
            .cinta-logo { margin: 0 1.75rem; }
            .cinta-logo[data-repetido] { display: none; }
        }
    </style>
    
    @yield('styles')
</head>
<body class="bg-pg-negro text-pg-tiza font-body antialiased">
    <!-- Skip to content (Accessibility) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-pg-rojo text-white px-4 py-2 rounded-sm z-50">
        Saltar al contenido principal
    </a>

    {{-- Menu, contenido, pie y el WhatsApp flotante: iguales en todas las paginas. --}}
    @include('landing.partes.menu')
    <main id="main-content">
        @yield('content')
    </main>
    @include('landing.partes.pie')
    @include('landing.partes.whatsapp')
    @include('landing.partes.tienda-flotante')

    <!-- Scripts -->
    <script>
        // CSRF Token para AJAX
        window.csrfToken = '{{ csrf_token() }}';
        
        // Intersection Observer para animaciones on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                root: null,
                // Un poco por dentro del borde: que se vea entrar, no que ya este.
                rootMargin: '0px 0px -8% 0px',
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

            // Y el video de portada se queda quieto en su primer fotograma: un
            // fondo que se mueve en bucle es justo lo que esa preferencia pide
            // no ver, y el CSS no puede pararlo.
            if (reducir) {
                document.querySelectorAll('video.portada-video').forEach(video => {
                    video.removeAttribute('autoplay');
                    video.removeAttribute('loop');
                    video.pause();
                });
            }
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                if (reducir) {
                    return;
                }
                el.style.opacity = '0';
                el.style.transform = 'translateY(28px)';
                // El retraso escrito en cada tarjeta hace que entren de a una.
                const retraso = el.style.animationDelay || '0s';
                el.style.transition = `opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1) ${retraso}, transform 0.8s cubic-bezier(0.16, 1, 0.3, 1) ${retraso}`;
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

            // ===== Lo que se mueve con el scroll =====
            // Una sola lectura por fotograma para las tres cosas: la barra de
            // avance, el menu que se aparta y la portada que se queda atras.
            if (!reducir) {
                const avance = document.createElement('div');
                avance.className = 'avance';
                avance.setAttribute('aria-hidden', 'true');
                document.body.appendChild(avance);

                const texto = document.querySelector('.entrada');
                const portada = texto ? texto.closest('section') : null;
                const fondo = portada ? portada.firstElementChild : null;

                let anterior = window.scrollY;
                let pendiente = false;

                // El mouse sobre la portada: el fondo se va al lado CONTRARIO,
                // unos pocos pixeles, y el texto apenas lo sigue. `mx` y `my`
                // persiguen al mouse con retraso, que es lo que lo hace suave.
                let mx = 0, my = 0, destinoX = 0, destinoY = 0, siguiendo = false;
                const pintar = () => {
                    pendiente = false;
                    const y = window.scrollY;
                    const total = document.documentElement.scrollHeight - window.innerHeight;
                    avance.style.transform = `scaleX(${total > 0 ? Math.min(1, y / total) : 0})`;

                    // El menu se va al bajar y vuelve al primer gesto de subir.
                    // Con el menu del telefono abierto no se mueve.
                    if (navbar) {
                        const abierto = mobileMenu && !mobileMenu.classList.contains('hidden');
                        if (y > anterior + 4 && y > 300 && !abierto) {
                            navbar.classList.add('recogido');
                        } else if (y < anterior - 4 || y <= 300) {
                            navbar.classList.remove('recogido');
                        }
                    }
                    anterior = y;

                    // La foto baja mas lento que la pagina y el texto se apaga:
                    // da fondo sin que nada salte. Solo mientras la portada se ve.
                    if (portada && y < portada.offsetHeight) {
                        const alto = portada.offsetHeight;
                        if (fondo && fondo !== texto) {
                            // Un pelo mas grande que su hueco: asi puede moverse
                            // con el mouse sin ensenar el borde.
                            fondo.style.transform = `translate3d(${-mx * 14}px, ${y * 0.22 - my * 8}px, 0) scale(1.04)`;
                        }
                        texto.style.transform = `translate3d(${mx * 4}px, ${y * 0.12 + my * 3}px, 0)`;
                        texto.style.opacity = String(Math.max(0, 1 - y / (alto * 0.9)));
                    }
                };
                const seguir = () => {
                    mx += (destinoX - mx) * 0.06;
                    my += (destinoY - my) * 0.06;
                    pintar();
                    if (Math.abs(destinoX - mx) > 0.001 || Math.abs(destinoY - my) > 0.001) {
                        requestAnimationFrame(seguir);
                    } else {
                        siguiendo = false;
                    }
                };
                const apuntar = (x, y) => {
                    destinoX = x;
                    destinoY = y;
                    if (!siguiendo) {
                        siguiendo = true;
                        requestAnimationFrame(seguir);
                    }
                };
                // Solo con mouse de verdad: en un telefono no hay nada que seguir.
                if (portada && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                    portada.addEventListener('pointermove', (e) => {
                        const caja = portada.getBoundingClientRect();
                        apuntar((e.clientX - caja.left) / caja.width - 0.5, (e.clientY - caja.top) / caja.height - 0.5);
                    });
                    portada.addEventListener('pointerleave', () => apuntar(0, 0));
                }

                window.addEventListener('scroll', () => {
                    if (!pendiente) {
                        pendiente = true;
                        requestAnimationFrame(pintar);
                    }
                }, { passive: true });
                pintar();
            }
            if (mobileMenuBtn && mobileMenu) {
                // Abierto, el menu es un panel macizo: con el fondo traslucido de
                // la barra se leia la pagina de atras por entre los enlaces.
                const icono = mobileMenuBtn.querySelector('i');
                const poner = (abrir) => {
                    mobileMenu.classList.toggle('hidden', !abrir);
                    navbar && navbar.classList.toggle('abierto', abrir);
                    mobileMenuBtn.setAttribute('aria-expanded', abrir ? 'true' : 'false');
                    mobileMenuBtn.setAttribute('aria-label', abrir ? 'Cerrar el menú' : 'Abrir el menú');
                    if (icono) {
                        icono.classList.toggle('fa-bars', !abrir);
                        icono.classList.toggle('fa-xmark', abrir);
                    }
                };
                mobileMenuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    poner(mobileMenu.classList.contains('hidden'));
                });
                // Se cierra tocando fuera o con Escape.
                document.addEventListener('click', (e) => {
                    if (!mobileMenu.classList.contains('hidden') && navbar && !navbar.contains(e.target)) {
                        poner(false);
                    }
                });
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
                        poner(false);
                    }
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
