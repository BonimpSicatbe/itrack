<div class="flex flex-col w-full gap-6 rounded-xl">
    @if($activeSemester)
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

            <!-- Search Box -->
            <div class="w-full sm:w-1/3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-500"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" id="search"
                        class="pl-10 block w-sm rounded-xl border-gray-300 shadow-sm focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm" 
                        placeholder="Search requirements..."
                    >
                </div>
            </div>

            <!-- Redirect Button -->
            <div class="w-full sm:w-auto mt-4 sm:mt-0">
                <a href="{{ route('admin.requirements.index') }}" 
                class="px-4 py-1.5 bg-1C7C54 text-white font-semibold rounded-full hover:bg-1B512D focus:outline-none focus:ring-2 focus:ring-73E2A7 focus:ring-offset-2 transition text-sm cursor-pointer flex items-center gap-2">
                    Requirements Page
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- content --}}
        <div class="max-h-[500px] overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
            <table class="table table-auto table-striped table-pin-rows table-sm min-w-[800px] rounded-lg"> 
                <thead>
                    <tr class="bg-base-300 font-bold uppercase">
                        <th class="cursor-pointer hover:bg-blue-50 p-4" wire:click="sortBy('name')" style="background-color: #1C7C54; color: white;">
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
                        <th class="p-4" style="background-color: #1C7C54; color: white;">Description</th>
                        <th class="cursor-pointer hover:bg-blue-50 p-4" wire:click="sortBy('due')" style="background-color: #1C7C54; color: white;">
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
                        <th class="cursor-pointer hover:bg-blue-50 p-4" wire:click="sortBy('assigned_to')" style="background-color: #1C7C54; color: white;">
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
                        <th class="cursor-pointer hover:bg-blue-50 p-4" wire:click="sortBy('created_at')" style="background-color: #1C7C54; color: white;">
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
                        <th class="cursor-pointer hover:bg-blue-50 p-4" wire:click="sortBy('created_by')" style="background-color: #1C7C54; color: white;">
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
                        <tr class="hover:bg-blue-50 transition-colors duration-150 cursor-pointer" 
                            wire:click="showRequirement({{ $requirement->id }})">
                            <td class="font-medium">{{ $requirement->name }}</td>
                            <td class="whitespace-normal max-w-[300px]">
                                {{ Str::limit($requirement->description, 50) }}
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    @if(\Carbon\Carbon::parse($requirement->due)->isPast())
                                        <i class="fa-solid fa-circle-exclamation text-red-500 text-sm"></i>
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
                            <td colspan="6" class="text-center p-8 text-gray-500">
                                <i class="fa-solid fa-inbox text-4xl text-gray-300 mb-2"></i>
                                <p>No requirements found.</p>
                                @if($search)
                                    <p class="text-sm mt-2">Try adjusting your search term</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="w-full flex justify-center py-2">
            {{ $requirements->links() }}
        </div>
    @else
        <div class="alert bg-[#DEF4C6] text-[#1B512D] rounded-lg shadow-lg">
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