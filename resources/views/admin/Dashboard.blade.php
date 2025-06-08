<x-admin.app-layout>
    {{-- overview --}}
    @livewire('admin.overview')

    {{-- accordion --}}
    <div class="flex flex-col gap-4">
        @livewire('admin.tasks')
    </div>
</x-admin.app-layout>
