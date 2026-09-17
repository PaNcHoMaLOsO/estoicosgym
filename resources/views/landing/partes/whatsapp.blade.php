{{-- El WhatsApp del gimnasio, flotando en todas las paginas. Sale de Configuracion -> Web. --}}
@if($whatsapp)
    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" data-evento="whatsapp_gimnasio" aria-label="Escríbenos por WhatsApp"
       {{-- Va ENCIMA del de la tienda, que se queda abajo del todo. La distancia
            cambia con la pantalla porque abajo hay un círculo en el móvil y un
            recuadro más alto —con el logotipo entero— a partir de ahí. --}}
       class="pulso fixed bottom-24 sm:bottom-28 right-5 z-40 w-14 h-14 rounded-full bg-[#25D366] text-white flex items-center justify-center hover:scale-110 transition-transform">
        <i class="fab fa-whatsapp text-2xl" aria-hidden="true"></i>
    </a>
@endif
