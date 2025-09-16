<x-user.app-layout>
    <div class="flex flex-col gap-4 h-full w-full overflow-y-auto">
        {{-- Active Semester Card --}}
        @livewire('user.dashboard.active-semester')

        {{-- overview --}}
        @livewire('user.dashboard.overview')

        {{-- Conditionally display sections only when there's an active semester --}}
        @if(\App\Models\Semester::getActiveSemester())
            {{-- progress --}}
            @livewire('user.dashboard.progress')

            {{-- pendings --}}
            @livewire('user.dashboard.pending')

            {{-- recents --}}
            @livewire('user.dashboard.recent')
        @endif
    </div>
</x-user.app-layout>