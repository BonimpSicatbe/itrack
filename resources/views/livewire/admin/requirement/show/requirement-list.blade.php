<div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
    <div class="text-lg font-bold uppercase">User Requirements</div>
    <div class="overflow-auto max-h-[500px] rounded-lg shadow-md">
        <x-table>
            <x-table.head>
                <x-table.row>
                    <x-table.header>name</x-table.header>
                    <x-table.header>description</x-table.header>
                    <x-table.header>due</x-table.header>
                    <x-table.header>assigned_to</x-table.header>
                    <x-table.header>status</x-table.header>
                    <x-table.header>priority</x-table.header>
                    <x-table.header>created_by</x-table.header>
                    <x-table.header>updated_by</x-table.header>
                    <x-table.header>archived_by</x-table.header>
                </x-table.row>
            </x-table.head>
            <x-table.body>
                @forelse ($this->requirements as $requirement)
                    <x-table.row wire:click='viewRequirement({{ $requirement->id }})' class="hover:bg-base-200 cursor-pointer">
                        <x-table.cell>{{ $requirement->name }}</x-table.cell>
                        <x-table.cell>{{ $requirement->description }}</x-table.cell>
                        <x-table.cell>{{ $requirement->due }}</x-table.cell>
                        <x-table.cell>{{ $requirement->assigned_to }}</x-table.cell>
                        <x-table.cell>{{ $requirement->status }}</x-table.cell>
                        <x-table.cell>{{ $requirement->priority }}</x-table.cell>
                        <x-table.cell>{{ $requirement->created_by ?? 'N/A' }}</x-table.cell>
                        <x-table.cell>{{ $requirement->updated_by ?? 'N/A' }}</x-table.cell>
                        <x-table.cell>{{ $requirement->archived_by ?? 'N/A' }}</x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell>User Does Not Have Any Requirements</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>
</div>
