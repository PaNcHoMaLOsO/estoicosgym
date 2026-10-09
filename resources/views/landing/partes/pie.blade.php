<footer class="bg-pg-carbon border-t border-pg-tiza/5">
    <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20 py-7 lg:py-9">
        <div class="grid grid-cols-[2fr_3fr] md:grid-cols-3 gap-x-5 gap-y-6 md:gap-10">
            <div class="col-span-2 md:col-span-1">
                <picture>
                    <source srcset="{{ asset('images/progym-logo-320.webp') }}" type="image/webp">
                    <img src="{{ asset('images/progym-logo-320.png') }}" alt="{{ $gimnasio['nombre'] }}" width="1096" height="495" class="h-12 lg:h-14 w-auto" loading="lazy">
                </picture>
                <p class="text-pg-tiza/55 font-modern text-sm mt-3 lg:mt-4">
                    {{ $gimnasio['nombre'] }}{{ $web['ciudad'] ? ' · Gimnasio en ' . $web['ciudad'] : '' }}{{ $web['region'] ? ', ' . $web['region'] : '' }}{{ $web['ciudad'] ? ', Chile' : '' }}
                </p>
                {{-- La dirección se toca y abre Google Maps: en el celular es lo
                     primero que se intenta. --}}
                @if($gimnasio['direccion'])
                    <p class="font-modern text-sm mt-1">
                        @if($web['google_maps'])
                            <a href="{{ $web['google_maps'] }}" target="_blank" rel="noopener" data-evento="como_llegar"
                               class="inline-flex items-center gap-1.5 text-pg-tiza/55 underline decoration-pg-tiza/20 underline-offset-4 transition-colors hover:text-pg-rojo-claro hover:decoration-pg-rojo-claro">
                                <x-icono nombre="map-marker-alt" class="text-xs" />{{ implode(', ', array_filter([$gimnasio['direccion'], $web['ciudad']])) }}
                            </a>
                        @else
                            <span class="text-pg-tiza/55">{{ implode(', ', array_filter([$gimnasio['direccion'], $web['ciudad']])) }}</span>
                        @endif
                    </p>
                @endif
                {{-- El nombre, la dirección y el teléfono en todas las páginas, iguales
                     a los de Google Maps: es lo que Google compara. --}}
                @if($gimnasio['telefono'] || $web['google_maps'])
                    <p class="flex flex-wrap gap-x-4 gap-y-1 font-modern text-sm mt-1">
                        @if($gimnasio['telefono'])
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $gimnasio['telefono']) }}" data-evento="contacto_directo" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">{{ $gimnasio['telefono'] }}</a>
                        @endif
                        @if($web['google_maps'])
                            <a href="{{ $web['google_maps'] }}" target="_blank" rel="noopener" data-evento="como_llegar" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Cómo llegar</a>
                        @endif
                    </p>
                @endif
                @if($redes)
                    <div class="flex gap-2 mt-4">
                        @foreach($redes as $red)
                            <a href="{{ $red['url'] }}" target="_blank" rel="noopener" aria-label="{{ $red['nombre'] }}" class="w-9 h-9 rounded-lg bg-pg-negro border border-pg-tiza/10 hover:border-pg-rojo/40 flex items-center justify-center text-pg-tiza hover:text-pg-rojo-claro transition-colors"><x-icono :nombre="$red['icono']" class="text-sm" /></a>
                        @endforeach
                    </div>
                @endif
                @if($web['resenas'])
                    {{-- Las reseñas son lo que más pesa para salir primero en el mapa. --}}
                    <a href="{{ $web['resenas'] }}" target="_blank" rel="noopener" data-evento="resena_google" class="inline-flex items-center gap-2 mt-5 text-sm font-modern text-pg-tiza/70 hover:text-pg-tiza transition-colors">
                        <x-icono nombre="star" class="text-yellow-400" /> Déjanos tu reseña en Google
                    </a>
                @endif
            </div>

            <div>
                <h2 class="font-modern text-xs uppercase tracking-widest mb-3 text-pg-tiza/45">Páginas</h2>
                <ul class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-1.5 font-modern text-sm">
                    <li><a href="{{ route('landing') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Inicio</a></li>
                    @if($navegacion['gimnasio'])
                        <li><a href="{{ route('landing.gimnasio') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">El gimnasio</a></li>
                    @endif
                    @if($navegacion['planes'])
                        <li><a href="{{ route('landing.planes') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Planes y precios</a></li>
                    @endif
                    @if($navegacion['clases'])
                        <li><a href="{{ route('landing.clases') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Clases</a></li>
                    @endif
                    @if($navegacion['especialistas'])
                        <li><a href="{{ route('landing.especialistas') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Profesionales</a></li>
                    @endif
                    @if($navegacion['convenios'])
                        <li><a href="{{ route('landing.convenios') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Convenios</a></li>
                    @endif
                    @if($navegacion['arriendo'])
                        <li><a href="{{ route('landing.arriendo') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Arrienda horas</a></li>
                    @endif
                    <li><a href="{{ route('landing.contacto') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Contacto</a></li>
                    @if($navegacion['rutinas'])
                        <li><a href="{{ route('landing.rutina') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Rutinas</a></li>
                        <li><a href="{{ route('landing.ejercicios') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Ejercicios</a></li>
                    @endif
                    @if($navegacion['membresia'])
                        <li><a href="{{ route('landing.membresia') }}" class="text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">Mi membresía</a></li>
                    @endif
                    @if($tienda ?? null)
                        {{-- La tienda es otra web: se marca con el icono para que
                             nadie pulse esperando quedarse en la del gimnasio. --}}
                        <li>
                            <a href="{{ $tienda['url'] }}" target="_blank" rel="noopener" data-evento="tienda_suplementos"
                               class="inline-flex items-center gap-2 text-pg-tiza/55 hover:text-pg-rojo-claro transition-colors">
                                {{ $tienda['titulo'] }}
                                <x-icono nombre="arrow-up-right-from-square" class="text-[10px]" />
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <div>
                <h2 class="font-modern text-xs uppercase tracking-widest mb-3 text-pg-tiza/45">Horario</h2>
                @if($horario['configurado'])
                    {{-- Los dias seguidos con el mismo horario van en una sola linea:
                         «Lunes a viernes» en vez de cinco filas iguales. Siete filas
                         hacian del pie lo mas pesado de la pagina. --}}
                    <?php
                        $grupos = [];
                        foreach ($horario['dias'] as $dia) {
                            $texto = $dia['tramos'] ? implode(' · ', array_map(fn ($t) => $t[0] . ' a ' . $t[1], $dia['tramos'])) : 'Cerrado';
                            $ultimo = count($grupos) - 1;
                            if ($ultimo >= 0 && $grupos[$ultimo]['texto'] === $texto) {
                                $grupos[$ultimo]['hasta'] = $dia['nombre'];
                                $grupos[$ultimo]['hoy'] = $grupos[$ultimo]['hoy'] || $dia['clave'] === $horario['hoy'];
                            } else {
                                $grupos[] = ['desde' => $dia['nombre'], 'hasta' => null, 'texto' => $texto, 'hoy' => $dia['clave'] === $horario['hoy']];
                            }
                        }
                    ?>
                    <ul class="space-y-1.5 font-modern text-xs sm:text-sm">
                        @foreach($grupos as $g)
                            <li class="flex justify-between gap-3 {{ $g['hoy'] ? 'text-pg-tiza' : 'text-pg-tiza/55' }}">
                                <span>{{ $g['desde'] }}{{ $g['hasta'] ? ' a ' . mb_strtolower($g['hasta']) : '' }}</span>
                                <span class="tabular-nums">{{ $g['texto'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-pg-tiza/55 font-modern text-sm">Pregunta en el mesón.</p>
                @endif
            </div>
        </div>

        <div class="border-t border-pg-tiza/5 mt-5 pt-4 lg:mt-7 lg:pt-5 flex flex-col md:flex-row items-center justify-between gap-3">
            <p class="text-pg-tiza/40 text-xs font-modern">&copy; {{ date('Y') }} {{ $gimnasio['nombre'] }}. Todos los derechos reservados.</p>
            <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
                <a href="{{ route('landing.terminos') }}" class="text-pg-tiza/40 hover:text-pg-tiza text-xs font-modern transition-colors">Términos y condiciones</a>
                <a href="{{ route('landing.privacidad') }}" class="text-pg-tiza/40 hover:text-pg-tiza text-xs font-modern transition-colors">Privacidad y cookies</a>
            </div>
        </div>
    </div>
</footer>
