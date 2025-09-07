<div class="flex flex-col lg:flex-row gap-6">
    <div class="w-full bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">

        <!-- Header (Fixed) -->
        <div class="flex items-center justify-between px-6 py-4 sticky top-0 z-10"
             style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="text-2xl fa-solid fa-circle-info"></i> Edit Requirement Details
            </h2>
            <button type="button" onclick="history.back()" class="bg-white text-green-700 px-4 py-1.5 rounded-full shadow font-semibold text-sm transition-all duration-200 flex items-center gap-2">
                <i class="fa-solid fa-arrow-left text-green-700"></i> Back
            </button>
        </div>

        <!-- Body (Scrollable) -->
        <div class="border-b border-gray-100 max-h-[625px] overflow-y-auto">
            <!-- Details Form -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2">
                    <x-text-fieldset label="requirement name" name="name" wire:model="name" type="text" />
                </div>
                <div class="md:col-span-2">
                    <x-textarea-fieldset label="requirement description" name="description" wire:model="description" />
                </div>
                <x-text-fieldset label="requirement due date & time" name="due" wire:model="due" type="datetime-local" />
                <x-select-fieldset label="requirement priority" name="priority" wire:model="priority">
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                </x-select-fieldset>
                <x-select-fieldset wire:model.live="sector" label="requirement sector">
                    <option value="college">College</option>
                    <option value="department">Department</option>
                </x-select-fieldset>
                @if ($this->sector === 'college')
                    <x-select-fieldset name="assigned_to" wire:model="assigned_to" label="select college">
                        @foreach ($colleges as $college)
                            <option value="{{ $college->name }}">{{ $college->name }}</option>
                        @endforeach
                    </x-select-fieldset>
                @elseif ($this->sector === 'department')
                    <x-select-fieldset name="assigned_to" wire:model="assigned_to" label="select department">
                        @foreach ($departments as $department)
                            <option value="{{ $department->name }}">{{ $department->name }}</option>
                        @endforeach
                    </x-select-fieldset>
                @endif
                <div class="md:col-span-2 flex justify-end gap-2 pt-4">
                    <button type="button" onclick="history.back()" class="btn text-sm px-4 py-1.5 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-full transition">
                        Cancel
                    </button>
                    <button type="submit" wire:click.prevent="updateRequirement" class="btn text-sm px-4 py-1.5 bg-green-600 text-white hover:bg-green-700 rounded-full transition">
                        Update Requirement
                    </button>
                </div>
            </div>

            <!-- Required Files -->
            <div class="px-6 py-3 bg-gray-100 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="text-xl fa-solid fa-folder-open text-1C7C54"></i>
                    <h3 class="text-lg font-semibold text-gray-800">Required Files</h3>
                </div>
                <button type="button" 
                        class="btn btn-sm bg-blue-600 text-white hover:bg-blue-700 rounded-full transition flex items-center gap-2"
                        wire:click="$set('showUploadModal', true)">
                    <i class="fa-solid fa-upload"></i>
                    Upload Files
                </button>
            </div>

            <div class="p-4">
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-green-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">File Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">Modified</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-white uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($requiredFiles as $file)
                                <tr class="hover:bg-73E2A7/10 transition duration-200">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 truncate max-w-xs">{{ $file->file_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ strtoupper($file->extension) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $file->humanReadableSize }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $file->updated_at->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                        <a href="{{ route('guide.download', $file->id) }}" 
                                           class="text-1C7C54 hover:text-1B512D transition">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        @if($this->isPreviewable($file->mime_type))
                                        <a href="{{ route('guide.preview', $file->id) }}" 
                                           target="_blank"
                                           class="text-B1CF5F hover:text-1B512D transition">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @endif
                                        <button wire:click.prevent="confirmFileRemoval({{ $file->id }})" 
                                                wire:loading.attr="disabled"
                                                type="button"
                                                class="text-red-600 hover:text-red-800 transition" 
                                                >
                                            <span wire:loading.remove wire:target="removeFile">
                                                <i class="fa-solid fa-trash"></i>
                                            </span>
                                            <span wire:loading wire:target="removeFile">
                                                <i class="fa-solid fa-spinner animate-spin"></i>
                                            </span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-xs text-gray-500">No required files attached.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Assigned Users -->
            <div class="px-6 py-3 bg-gray-100 border-t border-gray-100 flex items-center gap-2">
                <i class="fa-solid text-xl fa-users text-1C7C54"></i>
                <h3 class="font-semibold text-lg text-gray-800">Assigned Users</h3>
            </div>
            
            <div class="p-4">
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-green-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase">Email</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($assignedUsers as $user)
                                <tr class="hover:bg-73E2A7/10 transition cursor-pointer" wire:click="showUser({{ $user->id }})">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full flex items-center justify-center bg-73E2A7 text-1B512D font-bold shadow">
                                                {{ substr($user->full_name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-gray-900 truncate">{{ $user->full_name }}</div>
                                                <div class="text-xs text-gray-500 truncate">{{ $user->department->name ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 truncate">{{ $user->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-center text-xs text-gray-500">No users assigned.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Upload Modal --}}
    <input type="checkbox" id="upload_required_files_modal" class="modal-toggle" @checked($showUploadModal) />
    <div class="modal" role="dialog" wire:ignore.self>
        <form wire:submit.prevent="uploadRequiredFiles" class="modal-box flex flex-col gap-3 rounded-xl">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">Upload Required Files</h3>
                <button type="button" 
                        class="btn btn-ghost btn-sm btn-circle"
                        wire:click="$set('showUploadModal', false)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <x-file-fieldset name="required_files" wire:model="required_files" multiple />
            <button type="submit" class="btn px-4 py-1.5 bg-green-600 text-white hover:bg-green-700 rounded-full transition w-full">
                Submit
            </button>
        </form>
        <label class="modal-backdrop" wire:click="$set('showUploadModal', false)"></label>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <x-modal name="delete-file-confirmation-modal" :show="$showDeleteModal" maxWidth="md">
            <div class="bg-red-600 text-white rounded-t-2xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm File Deletion</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="space-y-4">
                    @php
                        $fileToDelete = $requiredFiles->find($fileToDelete);
                    @endphp
                    <p class="text-gray-700">
                        Are you sure you want to delete the file 
                        <span class="font-semibold text-red-600">"{{ $fileToDelete->file_name ?? 'this file' }}"</span>?
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. The file will be permanently removed.
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)" 
                            class="px-4 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="removeFile" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-full text-sm font-medium cursor-pointer"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="removeFile">
                            <i class="fa-solid fa-trash mr-2"></i> Delete
                        </span>
                        <span wire:loading wire:target="removeFile">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
</div>