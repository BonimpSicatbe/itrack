<x-user.app-layout>
    <div class="flex flex-col gap-4 h-fit w-full">
        {{-- Recent Submissions Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Recent Submissions</h1>
        </div>

        {{-- Main Recent Submissions Section --}}
        @livewire('user.recents.recent-submissions-list')
    </div>
</x-user.app-layout>