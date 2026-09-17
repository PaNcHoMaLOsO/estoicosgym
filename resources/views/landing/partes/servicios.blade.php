<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @foreach($servicios as $index => $servicio)
        {{-- El icono al lado del título y sin cuadrado de color detrás: el
             cuadradito con degradado que crece y se gira al pasar por encima es
             el adorno de plantilla, y ocupaba más sitio que lo que hay que leer. --}}
        <div class="animate-on-scroll card-hover bg-pg-carbon/70 border border-pg-tiza/10 rounded-2xl p-8 hover:border-pg-rojo/40" style="animation-delay: {{ $index * 0.1 }}s">
            <h3 class="flex items-center gap-3 font-display text-2xl uppercase text-pg-tiza">
                <i class="fas fa-{{ $servicio['icono'] }} text-pg-rojo-claro text-base" aria-hidden="true"></i>
                <span>{{ $servicio['titulo'] }}</span>
            </h3>
            <p class="text-pg-tiza/60 font-modern text-sm mt-3 leading-relaxed">{{ $servicio['descripcion'] }}</p>
        </div>
    @endforeach
</div>
