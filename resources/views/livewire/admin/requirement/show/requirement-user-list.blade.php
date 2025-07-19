<div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
    <div class="text-lg font-bold uppercase">Requirement User List</div>
    <div class="max-h-[500px] overflow-auto rounded-lg shadow-lg">
        <x-table>
            <x-table.head>
                <x-table.row>
                    <x-table.header>Name</x-table.header>
                    <x-table.header>Email</x-table.header>
                    <x-table.header>College</x-table.header>
                    <x-table.header>Department</x-table.header>
                </x-table.row>
            </x-table.head>
            <x-table.body>
                @forelse ($assignedUsers as $user)
                    <x-table.row wire:click='viewUser({{ $user->id }})' class="hover:bg-base-200 cursor-pointer">
                        <x-table.cell>{{ $user->full_name }}</x-table.cell>
                        <x-table.cell>{{ $user->email }}</x-table.cell>
                        <x-table.cell>{{ $user->college->name ?? 'N/A' }}</x-table.cell>
                        <x-table.cell>{{ $user->department->name ?? 'N/A' }}</x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="4" class="text-center">No users assigned.</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>
</div>
