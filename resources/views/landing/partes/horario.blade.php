{{-- El horario dia por dia, con el de hoy marcado. Sale de Configuracion -> Horario. --}}
@if($horario['configurado'])
    <section id="horario" class="py-20 bg-pg-carbon">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 animate-on-scroll">
                <span class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">Cuándo venir</span>
                <h2 class="font-display text-4xl md:text-5xl mt-3 text-pg-tiza">HORARIO</h2>
            </div>
            <dl class="animate-on-scroll divide-y divide-pg-tiza/10 border border-pg-tiza/10 rounded-2xl overflow-hidden bg-pg-negro/60">
                @foreach($horario['dias'] as $dia)
                    <div class="flex items-center justify-between gap-4 px-6 py-4 {{ $dia['clave'] === $horario['hoy'] ? 'bg-pg-rojo/10' : '' }}">
                        <dt class="font-modern text-pg-tiza">
                            {{ $dia['nombre'] }}
                            @if($dia['clave'] === $horario['hoy'])
                                <span class="ml-2 text-xs uppercase tracking-wider text-pg-rojo-claro">Hoy</span>
                            @endif
                        </dt>
                        <dd class="font-modern tabular-nums {{ $dia['tramos'] ? 'text-pg-tiza' : 'text-pg-tiza/40' }}">
                            {{ $dia['tramos'] ? implode(' · ', array_map(fn ($t) => $t[0] . ' – ' . $t[1], $dia['tramos'])) : 'Cerrado' }}
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
