@props(['nombre' => ''])
{{-- Un ícono de Font Awesome Free en línea: ver App\Support\Iconos. --}}
{{ \App\Support\Iconos::svg((string) $nombre, (string) $attributes->get('class', '')) }}
