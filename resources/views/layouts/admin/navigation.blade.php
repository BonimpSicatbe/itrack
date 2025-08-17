<!-- resources/views/layouts/admin/navigation.blade.php -->
<nav class="bg-gray">
    <div class="container mx-auto px-6 pt-6 pb-3">
        <ul class="w-full flex flex-row gap-2 items-center flex-wrap">
            @foreach ($navLinks as $index => $navlink)
                @if ($index === count($navLinks) - 3)
                    <li class="flex-1"></li>
                @endif

                <li>
                    @if (isset($navlink['is_logout']) && $navlink['is_logout'])
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-ghost capitalize flex items-center gap-2">
                                <i class="fa-solid fa-{{ $navlink['icon'] }}"></i>
                                <span class="hidden sm:inline">{{ $navlink['label'] }}</span>
                            </button>
                        </form>
                    @else
                        <a
                            href="{{ route($navlink['route']) }}"
                            @if(!str_contains($navlink['route'], 'requirements'))
                                wire:navigate
                            @endif
                            @class([
                                'btn btn-sm capitalize flex items-center gap-2',
                                'btn-active' => $isActive($navlink['route']),
                                'btn-ghost' => !$isActive($navlink['route'])
                            ])
                        >
                            <i class="fa-solid fa-{{ $navlink['icon'] }}"></i>
                            <span class="hidden sm:inline">{{ $navlink['label'] }}</span>
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</nav>

@push('scripts')
<script>
    document.addEventListener('livewire:navigated', () => {
        // Re-initialize Livewire components after navigation
        Livewire.rescan();
    });
</script>
@endpush