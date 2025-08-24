<x-user.app-layout>
    <div class="flex flex-col gap-4 h-fit w-full">
        {{-- Main Requirements Section with Header Included --}}
        @livewire('user.requirements.requirements-list')

        {{-- Additional Sections --}}
        {{-- @livewire('user.requirements.related-files') --}}
        {{-- @livewire('user.requirements.calendar-view') --}}
    </div>
</x-user.app-layout>