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
    <div class="overflow-x-auto max-h-[500px]">
        <table class="table table-fixed text-nowrap w-full">
            <thead>
                <tr class="capitalize">
                    <th class="cursor-pointer" wire:click="sortBy('name')">
                        Name
                        @if ($sortField === 'name')
                            <i
                                class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} min-w-[20px] text-center"></i>
                        @else
                            <i class="fa-solid fa-sort min-w-[20px] text-center"></i>
                        @endif
                    </th>
                    <th class="cursor-pointer" wire:click="sortBy('assigned_to')">
                        assigned to
                        @if ($sortField === 'assigned_to')
                            <i
                                class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} min-w-[20px] text-center"></i>
                        @else
                            <i class="fa-solid fa-sort min-w-[20px] text-center"></i>
                        @endif
                    </th>
                    <th class="cursor-pointer" wire:click="sortBy('assigned_targets')">
                        assigned targets
                        @if ($sortField === 'assigned_targets')
                            <i
                                class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} min-w-[20px] text-center"></i>
                        @else
                            <i class="fa-solid fa-sort min-w-[20px] text-center"></i>
                        @endif
                    </th>
                    <th class="cursor-pointer" wire:click="sortBy('status')">
                        status
                        @if ($sortField === 'status')
                            <i
                                class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} min-w-[20px] text-center"></i>
                        @else
                            <i class="fa-solid fa-sort min-w-[20px] text-center"></i>
                        @endif
                    </th>
                    <td>progress</td>
                    <th class="cursor-pointer" wire:click="sortBy('priority')">
                        priority
                        @if ($sortField === 'priority')
                            <i
                                class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} min-w-[20px] text-center"></i>
                        @else
                            <i class="fa-solid fa-sort min-w-[20px] text-center"></i>
                        @endif
                    </th>
                    <th class="cursor-pointer" wire:click="sortBy('due')">
                        due date
                        @if ($sortField === 'due')
                            <i
                                class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} min-w-[20px] text-center"></i>
                        @else
                            <i class="fa-solid fa-sort min-w-[20px] text-center"></i>
                        @endif
                    </th>
                    <th class="cursor-pointer" wire:click="sortBy('created_by')">
                        created by
                        @if ($sortField === 'created_by')
                            <i
                                class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} min-w-[20px] text-center"></i>
                        @else
                            <i class="fa-solid fa-sort min-w-[20px] text-center"></i>
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendings as $pending)
                    <tr>
                        <td class="truncate">{{ $pending->name }}</td>
                        <td class="truncate">
                            {{ $pending->target === 'department'
                                ? optional(\App\Models\Department::where('id', $pending->target_id)->first())->name
                                : optional(\App\Models\College::where('id', $pending->target_id)->first())->name }}
                        </td>
                        <td class="truncate">{{ $pending->target === 'department'
                            ? optional(\App\Models\Department::where('id', $pending->target_id)->first())->users->count()
                            : optional(\App\Models\College::where('id', $pending->target_id)->first())->users->count() }}
                        </td>
                        <td class="truncate">{{ $pending->status }}</td>
                        <td class="truncate"><progress class="progress progress-primary w-full" value="10" max="100"></progress>
                        </td>
                        <td class="truncate">{{ $pending->priority }}</td>
                        <td class="truncate">{{ \Carbon\Carbon::parse($pending->due)->format('F j, Y') }}</td>
                        <td class="truncate">{{ $pending->createdBy->firstname }} {{ $pending->createdBy->middlename }}
                            {{ $pending->createdBy->lastname }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
