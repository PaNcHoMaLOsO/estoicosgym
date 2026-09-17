{{-- La cabecera de cada pagina: el titulo que se lee primero. --}}
{{-- Sin la mancha de color borrosa de la esquina: era humo, y es de lo que hace
     que la página se vea hecha con plantilla. --}}
{{-- `compacta`: para las páginas donde lo que importa viene debajo —los planes—
     y el título no puede comerse media pantalla del teléfono. --}}
<section class="pt-20 lg:pt-28 {{ !empty($compacta) ? 'pb-6 lg:pb-8' : 'pb-12' }} bg-pg-negro">
    {{-- Centrado: alineado a la izquierda, en una pantalla ancha dejaba media
         pantalla vacía a la derecha. --}}
    <div class="max-w-[1520px] mx-auto px-5 sm:px-8 lg:px-12 xl:px-20 text-center fade-in">
        <p class="text-pg-rojo-claro font-modern tracking-widest uppercase text-sm">{{ $antetitulo }}</p>
        <h1 class="font-display {{ !empty($compacta) ? 'text-3xl md:text-4xl mt-2' : 'text-4xl md:text-5xl mt-3' }} text-pg-tiza uppercase">{{ $titulo }}</h1>
        @if(!empty($bajada))
            <p class="text-pg-tiza/65 {{ !empty($compacta) ? 'mt-3 text-sm md:text-base' : 'mt-5 text-base' }} max-w-3xl mx-auto font-modern">{{ $bajada }}</p>
        @endif
    </div>
</section>
