<ul class="p-4 bg-white w-full rounded-lg shadow flex flex-row gap-4 items-center">
    @foreach ($navLinks as $index => $navlink)
        <li class="@if($index >= count($navLinks) - 3) ml-auto @endif">
            <a href="{{ $navlink['url'] }}" class="btn btn-sm btn-ghost capitalize">
                <i class="{{ $navlink['icon'] }}"></i>
                {{ $navlink['label'] }}
            </a>
        </li>
    @endforeach
</ul>
