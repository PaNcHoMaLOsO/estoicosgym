{{--
    El acceso flotante a la tienda de suplementos, en todas las páginas.

    ES UN RECUADRO CON EL LOGOTIPO ENTERO, EN TODAS LAS PANTALLAS. En el teléfono
    iba la marca sola (la columna y el laurel) dentro de un círculo, y no se
    entendía qué era: un símbolo suelto no dice a dónde lleva. Ahora va el mismo
    logotipo que en el computador, un poco más chico para que quepa.

    Al pasar por encima sale una etiqueta que dice qué es, porque el logotipo
    dice el nombre pero no que sea una tienda.

    Va abajo del todo; encima se apilan el WhatsApp y el Instagram. Uno sobre
    otro y no al lado: en un teléfono, dos cosas juntas se pulsan mal.
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
       class="pulso group fixed bottom-4 right-4 z-40 flex items-center justify-center rounded-xl border border-[#C142D1]/30 bg-pg-carbon/90 px-2.5 py-1.5 backdrop-blur-sm transition-colors hover:border-[#C142D1]/60
              sm:bottom-5 sm:right-5 sm:px-3 sm:py-2"
       style="--color-pulso: #C142D1">

        {{-- La etiqueta sale a la izquierda: a la derecha se saldría de la pantalla. --}}
        <span class="pointer-events-none absolute right-full mr-3 hidden whitespace-nowrap rounded-lg border border-pg-tiza/15 bg-pg-negro px-3 py-2 font-modern text-sm text-pg-tiza opacity-0 shadow-lg shadow-black/50 transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 sm:block">
            Tienda de suplementos
        </span>

        {{-- El logotipo entero siempre; el icono solo queda de respaldo si no hay logotipo. --}}
        @if($tienda['logo'])
            <img src="{{ $tienda['logo'] }}" alt="{{ $tienda['titulo'] }}" class="h-7 w-auto sm:h-9">
        @else
            <img src="{{ $tienda['icono'] }}" alt="{{ $tienda['titulo'] }}" class="h-7 w-7 object-contain">
        @endif
    </a>
@endif
