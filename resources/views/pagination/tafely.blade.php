@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-5">

        <p class="font-body text-xs text-gray-500">
            Affichage de <span class="font-semibold text-gray-700">{{ $paginator->firstItem() }}</span>
            à <span class="font-semibold text-gray-700">{{ $paginator->lastItem() }}</span>
            sur <span class="font-semibold text-gray-700">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1.5">

            {{-- Précédent --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1 px-3 py-2 rounded-full text-xs font-body font-bold text-gray-300 border border-gray-100 cursor-not-allowed select-none">
                    <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                    <span class="hidden sm:inline">Précédent</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex items-center gap-1 px-3 py-2 rounded-full text-xs font-body font-bold text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                    <span class="hidden sm:inline">Précédent</span>
                </a>
            @endif

            {{-- Numéros de page --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="hidden sm:inline-flex px-2 text-xs font-body text-gray-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="inline-flex items-center justify-center h-8 min-w-[32px] px-2.5 rounded-full text-xs font-body font-bold bg-primary-700 text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="hidden sm:inline-flex items-center justify-center h-8 min-w-[32px] px-2.5 rounded-full text-xs font-body font-bold text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Suivant --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex items-center gap-1 px-3 py-2 rounded-full text-xs font-body font-bold text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 transition-colors">
                    <span class="hidden sm:inline">Suivant</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-2 rounded-full text-xs font-body font-bold text-gray-300 border border-gray-100 cursor-not-allowed select-none">
                    <span class="hidden sm:inline">Suivant</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                </span>
            @endif
        </div>
    </nav>
@endif