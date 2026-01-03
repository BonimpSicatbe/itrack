<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-4 px-6 pt-6">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-xl font-semibold text-green-700">Signatory Management</h3>
                <p class="text-sm text-gray-600">| Manage signatures and signatory information. Only one signatory can be active at a time.</p>
            </div>
        </div>
        <button 
            wire:click="openModal"
            class="btn btn-md bg-green-600 rounded-xl text-gray-50 flex items-center gap-2"
        >
            <i class="fa-solid fa-plus min-w-[20px] text-center"></i>
            Add Signatory
        </button>
    </div>

    <!-- Divider -->
    <div class="border-b border-gray-200 mb-4"></div>

    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="mx-6 mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            <i class="fa-solid fa-circle-check mr-2"></i>
            {{ session('success') }}
        </div>
    @endif
    
    @if(session()->has('info'))
        <div class="mx-6 mb-4 p-3 bg-blue-100 text-blue-700 rounded-lg">
            <i class="fa-solid fa-circle-info mr-2"></i>
            {{ session('info') }}
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="mx-6 mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
            <i class="fa-solid fa-circle-exclamation mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Active Signatory Warning -->
    @php
        $activeSignatory = App\Models\Signatory::where('is_active', true)->first();
    @endphp
    @if($activeSignatory)
        <div class="mx-6 mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-info text-yellow-600"></i>
                <div>
                    <p class="font-medium text-yellow-800">Active Signatory: {{ $activeSignatory->name }}</p>
                    <p class="text-sm text-yellow-700">Only one signatory can be active at a time. New signatories will be added as inactive.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Search and Total Signatories -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4 px-6">
        <!-- Search Box -->
        <div class="w-full sm:w-1/2">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    class="pl-10 block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm"
                    placeholder="Search by name or position..."
                >
            </div>
        </div>

        <!-- Total Signatories Badge -->
        <div class="flex items-center gap-2 bg-green-50 border border-green-600 px-4 py-2 rounded-xl shadow-sm">
            <i class="fa-solid fa-user-tie text-green-700"></i>
            <span class="text-sm font-semibold text-green-700">
                Total Signatories: {{ $signatories->total() }}
            </span>
        </div>
    </div>

    <!-- Signatories Table -->
    <div class="max-h-[500px] overflow-x-auto border border-gray-200 shadow-sm mx-6 rounded-xl">
        <table class="table table-auto table-striped table-pin-rows table-sm w-full rounded-xl">
            <thead>
                <tr class="bg-base-300 font-bold uppercase">
                    <th class="cursor-pointer hover:bg-green-800 p-4 text-left bg-green-700" wire:click="sortBy('name')" style="color: white; width: 25%;">
                        <div class="flex items-center pt-2 pb-2">
                            Name
                            <div class="ml-1">
                                @if ($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort opacity-30"></i>
                                @endif
                            </div>
                        </div>
                    </th>
                    <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4 text-left" wire:click="sortBy('position')" style="color: white; width: 25%;">
                        <div class="flex items-center pt-2 pb-2">
                            Position
                            <div class="ml-1">
                                @if ($sortField === 'position')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort opacity-30"></i>
                                @endif
                            </div>
                        </div>
                    </th>
                    <th class="p-4 text-left bg-green-700" style="color: white; width: 20%;">Signature</th>
                    <th class="p-4 text-left bg-green-700" style="color: white; width: 15%;">Status</th>
                    <th class="p-4 text-center bg-green-700" style="color: white; width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($signatories as $signatory)
                    <tr class="hover:bg-green-50">
                        <td class="whitespace-nowrap p-4">
                            <div class="text-sm font-semibold text-gray-900 pl-4">
                                {{ $signatory->name }}
                            </div>
                            <div class="text-xs text-gray-500 pl-4 mt-1">
                                Created: {{ $signatory->created_at->format('M d, Y') }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap p-4">
                            <div class="text-sm text-gray-500">
                                {{ $signatory->position }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap p-4">
                            <div class="flex items-center">
                                @if($signatory->has_signature)
                                    <div class="relative group">
                                        <!-- Thumbnail preview -->
                                        <div class="flex items-center space-x-2">
                                            <div class="relative">
                                                <img 
                                                    src="{{ route('admin.signatory.signature.preview', $signatory->id) }}" 
                                                    alt="Signature of {{ $signatory->name }}"
                                                    class="h-12 w-24 object-contain border border-gray-300 rounded-lg bg-white p-1 shadow-sm cursor-pointer hover:shadow-md transition-shadow duration-200"
                                                    onclick="window.open('{{ route('admin.signatory.signature.preview', $signatory->id) }}', '_blank')"
                                                    title="Click to preview signature in new tab"
                                                    onerror="this.onerror=null; this.src='https://via.placeholder.com/96x48/efefef/666666?text=No+Signature'; this.classList.add('opacity-50');"
                                                />
                                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                    <div class="bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                                                        <i class="fa-solid fa-expand mr-1"></i>Preview
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex flex-col space-y-1">
                                                <span class="text-xs text-gray-500">
                                                    {{ $signatory->getFirstMedia('signatures')->mime_type ?? 'Image'}}
                                                </span>
                                                <a 
                                                    href="{{ route('admin.signatory.signature.download', $signatory->id) }}"
                                                    class="text-xs text-blue-600 hover:text-blue-800 hover:underline flex items-center"
                                                    title="Download signature"
                                                >
                                                    <i class="fa-solid fa-download mr-1 text-xs"></i>
                                                    Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-2 text-gray-400">
                                        <div class="h-12 w-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                                            <i class="fa-solid fa-signature text-lg"></i>
                                        </div>
                                        <div class="text-xs italic">
                                            No signature uploaded
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="whitespace-nowrap p-4">
                            <button 
                                wire:click="toggleActive({{ $signatory->id }})"
                                class="px-3 py-1 text-xs rounded-full transition-colors {{ $signatory->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}"
                                title="{{ $signatory->is_active ? 'Deactivate' : 'Activate' }}"
                            >
                                {{ $signatory->is_active ? '✓ Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="whitespace-nowrap p-4">
                            <div class="flex justify-center space-x-2 text-base">
                                <!-- Edit button -->
                                <button 
                                    class="text-amber-500 hover:bg-amber-100 rounded-xl p-2 tooltip cursor-pointer"
                                    data-tip="Edit" 
                                    wire:click="edit({{ $signatory->id }})"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <!-- Delete button -->
                                <button 
                                    class="text-red-500 hover:bg-red-100 rounded-xl p-2 tooltip cursor-pointer"
                                    data-tip="Delete"
                                    wire:click="deleteConfirm({{ $signatory->id }})"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center p-8 text-gray-500">
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

    <!-- Add/Edit Signatory Modal -->
    @if($showModal)
        <x-modal name="signatory-modal" :show="$showModal" maxWidth="md">
            <div class="p-0 m-0 rounded-xl">
                <!-- Header -->
                <div class="text-white px-6 py-4 flex items-center space-x-3" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                    <i class="fa-solid {{ $isEditing ? 'fa-user-pen' : 'fa-user-plus' }} text-lg"></i>
                    <h3 class="text-xl font-semibold">{{ $isEditing ? 'Edit Signatory' : 'Add New Signatory' }}</h3>
                </div>

                <!-- Body -->
                <div class="bg-white px-6 py-6 rounded-b-xl">
                    <!-- Active Signatory Warning -->
                    @php
                        $activeSignatory = App\Models\Signatory::where('is_active', true)->first();
                    @endphp
                    @if($activeSignatory && !$isEditing)
                        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-yellow-600"></i>
                                <p class="text-sm text-yellow-700">
                                    <span class="font-medium">Active Signatory: {{ $activeSignatory->name }}</span><br>
                                    Only one signatory can be active at a time. New signatories will be added as inactive.
                                </p>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="save">
                        <div class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                <input 
                                    type="text" 
                                    wire:model="name"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="Enter signatory name"
                                    autofocus
                                >
                                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Position -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                                <input 
                                    type="text" 
                                    wire:model="position"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="e.g., Dean, Director, etc."
                                >
                                @error('position') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Signature Upload -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Signature Image</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 relative">
                                    <!-- File Uploading Indicator -->
                                    @if($uploadingSignature)
                                        <div class="absolute inset-0 bg-white bg-opacity-90 rounded-xl flex flex-col items-center justify-center z-10">
                                            <div class="text-center">
                                                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-green-600 mb-3 mx-auto"></div>
                                                <p class="text-sm text-gray-700 font-medium">Uploading signature...</p>
                                                <p class="text-xs text-gray-500 mt-1">Please wait</p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <input 
                                        type="file" 
                                        wire:model="signature"
                                        accept="image/jpeg,image/png,image/gif"
                                        class="w-full"
                                        id="signature-upload"
                                        wire:loading.attr="disabled"
                                    >
                                    <p class="text-xs text-gray-500 mt-2">Upload signature image (JPG, PNG, GIF, max 2MB)</p>
                                    @error('signature') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Show current signature status if editing -->
                                @if($isEditing && $editId)
                                    @php
                                        $signatory = App\Models\Signatory::find($editId);
                                    @endphp
                                    @if($signatory && $signatory->has_signature)
                                        <div class="mt-3">
                                            <div class="flex items-center gap-3">
                                                @if($signatory->signature_url)
                                                    <div class="flex items-center space-x-2">
                                                        <img 
                                                            src="{{ route('admin.signatory.signature.preview', $signatory->id) }}" 
                                                            alt="Current signature"
                                                            class="h-16 w-32 object-contain border border-gray-300 rounded-lg bg-white p-1"
                                                            onerror="this.onerror=null; this.src='https://via.placeholder.com/128x64/efefef/666666?text=Preview+Error';"
                                                        />
                                                        <div class="flex flex-col space-y-1">
                                                            <a 
                                                                href="{{ route('admin.signatory.signature.download', $signatory->id) }}"
                                                                class="px-3 py-1 bg-green-100 text-green-700 hover:bg-green-200 rounded-lg transition-colors flex items-center gap-1 text-xs"
                                                            >
                                                                <i class="fa-solid fa-download"></i>
                                                                Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">
                                                Uploading a new file will replace the existing signature.
                                            </p>
                                        </div>
                                    @else
                                        <div class="mt-3">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-xmark text-red-600"></i>
                                                <span class="text-sm text-gray-600">No signature uploaded</span>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                
                                @if($signaturePreview)
                                    <div class="mt-3">
                                        <p class="text-sm text-gray-600 mb-1">New Signature Preview:</p>
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $signaturePreview }}" class="h-20 border rounded-xl object-contain">
                                            <div class="flex flex-col space-y-2">
                                                <span class="text-xs text-gray-500">
                                                    This is a temporary preview. The actual file will be saved after submission.
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Active Status -->
                            <div class="pt-2">
                                <div class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model="is_active"
                                        id="is_active"
                                        class="h-4 w-4 text-green-600 rounded focus:ring-green-500"
                                        {{ $activeSignatory && !$isEditing ? 'disabled' : '' }}
                                    >
                                    <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
                                        Set as Active Signatory
                                        @if($activeSignatory && !$isEditing)
                                            <span class="text-xs text-yellow-600 ml-1">(Not available)</span>
                                        @endif
                                    </label>
                                </div>
                                @if($activeSignatory && !$isEditing)
                                    <p class="text-xs text-gray-500 mt-1 ml-6">
                                        There is already an active signatory ({{ $activeSignatory->name }}). 
                                        You must deactivate it first or edit it to change the active status.
                                    </p>
                                @else
                                    <p class="text-xs text-gray-500 mt-1 ml-6">
                                        Only one signatory can be active at a time. Activating this will deactivate others.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                            <button 
                                type="button"
                                wire:click="closeModal"
                                class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 cursor-pointer"
                                wire:loading.attr="disabled"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-medium cursor-pointer"
                                wire:loading.attr="disabled"
                                wire:target="save"
                            >
                                <span wire:loading.remove wire:target="save">
                                    {{ $isEditing ? 'Update Signatory' : 'Save Signatory' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> 
                                    {{ $isEditing ? 'Updating...' : 'Saving...' }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <x-modal name="delete-modal" :show="$showDeleteModal" maxWidth="md">
            <div class="p-0 m-0 rounded-xl">
                <div class="bg-red-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <h3 class="text-xl font-semibold">Delete Signatory</h3>
                </div>

                <div class="bg-white px-6 py-6 rounded-b-xl">
                    @if($signatoryToDelete)
                        <div class="space-y-4">
                            <p class="text-gray-700">
                                Are you sure you want to delete the signatory 
                                <span class="font-semibold text-red-600">"{{ $signatoryToDelete->name }}"</span>?
                            </p>
                            <p class="text-sm text-gray-600">
                                This action cannot be undone. All signature data will be permanently removed.
                            </p>
                            
                            @if($signatoryToDelete->is_active)
                                <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-sm text-red-700">
                                        <i class="fa-solid fa-exclamation-triangle mr-2"></i>
                                        This signatory is currently active. You cannot delete an active signatory.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                            <button 
                                wire:click="$set('showDeleteModal', false)"
                                class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button 
                                wire:click="delete"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-medium cursor-pointer"
                                wire:loading.attr="disabled"
                                {{ $signatoryToDelete->is_active ? 'disabled' : '' }}
                            >
                                <span wire:loading.remove wire:target="delete">
                                    <i class="fa-solid fa-trash mr-2"></i> Delete
                                </span>
                                <span wire:loading wire:target="delete">
                                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                                </span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </x-modal>
    @endif
</div>