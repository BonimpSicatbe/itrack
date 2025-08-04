<div class="flex flex-col gap-4 w-full">
    <div class="flex flex-col gap-4 w-full bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold">Academic Semester Management</h2>

        <!-- Semester Form -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Semester Name</label>
                        <input type="text" wire:model="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" wire:model="start_date" id="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" wire:model="end_date" id="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">Set as active semester</label>
                </div>
                <div class="flex justify-end">
                    <button type="button" wire:click="resetForm" class="btn btn-default mr-2">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        {{ $editMode ? 'Update Semester' : 'Create Semester' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Semesters List -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($semesters as $semester)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $semester->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $semester->start_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $semester->end_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($semester->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <button wire:click="setActive({{ $semester->id }})" class="text-blue-600 hover:text-blue-900">Set Active</button>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button wire:click="edit({{ $semester->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                                <button wire:click="delete({{ $semester->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No semesters found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>