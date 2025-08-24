<div class="flex flex-col w-full gap-6">
    @if($activeSemester)
        <div class="flex flex-col sm:flex-row gap-4 w-full items-start sm:items-center">
            <div class="relative w-full sm:w-96">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" wire:model.live="search" id="search" 
                    class="input input-sm input-bordered pl-9 w-full" 
                    placeholder="Search requirements by name...">
            </div>
            
            <div class="grow"></div>
            
            <!-- Redirect Button -->
            <a href="{{ route('admin.requirements.index') }}" 
               class="btn bg-success btn-sm flex items-center gap-2">
                <i class="fa-solid fa-arrow-right"></i>
                Go to Requirements Page
            </a>
        </div>

        {{-- content --}}
        <div class="max-h-[500px] overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="table table-auto table-striped table-pin-rows table-sm min-w-[800px] rounded-lg"> 
                <thead>
                    <tr class="bg-base-300 font-bold uppercase">
                        <th class="cursor-pointer hover:bg-gray-100 p-4" wire:click="sortBy('name')" style="background-color: #6a994e; color: white;">
                            <div class="flex items-center pt-2 pb-2">
                                Name
                                <div class="ml-1">
                                    @if($sortField === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                        <th class="p-4" style="background-color: #6a994e; color: white;">Description</th>
                        <th class="cursor-pointer hover:bg-gray-100 p-4" wire:click="sortBy('due')" style="background-color: #6a994e; color: white;">
                            <div class="flex items-center">
                                Due Date
                                <div class="ml-1">
                                    @if($sortField === 'due')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                        <th class="cursor-pointer hover:bg-gray-100 p-4" wire:click="sortBy('assigned_to')" style="background-color: #6a994e; color: white;">
                            <div class="flex items-center">
                                Assigned To
                                <div class="ml-1">
                                    @if($sortField === 'assigned_to')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                        <th class="cursor-pointer hover:bg-gray-100 p-4" wire:click="sortBy('created_at')" style="background-color: #6a994e; color: white;">
                            <div class="flex items-center">
                                Created At
                                <div class="ml-1">
                                    @if($sortField === 'created_at')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                        <th class="cursor-pointer hover:bg-gray-100 p-4" wire:click="sortBy('created_by')" style="background-color: #6a994e; color: white;">
                            <div class="flex items-center">
                                Created By
                                <div class="ml-1">
                                    @if($sortField === 'created_by')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($requirements as $requirement)
                        <tr class="hover:bg-blue-50 transition-colors duration-150">
                            <td class="font-medium">{{ $requirement->name }}</td>
                            <td class="whitespace-normal max-w-[300px]">
                                {{ Str::limit($requirement->description, 50) }}
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    @if(\Carbon\Carbon::parse($requirement->due)->isPast())
                                        <i class="fa-solid fa-circle-exclamation text-red-500 text-xs"></i>
                                    @endif
                                    <span class="{{ \Carbon\Carbon::parse($requirement->due)->isPast() ? 'text-red-600 font-medium' : '' }}">
                                        {{ \Carbon\Carbon::parse($requirement->due)->format('m/d/Y h:i a') }}
                                    </span>
                                </div>
                            </td>
                            <td class="whitespace-normal min-w-[150px] max-w-[200px]">
                                {{ $requirement->assigned_to }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($requirement->created_at)->format('m/d/Y h:i a') }}
                            </td>
                            <td>
                                {{ $requirement->creator?->firstname }} {{ $requirement->creator?->lastname }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4">No requirements found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="w-full flex justify-center py-2">
            {{ $requirements->links() }}
        </div>
    @else
        <div class="alert alert-warning shadow-lg">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <div>
                <h3 class="font-bold">No Active Semester</h3>
                <div class="text-xs">Please activate a semester to view requirements.</div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('close-modal', () => {
                document.getElementById('createRequirement').checked = false;
            });
        });
    </script>
</div>