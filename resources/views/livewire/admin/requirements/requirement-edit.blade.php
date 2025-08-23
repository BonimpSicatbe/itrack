<div class="flex flex-col lg:flex-row gap-6 w-full pb-5">
    {{-- Breadcrumbs --}}
    {{-- Left Column --}}
    <div class="w-full lg:w-2/3 space-y-6">
        {{-- Requirement Details Card --}}
        <div class="w-full bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 flex justify-between items-center bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">Edit Requirement Details</h2>
            </div>
            <div class="p-6 space-y-4">
                <form wire:submit.prevent='updateRequirement' class="grid grid-cols-1 md:grid-cols-2 gap-4" enctype="multipart/form-data">
                    {{-- requirement name --}}
                    <div class="col-span-2">
                        <x-text-fieldset label="requirement name" name="name" wire:model="name" type="text" />
                    </div>

                    <div class="col-span-2">
                        <x-textarea-fieldset label="requirement description" name="description" wire:model="description" />
                    </div>

                    {{-- requirement due date --}}
                    <x-text-fieldset label="requirement due date & time" name="due" wire:model="due" type="datetime-local" />

                    {{-- requirement priority --}}
                    <x-select-fieldset label="requirement priority" name="priority" wire:model="priority">
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                    </x-select-fieldset>

                    {{-- requirement sector --}}
                    <x-select-fieldset wire:model.live="sector" label="requirement sector">
                        <option value="college">College</option>
                        <option value="department">Department</option>
                    </x-select-fieldset>

                    {{-- requirement assigned to --}}
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

                    <div class="col-span-2 flex justify-end gap-2 pt-4">
                        <button type="button" onclick="history.back()" class="btn btn-sm btn-default">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">Update Requirement</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Required Files Card --}}
        <div class="w-full bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Required Files</h2>
                <button type="button" 
                        class="btn btn-sm btn-primary"
                        wire:click="$set('showUploadModal', true)">
                    <i class="fa-solid fa-upload mr-2"></i>
                    Upload Files
                </button>
            </div>
            <div class="p-6">
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modified</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($requiredFiles as $file)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 truncate max-w-xs">{{ $file->file_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ strtoupper($file->extension) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->humanReadableSize }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->updated_at->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('guide.download', ['media' => $file->id]) }}" 
                                           class="text-blue-600 hover:text-blue-900 inline-flex items-center" 
                                           title="Download">
                                            <i class="fa-solid fa-download mr-1"></i>
                                        </a>
                                        @if($this->isPreviewable($file->mime_type))
                                        <a href="{{ route('guide.preview', ['media' => $file->id]) }}" 
                                           target="_blank"
                                           class="text-green-600 hover:text-green-900 inline-flex items-center" 
                                           title="View">
                                            <i class="fa-solid fa-eye mr-1"></i>
                                        </a>
                                        @endif
                                        <button wire:click.prevent="removeFile({{ $file->id }})" 
                                                wire:loading.attr="disabled"
                                                type="button"
                                                class="text-red-600 hover:text-red-900 inline-flex items-center" 
                                                title="Remove">
                                            <span wire:loading.remove wire:target="removeFile({{ $file->id }})">
                                                <i class="fa-solid fa-trash mr-1"></i>
                                            </span>
                                            <span wire:loading wire:target="removeFile({{ $file->id }})">
                                                <i class="fa-solid fa-spinner animate-spin mr-1"></i>
                                            </span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No required files attached to this requirement.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column --}}
    <div class="w-full lg:w-1/3">
        {{-- Assigned Users Card --}}
        <div class="w-full bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden h-full">
            <div class="border-b border-gray-100 px-6 py-4 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">Assigned Users</h2>
            </div>
            <div class="p-6">
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            </tr>
                        </thead>
                        {{-- In the Assigned Users Card section --}}
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($assignedUsers as $user)
                                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" wire:click='showUser({{ $user->id }})'>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600">
                                                {{ substr($user->full_name, 0, 1) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->department->name ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No users assigned to this requirement.</td>
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
        <form wire:submit.prevent="uploadRequiredFiles" class="modal-box flex flex-col gap-2">
            <div class="flex flex-row gap-4 w-full justify-between">
                <h3 class="text-lg font-bold">Upload Required Files</h3>
                <button type="button" 
                        class="btn btn-ghost btn-default btn-sm btn-circle"
                        wire:click="$set('showUploadModal', false)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <x-file-fieldset name="required_files" wire:model="required_files" multiple />
            <button type="submit" class="btn btn-success btn-sm w-full">
                Submit
            </button>
        </form>
        <label class="modal-backdrop" wire:click="$set('showUploadModal', false)"></label>
    </div>
</div>