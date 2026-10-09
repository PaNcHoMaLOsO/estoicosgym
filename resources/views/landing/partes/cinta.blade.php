{{--
    Cinta de logos en movimiento. La lista se repite para llenar el ancho y
    se duplica para que la vuelta no se note. Se detiene al pasar el mouse y
    se queda quieta (en grilla) si el equipo pide reducir el movimiento.

    Con menos de tres logos no se mueve: el mismo logo pasando una y otra vez
    se ve como un error, no como una cinta.
--}}
@php
    // SOLO LOGOS. Lo que no tiene logo subido salía con su nombre en texto,
    // metido entre los logos («Universidad de Concepción» en letra suelta):
    // una cinta de logos con palabras se ve rota. Sin logo, no va en la cinta.
    $logos = array_values(array_filter($logos, fn ($c) => ! empty($c['logo'])));
    $enMovimiento = count($logos) >= 3;
    $vueltas = $enMovimiento ? (int) ceil(8 / count($logos)) : 1;
    $pista = collect(range(1, $vueltas))->flatMap(fn () => $logos)->values()->all();
    $duracion = max(24, count($pista) * 5);

    // EL MISMO PESO A LA VISTA, NO LA MISMA ALTURA. Con una altura fija, un
    // logo ancho (UCSC) llenaba su espacio y un escudo (Carabineros, Bomberos)
    // quedaba diminuto. Cada uno ocupa más o menos la misma área: el alto sale
    // de su proporción, entre 3,25 y 5,5 rem.
    $alto = function (?string $logo): ?string {
        $medidas = $logo ? \App\Support\MedidasDeImagen::de($logo) : null;

        if (! $medidas || ! $medidas[1]) {
            return null;
        }

        return round(min(5.5, max(3.25, sqrt(30 / ($medidas[0] / $medidas[1])))), 2) . 'rem';
    };
@endphp
@if(count($logos))
{{-- La barra blanca va FUERA de la cinta: el degradado que difumina los
     extremos se aplica a la cinta, y si el blanco estuviera ahí se desvanecería
     con ella y la barra dejaría de ser una barra. --}}
<div class="cinta-barra">
    <div class="cinta {{ $enMovimiento ? '' : 'cinta-quieta' }}" role="region" aria-label="{{ $etiqueta ?? 'Logos de los convenios' }}">
    <div class="cinta-pista" @if($enMovimiento) style="animation-duration: {{ $duracion }}s" @endif>
        @foreach($enMovimiento ? [false, true] : [false] as $copia)
            @foreach($pista as $i => $c)
                @php($repetido = $copia || $i >= count($logos))
                <div class="cinta-logo" @if($h = $alto($c['logo'])) style="height: {{ $h }}" @endif @if($repetido) data-repetido aria-hidden="true" @endif>
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
@endif
