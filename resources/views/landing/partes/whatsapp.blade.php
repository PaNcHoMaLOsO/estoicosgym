{{-- Los botones flotantes del gimnasio, en todas las páginas. Salen de Configuración -> Web.

     Tres pisos, de abajo arriba: la tienda (su recuadro, en su propio archivo),
     el WhatsApp y el Instagram. Chicos (44 px, el mínimo cómodo para el dedo) para
     que no tapen la página: lo que llama la atención es el color y el latido, no
     el tamaño. Las distancias cambian con la pantalla porque el
     recuadro de la tienda es más bajo en el teléfono.

     Laten los tres EN CASCADA, de abajo arriba: la tienda, 0,8 s después el
     WhatsApp y 0,8 s después el Instagram (el ciclo dura 2,4 s). Todos a la vez
     hacen palpitar la esquina entera; en cascada se lee como una sola ola. --}}
@php($instagram = collect($redes ?? [])->first(fn ($r) => str_contains(strtolower($r['nombre'] ?? ''), 'instagram')))

@if($whatsapp)
    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" data-evento="whatsapp_gimnasio" aria-label="Escríbenos por WhatsApp"
       style="animation-delay: -1.6s"
       class="pulso fixed bottom-[4.125rem] right-4 sm:bottom-[5.25rem] sm:right-5 z-40 w-11 h-11 rounded-full bg-[#25D366] text-white flex items-center justify-center hover:scale-110 transition-transform">
        <i class="fab fa-whatsapp text-xl" aria-hidden="true"></i>
    </a>
@endif

@if($instagram)
    {{-- Sin WhatsApp configurado baja a su sitio, para no dejar un hueco en medio. --}}
    <a href="{{ $instagram['url'] }}" target="_blank" rel="noopener" data-evento="instagram_gimnasio" aria-label="Síguenos en Instagram"
       class="fixed {{ $whatsapp ? 'bottom-[7.375rem] sm:bottom-[8.625rem]' : 'bottom-[4.125rem] sm:bottom-[5.25rem]' }} right-4 sm:right-5 z-40 w-11 h-11 rounded-full text-white flex items-center justify-center hover:scale-110 transition-transform pulso"
       style="--color-pulso: #d6249f; animation-delay: -0.8s; background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%)">
        <i class="fab fa-instagram text-xl" aria-hidden="true"></i>
    </a>
@endif
