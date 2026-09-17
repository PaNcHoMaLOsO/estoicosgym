{{--
    SIN TARJETAS: una línea separa un servicio del siguiente.

    Cada uno iba en su propio recuadro con borde, fondo y esquinas redondeadas.
    Tres cajas seguidas pesan más que lo que llevan dentro, y una página hecha
    toda de cajitas iguales se reconoce de lejos como plantilla.

    En pantalla estrecha van uno debajo de otro con la línea en horizontal; a
    partir de `lg` van en fila con la línea en vertical. El cambio se hace en el
    mismo punto en el que caben todos en una fila: separando por columnas antes,
    la línea saldría entre filas y no entre servicios.
--}}
<div class="grid grid-cols-1 divide-y lg:grid-cols-3 lg:divide-y-0 lg:divide-x divide-pg-tiza/10">
    @foreach($servicios as $index => $servicio)
        {{-- El icono al lado del título y sin cuadrado de color detrás: el
             cuadradito con degradado que crece y se gira al pasar por encima es
             el adorno de plantilla, y ocupaba más sitio que lo que hay que leer. --}}
        <div class="animate-on-scroll py-4 lg:px-8 lg:py-2" style="animation-delay: {{ $index * 0.1 }}s">
            <h3 class="flex items-center gap-3 font-display text-xl uppercase text-pg-tiza">
                <i class="fas fa-{{ $servicio['icono'] }} text-pg-rojo-claro text-base" aria-hidden="true"></i>
                <span>{{ $servicio['titulo'] }}</span>
            </h3>
            <p class="text-pg-tiza/60 font-modern text-sm mt-1.5 lg:mt-3 leading-relaxed">{{ $servicio['descripcion'] }}</p>
        </div>
    @endforeach
</div>
