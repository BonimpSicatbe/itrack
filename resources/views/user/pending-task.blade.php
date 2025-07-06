<x-user.app-layout>
    <div class="flex flex-col gap-4 h-fit w-full">
        {{-- Pending Requirements Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Pending Requirements</h1>
            <div>
                @livewire('user.pending-task.calendar-button')
            </div>
        </div>

        {{-- Main Pending Requirements Section --}}
        @livewire('user.pending-task.requirements-list')

        {{-- Additional Sections --}}
        {{-- @livewire('user.pending-task.related-files') --}}
        {{-- @livewire('user.pending-task.calendar-view') --}}
    </div>
</x-user.app-layout>