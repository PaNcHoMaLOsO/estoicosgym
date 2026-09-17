{{-- La cabecera de cada pagina: el titulo que se lee primero. --}}
{{-- Sin la mancha de color borrosa de la esquina: era humo, y es de lo que hace
     que la página se vea hecha con plantilla. --}}
<section class="pt-36 pb-16 bg-pg-negro">
    {{-- Centrado: alineado a la izquierda, en una pantalla ancha dejaba media
         pantalla vacía a la derecha. --}}
    <div class="max-w-[1520px] mx-auto px-4 sm:px-6 lg:px-8 text-center fade-in">
        <p class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">{{ $antetitulo }}</p>
        <h1 class="font-display text-5xl md:text-7xl text-pg-tiza mt-3 uppercase">{{ $titulo }}</h1>
        @if(!empty($bajada))
            <p class="text-pg-tiza/65 mt-5 max-w-3xl mx-auto font-modern text-lg">{{ $bajada }}</p>
        @endif
    </div>
</section>
