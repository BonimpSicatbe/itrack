<x-user.app-layout>
    <div class="flex flex-col gap-4 h-fit w-full">
        {{-- Pending Requirements Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Pending Requirements</h1>
            <div>
                @livewire('user.requirements.calendar-button')
            </div>
        </div>

        {{-- Main Pending Requirements Section --}}
        @livewire('user.requirements.requirements-list')

        {{-- Additional Sections --}}
        {{-- @livewire('user.requirements.related-files') --}}
        {{-- @livewire('user.requirements.calendar-view') --}}
    </div>
</x-user.app-layout>