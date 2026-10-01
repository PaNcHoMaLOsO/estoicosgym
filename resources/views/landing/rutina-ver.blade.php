@extends('layouts.landing')

@section('title', $web['titulo'])
@section('description', $web['descripcion'])

@section('content')
    @include('landing.partes.cabecera', [
        'antetitulo' => 'Rutina',
        'titulo' => $nombre,
        'bajada' => "{$objetivo} · {$nivel} · {$rutina->dias_por_semana} días por semana",
        'compacta' => true,
    ])

    <section class="bg-pg-negro pb-14">
        <div class="mx-auto max-w-3xl px-4 sm:px-8">

            @if($rutina->descripcion)
                <p class="font-modern text-pg-tiza/75">{{ $rutina->descripcion }}</p>
            @endif

            <p class="mt-3 font-modern text-sm text-pg-tiza/75">
                <span class="text-pg-rojo-claro">Para avanzar:</span> {{ $progresar }}
            </p>

            {{-- LOS DÍAS UNO DEBAJO DEL OTRO, sin pestañas: se lee igual en
                 cualquier teléfono y se puede imprimir o compartir entera. --}}
            @foreach($dias as $dia)
                <article class="mt-12">
                    <header class="flex items-center justify-between gap-4 border-b border-pg-tiza/10 pb-3">
                        <div>
                            <h2 class="font-display text-xl uppercase text-pg-tiza">Día {{ $dia['numero'] }} · {{ $dia['titulo'] }}</h2>
                            @if($dia['foco'])
                                <p class="font-modern text-sm text-pg-tiza/55">{{ $dia['foco'] }}</p>
                            @endif
                        </div>
                        {{-- Todo lo que trabaja el día, de un vistazo. --}}
                        <x-mapa-muscular :principal="$dia['principales']" :secundarios="$dia['secundarios']" class="h-20 w-24 shrink-0 sm:h-24 sm:w-28" />
                    </header>

                    <ol class="divide-y divide-pg-tiza/10">
                        @foreach($dia['lineas'] as $linea)
                            <li class="flex gap-4 py-4">
                                @include('landing.partes.imagen-ejercicio', ['e' => $linea, 'tamano' => 'size-28 sm:size-32'])

                                <div class="min-w-0 font-modern">
                                    <h3 class="font-semibold text-pg-tiza">{{ $linea['nombre'] }}</h3>
                                    <p class="mt-0.5 text-sm tabular-nums text-pg-tiza/80">{{ $linea['dosis'] }}</p>
                                    @if($linea['descanso'])
                                        <p class="text-sm tabular-nums text-pg-tiza/55">Descanso {{ $linea['descanso'] }} s</p>
                                    @endif
                                    @if($linea['nota'])
                                        <p class="mt-1.5 text-sm text-pg-tiza/55">{{ $linea['nota'] }}</p>
                                    @endif
                                    {{-- La sala llena: si la máquina tiene a alguien, esto
                                         otro trabaja lo mismo y no hay que esperar. --}}
                                    @if($linea['alternativa'])
                                        <p class="mt-1.5 text-xs text-pg-tiza/45">
                                            <span class="text-pg-rojo-claro">Si está ocupada:</span> {{ $linea['alternativa'] }}
                                        </p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </article>
            @endforeach

            {{-- OTRAS VARIANTES del mismo objetivo: con más o menos días, u
                 otro nivel. Quien ya puede venir un día más pasa a esa. --}}
            @if($variantes)
                <div class="mt-12">
                    <h2 class="font-display text-lg uppercase text-pg-tiza">Otras variantes</h2>
                    <ul class="mt-2 divide-y divide-pg-tiza/10">
                        @foreach($variantes as $v)
                            <li>
                                <a href="{{ $v['url'] }}" class="flex items-center justify-between gap-4 py-3 font-modern text-pg-tiza/80 transition-colors hover:text-pg-tiza">
                                    <span>
                                        <span class="block text-sm font-semibold">{{ $v['nombre'] }}</span>
                                        <span class="block text-xs text-pg-tiza/50">{{ $v['nivel'] }} · {{ $v['dias'] }} días</span>
                                    </span>
                                    <x-icono nombre="arrow-right" class="text-xs text-pg-tiza/40" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="mt-8 flex flex-wrap gap-x-6 gap-y-2 font-modern text-sm">
                <a href="{{ route('landing.rutinas') }}" class="text-pg-tiza underline underline-offset-4 hover:text-white">Todas las rutinas</a>
                <a href="{{ route('landing.ejercicios') }}" class="text-pg-tiza underline underline-offset-4 hover:text-white">Ejercicios del gimnasio</a>
            </p>

            {{-- EL AVISO VA SIEMPRE Y ABAJO DE LA RUTINA, que es donde se
                 termina de leer. Es una guía general de sala: no la revisó
                 nadie para esta persona en particular. --}}
            <p class="mt-8 font-modern text-xs leading-relaxed text-pg-tiza/50">
                Es una guía general de la sala, igual para todos, y no reemplaza a un profesional.
                Si tienes una lesión, estás embarazada, tomas medicamentos o tienes alguna condición de
                salud, consulta antes con tu médico. Calienta 5 minutos, usa un peso con el que puedas
                terminar todas las repeticiones con buena técnica y, si algo te duele, detente y pregunta en el mesón.
            </p>
        </div>
    </section>
@endsection
