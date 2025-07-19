<div class="flex flex-col gap-4 w-full bg-white rounded-lg">
    {{-- header (action header) --}}
    <div class="flex flex-row items-center gap-4 w-full text-nowrap">
        <div class="text-lg font-bold uppercase">Requirement Lists</div>
        <div class="grow"></div>
        <input type="text" name="search" id="search" class="input input-bordered input-sm max-w-2xl"
            placeholder="Seaarch by requirement name" wire:model.live="search">
        {{-- <button class="btn btn-sm btn-success btn-ghost">
            <i class="fa-solid fa-plus sm:hidden"></i>
            <span class="sm:inline hidden">Create Requirement</span>
        </button> --}}
        <label for="create_requirement_modal" class="btn btn-sm btn-success btn-ghost">
            <i class="fa-solid fa-plus sm:hidden"></i>
            <span class="sm:inline hidden">Create Requirement</span>
        </label>
    </div>

    {{-- show requirement list --}}
    <div class="overflow-auto max-h-[500px]">
        <x-table class="table-md">
            <x-table.head>
                <x-table.row>
                    <x-table.header>name</x-table.header>
                    <x-table.header>description</x-table.header>
                    <x-table.header>due</x-table.header>
                    <x-table.header>assigned_to</x-table.header>
                    <x-table.header>status</x-table.header>
                    <x-table.header>priority</x-table.header>
                    <x-table.header>created_by</x-table.header>
                </x-table.row>
            </x-table.head>
            <x-table.body>
                @forelse ($requirements as $requirement)
                    <x-table.row wire:click='viewRequirement({{ $requirement->id }})'
                        class="hover:bg-base-200 cursor-pointer">
                        <x-table.cell>{{ $requirement->name }}</x-table.cell>
                        <x-table.cell>{{ $requirement->description }}</x-table.cell>
                        <x-table.cell>{{ $requirement->due->format('M d, Y - h:i a') }}</x-table.cell>
                        <x-table.cell>{{ $requirement->assigned_to }}</x-table.cell>
                        <x-table.cell>{{ $requirement->status }}</x-table.cell>
                        <x-table.cell>{{ $requirement->priority }}</x-table.cell>
                        <x-table.cell>{{ $requirement->createdBy->full_name ?? 'N/A' }}</x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan='7' class="text-gray-500 text-center">No Requirements
                            Found</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>

    {{-- total count of requirements (optional) --}}
    {{-- <div class="flex flex-row justify-between items-center gap-4">
        <div class="text-lg font-black capitalize flex flex-col gap-0 w-full">
            <span class="text-xs text-gray-500">total: </span>
            <span class="rounded-lg p-2 bg-gray-100">{{ $requirements->count() }}</span>
        </div>
        <div class="text-lg font-black capitalize flex flex-col gap-0 w-full">
            <span class="text-xs text-gray-500">pending: </span>
            <span class="rounded-lg p-2 bg-gray-100">{{ $requirements->where('status', 'pending')->count() }}</span>
        </div>
        <div class="text-lg font-black capitalize flex flex-col gap-0 w-full">
            <span class="text-xs text-gray-500">completed: </span>
            <span class="rounded-lg p-2 bg-gray-100">{{ $requirements->where('status', 'completed')->count() }}</span>
        </div>
        <div class="text-lg font-black capitalize flex flex-col gap-0 w-full">
            <span class="text-xs text-gray-500">high: </span>
            <span class="rounded-lg p-2 bg-gray-100">{{ $requirements->where('priority', 'high')->count() }}</span>
        </div>
        <div class="text-lg font-black capitalize flex flex-col gap-0 w-full">
            <span class="text-xs text-gray-500">low: </span>
            <span class="rounded-lg p-2 bg-gray-100">{{ $requirements->where('priority', 'low')->count() }}</span>
        </div>
        <div class="text-lg font-black capitalize flex flex-col gap-0 w-full">
            <span class="text-xs text-gray-500">normal: </span>
            <span class="rounded-lg p-2 bg-gray-100">{{ $requirements->where('priority', 'normal')->count() }}</span>
        </div>
    </div> --}}

    <input type="checkbox" id="create_requirement_modal" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box">
            <div class="flex flex-row justify-between items-center gap-4">
                <h3 class="text-lg font-bold">Hello!</h3>
                <div class="modal-action"><label for="create_requirement_modal"
                        class="btn btn-sm btn-circle btn-ghost btn-default"><i class="fa-solid fa-xmark"></i></label>
                </div>
            </div>
            <p class="py-4">This modal works with a hidden checkbox!</p>
        </div>
        <label class="modal-backdrop" for="create_requirement_modal"></label>
    </div>
</div>
