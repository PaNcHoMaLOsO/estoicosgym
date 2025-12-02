@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegación de páginas" class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
        {{-- Información de resultados --}}
        <div class="pagination-info text-muted small">
            Mostrando {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }} resultados
        </div>
        
        <ul class="pagination pagination-sm mb-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-hidden="true">
                        <i class="fas fa-chevron-left" style="font-size: 10px;"></i>
                        <span class="d-none d-sm-inline ml-1">Anterior</span>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior">
                        <i class="fas fa-chevron-left" style="font-size: 10px;"></i>
                        <span class="d-none d-sm-inline ml-1">Anterior</span>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente">
                        <span class="d-none d-sm-inline mr-1">Siguiente</span>
                        <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-hidden="true">
                        <span class="d-none d-sm-inline mr-1">Siguiente</span>
                        <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
    
    <style>
        .pagination-info {
            color: #6c757d;
            font-size: 0.85rem;
        }
        .pagination .page-link {
            border-radius: 6px !important;
            margin: 0 2px;
            padding: 6px 12px;
            font-size: 0.85rem;
            color: #1a1a2e;
            border: 1px solid #dee2e6;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }
        .pagination .page-link:hover {
            background-color: #e94560;
            border-color: #e94560;
            color: #fff;
        }
        .pagination .page-item.active .page-link {
            background-color: #1a1a2e;
            border-color: #1a1a2e;
            color: #fff;
        }
        .pagination .page-item.disabled .page-link {
            color: #adb5bd;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        .pagination .page-link i {
            font-size: 10px !important;
            line-height: 1;
        }
    </style>
@endif
