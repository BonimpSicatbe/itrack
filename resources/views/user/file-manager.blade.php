<x-user.app-layout>
    <div class="flex flex-col gap-4 h-full w-full">
        {{-- File Manager Wrapper --}}
        <div class="flex flex-col gap-4 p-4 bg-white rounded-lg h-full">
            {{-- Use the FileManager Livewire component that provides the variables --}}
            @livewire('user.file-manager.file-manager')
        </div>
    </div>
</x-user.app-layout>