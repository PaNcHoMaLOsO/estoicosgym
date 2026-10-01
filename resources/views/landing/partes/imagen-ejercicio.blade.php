{{-- La imagen de un ejercicio: la foto o el GIF que subió el gimnasio, o el
     mapa muscular mientras no haya. Recibe $e (con imagen, nombre, principal
     y secundarios) y $tamano (las clases de ancho y alto). --}}
<div class="{{ $tamano }} shrink-0 overflow-hidden rounded-lg bg-pg-grafito">
    @if($e['imagen'])
        <img src="{{ $e['imagen'] }}" alt="{{ $e['nombre'] }}" loading="lazy" decoding="async" class="size-full object-cover">
    @else
        <x-mapa-muscular :principal="$e['principal']" :secundarios="$e['secundarios']" class="size-full p-1.5" />
    @endif
</div>
