{{--
    Cinta de logos en movimiento. La lista se repite para llenar el ancho y
    se duplica para que la vuelta no se note. Se detiene al pasar el mouse y
    se queda quieta —en grilla— si el equipo pide reducir el movimiento.

    Con menos de tres logos no se mueve: el mismo logo pasando una y otra vez
    se ve como un error, no como una cinta.
--}}
@php
    $enMovimiento = count($logos) >= 3;
    $vueltas = $enMovimiento ? (int) ceil(8 / count($logos)) : 1;
    $pista = collect(range(1, $vueltas))->flatMap(fn () => $logos)->values()->all();
    $duracion = max(24, count($pista) * 5);
@endphp
{{-- La barra blanca va FUERA de la cinta: el degradado que difumina los
     extremos se aplica a la cinta, y si el blanco estuviera ahí se desvanecería
     con ella y la barra dejaría de ser una barra. --}}
<div class="cinta-barra">
    <div class="cinta {{ $enMovimiento ? '' : 'cinta-quieta' }}" role="region" aria-label="Logos de los convenios">
    <div class="cinta-pista" @if($enMovimiento) style="animation-duration: {{ $duracion }}s" @endif>
        @foreach($enMovimiento ? [false, true] : [false] as $copia)
            @foreach($pista as $i => $c)
                @php($repetido = $copia || $i >= count($logos))
                <div class="cinta-logo" @if($repetido) data-repetido aria-hidden="true" @endif>
                    @if($c['logo'])
                        <img src="{{ $c['logo'] }}" alt="{{ $repetido ? '' : $c['nombre'] }}">
                    @else
                        <span>{{ $c['nombre'] }}</span>
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>
    </div>
</div>
