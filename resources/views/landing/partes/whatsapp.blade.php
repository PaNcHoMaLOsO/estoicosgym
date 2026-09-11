{{-- El WhatsApp del gimnasio, flotando en todas las paginas. Sale de Configuracion -> Web. --}}
@if($whatsapp)
    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" data-evento="whatsapp_gimnasio" aria-label="Escríbenos por WhatsApp"
       class="pulso fixed bottom-5 right-5 z-40 w-14 h-14 rounded-full bg-[#25D366] text-white shadow-xl shadow-black/40 flex items-center justify-center hover:scale-110 transition-transform">
        <i class="fab fa-whatsapp text-3xl" aria-hidden="true"></i>
    </a>
@endif
