<div class="flex flex-col gap-4 p-4">
    {{-- header (action header) --}}
    <div class="text-lg font-black uppercase">Uploaded Required Files</div>
    {{-- body (file list) --}}
    <div class="overflow-auto max-h-[500px]">
        <x-table class="table-md">
            <x-table.head>
                <x-table.row>
                    <x-table.header>File Name</x-table.header>
                    <x-table.header>File Size</x-table.header>
                    <x-table.header>Collection Name</x-table.header>
                    <x-table.header>Uploaded At</x-table.header>
                </x-table.row>
            </x-table.head>
            <x-table.body>
                @forelse ($requirement->getMedia('requirementRequiredFiles') as $file)
                    <x-table.row class="hover:bg-base-200 cursor-pointer" wire:click='viewFile({{ $file->id }})'>
                        <x-table.cell>{{ $file->name }}</x-table.cell>
                        <x-table.cell>{{ $file->size }}</x-table.cell>
                        <x-table.cell>{{ $file->collection_name }}</x-table.cell>
                        <x-table.cell>{{ $file->created_at->format('M d, Y h:i A') }}</x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan='3' class="text-gray-500 text-center">No Required Files Uploaded</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>
</div>
