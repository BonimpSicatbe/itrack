<div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
    {{-- header title --}}
    <div class="text-lg font-bold uppercase">Pendings</div>
    {{-- header actions --}}
    <div class="flex flex-row gap-4 w-full">
        {{-- filters --}}
        <input type="text" wire:model.live="search" id="search" class="input input-sm input-bordered w-128"
            placeholder="Search pendings">
    </div>

    {{-- content --}}
    <div class="overflow-x-auto max-h-[500px] rounded-lg shadow-md">
        <x-table>
            <x-table.head>
                <x-table.row>
                    <x-table.header sortable :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')">name</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'target' ? $sortDirection : null" wire:click="sortBy('target')">assigned to</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'target_id' ? $sortDirection : null" wire:click="sortBy('target_id')">assigned targets</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'status' ? $sortDirection : null" wire:click="sortBy('status')">status</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'priority' ? $sortDirection : null" wire:click="sortBy('priority')">priority</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'due' ? $sortDirection : null" wire:click="sortBy('due')">due</x-table.header>
                    <x-table.header sortable :direction="$sortField === 'created_by' ? $sortDirection : null" wire:click="sortBy('created_by')">created by</x-table.header>
                </x-table.row>
            </x-table.head>
            <x-table.body>
                @forelse ($pendings as $pending)
                    <x-table.row wire:loading.class.delay="opacity-50">
                        <x-table.cell>{{ $pending->name }}</x-table.cell>
                        <x-table.cell>{{ $pending->assigned_to }}</x-table.cell>
                        <x-table.cell>{{ $pending->assignedTargets()->count() }}</x-table.cell>
                        <x-table.cell>{{ $pending->status }}</x-table.cell>
                        {{-- <x-table.cell>{{ $pending->priority }}</x-table.cell> --}}
                        <x-table.cell>{{ $pending->priority }}</x-table.cell>
                        <x-table.cell>{{ \Carbon\Carbon::parse($pending->due)->format('F d, Y') }}</x-table.cell>
                        <x-table.cell>{{ $pending->createdBy?->firstname }}
                            {{ $pending->createdBy?->lastname }}</x-table.cell>
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
        {{ $pendings->links() }}
    </div>
</div>
