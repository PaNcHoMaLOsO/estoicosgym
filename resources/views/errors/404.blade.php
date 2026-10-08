{{--
    La página que no existe, con la cara de la web.

    SUELTA A PROPÓSITO: se pinta también cuando la dirección no calza con
    ninguna ruta, y ahí no hay sesión ni controlador ni nada de lo que usa el
    diseño de la web. Lo poco que necesita lo lee aquí, y si la base de datos
    falla, sale igual con lo de siempre.

    Fuera de Google (noindex) y con el camino de vuelta a lo que más se busca.
--}}
@php
    $nombre = rescue(fn () => \App\Support\Ajustes::obtener('gimnasio.nombre'), null, false) ?: 'PRO GYM';
    $ciudad = trim((string) rescue(fn () => \App\Support\Ajustes::obtener('web.ciudad'), '', false));
    $hayClases = (bool) rescue(fn () => \App\Models\Clase::where('activo', true)->exists(), false, false);
    $caminos = array_values(array_filter([
        ['ruta' => 'landing', 'texto' => 'Inicio'],
        ['ruta' => 'landing.planes', 'texto' => 'Planes'],
        $hayClases ? ['ruta' => 'landing.clases', 'texto' => 'Clases'] : null,
        ['ruta' => 'landing.contacto', 'texto' => 'Contacto'],
    ]));
    $fuentes = 'https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap';
@endphp
<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada | {{ $nombre }}{{ $ciudad ? ' ' . $ciudad : '' }}</title>
    <meta name="robots" content="noindex, follow">
    <meta name="theme-color" content="#0a0a0b">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ $fuentes }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ $fuentes }}"></noscript>
    @vite('resources/css/landing.css')
</head>
<body class="bg-pg-negro text-pg-tiza font-body antialiased">
    <main class="min-h-svh flex flex-col items-center justify-center px-5 py-12 text-center">
        <a href="{{ route('landing') }}" aria-label="{{ $nombre }}, ir al inicio">
            <picture>
                <source srcset="{{ asset('images/progym-logo-320.webp') }}" type="image/webp">
                <img src="{{ asset('images/progym-logo-320.png') }}" alt="{{ $nombre }}" width="1096" height="495" class="h-12 w-auto">
            </picture>
        </a>

        <p class="mt-10 font-display text-7xl text-pg-rojo leading-none">404</p>
        <h1 class="mt-4 font-display text-3xl uppercase text-pg-tiza">Esta página no existe</h1>
        <p class="mt-3 max-w-sm font-modern text-sm text-pg-tiza/60">Puede que el enlace esté viejo o mal escrito.</p>

        <nav class="mt-8 grid w-full max-w-sm grid-cols-2 gap-3" aria-label="Páginas">
            @foreach($caminos as $i => $c)
                <a href="{{ route($c['ruta']) }}"
                   class="{{ $i === 0 ? 'bg-pg-rojo hover:bg-pg-rojo-oscuro text-white' : 'border border-pg-tiza/20 hover:border-pg-rojo text-pg-tiza' }} inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 font-modern text-sm font-semibold transition-colors">
                    {{ $c['texto'] }}
                </a>
            @endforeach
        </nav>
    </main>
</body>
</html>
