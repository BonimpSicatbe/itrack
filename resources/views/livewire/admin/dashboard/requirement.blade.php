<div class="flex flex-col w-full gap-6 rounded-xl">
    @if($activeSemester)
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6">
            <!-- Search Box -->
            <div class="w-full sm:w-1/3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-500"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        id="search"
                        class="pl-10 block w-sm rounded-xl border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm" 
                        placeholder="Search requirements..."
                    >
                </div>
            </div>

            <!-- Redirect Button -->
            <div class="w-full sm:w-auto mt-4 sm:mt-0">
                <a href="{{ route('admin.requirements.index') }}" 
                   class="px-4 py-1.5 bg-green-600 text-white font-semibold rounded-full hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-73E2A7 focus:ring-offset-2 transition text-sm cursor-pointer flex items-center gap-2">
                    Requirements Page
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </a>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="max-h-[500px] overflow-x-auto shadow-sm mb-4">
            <table class="min-w-full divide-y divide-gray-200 rounded-lg"> 
                <thead>
                    <tr class="bg-green-700">
                        @php
                            $columns = [
                                'name' => 'Name',
                                'description' => 'Description',
                                'due' => 'Due Date',
                                'assigned_to' => 'Assigned To',
                                'created_at' => 'Created At',
                                'created_by' => 'Created By'
                            ];
                        @endphp
                        
                        @foreach($columns as $field => $label)
                            <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider cursor-pointer hover:bg-green-800 transition-colors"
                                wire:click="sortBy('{{ $field }}')">
                                <div class="flex items-center">
                                    <span>{{ $label }}</span>
                                    @if($sortField === $field)
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-green-300"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-70"></i>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 text-gray-600">
                    @forelse ($requirements as $requirement)
                        <tr class="hover:bg-green-50 transition-colors duration-150 cursor-pointer" 
                            wire:click="showRequirement({{ $requirement->id }})">
                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $requirement->name }}</td>
                            <td class="px-4 py-3 whitespace-normal max-w-[300px]">
                                {{ Str::limit($requirement->description, 50) }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $dueDate = \Carbon\Carbon::parse($requirement->due);
                                    $isPast = $dueDate->isPast();
                                @endphp
                                <div class="flex items-center gap-1">
                                    @if($isPast)
                                        <i class="fa-solid fa-circle-exclamation text-red-500 text-sm"></i>
                                    @endif
                                    <span class="{{ $isPast ? 'text-red-600 font-medium' : '' }}">
                                        {{ $dueDate->format('m/d/Y h:i a') }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-normal min-w-[150px] max-w-[200px]">
                                {{ $requirement->assigned_to }}
                            </td>
                            <td class="px-4 py-3">
                                {{ \Carbon\Carbon::parse($requirement->created_at)->format('m/d/Y h:i a') }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $requirement->creator?->firstname }} {{ $requirement->creator?->lastname }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <i class="fa-solid fa-folder-open text-4xl text-gray-300 mb-2 block mx-auto"></i>
                                <p class="text-sm font-semibold">No requirements found.</p>
                                @if($search)
                                    <p class="text-sm font-semibold text-amber-500 mt-1">Try adjusting your search term</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="flex items-center p-4 bg-[#DEF4C6] text-[#1B512D] rounded-lg shadow-lg">
            <i class="fa-solid fa-triangle-exclamation text-lg mr-3"></i>
            <div>
                <h3 class="font-bold">No Active Semester</h3>
                <div class="text-xs">Please activate a semester to view requirements.</div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('close-modal', () => {
                const modal = document.getElementById('createRequirement');
                if (modal) modal.checked = false;
            });
        });
    </script>
</div>