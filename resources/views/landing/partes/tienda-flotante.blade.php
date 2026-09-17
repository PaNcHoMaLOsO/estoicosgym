{{--
    El acceso flotante a la tienda de suplementos, en todas las páginas.

    ES UN RECUADRO CON EL LOGOTIPO ENTERO, no un círculo con un icono: un símbolo
    suelto no dice a dónde lleva, y hay que pasar por encima para enterarse. Con
    el logotipo completo se entiende sin hacer nada.

    En el móvil no cabe un recuadro ancho al lado del de WhatsApp, así que ahí se
    queda la marca sola —la columna y el laurel— en un círculo del mismo tamaño
    que el de WhatsApp.

    Y al pasar por encima sale una etiqueta que dice qué es, porque el logotipo
    dice el nombre pero no que sea una tienda.

    Se sienta ENCIMA del de WhatsApp, no al lado: en un teléfono, dos cosas
    juntas abajo a la derecha se pulsan mal.
--}}
@if(($tienda ?? null) && ($tienda['logo'] || $tienda['icono']))
    <a href="{{ $tienda['url'] }}" target="_blank" rel="noopener"
       data-evento="tienda_suplementos"
       aria-label="{{ $tienda['titulo'] }}, tienda de suplementos: se abre en otra pestaña"
       {{-- El aro va como BORDE, no como `ring`: en Tailwind el ring se dibuja
            con `box-shadow`, que es justo lo que anima el pulso, y se lo comía.

            Fino y traslúcido: a dos píxeles y morado a tope, el aro gritaba más
            que el propio logotipo. Quien tiene que llamar la atención es la
            marca, no la línea de alrededor. --}}
       class="pulso group fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full border border-[#C142D1]/30 bg-pg-carbon transition-colors hover:border-[#C142D1]/60
              sm:h-auto sm:w-auto sm:rounded-2xl sm:px-4 sm:py-3"
       style="--color-pulso: #C142D1">

        {{-- La etiqueta sale a la izquierda: a la derecha se saldría de la pantalla. --}}
        <span class="pointer-events-none absolute right-full mr-3 hidden whitespace-nowrap rounded-lg border border-pg-tiza/15 bg-pg-negro px-3 py-2 font-modern text-sm text-pg-tiza opacity-0 shadow-lg shadow-black/50 transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 sm:block">
            Tienda de suplementos
        </span>

        @if($tienda['icono'])
            <img src="{{ $tienda['icono'] }}" alt="" class="h-9 w-9 object-contain sm:hidden">
        @endif

        @if($tienda['logo'])
            <img src="{{ $tienda['logo'] }}" alt="{{ $tienda['titulo'] }}"
                 class="{{ $tienda['icono'] ? 'hidden sm:block' : '' }} h-11 w-auto">
        @endif
    </a>
@endif
