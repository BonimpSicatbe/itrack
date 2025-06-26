<th {{ $attributes->merge(['class' => 'bg-base-300 px-4 py-2 text-left capitalize' . ($attributes->has('sortable') ? ' cursor-pointer' : '') . ' select-none']) }}>
    <span class="flex items-center">
        {{ $slot }}

        @if ($attributes->has('sortable'))
            @if ($direction === 'asc')
                <svg class="ml-1 w-3 h-3 inline-block" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 16 16">
                    <path d="M4 10l4-4 4 4" />
                </svg>
            @elseif ($direction === 'desc')
                <svg class="ml-1 w-3 h-3 inline-block" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 16 16">
                    <path d="M4 6l4 4 4-4" />
                </svg>
            @else
                <svg class="ml-1 w-3 h-3 inline-block" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 16 16">
                    <path d="M4 10l4 4 4-4M4 6l4-4 4 4" />
                </svg>
            @endif
        @endif
    </span>
</th>
