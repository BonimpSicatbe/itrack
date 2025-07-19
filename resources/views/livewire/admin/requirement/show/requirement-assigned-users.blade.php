<div class="flex flex-col gap-4 p-4">
    {{-- header (action header) --}}
    <div class="text-lg font-black uppercase">Assigned Users</div>

    {{-- body (user list) --}}
    <div class="overflow-auto max-h-[500px]">
        <x-table class="table-md">
            <x-table.head>
                <x-table.row>
                    <x-table.header>Name</x-table.header>
                    <x-table.header>College</x-table.header>
                    <x-table.header>Department</x-table.header>
                    <x-table.header>Email</x-table.header>
                </x-table.row>
            </x-table.head>
            <x-table.body>
                @forelse ($requirement->assignedTargets() as $user)
                    <x-table.row class="hover:bg-base-200 cursor-pointer" wire:click='viewUser({{ $user->id }})'>
                        <x-table.cell>{{ $user->full_name }}</x-table.cell>
                        <x-table.cell>{{ $user->college->name }}</x-table.cell>
                        <x-table.cell>{{ $user->department->name }}</x-table.cell>
                        <x-table.cell>{{ $user->email }}</x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan='3' class="text-gray-500 text-center">No Users Assigned</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>
</div>
