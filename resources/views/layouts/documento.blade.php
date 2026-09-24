{{--
    Páginas de un documento suelto: el contrato para firmar, su copia firmada
    y la copia que se imprime desde el panel.

    SIN nada de la web de por medio: ni analítica, ni WhatsApp flotante, ni
    menú. El enlace de un contrato es la llave de un documento con datos
    personales: esta página no sale en Google y no le pasa su dirección a
    nadie, tampoco a las fuentes de Google.
--}}
@php($gimnasio = $gimnasio ?? \App\Support\TextosLegales::datosDelGimnasio())
<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0a0b">
    <title>@yield('titulo') | {{ $gimnasio['gimnasio'] }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/progym-isotipo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    @vite('resources/css/landing.css')
</head>
<body class="min-h-screen bg-pg-negro font-modern text-pg-tiza antialiased print:bg-white print:text-black">
    <header class="border-b border-pg-tiza/10 print:hidden">
        <div class="mx-auto flex max-w-3xl items-center justify-between gap-4 px-4 py-4">
            <picture>
                <source srcset="{{ asset('images/progym-logo-320.webp') }}" type="image/webp">
                <img src="{{ asset('images/progym-logo-320.png') }}" alt="{{ $gimnasio['gimnasio'] }}" width="1096" height="495" class="h-10 w-auto">
            </picture>
            <span class="text-right text-[11px] uppercase tracking-[0.25em] text-pg-acero">@yield('etiqueta')</span>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-3 py-6 sm:px-6 sm:py-10 print:max-w-none print:p-0">
        @yield('contenido')
    </main>

    <footer class="border-t border-pg-tiza/10 print:hidden">
        {{-- Solo lo que está: en el pie, un «(por completar)» no le sirve a nadie. --}}
        <div class="mx-auto max-w-3xl px-4 py-6 text-center text-xs leading-relaxed text-pg-tiza/45">
            {{ collect([$gimnasio['gimnasio'], $gimnasio['direccion_gimnasio'], $gimnasio['email_gimnasio']])
                ->reject(fn ($dato) => $dato === \App\Support\TextosLegales::POR_COMPLETAR)
                ->implode(' · ') }}
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
