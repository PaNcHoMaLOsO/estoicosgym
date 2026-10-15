{{-- El dibujo de una opción de «qué entrenaste» / «qué entrenar»: el mini
     mapa muscular con el grupo marcado, un corazón para el cardio o alguien
     estirándose para el día de movilidad. Recibe $grupo. --}}
@if($grupo === 'cardio')
    <span class="grid h-10 w-12 shrink-0 place-items-center text-xl text-pg-rojo sm:h-14 sm:w-16 sm:text-3xl" aria-hidden="true"><x-icono nombre="heart-pulse" /></span>
@elseif($grupo === 'movilidad')
    <span class="grid h-10 w-12 shrink-0 place-items-center text-xl text-pg-rojo sm:h-14 sm:w-16 sm:text-3xl" aria-hidden="true"><x-icono nombre="child-reaching" /></span>
@else
    <x-mapa-muscular :principal="\App\Support\EntrenamientoDeHoy::MUSCULOS[$grupo] ?? []" class="h-10 w-12 shrink-0 sm:h-14 sm:w-16" aria-hidden="true" />
@endif
