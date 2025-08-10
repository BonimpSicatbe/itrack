<div class="flex flex-col gap-6 w-full">
    <div class="w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        {{-- requirements table list --}}
        <div class="flex flex-col gap-4 w-full h-full">
            {{-- header / actions --}}
            <div class="flex flex-col sm:flex-row flex-wrap items-center justify-between gap-4 w-full">
                <h2 class="text-lg font-semibold w-full sm:w-auto sm:text-left">Requirements List</h2>
                <div class="flex flex-row gap-4 w-full sm:w-auto justify-center sm:justify-end">
                    <input type="text" wire:model.live="search" id="search" class="input input-bordered input-sm w-full sm:w-sm"
                        placeholder="Search requirements...">
                    <label for="createRequirement" class="btn btn-sm btn-success flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span class="hidden sm:inline">Create Requirement</span>
                    </label>
                </div>
            </div>

            {{-- filter controls --}}
            <div class="flex flex-col md:flex-row gap-4 w-full">
                <div class="flex-1">
                    <label class="label">Status</label>
                    <select wire:model.live="sortStatus" class="select select-bordered select-sm w-full">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                
                <div class="flex-1">
                    <label class="label">Assigned To</label>
                    <select wire:model.live="sortAssignedTo" class="select select-bordered select-sm w-full">
                        <option value="">All Assignments</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->name }}">College: {{ $college->name }}</option>
                        @endforeach
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}">Dept: {{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex-1">
                    <label class="label">Due Date</label>
                    <select wire:model.live="sortDueDate" class="select select-bordered select-sm w-full">
                        <option value="asc">Oldest First</option>
                        <option value="desc">Newest First</option>
                    </select>
                </div>
            </div>

            {{-- body / table --}}
            <div class="max-h-[500px] overflow-x-auto">
                <table class="table table-auto table-striped table-pin-rows table-sm min-w-[600px]">
                    <thead>
                        <tr class="bg-base-300 font-bold uppercase">
                            <th>Name</th>
                            <th>Description</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Assigned To</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requirements as $requirement)
                            <tr>
                                <td class="truncate max-w-[150px]">{{ $requirement->name }}</td>
                                <td class="truncate max-w-[150px]">{{ $requirement->description }}</td>
                                <td class="truncate">{{ $requirement->due->format('m/d/Y h:i a') }}</td>
                                <td class="truncate">
                                    <span class="badge badge-{{ $requirement->status_color }}">
                                        {{ $requirement->status }}
                                    </span>
                                </td>
                                <td class="truncate">
                                    <span class="badge badge-{{ $requirement->priority_color }}">
                                        {{ $requirement->priority }}
                                    </span>
                                </td>
                                <td class="truncate">{{ $requirement->assigned_to }}</td>
                                <td class="truncate">{{ $requirement->created_at->format('m/d/Y h:i a') }}</td>
                                <td class="flex flex-row gap-2 truncate">
                                    <a href="{{ route('admin.requirements.show', ['requirement' => $requirement]) }}"
                                        class="btn btn-xs btn-ghost btn-success">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <form wire:submit.live='deleteRequirement({{ $requirement->id }})'>
                                        <button type="submit" class="btn btn-xs btn-ghost btn-error">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement]) }}"
                                        class="btn btn-xs btn-ghost btn-info">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No requirements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Include the create modal component --}}
    @livewire('admin.requirement-create-modal')
</div>