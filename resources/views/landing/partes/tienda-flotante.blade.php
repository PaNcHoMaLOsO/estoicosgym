{{--
    El botón flotante de la tienda de suplementos, igual que el de WhatsApp pero
    con la marca de Estoicos, en todas las páginas.

    VA CON LA MARCA SOLA, no con el logotipo entero: dentro de un círculo de 56
    píxeles el «ESTOICOS SUPLEMENTOS» de al lado queda en un borrón ilegible.

    El círculo es oscuro y no blanco porque el logotipo está hecho para fondo
    oscuro: sobre blanco, la columna —que es color crema— casi no se ve. El aro
    morado es el color de su propia marca, sacado del archivo del logo, y es lo
    que lo despega del fondo de la página, que también es oscuro.

    Se sienta encima del de WhatsApp, no al lado: en un teléfono, dos botones
    juntos abajo a la derecha se pulsan mal.
--}}
@if(($tienda ?? null) && $tienda['icono'])
    <a href="{{ $tienda['url'] }}" target="_blank" rel="noopener"
       data-evento="tienda_suplementos"
       title="{{ $tienda['titulo'] }}"
       aria-label="{{ $tienda['titulo'] }}: ir a la tienda, se abre en otra pestaña"
       {{-- El aro va como BORDE, no como `ring`: en Tailwind el ring se dibuja
            con `box-shadow`, que es justo lo que anima el pulso, y se lo comía. --}}
       class="pulso fixed bottom-24 right-5 z-40 w-14 h-14 rounded-full bg-pg-carbon border-2 border-[#C142D1] flex items-center justify-center hover:scale-110 transition-transform"
       style="--color-pulso: #C142D1">
        <img src="{{ $tienda['icono'] }}" alt="" class="w-9 h-9 object-contain">
    </a>
@endif
