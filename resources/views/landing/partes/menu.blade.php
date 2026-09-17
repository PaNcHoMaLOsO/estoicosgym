{{--
    El menu de todas las paginas. Solo enlaza lo que tiene algo que mostrar:
    sin convenios publicados no hay «Convenios», sin especialistas no hay
    «Especialistas». El boton rojo es lo que mas busca un socio: su membresia.
--}}
@php
    $enlaces = array_values(array_filter([
        ['ruta' => 'landing.gimnasio', 'texto' => 'El gimnasio'],
        ['ruta' => 'landing.planes', 'texto' => 'Planes'],
        $navegacion['convenios'] ? ['ruta' => 'landing.convenios', 'texto' => 'Convenios'] : null,
        $navegacion['especialistas'] ? ['ruta' => 'landing.especialistas', 'texto' => 'Especialistas'] : null,
        ['ruta' => 'landing.contacto', 'texto' => 'Contacto'],
    ]));
@endphp
<header id="navbar" class="fixed top-0 inset-x-0 z-50 bg-pg-negro/90 backdrop-blur-md border-b border-pg-tiza/5">
    @if($aviso)
        <div class="bg-pg-rojo text-white text-center text-sm font-modern px-4 py-2">
            <i class="fas fa-bullhorn mr-2" aria-hidden="true"></i>{{ $aviso }}
        </div>
    @endif

    <nav class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8" aria-label="Principal">
        <div class="flex items-center justify-between h-24">
            <a href="{{ route('landing') }}" class="flex items-center shrink-0" aria-label="{{ $gimnasio['nombre'] }}, ir al inicio">
                <picture>
                    <source srcset="{{ asset('images/progym-logo.webp') }}" type="image/webp">
                    <img src="{{ asset('images/progym-logo.png') }}" alt="{{ $gimnasio['nombre'] }}" width="1096" height="495" class="h-14 w-auto">
                </picture>
            </a>

            <div class="hidden lg:flex items-center gap-7">
                @foreach($enlaces as $e)
                    <a href="{{ route($e['ruta']) }}" @if(request()->routeIs($e['ruta'])) aria-current="page" @endif
                       class="relative font-modern text-sm transition-colors py-2 {{ request()->routeIs($e['ruta']) ? 'text-pg-tiza after:absolute after:inset-x-0 after:-bottom-0.5 after:h-0.5 after:bg-pg-rojo after:rounded-full' : 'text-pg-tiza/70 hover:text-pg-rojo-claro' }}">{{ $e['texto'] }}</a>
                @endforeach
                <a href="{{ route('landing.membresia') }}" class="bg-pg-rojo hover:bg-pg-rojo-oscuro text-white font-semibold px-5 py-2.5 rounded-lg transition-colors font-modern text-sm">
                    <i class="fas fa-id-card mr-2" aria-hidden="true"></i>Mi membresía
                </a>
            </div>

            <button id="mobile-menu-btn" type="button" class="lg:hidden text-pg-tiza p-2" aria-label="Abrir el menú" aria-controls="mobile-menu">
                <i class="fas fa-bars text-xl" aria-hidden="true"></i>
            </button>
        </div>

        <div id="mobile-menu" class="hidden lg:hidden pb-5">
            <div class="flex flex-col gap-1">
                @foreach($enlaces as $e)
                    <a href="{{ route($e['ruta']) }}" @if(request()->routeIs($e['ruta'])) aria-current="page" @endif
                       class="py-3 px-3 rounded-lg font-modern {{ request()->routeIs($e['ruta']) ? 'text-pg-tiza bg-pg-carbon' : 'text-pg-tiza/80 hover:text-pg-rojo-claro' }}">{{ $e['texto'] }}</a>
                @endforeach
                <a href="{{ route('landing.membresia') }}" class="mt-2 bg-pg-rojo text-white font-semibold px-6 py-3 rounded-lg text-center font-modern">Mi membresía</a>
            </div>
        </div>
    </nav>
</header>
