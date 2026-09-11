{{-- El horario dia por dia, con el de hoy marcado. Sale de Configuracion -> Horario. --}}
@if($horario['configurado'])
    <section id="horario" class="py-20 bg-pg-carbon">
        <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 animate-on-scroll">
                <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Cuándo venir</span>
                <h2 class="font-display text-4xl md:text-5xl mt-3 text-pg-tiza">HORARIO</h2>
            </div>
            {{-- Los siete días uno al lado del otro: en una lista angosta al centro
                 sobraba todo el ancho de la pantalla. --}}
            <dl class="animate-on-scroll grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                @foreach($horario['dias'] as $dia)
                    <div class="rounded-2xl border px-4 py-5 text-center {{ $dia['clave'] === $horario['hoy'] ? 'border-pg-rojo/50 bg-pg-rojo/10' : 'border-pg-tiza/10 bg-pg-negro/60' }}">
                        <dt class="font-modern text-sm uppercase tracking-wider {{ $dia['clave'] === $horario['hoy'] ? 'text-pg-rojo-claro' : 'text-pg-tiza/55' }}">
                            {{ $dia['nombre'] }}
                            @if($dia['clave'] === $horario['hoy'])
                                <span class="block text-[10px] tracking-widest">Hoy</span>
                            @endif
                        </dt>
                        <dd class="mt-2 font-modern tabular-nums leading-snug {{ $dia['tramos'] ? 'text-pg-tiza' : 'text-pg-tiza/40' }}">
                            @forelse($dia['tramos'] as $tramo)
                                <span class="block">{{ $tramo[0] }} – {{ $tramo[1] }}</span>
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
