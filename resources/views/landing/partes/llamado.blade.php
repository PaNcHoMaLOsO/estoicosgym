{{-- Rojo plano y sin rayas de fondo: el degradado de tres paradas con la trama
     diagonal encima era puro decorado de plantilla. Y los botones ya no crecen
     al pasar por encima. --}}
<section class="py-8 lg:py-14 bg-pg-rojo">
    <div class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 xl:px-20 text-center animate-on-scroll">
        <h2 class="font-display text-3xl md:text-4xl text-white">¿LISTO PARA EMPEZAR?</h2>
        <p class="text-white/85 text-base mt-3 mb-6 lg:mt-5 lg:mb-9 max-w-2xl mx-auto font-modern">Ven a conocernos o escríbenos: te ayudamos a elegir el plan que te sirve.</p>
        <div class="flex flex-row gap-3 lg:gap-4 justify-center">
            <a href="{{ route('landing.contacto') }}" class="inline-block bg-pg-negro hover:bg-pg-carbon text-pg-tiza flex-1 sm:flex-none text-center font-bold px-4 sm:px-9 py-3 rounded-lg text-sm sm:text-base transition-colors font-modern">Escríbenos</a>
            @if($whatsapp)
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" data-evento="whatsapp_gimnasio" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-pg-tiza text-pg-negro flex-1 sm:flex-none text-center font-bold px-4 sm:px-9 py-3 rounded-lg text-sm sm:text-base transition-colors font-modern">
                    <i class="fab fa-whatsapp text-xl" aria-hidden="true"></i> WhatsApp
                </a>
            @endif
        </div>
    </div>
</section>
