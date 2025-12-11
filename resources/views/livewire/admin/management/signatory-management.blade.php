<div class="p-6">
    <!-- Success Message -->
    @if(session()->has('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session()->has('info'))
        <div class="mb-4 p-3 bg-blue-100 text-blue-700 rounded-lg">
            {{ session('info') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Signatory Management</h3>
            <p class="text-sm text-gray-600">Manage signatures and signatory information. Only one signatory can be active at a time.</p>
        </div>
        <button 
            wire:click="resetForm"
            class="mt-3 md:mt-0 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2"
        >
            <i class="fa-solid fa-plus"></i>
            Add New Signatory
        </button>
    </div>

    <!-- Active Signatory Warning -->
    @php
        $activeSignatory = App\Models\Signatory::where('is_active', true)->first();
    @endphp
    @if($activeSignatory && !$isEditing)
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-info text-yellow-600"></i>
                <div>
                    <p class="font-medium text-yellow-800">Active Signatory: {{ $activeSignatory->name }}</p>
                    <p class="text-sm text-yellow-700">Only one signatory can be active at a time. New signatories will be added as inactive.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-gray-50 p-6 rounded-lg mb-6 border">
        <h4 class="font-medium text-gray-700 mb-4">
            {{ $isEditing ? 'Edit Signatory' : 'Add New Signatory' }}
        </h4>
        
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input 
                        type="text" 
                        wire:model="name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="Enter signatory name"
                    >
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Position -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                    <input 
                        type="text" 
                        wire:model="position"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="e.g., Dean, Director, etc."
                    >
                    @error('position') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Signature Upload -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Signature Image</label>
                    <input 
                        type="file" 
                        wire:model="signature"
                        accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                    >
                    <p class="text-xs text-gray-500 mt-1">Upload signature image (PNG, JPG, max 2MB)</p>
                    @error('signature') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    
                    <!-- Show current signature if editing -->
                    @if($isEditing && $editId)
                        @php
                            $signatory = App\Models\Signatory::find($editId);
                        @endphp
                        @if($signatory && $signatory->has_signature)
                            <div class="mt-2">
                                <p class="text-sm text-gray-600">Current Signature:</p>
                                <img src="{{ $signatory->signature_url }}" class="mt-1 h-20 border rounded">
                            </div>
                        @endif
                    @endif
                    
                    @if($signature)
                        <div class="mt-2">
                            <p class="text-sm text-gray-600">Preview (new):</p>
                            <img src="{{ $signature->temporaryUrl() }}" class="mt-1 h-20 border rounded">
                        </div>
                    @endif
                </div>

                <!-- Active Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center mt-2">
                        <input 
                            type="checkbox" 
                            wire:model="is_active"
                            id="is_active"
                            class="h-4 w-4 text-green-600 rounded focus:ring-green-500"
                            {{ $activeSignatory && !$isEditing ? 'disabled' : '' }}
                        >
                        <label for="is_active" class="ml-2 text-sm text-gray-700">
                            Active 
                            @if($activeSignatory && !$isEditing)
                                <span class="text-xs text-yellow-600">(An active signatory already exists)</span>
                            @endif
                        </label>
                    </div>
                    @if($activeSignatory && !$isEditing)
                        <p class="text-xs text-gray-500 mt-1">
                            To activate this signatory, first deactivate the current active signatory or edit it.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-2">
                <button 
                    type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                >
                    {{ $isEditing ? 'Update Signatory' : 'Save Signatory' }}
                </button>
                @if($isEditing)
                    <button 
                        type="button"
                        wire:click="cancel"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                    >
                        Cancel
                    </button>
                @endif
            </div>
        </form>
    </div>

    <!-- Signatories List -->
    <div class="bg-white rounded-lg border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Signature</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($signatories as $signatory)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $signatory->name }}</div>
                                <div class="text-xs text-gray-500">Created: {{ $signatory->created_at->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $signatory->position }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($signatory->has_signature)
                                    <img 
                                        src="{{ $signatory->signature_url }}" 
                                        alt="Signature" 
                                        class="h-10 w-auto object-contain"
                                    >
                                @else
                                    <span class="text-gray-400 text-sm">No signature</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button 
                                    wire:click="toggleActive({{ $signatory->id }})"
                                    class="px-3 py-1 text-xs rounded-full transition-colors {{ $signatory->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}"
                                    title="{{ $signatory->is_active ? 'Deactivate' : 'Activate' }}"
                                >
                                    {{ $signatory->is_active ? '✓ Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <button 
                                        wire:click="edit({{ $signatory->id }})"
                                        class="text-blue-600 hover:text-blue-900 transition-colors"
                                        title="Edit"
                                    >
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button 
                                        wire:click="deleteConfirm({{ $signatory->id }})"
                                        class="text-red-600 hover:text-red-900 transition-colors"
                                        title="Delete"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fa-solid fa-user-tie text-3xl mb-2 text-gray-300"></i>
                                <p>No signatories found. Add your first signatory above.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($signatories->hasPages())
            <div class="px-6 py-4 border-t">
                {{ $signatories->links() }}
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-red-100 rounded-full">
                        <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Delete Signatory</h3>
                </div>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this signatory? This action cannot be undone.</p>
                <div class="flex justify-end gap-3">
                    <button 
                        wire:click="$set('showDeleteModal', false)"
                        class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="delete"
                        class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-lg transition-colors"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>