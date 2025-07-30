{{--
    // TODO add priority option to requirement creation
    // TODO make the due date format to date time
--}}

<div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
    {{-- Header and table content remains the same --}}
    <div class="text-lg font-bold uppercase">Requirements</div>
    <div class="flex flex-row gap-4 w-full">
        <input type="text" wire:model.live="search" id="search" class="input input-sm input-bordered w-128"
            placeholder="Search requirement name">
        <div class="grow"></div>
        <label for="createRequirement" class="btn btn-sm btn-default">Create Requirement</label>
    </div>

    {{-- content --}}
    <div class="overflow-auto max-h-[500px] rounded-lg shadow-md">
        <x-table>
            <x-table.head>
                <x-table.row>
                    <x-table.header sortable :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">name</x-table.header>
                    <x-table.header>description</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'due' ? $sortDirection : null" wire:click="sortBy('due')">due</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'assigned_to' ? $sortDirection : null" wire:click="sortBy('assigned_to')">assigned to</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'status' ? $sortDirection : null" wire:click="sortBy('status')">status</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'priority' ? $sortDirection : null" wire:click="sortBy('priority')">priority</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'created_by' ? $sortDirection : null" wire:click="sortBy('created_by')">created by</x-table.header>
                </x-table.row>
            </x-table.head>
            <x-table.body>
                @forelse ($requirements as $requirement)
                    <x-table.row wire:click='showRequirement({{ $requirement->id }})'
                        wire:loading.class.delay="opacity-50" class="hover:bg-base-200 cursor-pointer">
                        <x-table.cell>{{ $requirement->name }}</x-table.cell>
                        <x-table.cell>{{ $requirement->description }}</x-table.cell>
                        <x-table.cell>{{ \Carbon\Carbon::parse($requirement->due)->format('F d, Y') }}</x-table.cell>
                        <x-table.cell>{{ $requirement->assigned_to }}</x-table.cell>
                        <x-table.cell>{{ $requirement->status }}</x-table.cell>
                        <x-table.cell>{{ $requirement->priority }}</x-table.cell>
                        <x-table.cell>{{ $requirement->createdBy?->firstname }}
                            {{ $requirement->createdBy?->lastname }}</x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="7">
                            <div
                                class="flex font-bold h-full items-center justify-center p-4 text-center text-lg truncate w-full text-gray-500">
                                No Results Found
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>

    <div class="w-full text-center">
        {{ $requirements->links() }}
    </div>

    {{-- Include the modal component --}}
    @livewire('admin.requirement-create-modal')

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('close-modal', () => {
                document.getElementById('createRequirement').checked = false;
            });
        });
    </script>
</div>