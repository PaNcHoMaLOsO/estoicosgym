<footer class="bg-pg-carbon border-t border-pg-tiza/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div>
                <picture>
                    <source srcset="{{ asset('images/progym-logo.webp') }}" type="image/webp">
                    <img src="{{ asset('images/progym-logo.png') }}" alt="{{ $gimnasio['nombre'] }}" width="1096" height="495" class="h-16 w-auto" loading="lazy">
                </picture>
                <p class="text-pg-tiza/55 font-modern text-sm mt-5">
                    {{ $gimnasio['nombre'] }}{{ $web['ciudad'] ? ' · Gimnasio en ' . $web['ciudad'] : '' }}{{ $web['region'] ? ', ' . $web['region'] : '' }}
                </p>
                @if($gimnasio['direccion'])
                    <p class="text-pg-tiza/55 font-modern text-sm mt-1">{{ $gimnasio['direccion'] }}</p>
                @endif
                @if($web['instagram'] || $web['facebook'])
                    <div class="flex gap-3 mt-5">
                        @if($web['instagram'])
                            <a href="{{ $web['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram" class="w-11 h-11 rounded-xl bg-pg-negro border border-pg-tiza/10 hover:border-pg-rojo/40 flex items-center justify-center text-pg-tiza hover:text-pg-rojo-claro transition-colors"><i class="fab fa-instagram text-lg" aria-hidden="true"></i></a>
                        @endif
                        @if($web['facebook'])
                            <a href="{{ $web['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook" class="w-11 h-11 rounded-xl bg-pg-negro border border-pg-tiza/10 hover:border-pg-rojo/40 flex items-center justify-center text-pg-tiza hover:text-pg-rojo-claro transition-colors"><i class="fab fa-facebook-f text-lg" aria-hidden="true"></i></a>
                        @endif
                    </div>
                @endif
            </div>

            <div>
                <h2 class="font-semibold mb-4 text-pg-tiza">Páginas</h2>
                <ul class="space-y-2 font-modern text-sm">
                    <li><a href="{{ route('landing') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Inicio</a></li>
                    <li><a href="{{ route('landing.gimnasio') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">El gimnasio</a></li>
                    <li><a href="{{ route('landing.planes') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Planes y precios</a></li>
                    @if($navegacion['convenios'])
                        <li><a href="{{ route('landing.convenios') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Convenios</a></li>
                    @endif
                    @if($navegacion['especialistas'])
                        <li><a href="{{ route('landing.especialistas') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Especialistas</a></li>
                    @endif
                    <li><a href="{{ route('landing.contacto') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Contacto</a></li>
                    <li><a href="{{ route('landing.membresia') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Mi membresía</a></li>
                </ul>
            </div>

            <div>
                <h2 class="font-semibold mb-4 text-pg-tiza">Horario</h2>
                @if($horario['configurado'])
                    <ul class="space-y-1 font-modern text-sm">
                        @foreach($horario['dias'] as $dia)
                            <li class="flex justify-between gap-4 {{ $dia['clave'] === $horario['hoy'] ? 'text-pg-tiza' : 'text-pg-tiza/55' }}">
                                <span>{{ $dia['nombre'] }}</span>
                                <span class="tabular-nums">{{ $dia['tramos'] ? implode(' · ', array_map(fn ($t) => $t[0] . '–' . $t[1], $dia['tramos'])) : 'Cerrado' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @elseif($gimnasio['horario'])
                    <p class="text-pg-tiza/55 font-modern text-sm">{{ $gimnasio['horario'] }}</p>
                @else
                    <p class="text-pg-tiza/55 font-modern text-sm">Pregunta en el mesón.</p>
                @endif
            </div>
        </div>

        <div class="border-t border-pg-tiza/5 mt-12 pt-8 flex flex-col md:flex-row items-center justify-between gap-3">
            <p class="text-pg-tiza/40 text-sm font-modern">&copy; {{ date('Y') }} {{ $gimnasio['nombre'] }}. Todos los derechos reservados.</p>
            <a href="{{ route('landing.privacidad') }}" class="text-pg-tiza/40 hover:text-pg-tiza text-sm font-modern transition-colors">Privacidad y cookies</a>
        </div>
    </div>
</footer>
