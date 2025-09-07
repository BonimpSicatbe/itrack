@if ($paginator->hasPages())
    <div class="flex items-center justify-between mt-4">
        {{-- Results Info --}}
        <div class="text-sm text-gray-700">
            Showing
            <span class="font-medium">{{ $paginator->firstItem() ?? 0 }}</span>
            to
            <span class="font-medium">{{ $paginator->lastItem() ?? 0 }}</span>
            of
            <span class="font-medium">{{ $paginator->total() ?? 0 }}</span>
            results
        </div>

        {{-- Pagination Controls --}}
        <div class="flex items-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1 text-sm text-gray-400 bg-white border-2 border-DEF4C6 rounded-l-xl cursor-default border-r-0">
                    &laquo;
                </span>
            @else
                <button wire:click="previousPage" class="px-3 py-1 text-sm text-1B512D bg-white border-2 border-DEF4C6 rounded-l-xl hover:bg-DEF4C6 transition border-r-0">
                    &laquo;
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="px-3 py-1 text-sm text-gray-400 bg-white border-2 border-DEF4C6 cursor-default border-r-0">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-1 text-sm text-white bg-1C7C54 border-2 border-1C7C54 border-r-0">
                                {{ $page }}
                            </span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" class="px-3 py-1 text-sm text-1B512D bg-white border-2 border-DEF4C6 hover:bg-DEF4C6 transition border-r-0">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" class="px-3 py-1 text-sm text-1B512D bg-white border-2 border-DEF4C6 rounded-r-xl hover:bg-DEF4C6 transition">
                    &raquo;
                </button>
            @else
                <span class="px-3 py-1 text-sm text-gray-400 bg-white border-2 border-DEF4C6 rounded-r-xl cursor-default">
                    &raquo;
                </span>
            @endif
        </div>
    </div>
@else
    {{-- Show results info even when no pagination is needed --}}
    <div class="flex items-center justify-between mt-4">
        <div class="text-sm text-gray-700 font-semibold">
            Showing
            <span class="font-medium">{{ $paginator->firstItem() ?? 0 }}</span>
            to
            <span class="font-medium">{{ $paginator->lastItem() ?? 0 }}</span>
            of
            <span class="font-medium">{{ $paginator->total() ?? 0 }}</span>
            results
        </div>
        {{-- Empty div to maintain justify-between alignment --}}
        <div></div>
    </div>
@endif