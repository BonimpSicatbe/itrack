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

            {{-- body / table --}}
            <div class="max-h-[500px] overflow-x-auto">
                <table class="table table-auto table-striped table-pin-rows table-sm min-w-[600px]">
                    <thead>
                        <tr class="bg-base-300 font-bold uppercase">
                            <th>name</th>
                            <th>description</th>
                            <th>due date</th>
                            <th>status</th>
                            <th>priority</th>
                            <th>created at</th>
                            <th>action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requirements as $requirement)
                            <tr>
                                <td class="truncate max-w-[250px]">{{ $requirement->name }}</td>
                                <td class="truncate max-w-[250px]">{{ $requirement->description }}</td>
                                <td class="truncate">{{ \Carbon\Carbon::parse($requirement->due)->format('m/d/Y h:i a') }}
                                </td>
                                <td class="truncate">{{ $requirement->status }}</td>
                                <td class="truncate">{{ $requirement->priority }}</td>
                                <td class="truncate">
                                    {{ \Carbon\Carbon::parse($requirement->created_at)->format('m/d/Y h:i a') }}</td>
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
                                <td colspan="7" class="text-center">No requirements found.</td>
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