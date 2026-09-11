<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @foreach($servicios as $index => $servicio)
        <div class="animate-on-scroll card-hover group bg-pg-carbon/70 border border-pg-tiza/10 rounded-2xl p-8 hover:border-pg-rojo/40" style="animation-delay: {{ $index * 0.1 }}s">
            <div class="w-16 h-16 bg-gradient-to-br from-pg-rojo/25 to-pg-rojo-oscuro/20 rounded-xl flex items-center justify-center mb-6 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">
                <i class="fas fa-{{ $servicio['icono'] }} text-pg-rojo-claro text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="font-display text-2xl uppercase mb-3 text-pg-tiza">{{ $servicio['titulo'] }}</h3>
            <p class="text-pg-tiza/60 font-modern text-sm">{{ $servicio['descripcion'] }}</p>
        </div>
    @endforeach
</div>
