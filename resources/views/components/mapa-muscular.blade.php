@props(['principal' => [], 'secundarios' => [], 'grupos' => null])
{{--
    MAPA MUSCULAR: una silueta de frente y de espalda con los grupos que
    trabaja un ejercicio (o un día entero) marcados en rojo. Es lo que se ve
    mientras el gimnasio no suba una foto de la máquina.

    Los trazos están dibujados a mano para esta web: una figura de 50 × 100 en
    la que se dibuja solo el lado izquierdo de cada músculo y se refleja. Sin
    clases de Tailwind adentro (esta carpeta no la compila la hoja de la web):
    los colores salen de las variables de la marca.

    Uso: <x-mapa-muscular principal="pecho" :secundarios="['triceps']" class="h-24" />
    o <x-mapa-muscular :grupos="['pecho', 'triceps']" />, todos como principales.
--}}
@php
    $principales = array_values(array_filter((array) ($grupos ?? $principal)));
    $secundarios = array_values(array_diff(array_filter((array) $secundarios), $principales));
    $nombres = \App\Models\Ejercicio::MUSCULOS;

    // [grupo, forma, se refleja]. Coordenadas de la figura de 50 × 100.
    $frente = [
        ['hombros', '<ellipse cx="13.2" cy="21.8" rx="3.3" ry="3.4"/>', true],
        ['pecho', '<path d="M15.4 20.8Q20 19.6 24.6 20.4V28.4Q19.6 30.4 15.9 27.6Z"/>', true],
        ['biceps', '<ellipse cx="10.9" cy="30.5" rx="1.9" ry="5" transform="rotate(8 10.9 30.5)"/>', true],
        ['abdomen', '<rect x="21.1" y="30.2" width="7.8" height="17" rx="2.6"/>', false],
        ['cuadriceps', '<path d="M16.3 61L23.8 62.6L23.2 76Q20.5 77.6 18.2 76Q15.8 68 16.3 61Z"/>', true],
        ['pantorrillas', '<ellipse cx="20.6" cy="86.5" rx="1.8" ry="6"/>', true],
    ];
    $espalda = [
        ['hombros', '<ellipse cx="13.2" cy="21.8" rx="3.3" ry="3.4"/>', true],
        ['espalda', '<path d="M20 16.6H30L33 21L25 26.2L17 21Z"/>', false],
        ['espalda', '<path d="M15.3 23Q20 25 24.5 27V40Q20 38.2 16.8 36Q15.5 30 15.3 23Z"/>', true],
        ['triceps', '<ellipse cx="10.7" cy="29.6" rx="2" ry="5" transform="rotate(8 10.7 29.6)"/>', true],
        ['lumbar', '<rect x="21" y="40.6" width="8" height="8" rx="2"/>', false],
        ['gluteos', '<ellipse cx="20.6" cy="54.6" rx="4.6" ry="4.8"/>', true],
        ['isquios', '<path d="M16.3 62L23.6 63.5L23.1 76.5Q20.5 78 18.2 76.5Q15.9 69 16.3 62Z"/>', true],
        ['pantorrillas', '<ellipse cx="20.5" cy="85.5" rx="2.4" ry="6.5"/>', true],
    ];

    // La silueta: cabeza, cuello, tronco, cadera y el lado izquierdo de
    // brazos y piernas (se refleja).
    $cuerpo = '<ellipse cx="25" cy="8" rx="5.5" ry="6.5"/><rect x="22.5" y="13.5" width="5" height="4.5"/>'
        . '<path d="M13 20Q25 15.4 37 20L35 36Q34 44 33.6 50H16.4Q16 44 15 36Z"/>'
        . '<path d="M16.4 49H33.6Q35 55 34.5 60L25.8 62L25 58.5L24.2 62L15.5 60Q15 55 16.4 49Z"/>';
    $lado = '<path d="M13 20Q9.4 21 9 26L8 38Q9.6 39.6 11.6 38L14.2 27Z"/>'
        . '<path d="M8 38L6.2 52Q7.6 53.6 9 52.6L11.6 38Z"/><ellipse cx="7.4" cy="55.6" rx="1.8" ry="2.8"/>'
        . '<path d="M15.5 59L24.2 61L23.5 78Q20.5 80 17.8 78Q15 68 15.5 59Z"/>'
        . '<path d="M17.8 79Q20.5 81 23.5 79L22.8 95Q20.8 96.6 19.2 95Q17 87 17.8 79Z"/>'
        . '<path d="M19 95.6H23L23.5 98.6H18Z"/>';

    $color = function (string $grupo) use ($principales, $secundarios) {
        if (in_array($grupo, $principales, true)) {
            return 'fill="var(--color-pg-rojo)"';
        }

        return in_array($grupo, $secundarios, true)
            ? 'fill="var(--color-pg-rojo)" fill-opacity=".45"'
            : 'fill="var(--color-pg-tiza)" fill-opacity=".1"';
    };

    $figura = function (array $formas) use ($color, $cuerpo, $lado) {
        $svg = '<g fill="var(--color-pg-tiza)" fill-opacity=".15">' . $cuerpo . $lado
            . '<g transform="matrix(-1 0 0 1 50 0)">' . $lado . '</g></g>';

        foreach ($formas as [$grupo, $forma, $refleja]) {
            $svg .= '<g ' . $color($grupo) . '>' . $forma
                . ($refleja ? '<g transform="matrix(-1 0 0 1 50 0)">' . $forma . '</g>' : '') . '</g>';
        }

        return $svg;
    };

    $etiqueta = collect([...$principales, ...$secundarios])->map(fn ($g) => mb_strtolower($nombres[$g] ?? $g))->implode(', ');
@endphp
<svg viewBox="0 0 120 106" xmlns="http://www.w3.org/2000/svg" role="img"
    aria-label="{{ $etiqueta ? 'Trabaja: ' . $etiqueta : 'Mapa muscular' }}" {{ $attributes }}>
    <g transform="translate(4 4)">{!! $figura($frente) !!}</g>
    <g transform="translate(66 4)">{!! $figura($espalda) !!}</g>
</svg>
