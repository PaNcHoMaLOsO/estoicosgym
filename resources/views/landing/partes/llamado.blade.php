<section class="relative py-24 bg-linear-to-r from-pg-rojo-oscuro via-pg-rojo to-pg-rojo-oscuro overflow-hidden">
    <div class="absolute inset-0 franjas opacity-25" aria-hidden="true"></div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center animate-on-scroll">
        <h2 class="font-display text-4xl md:text-6xl text-white mb-6">¿LISTO PARA EMPEZAR?</h2>
        <p class="text-white/85 text-lg mb-10 max-w-2xl mx-auto font-modern">Ven a conocernos o escríbenos: te ayudamos a elegir el plan que te sirve.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('landing.contacto') }}" class="inline-block bg-pg-negro hover:bg-pg-carbon text-pg-tiza font-bold px-10 py-4 rounded-lg text-lg transition-all hover:scale-105 font-modern">Escríbenos</a>
            @if($whatsapp)
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" data-evento="whatsapp_gimnasio" class="inline-flex items-center justify-center gap-2 bg-white text-pg-negro font-bold px-10 py-4 rounded-lg text-lg transition-all hover:scale-105 font-modern">
                    <i class="fab fa-whatsapp text-xl" aria-hidden="true"></i> WhatsApp
                </a>
            @endif
        </div>
    </div>
</section>
