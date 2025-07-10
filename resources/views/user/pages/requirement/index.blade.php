{{--
    show user requirement list
--}}

<x-user.app-layout>
    <div class="flex flex-col gap-4 h-full w-full">
        <div class="flex flex-col gap-4 p-4 bg-white rounded-lg">
            {{-- header --}}
            <div class="text-lg uppercase font-bold">Requirements</div>

            {{-- requirement list --}}
            <div class="rounded-lg shadow-md overflow-hidden">
                <x-table>
                    <x-table.head>
                        <x-table.row>
                            <x-table.header>Name</x-table.header>
                            <x-table.header>Assigned By</x-table.header>
                            <x-table.header>Due Date</x-table.header>
                            <x-table.header>Status</x-table.header>
                            <x-table.header>Uploaded At</x-table.header>
                        </x-table.row>
                    </x-table.head>
                    <x-table.body>
                        @forelse ($requirements as $requirement)
                            <x-table.row>
                                <x-table.cell>{{ $requirement->name }}</x-table.cell>
                                <x-table.cell>{{ $requirement->createdBy->full_name }}</x-table.cell>
                                <x-table.cell>{{ $requirement->due }}</x-table.cell>
                                <x-table.cell>{{ $requirement->status }}</x-table.cell>
                                <x-table.cell>{{ $requirement->created_at }}</x-table.cell>
                            </x-table.row>
                        @empty
                            <x-table.row>
                                <x-table.cell>No Uploaded Requirements</x-table.cell>
                            </x-table.row>
                        @endforelse
                    </x-table.body>
                </x-table>

            </div>
        </div>
    </div>
</x-user.app-layout>
