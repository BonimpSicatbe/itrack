<x-user.app-layout>
    <div class="flex flex-col gap-4 h-fit w-full">
        {{-- Active Semester Card --}}
        @livewire('user.dashboard.active-semester')

        {{-- overview --}}
        @livewire('user.dashboard.overview')

        {{-- progress --}}
        @livewire('user.dashboard.progress')

        {{-- pendings --}}
        @livewire('user.dashboard.pending')

        {{-- recents --}}
        @livewire('user.dashboard.recent')

        
    </div>
</x-user.app-layout>