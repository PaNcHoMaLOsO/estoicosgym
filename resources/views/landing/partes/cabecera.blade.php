{{-- La cabecera de cada pagina: el titulo que se lee primero. --}}
<section class="relative pt-36 pb-14 bg-pg-negro overflow-hidden">
    <div class="absolute -top-24 -right-24 w-[28rem] h-[28rem] bg-pg-rojo/10 rounded-full blur-3xl brillo" aria-hidden="true"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 fade-in">
        <p class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">{{ $antetitulo }}</p>
        <h1 class="font-display text-5xl md:text-7xl text-pg-tiza mt-3 uppercase">{{ $titulo }}</h1>
        @if(!empty($bajada))
            <p class="text-pg-tiza/65 mt-5 max-w-2xl font-modern text-lg">{{ $bajada }}</p>
        @endif
    </div>
</section>
