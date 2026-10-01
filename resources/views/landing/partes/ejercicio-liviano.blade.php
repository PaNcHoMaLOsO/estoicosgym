{{-- Uno del calentamiento, de la movilidad o de los estiramientos: su mini
     mapa (o un ícono si no marca músculos), el nombre con la banda si la usa,
     la dosis y cómo se hace en una línea. Recibe $x. --}}
<li class="flex gap-3.5">
    @if($x['musculos'])
        <x-mapa-muscular :principal="$x['musculos']" class="h-14 w-16 shrink-0" />
    @else
        <span class="grid h-14 w-16 shrink-0 place-items-center text-2xl text-pg-rojo-claro" aria-hidden="true"><x-icono nombre="person-running" /></span>
    @endif
    <div class="min-w-0 flex-1 font-modern">
        <h3 class="font-semibold leading-snug text-pg-tiza">
            {{ $x['nombre'] }}
            @if($x['banda'])
                <span class="ml-1 inline-flex translate-y-0.5 items-center text-pg-rojo-claro" title="Con banda elástica"><x-icono nombre="banda" class="text-base" /><span class="sr-only">, con banda</span></span>
            @endif
        </h3>
        <p class="mt-0.5 font-display text-lg leading-none tabular-nums text-pg-rojo-claro">{{ $x['dosis'] }}</p>
        <p class="mt-1.5 text-sm leading-snug text-pg-tiza/65">{{ $x['como'] }}</p>
    </div>
</li>
