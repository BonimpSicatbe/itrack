<x-user.app-layout>
    <div class="flex flex-col gap-4 h-fit w-full">
        {{-- overview --}}
        @livewire('user.dashboard.overview')

        {{-- pendings --}}
        @livewire('user.dashboard.pending')

        {{-- recents --}}
        {{-- @livewire('user.dashboard.recent') --}}

        {{-- progresss --}}
        @livewire('user.dashboard.progress')
    </div>
</x-user.app-layout>
