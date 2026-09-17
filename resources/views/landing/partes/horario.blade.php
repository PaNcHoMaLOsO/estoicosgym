{{-- El horario dia por dia, con el de hoy marcado. Sale de Configuracion -> Horario. --}}
@if($horario['configurado'])
    <section id="horario" class="py-8 lg:py-14 bg-pg-carbon">
        <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20">
            <div class="text-center mb-6 lg:mb-10 animate-on-scroll">
                <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Cuándo venir</span>
                <h2 class="font-display text-3xl md:text-4xl mt-3 text-pg-tiza">HORARIO</h2>
            </div>
            {{-- Los siete días uno al lado del otro: en una lista angosta al centro
                 sobraba todo el ancho de la pantalla. --}}
            {{-- Siete días, siete líneas: no siete tarjetas. El de hoy se marca
                 con el color del texto, que basta para encontrarlo de un vistazo
                 sin meterle un recuadro rojo alrededor. --}}
            <dl class="animate-on-scroll grid grid-cols-1 divide-y lg:grid-cols-7 lg:divide-y-0 lg:divide-x divide-pg-tiza/10">
                @foreach($horario['dias'] as $dia)
                    <div class="flex items-baseline justify-between gap-3 py-2.5 lg:py-4 lg:block lg:px-4 lg:text-center {{ $dia['clave'] === $horario['hoy'] ? 'lg:bg-pg-rojo/5' : '' }}">
                        <dt class="font-modern text-sm uppercase tracking-wider {{ $dia['clave'] === $horario['hoy'] ? 'text-pg-rojo-claro' : 'text-pg-tiza/55' }}">
                            {{ $dia['nombre'] }}
                            @if($dia['clave'] === $horario['hoy'])
                                <span class="ml-2 lg:ml-0 inline lg:block text-[10px] tracking-widest">Hoy</span>
                            @endif
                        </dt>
                        <dd class="lg:mt-2 font-modern tabular-nums leading-snug {{ $dia['tramos'] ? 'text-pg-tiza' : 'text-pg-tiza/40' }}">
                            @forelse($dia['tramos'] as $tramo)
                                <span class="block">{{ $tramo[0] }} a {{ $tramo[1] }}</span>
                            @empty
                                Cerrado
                            @endforelse
                        </dd>
                    </div>
                @endforeach
            </dl>
            @if($horario['nota'])
                <p class="text-center text-pg-tiza/60 text-sm mt-4 font-modern">{{ $horario['nota'] }}</p>
            @endif
        </div>
    </section>
@endif
