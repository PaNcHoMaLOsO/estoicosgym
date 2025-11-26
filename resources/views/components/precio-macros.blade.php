{{-- 
    Helper Blade para formatear precios
    Uso:
        @precio(40000)              // 40.000
        @precioConMoneda(40000)     // $40.000
        @precioConDecimales(40000.50)  // 40.000,50
        @precioConMonedaY(40000.50)    // $40.000,50
--}}

@php
    use App\Helpers\PrecioHelper;
@endphp

{{-- Macro para formato simple (sin decimales) --}}
@macro('precio', function($valor) {
    return PrecioHelper::formato($valor);
})

{{-- Macro para formato con símbolo de moneda --}}
@macro('precioConMoneda', function($valor) {
    return PrecioHelper::formatoConMoneda($valor);
})

{{-- Macro para formato con decimales --}}
@macro('precioConDecimales', function($valor, $decimales = 2) {
    return PrecioHelper::formatoConDecimales($valor, $decimales);
})

{{-- Macro para formato con moneda y decimales --}}
@macro('precioConMonedaY', function($valor) {
    return PrecioHelper::formatoConMonedaYDecimales($valor);
})
