@php
    $unreadCount = auth()->user()->unreadNotifications->count();
@endphp

<div class="relative">
    <x-icon name="bell" class="w-5 h-5" />
    @if($unreadCount > 0)
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
            {{ $unreadCount }}
        </span>
    @endif
</div>