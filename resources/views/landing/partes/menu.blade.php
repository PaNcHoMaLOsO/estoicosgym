{{--
    El menu de todas las paginas. Solo enlaza lo que está encendido en
    Configuración → Páginas que se ven y, de eso, lo que tiene algo que mostrar:
    sin convenios publicados no hay «Convenios», sin especialistas no hay
    «Especialistas», sin clases no hay «Clases». El boton rojo es lo que
    mas busca un socio: su membresia.
--}}
@php
    // Primero lo de quien viene a entrenar (el lugar, los planes, las clases y
    // los especialistas), después lo de grupos e instituciones, y al final
    // cómo contactar.
    $enlaces = array_values(array_filter([
        $navegacion['gimnasio'] ? ['ruta' => 'landing.gimnasio', 'texto' => 'El gimnasio'] : null,
        $navegacion['planes'] ? ['ruta' => 'landing.planes', 'texto' => 'Planes'] : null,
        $navegacion['clases'] ? ['ruta' => 'landing.clases', 'texto' => 'Clases', 'tambien' => 'landing.clase'] : null,
        $navegacion['rutinas'] ? ['ruta' => 'landing.rutina', 'texto' => 'Rutinas', 'tambien' => ['landing.rutina*', 'landing.ejercicios']] : null,
        $navegacion['especialistas'] ? ['ruta' => 'landing.especialistas', 'texto' => 'Especialistas', 'tambien' => 'landing.especiali*'] : null,
        $navegacion['convenios'] ? ['ruta' => 'landing.convenios', 'texto' => 'Convenios'] : null,
        $navegacion['arriendo'] ? ['ruta' => 'landing.arriendo', 'texto' => 'Arrienda horas'] : null,
        ['ruta' => 'landing.contacto', 'texto' => 'Contacto'],
    ]));
@endphp
<header id="navbar" class="fixed top-0 inset-x-0 z-50 bg-pg-negro/90 backdrop-blur-md border-b border-pg-tiza/5">
    @if($aviso)
        <div class="bg-pg-rojo text-white text-center text-sm font-modern px-4 py-2">
            <x-icono nombre="bullhorn" class="mr-2" />{{ $aviso }}
        </div>
    @endif
    {{-- Solo la ve quien entró al panel: para el público esta página no existe. --}}
    @if($paginaApagada = request()->attributes->get('paginaApagada'))
        <div class="bg-amber-400 text-pg-negro text-center text-sm font-modern font-semibold px-4 py-2">
            <x-icono nombre="lock" class="mr-2" />«{{ $paginaApagada }}» está apagada: solo tú la ves. Se enciende en Configuración → Páginas que se ven.
        </div>
    @endif

    <nav class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20" aria-label="Principal">
        <div class="flex items-center justify-between h-16 lg:h-24">
            <a href="{{ route('landing') }}" class="flex items-center shrink-0" aria-label="{{ $gimnasio['nombre'] }}, ir al inicio">
                <picture>
                    <source srcset="{{ asset('images/progym-logo-320.webp') }}" type="image/webp">
                    <img src="{{ asset('images/progym-logo-320.png') }}" alt="{{ $gimnasio['nombre'] }}" width="1096" height="495" class="h-10 lg:h-14 w-auto">
                </picture>
            </a>

            {{-- Con siete enlaces no caben con el aire de siempre a 1024 px: más juntos
                 hasta la pantalla ancha, y sin partirse en dos líneas. --}}
            <div class="hidden lg:flex items-center gap-4 xl:gap-7">
                @foreach($enlaces as $e)
                    <a href="{{ route($e['ruta']) }}" @if(request()->routeIs($e['ruta'], $e['tambien'] ?? $e['ruta'])) aria-current="page" @endif
                       class="relative whitespace-nowrap font-modern text-sm transition-colors py-2 {{ request()->routeIs($e['ruta'], $e['tambien'] ?? $e['ruta']) ? 'text-pg-tiza after:absolute after:inset-x-0 after:-bottom-0.5 after:h-0.5 after:bg-pg-rojo after:rounded-full' : 'text-pg-tiza/70 hover:text-pg-rojo-claro' }}">{{ $e['texto'] }}</a>
                @endforeach
                @if($navegacion['membresia'])
                    <a href="{{ route('landing.membresia') }}" class="whitespace-nowrap bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-semibold px-4 xl:px-5 py-2.5 rounded-lg transition-colors font-modern text-sm">
                        <x-icono nombre="id-card" class="mr-2" />Mi membresía
                    </a>
                @endif
            </div>

            <button id="mobile-menu-btn" type="button" class="lg:hidden text-pg-tiza p-2" aria-label="Abrir el menú" aria-controls="mobile-menu">
                <x-icono nombre="bars" class="text-xl" />
                <x-icono nombre="xmark" class="text-xl hidden" />
            </button>
        </div>

        <div id="mobile-menu" class="hidden lg:hidden pb-5">
            <div class="flex flex-col gap-1">
                @foreach($enlaces as $e)
                    <a href="{{ route($e['ruta']) }}" @if(request()->routeIs($e['ruta'], $e['tambien'] ?? $e['ruta'])) aria-current="page" @endif
                       class="py-3 px-3 rounded-lg font-modern {{ request()->routeIs($e['ruta'], $e['tambien'] ?? $e['ruta']) ? 'text-pg-tiza bg-pg-carbon' : 'text-pg-tiza/80 hover:text-pg-rojo-claro' }}">{{ $e['texto'] }}</a>
                @endforeach
                @if($navegacion['membresia'])
                    <a href="{{ route('landing.membresia') }}" class="mt-2 bg-pg-rojo text-white font-semibold px-6 py-3 rounded-lg text-center font-modern">Mi membresía</a>
                @endif
            </div>
        </div>
    </nav>
</header>
